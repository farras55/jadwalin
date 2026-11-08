<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ScheduleBuilderService
{
    /**
     * Generate roster automatically using heuristic constraints, fairness rules,
     * and custom manager requirements (quota per shift, active days, dynamic date range).
     *
     * @param  int|array  $departmentId  Single department ID or array of department IDs
     * @param  array|bool|null  $requirementsOrOverwrite  Custom requirements array, or boolean overwrite flag for backward compatibility
     */
    public function generateWeeklyRoster(
        int $companyId,
        int|array $departmentId,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        array|bool|null $requirementsOrOverwrite = null,
        bool $overwriteDrafts = true
    ): array {
        // Handle backward compatibility for signature
        $requirements = null;
        if (is_bool($requirementsOrOverwrite)) {
            $overwriteDrafts = $requirementsOrOverwrite;
        } elseif (is_array($requirementsOrOverwrite)) {
            $requirements = $requirementsOrOverwrite;
        }

        $departmentIds = is_array($departmentId) ? $departmentId : [$departmentId];

        return DB::transaction(function () use ($companyId, $departmentIds, $startDate, $endDate, $requirements, $overwriteDrafts) {
            // 1. Inisialisasi Pengaturan Ketenagakerjaan (CompanySettings)
            $settings = CompanySetting::firstOrCreate(
                ['company_id' => $companyId],
                [
                    'max_regular_hours' => 40,
                    'max_overtime_hours' => 18,
                    'work_hour_limit_type' => 'weekly',
                    'min_rest_hours' => 11,
                    'max_working_days_per_week' => 6,
                    'auto_buffer_minutes' => 30,
                    'grace_period_minutes' => 15,
                    'is_auto_clockout' => true,
                ]
            );

            $maxRegularHours = (int) ($settings->max_regular_hours ?? 40);
            $maxOvertimeHours = (int) ($settings->max_overtime_hours ?? 18);
            $absoluteMaxHours = $maxRegularHours > 0 ? ($maxRegularHours + $maxOvertimeHours) : 0;
            $minRestHours = (int) ($settings->min_rest_hours ?? 11);
            $maxWorkingDays = (int) ($settings->max_working_days_per_week ?? 6);
            $bufferMinutes = (int) ($settings->auto_buffer_minutes ?? 30);

            $rangeStart = $startDate->copy()->startOfDay();
            $rangeEnd = $endDate->copy()->endOfDay();

            $totalShiftsCreated = 0;
            $totalAssignmentsCreated = 0;
            $allSoftWarnings = [];
            $allUnfilledSlots = [];

            // 2. Proses Setiap Departemen Sasaran
            // Bersihkan draf lama di seluruh departemen sasaran dalam 1 query batch
            if ($overwriteDrafts && ! empty($departmentIds)) {
                $draftShiftIds = Shift::where('company_id', $companyId)
                    ->whereIn('department_id', $departmentIds)
                    ->where('status', 'draft')
                    ->whereBetween('start_time', [$rangeStart, $rangeEnd])
                    ->pluck('id');

                if ($draftShiftIds->isNotEmpty()) {
                    $draftShiftIds->chunk(500)->each(function ($chunk) {
                        ShiftAssignment::whereIn('shift_id', $chunk)->delete();
                        Shift::whereIn('id', $chunk)->delete();
                    });
                }
            }

            // 2. Proses Setiap Departemen Sasaran
            foreach ($departmentIds as $deptId) {
                $deptId = (int) $deptId;

                // Pre-load template yang sudah ada pada departemen ini untuk menghindari query berulang
                $existingTemplates = ShiftTemplate::where('company_id', $companyId)
                    ->where('department_id', $deptId)
                    ->get()
                    ->keyBy('id');

                // Siapkan Konfigurasi Shift (Mendukung Jumlah Shift Dinamis: Tambah/Hapus/Kustom)
                $configuredShifts = [];

                $deptReqs = null;
                if ($requirements) {
                    if (isset($requirements[$deptId]) && is_array($requirements[$deptId])) {
                        // Struktur multi-departemen: $requirements[deptId] = [ 0 => [...], 1 => [...] ]
                        $deptReqs = $requirements[$deptId];
                    } elseif (
                        count($departmentIds) === 1 &&
                        is_array($requirements) &&
                        ! isset($requirements[$deptId]) &&
                        isset($requirements[0]) &&
                        is_array($requirements[0])
                    ) {
                        // Format legacy flat untuk 1 departemen: $requirements = [ 0 => [...], 1 => [...] ]
                        $deptReqs = $requirements;
                    }
                }

                if ($deptReqs && ! empty($deptReqs)) {
                    foreach ($deptReqs as $key => $reqItem) {
                        if (! is_array($reqItem)) {
                            continue;
                        }

                        $isEnabled = ! empty($reqItem['enabled']) && $reqItem['enabled'] !== 'false' && $reqItem['enabled'] !== '0';
                        if (! $isEnabled) {
                            continue;
                        }

                        $tmpl = null;
                        $templateId = ! empty($reqItem['template_id']) ? (int) $reqItem['template_id'] : (is_numeric($key) ? (int) $key : null);
                        if ($templateId && $existingTemplates->has($templateId)) {
                            $tmpl = $existingTemplates->get($templateId);
                        }

                        $title = trim($reqItem['title'] ?? ($tmpl?->title ?? 'Shift'));
                        $startTime = $reqItem['start_time'] ?? ($tmpl?->start_time ?? '08:00:00');
                        $endTime = $reqItem['end_time'] ?? ($tmpl?->end_time ?? '16:00:00');
                        $quota = max(1, (int) ($reqItem['quota'] ?? ($tmpl?->default_quota ?? 1)));
                        $days = isset($reqItem['days']) && is_array($reqItem['days']) ? array_map('intval', $reqItem['days']) : [1, 2, 3, 4, 5, 6, 7];
                        $colorCode = $reqItem['color_code'] ?? ($tmpl?->color_code ?? '#6C5CE7');

                        $formattedStartTime = strlen($startTime) === 5 ? $startTime.':00' : $startTime;
                        $formattedEndTime = strlen($endTime) === 5 ? $endTime.':00' : $endTime;

                        if ($tmpl) {
                            if (
                                $tmpl->title !== $title ||
                                $tmpl->start_time !== $formattedStartTime ||
                                $tmpl->end_time !== $formattedEndTime ||
                                (int) $tmpl->default_quota !== (int) $quota ||
                                $tmpl->color_code !== $colorCode
                            ) {
                                $tmpl->update([
                                    'title' => $title,
                                    'start_time' => $formattedStartTime,
                                    'end_time' => $formattedEndTime,
                                    'default_quota' => $quota,
                                    'color_code' => $colorCode,
                                ]);
                            }
                        } else {
                            $tmpl = ShiftTemplate::firstOrCreate(
                                [
                                    'company_id' => $companyId,
                                    'department_id' => $deptId,
                                    'title' => $title,
                                    'start_time' => $formattedStartTime,
                                    'end_time' => $formattedEndTime,
                                ],
                                [
                                    'default_quota' => $quota,
                                    'color_code' => $colorCode,
                                ]
                            );
                            $existingTemplates->put($tmpl->id, $tmpl);
                        }

                        $configuredShifts[] = [
                            'template_id' => $tmpl?->id,
                            'title' => $title,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'quota' => $quota,
                            'days' => $days,
                            'color_code' => $colorCode,
                        ];
                    }
                } else {
                    $templates = $existingTemplates->sortBy('start_time');

                    foreach ($templates as $t) {
                        $configuredShifts[] = [
                            'template_id' => $t->id,
                            'title' => $t->title,
                            'start_time' => $t->start_time,
                            'end_time' => $t->end_time,
                            'quota' => $t->default_quota,
                            'days' => [1, 2, 3, 4, 5, 6, 7],
                            'color_code' => $t->color_code ?? '#6C5CE7',
                        ];
                    }
                }

                if (empty($configuredShifts)) {
                    continue;
                }

                // Ambil Staf Aktif pada Departemen
                $employees = User::where('company_id', $companyId)
                    ->where('department_id', $deptId)
                    ->where('status', 'active')
                    ->where('role', 'employee')
                    ->get();

                if ($employees->isEmpty()) {
                    continue;
                }

                $empIds = $employees->pluck('id')->all();

                // OPTIMASI: Eager Load Semua ShiftAssignment Existing dalam 1 Query Tunggal (Eliminasi N+1)
                $existingAssignmentsGrouped = ShiftAssignment::whereIn('user_id', $empIds)
                    ->whereHas('shift', function ($q) use ($rangeStart, $rangeEnd) {
                        $q->whereBetween('start_time', [$rangeStart, $rangeEnd])
                            ->where('status', '!=', 'draft');
                    })
                    ->with('shift')
                    ->get()
                    ->groupBy('user_id');

                // Muat Ketersediaan yang Approved dalam 1 Query Tunggal
                $availabilities = Availability::where('company_id', $companyId)
                    ->where('status', 'approved')
                    ->whereIn('user_id', $empIds)
                    ->get();

                // Pre-parse Availabilities ke Array In-Memory agar O(1) & Tanpa Overhead Carbon::parse di Loop
                $parsedAvailabilitiesByUser = [];
                foreach ($availabilities as $avail) {
                    $sCarbon = Carbon::parse($avail->start_time);
                    $eCarbon = Carbon::parse($avail->end_time);

                    $parsedAvailabilitiesByUser[$avail->user_id][] = [
                        'is_recurring' => (bool) $avail->is_recurring,
                        'day_of_week' => (int) $avail->day_of_week,
                        'start_h' => (int) $sCarbon->hour,
                        'start_m' => (int) $sCarbon->minute,
                        'end_h' => (int) $eCarbon->hour,
                        'end_m' => (int) $eCarbon->minute,
                        'start_carbon' => $sCarbon,
                        'end_carbon' => $eCarbon,
                    ];
                }

                // Inisialisasi Tracker Beban Kerja Staf In-Memory
                $staffTrackers = [];
                foreach ($employees as $emp) {
                    $userAssignments = $existingAssignmentsGrouped->get($emp->id, collect());
                    $totalHours = 0.0;
                    $daysWorked = [];
                    $assignedShifts = [];

                    foreach ($userAssignments as $ea) {
                        $s = $ea->shift;
                        if (! $s) {
                            continue;
                        }
                        $dur = $s->start_time->diffInMinutes($s->end_time) / 60.0;
                        $totalHours += $dur;
                        $daysWorked[$s->start_time->format('Y-m-d')] = true;
                        $assignedShifts[] = [
                            'start_time' => $s->start_time->copy(),
                            'end_time' => $s->end_time->copy(),
                        ];
                    }

                    $staffTrackers[$emp->id] = [
                        'user' => $emp,
                        'total_hours' => $totalHours,
                        'days_worked' => $daysWorked,
                        'assigned_shifts' => $assignedShifts,
                    ];
                }

                // Hitung RDO (Rolling Day Off / Libur Bergilir) untuk staf Full-Time
                // Berdasarkan regulasi dan batas jam platform (CompanySetting)
                $ftEmployees = $employees->where('employment_type', 'full_time')->values();
                $ftCount = $ftEmployees->count();

                $maxWorkDaysSetting = ($maxWorkingDays > 0 && $maxWorkingDays <= 7) ? $maxWorkingDays : 6;
                $avgShiftDuration = 8.0;
                if (! empty($configuredShifts)) {
                    $durations = [];
                    foreach ($configuredShifts as $sc) {
                        $sT = Carbon::parse($sc['start_time']);
                        $eT = Carbon::parse($sc['end_time']);
                        if ($eT->lte($sT)) {
                            $eT->addDay();
                        }
                        $durations[] = $sT->diffInMinutes($eT) / 60.0;
                    }
                    if (! empty($durations)) {
                        $avgShiftDuration = array_sum($durations) / count($durations);
                    }
                }

                $daysFromRegularHours = ($maxRegularHours > 0)
                    ? (int) floor($maxRegularHours / max(1.0, $avgShiftDuration))
                    : $maxWorkDaysSetting;

                // Hari kerja reguler yang diizinkan per siklus 7 hari
                $allowedWorkDays = max(1, min($maxWorkDaysSetting, $daysFromRegularHours));
                $daysOffPerWeek = max(0, 7 - $allowedWorkDays);

                $scheduledOffDaysByUser = [];
                if ($daysOffPerWeek > 0 && $ftCount > 0) {
                    // Pola penyebaran hari libur di hari kerja biasa (Selasa=2, Kamis=4, Rabu=3, Jumat=5, Senin=1, Sabtu=6)
                    // Menjaga hari Minggu tetap penuh staf FT agar tidak menumpuk di hari Minggu
                    $dayPattern = [2, 4, 3, 5, 1, 6];
                    foreach ($ftEmployees as $idx => $ftUser) {
                        for ($d = 0; $d < $daysOffPerWeek; $d++) {
                            $slotIdx = ($idx * $daysOffPerWeek + $d) % count($dayPattern);
                            $offDayIso = $dayPattern[$slotIdx];
                            $scheduledOffDaysByUser[$ftUser->id][$offDayIso] = true;
                        }
                    }
                }

                $deptPendingAssignments = [];

                // Loop Tanggal Dinamis (Mulai s.d. Selesai)
                $currentDate = $rangeStart->copy();
                while ($currentDate->lte($rangeEnd)) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $dayOfWeekIso = $currentDate->dayOfWeekIso; // 1 = Senin, 7 = Minggu

                    // 1. Kumpulkan shift aktif untuk hari ini
                    $activeShiftsToday = [];
                    foreach ($configuredShifts as $scIdx => $sc) {
                        if (empty($sc['days']) || in_array($dayOfWeekIso, $sc['days']) || in_array((string) $dayOfWeekIso, $sc['days'])) {
                            $activeShiftsToday[$scIdx] = $sc;
                        }
                    }

                    // 2. Hitung staf FT yang aktif dan siap bertugas hari ini (bukan RDO hari ini & belum bekerja hari ini)
                    $ftReadyTodayCount = 0;
                    foreach ($staffTrackers as $trk) {
                        $u = $trk['user'];
                        if ($u->employment_type === 'full_time') {
                            $isOff = ! empty($scheduledOffDaysByUser[$u->id][$dayOfWeekIso]);
                            $workedToday = isset($trk['days_worked'][$dateStr]);
                            if (! $isOff && ! $workedToday) {
                                $ftReadyTodayCount++;
                            }
                        }
                    }

                    // 3. Alokasikan target FT secara berimbang per shift agar shift malam juga didampingi FT Leader
                    $targetFTPerShift = [];
                    $activeShiftCount = count($activeShiftsToday);
                    if ($activeShiftCount > 0 && $ftReadyTodayCount > 0) {
                        if ($ftReadyTodayCount >= $activeShiftCount) {
                            foreach ($activeShiftsToday as $scIdx => $sc) {
                                $targetFTPerShift[$scIdx] = 1;
                            }
                            $remainingFT = $ftReadyTodayCount - $activeShiftCount;
                            $sortedShiftIndices = array_keys($activeShiftsToday);
                            usort($sortedShiftIndices, function ($a, $b) use ($activeShiftsToday) {
                                return $activeShiftsToday[$b]['quota'] <=> $activeShiftsToday[$a]['quota'];
                            });
                            foreach ($sortedShiftIndices as $scIdx) {
                                if ($remainingFT <= 0) {
                                    break;
                                }
                                $maxPossibleForShift = $activeShiftsToday[$scIdx]['quota'];
                                if ($targetFTPerShift[$scIdx] < $maxPossibleForShift) {
                                    $targetFTPerShift[$scIdx]++;
                                    $remainingFT--;
                                }
                            }
                        } else {
                            $sortedShiftIndices = array_keys($activeShiftsToday);
                            usort($sortedShiftIndices, function ($a, $b) use ($activeShiftsToday) {
                                return $activeShiftsToday[$b]['quota'] <=> $activeShiftsToday[$a]['quota'];
                            });
                            $assignedCount = 0;
                            foreach ($sortedShiftIndices as $scIdx) {
                                if ($assignedCount < $ftReadyTodayCount) {
                                    $targetFTPerShift[$scIdx] = 1;
                                    $assignedCount++;
                                } else {
                                    $targetFTPerShift[$scIdx] = 0;
                                }
                            }
                        }
                    }

                    foreach ($configuredShifts as $shiftIdx => $shiftConfig) {
                        // Cek apakah hari ini termasuk hari aktif shift
                        if (! empty($shiftConfig['days']) && ! in_array($dayOfWeekIso, $shiftConfig['days']) && ! in_array((string) $dayOfWeekIso, $shiftConfig['days'])) {
                            continue;
                        }

                        $quotaNeeded = $shiftConfig['quota'];
                        $startTimePart = Carbon::parse($shiftConfig['start_time']);
                        $endTimePart = Carbon::parse($shiftConfig['end_time']);

                        $shiftStart = $currentDate->copy()->setTime(
                            $startTimePart->hour,
                            $startTimePart->minute,
                            $startTimePart->second
                        );

                        $shiftEnd = $currentDate->copy()->setTime(
                            $endTimePart->hour,
                            $endTimePart->minute,
                            $endTimePart->second
                        );

                        if ($shiftEnd->lte($shiftStart)) {
                            $shiftEnd->addDay();
                        }

                        $shiftDuration = $shiftStart->diffInMinutes($shiftEnd) / 60.0;

                        // Buffer dihitung sekali per shift, BUKAN di dalam loop tiap kandidat
                        $bufferedShiftStart = $shiftStart->copy()->subMinutes($bufferMinutes);
                        $bufferedShiftEnd = $shiftEnd->copy()->addMinutes($bufferMinutes);

                        // Buat Entitas Shift
                        $shift = Shift::create([
                            'company_id' => $companyId,
                            'department_id' => $deptId,
                            'template_id' => $shiftConfig['template_id'],
                            'title' => $shiftConfig['title'],
                            'start_time' => $shiftStart,
                            'end_time' => $shiftEnd,
                            'quota' => $quotaNeeded,
                            'is_open_shift' => false,
                            'requires_approval' => true,
                            'is_holiday' => false,
                            'status' => 'draft',
                        ]);

                        $totalShiftsCreated++;

                        // Evaluasi Kandidat Karyawan
                        $qualifiedCandidates = [];

                        foreach ($staffTrackers as $userId => $tracker) {
                            $user = $tracker['user'];

                            // HARD CONSTRAINT 1: Maksimal 1 Shift per Hari Kalender & RDO
                            $alreadyWorkedToday = isset($tracker['days_worked'][$dateStr]);
                            if ($alreadyWorkedToday) {
                                continue;
                            }

                            // RDO (Rest Day Off): Cek hari kerja berturut-turut jika disetel (> 0 dan < 7)
                            if ($maxWorkingDays > 0 && $maxWorkingDays < 7) {
                                $consecutiveDays = 0;
                                $checkDate = $currentDate->copy()->subDay();
                                while (isset($tracker['days_worked'][$checkDate->toDateString()])) {
                                    $consecutiveDays++;
                                    $checkDate->subDay();
                                }
                                if ($consecutiveDays >= $maxWorkingDays) {
                                    continue;
                                }
                            }

                            // HARD CONSTRAINT 2: Jadwal Halangan (Availability + Buffer)
                            $hasConflict = false;
                            $userAvails = $parsedAvailabilitiesByUser[$user->id] ?? [];
                            foreach ($userAvails as $avail) {
                                if ($avail['is_recurring']) {
                                    if ($avail['day_of_week'] === $dayOfWeekIso) {
                                        $availStart = $currentDate->copy()->setTime($avail['start_h'], $avail['start_m'], 0);
                                        $availEnd = $currentDate->copy()->setTime($avail['end_h'], $avail['end_m'], 0);
                                        if ($availEnd->lte($availStart)) {
                                            $availEnd->addDay();
                                        }

                                        if ($bufferedShiftStart->lt($availEnd) && $bufferedShiftEnd->gt($availStart)) {
                                            $hasConflict = true;
                                            break;
                                        }
                                    }
                                } else {
                                    if ($bufferedShiftStart->lt($avail['end_carbon']) && $bufferedShiftEnd->gt($avail['start_carbon'])) {
                                        $hasConflict = true;
                                        break;
                                    }
                                }
                            }

                            if ($hasConflict) {
                                continue;
                            }

                            // HARD CONSTRAINT 3: Anti-Double Booking
                            $isDoubleBooked = false;
                            foreach ($tracker['assigned_shifts'] as $assigned) {
                                if ($shiftStart->lt($assigned['end_time']) && $shiftEnd->gt($assigned['start_time'])) {
                                    $isDoubleBooked = true;
                                    break;
                                }
                            }

                            if ($isDoubleBooked) {
                                continue;
                            }

                            // HARD CONSTRAINT 4: Plafon Jam Kerja Berdasarkan Employment Type (maxRegularHours <= 0 = Fitur Pembatasan Nonaktif/Unlimited)
                            if ($maxRegularHours > 0) {
                                $prospectiveTotalHours = $tracker['total_hours'] + $shiftDuration;
                                if ($user->employment_type === 'full_time') {
                                    if ($absoluteMaxHours > 0 && $prospectiveTotalHours > $absoluteMaxHours) {
                                        continue;
                                    }
                                } elseif ($user->employment_type === 'part_time') {
                                    $partTimeCap = ($user->max_weekly_hours !== null && (float) $user->max_weekly_hours > 0)
                                        ? (float) $user->max_weekly_hours
                                        : (float) $absoluteMaxHours;

                                    if ($partTimeCap > 0 && $prospectiveTotalHours > $partTimeCap) {
                                        continue;
                                    }
                                } elseif ($user->employment_type === 'freelance') {
                                    $freelanceCap = ($user->max_weekly_hours !== null && (float) $user->max_weekly_hours > 0)
                                        ? (float) $user->max_weekly_hours
                                        : (float) $absoluteMaxHours;

                                    if ($freelanceCap > 0 && $prospectiveTotalHours > $freelanceCap) {
                                        continue;
                                    }
                                }
                            }

                            // SOFT CONSTRAINT: Jeda Istirahat Minimum (Rest Period 11 Jam)
                            $hasSoftRestWarning = false;
                            $restWarningReason = null;

                            foreach ($tracker['assigned_shifts'] as $assigned) {
                                if ($assigned['end_time']->lte($shiftStart)) {
                                    $restHours = $assigned['end_time']->diffInMinutes($shiftStart) / 60.0;
                                    if ($restHours < $minRestHours) {
                                        $hasSoftRestWarning = true;
                                        $restWarningReason = sprintf(
                                            'Jeda istirahat hanya %.1f jam (kurang dari standar %d jam)',
                                            $restHours,
                                            $minRestHours
                                        );
                                        break;
                                    }
                                }
                                if ($shiftEnd->lte($assigned['start_time'])) {
                                    $restHours = $shiftEnd->diffInMinutes($assigned['start_time']) / 60.0;
                                    if ($restHours < $minRestHours) {
                                        $hasSoftRestWarning = true;
                                        $restWarningReason = sprintf(
                                            'Jeda istirahat ke shift berikutnya hanya %.1f jam (kurang dari %d jam)',
                                            $restHours,
                                            $minRestHours
                                        );
                                        break;
                                    }
                                }
                            }

                            $prospectiveTotalHours = $tracker['total_hours'] + $shiftDuration;
                            $isFT = ($user->employment_type === 'full_time');
                            $isOvertime = ($isFT && $maxRegularHours > 0 && $prospectiveTotalHours > $maxRegularHours);
                            $isScheduledOffDay = ! empty($scheduledOffDaysByUser[$user->id][$dayOfWeekIso]);

                            // Tentukan Tier Prioritas Cerdas:
                            // Tier 1: Staf Full-Time reguler aktif (tidak RDO & belum overtime) -> Prioritas Utama
                            // Tier 2: Staf Part-Time & Freelance reguler (dalam batas jam kerja aman)
                            // Tier 3: Staf Full-Time yang sedang dijadwalkan Libur Bergilir (RDO hari ini - hanya sebagai fallback)
                            // Tier 4: Staf Full-Time yang masuk jam lembur (Overtime)
                            if ($isFT) {
                                if ($isOvertime) {
                                    $tierScore = 4;
                                } elseif ($isScheduledOffDay) {
                                    $tierScore = 3;
                                } else {
                                    $tierScore = 1;
                                }
                            } else {
                                $tierScore = 2;
                            }

                            $qualifiedCandidates[] = [
                                'user_id' => $user->id,
                                'user' => $user,
                                'tier_score' => $tierScore,
                                'is_scheduled_off' => $isScheduledOffDay,
                                'total_hours' => $tracker['total_hours'],
                                'days_count' => count($tracker['days_worked']),
                                'has_soft_warning' => $hasSoftRestWarning,
                                'warning_reason' => $restWarningReason,
                            ];
                        }

                        // Fairness & Priority Heuristic Sort
                        usort($qualifiedCandidates, function ($a, $b) use ($dayOfWeekIso, $shiftConfig) {
                            // 1. Tier Score (FT reguler > PT/FL reguler > FT RDO cadangan > Overtime)
                            if ($a['tier_score'] !== $b['tier_score']) {
                                return $a['tier_score'] <=> $b['tier_score'];
                            }

                            // 2. Jeda istirahat minimum (prioritaskan staf yang cukup istirahat >= 11 jam)
                            if ($a['has_soft_warning'] !== $b['has_soft_warning']) {
                                return $a['has_soft_warning'] ? 1 : -1;
                            }

                            // 3. Keseimbangan jam kerja (prioritaskan staf dengan jam kerja lebih sedikit)
                            if (abs($a['total_hours'] - $b['total_hours']) > 0.01) {
                                return $a['total_hours'] <=> $b['total_hours'];
                            }

                            // 4. Keseimbangan hari kerja
                            if ($a['days_count'] !== $b['days_count']) {
                                return $a['days_count'] <=> $b['days_count'];
                            }

                            // 5. Rotasi shift dinamis agar staf berotasi adil antar shift
                            $templateId = (int) ($shiftConfig['template_id'] ?? 1);
                            $rotA = ($a['user_id'] * 7 + $dayOfWeekIso * 3 + $templateId) % 100;
                            $rotB = ($b['user_id'] * 7 + $dayOfWeekIso * 3 + $templateId) % 100;

                            return $rotA <=> $rotB;
                        });

                        // Alokasi Penugasan Berimbang (Mix FT & PT di Setiap Shift)
                        $targetFT = $targetFTPerShift[$shiftIdx] ?? null;
                        $assignedCandidates = [];

                        if ($targetFT !== null && $targetFT >= 0) {
                            $ftCandidates = [];
                            $otherCandidates = [];
                            $fallbackCandidates = [];

                            foreach ($qualifiedCandidates as $c) {
                                if ($c['tier_score'] === 1) {
                                    $ftCandidates[] = $c;
                                } elseif ($c['tier_score'] === 2) {
                                    $otherCandidates[] = $c;
                                } else {
                                    $fallbackCandidates[] = $c;
                                }
                            }

                            // 1. Ambil porsi FT sesuai target shift (misal 1 FT untuk Pagi, 2 FT untuk Sore, 1 FT untuk Malam)
                            $takeFTCount = min($targetFT, count($ftCandidates), $quotaNeeded);
                            for ($i = 0; $i < $takeFTCount; $i++) {
                                $assignedCandidates[] = $ftCandidates[$i];
                            }
                            $remainingFTCandidates = array_slice($ftCandidates, $takeFTCount);

                            // 2. Isi slot berikutnya dengan staf Part-Time / Freelance (Tier 2)
                            $slotsLeft = $quotaNeeded - count($assignedCandidates);
                            $takeOtherCount = min($slotsLeft, count($otherCandidates));
                            for ($i = 0; $i < $takeOtherCount; $i++) {
                                $assignedCandidates[] = $otherCandidates[$i];
                            }
                            $remainingOtherCandidates = array_slice($otherCandidates, $takeOtherCount);

                            // 3. Jika slot masih belum penuh (misal PT ada kuliah/halangan), isi dengan sisa FT reguler
                            $slotsLeft = $quotaNeeded - count($assignedCandidates);
                            if ($slotsLeft > 0 && ! empty($remainingFTCandidates)) {
                                $extraFT = array_slice($remainingFTCandidates, 0, $slotsLeft);
                                foreach ($extraFT as $ef) {
                                    $assignedCandidates[] = $ef;
                                }
                            }

                            // 4. Jika masih belum penuh, ambil fallback (Tier 3 RDO / Tier 4 Overtime)
                            $slotsLeft = $quotaNeeded - count($assignedCandidates);
                            if ($slotsLeft > 0 && ! empty($fallbackCandidates)) {
                                $extraFallback = array_slice($fallbackCandidates, 0, $slotsLeft);
                                foreach ($extraFallback as $efb) {
                                    $assignedCandidates[] = $efb;
                                }
                            }
                        } else {
                            $assignedCandidates = array_slice($qualifiedCandidates, 0, $quotaNeeded);
                        }
                        $now = now();

                        foreach ($assignedCandidates as $candidate) {
                            $user = $candidate['user'];
                            $userId = $user->id;
                            $currentHours = $staffTrackers[$userId]['total_hours'];
                            $newTotal = $currentHours + $shiftDuration;

                            $isOvertime = false;
                            $overtimeStatus = 'none';
                            $regularHours = 0.0;
                            $overtimeHours = 0.0;

                            if ($user->employment_type === 'full_time' && $maxRegularHours > 0) {
                                if ($newTotal > $maxRegularHours) {
                                    $isOvertime = true;
                                    $overtimeStatus = 'pending_approval';

                                    if ($currentHours >= $maxRegularHours) {
                                        $regularHours = 0.0;
                                        $overtimeHours = $shiftDuration;
                                    } else {
                                        $regularHours = $maxRegularHours - $currentHours;
                                        $overtimeHours = $shiftDuration - $regularHours;
                                    }
                                } else {
                                    $regularHours = $shiftDuration;
                                    $overtimeHours = 0.0;
                                }
                            } else {
                                $regularHours = $shiftDuration;
                                $overtimeHours = 0.0;
                            }

                            // Kumpulkan ke pending assignments untuk batch insert
                            $deptPendingAssignments[] = [
                                'company_id' => $companyId,
                                'shift_id' => $shift->id,
                                'user_id' => $userId,
                                'is_overtime' => $isOvertime,
                                'overtime_status' => $overtimeStatus,
                                'clock_in_time' => null,
                                'clock_out_time' => null,
                                'late_minutes' => 0,
                                'regular_hours' => round($regularHours, 2),
                                'overtime_hours' => round($overtimeHours, 2),
                                'status' => 'scheduled',
                                'is_auto_clockout' => false,
                                'manager_notes' => null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];

                            $totalAssignmentsCreated++;

                            $staffTrackers[$userId]['total_hours'] = $newTotal;
                            $staffTrackers[$userId]['days_worked'][$dateStr] = true;
                            $staffTrackers[$userId]['assigned_shifts'][] = [
                                'start_time' => $shiftStart->copy(),
                                'end_time' => $shiftEnd->copy(),
                            ];

                            if ($candidate['has_soft_warning']) {
                                $allSoftWarnings[] = [
                                    'user_id' => $user->id,
                                    'user_name' => $user->name,
                                    'shift_id' => $shift->id,
                                    'shift_title' => $shift->title,
                                    'date' => $dateStr,
                                    'reason' => $candidate['warning_reason'],
                                ];
                            }
                        }

                        // Unfilled Slots Defisit
                        $assignedCount = count($assignedCandidates);
                        if ($assignedCount < $quotaNeeded) {
                            $allUnfilledSlots[] = [
                                'shift_id' => $shift->id,
                                'shift_title' => $shift->title,
                                'date' => $dateStr,
                                'start_time' => $shift->start_time->format('H:i'),
                                'end_time' => $shift->end_time->format('H:i'),
                                'needed_quota' => $quotaNeeded,
                                'assigned_quota' => $assignedCount,
                                'deficit' => $quotaNeeded - $assignedCount,
                            ];
                        }
                    }

                    $currentDate->addDay();
                }

                // Batch Insert Seluruh ShiftAssignment Departemen Ini dalam 1 Query Tunggal (Eliminasi ratusan INSERT)
                if (! empty($deptPendingAssignments)) {
                    foreach (array_chunk($deptPendingAssignments, 250) as $chunk) {
                        ShiftAssignment::insert($chunk);
                    }
                }
            }

            return [
                'shifts_created' => $totalShiftsCreated,
                'assignments_created' => $totalAssignmentsCreated,
                'soft_warnings' => $allSoftWarnings,
                'unfilled_slots' => $allUnfilledSlots,
                'message' => count($departmentIds) > 1
                    ? sprintf(
                        'Berhasil menyusun %d shift kerja dan menugaskan %d personel secara otomatis pada %d departemen.',
                        $totalShiftsCreated,
                        $totalAssignmentsCreated,
                        count($departmentIds)
                    )
                    : sprintf(
                        'Berhasil menyusun %d shift kerja dan menugaskan %d personel secara otomatis.',
                        $totalShiftsCreated,
                        $totalAssignmentsCreated
                    ),
            ];
        });
    }

    /**
     * Publish all draft shifts in the specified range and department(s).
     */
    public function publishWeeklyRoster(
        int $companyId,
        int|array $departmentId,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        bool $overwritePublished = false
    ): int {
        $departmentIds = is_array($departmentId) ? $departmentId : [$departmentId];

        return DB::transaction(function () use ($companyId, $departmentIds, $startDate, $endDate, $overwritePublished) {
            $rangeStart = $startDate->copy()->startOfDay();
            $rangeEnd = $endDate->copy()->endOfDay();

            if ($overwritePublished) {
                $oldPublishedIds = Shift::where('company_id', $companyId)
                    ->whereIn('department_id', $departmentIds)
                    ->where('status', 'published')
                    ->whereBetween('start_time', [$rangeStart, $rangeEnd])
                    ->pluck('id');

                if ($oldPublishedIds->isNotEmpty()) {
                    $oldPublishedIds->chunk(500)->each(function ($chunk) {
                        ShiftAssignment::whereIn('shift_id', $chunk)->delete();
                        Shift::whereIn('id', $chunk)->delete();
                    });
                }
            }

            return Shift::where('company_id', $companyId)
                ->whereIn('department_id', $departmentIds)
                ->where('status', 'draft')
                ->whereBetween('start_time', [$rangeStart, $rangeEnd])
                ->update(['status' => 'published']);
        });
    }

    /**
     * Clear all draft shifts in the specified range and department(s).
     */
    public function clearWeeklyDrafts(
        int $companyId,
        int|array $departmentId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): int {
        $departmentIds = is_array($departmentId) ? $departmentId : [$departmentId];

        return DB::transaction(function () use ($companyId, $departmentIds, $startDate, $endDate) {
            $draftShiftIds = Shift::where('company_id', $companyId)
                ->whereIn('department_id', $departmentIds)
                ->where('status', 'draft')
                ->whereBetween('start_time', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->pluck('id');

            if ($draftShiftIds->isEmpty()) {
                return 0;
            }

            ShiftAssignment::whereIn('shift_id', $draftShiftIds)->delete();

            return Shift::whereIn('id', $draftShiftIds)->delete();
        });
    }
}
