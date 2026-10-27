<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\AttendanceMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceMonitorController extends Controller
{
    /**
     * Service monitoring presensi untuk query dan kalkulasi metrik.
     */
    protected AttendanceMonitorService $attendanceService;

    public function __construct(AttendanceMonitorService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Menampilkan dashboard monitoring presensi tim realtime untuk manajer.
     * Mengambil seluruh penugasan shift hari ini di departemen manajer,
     * menghitung metrik agregat, dan me-render view server-side awal.
     */
    public function index(Request $request): View
    {
        $manager = auth()->user();

        $data = $this->attendanceService->getTodayDepartmentAttendance($manager);

        return view('manager.attendance.index', [
            'serverTime' => $data['server_time'],
            'serverDate' => $data['server_date'],
            'timezone' => $data['timezone'],
            'metrics' => $data['metrics'],
            'rows' => $data['rows'],
            'gracePeriod' => $data['grace_period'],
            'manager' => $manager,
        ]);
    }

    /**
     * Endpoint polling JSON realtime (<100ms) tanpa render Blade.
     * Menggunakan service yang sama dengan index() agar logika klasifikasi
     * (on_time, late, not_started, absent, on_leave) tetap 100% konsisten.
     */
    public function realtimeFeed(Request $request): JsonResponse
    {
        $manager = auth()->user();

        $data = $this->attendanceService->getTodayDepartmentAttendance($manager);

        return response()->json([
            'server_time' => $data['server_time'],
            'server_date' => $data['server_date'],
            'metrics' => [
                'scheduled' => $data['metrics']['scheduled'],
                'total_scheduled' => $data['metrics']['total_scheduled'],
                'on_time' => $data['metrics']['on_time'],
                'late' => $data['metrics']['late'],
                'absent' => $data['metrics']['absent'],
                'not_started' => $data['metrics']['not_started'],
                'not_present' => $data['metrics']['not_present'],
                'on_leave' => $data['metrics']['on_leave'],
            ],
            'rows' => $data['rows'],
        ]);
    }
}
