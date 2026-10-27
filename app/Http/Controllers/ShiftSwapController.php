<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShiftSwapController extends Controller
{
    /**
     * Tampilan daftar pertukaran shift untuk Karyawan:
     * - Permintaan Masuk (Incoming)
     * - Pengajuan Saya (Outgoing)
     * - Riwayat Pertukaran (History)
     */
    public function indexEmployee(Request $request): View
    {
        $user = auth()->user();

        // Permintaan masuk dari rekan kerja yang butuh respons
        $incomingSwaps = ShiftSwap::with([
            'shiftAssignment.shift.template',
            'targetShiftAssignment.shift.template',
            'requester.position',
            'requester.department',
        ])
            ->where('company_id', $user->company_id)
            ->where('target_user_id', $user->id)
            ->whereIn('status', ['pending_target', 'pending_manager'])
            ->latest()
            ->get();

        // Pengajuan swap yang dibuat oleh user dan masih aktif
        $outgoingSwaps = ShiftSwap::with([
            'shiftAssignment.shift.template',
            'targetShiftAssignment.shift.template',
            'targetUser.position',
            'targetUser.department',
        ])
            ->where('company_id', $user->company_id)
            ->where('requester_id', $user->id)
            ->whereIn('status', ['pending_target', 'pending_manager'])
            ->latest()
            ->get();

        // Riwayat swap selesai (Disetujui / Ditolak)
        $swapHistory = ShiftSwap::with([
            'shiftAssignment.shift.template',
            'targetShiftAssignment.shift.template',
            'requester.position',
            'targetUser.position',
            'approvedBy',
        ])
            ->where('company_id', $user->company_id)
            ->where(function ($query) use ($user) {
                $query->where('requester_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->paginate(10);

        // Penugasan shift milik user yang masih aktif/mendatang dan belum diajukan swap aktif
        $myAssignments = ShiftAssignment::with(['shift.template'])
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) {
                $query->where('start_time', '>=', now()->subHours(2));
            })
            ->whereDoesntHave('shiftSwaps', function ($query) {
                $query->whereIn('status', ['pending_target', 'pending_manager']);
            })
            ->get();

        // Rekan kerja di departemen/perusahaan yang sama untuk tujuan transfer/barter
        $colleagues = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', 'employee')
            ->when($user->department_id, function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->with([
                'position',
                'shiftAssignments' => function ($query) {
                    $query->with('shift.template')
                        ->whereHas('shift', function ($q) {
                            $q->where('start_time', '>=', now()->subHours(2));
                        })
                        ->whereDoesntHave('shiftSwaps', function ($q) {
                            $q->whereIn('status', ['pending_target', 'pending_manager']);
                        });
                },
            ])
            ->orderBy('name')
            ->get();

        $colleagues->each(function ($colleague) {
            $colleague->shiftAssignments->each(function ($sa) {
                if ($sa->shift) {
                    $start = $sa->shift->start_time;
                    $end = $sa->shift->end_time;
                    $sa->formatted_label = $sa->shift->title.' ('.$start->translatedFormat('d M Y, H:i').' - '.$end->format('H:i').')';
                }
            });
        });

        return view('employee.swaps.index', compact(
            'incomingSwaps',
            'outgoingSwaps',
            'swapHistory',
            'myAssignments',
            'colleagues'
        ));
    }

    /**
     * Pengajuan Permintaan Tukar Shift Baru (Dual-Mode: Transfer vs Barter/Exchange)
     */
    public function requestSwap(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'swap_type' => ['required', 'in:transfer,exchange'],
            'shift_assignment_id' => ['required', 'exists:shift_assignments,id'],
            'target_user_id' => ['required', 'exists:users,id'],
            'target_shift_assignment_id' => ['nullable', 'required_if:swap_type,exchange', 'exists:shift_assignments,id'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'swap_type.required' => 'Pilih mode pertukaran shift.',
            'shift_assignment_id.required' => 'Pilih shift Anda yang ingin diserahkan atau ditukar.',
            'target_user_id.required' => 'Pilih rekan kerja tujuan.',
            'target_shift_assignment_id.required_if' => 'Pilih jadwal shift rekan kerja untuk mode pertukaran.',
            'reason.required' => 'Sertakan alasan pertukaran shift.',
        ]);

        // 1. Verifikasi kepemilikan shift pemohon
        $myAssignment = ShiftAssignment::where('id', $validated['shift_assignment_id'])
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $myAssignment) {
            return back()->with('error', 'Penugasan shift yang Anda pilih tidak valid atau bukan milik Anda.');
        }

        // 2. Cegah tukar dengan diri sendiri
        if ((int) $validated['target_user_id'] === (int) $user->id) {
            return back()->with('error', 'Anda tidak dapat melakukan pertukaran shift dengan diri sendiri.');
        }

        // 3. Verifikasi rekan kerja berada di perusahaan yang sama
        $targetUser = User::where('id', $validated['target_user_id'])
            ->where('company_id', $user->company_id)
            ->first();

        if (! $targetUser) {
            return back()->with('error', 'Rekan kerja tujuan tidak valid.');
        }

        // 4. Verifikasi kepemilikan shift rekan kerja jika mode exchange
        $targetAssignment = null;
        if ($validated['swap_type'] === 'exchange') {
            $targetAssignment = ShiftAssignment::where('id', $validated['target_shift_assignment_id'])
                ->where('company_id', $user->company_id)
                ->where('user_id', $targetUser->id)
                ->first();

            if (! $targetAssignment) {
                return back()->with('error', 'Penugasan shift rekan kerja tidak valid atau bukan milik rekan tersebut.');
            }
        }

        // 5. Pencegahan Concurrency & Duplikasi:
        // Cek apakah shift pemohon sudah terlibat dalam swap yang masih aktif (pending_target / pending_manager)
        $isMyAssignmentLocked = ShiftSwap::where(function ($query) use ($myAssignment) {
            $query->where('shift_assignment_id', $myAssignment->id)
                ->orWhere('target_shift_assignment_id', $myAssignment->id);
        })
            ->whereIn('status', ['pending_target', 'pending_manager'])
            ->exists();

        if ($isMyAssignmentLocked) {
            return back()->with('error', 'Shift Anda sedang dalam proses pertukaran yang belum selesai.');
        }

        // Jika mode barter, cek juga apakah shift rekan target sudah terlibat swap aktif
        if ($targetAssignment) {
            $isTargetAssignmentLocked = ShiftSwap::where(function ($query) use ($targetAssignment) {
                $query->where('shift_assignment_id', $targetAssignment->id)
                    ->orWhere('target_shift_assignment_id', $targetAssignment->id);
            })
                ->whereIn('status', ['pending_target', 'pending_manager'])
                ->exists();

            if ($isTargetAssignmentLocked) {
                return back()->with('error', 'Shift rekan kerja yang dipilih sedang dalam proses pertukaran lain.');
            }
        }

        // 6. Buat record swap
        $swap = ShiftSwap::create([
            'company_id' => $user->company_id,
            'shift_assignment_id' => $myAssignment->id,
            'target_shift_assignment_id' => $targetAssignment?->id,
            'swap_type' => $validated['swap_type'],
            'requester_id' => $user->id,
            'target_user_id' => $targetUser->id,
            'reason' => $validated['reason'],
            'status' => 'pending_target',
        ]);

        // 7. Kirim Notifikasi in-app ke rekan target
        $typeLabel = $swap->isTransfer() ? 'penyerahan' : 'pertukaran';
        NotificationService::send(
            $targetUser->id,
            'shift_swap_request',
            'Permintaan Tukar Shift Masuk',
            "{$user->name} mengajukan permintaan {$typeLabel} shift kepada Anda. Silakan tinjau dan konfirmasi.",
            $user->company_id
        );

        return redirect()->route('employee.swaps')->with('success', 'Permintaan tukar shift berhasil diajukan dan menunggu persetujuan rekan kerja.');
    }

    /**
     * Respons Rekan Target: Menyetujui (ke pending_manager) atau Menolak (ke rejected)
     */
    public function respondSwap(Request $request, ShiftSwap $swap): RedirectResponse
    {
        $user = auth()->user();

        // Validasi otorisasi target
        if ($swap->company_id !== $user->company_id || $swap->target_user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk merespons permohonan swap ini.');
        }

        if ($swap->status !== 'pending_target') {
            return back()->with('error', 'Permintaan tukar shift ini sudah tidak dapat direspons.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        if ($validated['action'] === 'accept') {
            $swap->status = 'pending_manager';
            $swap->save();

            // Notifikasi ke pemohon
            NotificationService::send(
                $swap->requester_id,
                'shift_swap_accepted',
                'Tukar Shift Diterima Rekan',
                "{$user->name} telah menyetujui permintaan tukar shift Anda. Sekarang menunggu persetujuan akhir dari Manajer.",
                $swap->company_id
            );

            // Notifikasi ke manajer terkait
            $managers = User::where('company_id', $swap->company_id)
                ->where('role', 'manager')
                ->when($user->department_id, function ($q) use ($user) {
                    $q->where(function ($inner) use ($user) {
                        $inner->where('department_id', $user->department_id)
                            ->orWhere('is_global_manager', true);
                    });
                })
                ->get();

            foreach ($managers as $mgr) {
                NotificationService::send(
                    $mgr->id,
                    'shift_swap_pending_manager',
                    'Persetujuan Tukar Shift Diperlukan',
                    "Permintaan tukar shift antara {$swap->requester->name} dan {$user->name} membutuhkan persetujuan Anda.",
                    $swap->company_id
                );
            }

            return redirect()->route('employee.swaps')->with('success', 'Anda telah menyetujui permintaan tukar shift. Permintaan diteruskan ke manajer.');
        }

        // Jika ditolak oleh rekan kerja
        $swap->status = 'rejected';
        $swap->save();

        NotificationService::send(
            $swap->requester_id,
            'shift_swap_rejected',
            'Tukar Shift Ditolak',
            "{$user->name} telah menolak permintaan tukar shift yang Anda ajukan.",
            $swap->company_id
        );

        return redirect()->route('employee.swaps')->with('info', 'Anda telah menolak permintaan tukar shift tersebut.');
    }

    /**
     * Tampilan daftar permohonan swap untuk Manajer (Menunggu Persetujuan & Riwayat)
     */
    public function indexManager(Request $request): View
    {
        $manager = auth()->user();

        // Swap yang berstatus pending_manager di lingkup departemen manajer
        $pendingSwaps = ShiftSwap::with([
            'shiftAssignment.shift.template',
            'targetShiftAssignment.shift.template',
            'requester.position',
            'requester.department',
            'targetUser.position',
            'targetUser.department',
        ])
            ->where('company_id', $manager->company_id)
            ->whereHas('requester', function ($query) use ($manager) {
                if (! $manager->is_global_manager && $manager->department_id) {
                    $query->where('department_id', $manager->department_id);
                }
            })
            ->where('status', 'pending_manager')
            ->latest()
            ->get();

        // Riwayat swap yang sudah disetujui / ditolak manajer
        $historySwaps = ShiftSwap::with([
            'shiftAssignment.shift.template',
            'targetShiftAssignment.shift.template',
            'requester.position',
            'targetUser.position',
            'approvedBy',
        ])
            ->where('company_id', $manager->company_id)
            ->whereHas('requester', function ($query) use ($manager) {
                if (! $manager->is_global_manager && $manager->department_id) {
                    $query->where('department_id', $manager->department_id);
                }
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->paginate(15);

        return view('manager.swaps.index', compact('pendingSwaps', 'historySwaps'));
    }

    /**
     * Eksekusi transaksi atomik pertukaran / penyerahan penugasan shift dengan lockForUpdate()
     */
    public function approveManager(ShiftSwap $swap): RedirectResponse
    {
        $manager = auth()->user();

        if ($swap->company_id !== $manager->company_id) {
            abort(403, 'Akses tidak sah ke data perusahaan lain.');
        }

        try {
            DB::transaction(function () use ($swap, $manager) {
                // 1. Kunci baris ShiftSwap dengan pessimistic lock
                $lockedSwap = ShiftSwap::where('id', $swap->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSwap->status !== 'pending_manager') {
                    throw new \RuntimeException('Permintaan tukar shift ini tidak lagi dalam status menunggu persetujuan manajer.');
                }

                // 2. Kunci baris ShiftAssignment pemohon
                $requesterAssignment = ShiftAssignment::where('id', $lockedSwap->shift_assignment_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 3. Kunci baris ShiftAssignment target jika mode barter
                $targetAssignment = null;
                if ($lockedSwap->isExchange()) {
                    $targetAssignment = ShiftAssignment::where('id', $lockedSwap->target_shift_assignment_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $oldValues = [
                    'swap_id' => $lockedSwap->id,
                    'swap_type' => $lockedSwap->swap_type,
                    'requester_assignment_id' => $requesterAssignment->id,
                    'requester_assignment_user_id' => $requesterAssignment->user_id,
                    'target_assignment_id' => $targetAssignment?->id,
                    'target_assignment_user_id' => $targetAssignment?->user_id,
                ];

                // 4. Lakukan pertukaran kepemilikan secara atomik
                if ($lockedSwap->isTransfer()) {
                    // Mode 1: Handover / 1-Way Transfer
                    $requesterAssignment->user_id = $lockedSwap->target_user_id;
                    $requesterAssignment->save();
                } else {
                    // Mode 2: Mutual Exchange / 2-Way Barter
                    $requesterAssignment->user_id = $lockedSwap->target_user_id;
                    $requesterAssignment->save();

                    $targetAssignment->user_id = $lockedSwap->requester_id;
                    $targetAssignment->save();
                }

                // 5. Perbarui status Swap
                $lockedSwap->status = 'approved';
                $lockedSwap->approved_by = $manager->id;
                $lockedSwap->approved_at = now();
                $lockedSwap->save();

                // 6. Rekam Audit Log
                AuditLog::create([
                    'company_id' => $manager->company_id,
                    'user_id' => $manager->id,
                    'action' => 'approve_shift_swap',
                    'model_type' => ShiftSwap::class,
                    'old_values' => $oldValues,
                    'new_values' => [
                        'requester_assignment_user_id' => $requesterAssignment->user_id,
                        'target_assignment_user_id' => $targetAssignment?->user_id,
                        'status' => 'approved',
                        'approved_by' => $manager->id,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // 7. Kirim Notifikasi in-app ke kedua belah pihak
                $notifMessage = 'Permintaan '.($lockedSwap->isTransfer() ? 'penyerahan' : 'pertukaran').' shift Anda telah disetujui resmi oleh manajer.';

                NotificationService::send(
                    $lockedSwap->requester_id,
                    'shift_swap_approved',
                    'Tukar Shift Disetujui Manajer',
                    $notifMessage,
                    $lockedSwap->company_id
                );

                NotificationService::send(
                    $lockedSwap->target_user_id,
                    'shift_swap_approved',
                    'Tukar Shift Disetujui Manajer',
                    $notifMessage,
                    $lockedSwap->company_id
                );
            });

            return redirect()->route('manager.swaps.index')->with('success', 'Permintaan tukar shift berhasil disetujui. Penugasan jadwal telah diperbarui secara otomatis.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses persetujuan tukar shift: '.$e->getMessage());
        }
    }

    /**
     * Manajer menolak swap dengan catatan alasan penolakan
     */
    public function rejectManager(Request $request, ShiftSwap $swap): RedirectResponse
    {
        $manager = auth()->user();

        if ($swap->company_id !== $manager->company_id) {
            abort(403, 'Akses tidak sah ke data perusahaan lain.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($swap, $manager, $validated) {
                $lockedSwap = ShiftSwap::where('id', $swap->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSwap->status !== 'pending_manager') {
                    throw new \RuntimeException('Permintaan tukar shift ini tidak lagi dalam status menunggu persetujuan manajer.');
                }

                $lockedSwap->status = 'rejected';
                $lockedSwap->approved_by = $manager->id;
                $lockedSwap->approved_at = now();

                $note = $validated['rejection_reason'] ?? null;
                if (! empty($note)) {
                    $lockedSwap->reason = $lockedSwap->reason.' [Catatan Manajer: '.$note.']';
                }
                $lockedSwap->save();

                // Audit Log
                AuditLog::create([
                    'company_id' => $manager->company_id,
                    'user_id' => $manager->id,
                    'action' => 'reject_shift_swap',
                    'model_type' => ShiftSwap::class,
                    'old_values' => ['status' => 'pending_manager'],
                    'new_values' => [
                        'status' => 'rejected',
                        'approved_by' => $manager->id,
                        'reason' => $lockedSwap->reason,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Notifikasi ke kedua belah pihak
                $reasonText = ! empty($note) ? " Alasan: {$note}" : '';
                NotificationService::send(
                    $lockedSwap->requester_id,
                    'shift_swap_rejected_manager',
                    'Tukar Shift Ditolak Manajer',
                    "Permintaan tukar shift Anda ditolak oleh manajer.{$reasonText}",
                    $lockedSwap->company_id
                );

                NotificationService::send(
                    $lockedSwap->target_user_id,
                    'shift_swap_rejected_manager',
                    'Tukar Shift Ditolak Manajer',
                    "Permintaan tukar shift ditolak oleh manajer.{$reasonText}",
                    $lockedSwap->company_id
                );
            });

            return redirect()->route('manager.swaps.index')->with('info', 'Permintaan tukar shift telah ditolak.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses penolakan: '.$e->getMessage());
        }
    }
}
