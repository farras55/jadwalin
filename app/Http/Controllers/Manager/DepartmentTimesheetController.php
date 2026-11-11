<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Services\TimesheetExportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DepartmentTimesheetController extends Controller
{
    /**
     * Menampilkan rekapitulasi timesheet staf departemen manajer.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();

        $this->authorizeDepartment($request, $manager);

        [$from, $to] = $this->resolveDateRange($request);
        $baseQuery = $this->filteredAssignmentsQuery($manager, $from, $to);

        // Agregasi di level database (SQL SUM & COUNT) tanpa memuat seluruh baris ke memori
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

        $assignments = (clone $baseQuery)
            ->with(['user.department', 'shift.department'])
            ->orderByDesc(
                Shift::select('start_time')
                    ->whereColumn('shifts.id', 'shift_assignments.shift_id')
            )
            ->paginate(15)
            ->withQueryString();

        $department = $manager->department;

        return view('manager.timesheets.index', [
            'manager' => $manager,
            'department' => $department,
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
     * Ekspor laporan timesheet departemen ke format Excel (.xlsx) menggunakan TimesheetExportService.
     */
    public function exportExcel(Request $request, TimesheetExportService $exportService): BinaryFileResponse
    {
        $manager = $request->user();
        $this->authorizeDepartment($request, $manager);

        return $exportService->exportExcel($manager, $request);
    }

    /**
     * Ekspor laporan timesheet departemen ke format PDF (.pdf) menggunakan TimesheetExportService.
     */
    public function exportPdf(Request $request, TimesheetExportService $exportService): Response
    {
        $manager = $request->user();
        $this->authorizeDepartment($request, $manager);

        return $exportService->exportPdf($manager, $request);
    }

    /**
     * Pastikan isolasi data: manajer tidak dapat mengakses data departemen lain.
     */
    private function authorizeDepartment(Request $request, User $manager): void
    {
        if ($request->filled('department_id') && (int) $request->input('department_id') !== (int) $manager->department_id) {
            abort(403, 'Akses ditolak: Anda hanya berwenang mengakses data departemen Anda sendiri.');
        }
    }

    /**
     * Query dasar shift_assignments staf departemen dengan scope ketat where('department_id', $manager->department_id).
     */
    private function filteredAssignmentsQuery(User $manager, Carbon $from, Carbon $to): Builder
    {
        return ShiftAssignment::query()
            ->where('company_id', $manager->company_id)
            ->whereHas('user', function ($q) use ($manager) {
                $q->where('department_id', $manager->department_id);
            })
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('clock_in_time', [$from, $to])
                    ->orWhereHas('shift', function ($sub) use ($from, $to) {
                        $sub->whereBetween('start_time', [$from, $to]);
                    });
            });
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
}
