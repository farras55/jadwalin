<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\TimesheetReport;
use App\Models\User;
use App\Services\TimesheetExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimesheetReportController extends Controller
{
    /**
     * Menampilkan halaman rekapitulasi timesheet karyawan dengan filter.
     */
    public function index(Request $request)
    {
        $data = $this->getTimesheetData($request);

        return view('admin.reports.timesheet', $data);
    }

    /**
     * Menangani stream download file laporan timesheet format Excel (.xlsx).
     */
    public function exportExcel(Request $request, TimesheetExportService $exportService)
    {
        return $exportService->exportExcel($request->user(), $request);
    }

    /**
     * Menangani stream download file laporan timesheet format PDF (.pdf).
     */
    public function exportPdf(Request $request, TimesheetExportService $exportService)
    {
        return $exportService->exportPdf($request->user(), $request);
    }

    /**
     * Melakukan filter dan agregasi data timesheet shift assignments.
     */
    protected function getTimesheetData(Request $request): array
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        $user = auth()->user();
        $company = $user?->company;
        $companyId = $user?->company_id;

        // Ambil daftar departemen untuk dropdown filter
        $departments = Department::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        // Query pengguna dengan eager loading untuk mencegah N+1 Problem
        $usersQuery = User::query()
            ->where('company_id', $companyId)
            ->whereIn('role', ['employee', 'manager'])
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->with([
                'department',
                'timesheetReports' => fn ($q) => $q->where('period_month', $month)->where('period_year', $year),
                'shiftAssignments' => function ($q) use ($month, $year) {
                    $q->with('shift')
                        ->where(function ($query) use ($month, $year) {
                            $query->where(function ($sub) use ($month, $year) {
                                $sub->whereYear('clock_in_time', $year)
                                    ->whereMonth('clock_in_time', $month);
                            })->orWhereHas('shift', function ($sub) use ($month, $year) {
                                $sub->whereYear('start_time', $year)
                                    ->whereMonth('start_time', $month);
                            });
                        });
                },
            ])
            ->orderBy('name');

        $users = $usersQuery->get();

        // Cek apakah periode telah selesai (lewat dari waktu sekarang)
        $isPeriodPast = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->isPast();

        // Agregasi jam reguler, jam lembur, keterlambatan, dan kehadiran per karyawan
        $reports = $users->map(function ($employee) use ($month, $year, $companyId, $isPeriodPast) {
            // Semua shift yang dijadwalkan pada periode terkait
            $scheduledShifts = $employee->shiftAssignments->filter(function ($assignment) use ($month, $year) {
                $date = $assignment->clock_in_time ?? $assignment->shift?->start_time;
                if (! $date) {
                    return false;
                }

                return (int) $date->format('n') === $month && (int) $date->format('Y') === $year;
            });

            // Shift yang dihadiri: clock_in_time dan clock_out_time tidak null
            $attendedShifts = $scheduledShifts->filter(function ($assignment) {
                return ! is_null($assignment->clock_in_time) && ! is_null($assignment->clock_out_time);
            });

            $unattendedShifts = $scheduledShifts->reject(function ($assignment) {
                return ! is_null($assignment->clock_in_time) && ! is_null($assignment->clock_out_time);
            });

            $totalRegularHours = (float) $attendedShifts->sum('regular_hours');
            $totalOvertimeHours = (float) $attendedShifts->sum('overtime_hours');
            $totalLateMinutes = (int) $attendedShifts->sum('late_minutes');

            $totalUnattendedHours = (float) $unattendedShifts->sum(function ($assignment) {
                if ($assignment->shift && $assignment->shift->start_time && $assignment->shift->end_time) {
                    $start = Carbon::parse($assignment->shift->start_time);
                    $end = Carbon::parse($assignment->shift->end_time);

                    return round(max(0, $start->diffInMinutes($end) / 60), 2);
                }

                return (float) ($assignment->regular_hours > 0 ? $assignment->regular_hours : 8.0);
            });

            $attendanceDays = $attendedShifts->pluck('clock_in_time')
                ->map(fn ($d) => $d?->format('Y-m-d'))
                ->filter()
                ->unique()
                ->count();

            // Relasi timesheet_reports
            $timesheetReport = $employee->timesheetReports->first();

            // Jika periode bulannya sudah lewat/selesai, status otomatis menjadi finalized
            $status = $isPeriodPast ? 'finalized' : ($timesheetReport ? $timesheetReport->status : 'draft');

            if (! $timesheetReport && $attendedShifts->isNotEmpty()) {
                $timesheetReport = TimesheetReport::create([
                    'company_id' => $companyId,
                    'user_id' => $employee->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'status' => $status,
                    'generated_by' => auth()->id(),
                ]);
            } elseif ($timesheetReport && $isPeriodPast && $timesheetReport->status !== 'finalized') {
                $timesheetReport->update([
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'status' => 'finalized',
                    'generated_by' => auth()->id(),
                ]);
            } elseif ($timesheetReport && $timesheetReport->status === 'draft') {
                $timesheetReport->update([
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'generated_by' => auth()->id(),
                ]);
            }

            return (object) [
                'user' => $employee,
                'user_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_email' => $employee->email,
                'department_name' => $employee->department?->name ?? '-',
                'scheduled_shifts' => $scheduledShifts->count(),
                'attended_shifts' => $attendedShifts->count(),
                'attendance_days' => $attendanceDays,
                'total_regular_hours' => round($totalRegularHours, 2),
                'total_overtime_hours' => round($totalOvertimeHours, 2),
                'total_unattended_hours' => round($totalUnattendedHours, 2),
                'total_late_minutes' => $totalLateMinutes,
                'status' => $status,
                'timesheet_report_id' => $timesheetReport?->id,
            ];
        });

        // Metrik Ringkasan KPI
        $totalMetrics = (object) [
            'total_employees' => $reports->count(),
            'total_regular_hours' => round($reports->sum('total_regular_hours'), 2),
            'total_overtime_hours' => round($reports->sum('total_overtime_hours'), 2),
            'total_unattended_hours' => round($reports->sum('total_unattended_hours'), 2),
            'total_late_minutes' => (int) $reports->sum('total_late_minutes'),
            'total_attendance_shifts' => (int) $reports->sum('attended_shifts'),
            'total_scheduled_shifts' => (int) $reports->sum('scheduled_shifts'),
        ];

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        $selectedDepartment = $departmentId ? $departments->firstWhere('id', $departmentId) : null;

        return compact(
            'company',
            'departments',
            'reports',
            'month',
            'year',
            'departmentId',
            'totalMetrics',
            'periodLabel',
            'selectedDepartment',
            'monthNames'
        );
    }
}
