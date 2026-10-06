<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Find today's shift assignment
        $todayShift = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', $today);
            })
            ->first();

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
        $today = Carbon::today();
        $now = now();

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', $today);
            })
            ->first();

        if (!$assignment) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        if ($assignment->clock_in_time) {
            return back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
        }

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $gracePeriod = $settings->grace_period_minutes ?? 0;

        $startTime = Carbon::parse($assignment->shift->start_time);
        
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
        $today = Carbon::today();
        $now = now();

        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($today) {
                $query->whereDate('start_time', $today);
            })
            ->first();

        if (!$assignment) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        if (!$assignment->clock_in_time) {
            return back()->with('error', 'Anda belum melakukan clock-in.');
        }

        if ($assignment->clock_out_time) {
            return back()->with('error', 'Anda sudah melakukan clock-out hari ini.');
        }

        $totalMinutes = Carbon::parse($assignment->clock_in_time)->diffInMinutes($now);
        $totalHours = $totalMinutes / 60;

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $maxRegularHours = $settings->max_regular_hours ?? 8;

        $regularHours = min($totalHours, $maxRegularHours);
        $overtimeHours = max(0, $totalHours - $maxRegularHours);

        $assignment->update([
            'clock_out_time' => $now,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
        ]);

        return back()->with('success', 'Clock Out berhasil disimpan.');
    }
}
