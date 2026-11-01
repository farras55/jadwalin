<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\ShiftAssignment;
use App\Models\User;
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
        $inProgress = ShiftAssignment::with(['shift.department', 'shift.company'])
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->latest('clock_in_time')
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        // 2. Shift hari ini yang belum selesai (clock_out_time null)
        $upcomingToday = ShiftAssignment::with(['shift.department', 'shift.company'])
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
        return ShiftAssignment::with(['shift.department', 'shift.company'])
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
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        // 1. Ambil shift assignment aktif hari ini
        $todayShift = $this->getActiveAssignment($user);

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
            return ! empty($a->clock_in_time) || in_array($a->status, ['present', 'late', 'completed']);
        });
        $presentShifts = $presentAssignments->count();

        // Persentase kehadiran
        $attendancePercentage = $totalShifts > 0
            ? round(($presentShifts / $totalShifts) * 100, 1)
            : 0;

        // Hari tepat waktu (hadir dan tanpa keterlambatan)
        $onTimeDays = $monthAssignments->filter(function ($a) {
            $hasAttended = ! empty($a->clock_in_time) || in_array($a->status, ['present', 'late', 'completed']);
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
            if (! empty($a->clock_in_time) && ! empty($a->clock_out_time)) {
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

        // ==============================================================================
        // 3. Lokasi Acuan Presensi Cabang/Outlet Tersinkronisasi Superadmin
        // ==============================================================================
        $location = $this->resolveAttendanceLocation($user, $todayShift);
        $branch = $location['department'];
        $officeLat = $location['latitude'];
        $officeLng = $location['longitude'];
        $radius = $location['radius'];
        $officeLatitude = $officeLat;
        $officeLongitude = $officeLng;
        $attendanceRadius = $radius;
        $isLocationConfigured = $location['is_configured'];
        $officeName = $location['name'];
        $officeAddress = $location['address'];

        $outlet = [
            'id' => $location['id'],
            'name' => $officeName,
            'address' => $officeAddress,
            'latitude' => $officeLat,
            'longitude' => $officeLng,
            'radius' => $radius,
            'is_configured' => $isLocationConfigured,
        ];

        $companySetting = CompanySetting::where('company_id', $user->company_id)->first();
        $toleranceMinutes = (int) ($companySetting?->grace_period_minutes ?? 5);

        // 4. Tentukan state presensi hari ini (sepenuhnya dari backend)
        $isClockedIn = $todayShift && ! empty($todayShift->clock_in_time);
        $isClockedOut = $todayShift && ! empty($todayShift->clock_out_time);

        // Apakah jam shift sudah lewat batas akhir?
        $shiftEnded = false;
        if ($todayShift && $todayShift->shift) {
            $shiftEndTime = Carbon::parse($todayShift->shift->end_time);
            $shiftEnded = Carbon::now()->greaterThan($shiftEndTime);
        }

        // State: 'idle' | 'working' | 'completed' | 'no_shift'
        if (! $todayShift) {
            $attendanceState = 'no_shift';
        } elseif ($isClockedOut) {
            $attendanceState = 'completed';
        } elseif ($isClockedIn) {
            $attendanceState = 'working';
        } else {
            $attendanceState = 'idle';
        }

        // 5. Format keterlambatan hari ini → Jam & Menit
        $todayLateMinutes = (int) ($todayShift->late_minutes ?? 0);
        $todayLateHours = (int) floor($todayLateMinutes / 60);
        $todayLateRemMin = $todayLateMinutes % 60;
        $todayLateFormatted = $todayLateHours > 0
            ? "{$todayLateHours} Jam ".str_pad($todayLateRemMin, 2, '0', STR_PAD_LEFT).' Menit'
            : "{$todayLateMinutes} Menit";

        // 6. Riwayat 7 Hari Terakhir
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
            'branch',
            'officeLat',
            'officeLng',
            'radius',
            'officeLatitude',
            'officeLongitude',
            'attendanceRadius',
            'isLocationConfigured',
            'attendanceState',
            'isClockedIn',
            'isClockedOut',
            'shiftEnded',
            'todayLateMinutes',
            'todayLateFormatted',
            'toleranceMinutes'
        ));
    }

    /**
     * Endpoint API: GET /api/employee/attendance-location
     * Mengembalikan titik koordinat dan geofencing penugasan karyawan yang sedang login.
     */
    public function location(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $todayShift = $this->getActiveAssignment($user);
        $loc = $this->resolveAttendanceLocation($user, $todayShift);

        return response()->json([
            'id' => $loc['id'],
            'name' => $loc['name'],
            'address' => $loc['address'],
            'latitude' => $loc['latitude'],
            'longitude' => $loc['longitude'],
            'radius_meters' => $loc['radius'],
            'is_configured' => $loc['is_configured'],
            'updated_at' => $loc['updated_at'],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Resolusi single source of truth untuk lokasi cabang/outlet presensi employee.
     * Mengutamakan: Departemen Shift Hari Ini -> Departemen Penugasan Karyawan -> Cabang Pertama Perusahaan.
     */
    public function resolveAttendanceLocation(User $user, ?ShiftAssignment $todayShift = null): array
    {
        $targetDepartment = $todayShift?->shift?->department
            ?? $user->department
            ?? $user->branch
            ?? Department::where('company_id', $user->company_id)->orderBy('id')->first();

        $company = $user->company;
        $companySetting = CompanySetting::where('company_id', $user->company_id)->first();

        $latitude = $targetDepartment?->latitude !== null
            ? (float) $targetDepartment->latitude
            : ($companySetting?->office_latitude !== null
                ? (float) $companySetting->office_latitude
                : ($company?->office_latitude !== null ? (float) $company->office_latitude : null));

        $longitude = $targetDepartment?->longitude !== null
            ? (float) $targetDepartment->longitude
            : ($companySetting?->office_longitude !== null
                ? (float) $companySetting->office_longitude
                : ($company?->office_longitude !== null ? (float) $company->office_longitude : null));

        $radius = $targetDepartment?->radius_meters !== null
            ? (int) $targetDepartment->radius_meters
            : ($targetDepartment?->radius !== null
                ? (int) $targetDepartment->radius
                : ($companySetting?->attendance_radius_meters !== null
                    ? (int) $companySetting->attendance_radius_meters
                    : ($company?->attendance_radius_meters !== null ? (int) $company->attendance_radius_meters : (int) config('jadwalin.attendance.default_radius_meters', 100))));

        $name = $targetDepartment?->name ?? $company?->name ?? config('jadwalin.attendance.default_name', 'Kantor Utama');
        $address = $targetDepartment?->address ?? $company?->address ?? config('jadwalin.attendance.default_address', '-');
        $isConfigured = $latitude !== null && $longitude !== null;

        return [
            'id' => $targetDepartment?->id ?? 0,
            'department' => $targetDepartment,
            'name' => $name,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radius,
            'is_configured' => $isConfigured,
            'updated_at' => $targetDepartment?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    /**
     * Hitung jarak dua titik GPS menggunakan Haversine Formula (dalam meter).
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return (int) round($angle * $earthRadius);
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $timezone = $user->company?->timezone ?? 'Asia/Jakarta';
        $now = Carbon::now($timezone);

        $assignment = $this->getActiveAssignment($user);

        if (! $assignment || ! $assignment->shift) {
            return back()->with('error', 'Tidak ada shift yang ditugaskan untuk Anda hari ini.');
        }

        if ($assignment->clock_in_time) {
            return back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
        }

        // =========================================================================
        // VALIDASI STATUS LOKASI CABANG / OUTLET DARI DATABASE
        // =========================================================================
        $location = $this->resolveAttendanceLocation($user, $assignment);

        if (! $location['is_configured']) {
            return back()->with('error', 'Lokasi cabang belum diatur oleh admin. Presensi tidak dapat dilakukan.');
        }

        $startTime = Carbon::parse($assignment->shift->start_time, $timezone);
        $endTime = Carbon::parse($assignment->shift->end_time, $timezone);

        // Batasi clock-in paling cepat 60 menit sebelum shift dimulai
        if ($now->lt($startTime->copy()->subMinutes(60))) {
            return back()->with('error', 'Clock In hanya dapat dilakukan paling cepat 60 menit sebelum shift dimulai.');
        }

        // Cegah Clock-In jika jam shift telah berakhir
        if ($now->greaterThan($endTime)) {
            return back()->with('error', 'Shift telah berakhir. Presensi tidak dapat dilakukan.');
        }

        $settings = CompanySetting::where('company_id', $user->company_id)->first();
        $gracePeriod = ($settings && $settings->grace_period_minutes !== null)
            ? (int) $settings->grace_period_minutes
            : 0;

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
        $timezone = $user->company?->timezone ?? 'Asia/Jakarta';
        $now = Carbon::now($timezone);

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

        // Perhitungan jam kerja berbasis waktu server
        $totalMinutes = Carbon::parse($assignment->clock_in_time)->diffInMinutes($now);
        $totalHours = round($totalMinutes / 60, 2);

        $shiftStart = Carbon::parse($assignment->shift->start_time, $timezone);
        $shiftEnd = Carbon::parse($assignment->shift->end_time, $timezone);
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
