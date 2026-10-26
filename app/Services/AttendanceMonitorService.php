<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\CompanySetting;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;

class AttendanceMonitorService
{
    /**
     * Mengambil data presensi departemen hari ini untuk manager,
     * termasuk metrik agregat dan daftar baris kehadiran staf.
     *
     * Logika klasifikasi status konsisten antara index dan realtime feed:
     * - on_time: sudah clock-in dan clock_in <= shift_start + toleransi
     * - late: sudah clock-in tapi melewati toleransi
     * - on_leave: berstatus cuti/izin (tidak clock-in dan tidak dihitung absent)
     * - not_started: belum clock-in DAN waktu sekarang belum melewati shift_start (+ toleransi)
     * - absent: belum clock-in DAN shift sudah dimulai/lewat batas toleransi
     */
    public function getTodayDepartmentAttendance(User $manager, ?Carbon $currentTime = null): array
    {
        // 1. Tentukan timezone dan waktu referensi (menghargai Carbon::setTestNow jika diset di test)
        $timezone = $manager->company?->timezone ?: config('app.timezone', 'Asia/Jakarta');
        $now = $currentTime ? $currentTime->copy() : now();
        $today = Carbon::today();

        // 2. Ambil toleransi keterlambatan dari company_settings (grace_period_minutes)
        // Default 0 menit bila setting belum diset / record tidak ditemukan
        $companySetting = CompanySetting::where('company_id', $manager->company_id)->first();
        $gracePeriod = (int) ($companySetting?->grace_period_minutes ?? 0);

        // 3. Query shift_assignments untuk HARI INI
        // Memfilter staf di departemen manajer (kecuali jika is_global_manager)
        // Eager load user, shift, user.position, user.department untuk menghindari N+1 query.
        $assignments = ShiftAssignment::query()
            ->where('shift_assignments.company_id', $manager->company_id)
            ->whereHas('user', function ($q) use ($manager) {
                $q->where('status', '!=', 'resigned');
                if (! $manager->is_global_manager) {
                    $q->where('department_id', $manager->department_id);
                }
            })
            ->whereHas('shift', function ($q) use ($today) {
                // Shift hari ini: start_time berada pada tanggal hari ini.
                // Mendukung shift lintas hari (overnight) yang dimulai pada hari ini dan berakhir keesokan harinya.
                $q->whereDate('start_time', $today);
            })
            ->with([
                'user' => function ($q) {
                    $q->select('id', 'company_id', 'department_id', 'position_id', 'name', 'role', 'status', 'profile_photo');
                },
                'user.position:id,name',
                'user.department:id,name',
                'shift:id,company_id,department_id,title,start_time,end_time',
            ])
            ->get();

        // 4. Periksa data izin/cuti staf (tabel availabilities dengan status approved untuk hari ini)
        // Staf juga dianggap on_leave jika user->status == 'on_leave'
        $onLeaveUserIds = Availability::where('company_id', $manager->company_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($now, $today) {
                $q->where(function ($sub) use ($today) {
                    $sub->where('is_recurring', false)
                        ->whereDate('start_time', '<=', $today)
                        ->whereDate('end_time', '>=', $today);
                })->orWhere(function ($sub) use ($now) {
                    $sub->where('is_recurring', true)
                        ->where('day_of_week', $now->dayOfWeekIso);
                });
            })
            ->pluck('user_id')
            ->flip()
            ->all();

        // 5. Inisialisasi metrik
        // Kartu metrik:
        // - scheduled: Total dijadwalkan hari ini
        // - on_time: Hadir tepat waktu
        // - late: Hadir terlambat
        // - absent: Belum hadir setelah jam shift dimulai + toleransi
        // - not_started: Shift belum dimulai (belum hadir namun belum terlambat)
        // - not_present: Total belum hadir (absent + not_started)
        // - on_leave: Sedang izin / cuti
        $metrics = [
            'scheduled' => 0,
            'total_scheduled' => 0,
            'on_time' => 0,
            'late' => 0,
            'absent' => 0,
            'not_started' => 0,
            'not_present' => 0,
            'on_leave' => 0,
        ];

        // 6. Urutkan assignments berdasarkan jam mulai shift, lalu nama staf
        $sortedAssignments = $assignments->sortBy([
            fn ($a, $b) => ($a->shift?->start_time <=> $b->shift?->start_time),
            fn ($a, $b) => strcmp($a->user?->name ?? '', $b->user?->name ?? ''),
        ]);

        $rows = [];

        foreach ($sortedAssignments as $assignment) {
            $shift = $assignment->shift;
            $user = $assignment->user;

            if (! $shift || ! $user) {
                continue;
            }

            $shiftStart = Carbon::parse($shift->start_time);
            $shiftEnd = Carbon::parse($shift->end_time);
            $clockIn = $assignment->clock_in_time ? Carbon::parse($assignment->clock_in_time) : null;
            $clockOut = $assignment->clock_out_time ? Carbon::parse($assignment->clock_out_time) : null;

            // Batas toleransi keterlambatan
            $toleranceThreshold = $shiftStart->copy()->addMinutes($gracePeriod);

            // Cek apakah staf berhalangan / cuti
            $isOnLeave = ($user->status === 'on_leave') || isset($onLeaveUserIds[$user->id]);

            // Klasifikasi status
            if ($clockIn) {
                if ($clockIn->lte($toleranceThreshold)) {
                    $status = 'on_time';
                    $statusLabel = 'Tepat Waktu';
                } else {
                    $status = 'late';
                    $statusLabel = 'Terlambat';
                }
            } elseif ($isOnLeave) {
                $status = 'on_leave';
                $statusLabel = 'Izin / Cuti';
            } else {
                if ($now->lte($toleranceThreshold)) {
                    $status = 'not_started';
                    $statusLabel = 'Belum Mulai';
                } else {
                    $status = 'absent';
                    $statusLabel = 'Belum Hadir';
                }
            }

            // Agregasi metrik
            $metrics['scheduled']++;
            if (isset($metrics[$status])) {
                $metrics[$status]++;
            }

            // Inisial nama staf
            $initials = collect(explode(' ', $user->name))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->join('');

            // Cek shift lintas hari (overnight)
            $isOvernight = ($shiftEnd->toDateString() !== $shiftStart->toDateString());

            $rows[] = [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'position' => $user->position?->name ?? 'Staf Operasional',
                'department' => $user->department?->name ?? '-',
                'initials' => strtoupper($initials ?: 'ST'),
                'profile_photo_url' => $user->profile_photo ? asset('storage/'.$user->profile_photo) : null,
                'shift' => $shift->title,
                'shift_start' => $shiftStart->format('H:i'),
                'shift_end' => $shiftEnd->format('H:i'),
                'is_overnight' => $isOvernight,
                'clock_in' => $clockIn ? $clockIn->format('H:i') : null,
                'clock_in_full' => $clockIn ? $clockIn->format('H:i:s') : null,
                'clock_out' => $clockOut ? $clockOut->format('H:i') : null,
                'late_minutes' => (int) $assignment->late_minutes,
                'status' => $status,
                'status_label' => $statusLabel,
            ];
        }

        // Hitung total terjadwal dan belum hadir
        $metrics['total_scheduled'] = $metrics['scheduled'];
        $metrics['not_present'] = $metrics['absent'] + $metrics['not_started'];

        return [
            'server_time' => $now->format('H:i:s'),
            'server_date' => $now->translatedFormat('l, d F Y'),
            'timezone' => $timezone,
            'grace_period' => $gracePeriod,
            'metrics' => $metrics,
            'rows' => $rows,
            'raw_assignments' => $sortedAssignments,
        ];
    }
}
