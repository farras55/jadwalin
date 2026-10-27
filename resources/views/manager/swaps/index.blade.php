@php
    $statusMap = [
        'pending_target' => ['Menunggu Rekan', 'bg-amber-100 text-amber-800 border-amber-200', 'clock'],
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-100 text-blue-800 border-blue-200', 'shield-alert'],
        'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-800 border-red-200', 'x-circle'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Persetujuan Tukar Shift
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Verifikasi dan setujui permohonan pergantian penugasan shift antar staf di departemen Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    Manajer
                </span>
            </div>
        </div>
    </x-slot>

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
    >
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-800 p-4 rounded-xl border border-red-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl border border-blue-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="h-5 w-5 text-blue-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('info') }}</span>
                </div>
            </div>
        @endif

        {{-- Tabs Header --}}
        <div class="flex items-center justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-brand-border rounded-xl shadow-2xs">
                <button 
                    type="button" 
                    @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="clock" class="h-4 w-4"></i>
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
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="history" class="h-4 w-4"></i>
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
                @endphp
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs hover:border-[#6C5CE7]/40 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isTransfer)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                        Penyerahan Shift (1-Arah)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                                        Pertukaran / Barter (2-Arah)
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="user-check" class="h-3 w-3"></i>
                                    Disetujui Rekan Kerja
                                </span>
                            </div>

                            {{-- Visual Dual Mode Comparison Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                {{-- Pemohon --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <div class="w-6 h-6 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] font-bold text-[10px] flex items-center justify-center">
                                            {{ substr($swap->requester->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2A3E] text-xs leading-none">{{ $swap->requester->name }}</p>
                                            <p class="text-[10px] text-[#7C7896]">{{ $swap->requester->position?->name ?? 'Staf' }} &bull; {{ $swap->requester->department?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-200/60">
                                        <p class="text-xs font-semibold text-[#2D2A3E]">{{ $reqShift->title }}</p>
                                        <p class="text-[11px] text-[#7C7896]">
                                            {{ $reqShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $reqShift->start_time->format('H:i') }} - {{ $reqShift->end_time->format('H:i') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Penerima / Rekan Barter --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center">
                                            {{ substr($swap->targetUser->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2A3E] text-xs leading-none">{{ $swap->targetUser->name }}</p>
                                            <p class="text-[10px] text-[#7C7896]">{{ $swap->targetUser->position?->name ?? 'Staf' }} &bull; {{ $swap->targetUser->department?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-200/60">
                                        @if ($isTransfer)
                                            <p class="text-xs text-purple-700 font-medium">Menerima pengalihan shift di samping</p>
                                            <p class="text-[11px] text-slate-500">Akan ditugaskan menggantikan pemohon</p>
                                        @else
                                            @if ($targetShift)
                                                <p class="text-xs font-semibold text-[#2D2A3E]">{{ $targetShift->title }}</p>
                                                <p class="text-[11px] text-[#7C7896]">
                                                    {{ $targetShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $targetShift->start_time->format('H:i') }} - {{ $targetShift->end_time->format('H:i') }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 bg-amber-50/40 border border-amber-200/50 rounded-lg p-2.5">
                                <span class="font-semibold text-amber-900">Alasan:</span> "{{ $swap->reason }}"
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2.5 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <button 
                                type="button" 
                                @click="openRejectModal('{{ route('manager.swaps.reject', $swap) }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition"
                            >
                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                Tolak
                            </button>

                            <form method="POST" action="{{ route('manager.swaps.approve', $swap) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui pertukaran shift ini? Penugasan jadwal akan otomatis diperbarui secara atomik.')"
                                >
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                    Setujui & Terapkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="shield-check" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Tidak ada permohonan yang menunggu persetujuan</p>
                    <p class="text-xs text-[#7C7896] mt-1">Saat ada pertukaran shift yang telah disepakati oleh rekan kerja, daftarnya akan muncul di sini untuk ditinjau manajer.</p>
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
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $isTransfer ? 'Transfer 1-Arah' : 'Barter 2-Arah' }}
                                </span>
                            </div>

                            <p class="text-sm font-bold text-[#2D2A3E]">
                                {{ $swap->requester->name }}
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                {{ $swap->targetUser->name }}
                            </p>

                            <p class="text-xs text-slate-500">
                                Shift: <span class="font-medium text-slate-700">{{ $reqShift->title }}</span>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    &bull; Ditukar dengan <span class="font-medium text-slate-700">{{ $targetShift->title }}</span>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
                            </p>

                            <p class="text-xs text-slate-500">
                                Alasan & Catatan: "{{ $swap->reason }}"
                            </p>

                            @if ($swap->approved_at)
                                <p class="text-[11px] text-slate-400">
                                    Ditinjau oleh {{ $swap->approvedBy?->name ?? 'Manajer' }} pada {{ $swap->approved_at->translatedFormat('d M Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-right text-slate-400">
                            {{ $swap->created_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum ada riwayat permohonan</p>
                    <p class="text-xs text-[#7C7896] mt-1">Riwayat peninjauan manajer akan dicatat di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $historySwaps->links() }}
            </div>
        </div>

        {{-- REJECT REASON MODAL --}}
        <div 
            x-show="showRejectModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeRejectModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-brand-border space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-[#2D2A3E]">Tolak Permohonan Tukar Shift</h3>
                        <button type="button" @click="closeRejectModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-5 w-5"></i>
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
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-red-500 focus:ring-red-500"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="closeRejectModal()"
                                class="px-4 py-2 text-xs md:text-sm font-semibold rounded-xl text-slate-700 hover:bg-slate-100 transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 text-xs md:text-sm font-semibold rounded-xl text-white bg-red-600 hover:bg-red-700 transition"
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
