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
                    Tukar & Serah Shift
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Ajukan penyerahan tugas (1-arah) atau barter jadwal (2-arah) dengan rekan kerja secara transparan.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                    Karyawan
                </span>
            </div>
        </div>
    </x-slot>

    <div 
        class="space-y-6" 
        x-data="{
            activeTab: 'incoming',
            showCreateModal: false,
            swapType: 'transfer',
            selectedColleagueId: '',
            colleagues: {{ Js::from($colleagues) }},
            get selectedColleague() {
                return this.colleagues.find(c => c.id == this.selectedColleagueId) || null;
            },
            get targetAssignments() {
                return this.selectedColleague ? (this.selectedColleague.shift_assignments || []) : [];
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

        {{-- Action Bar & Tabs Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-brand-border rounded-xl shadow-2xs">
                <button 
                    type="button" 
                    @click="activeTab = 'incoming'"
                    :class="activeTab === 'incoming' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="relative px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="inbox" class="h-4 w-4"></i>
                    <span>Permintaan Masuk</span>
                    @if ($incomingSwaps->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'incoming' ? 'bg-white text-[#6C5CE7]' : 'bg-[#6C5CE7] text-white'">
                            {{ $incomingSwaps->count() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'outgoing'"
                    :class="activeTab === 'outgoing' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="send" class="h-4 w-4"></i>
                    <span>Pengajuan Saya</span>
                    @if ($outgoingSwaps->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'outgoing' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                            {{ $outgoingSwaps->count() }}
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
                    <span>Riwayat</span>
                    @if ($swapHistory->total() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'history' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                            {{ $swapHistory->total() }}
                        </span>
                    @endif
                </button>
            </div>

            <button 
                type="button" 
                @click="showCreateModal = true"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm"
            >
                <i data-lucide="plus" class="h-4 w-4"></i>
                <span>Ajukan Tukar Shift</span>
            </button>
        </div>

        {{-- TAB 1: PERMINTAAN MASUK --}}
        <div x-show="activeTab === 'incoming'" class="space-y-4">
            @forelse ($incomingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $myShift = $swap->targetShiftAssignment?->shift;
                @endphp
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs hover:border-[#6C5CE7]/30 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-2.5">
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

                                @if ($swap->status === 'pending_target')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="h-3 w-3"></i>
                                        Menunggu Persetujuan Anda
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                        Menunggu Manajer
                                    </span>
                                @endif
                            </div>

                            {{-- Visual Detail Swap --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                {{-- Shift Ditawarkan oleh Rekan --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                        <i data-lucide="user" class="h-3.5 w-3.5"></i>
                                        Dari: {{ $swap->requester->name }}
                                    </p>
                                    <p class="font-bold text-[#2D2A3E] text-sm">
                                        {{ $reqShift->title }}
                                    </p>
                                    <p class="text-xs text-[#7C7896] mt-0.5">
                                        {{ $reqShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $reqShift->start_time->format('H:i') }} - {{ $reqShift->end_time->format('H:i') }}
                                    </p>
                                </div>

                                {{-- Shift Anda yang Diminta (Jika Barter) / Status Penerima (Jika Transfer) --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    @if ($isTransfer)
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                            <i data-lucide="arrow-down-left" class="h-3.5 w-3.5 text-purple-600"></i>
                                            Akan Diserahkan Kepada Anda
                                        </p>
                                        <p class="text-xs text-slate-600 mt-1">
                                            Anda akan menjadi penanggung jawab shift ini setelah disetujui manajer.
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                            <i data-lucide="calendar" class="h-3.5 w-3.5 text-indigo-600"></i>
                                            Ditukar Dengan Shift Anda:
                                        </p>
                                        @if ($myShift)
                                            <p class="font-bold text-[#2D2A3E] text-sm">
                                                {{ $myShift->title }}
                                            </p>
                                            <p class="text-xs text-[#7C7896] mt-0.5">
                                                {{ $myShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $myShift->start_time->format('H:i') }} - {{ $myShift->end_time->format('H:i') }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Catatan / Alasan --}}
                            <div class="text-xs text-slate-600 bg-amber-50/50 border border-amber-100 rounded-lg p-2.5">
                                <span class="font-semibold text-amber-900">Alasan Pemohon:</span> "{{ $swap->reason }}"
                            </div>
                        </div>

                        {{-- Action Buttons / Status --}}
                        @if ($swap->status === 'pending_target')
                            <div class="flex items-center gap-2.5 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition"
                                        onclick="return confirm('Apakah Anda yakin ingin menolak permohonan tukar shift ini?')"
                                    >
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        Tolak
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                                    >
                                        <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                        Setujui Swap
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="shield-alert" class="h-3.5 w-3.5 text-blue-600"></i>
                                    Telah Disetujui Rekan &bull; Menunggu Manajer
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="inbox" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Tidak ada permintaan masuk</p>
                    <p class="text-xs text-[#7C7896] mt-1">Saat rekan kerja mengajukan penyerahan atau barter shift kepada Anda, daftarnya akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        {{-- TAB 2: PENGAJUAN SAYA --}}
        <div x-show="activeTab === 'outgoing'" class="space-y-4" style="display: none;">
            @forelse ($outgoingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs hover:border-[#6C5CE7]/30 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
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

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                        Shift Anda:
                                    </p>
                                    <p class="font-bold text-[#2D2A3E] text-sm">{{ $reqShift->title }}</p>
                                    <p class="text-xs text-[#7C7896] mt-0.5">
                                        {{ $reqShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $reqShift->start_time->format('H:i') }} - {{ $reqShift->end_time->format('H:i') }}
                                    </p>
                                </div>

                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                        Tujuan: {{ $swap->targetUser->name }}
                                    </p>
                                    @if ($isTransfer)
                                        <p class="text-xs text-slate-600 mt-1">
                                            Menyerahkan tanggung jawab shift ini kepada rekan kerja.
                                        </p>
                                    @else
                                        @if ($targetShift)
                                            <p class="font-bold text-[#2D2A3E] text-sm">{{ $targetShift->title }}</p>
                                            <p class="text-xs text-[#7C7896] mt-0.5">
                                                {{ $targetShift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $targetShift->start_time->format('H:i') }} - {{ $targetShift->end_time->format('H:i') }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <p class="text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">Alasan:</span> "{{ $swap->reason }}" &bull; Diajukan {{ $swap->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="send" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum ada pengajuan aktif</p>
                    <p class="text-xs text-[#7C7896] mt-1">Gunakan tombol "Ajukan Tukar Shift" di atas untuk menyerahkan atau menukar shift Anda.</p>
                </div>
            @endforelse
        </div>

        {{-- TAB 3: RIWAYAT SWAP --}}
        <div x-show="activeTab === 'history'" class="space-y-4" style="display: none;">
            @forelse ($swapHistory as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $isRequester = $swap->requester_id === auth()->id();
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
                                {{ $isRequester ? 'Anda' : $swap->requester->name }}
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                {{ $swap->target_user_id === auth()->id() ? 'Anda' : $swap->targetUser->name }}
                            </p>

                            <p class="text-xs text-slate-500">
                                Shift: <span class="font-medium text-slate-700">{{ $reqShift->title }}</span>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    ditukar dengan <span class="font-medium text-slate-700">{{ $targetShift->title }}</span>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
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
                    <p class="font-bold text-[#2D2A3E]">Belum ada riwayat pertukaran</p>
                    <p class="text-xs text-[#7C7896] mt-1">Seluruh riwayat pertukaran yang telah disetujui atau ditolak akan tersimpan di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $swapHistory->links() }}
            </div>
        </div>

        {{-- MODAL PENGAJUAN BARU DUAL-MODE --}}
        <div 
            x-show="showCreateModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showCreateModal = false"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-brand-border space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-[#2D2A3E]">Ajukan Tukar / Serah Shift</h3>
                            <p class="text-xs text-[#7C7896] mt-0.5">Pilih mode dan jadwal yang ingin diproses.</p>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('employee.swaps.request') }}" class="space-y-4">
                        @csrf

                        {{-- Mode Selector (Radio Buttons) --}}
                        <div>
                            <label class="block text-xs font-bold text-[#2D2A3E] uppercase tracking-wider mb-2">
                                Mode Pertukaran
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="swapType === 'transfer' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 text-[#6C5CE7] font-semibold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                            <span class="text-xs">Serahkan Saja</span>
                                        </div>
                                        <input type="radio" name="swap_type" value="transfer" x-model="swapType" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="swapType === 'transfer' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'transfer'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">Penyerahan 1-arah ke rekan.</p>
                                </label>

                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="swapType === 'exchange' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 text-[#6C5CE7] font-semibold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                                            <span class="text-xs">Tukar Jadwal</span>
                                        </div>
                                        <input type="radio" name="swap_type" value="exchange" x-model="swapType" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="swapType === 'exchange' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'exchange'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">Barter 2-arah antar shift.</p>
                                </label>
                            </div>
                        </div>

                        {{-- Pilih Shift Saya --}}
                        <div>
                            <label for="shift_assignment_id" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Shift Anda yang Ingin Dialihkan: <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="shift_assignment_id" 
                                name="shift_assignment_id" 
                                required
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white"
                            >
                                <option value="" disabled selected>-- Pilih Shift Anda --</option>
                                @foreach ($myAssignments as $assignment)
                                    <option value="{{ $assignment->id }}">
                                        {{ $assignment->shift->title }} ({{ $assignment->shift->start_time->translatedFormat('d M Y, H:i') }} - {{ $assignment->shift->end_time->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($myAssignments->isEmpty())
                                <p class="text-[11px] text-amber-600 mt-1">Anda belum memiliki penugasan shift aktif mendatang yang dapat ditukar.</p>
                            @endif
                        </div>

                        {{-- Pilih Rekan Kerja --}}
                        <div>
                            <label for="target_user_id" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Rekan Kerja Tujuan: <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="target_user_id" 
                                name="target_user_id" 
                                x-model="selectedColleagueId"
                                required
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white"
                            >
                                <option value="" disabled selected>-- Pilih Rekan Kerja --</option>
                                <template x-for="c in colleagues" :key="c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + (c.position ? c.position.name : 'Staf') + ')'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Mode Exchange: Pilih Shift Rekan yang Diinginkan --}}
                        <div x-show="swapType === 'exchange'" style="display: none;">
                            <label for="target_shift_assignment_id" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Shift Rekan yang Ingin Diambil: <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="target_shift_assignment_id" 
                                name="target_shift_assignment_id" 
                                :required="swapType === 'exchange'"
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white"
                            >
                                <option value="" disabled selected>-- Pilih Shift Rekan Kerja --</option>
                                <template x-for="ta in targetAssignments" :key="ta.id">
                                    <option :value="ta.id" x-text="ta.formatted_label || (ta.shift.title + ' (' + ta.shift.start_time.substring(0, 16) + ')')"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1" x-show="selectedColleagueId && targetAssignments.length === 0">
                                Rekan ini belum memiliki penugasan jadwal aktif yang dapat ditukar.
                            </p>
                        </div>

                        {{-- Alasan Permintaan --}}
                        <div>
                            <label for="reason" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Alasan Permintaan: <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="reason" 
                                name="reason" 
                                rows="3" 
                                required
                                placeholder="Jelaskan kebutuhan pergantian shift (misal: urusan keluarga mendesak, kuliah praktikum, dsb)."
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="showCreateModal = false"
                                class="px-4 py-2.5 text-xs md:text-sm font-semibold rounded-xl text-slate-700 hover:bg-slate-100 transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 text-xs md:text-sm font-semibold rounded-xl text-white bg-brand-primary hover:bg-brand-hover shadow-sm transition"
                            >
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
