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
            $absoluteMaxHours = $maxRegularHours + $maxOvertimeHours;
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
                    $sStr = substr($avail->start_time, 0, 8);
                    $eStr = substr($avail->end_time, 0, 8);
                    $sParts = explode(':', $sStr);
                    $eParts = explode(':', $eStr);

                    $parsedAvailabilitiesByUser[$avail->user_id][] = [
                        'is_recurring' => (bool) $avail->is_recurring,
                        'day_of_week' => (int) $avail->day_of_week,
                        'start_h' => (int) ($sParts[0] ?? 0),
                        'start_m' => (int) ($sParts[1] ?? 0),
                        'end_h' => (int) ($eParts[0] ?? 0),
                        'end_m' => (int) ($eParts[1] ?? 0),
                        'start_carbon' => $avail->is_recurring ? null : Carbon::parse($avail->start_time),
                        'end_carbon' => $avail->is_recurring ? null : Carbon::parse($avail->end_time),
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

                $deptPendingAssignments = [];

                // Loop Tanggal Dinamis (Mulai s.d. Selesai)
                $currentDate = $rangeStart->copy();
                while ($currentDate->lte($rangeEnd)) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $dayOfWeekIso = $currentDate->dayOfWeekIso; // 1 = Senin, 7 = Minggu

                    foreach ($configuredShifts as $shiftConfig) {
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

                            // HARD CONSTRAINT 1: RDO (Maksimal Hari Kerja Berturut-turut)
                            $alreadyWorkedToday = isset($tracker['days_worked'][$dateStr]);
                            if (! $alreadyWorkedToday && count($tracker['days_worked']) >= $maxWorkingDays) {
                                continue;
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

                            // HARD CONSTRAINT 4: Plafon Jam Kerja Berdasarkan Employment Type
                            $prospectiveTotalHours = $tracker['total_hours'] + $shiftDuration;
                            if ($user->employment_type === 'full_time') {
                                if ($prospectiveTotalHours > $absoluteMaxHours) {
                                    continue;
                                }
                            } elseif ($user->employment_type === 'part_time') {
                                $partTimeCap = (float) ($user->max_weekly_hours ?? 24);
                                if ($prospectiveTotalHours > $partTimeCap) {
                                    continue;
                                }
                            } elseif ($user->employment_type === 'freelance') {
                                $freelanceCap = (float) ($user->max_weekly_hours ?? 40);
                                if ($prospectiveTotalHours > $freelanceCap) {
                                    continue;
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

                            $typePriority = match ($user->employment_type) {
                                'full_time' => 1,
                                'part_time' => 2,
                                'freelance' => 3,
                                default => 2,
                            };

                            $qualifiedCandidates[] = [
                                'user_id' => $user->id,
                                'user' => $user,
                                'type_priority' => $typePriority,
                                'total_hours' => $tracker['total_hours'],
                                'days_count' => count($tracker['days_worked']),
                                'has_soft_warning' => $hasSoftRestWarning,
                                'warning_reason' => $restWarningReason,
                            ];
                        }

                        // Fairness & Priority Heuristic Sort
                        usort($qualifiedCandidates, function ($a, $b) {
                            if ($a['type_priority'] !== $b['type_priority']) {
                                return $a['type_priority'] <=> $b['type_priority'];
                            }
                            if (abs($a['total_hours'] - $b['total_hours']) > 0.01) {
                                return $a['total_hours'] <=> $b['total_hours'];
                            }

                            return $a['days_count'] <=> $b['days_count'];
                        });

                        // Alokasi Penugasan
                        $assignedCandidates = array_slice($qualifiedCandidates, 0, $quotaNeeded);
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

                            if ($user->employment_type === 'full_time') {
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
