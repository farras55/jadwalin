@php
    $statusMap = [
        'pending_target' => ['Menunggu Rekan', 'bg-amber-50 text-amber-800 border-amber-200', 'clock'],
        'pending_manager' => ['Menunggu Manajer', 'bg-[#F5F3FF] text-[#6C5CE7] border-[#E2E0F7]', 'shield-alert'],
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
        {{-- Header Group: Breadcrumb & Page Title --}}
        <div>
            {{-- Breadcrumb Standar Permanen --}}
            <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-[#6C5CE7] font-medium">Persetujuan Tukar Shift</span>
            </div>

            {{-- Header Halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Persetujuan Tukar Shift
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                        Verifikasi dan setujui permohonan pergantian penugasan shift antar staf di departemen Anda.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                        <span>Tinjauan Manajer</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-200 shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span class="text-xs md:text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-4 rounded-xl border border-rose-200 shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                    <span class="text-xs md:text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl border border-blue-200 shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 shrink-0"></i>
                    <span class="text-xs md:text-sm font-medium">{{ session('info') }}</span>
                </div>
            </div>
        @endif

        {{-- Tabs Header Sesuai Desain Acuan --}}
        <div class="flex items-center justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-[#E2E0F7] rounded-2xl shadow-2xs">
                <button 
                    type="button" 
                    @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-xl transition-all flex items-center gap-2"
                >
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>Menunggu Persetujuan</span>
                    @if ($pendingSwaps->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'pending' ? 'bg-white text-[#6C5CE7]' : 'bg-[#6C5CE7] text-white'">
                            {{ $pendingSwaps->count() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-xl transition-all flex items-center gap-2"
                >
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Riwayat Keputusan</span>
                </button>
            </div>
        </div>

        {{-- TAB 1: PENDING MANAGER APPROVALS --}}
        <div x-show="activeTab === 'pending'" class="space-y-4">
            @forelse ($pendingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    $reqInitial = strtoupper(substr($swap->requester->name, 0, 1));
                    $targetInitial = strtoupper(substr($swap->targetUser->name, 0, 1));
                @endphp
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs hover:border-[#6C5CE7]/40 transition-all space-y-4">
                    {{-- Row 1: Dual Person Comparison Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-11 gap-3 items-center">
                        {{-- Pemohon (Requester) --}}
                        <div class="md:col-span-5 bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl p-3.5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6C5CE7] text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                                {{ $reqInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-[#2D2A3E] text-xs md:text-sm truncate leading-tight">
                                    {{ $swap->requester->name }}
                                </p>
                                <p class="text-[11px] text-[#7C7896] truncate mt-0.5">
                                    {{ $swap->requester->position?->name ?? 'Staf' }} &bull; {{ $swap->requester->department?->name ?? '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Middle Exchange Icon --}}
                        <div class="md:col-span-1 flex items-center justify-center py-1 md:py-0">
                            <div class="w-8 h-8 rounded-full bg-[#F5F3FF] border border-[#E2E0F7] text-[#6C5CE7] flex items-center justify-center shadow-2xs">
                                @if ($isTransfer)
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                @else
                                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                                @endif
                            </div>
                        </div>

                        {{-- Penerima / Rekan Barter --}}
                        <div class="md:col-span-5 bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl p-3.5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs border border-emerald-200">
                                {{ $targetInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-[#2D2A3E] text-xs md:text-sm truncate leading-tight">
                                    {{ $swap->targetUser->name }}
                                </p>
                                <p class="text-[11px] text-[#7C7896] truncate mt-0.5">
                                    {{ $swap->targetUser->position?->name ?? 'Staf' }} &bull; {{ $swap->targetUser->department?->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Shift Info, Status Pill & Action Buttons --}}
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pt-1">
                        {{-- Shift Details --}}
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs md:text-sm font-bold text-[#2D2A3E]">
                                        {{ $reqShift->title }}
                                        @if (!$isTransfer && $targetShift)
                                            <span class="text-[#7C7896] font-normal">&bull; Ditukar dengan:</span> {{ $targetShift->title }}
                                        @endif
                                    </p>
                                    @if ($isTransfer)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            Penyerahan 1-Arah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Pertukaran 2-Arah
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-[#7C7896] mt-0.5">
                                    {{ $reqShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $reqShift->start_time->format('H:i') }} - {{ $reqShift->end_time->format('H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Status Pill & Action Buttons --}}
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Menunggu
                            </span>

                            <button 
                                type="button" 
                                @click="openRejectModal('{{ route('manager.swaps.reject', $swap) }}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                            >
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                Tolak
                            </button>

                            <form method="POST" action="{{ route('manager.swaps.approve', $swap) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui pertukaran shift ini? Penugasan jadwal akan otomatis diperbarui secara atomik.')"
                                >
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Setujui & Terapkan
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Row 3: Reason Alert Banner --}}
                    <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3 flex items-start gap-2.5 text-xs text-amber-900">
                        <i data-lucide="info" class="w-4 h-4 text-amber-700 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold">Alasan:</span> "{{ $swap->reason }}"
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Tidak Ada Permohonan yang Menunggu Persetujuan</p>
                    <p class="text-xs text-[#7C7896] mt-1 max-w-sm mx-auto">
                        Saat ada pertukaran shift yang telah disepakati oleh rekan kerja, daftarnya akan muncul di sini untuk ditinjau manajer.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- TAB 2: RIWAYAT KEPUTUSAN --}}
        <div x-show="activeTab === 'history'" class="space-y-4" style="display: none;">
            @forelse ($historySwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="w-3 h-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-xs font-medium text-[#7C7896]">
                                    {{ $isTransfer ? 'Penyerahan 1-Arah' : 'Pertukaran 2-Arah' }}
                                </span>
                            </div>

                            <p class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                                <span>{{ $swap->requester->name }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#7C7896]"></i>
                                <span>{{ $swap->targetUser->name }}</span>
                            </p>

                            <p class="text-xs text-[#7C7896]">
                                Shift: <strong class="text-[#2D2A3E]">{{ $reqShift->title }}</strong>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    &bull; Ditukar dengan: <strong class="text-[#2D2A3E]">{{ $targetShift->title }}</strong>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
                            </p>

                            <p class="text-xs text-[#7C7896] bg-[#F8F7FF] border border-[#E2E0F7] rounded-lg px-3 py-1.5 inline-block">
                                <span class="font-semibold text-[#2D2A3E]">Alasan:</span> "{{ $swap->reason }}"
                            </p>

                            @if ($swap->approved_at)
                                <p class="text-[11px] text-[#7C7896]">
                                    Ditinjau oleh {{ $swap->approvedBy?->name ?? 'Manajer' }} pada {{ $swap->approved_at->translatedFormat('d M Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-right text-[#7C7896] font-medium shrink-0">
                            {{ $swap->created_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="w-6 h-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum Ada Riwayat Permohonan</p>
                    <p class="text-xs text-[#7C7896] mt-1 max-w-sm mx-auto">Riwayat peninjauan manajer akan dicatat di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $historySwaps->links() }}
            </div>
        </div>

        {{-- REJECT REASON MODAL --}}
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
            <div class="fixed inset-0 bg-[#2D2A3E]/60 backdrop-blur-xs" @click="closeRejectModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#E2E0F7] space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E0F7]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#2D2A3E]">Tolak Tukar Shift</h3>
                                <p class="text-xs text-[#7C7896]">Masukkan catatan penolakan untuk staf</p>
                            </div>
                        </div>
                        <button type="button" @click="closeRejectModal()" class="p-1.5 rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="rejectActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="rejection_reason" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Catatan / Alasan Penolakan (Opsional):
                            </label>
                            <textarea 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                x-model="rejectReason"
                                rows="3" 
                                placeholder="Misal: Kurang staf pada slot waktu tersebut, belum memenuhi batasan jam istirahat, dsb."
                                class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] bg-[#F5F3FF]/30 px-3.5 py-2.5 text-[#2D2A3E] placeholder-[#7C7896]/60 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] focus:outline-none transition"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#E2E0F7]">
                            <button 
                                type="button" 
                                @click="closeRejectModal()"
                                class="px-4 py-2.5 text-xs font-semibold rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2.5 text-xs font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-2xs transition"
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
