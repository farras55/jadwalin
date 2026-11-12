@php
    $statusMap = [
        'pending_target' => ['Menunggu Rekan', 'bg-amber-100 text-amber-800 border-amber-200', 'clock'],
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-100 text-blue-800 border-blue-200', 'shield-alert'],
        'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-800 border-red-200', 'x-circle'],
    ];

    $pendingActionCount = $incomingSwaps->where('status', 'pending_target')->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <span class="text-[#6C5CE7] font-medium">Tukar &amp; Serah Shift</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Tukar &amp; Serah Shift
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Pusat pengelolaan permohonan barter shift (2-arah) dan penyerahan tugas (1-arah) antar rekan kerja.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                    Tukar Shift
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-5"
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
        @open-swap-modal.window="showCreateModal = true"
    >
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-200 shadow-sm flex items-center gap-2.5">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-800 p-4 rounded-xl border border-red-200 shadow-sm flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl border border-blue-200 shadow-sm flex items-center gap-2.5">
                <i data-lucide="info" class="h-5 w-5 text-blue-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('info') }}</span>
            </div>
        @endif

        {{-- =====================================================
             REQUEST MANAGEMENT ACTION BAR & STATUS STRIP
        ====================================================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                {{-- Left: Tab Switcher --}}
                <div class="inline-flex p-1 bg-[#F5F3FF]/70 border border-[#E2E0F7] rounded-xl shadow-2xs">
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
                        <span>Riwayat Swap</span>
                        @if ($swapHistory->total() > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="activeTab === 'history' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                                {{ $swapHistory->total() }}
                            </span>
                        @endif
                    </button>
                </div>

                {{-- Right: Status indicator pill --}}
                <div class="flex items-center gap-3">
                    @if ($pendingActionCount > 0)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 rounded-xl border border-amber-200 text-xs font-semibold text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>{{ $pendingActionCount }} permintaan menunggu tindakan Anda</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 rounded-xl border border-emerald-200 text-xs font-semibold text-emerald-800">
                            <i data-lucide="check" class="h-3.5 w-3.5 text-emerald-600"></i>
                            <span>Semua permohonan masuk telah direspons</span>
                        </div>
                    @endif

                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs md:text-sm font-semibold text-white bg-[#6C5CE7] hover:bg-[#5A4AD1] transition-all shadow-sm cursor-pointer"
                    >
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        <span>Ajukan Tukar Shift</span>
                    </button>
                </div>

            </div>
        </div>

        {{-- =====================================================
             TAB 1: PERMINTAAN MASUK (INBOX UTAMA)
        ====================================================== --}}
        <div x-show="activeTab === 'incoming'" class="space-y-4">
            @forelse ($incomingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $myShift = $swap->targetShiftAssignment?->shift;
                    $isPendingAction = ($swap->status === 'pending_target');
                @endphp

                {{-- Request Exchange Desk Card --}}
                <div class="bg-white rounded-2xl border transition-all shadow-2xs overflow-hidden
                    {{ $isPendingAction ? 'border-[#6C5CE7] ring-1 ring-[#6C5CE7]/20' : 'border-[#E2E0F7]' }}">

                    {{-- Card Header: Type Badge & Status --}}
                    <div class="px-5 py-3 border-b border-[#E2E0F7] flex flex-wrap items-center justify-between gap-2.5
                        {{ $isPendingAction ? 'bg-[#F5F3FF]/80' : 'bg-slate-50/60' }}">

                        <div class="flex items-center gap-2">
                            @if ($isTransfer)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    <i data-lucide="arrow-right" class="h-3.5 w-3.5 text-purple-600"></i>
                                    Penyerahan Shift (1-Arah)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5 text-indigo-600"></i>
                                    Pertukaran / Barter (2-Arah)
                                </span>
                            @endif

                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-[#7C7896]">Diajukan {{ $swap->created_at->diffForHumans() }}</span>
                        </div>

                        <div>
                            @if ($swap->status === 'pending_target')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                    <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                                    Menunggu Persetujuan Anda
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                    Menunggu Persetujuan Manajer
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5 space-y-4">

                        {{-- Requester Identity Strip --}}
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($swap->requester->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#2D2A3E]">
                                    {{ $swap->requester->name }}
                                </p>
                                <p class="text-xs text-[#7C7896]">
                                    Rekan Kerja Pemohon
                                    @if ($swap->requester->department)
                                        • {{ $swap->requester->department->name }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Visual Shift Exchange Comparison Block --}}
                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4">
                            <div class="grid grid-cols-1 md:grid-cols-11 gap-3 items-center">

                                {{-- Box Left: Shift Pemohon (Offered) --}}
                                <div class="md:col-span-5 bg-white border border-[#E2E0F7] rounded-xl p-3.5 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#6C5CE7]">Shift Ditawarkan</span>
                                        <span class="text-[10px] font-semibold text-slate-400">Dari Pemohon</span>
                                    </div>
                                    <p class="font-bold text-[#2D2A3E] text-sm">{{ $reqShift->title }}</p>
                                    <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                        <i data-lucide="calendar" class="h-3 w-3 shrink-0 text-[#6C5CE7]"></i>
                                        <span>{{ $reqShift->start_time->translatedFormat('l, d M Y') }}</span>
                                    </p>
                                    <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                        <i data-lucide="clock" class="h-3 w-3 shrink-0 text-slate-400"></i>
                                        <span>{{ $reqShift->start_time->format('H:i') }} – {{ $reqShift->end_time->format('H:i') }} WIB</span>
                                    </p>
                                </div>

                                {{-- Center: Visual Exchange Direction --}}
                                <div class="md:col-span-1 flex items-center justify-center py-1 md:py-0">
                                    <div class="w-8 h-8 rounded-full bg-white border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7] shadow-2xs">
                                        <i data-lucide="{{ $isTransfer ? 'arrow-right' : 'arrow-left-right' }}" class="h-4 w-4"></i>
                                    </div>
                                </div>

                                {{-- Box Right: Target Shift (Or Transfer Recipient) --}}
                                <div class="md:col-span-5 bg-white border border-[#E2E0F7] rounded-xl p-3.5 shadow-2xs space-y-1">
                                    @if ($isTransfer)
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Penerima Tugas</span>
                                            <span class="text-[10px] font-semibold text-emerald-600">Diserahkan ke Anda</span>
                                        </div>
                                        <p class="font-bold text-[#2D2A3E] text-sm">Tanggung Jawab Dialihkan ke Anda</p>
                                        <p class="text-xs text-[#7C7896]">
                                            Setelah disetujui, Anda akan menjadi penanggung jawab resmi shift ini pada jadwal kerja Anda.
                                        </p>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Shift Anda yang Ditukar</span>
                                            <span class="text-[10px] font-semibold text-slate-400">Jadwal Anda</span>
                                        </div>
                                        @if ($myShift)
                                            <p class="font-bold text-[#2D2A3E] text-sm">{{ $myShift->title }}</p>
                                            <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                                <i data-lucide="calendar" class="h-3 w-3 shrink-0 text-indigo-600"></i>
                                                <span>{{ $myShift->start_time->translatedFormat('l, d M Y') }}</span>
                                            </p>
                                            <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                                <i data-lucide="clock" class="h-3 w-3 shrink-0 text-slate-400"></i>
                                                <span>{{ $myShift->start_time->format('H:i') }} – {{ $myShift->end_time->format('H:i') }} WIB</span>
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 italic">Jadwal shift pengganti tidak ditemukan.</p>
                                        @endif
                                    @endif
                                </div>

                            </div>
                        </div>

                        {{-- Reason Box --}}
                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-3 flex items-start gap-2.5">
                            <i data-lucide="message-square" class="h-4 w-4 text-amber-700 shrink-0 mt-0.5"></i>
                            <div class="text-xs text-[#2D2A3E]">
                                <span class="font-bold text-amber-900">Alasan Pemohon:</span>
                                <span class="italic text-slate-700">"{{ $swap->reason }}"</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 border-t border-slate-100">
                            @if ($swap->status === 'pending_target')
                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-all"
                                        onclick="return confirm('Apakah Anda yakin ingin menolak permohonan pertukaran shift ini?')"
                                    >
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                        <span>Tolak Permintaan</span>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-all"
                                    >
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                        <span>{{ $isTransfer ? 'Setujui Penyerahan' : 'Setujui Pertukaran Shift' }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="shield-alert" class="h-3.5 w-3.5 text-blue-600"></i>
                                    Telah Anda Setujui • Menunggu Persetujuan Manajer
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-16 h-16 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-4">
                        <i data-lucide="inbox" class="h-8 w-8"></i>
                    </div>
                    <h3 class="font-bold text-[#2D2A3E] text-base">Kotak Masuk Permintaan Kosong</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 max-w-md mx-auto">
                        Belum ada permohonan pertukaran atau penyerahan shift dari rekan kerja yang diajukan kepada Anda saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- =====================================================
             TAB 2: PENGAJUAN SAYA (OUTGOING REQUESTS)
        ====================================================== --}}
        <div x-show="activeTab === 'outgoing'" class="space-y-4" style="display: none;">
            @forelse ($outgoingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/30 transition-all p-5 space-y-3.5">

                    {{-- Header Row --}}
                    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            @if ($isTransfer)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                    Penyerahan Shift (1-Arah)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i data-lucide="arrow-left-right" class="h-3 w-3"></i>
                                    Pertukaran / Barter (2-Arah)
                                </span>
                            @endif

                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-[#7C7896]">Diajukan {{ $swap->created_at->diffForHumans() }}</span>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $statusClass }}">
                            <i data-lucide="{{ $statusIcon }}" class="h-3.5 w-3.5"></i>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Shift Flow Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div class="bg-[#F5F3FF]/70 border border-[#E2E0F7] rounded-xl p-3.5 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#6C5CE7] block">Shift Anda yang Dialihkan</span>
                            <p class="font-bold text-[#2D2A3E] text-sm">{{ $reqShift->title }}</p>
                            <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                <i data-lucide="calendar" class="h-3 w-3 text-[#6C5CE7]"></i>
                                <span>{{ $reqShift->start_time->translatedFormat('l, d M Y') }}</span>
                            </p>
                            <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                <i data-lucide="clock" class="h-3 w-3 text-slate-400"></i>
                                <span>{{ $reqShift->start_time->format('H:i') }} – {{ $reqShift->end_time->format('H:i') }} WIB</span>
                            </p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">
                                Rekan Tujuan: <strong class="text-[#2D2A3E]">{{ $swap->targetUser->name }}</strong>
                            </span>
                            @if ($isTransfer)
                                <p class="text-xs text-slate-600 pt-1">
                                    Permohonan penyerahan tanggung jawab shift ini kepada rekan kerja.
                                </p>
                            @else
                                @if ($targetShift)
                                    <p class="font-bold text-[#2D2A3E] text-sm">{{ $targetShift->title }}</p>
                                    <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                        <i data-lucide="calendar" class="h-3 w-3 text-indigo-600"></i>
                                        <span>{{ $targetShift->start_time->translatedFormat('l, d M Y') }}</span>
                                    </p>
                                    <p class="text-xs text-[#7C7896] flex items-center gap-1">
                                        <i data-lucide="clock" class="h-3 w-3 text-slate-400"></i>
                                        <span>{{ $targetShift->start_time->format('H:i') }} – {{ $targetShift->end_time->format('H:i') }} WIB</span>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Reason Strip --}}
                    <div class="text-xs text-[#7C7896] flex items-center gap-2 pt-1">
                        <i data-lucide="message-square" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                        <span>
                            <strong class="text-[#2D2A3E]">Alasan:</strong> "{{ $swap->reason }}"
                        </span>
                    </div>

                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-16 h-16 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-4">
                        <i data-lucide="send" class="h-8 w-8"></i>
                    </div>
                    <h3 class="font-bold text-[#2D2A3E] text-base">Belum Ada Pengajuan Aktif</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 max-w-sm mx-auto">
                        Gunakan tombol "Ajukan Tukar Shift" di atas untuk mengajukan barter jadwal atau menyerahkan tugas shift Anda kepada rekan kerja.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- =====================================================
             TAB 3: RIWAYAT SWAP (AUDIT & HISTORICAL LOG)
        ====================================================== --}}
        <div x-show="activeTab === 'history'" class="space-y-3" style="display: none;">
            @forelse ($swapHistory as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $isRequester = $swap->requester_id === auth()->id();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs p-4 sm:p-5 hover:border-[#6C5CE7]/30 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3.5 w-3.5"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-xs font-bold text-slate-500">
                                    {{ $isTransfer ? 'Transfer 1-Arah' : 'Barter 2-Arah' }}
                                </span>
                            </div>

                            <p class="text-sm md:text-base font-bold text-[#2D2A3E]">
                                <span>{{ $isRequester ? 'Anda' : $swap->requester->name }}</span>
                                <span class="text-[#6C5CE7] mx-1.5 font-normal">&rarr;</span>
                                <span>{{ $swap->target_user_id === auth()->id() ? 'Anda' : $swap->targetUser->name }}</span>
                            </p>

                            <p class="text-xs text-[#7C7896]">
                                Shift: <strong class="text-[#2D2A3E]">{{ $reqShift->title }}</strong>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    ditukar dengan <strong class="text-[#2D2A3E]">{{ $targetShift->title }}</strong>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
                            </p>

                            @if ($swap->approved_at)
                                <p class="text-[11px] text-slate-400">
                                    Ditinjau oleh <span class="font-medium text-slate-600">{{ $swap->approvedBy?->name ?? 'Manajer' }}</span> pada {{ $swap->approved_at->translatedFormat('d M Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-[#7C7896] sm:text-right shrink-0">
                            <p class="text-[11px] text-slate-400">Tanggal Pengajuan:</p>
                            <p class="font-medium text-[#2D2A3E]">{{ $swap->created_at->translatedFormat('d M Y') }}</p>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-14 h-14 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-4">
                        <i data-lucide="history" class="h-7 w-7"></i>
                    </div>
                    <h3 class="font-bold text-[#2D2A3E] text-base">Belum Ada Riwayat Pertukaran</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 max-w-sm mx-auto">
                        Semua riwayat pertukaran maupun penyerahan shift yang telah disetujui atau ditolak akan tersimpan secara permanen di sini.
                    </p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $swapHistory->links() }}
            </div>
        </div>

        {{-- =====================================================
             MODAL AJUKAN TUKAR / SERAH SHIFT (PRESERVED MECHANICS)
        ====================================================== --}}
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
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showCreateModal = false"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-[#E2E0F7] overflow-hidden my-8">

                    {{-- Modal Header --}}
                    <div class="px-6 py-5 border-b border-[#E2E0F7] bg-gradient-to-r from-white to-[#F5F3FF] flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-[#2D2A3E]">Ajukan Tukar / Serah Shift</h3>
                            <p class="text-xs text-[#7C7896] mt-0.5">Pilih mode pengalihan jadwal dan rekan kerja yang dituju.</p>
                        </div>
                        <button type="button" @click="showCreateModal = false"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" action="{{ route('employee.swaps.request') }}" class="p-6 space-y-4">
                        @csrf

                        {{-- Mode Selector (Radio Cards) --}}
                        <div>
                            <label class="block text-xs font-bold text-[#2D2A3E] uppercase tracking-wider mb-2">
                                Mode Pertukaran <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                                    :class="swapType === 'transfer' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 shadow-xs' : 'border-[#E2E0F7] hover:bg-slate-50'"
                                >
                                    <div class="flex items-start justify-between mb-1.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                             :class="swapType === 'transfer' ? 'bg-[#6C5CE7] text-white' : 'bg-[#F5F3FF] text-[#7C7896]'">
                                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                        </div>
                                        <input type="radio" name="swap_type" value="transfer" x-model="swapType" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center mt-1"
                                              :class="swapType === 'transfer' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'transfer'"></span>
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold" :class="swapType === 'transfer' ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]'">
                                        Serahkan Saja (1-Arah)
                                    </span>
                                    <span class="text-[11px] text-[#7C7896] mt-0.5">Penyerahan tanpa barter balik.</span>
                                </label>

                                <label
                                    class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all"
                                    :class="swapType === 'exchange' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 shadow-xs' : 'border-[#E2E0F7] hover:bg-slate-50'"
                                >
                                    <div class="flex items-start justify-between mb-1.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                             :class="swapType === 'exchange' ? 'bg-[#6C5CE7] text-white' : 'bg-[#F5F3FF] text-[#7C7896]'">
                                            <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                                        </div>
                                        <input type="radio" name="swap_type" value="exchange" x-model="swapType" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center mt-1"
                                              :class="swapType === 'exchange' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'exchange'"></span>
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold" :class="swapType === 'exchange' ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]'">
                                        Tukar Jadwal (2-Arah)
                                    </span>
                                    <span class="text-[11px] text-[#7C7896] mt-0.5">Barter 2-arah antar jadwal.</span>
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
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white text-[#2D2A3E]"
                            >
                                <option value="" disabled selected>-- Pilih Shift Anda --</option>
                                @foreach ($myAssignments as $assignment)
                                    <option value="{{ $assignment->id }}">
                                        {{ $assignment->shift->title }} ({{ $assignment->shift->start_time->translatedFormat('d M Y, H:i') }} - {{ $assignment->shift->end_time->format('H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($myAssignments->isEmpty())
                                <p class="text-[11px] text-amber-600 mt-1">Anda belum memiliki penugasan shift aktif mendatang yang dapat dialihkan.</p>
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
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white text-[#2D2A3E]"
                            >
                                <option value="" disabled selected>-- Pilih Rekan Kerja --</option>
                                <template x-for="c in colleagues" :key="c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + (c.position ? c.position.name : 'Staf') + ')'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Mode Exchange: Pilih Shift Rekan --}}
                        <div x-show="swapType === 'exchange'" style="display: none;">
                            <label for="target_shift_assignment_id" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                Shift Rekan yang Ingin Diambil: <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="target_shift_assignment_id"
                                name="target_shift_assignment_id"
                                :required="swapType === 'exchange'"
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white text-[#2D2A3E]"
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
                                placeholder="Jelaskan kebutuhan pergantian shift (misal: urusan keluarga mendesak, jadwal kuliah praktikum, dsb)."
                                class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] text-[#2D2A3E] resize-none"
                            ></textarea>
                        </div>

                        {{-- Footer Action Buttons --}}
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
                                class="px-5 py-2.5 text-xs md:text-sm font-semibold rounded-xl text-white bg-[#6C5CE7] hover:bg-[#5A4AD1] shadow-sm transition"
                            >
                                Kirim Pengajuan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
    @endpush
</x-app-layout>
