<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\AttendanceRegularizationRequest;
use App\Models\AttendanceRegularization;
use App\Models\AuditLog;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceRegularizationController extends Controller
{
    /**
     * Menampilkan riwayat pengajuan koreksi kehadiran karyawan yang login.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();

        $regularizations = AttendanceRegularization::with([
            'shiftAssignment.shift.department',
            'reviewer',
        ])
            ->where('employee_id', $user->id)
            ->latest()
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($regularizations);
        }

        return view('employee.attendance.regularizations.index', [
            'regularizations' => $regularizations,
        ]);
    }

    /**
     * Menyimpan pengajuan koreksi kehadiran baru.
     */
    public function store(AttendanceRegularizationRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $regularization = DB::transaction(function () use ($request, $user) {
            $assignment = ShiftAssignment::with('shift')->findOrFail($request->validated('shift_assignment_id'));

            $reg = AttendanceRegularization::create([
                'company_id' => $user->company_id,
                'shift_assignment_id' => $assignment->id,
                'employee_id' => $user->id,
                'requested_clock_out_time' => $request->validated('requested_clock_out_time'),
                'reason' => $request->validated('reason'),
                'status' => 'pending',
                'overtime_status' => 'pending_approval',
            ]);

            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'action' => 'regularization_submitted',
                'model_type' => AttendanceRegularization::class,
                'old_values' => null,
                'new_values' => [
                    'id' => $reg->id,
                    'shift_assignment_id' => $assignment->id,
                    'requested_clock_out_time' => $reg->requested_clock_out_time?->toDateTimeString(),
                    'reason' => $reg->reason,
                    'status' => 'pending',
                ],
                'ip_address' => $request->ip(),
            ]);

            // Kirim notifikasi in-app ke manajer terkait
            $managers = User::query()
                ->where('company_id', $user->company_id)
                ->where('role', 'manager')
                ->where(function ($q) use ($user) {
                    $q->where('department_id', $user->department_id)
                        ->orWhere('is_global_manager', true);
                })
                ->get();

            foreach ($managers as $manager) {
                NotificationService::send(
                    $manager->id,
                    'regularization_submitted',
                    'Pengajuan Koreksi Kehadiran',
                    "Karyawan {$user->name} mengajukan koreksi jam pulang riil untuk shift {$assignment->shift?->title}.",
                    $user->company_id
                );
            }

            return $reg;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan koreksi kehadiran berhasil dikirim.',
                'data' => $regularization,
            ], 201);
        }

        return redirect()->back()->with('success', 'Pengajuan koreksi kehadiran berhasil dikirim.');
    }
}
