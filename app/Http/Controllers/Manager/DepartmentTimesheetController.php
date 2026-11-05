<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\TimesheetReport;
use App\Models\User;
use App\Services\TimesheetExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
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
     * Mengambil dan mengagregasi data timesheet karyawan departemen manajer
     * sesuai format laporan yang diperoleh owner (superadmin).
     */
    protected function getManagerTimesheetData(Request $request, User $manager): array
    {
        [$from, $to, $month, $year, $periodLabel] = $this->resolveDateRangeAndPeriod($request);

        $company = $manager->company;
        $department = $manager->department;
        $departmentId = $manager->department_id;
        $companyId = $manager->company_id;

        // Cek apakah periode telah selesai (lewat dari waktu sekarang)
        $isPeriodPast = $to->endOfDay()->isPast();

        // Query seluruh karyawan dalam departemen manajer (eager loading untuk mencegah N+1)
        $users = User::query()
            ->where('company_id', $companyId)
            ->where('department_id', $departmentId)
            ->whereIn('role', ['employee', 'manager'])
            ->with([
                'department',
                'timesheetReports' => fn ($q) => $q->where('period_month', $month)->where('period_year', $year),
                'shiftAssignments' => function ($q) use ($from, $to) {
                    $q->with('shift')
                        ->where(function ($query) use ($from, $to) {
                            $query->whereBetween('clock_in_time', [$from, $to])
                                ->orWhereHas('shift', function ($sub) use ($from, $to) {
                                    $sub->whereBetween('start_time', [$from, $to]);
                                });
                        });
                },
            ])
            ->orderBy('name')
            ->get();

        // Agregasi per karyawan sesuai standar laporan owner
        $reports = $users->map(function ($employee) use ($from, $to, $month, $year, $companyId, $manager, $isPeriodPast) {
            $scheduledShifts = $employee->shiftAssignments->filter(function ($assignment) use ($from, $to) {
                $date = $assignment->clock_in_time ?? $assignment->shift?->start_time;
                if (! $date) {
                    return false;
                }

                $cDate = Carbon::parse($date);

                return $cDate->betweenIncluded($from, $to);
            });

            $attendedShifts = $scheduledShifts->filter(function ($assignment) {
                return (! is_null($assignment->clock_in_time) && ! is_null($assignment->clock_out_time))
                    || in_array($assignment->status, ['present', 'late', 'completed']);
            });

            $unattendedShifts = $scheduledShifts->reject(function ($assignment) {
                return (! is_null($assignment->clock_in_time) && ! is_null($assignment->clock_out_time))
                    || in_array($assignment->status, ['present', 'late', 'completed']);
            });

            $totalRegularHours = (float) $attendedShifts->sum('regular_hours');
            $totalOvertimeHours = (float) $attendedShifts->sum('overtime_hours');
            $totalLateMinutes = (int) $attendedShifts->sum('late_minutes');

            // Hitung jam shift yang tidak dihadiri
            $totalUnattendedHours = (float) $unattendedShifts->sum(function ($assignment) {
                if ($assignment->shift && $assignment->shift->start_time && $assignment->shift->end_time) {
                    $start = Carbon::parse($assignment->shift->start_time);
                    $end = Carbon::parse($assignment->shift->end_time);

                    return round(max(0, $start->diffInMinutes($end) / 60), 2);
                }

                return (float) ($assignment->regular_hours > 0 ? $assignment->regular_hours : 8.0);
            });

            $attendanceDays = $attendedShifts->pluck('clock_in_time')
                ->map(fn ($d) => $d ? Carbon::parse($d)->format('Y-m-d') : null)
                ->filter()
                ->unique()
                ->count();

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
                    'status' => 'finalized',
                ]);
            }

            return (object) [
                'user' => $employee,
                'user_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_email' => $employee->email,
                'department_name' => $employee->department?->name ?? $manager->department?->name ?? '-',
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

        $totalMetrics = (object) [
            'total_employees' => $reports->count(),
            'total_regular_hours' => round($reports->sum('total_regular_hours'), 2),
            'total_overtime_hours' => round($reports->sum('total_overtime_hours'), 2),
            'total_unattended_hours' => round($reports->sum('total_unattended_hours'), 2),
            'total_late_minutes' => (int) $reports->sum('total_late_minutes'),
            'total_attendance_shifts' => (int) $reports->sum('attended_shifts'),
            'total_scheduled_shifts' => (int) $reports->sum('scheduled_shifts'),
        ];

        return compact(
            'company',
            'department',
            'reports',
            'from',
            'to',
            'month',
            'year',
            'totalMetrics',
            'periodLabel'
        );
    }

    /**
     * Resolusi rentang tanggal dan label periode filter.
     *
     * @return array{0: Carbon, 1: Carbon, 2: int, 3: int, 4: string}
     */
    protected function resolveDateRangeAndPeriod(Request $request): array
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($request->filled('month') && $request->filled('year')) {
            $month = (int) $request->month;
            $year = (int) $request->year;
            $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth()->endOfDay();
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        } elseif ($request->filled('month')) {
            $month = (int) $request->month;
            $year = now()->year;
            $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth()->endOfDay();
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        } elseif ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $month = (int) $from->month;
            $year = (int) $from->year;
            if ($from->isSameMonth($to)) {
                $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
            } else {
                $periodLabel = $from->translatedFormat('d M Y').' - '.$to->translatedFormat('d M Y');
            }
        } elseif ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = $from->copy()->endOfMonth()->endOfDay();
            $month = (int) $from->month;
            $year = (int) $from->year;
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        } else {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth()->endOfDay();
            $month = (int) now()->month;
            $year = (int) now()->year;
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        }

        return [$from, $to, $month, $year, $periodLabel];
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
