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
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        // 1. Ambil shift assignment aktif hari ini — selalu fresh dari DB, tidak pernah cache.
        //    Fase 1: Cari assignment yang sudah di-clock-in hari ini (prioritas tertinggi,
        //            karena user sudah hadir — data presensinya harus dipertahankan).
        $todayShift = ShiftAssignment::with(['shift.department', 'shift.company'])
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereDate('clock_in_time', $today)
            ->latest('clock_in_time')
            ->first();

        //    Fase 2: Jika belum ada clock-in hari ini, ambil assignment terbaru yang
        //            shift-nya dijadwalkan mulai hari ini (mencerminkan perubahan manager).
        if (!$todayShift) {
            $todayShift = ShiftAssignment::with(['shift.department', 'shift.company'])
                ->where('user_id', $user->id)
                ->whereHas('shift', function ($q) use ($today) {
                    $q->whereDate('start_time', $today);
                })
                ->whereNull('clock_in_time')     // Belum clock-in (belum ada duplikat dengan Fase 1)
                ->latest('updated_at')           // Perubahan manager tercermin via updated_at terbaru
                ->first();
        }

        // 2. Kalkulasi Top Summary Cards (Kehadiran Bulan Berjalan)
        $monthAssignments = ShiftAssignment::with(['shift.department'])
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_time', [$startOfMonth, $endOfMonth]);
            })
            ->get();

        $totalShifts = $monthAssignments->count();

        // Presensi hadir (sudah clock-in atau berstatus hadir/terlambat/selesai)
        $presentAssignments = $monthAssignments->filter(function ($a) {
            return !empty($a->clock_in_time) || in_array($a->status, ['present', 'late', 'completed']);
        });
        $presentShifts = $presentAssignments->count();

        // Persentase kehadiran
        $attendancePercentage = $totalShifts > 0 
            ? round(($presentShifts / $totalShifts) * 100, 1) 
            : 0;

        // Hari tepat waktu (hadir dan tanpa keterlambatan)
        $onTimeDays = $monthAssignments->filter(function ($a) {
            $hasAttended = !empty($a->clock_in_time) || in_array($a->status, ['present', 'late', 'completed']);
            $isNotLate = ($a->late_minutes ?? 0) <= 0 && $a->status !== 'late';
            return $hasAttended && $isNotLate;
        })->count();

        $onTimeStatus = ($presentShifts > 0 && ($onTimeDays / $presentShifts) >= 0.8) 
            ? 'On-Track' 
            : ($presentShifts == 0 ? 'On-Track' : 'Perlu Evaluasi');

        // Total akumulasi jam kerja yang sudah diselesaikan
        $totalWorkHours = (float) $monthAssignments->reduce(function ($carry, $a) {
            $reg = (float) ($a->regular_hours ?? 0);
            $ovt = (float) ($a->overtime_hours ?? 0);
            if ($reg + $ovt > 0) {
                return $carry + $reg + $ovt;
            }
            if (!empty($a->clock_in_time) && !empty($a->clock_out_time)) {
                $diff = Carbon::parse($a->clock_in_time)->diffInMinutes(Carbon::parse($a->clock_out_time));
                return $carry + ($diff / 60);
            }
            return $carry;
        }, 0);

        $attendanceStats = [
            'attendance_percentage' => $attendancePercentage,
            'present_shifts' => $presentShifts,
            'total_shifts' => $totalShifts,
            'on_time_days' => $onTimeDays,
            'on_time_status' => $onTimeStatus,
            'total_work_hours' => round($totalWorkHours, 1),
        ];

        // 3. Lokasi Outlet Penugasan dari Jadwal Shift Aktif Hari Ini
        $department = $todayShift->shift->department ?? $user->department ?? null;
        $company = $user->company;

        $outlet = [
            'name'      => $department->name ?? 'Outlet Senopati',
            'address'   => $department->address ?? ($company->address ?? 'Jl. Suryo No. 12, Senopati, Kebayoran Baru, Jakarta Selatan'),
            'latitude'  => (float) ($department->latitude ?? -6.2383200),
            'longitude' => (float) ($department->longitude ?? 106.8122800),
            'radius'    => (int)   ($department->radius_meters ?? 50),
        ];

        // 5. Tentukan state presensi hari ini (sepenuhnya dari backend)
        $isClockedIn  = $todayShift && !empty($todayShift->clock_in_time);
        $isClockedOut = $todayShift && !empty($todayShift->clock_out_time);

        // Apakah jam shift sudah lewat batas akhir? (shift ended = tidak bisa clock-in lagi)
        $shiftEnded = false;
        if ($todayShift && $todayShift->shift) {
            $shiftEndTime = Carbon::parse($todayShift->shift->end_time);
            $shiftEnded   = Carbon::now()->greaterThan($shiftEndTime);
        }

        // State: 'idle' | 'working' | 'completed' | 'no_shift'
        if (!$todayShift) {
            $attendanceState = 'no_shift';
        } elseif ($isClockedOut) {
            $attendanceState = 'completed';
        } elseif ($isClockedIn) {
            $attendanceState = 'working';
        } else {
            $attendanceState = 'idle';
        }

        // 6. Format keterlambatan hari ini → Jam & Menit
        $todayLateMinutes = (int) ($todayShift->late_minutes ?? 0);
        $todayLateHours   = (int) floor($todayLateMinutes / 60);
        $todayLateRemMin  = $todayLateMinutes % 60;
        $todayLateFormatted = $todayLateHours > 0
            ? "{$todayLateHours} Jam " . str_pad($todayLateRemMin, 2, '0', STR_PAD_LEFT) . " Menit"
            : "{$todayLateMinutes} Menit";

        // 7. Riwayat 7 Hari Terakhir
        $recentHistory = ShiftAssignment::with(['shift.department'])
            ->where('user_id', $user->id)
            ->where(function ($q) use ($today) {
                $q->whereDate('clock_in_time', '<=', $today)
                  ->whereDate('clock_in_time', '>=', $today->copy()->subDays(7))
                  ->orWhereHas('shift', function ($sub) use ($today) {
                      $sub->whereDate('start_time', '<=', $today)
                          ->whereDate('start_time', '>=', $today->copy()->subDays(7));
                  });
            })
            ->orderBy(
                ShiftAssignment::select('start_time')
                    ->from('shifts')
                    ->whereColumn('shifts.id', 'shift_assignments.shift_id')
                    ->limit(1),
                'desc'
            )
            ->get();

        return view('employee.attendance.index', compact(
            'todayShift',
            'recentHistory',
            'attendanceStats',
            'outlet',
            'attendanceState',
            'isClockedIn',
            'isClockedOut',
            'shiftEnded',
            'todayLateMinutes',
            'todayLateFormatted'
        ));
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        $now = Carbon::now();

        // Cari assignment yang shift-nya mulai hari ini dan belum clock-in
        // (query strict — hanya berdasarkan shift.start_time, bukan OR clock_in_time)
        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereNull('clock_in_time')
            ->whereHas('shift', function ($q) use ($today) {
                $q->whereDate('start_time', $today);
            })
            ->latest('updated_at')
            ->first();

        // Fallback: mungkin user sudah punya clock_in hari ini (double-submit)
        if (!$assignment) {
            $alreadyClockedIn = ShiftAssignment::where('user_id', $user->id)
                ->whereNotNull('clock_in_time')
                ->whereDate('clock_in_time', $today)
                ->exists();
            if ($alreadyClockedIn) {
                return back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
            }
        }

        if (!$assignment) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        // Guard ini redundan (query sudah whereNull), tapi dipertahankan sebagai safety net
        // untuk mencegah race condition concurrent requests
        if ($assignment && $assignment->clock_in_time) {
            return back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
        }

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $gracePeriod = ($settings && $settings->grace_period_minutes !== null) 
            ? (int) $settings->grace_period_minutes 
            : 5;

        $startTime = Carbon::parse($assignment->shift->start_time);
        $toleranceLimit = $startTime->copy()->addMinutes($gracePeriod);
        
        $lateMinutes = 0;
        $status = 'present';

        if ($now->greaterThan($toleranceLimit)) {
            $status = 'late';
            $lateMinutes = (int) $startTime->diffInMinutes($now);
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
        $now = Carbon::now();

        // Clock-out: cari assignment yang sudah clock-in hari ini (satu-satunya sumber kebenaran)
        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereDate('clock_in_time', $today)
            ->latest('clock_in_time')
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
        $totalHours = round($totalMinutes / 60, 2);

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $maxRegularHours = $settings->max_regular_hours ?? 8;

        $regularHours = min($totalHours, $maxRegularHours);
        $overtimeHours = max(0, round($totalHours - $maxRegularHours, 2));

        $assignment->update([
            'clock_out_time' => $now,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
        ]);

        return back()->with('success', 'Clock Out berhasil disimpan.');
    }
}
