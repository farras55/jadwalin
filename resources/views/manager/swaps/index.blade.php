@php
    $statusMap = [
        'pending_target' => ['Menunggu Rekan', 'bg-amber-50 text-amber-800 border-amber-200', 'clock'],
        'pending_manager' => ['Menunggu Manajer', 'bg-indigo-50 text-indigo-700 border-indigo-200', 'shield-alert'],
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-800 border-rose-200', 'x-circle'],
    ];
@endphp

<x-app-layout>
    <div 
        class="space-y-6" 
        x-data="{
            activeTab: 'pending',
            showRejectModal: false,
            rejectActionUrl: '',
            rejectReason: '',
            openRejectModal(actionUrl) {
                this.rejectActionUrl = actionUrl;
                this.rejectReason = '';
                this.showRejectModal = true;
            },
            closeRejectModal() {
                this.showRejectModal = false;
                this.rejectActionUrl = '';
                this.rejectReason = '';
            }
        }"
        @keydown.escape.window="closeRejectModal()"
    >
        {{-- ----------------------------------------------------------------- --}}
        {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                           --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <span>Manager</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-900 font-medium">Persetujuan Tukar Shift</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Persetujuan Tukar Shift
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Verifikasi dan setujui permohonan barter atau penyerahan penugasan shift antar staf departemen.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 shadow-card">
                    <span class="h-2 w-2 rounded-full {{ $pendingSwaps->count() > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                    <span>Menunggu Review:</span>
                    <strong class="font-mono text-slate-900 font-bold">{{ $pendingSwaps->count() }}</strong>
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 shadow-card flex items-center gap-3 text-xs sm:text-sm">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 shadow-card flex items-center gap-3 text-xs sm:text-sm">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-3.5 rounded-xl border border-blue-200 shadow-card flex items-center gap-3 text-xs sm:text-sm">
                <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- 2. TABS NAVIGASI (MENUNGGU & RIWAYAT)                             --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="flex items-center justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-slate-200/90 rounded-lg text-xs shadow-card">
                <button 
                    type="button" 
                    @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-indigo-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-md transition flex items-center gap-2"
                >
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span>Menunggu Persetujuan</span>
                    @if ($pendingSwaps->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold font-mono"
                              :class="activeTab === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                            {{ $pendingSwaps->count() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-indigo-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-md transition flex items-center gap-2"
                >
                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                    <span>Riwayat Keputusan</span>
                </button>
            </div>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 3. TAB 1: PENDING MANAGER APPROVALS                               --}}
        {{-- ----------------------------------------------------------------- --}}
        <div x-show="activeTab === 'pending'" class="space-y-4">
            @forelse ($pendingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    $reqInitial = strtoupper(substr($swap->requester->name, 0, 1));
                    $targetInitial = strtoupper(substr($swap->targetUser->name, 0, 1));
                @endphp
                <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-card hover:border-slate-300 transition space-y-3.5">
                    
                    {{-- Row 1: Dual Person Comparison Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-11 gap-2.5 items-center">
                        {{-- Pemohon (Requester) --}}
                        <div class="md:col-span-5 bg-slate-50/70 border border-slate-200/80 rounded-lg p-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $reqInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm truncate leading-tight">
                                        {{ $swap->requester->name }}
                                    </p>
                                    @php
                                        $rType = $swap->requester->employment_type ?? 'full_time';
                                        $rLabel = match ($rType) {
                                            'full_time' => 'FT',
                                            'part_time' => 'PT',
                                            default => 'FL',
                                        };
                                    @endphp
                                    <span class="text-[9px] font-bold uppercase border px-1.5 py-0.2 rounded bg-white text-slate-600 border-slate-200" title="{{ $rType }}">
                                        {{ $rLabel }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                    {{ $swap->requester->position?->name ?? 'Staf' }} &bull; {{ $swap->requester->department?->name ?? '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Middle Exchange Icon --}}
                        <div class="md:col-span-1 flex items-center justify-center py-1 md:py-0">
                            <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center">
                                @if ($isTransfer)
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                @else
                                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                                @endif
                            </div>
                        </div>

                        {{-- Penerima / Rekan Barter --}}
                        <div class="md:col-span-5 bg-slate-50/70 border border-slate-200/80 rounded-lg p-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $targetInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm truncate leading-tight">
                                        {{ $swap->targetUser->name }}
                                    </p>
                                    @php
                                        $tType = $swap->targetUser->employment_type ?? 'full_time';
                                        $tLabel = match ($tType) {
                                            'full_time' => 'FT',
                                            'part_time' => 'PT',
                                            default => 'FL',
                                        };
                                    @endphp
                                    <span class="text-[9px] font-bold uppercase border px-1.5 py-0.2 rounded bg-white text-slate-600 border-slate-200" title="{{ $tType }}">
                                        {{ $tLabel }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                    {{ $swap->targetUser->position?->name ?? 'Staf' }} &bull; {{ $swap->targetUser->department?->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Shift Info & Action Buttons --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                        {{-- Shift Details --}}
                        <div class="flex items-center gap-2.5 text-xs text-slate-700">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <div>
                                <span class="font-semibold text-slate-900">{{ $reqShift->title }}</span>
                                @if (!$isTransfer && $targetShift)
                                    <span class="text-slate-400">&bull; Ditukar dengan:</span> <span class="font-semibold text-slate-900">{{ $targetShift->title }}</span>
                                @endif
                                <span class="text-slate-400 text-[11px] ml-1">
                                    ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }} - {{ $reqShift->end_time->format('H:i') }})
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold border {{ $isTransfer ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $isTransfer ? '1-Arah' : '2-Arah' }}
                            </span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                            <button 
                                type="button" 
                                @click="openRejectModal('{{ route('manager.swaps.reject', $swap) }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                            >
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Tolak</span>
                            </button>

                            <form method="POST" action="{{ route('manager.swaps.approve', $swap) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs shadow-indigo-600/20 active:translate-y-px transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui pertukaran shift ini? Penugasan jadwal akan otomatis diperbarui secara atomik.')"
                                >
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Setujui</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Row 3: Reason Alert Banner --}}
                    <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-2.5 flex items-start gap-2 text-xs text-slate-700">
                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-slate-900">Alasan Staf:</span> &ldquo;{{ $swap->reason }}&rdquo;
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-10 text-center shadow-card">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2.5">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </div>
                    <p class="font-semibold text-xs text-slate-800">Tidak Ada Permohonan Menunggu</p>
                    <p class="text-xs text-slate-400 mt-0.5 max-w-sm mx-auto">
                        Saat ada pertukaran shift yang telah disepakati oleh rekan kerja, antreannya akan muncul di sini untuk ditinjau.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 4. TAB 2: RIWAYAT KEPUTUSAN                                       --}}
        {{-- ----------------------------------------------------------------- --}}
        <div x-show="activeTab === 'history'" class="space-y-3" style="display: none;">
            @forelse ($historySwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-card space-y-2.5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="w-3 h-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-400">
                                    {{ $isTransfer ? 'Penyerahan 1-Arah' : 'Pertukaran 2-Arah' }}
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm font-semibold text-slate-900 flex items-center gap-2">
                                <span>{{ $swap->requester->name }}</span>
                                <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400"></i>
                                <span>{{ $swap->targetUser->name }}</span>
                            </p>

                            <p class="text-xs text-slate-500">
                                Shift: <strong class="text-slate-800">{{ $reqShift->title }}</strong>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    &bull; Ditukar dengan: <strong class="text-slate-800">{{ $targetShift->title }}</strong>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
                            </p>

                            <p class="text-xs text-slate-600 bg-slate-50 border border-slate-200/60 rounded px-2.5 py-1 inline-block mt-1">
                                <span class="font-medium text-slate-800">Alasan:</span> &ldquo;{{ $swap->reason }}&rdquo;
                            </p>

                            @if ($swap->approved_at)
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Ditinjau oleh {{ $swap->approvedBy?->name ?? 'Manajer' }} pada {{ $swap->approved_at->translatedFormat('d M Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-right text-slate-400 font-mono shrink-0">
                            {{ $swap->created_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-10 text-center shadow-card">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2.5">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <p class="font-semibold text-xs text-slate-800">Belum Ada Riwayat Permohonan</p>
                    <p class="text-xs text-slate-400 mt-0.5 max-w-sm mx-auto">Riwayat peninjauan manajer akan dicatat di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $historySwaps->links() }}
            </div>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 5. REJECT REASON MODAL (CLEAN & SLEEK)                            --}}
        {{-- ----------------------------------------------------------------- --}}
        <div 
            x-show="showRejectModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="closeRejectModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-xl max-w-md w-full p-5 shadow-xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Tolak Permohonan Tukar Shift</h3>
                                <p class="text-xs text-slate-500">Berikan catatan penolakan untuk karyawan.</p>
                            </div>
                        </div>
                        <button type="button" @click="closeRejectModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form :action="rejectActionUrl" method="POST" class="space-y-3.5">
                        @csrf
                        <div>
                            <label for="rejection_reason" class="block text-xs font-semibold text-slate-800 mb-1">
                                Catatan / Alasan Penolakan (Opsional):
                            </label>
                            <textarea 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                x-model="rejectReason"
                                rows="3" 
                                placeholder="Misal: Kurang staf pada slot waktu tersebut, melebihi kuota jam kerja, dsb."
                                class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="closeRejectModal()"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition"
                            >
                                Konfirmasi Penolakan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
