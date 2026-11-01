<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\TimesheetReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;

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
    public function exportExcel(Request $request)
    {
        $data = $this->getTimesheetData($request);
        $company = $data['company'];
        $reports = $data['reports'];
        $month = $data['month'];
        $year = $data['year'];
        $periodLabel = $data['periodLabel'];
        $totalMetrics = $data['totalMetrics'];

        $filename = 'rekap_timesheet_'.$year.'_'.str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $rows = [
            ['<b>REKAPITULASI TIMESHEET KARYAWAN</b>'],
            ['Perusahaan', $company?->name ?? config('app.name', 'Jadwalin')],
            ['Periode', $periodLabel],
            ['Departemen', $data['selectedDepartment']?->name ?? 'Semua Departemen'],
            [],
            [
                '<b>No</b>',
                '<b>Nama Karyawan</b>',
                '<b>Departemen</b>',
                '<b>Shift Dijadwalkan</b>',
                '<b>Shift Dihadiri</b>',
                '<b>Hari Kehadiran</b>',
                '<b>Jam Reguler</b>',
                '<b>Jam Lembur</b>',
                '<b>Keterlambatan (Menit)</b>',
                '<b>Status</b>',
            ],
        ];

        foreach ($reports as $index => $row) {
            $rows[] = [
                $index + 1,
                $row->employee_name,
                $row->department_name,
                $row->scheduled_shifts,
                $row->attended_shifts,
                $row->attendance_days,
                number_format($row->total_regular_hours, 2),
                number_format($row->total_overtime_hours, 2),
                $row->total_late_minutes,
                ucfirst($row->status),
            ];
        }

        // Baris total agregat
        $rows[] = [];
        $rows[] = [
            '<b>TOTAL</b>',
            '',
            '',
            $totalMetrics->total_scheduled_shifts,
            $totalMetrics->total_attendance_shifts,
            '',
            number_format($totalMetrics->total_regular_hours, 2),
            number_format($totalMetrics->total_overtime_hours, 2),
            $totalMetrics->total_late_minutes,
            '',
        ];

        if (! app()->runningUnitTests()) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        if (class_exists(SimpleXLSXGen::class)) {
            $xlsx = SimpleXLSXGen::fromArray($rows);

            return response()->streamDownload(function () use ($xlsx) {
                echo (string) $xlsx;
            }, $filename.'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}.xlsx\"",
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
        }

        // Fallback CSV murni jika SimpleXLSXGen tidak tersedia
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            foreach ($rows as $row) {
                $cleanRow = array_map(function ($val) {
                    return strip_tags((string) $val);
                }, $row);
                fputcsv($handle, $cleanRow);
            }

            fclose($handle);
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    /**
     * Menangani stream download file laporan timesheet format PDF (.pdf).
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getTimesheetData($request);
        $filename = 'rekap_timesheet_'.$data['year'].'_'.str_pad((string) $data['month'], 2, '0', STR_PAD_LEFT);

        if (! app()->runningUnitTests()) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.reports.timesheet_pdf', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename.'.pdf');
        }

        $html = view('admin.reports.timesheet_pdf', $data)->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}.pdf\"",
        ]);
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

        // Agregasi jam reguler, jam lembur, keterlambatan, dan kehadiran per karyawan
        $reports = $users->map(function ($employee) use ($month, $year, $companyId) {
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

            $totalRegularHours = (float) $attendedShifts->sum('regular_hours');
            $totalOvertimeHours = (float) $attendedShifts->sum('overtime_hours');
            $totalLateMinutes = (int) $attendedShifts->sum('late_minutes');

            $attendanceDays = $attendedShifts->pluck('clock_in_time')
                ->map(fn ($d) => $d?->format('Y-m-d'))
                ->filter()
                ->unique()
                ->count();

            // Relasi timesheet_reports
            $timesheetReport = $employee->timesheetReports->first();

            if (! $timesheetReport && $attendedShifts->isNotEmpty()) {
                $timesheetReport = TimesheetReport::create([
                    'company_id' => $companyId,
                    'user_id' => $employee->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'status' => 'draft',
                    'generated_by' => auth()->id(),
                ]);
            } elseif ($timesheetReport && $timesheetReport->status === 'draft') {
                $timesheetReport->update([
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'generated_by' => auth()->id(),
                ]);
            }

            $status = $timesheetReport ? $timesheetReport->status : 'draft';

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
