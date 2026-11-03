<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRegularization;
use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceRegularizationController extends Controller
{
    /**
     * Menampilkan daftar pengajuan koreksi kehadiran bawahan tim.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();
        $status = $request->input('status', 'pending');

        $query = AttendanceRegularization::with([
            'employee.position',
            'employee.department',
            'shiftAssignment.shift.department',
            'reviewer',
        ])
            ->where('company_id', $manager->company_id)
            ->whereHas('employee', function ($q) use ($manager) {
                if (! $manager->is_global_manager) {
                    $q->where('department_id', $manager->department_id);
                }
            });

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $regularizations = $query->latest('created_at')->paginate(15);

        // Statistik ringkasan pengajuan
        $baseCountQuery = AttendanceRegularization::query()
            ->where('company_id', $manager->company_id)
            ->whereHas('employee', function ($q) use ($manager) {
                if (! $manager->is_global_manager) {
                    $q->where('department_id', $manager->department_id);
                }
            });

        $counts = [
            'pending' => (clone $baseCountQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseCountQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseCountQuery)->where('status', 'rejected')->count(),
            'all' => (clone $baseCountQuery)->count(),
        ];

        return view('manager.attendance.regularizations.index', [
            'regularizations' => $regularizations,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    /**
     * Menyetujui pengajuan koreksi kehadiran dan memperbarui data absensi.
     */
    public function approve(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $manager = $request->user();

        $this->authorizeManager($manager, $regularization);

        if ($regularization->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'overtime_status' => ['required', 'string', 'in:none,approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ], [
            'overtime_status.required' => 'Pilihan status lembur wajib ditentukan.',
            'overtime_status.in' => 'Status lembur tidak valid.',
            'review_note.max' => 'Catatan peninjauan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($manager, $regularization, $validated, $request) {
            $assignment = $regularization->shiftAssignment;
            $requestedOut = Carbon::parse($regularization->requested_clock_out_time);
            $clockIn = Carbon::parse($assignment->clock_in_time);

            // Hitung ulang jam kerja berdasarkan jam pulang yang disetujui
            $diffMinutes = max(0, $clockIn->diffInMinutes($requestedOut));
            $totalHours = round($diffMinutes / 60, 2);

            $setting = CompanySetting::where('company_id', $manager->company_id)->first();
            $maxRegularHours = ($setting && $setting->max_regular_hours !== null)
                ? (float) $setting->max_regular_hours
                : 8.0;

            $regularHours = min($totalHours, $maxRegularHours);

            if ($validated['overtime_status'] === 'approved') {
                $overtimeHours = max(0.0, round($totalHours - $maxRegularHours, 2));
                $isOvertime = ($overtimeHours > 0);
            } else {
                $overtimeHours = 0.0;
                $isOvertime = false;
            }

            $oldAssignmentValues = [
                'clock_out_time' => $assignment->clock_out_time?->toDateTimeString(),
                'regular_hours' => (float) $assignment->regular_hours,
                'overtime_hours' => (float) $assignment->overtime_hours,
                'overtime_status' => $assignment->overtime_status,
                'is_overtime' => (bool) $assignment->is_overtime,
            ];

            // Update shift assignment
            $assignment->update([
                'clock_out_time' => $requestedOut,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'is_overtime' => $isOvertime,
                'overtime_status' => $validated['overtime_status'],
            ]);

            // Update status regularisasi
            $regularization->update([
                'status' => 'approved',
                'reviewed_by' => $manager->id,
                'reviewed_at' => now(),
                'review_note' => $validated['review_note'] ?? null,
                'overtime_status' => $validated['overtime_status'],
            ]);

            // Catat ke audit log
            AuditLog::create([
                'company_id' => $manager->company_id,
                'user_id' => $manager->id,
                'action' => 'regularization_approved',
                'model_type' => AttendanceRegularization::class,
                'old_values' => $oldAssignmentValues,
                'new_values' => [
                    'regularization_id' => $regularization->id,
                    'clock_out_time' => $requestedOut->toDateTimeString(),
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'overtime_status' => $validated['overtime_status'],
                    'review_note' => $validated['review_note'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ]);

            // Kirim notifikasi in-app ke karyawan
            NotificationService::send(
                $regularization->employee_id,
                'regularization_approved',
                'Koreksi Kehadiran Disetujui',
                "Pengajuan koreksi jam pulang Anda telah disetujui oleh manajer ({$manager->name}).",
                $manager->company_id
            );
        });

        return redirect()->back()->with('success', 'Pengajuan koreksi kehadiran berhasil disetujui dan data absensi telah diperbarui.');
    }

    /**
     * Menolak pengajuan koreksi kehadiran karyawan.
     */
    public function reject(Request $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $manager = $request->user();

        $this->authorizeManager($manager, $regularization);

        if ($regularization->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'review_note.required' => 'Catatan alasan penolakan wajib diisi.',
            'review_note.min' => 'Catatan penolakan minimal 3 karakter.',
            'review_note.max' => 'Catatan penolakan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($manager, $regularization, $validated, $request) {
            $regularization->update([
                'status' => 'rejected',
                'reviewed_by' => $manager->id,
                'reviewed_at' => now(),
                'review_note' => $validated['review_note'],
            ]);

            AuditLog::create([
                'company_id' => $manager->company_id,
                'user_id' => $manager->id,
                'action' => 'regularization_rejected',
                'model_type' => AttendanceRegularization::class,
                'old_values' => [
                    'status' => 'pending',
                ],
                'new_values' => [
                    'regularization_id' => $regularization->id,
                    'status' => 'rejected',
                    'review_note' => $validated['review_note'],
                ],
                'ip_address' => $request->ip(),
            ]);

            NotificationService::send(
                $regularization->employee_id,
                'regularization_rejected',
                'Koreksi Kehadiran Ditolak',
                "Pengajuan koreksi jam pulang Anda ditolak oleh manajer. Alasan: {$validated['review_note']}",
                $manager->company_id
            );
        });

        return redirect()->back()->with('success', 'Pengajuan koreksi kehadiran berhasil ditolak.');
    }

    /**
     * Memastikan manajer berwenang memproses pengajuan ini dan mencegah self-approval.
     */
    private function authorizeManager(User $manager, AttendanceRegularization $regularization): void
    {
        $regularization->loadMissing('employee');

        abort_unless(
            $regularization->company_id === $manager->company_id,
            403,
            'Akses tidak diizinkan.'
        );

        if (! $manager->is_global_manager) {
            abort_unless(
                $regularization->employee && $regularization->employee->department_id === $manager->department_id,
                403,
                'Anda hanya dapat memproses pengajuan dari departemen yang Anda kelola.'
            );
        }

        abort_if(
            $regularization->employee_id === $manager->id,
            403,
            'Anda tidak dapat menyetujui pengajuan regularisasi kehadiran Anda sendiri.'
        );
    }
}
