<?php

namespace App\Services;

use App\Exports\TimesheetExport;
use App\Models\Department;
use App\Models\TimesheetReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TimesheetExportService
{
    /**
     * Helper terpusat untuk membangun nama file ekspor yang unik per role.
     * Pola: rekap_timesheet_{role}_{tahun}_{bulan}.{ext}
     *
     * @param  string|User  $roleOrUser
     */
    public static function buildExportFilename(mixed $roleOrUser, int|string $year, int|string $month, string $ext): string
    {
        $role = $roleOrUser instanceof User ? $roleOrUser->role : (string) $roleOrUser;

        // Sanitasi: huruf kecil, ganti karakter non-alfanumerik atau spasi dengan underscore
        $sanitizedRole = strtolower(trim($role));
        $sanitizedRole = preg_replace('/[^a-z0-9]+/i', '_', $sanitizedRole) ?? '';
        $sanitizedRole = trim($sanitizedRole, '_');

        if ($sanitizedRole === '') {
            $sanitizedRole = 'timesheet';
        }

        $paddedMonth = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);
        $cleanExt = ltrim(strtolower(trim($ext)), '.');

        return "rekap_timesheet_{$sanitizedRole}_{$year}_{$paddedMonth}.{$cleanExt}";
    }

    /**
     * Mengambil dan mengagregasi data timesheet sesuai hak akses role.
     */
    public function getTimesheetData(User $user, Request $request): array
    {
        [$from, $to, $month, $year, $periodLabel] = $this->resolveDateRangeAndPeriod($request);

        $company = $user->company;
        $companyId = $user->company_id;
        $isPeriodPast = $to->endOfDay()->isPast();

        // -------------------------------------------------------------
        // TUGAS 3: Penerapan Hak Akses Data Per Role
        // -------------------------------------------------------------
        $userQuery = User::query()->where('company_id', $companyId);
        $selectedDepartment = null;
        $departmentName = 'Semua Departemen';
        $departments = Department::where('company_id', $companyId)->orderBy('name')->get();

        if (in_array($user->role, ['superadmin', 'owner', 'admin'])) {
            // Super Admin & Owner: seluruh karyawan di perusahaan (dapat difilter per departemen)
            if ($request->filled('department_id')) {
                $deptId = (int) $request->input('department_id');
                $userQuery->where('department_id', $deptId);
                $selectedDepartment = $departments->firstWhere('id', $deptId);
                $departmentName = $selectedDepartment?->name ?? 'Semua Departemen';
            } else {
                $departmentName = 'Semua Departemen';
            }
        } elseif ($user->role === 'manager') {
            // Manager: hanya karyawan di departemennya sendiri
            if ($request->filled('department_id') && (int) $request->input('department_id') !== (int) $user->department_id) {
                abort(403, 'Akses ditolak: Anda hanya dapat mengakses data departemen Anda.');
            }

            $userQuery->where('department_id', $user->department_id);
            $selectedDepartment = $user->department;
            $departmentName = $user->department?->name ?? 'Semua Departemen';
        } else {
            // Employee: hanya data dirinya sendiri
            if ($request->filled('user_id') && (int) $request->input('user_id') !== (int) $user->id) {
                abort(403, 'Akses ditolak: Anda hanya dapat mengakses data timesheet Anda sendiri.');
            }
            if ($request->filled('employee_id') && (int) $request->input('employee_id') !== (int) $user->id) {
                abort(403, 'Akses ditolak: Anda hanya dapat mengakses data timesheet Anda sendiri.');
            }

            $userQuery->where('id', $user->id);
            $selectedDepartment = $user->department;
            $departmentName = $user->department?->name ?? '-';
        }

        // Ambil data staf / user beserta riwayat shift assignments dan timesheet report
        $users = $userQuery
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

        // Agregasi per karyawan
        $reports = $users->map(function ($employee) use ($from, $to, $month, $year, $companyId, $user, $isPeriodPast) {
            $scheduledShifts = $employee->shiftAssignments->filter(function ($assignment) use ($from, $to) {
                $date = $assignment->clock_in_time ?? $assignment->shift?->start_time;
                if (! $date) {
                    return false;
                }
                $carbonDate = Carbon::parse($date);

                return $carbonDate->betweenIncluded($from, $to);
            });

            $attendedShifts = $scheduledShifts->filter(function ($assignment) {
                return $assignment->clock_in_time !== null
                    || in_array($assignment->status, ['present', 'late', 'completed']);
            });

            $totalRegularHours = (float) $attendedShifts->sum('regular_hours');
            $totalOvertimeHours = (float) $attendedShifts->sum('overtime_hours');
            $totalLateMinutes = (int) $attendedShifts->sum('late_minutes');

            $unattendedShifts = $scheduledShifts->filter(function ($assignment) {
                return $assignment->clock_in_time === null
                    && ! in_array($assignment->status, ['present', 'late', 'completed']);
            });

            $totalUnattendedHours = (float) $unattendedShifts->sum(function ($assignment) {
                if ($assignment->shift && $assignment->shift->start_time && $assignment->shift->end_time) {
                    $start = Carbon::parse($assignment->shift->start_time);
                    $end = Carbon::parse($assignment->shift->end_time);

                    return round(max(0, $start->diffInMinutes($end) / 60), 2);
                }

                return (float) ($assignment->regular_hours > 0 ? $assignment->regular_hours : 8.0);
            });

            $attendanceDays = $attendedShifts->pluck('clock_in_time')
                ->map(fn ($d) => $d instanceof Carbon ? $d->format('Y-m-d') : ($d ? Carbon::parse($d)->format('Y-m-d') : null))
                ->filter()
                ->unique()
                ->count();

            // Status Laporan Timesheet (Otomatis 'Final' jika periode sudah lewat)
            $timesheetReport = $employee->timesheetReports->first();
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
                    'generated_by' => auth()->id() ?? $user->id,
                ]);
            } elseif ($timesheetReport && $isPeriodPast && $timesheetReport->status !== 'finalized') {
                $timesheetReport->update([
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'status' => 'finalized',
                    'generated_by' => auth()->id() ?? $user->id,
                ]);
            } elseif ($timesheetReport && $timesheetReport->status === 'draft') {
                $timesheetReport->update([
                    'total_regular_hours' => $totalRegularHours,
                    'total_overtime_hours' => $totalOvertimeHours,
                    'generated_by' => auth()->id() ?? $user->id,
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
                'status' => $status === 'finalized' ? 'Final' : 'Draft',
                'timesheet_report_id' => $timesheetReport?->id,
                'attendance_logs' => $scheduledShifts->values(),
            ];
        });

        // Metrik Total Keseluruhan
        $totalMetrics = (object) [
            'total_employees' => $reports->count(),
            'total_scheduled_shifts' => (int) $reports->sum('scheduled_shifts'),
            'total_attendance_shifts' => (int) $reports->sum('attended_shifts'),
            'total_attendance_days' => (int) $reports->sum('attendance_days'),
            'total_regular_hours' => round((float) $reports->sum('total_regular_hours'), 2),
            'total_overtime_hours' => round((float) $reports->sum('total_overtime_hours'), 2),
            'total_unattended_hours' => round((float) $reports->sum('total_unattended_hours'), 2),
            'total_late_minutes' => (int) $reports->sum('total_late_minutes'),
        ];

        $statusOverall = $isPeriodPast || ($reports->isNotEmpty() && $reports->every(fn ($r) => $r->status === 'Final')) ? 'Final' : 'Draft';

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return [
            'company' => $company,
            'companyName' => $company?->name ?? config('app.name', 'PT Cakrawala Mega Pratama'),
            'departments' => $departments,
            'department' => $selectedDepartment,
            'selectedDepartment' => $selectedDepartment,
            'departmentId' => $departmentId ?? ($selectedDepartment?->id),
            'departmentName' => $departmentName,
            'reports' => $reports,
            'month' => $month,
            'year' => $year,
            'from' => $from,
            'to' => $to,
            'periodLabel' => $periodLabel,
            'totalMetrics' => $totalMetrics,
            'status' => $statusOverall,
            'isPeriodPast' => $isPeriodPast,
            'monthNames' => $monthNames,
            'generatedAt' => now()->translatedFormat('d F Y, H:i').' WIB',
            'isEmployee' => $user->role === 'employee',
            'attendanceLogs' => $user->role === 'employee' ? ($reports->first()?->attendance_logs ?? collect()) : collect(),
        ];
    }

    /**
     * Ekspor file Excel (.xlsx) seragam untuk semua role.
     */
    public function exportExcel(User $user, Request $request): BinaryFileResponse
    {
        $data = $this->getTimesheetData($user, $request);
        $filename = self::buildExportFilename($user, $data['year'], $data['month'], 'xlsx');

        return Excel::download(new TimesheetExport($data), $filename);
    }

    /**
     * Ekspor file PDF (.pdf) seragam untuk semua role.
     */
    public function exportPdf(User $user, Request $request): Response
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $data = $this->getTimesheetData($user, $request);
        $filename = self::buildExportFilename($user, $data['year'], $data['month'], 'pdf');

        $pdf = Pdf::loadView('exports.timesheet_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Mengurai rentang tanggal dan nama periode dari Request.
     *
     * @return array{0: Carbon, 1: Carbon, 2: int, 3: int, 4: string}
     */
    private function resolveDateRangeAndPeriod(Request $request): array
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($request->filled('period')) {
            $parsed = Carbon::parse($request->input('period'));
            $month = (int) $parsed->month;
            $year = (int) $parsed->year;
            $from = $parsed->copy()->startOfMonth();
            $to = $parsed->copy()->endOfMonth();
        } elseif ($request->filled('month') && str_contains((string) $request->input('month'), '-')) {
            $parsed = Carbon::parse($request->input('month'));
            $month = (int) $parsed->month;
            $year = (int) $parsed->year;
            $from = $parsed->copy()->startOfMonth();
            $to = $parsed->copy()->endOfMonth();
        } elseif ($request->filled('month') && $request->filled('year')) {
            $month = (int) $request->input('month');
            $year = (int) $request->input('year');
            $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
        } elseif ($request->filled('month')) {
            $month = (int) $request->input('month');
            $year = (int) now()->year;
            $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
        } elseif ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $month = (int) $from->month;
            $year = (int) $from->year;
        } else {
            $month = (int) now()->month;
            $year = (int) now()->year;
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        }

        $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;

        return [$from, $to, $month, $year, $periodLabel];
    }
}
