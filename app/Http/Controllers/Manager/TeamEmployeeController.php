<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamEmployeeController extends Controller
{
    /**
     * Menampilkan daftar karyawan dalam departemen manager.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();

        $employeesQuery = User::query()
            ->with(['position', 'department'])
            ->where('company_id', $manager->company_id)
            ->where('role', 'employee');

        // Manager biasa hanya dapat melihat karyawan departemennya.
        // Global manager dapat melihat seluruh departemen perusahaannya.
        if (! $manager->is_global_manager) {
            $employeesQuery->where(
                'department_id',
                $manager->department_id
            );
        }

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $employeesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('position_id')) {
            $employeesQuery->where(
                'position_id',
                $request->input('position_id')
            );
        }

        if ($request->filled('employment_type')) {
            $employeesQuery->where(
                'employment_type',
                $request->input('employment_type')
            );
        }

        $employees = $employeesQuery
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $positionsQuery = Position::query()
            ->where('company_id', $manager->company_id);

        if (! $manager->is_global_manager) {
            $positionsQuery->where(
                'department_id',
                $manager->department_id
            );
        }

        $positions = $positionsQuery
            ->orderBy('name')
            ->get();

        return view('manager.employees.index', [
            'employees' => $employees,
            'positions' => $positions,
            'search' => $search,
            'selectedPosition' => $request->input('position_id'),
            'selectedEmploymentType' => $request->input('employment_type'),
        ]);
    }

    /**
     * Menampilkan profil dan aktivitas shift karyawan.
     */
    public function show(Request $request, User $user): View
    {
        $manager = $request->user();

        // Validasi perusahaan, peran karyawan, dan batas departemen.
        abort_unless(
            (int) $user->company_id === (int) $manager->company_id
                && $user->role === 'employee'
                && (
                    $manager->is_global_manager
                    || (int) $user->department_id ===
                        (int) $manager->department_id
                ),
            403,
            'Anda tidak memiliki akses ke profil karyawan ini.'
        );

        $user->load(['position', 'department']);

        $today = now()->toDateString();
        $thirtyDaysAgo = now()->subDays(29)->toDateString();

        // Tanggal jadwal diambil dari tabel shifts.
        $recentAssignments = ShiftAssignment::query()
            ->with('shift')
            ->where('company_id', $manager->company_id)
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use (
                $thirtyDaysAgo,
                $today
            ) {
                $query->whereBetween('start_time', [
                    $thirtyDaysAgo.' 00:00:00',
                    $today.' 23:59:59',
                ]);
            })
            ->get()
            ->sortByDesc(fn (ShiftAssignment $assignment) => $assignment->shift?->start_time
            )
            ->values();

        // Total jam shift pada bulan berjalan.
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthlyAssignments = ShiftAssignment::query()
            ->where('company_id', $manager->company_id)
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use (
                $monthStart,
                $monthEnd
            ) {
                $query->whereBetween('start_time', [
                    $monthStart.' 00:00:00',
                    $monthEnd.' 23:59:59',
                ]);
            })
            ->get();

        $monthlyHours = $monthlyAssignments->sum(
            fn (ShiftAssignment $assignment) => (float) $assignment->regular_hours
                + (float) $assignment->overtime_hours
        );

        $attendanceCount = $recentAssignments
            ->whereIn('status', ['present', 'late'])
            ->count();

        return view('manager.employees.show', [
            'employee' => $user,
            'recentAssignments' => $recentAssignments,
            'monthlyHours' => $monthlyHours,
            'attendanceCount' => $attendanceCount,
            'monthlyShiftCount' => $monthlyAssignments->count(),
        ]);
    }
}
