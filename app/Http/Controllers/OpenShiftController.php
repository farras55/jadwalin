<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OpenShiftController extends Controller
{
    /**
     * Tampilan daftar open shift & antrean klaim untuk Manajer.
     */
    public function indexManager(Request $request): View
    {
        $manager = auth()->user();

        // 1. Shift Terbuka Aktif
        $openShifts = Shift::with(['template', 'department', 'shiftAssignments.user'])
            ->withCount('shiftAssignments')
            ->where('company_id', $manager->company_id)
            ->where('is_open_shift', true)
            ->when(! $manager->is_global_manager && $manager->department_id, function ($q) use ($manager) {
                $q->where('department_id', $manager->department_id);
            })
            ->latest('start_time')
            ->paginate(10, ['*'], 'shifts_page');

        // 2. Antrean Klaim Masuk (Menunggu Persetujuan)
        // Diurutkan berdasarkan mikrodetik (applied_at ASC) untuk FCFS transparency
        $pendingClaims = OpenShiftClaim::with([
            'shift.template',
            'shift.department',
            'user.position',
            'user.department',
        ])
            ->where('company_id', $manager->company_id)
            ->where('status', 'pending_manager')
            ->whereHas('shift', function ($q) use ($manager) {
                if (! $manager->is_global_manager && $manager->department_id) {
                    $q->where('department_id', $manager->department_id);
                }
            })
            ->orderBy('applied_at', 'asc')
            ->get();

        // 3. Riwayat Keputusan Klaim
        $claimHistory = OpenShiftClaim::with([
            'shift.template',
            'user.position',
            'approvedBy',
        ])
            ->where('company_id', $manager->company_id)
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHas('shift', function ($q) use ($manager) {
                if (! $manager->is_global_manager && $manager->department_id) {
                    $q->where('department_id', $manager->department_id);
                }
            })
            ->latest('updated_at')
            ->paginate(15, ['*'], 'history_page');

        $isGlobalManager = (bool) $manager->is_global_manager;

        // 4. Shift Terjadwal yang Sudah Ada tapi Masih Kekurangan Staf (Ada slot kosong)
        $existingUnderstaffedShifts = Shift::with(['template', 'department'])
            ->withCount('shiftAssignments')
            ->where('company_id', $manager->company_id)
            ->when(! $isGlobalManager && $manager->department_id, function ($q) use ($manager) {
                $q->where('department_id', $manager->department_id);
            })
            ->where('start_time', '>', now())
            ->where('is_open_shift', false)
            ->whereRaw('(SELECT COUNT(*) FROM shift_assignments WHERE shift_assignments.shift_id = shifts.id) < shifts.quota')
            ->orderBy('start_time', 'asc')
            ->get();

        $existingUnderstaffedShifts->each(function ($s) {
            $remaining = max(0, $s->quota - $s->shift_assignments_count);
            $deptPrefix = $s->department ? "[{$s->department->name}] " : '[Global] ';
            $s->formatted_option = $deptPrefix.$s->title.' ('.$s->start_time->translatedFormat('d M Y, H:i').' - '.$s->end_time->format('H:i').") — Terisi: {$s->shift_assignments_count}/{$s->quota} (Kurang {$remaining} Staf)";
            $s->remaining_slots = $remaining;
            $s->dept_id = $s->department_id;
        });

        // Data pendukung form broadcast
        $templates = ShiftTemplate::where('company_id', $manager->company_id)->get();
        $departments = Department::where('company_id', $manager->company_id)
            ->when(! $isGlobalManager && $manager->department_id, function ($q) use ($manager) {
                $q->where('id', $manager->department_id);
            })
            ->orderBy('name')
            ->get();

        return view('manager.shifts.open', compact(
            'openShifts',
            'pendingClaims',
            'claimHistory',
            'templates',
            'departments',
            'existingUnderstaffedShifts',
            'isGlobalManager'
        ));
    }

    /**
     * Manajer mempublikasikan (broadcast) Open Shift:
     * Opsi 1: Shift tambahan baru.
     * Opsi 2: Shift yang sudah ada namun masih kekurangan karyawan (slot kosong).
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        $isGlobalManager = (bool) $manager->is_global_manager;
        $broadcastSource = $request->input('broadcast_source', 'new');

        // ========================================================
        // OPSI A: SHIFT YANG SUDAH ADA & KEKURANGAN KARYAWAN
        // ========================================================
        if ($broadcastSource === 'existing') {
            $validated = $request->validate([
                'existing_shift_id' => ['required', 'exists:shifts,id'],
                'requires_approval' => ['required', 'boolean'],
                'open_slots' => ['nullable', 'integer', 'min:1', 'max:100'],
                'quota' => ['nullable', 'integer', 'min:1', 'max:100'],
                'is_global_broadcast' => ['nullable', 'boolean'],
            ], [
                'existing_shift_id.required' => 'Pilih shift yang ingin dibuka kuotanya.',
                'requires_approval.required' => 'Pilih metode alokasi (Instant FCFS atau Approval).',
                'open_slots.min' => 'Jumlah slot terbuka minimal 1.',
            ]);

            $shift = Shift::withCount('shiftAssignments')
                ->where('id', $validated['existing_shift_id'])
                ->where('company_id', $manager->company_id)
                ->firstOrFail();

            if (! $isGlobalManager && (int) $shift->department_id !== (int) $manager->department_id) {
                return back()->with('error', 'Anda tidak memiliki hak akses untuk shift di departemen lain.');
            }

            if ($shift->start_time <= now()) {
                return back()->with('error', 'Shift yang dipilih sudah dimulai atau telah lewat.');
            }

            $isGlobal = $isGlobalManager && $request->boolean('is_global_broadcast');

            // Hitung kuota baru berdasarkan slot terbuka yang diinginkan manajer:
            // Jika manajer mengisi open_slots: kuota total = staf terisi saat ini + open_slots
            if (! empty($validated['open_slots'])) {
                $slotsNeeded = (int) $validated['open_slots'];
                $newQuota = $shift->shift_assignments_count + $slotsNeeded;
            } elseif (! empty($validated['quota'])) {
                // Kompatibilitas jika dikirim 'quota' total:
                $rawQuota = (int) $validated['quota'];
                $newQuota = max($shift->shift_assignments_count + 1, $rawQuota);
                $slotsNeeded = max(1, $newQuota - $shift->shift_assignments_count);
            } else {
                // Default: gunakan slot kosong yang ada saat ini
                $slotsNeeded = max(1, $shift->quota - $shift->shift_assignments_count);
                $newQuota = $shift->shift_assignments_count + $slotsNeeded;
            }

            $shift->update([
                'is_open_shift' => true,
                'is_global_broadcast' => $isGlobal,
                'requires_approval' => (bool) $validated['requires_approval'],
                'quota' => $newQuota,
            ]);

            $remaining = max(0, $newQuota - $shift->shift_assignments_count);

            // Catat Audit Log
            AuditLog::create([
                'company_id' => $manager->company_id,
                'user_id' => $manager->id,
                'action' => 'broadcast_existing_shift',
                'model_type' => Shift::class,
                'new_values' => [
                    'shift_id' => $shift->id,
                    'title' => $shift->title,
                    'quota' => $shift->quota,
                    'vacant_slots' => $remaining,
                    'is_global_broadcast' => $shift->is_global_broadcast,
                    'requires_approval' => $shift->requires_approval,
                ],
                'ip_address' => $request->ip(),
            ]);

            // Broadcast Notifikasi In-App
            if ($isGlobal) {
                $employees = User::where('company_id', $manager->company_id)
                    ->where('role', 'employee')
                    ->get();
            } else {
                $employees = User::where('company_id', $manager->company_id)
                    ->where('department_id', $shift->department_id)
                    ->where('role', 'employee')
                    ->get();
            }

            $scopeLabel = $isGlobal ? 'secara GLOBAL (Semua Departemen)' : 'departemen';
            foreach ($employees as $employee) {
                NotificationService::send(
                    $employee->id,
                    'open_shift_broadcast',
                    'Shift Terbuka Tersedia',
                    "Slot kosong dibuka {$scopeLabel} untuk shift '{$shift->title}' (Sisa {$remaining} slot). Segera klaim!",
                    $shift->company_id
                );
            }

            $targetMsg = $isGlobal ? 'ke seluruh karyawan perusahaan (Global Broadcast)' : 'ke staf departemen';

            return redirect()->route('manager.open-shifts.index')
                ->with('success', "Shift '{$shift->title}' berhasil dibuka untuk open shift dengan sisa {$remaining} slot {$targetMsg}.");
        }

        // ========================================================
        // OPSI B: SHIFT TAMBAHAN / BARU
        // ========================================================
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'department_id' => ['nullable'],
            'template_id' => ['nullable', 'exists:shift_templates,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'quota' => ['required', 'integer', 'min:1', 'max:100'],
            'requires_approval' => ['required', 'boolean'],
            'is_holiday' => ['nullable', 'boolean'],
            'is_global_broadcast' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul shift wajib diisi.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.after' => 'Waktu mulai shift harus berada di masa mendatang.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'quota.required' => 'Tentukan kuota staf yang dibutuhkan.',
            'quota.min' => 'Kuota minimal 1 staf.',
            'requires_approval.required' => 'Pilih metode alokasi (Instant FCFS atau Approval).',
        ]);

        $isGlobal = false;
        $departmentId = null;

        if ($isGlobalManager) {
            $inputDept = $request->input('department_id');
            $wantsGlobal = ($inputDept === 'global' || $request->boolean('is_global_broadcast'));

            if ($wantsGlobal) {
                $isGlobal = true;
                $departmentId = ($inputDept !== 'global' && ! empty($inputDept)) ? (int) $inputDept : null;
            } else {
                if (empty($inputDept) || $inputDept === 'global') {
                    return back()->with('error', 'Pilih departemen yang valid untuk open shift atau pilih Global Broadcast.');
                }
                $departmentId = (int) $inputDept;
            }
        } else {
            // Manager departemen hanya bisa untuk departemen miliknya
            $departmentId = $manager->department_id;
            $isGlobal = false;
        }

        $shift = Shift::create([
            'company_id' => $manager->company_id,
            'department_id' => $departmentId,
            'template_id' => $validated['template_id'] ?? null,
            'title' => $validated['title'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'quota' => $validated['quota'],
            'is_open_shift' => true,
            'is_global_broadcast' => $isGlobal,
            'requires_approval' => (bool) $validated['requires_approval'],
            'is_holiday' => (bool) ($validated['is_holiday'] ?? false),
            'status' => 'published',
        ]);

        // Catat Audit Log
        AuditLog::create([
            'company_id' => $manager->company_id,
            'user_id' => $manager->id,
            'action' => 'broadcast_open_shift',
            'model_type' => Shift::class,
            'new_values' => [
                'shift_id' => $shift->id,
                'title' => $shift->title,
                'department_id' => $shift->department_id,
                'is_global_broadcast' => $shift->is_global_broadcast,
                'quota' => $shift->quota,
                'requires_approval' => $shift->requires_approval,
            ],
            'ip_address' => $request->ip(),
        ]);

        // Broadcast Notifikasi In-App
        if ($isGlobal) {
            $employees = User::where('company_id', $manager->company_id)
                ->where('role', 'employee')
                ->get();
        } else {
            $employees = User::where('company_id', $manager->company_id)
                ->where('department_id', $shift->department_id)
                ->where('role', 'employee')
                ->get();
        }

        $scopeMsg = $isGlobal ? 'secara GLOBAL ke seluruh departemen' : 'untuk departemen';
        foreach ($employees as $employee) {
            NotificationService::send(
                $employee->id,
                'open_shift_broadcast',
                'Shift Terbuka Tersedia',
                "Shift terbuka baru '{$shift->title}' ({$shift->quota} slot) telah dipublikasikan {$scopeMsg}. Segera klaim sebelum kuota habis!",
                $shift->company_id
            );
        }

        $targetMsg = $isGlobal ? 'ke seluruh karyawan perusahaan (Global Broadcast)' : 'ke staf departemen';

        return redirect()->route('manager.open-shifts.index')
            ->with('success', "Open shift '{$shift->title}' dengan kuota {$shift->quota} slot berhasil dipublikasikan {$targetMsg}.");
    }

    /**
     * Manajer menutup pendaftaran Open Shift.
     */
    public function closeBroadcast(Shift $shift): RedirectResponse
    {
        $manager = auth()->user();

        if ($shift->company_id !== $manager->company_id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! $manager->is_global_manager && (int) $shift->department_id !== (int) $manager->department_id) {
            abort(403, 'Anda tidak memiliki hak akses ke departemen ini.');
        }

        $shift->update(['is_open_shift' => false]);

        return back()->with('info', "Pendaftaran terbuka untuk shift '{$shift->title}' telah ditutup.");
    }

    /**
     * Tampilan Open Shift untuk Karyawan (Daftar Tersedia & Riwayat Klaim).
     */
    public function availableShifts(Request $request): View
    {
        $user = auth()->user();

        // 1. Shift Terbuka yang dapat diklaim
        // - Perusahaan sama
        // - Bersifat Global Broadcast ATAU sesuai departemen user
        // - is_open_shift = true & start_time di masa depan
        // - Kuota belum terpenuhi oleh penugasan aktif
        // - Belum pernah diklaim atau ditugaskan ke user ini
        $availableShifts = Shift::with(['template', 'department', 'shiftAssignments'])
            ->withCount('shiftAssignments')
            ->where('company_id', $user->company_id)
            ->where('is_open_shift', true)
            ->where('start_time', '>', now())
            ->where(function ($q) use ($user) {
                $q->where('is_global_broadcast', true);
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                } else {
                    $q->orWhereNull('department_id');
                }
            })
            ->whereRaw('(SELECT COUNT(*) FROM shift_assignments WHERE shift_assignments.shift_id = shifts.id) < shifts.quota')
            ->whereDoesntHave('shiftAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereDoesntHave('openShiftClaims', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest('start_time')
            ->get();

        // 2. Riwayat Pengajuan Klaim Karyawan
        $myClaims = OpenShiftClaim::with(['shift.template', 'shift.department', 'approvedBy'])
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->latest('applied_at')
            ->paginate(10);

        return view('employee.shifts.open', compact('availableShifts', 'myClaims'));
    }

    /**
     * Eksekusi klaim Open Shift oleh Karyawan (Transaksi Atomik dengan Pessimistic Lock).
     */
    public function claim(Request $request, Shift $shift): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Validasi batasan organisasi
        if ($shift->company_id !== $user->company_id) {
            abort(403, 'Akses tidak sah ke data perusahaan lain.');
        }

        // Jika bukan global broadcast, batasi hanya untuk staf di departemen shift tersebut
        if (! $shift->is_global_broadcast && $user->department_id && (int) $shift->department_id !== (int) $user->department_id) {
            abort(403, 'Anda hanya dapat mengklaim shift pada departemen Anda.');
        }

        $nowMicro = Carbon::now();
        $appliedAt = $nowMicro->format('Y-m-d H:i:s.u');
        $isInstant = false;

        try {
            DB::transaction(function () use ($shift, $user, $appliedAt, &$isInstant) {
                // 1. Kunci baris Shift dengan Pessimistic Locking (lockForUpdate)
                $lockedShift = Shift::where('id', $shift->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Verifikasi status open shift
                if (! $lockedShift->is_open_shift) {
                    abort(409, 'Pendaftaran shift terbuka ini sudah ditutup.');
                }

                if ($lockedShift->start_time <= now()) {
                    abort(409, 'Waktu pendaftaran shift terbuka ini telah berakhir.');
                }

                // 3. Cegah klaim ganda oleh karyawan yang sama
                $alreadyAssigned = ShiftAssignment::where('shift_id', $lockedShift->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyAssigned) {
                    abort(422, 'Anda sudah ditugaskan pada shift ini.');
                }

                $alreadyClaimed = OpenShiftClaim::where('shift_id', $lockedShift->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyClaimed) {
                    abort(422, 'Anda sudah mengajukan klaim untuk shift ini.');
                }

                // 4. Hitung kuota yang telah terisi (ShiftAssignment aktif)
                $currentAssignmentsCount = ShiftAssignment::where('shift_id', $lockedShift->id)->count();

                if ($currentAssignmentsCount >= $lockedShift->quota) {
                    // Kuota habis karena persaingan konkurensi (Race Condition)
                    abort(409, 'Kuota shift terbuka ini sudah penuh.');
                }

                $isInstant = ! $lockedShift->requires_approval;

                if ($isInstant) {
                    // Cabang A: Instant FCFS (Langsung terkonfirmasi)
                    ShiftAssignment::create([
                        'company_id' => $lockedShift->company_id,
                        'shift_id' => $lockedShift->id,
                        'user_id' => $user->id,
                        'status' => 'scheduled',
                    ]);

                    OpenShiftClaim::create([
                        'company_id' => $lockedShift->company_id,
                        'shift_id' => $lockedShift->id,
                        'user_id' => $user->id,
                        'status' => 'approved',
                        'applied_at' => $appliedAt,
                        'approved_by' => null,
                    ]);

                    // Jika kuota sudah penuh setelah klaim ini, tutup open shift
                    if (($currentAssignmentsCount + 1) >= $lockedShift->quota) {
                        $lockedShift->update(['is_open_shift' => false]);
                    }
                } else {
                    // Cabang B: Membutuhkan Approval Manajer
                    OpenShiftClaim::create([
                        'company_id' => $lockedShift->company_id,
                        'shift_id' => $lockedShift->id,
                        'user_id' => $user->id,
                        'status' => 'pending_manager',
                        'applied_at' => $appliedAt,
                        'approved_by' => null,
                    ]);
                }
            });
        } catch (HttpException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('error', $e->getMessage());
        }

        if ($isInstant) {
            NotificationService::send(
                $user->id,
                'open_shift_claimed',
                'Klaim Shift Berhasil',
                "Anda berhasil mengklaim shift '{$shift->title}'. Jadwal telah ditambahkan ke kalender kerja Anda.",
                $shift->company_id
            );

            $msg = 'Selamat! Shift terbuka berhasil Anda klaim secara instan dan masuk ke jadwal kerja Anda.';
        } else {
            // Beri tahu manajer yang berwenang
            $managers = User::where('company_id', $shift->company_id)
                ->where('role', 'manager')
                ->where(function ($q) use ($shift) {
                    if ($shift->department_id) {
                        $q->where('department_id', $shift->department_id);
                    }
                    $q->orWhere('is_global_manager', true);
                })
                ->get();

            foreach ($managers as $mgr) {
                NotificationService::send(
                    $mgr->id,
                    'open_shift_pending_approval',
                    'Klaim Shift Terbuka Baru',
                    "Karyawan {$user->name} mengajukan klaim untuk open shift '{$shift->title}'.",
                    $shift->company_id
                );
            }

            $msg = 'Permohonan klaim shift berhasil diajukan dan sedang menunggu persetujuan manajer.';
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg], 200);
        }

        return redirect()->route('employee.open-shifts')->with('success', $msg);
    }

    /**
     * Manajer menyetujui klaim Open Shift.
     */
    public function approveClaim(OpenShiftClaim $claim): RedirectResponse
    {
        $manager = auth()->user();

        if ($claim->company_id !== $manager->company_id) {
            abort(403, 'Akses tidak sah.');
        }

        try {
            DB::transaction(function () use ($claim, $manager) {
                $lockedClaim = OpenShiftClaim::where('id', $claim->id)->lockForUpdate()->firstOrFail();
                $lockedShift = Shift::where('id', $lockedClaim->shift_id)->lockForUpdate()->firstOrFail();

                if ($lockedClaim->status !== 'pending_manager') {
                    abort(422, 'Permohonan klaim ini sudah tidak dalam status menunggu persetujuan.');
                }

                $currentAssignments = ShiftAssignment::where('shift_id', $lockedShift->id)->count();

                if ($currentAssignments >= $lockedShift->quota) {
                    $lockedClaim->update([
                        'status' => 'rejected',
                        'approved_by' => $manager->id,
                    ]);
                    abort(409, 'Kuota shift terbuka ini sudah penuh. Permohonan ditolak.');
                }

                // Terbitkan penugasan jadwal
                ShiftAssignment::create([
                    'company_id' => $lockedShift->company_id,
                    'shift_id' => $lockedShift->id,
                    'user_id' => $lockedClaim->user_id,
                    'status' => 'scheduled',
                ]);

                $lockedClaim->update([
                    'status' => 'approved',
                    'approved_by' => $manager->id,
                ]);

                // Jika kuota sudah genap terpenuhi, tutup broadcast
                if (($currentAssignments + 1) >= $lockedShift->quota) {
                    $lockedShift->update(['is_open_shift' => false]);
                }
            });
        } catch (HttpException $e) {
            return back()->with('error', $e->getMessage());
        }

        NotificationService::send(
            $claim->user_id,
            'open_shift_approved',
            'Klaim Shift Disetujui',
            "Permohonan Anda untuk shift '{$claim->shift->title}' telah disetujui manajer.",
            $claim->company_id
        );

        return redirect()->route('manager.open-shifts.index')
            ->with('success', "Klaim dari {$claim->user->name} berhasil disetujui dan jadwal telah ditugaskan.");
    }

    /**
     * Manajer menolak klaim Open Shift.
     */
    public function rejectClaim(Request $request, OpenShiftClaim $claim): RedirectResponse
    {
        $manager = auth()->user();

        if ($claim->company_id !== $manager->company_id) {
            abort(403, 'Akses tidak sah.');
        }

        $claim->update([
            'status' => 'rejected',
            'approved_by' => $manager->id,
        ]);

        NotificationService::send(
            $claim->user_id,
            'open_shift_rejected',
            'Klaim Shift Ditolak',
            "Permohonan klaim shift Anda untuk '{$claim->shift->title}' tidak disetujui manajer.",
            $claim->company_id
        );

        return redirect()->route('manager.open-shifts.index')
            ->with('info', "Klaim dari {$claim->user->name} telah ditolak.");
    }
}
