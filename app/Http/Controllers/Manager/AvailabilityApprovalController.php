<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityApprovalController extends Controller
{
    /**
     * Menampilkan daftar pengajuan availability
     * dari karyawan dalam department manager.
     */
    public function index(Request $request): View
    {
        $manager = auth()->user();

        $status = $request->input('status', 'pending_manager');

        $query = Availability::with([
            'user.position',
            'user.department',
        ])
            ->where('company_id', $manager->company_id)
            ->where('status', $status)
            ->whereHas('user', function ($query) use ($manager) {
                if (! $manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            })
            ->orderBy('start_time');

        $availabilities = $query->get();

        $pendingCount = Availability::where('company_id', $manager->company_id)
            ->where('status', 'pending_manager')
            ->whereHas('user', function ($query) use ($manager) {
                if (! $manager->is_global_manager) {
                    $query->where('department_id', $manager->department_id);
                }
            })
            ->count();

        return view('manager.availability.index', [
            'availabilities' => $availabilities,
            'pendingCount' => $pendingCount,
            'status' => $status,
        ]);
    }

    /**
     * Menyetujui pengajuan availability.
     */
    public function approve(Availability $availability): RedirectResponse
    {
        $manager = auth()->user();

        $this->authorizeAvailability($manager, $availability);

        if ($availability->status !== 'pending_manager') {
            return redirect()
                ->route('manager.availability.index')
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $availability->update([
            'status' => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('manager.availability.index')
            ->with('success', 'Pengajuan ketersediaan berhasil disetujui.');
    }

    /**
     * Menolak pengajuan availability.
     */
    public function reject(
        Request $request,
        Availability $availability
    ): RedirectResponse {
        $manager = auth()->user();

        $this->authorizeAvailability($manager, $availability);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        if ($availability->status !== 'pending_manager') {
            return redirect()
                ->route('manager.availability.index')
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $availability->update([
            'status' => 'rejected',
            'approved_by' => $manager->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('manager.availability.index')
            ->with('success', 'Pengajuan ketersediaan berhasil ditolak.');
    }

    /**
     * Memastikan manager hanya dapat memproses
     * availability dari department yang menjadi tanggung jawabnya.
     */
    private function authorizeAvailability($manager, Availability $availability): void
    {
        $availability->loadMissing('user');

        abort_unless(
            $availability->company_id === $manager->company_id,
            403
        );

        if (! $manager->is_global_manager) {
            abort_unless(
                $availability->user->department_id === $manager->department_id,
                403
            );
        }
    }
}