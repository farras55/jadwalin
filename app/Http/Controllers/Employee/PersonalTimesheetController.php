<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Services\TimesheetExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersonalTimesheetController extends Controller
{
    /**
     * Menampilkan riwayat timesheet dan agregasi jam kerja karyawan yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Validasi isolasi data: karyawan tidak boleh mengakses ID karyawan lain
        if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $user->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses timesheet Anda sendiri.');
        }

        if ($request->filled('employee_id') && (int) $request->input('employee_id') !== (int) $user->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses timesheet Anda sendiri.');
        }

        [$from, $to] = $this->resolveDateRange($request);

        // Query dasar dibatasi hanya untuk shift_assignments karyawan yang login
        $baseQuery = ShiftAssignment::query()
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('clock_in_time', [$from, $to])
                    ->orWhereHas('shift', function ($sub) use ($from, $to) {
                        $sub->whereBetween('start_time', [$from, $to]);
                    });
            });

        // =========================================================================
        // Agregasi di level database (SQL SUM & COUNT) tanpa me-load data ke PHP
        // =========================================================================
        $totalRegularHours = round((float) (clone $baseQuery)->sum('regular_hours'), 2);
        $totalOvertimeHours = round((float) (clone $baseQuery)->sum('overtime_hours'), 2);
        $totalShifts = (int) (clone $baseQuery)->count();
        $attendedShifts = (int) (clone $baseQuery)
            ->where(function ($q) {
                $q->whereNotNull('clock_in_time')
                    ->orWhereIn('status', ['present', 'late', 'completed']);
            })
            ->count();

        $attendanceRate = $totalShifts > 0
            ? round(($attendedShifts / $totalShifts) * 100, 1)
            : 0.0;

        // Ambil riwayat dengan pagination per chunk untuk efisiensi memori
        $assignments = (clone $baseQuery)
            ->with(['shift.department'])
            ->orderByDesc(
                Shift::select('start_time')
                    ->whereColumn('shifts.id', 'shift_assignments.shift_id')
            )
            ->paginate(15)
            ->withQueryString();

        return view('employee.timesheet.index', [
            'user' => $user,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totalRegularHours' => $totalRegularHours,
            'totalOvertimeHours' => $totalOvertimeHours,
            'totalShifts' => $totalShifts,
            'attendedShifts' => $attendedShifts,
            'attendanceRate' => $attendanceRate,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Tentukan rentang tanggal filter dari request dengan default bulan berjalan.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('month') && $request->filled('year')) {
            $from = Carbon::createFromDate((int) $request->year, (int) $request->month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
        } elseif ($request->filled('month')) {
            $from = Carbon::createFromDate(now()->year, (int) $request->month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
        } else {
            $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
            $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        }

        return [$from, $to];
    }

    /**
     * Ekspor timesheet personal karyawan ke format Excel (.xlsx) menggunakan TimesheetExportService.
     */
    public function exportExcel(Request $request, TimesheetExportService $exportService): BinaryFileResponse
    {
        $user = $request->user();
        $this->authorizePersonalAccess($request, $user);

        return $exportService->exportExcel($user, $request);
    }

    /**
     * Ekspor timesheet personal karyawan ke format PDF (.pdf) menggunakan TimesheetExportService.
     */
    public function exportPdf(Request $request, TimesheetExportService $exportService): Response
    {
        $user = $request->user();
        $this->authorizePersonalAccess($request, $user);

        return $exportService->exportPdf($user, $request);
    }

    /**
     * Validasi isolasi data personal karyawan.
     */
    protected function authorizePersonalAccess(Request $request, User $user): void
    {
        if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $user->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses timesheet Anda sendiri.');
        }

        if ($request->filled('employee_id') && (int) $request->input('employee_id') !== (int) $user->id) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengakses timesheet Anda sendiri.');
        }
    }
}
