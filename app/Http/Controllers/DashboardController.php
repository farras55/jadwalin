<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Company;
use App\Models\Department;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isSuperadmin()) {
            return $this->superadminDashboard($user);
        }

        if ($user->isManager()) {
            return $this->managerDashboard($user);
        }

        if ($user->isEmployee()) {
            return $this->employeeDashboard($user);
        }

        abort(403, 'Peran pengguna tidak dikenali.');
    }

    private function superadminDashboard(User $user): View
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $companyCount = Company::count();
        $configuredCompanyCount = Company::whereHas('companySetting')->count();

        return view('dashboard', [
            'roleTitle' => 'Super Admin',
            'departmentCount' => Department::count(),
            'employeeCount' => User::where('role', 'employee')
                ->where('status', 'active')
                ->count(),
            'monthlyShiftCount' => Shift::whereBetween('start_time', [
                $monthStart,
                $monthEnd,
            ])->count(),
            'companyCount' => $companyCount,
            'configuredCompanyCount' => $configuredCompanyCount,
        ]);
    }

    private function managerDashboard(User $manager): View
    {
        abort_unless($manager->company_id, 403, 'Akun belum terhubung ke perusahaan.');

        $today = now();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $teamQuery = User::where('company_id', $manager->company_id)
            ->where('role', 'employee')
            ->where('status', 'active');

        $this->applyManagerDepartmentScope($teamQuery, $manager);

        $teamCount = (clone $teamQuery)->count();

        $presentQuery = ShiftAssignment::where('company_id', $manager->company_id)
            ->where('status', 'present')
            ->whereHas('shift', function (Builder $query) use ($today) {
                $query->whereDate('start_time', $today->toDateString());
            })
            ->whereHas('user', function (Builder $query) use ($manager) {
                $query->where('company_id', $manager->company_id)
                    ->where('role', 'employee');

                if (!$manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            });

        $pendingAvailabilityQuery = Availability::where(
            'company_id',
            $manager->company_id
        )->where('status', 'pending_manager')
            ->whereHas('user', function (Builder $query) use ($manager) {
                $query->where('company_id', $manager->company_id);

                if (!$manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            });

        $pendingSwapQuery = ShiftSwap::where('company_id', $manager->company_id)
            ->where('status', 'pending_manager')
            ->whereHas('shiftAssignment.shift', function (Builder $query) use ($manager) {
                $query->where('company_id', $manager->company_id);

                if (!$manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            });

        $weeklyRosterQuery = ShiftAssignment::where(
            'company_id',
            $manager->company_id
        )->whereHas('shift', function (Builder $query) use ($weekStart, $weekEnd, $manager) {
            $query->where('company_id', $manager->company_id)
                ->whereBetween('start_time', [$weekStart, $weekEnd]);

            if (!$manager->is_global_manager) {
                $query->where('department_id', $manager->department_id);
            }
        });

        $pendingAvailability = (clone $pendingAvailabilityQuery)
            ->with('user')
            ->orderBy('start_time')
            ->first();

        $openShiftClaimCount = OpenShiftClaim::where(
            'company_id',
            $manager->company_id
        )->where('status', 'pending_manager')
            ->whereHas('shift', function (Builder $query) use ($manager) {
                $query->where('company_id', $manager->company_id);

                if (!$manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            })->count();

        return view('dashboard', [
            'roleTitle' => 'Manager',
            'teamCount' => $teamCount,
            'presentStaffCount' => $presentQuery->count(),
            'pendingAvailabilityCount' => (clone $pendingAvailabilityQuery)->count(),
            'pendingSwapCount' => $pendingSwapQuery->count(),
            'openShiftClaimCount' => $openShiftClaimCount,
            'weeklyRosterCount' => $weeklyRosterQuery->count(),
            'pendingAvailability' => $pendingAvailability,
        ]);
    }

    private function employeeDashboard(User $employee): View
    {
        abort_unless($employee->company_id, 403, 'Akun belum terhubung ke perusahaan.');

        $today = now();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $todayAssignments = ShiftAssignment::with('shift')
            ->where('company_id', $employee->company_id)
            ->where('user_id', $employee->id)
            ->whereHas('shift', function (Builder $query) use ($today) {
                $query->whereDate('start_time', $today->toDateString());
            })
            ->get();

        $weeklyAssignments = ShiftAssignment::with('shift')
            ->where('company_id', $employee->company_id)
            ->where('user_id', $employee->id)
            ->whereHas('shift', function (Builder $query) use ($weekStart, $weekEnd) {
                $query->whereBetween('start_time', [$weekStart, $weekEnd]);
            })
            ->get();

        // Jadwal dihitung dari waktu mulai dan selesai shift karena
        // tabel shift_assignments saat ini belum memiliki kolom date.
        $weeklyScheduledHours = $weeklyAssignments->sum(function ($assignment) {
            $shift = $assignment->shift;

            if (!$shift || !$shift->start_time || !$shift->end_time) {
                return 0;
            }

            return max(
                0,
                Carbon::parse($shift->start_time)->diffInMinutes(
                    Carbon::parse($shift->end_time),
                    false
                ) / 60
            );
        });

        $companySetting = $employee->company?->companySetting;

        $weeklyHourLimit = $employee->max_weekly_hours
            ?? $companySetting?->max_regular_hours
            ?? 40;

        $remainingWeeklyHours = max(
            0,
            $weeklyHourLimit - $weeklyScheduledHours
        );

        $openShiftsQuery = Shift::where('company_id', $employee->company_id)
            ->where('is_open_shift', true)
            ->where('status', 'published')
            ->where('start_time', '>=', $today)
            ->where(function (Builder $query) use ($employee) {
                $query->where('department_id', $employee->department_id)
                    ->orWhere('is_global_broadcast', true);
            });

        $openShifts = (clone $openShiftsQuery)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'roleTitle' => 'Karyawan',
            'todayAssignments' => $todayAssignments,
            'weeklyAssignments' => $weeklyAssignments,
            'weeklyScheduledHours' => round($weeklyScheduledHours, 2),
            'weeklyHourLimit' => $weeklyHourLimit,
            'remainingWeeklyHours' => round($remainingWeeklyHours, 2),
            'openShiftCount' => $openShiftsQuery->count(),
            'openShifts' => $openShifts,
        ]);
    }

    /**
     * Manager biasa hanya melihat departemennya.
     * Global manager dapat melihat semua departemen dalam perusahaannya.
     */
    private function applyManagerDepartmentScope(
        Builder $query,
        User $manager
    ): void {
        if (!$manager->is_global_manager) {
            $query->where('department_id', $manager->department_id);
        }
    }
}
