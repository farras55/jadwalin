<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Menemukan penugasan shift aktif untuk karyawan:
     * 1. Shift yang sedang berjalan (sudah clock-in, belum clock-out), mendukung shift lintas hari (overnight).
     * 2. Shift hari ini yang belum clock-out (mendukung shift ganda / split shift berurutan).
     * 3. Fallback ke penugasan hari ini yang paling baru (untuk tampilan status).
     */
    protected function getActiveAssignment($user)
    {
        $today = Carbon::today();

        // 1. Shift in-progress (mendukung overnight shift kemarin malam)
        $inProgress = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        // 2. Shift hari ini yang belum selesai (clock_out_time null)
        $upcomingToday = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereNull('clock_out_time')
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', $today);
            })
            ->orderBy(
                ShiftAssignment::select('start_time')
                    ->from('shifts')
                    ->whereColumn('shifts.id', 'shift_assignments.shift_id')
                    ->limit(1),
                'asc'
            )
            ->first();

        if ($upcomingToday) {
            return $upcomingToday;
        }

        // 3. Fallback: penugasan shift hari ini yang terakhir
        return ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', $today);
            })
            ->latest('id')
            ->first();
    }

    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Find today's active shift assignment
        $todayShift = $this->getActiveAssignment($user);

        // 7 days history
        $recentHistory = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', '<=', $today)
                    ->whereDate('start_time', '>=', $today->copy()->subDays(7));
            })
            ->orderBy(
                ShiftAssignment::select('start_time')
                    ->from('shifts')
                    ->whereColumn('shifts.id', 'shift_assignments.shift_id')
                    ->limit(1),
                'desc'
            )
            ->get();

        return view('employee.attendance.index', compact('todayShift', 'recentHistory'));
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $now = now();

        $assignment = $this->getActiveAssignment($user);

        if (! $assignment || ! $assignment->shift) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        if ($assignment->clock_in_time) {
            return back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
        }

        $startTime = Carbon::parse($assignment->shift->start_time);

        // Batasi clock-in paling cepat 60 menit sebelum shift dimulai
        if ($now->lt($startTime->copy()->subMinutes(60))) {
            return back()->with('error', 'Clock In hanya dapat dilakukan paling cepat 60 menit sebelum shift dimulai.');
        }

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $gracePeriod = $settings->grace_period_minutes ?? 0;

        $lateMinutes = 0;
        $status = 'present';

        if ($now->greaterThan($startTime->copy()->addMinutes($gracePeriod))) {
            $status = 'late';
            $lateMinutes = $startTime->diffInMinutes($now);
        }

        $assignment->update([
            'clock_in_time' => $now,
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ]);

        return back()->with('success', 'Clock In berhasil disimpan.');
    }

    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $now = now();

        $assignment = $this->getActiveAssignment($user);

        if (! $assignment || ! $assignment->shift) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        if (! $assignment->clock_in_time) {
            return back()->with('error', 'Anda belum melakukan clock-in.');
        }

        if ($assignment->clock_out_time) {
            return back()->with('error', 'Anda sudah melakukan clock-out hari ini.');
        }

        $totalMinutes = Carbon::parse($assignment->clock_in_time)->diffInMinutes($now);
        $totalHours = round($totalMinutes / 60, 2);

        $shiftStart = Carbon::parse($assignment->shift->start_time);
        $shiftEnd = Carbon::parse($assignment->shift->end_time);
        $scheduledHours = round($shiftStart->diffInMinutes($shiftEnd) / 60, 2);

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        // Plafon harian standar (jika max_regular_hours di database adalah batas mingguan misal 40, gunakan 8 jam)
        $dailyRegularCap = ($settings && (int) $settings->max_regular_hours <= 12)
            ? (int) $settings->max_regular_hours
            : 8;

        // Batas jam reguler adalah yang lebih kecil antara durasi jadwal shift dengan plafon harian
        $regularThreshold = min($scheduledHours, $dailyRegularCap);

        if ($assignment->is_overtime || optional($assignment->shift)->is_open_shift) {
            // Skenario A: Shift lembur sukarela / Open Shift di hari libur -> 100% jam lembur
            $regularHours = 0;
            $overtimeHours = $totalHours;
        } else {
            // Skenario B: Shift reguler dengan penambahan jam di luar jadwal
            $regularHours = min($totalHours, $regularThreshold);
            $overtimeHours = max(0, round($totalHours - $regularThreshold, 2));
        }

        $assignment->update([
            'clock_out_time' => $now,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
        ]);

        return back()->with('success', 'Clock Out berhasil disimpan.');
    }
}
