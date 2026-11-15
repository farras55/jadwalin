@php
    $statusMap = [
        'pending_target' => ['Menunggu Rekan', 'bg-amber-50 text-amber-800 border-amber-200', 'clock'],
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-50 text-blue-800 border-blue-200', 'shield-alert'],
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-800 border-rose-200', 'x-circle'],
    ];

    $pendingActionCount = $incomingSwaps->where('status', 'pending_target')->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-brand-primary font-semibold">Tukar &amp; Serah Shift</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Tukar &amp; Serah Shift
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Pusat pengelolaan permohonan barter shift (2-arah) dan penyerahan tugas (1-arah) antar rekan kerja
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                    Tukar Shift
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-4"
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
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 shadow-card flex items-center gap-2.5">
                <i data-lucide="check-circle" class="h-4 w-4 text-emerald-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 shadow-card flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="h-4 w-4 text-rose-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-3.5 rounded-xl border border-blue-200 shadow-card flex items-center gap-2.5">
                <i data-lucide="info" class="h-4 w-4 text-blue-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('info') }}</span>
            </div>
        @endif

        {{-- =====================================================
             REQUEST MANAGEMENT ACTION BAR & STATUS STRIP
        ====================================================== --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-card">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                {{-- Left: Tab Switcher --}}
                <div class="inline-flex max-w-full overflow-x-auto p-1 bg-slate-100 rounded-lg border border-slate-200/80">
                    <button
                        type="button"
                        @click="activeTab = 'incoming'"
                        :class="activeTab === 'incoming' ? 'bg-white text-slate-900 shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors flex items-center gap-1.5"
                    >
                        <i data-lucide="inbox" class="h-3.5 w-3.5"></i>
                        <span>Permintaan Masuk</span>
                        @if ($incomingSwaps->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                  :class="activeTab === 'incoming' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'">
                                {{ $incomingSwaps->count() }}
                            </span>
                        @endif
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'outgoing'"
                        :class="activeTab === 'outgoing' ? 'bg-white text-slate-900 shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors flex items-center gap-1.5"
                    >
                        <i data-lucide="send" class="h-3.5 w-3.5"></i>
                        <span>Pengajuan Saya</span>
                        @if ($outgoingSwaps->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                  :class="activeTab === 'outgoing' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'">
                                {{ $outgoingSwaps->count() }}
                            </span>
                        @endif
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'bg-white text-slate-900 shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors flex items-center gap-1.5"
                    >
                        <i data-lucide="history" class="h-3.5 w-3.5"></i>
                        <span>Riwayat Swap</span>
                        @if ($swapHistory->total() > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                  :class="activeTab === 'history' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'">
                                {{ $swapHistory->total() }}
                            </span>
                        @endif
                    </button>
                </div>

                {{-- Right: Status indicator pill & CTA --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
                    @if ($pendingActionCount > 0)
                        <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-amber-50 rounded-lg border border-amber-200 text-xs font-semibold text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ $pendingActionCount }} menunggu respon Anda</span>
                        </div>
                    @else
                        <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-200 text-xs font-medium text-slate-600">
                            <i data-lucide="check" class="h-3.5 w-3.5 text-emerald-600"></i>
                            <span>Semua permohonan telah direspons</span>
                        </div>
                    @endif

                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-card cursor-pointer"
                    >
                        <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                        <span>Ajukan Tukar Shift</span>
                    </button>
                </div>

            </div>
        </div>

        {{-- =====================================================
             TAB 1: PERMINTAAN MASUK (INBOX UTAMA)
        ====================================================== --}}
        <div x-show="activeTab === 'incoming'" class="space-y-3">
            @forelse ($incomingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $myShift = $swap->targetShiftAssignment?->shift;
                    $isPendingAction = ($swap->status === 'pending_target');
                @endphp

                {{-- Request Exchange Desk Card --}}
                <div class="bg-white rounded-xl border transition-all shadow-card overflow-hidden
                    {{ $isPendingAction ? 'border-brand-primary ring-1 ring-brand-primary/20' : 'border-slate-200/80' }}">

                    {{-- Card Header: Type Badge & Status --}}
                    <div class="px-4 py-2.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2
                        {{ $isPendingAction ? 'bg-indigo-50/20' : 'bg-slate-50/60' }}">

                        <div class="flex items-center gap-2">
                            @if ($isTransfer)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                    Penyerahan Shift (1-Arah)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i data-lucide="arrow-left-right" class="h-3 w-3"></i>
                                    Pertukaran / Barter (2-Arah)
                                </span>
                            @endif

                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-slate-500">Diajukan {{ $swap->created_at->diffForHumans() }}</span>
                        </div>

                        <div>
                            @if ($swap->status === 'pending_target')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu Persetujuan Anda
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                    Menunggu Persetujuan Manajer
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-4 space-y-3.5">

                        {{-- Requester Identity Strip --}}
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($swap->requester->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 leading-tight">
                                    {{ $swap->requester->name }}
                                </p>
                                <p class="text-[11px] text-slate-500">
                                    Pemohon
                                    @if ($swap->requester->department)
                                        • {{ $swap->requester->department->name }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Visual Shift Exchange Comparison Block --}}
                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-lg p-3">
                            <div class="grid grid-cols-1 md:grid-cols-11 gap-2.5 items-center">

                                {{-- Box Left: Shift Pemohon (Offered) --}}
                                <div class="md:col-span-5 bg-white border border-slate-200 rounded-lg p-3 shadow-card space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-brand-primary">Shift Ditawarkan</span>
                                        <span class="text-[10px] font-medium text-slate-400">Dari Pemohon</span>
                                    </div>
                                    <p class="font-bold text-slate-900 text-xs">{{ $reqShift->title }}</p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1">
                                        <i data-lucide="calendar" class="h-3 w-3 shrink-0 text-slate-400"></i>
                                        <span>{{ $reqShift->start_time->translatedFormat('l, d M Y') }}</span>
                                    </p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1 font-mono">
                                        <i data-lucide="clock" class="h-3 w-3 shrink-0 text-slate-400"></i>
                                        <span>{{ $reqShift->start_time->format('H:i') }} – {{ $reqShift->end_time->format('H:i') }} WIB</span>
                                    </p>
                                </div>

                                {{-- Center: Visual Exchange Direction --}}
                                <div class="md:col-span-1 flex items-center justify-center py-1 md:py-0">
                                    <div class="w-7 h-7 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 shadow-card">
                                        <i data-lucide="{{ $isTransfer ? 'arrow-right' : 'arrow-left-right' }}" class="h-3.5 w-3.5"></i>
                                    </div>
                                </div>

                                {{-- Box Right: Target Shift (Or Transfer Recipient) --}}
                                <div class="md:col-span-5 bg-white border border-slate-200 rounded-lg p-3 shadow-card space-y-1">
                                    @if ($isTransfer)
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Penerima Tugas</span>
                                            <span class="text-[10px] font-medium text-emerald-600">Diserahkan ke Anda</span>
                                        </div>
                                        <p class="font-bold text-slate-900 text-xs">Tanggung Jawab Dialihkan ke Anda</p>
                                        <p class="text-[11px] text-slate-500">
                                            Setelah disetujui, Anda menjadi penanggung jawab resmi shift ini pada jadwal kerja.
                                        </p>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Shift Anda yang Ditukar</span>
                                            <span class="text-[10px] font-medium text-slate-400">Jadwal Anda</span>
                                        </div>
                                        @if ($myShift)
                                            <p class="font-bold text-slate-900 text-xs">{{ $myShift->title }}</p>
                                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                                <i data-lucide="calendar" class="h-3 w-3 shrink-0 text-slate-400"></i>
                                                <span>{{ $myShift->start_time->translatedFormat('l, d M Y') }}</span>
                                            </p>
                                            <p class="text-xs text-slate-500 flex items-center gap-1 font-mono">
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
                        <div class="bg-amber-50/60 border border-amber-200/80 rounded-lg p-2.5 flex items-start gap-2">
                            <i data-lucide="message-square" class="h-3.5 w-3.5 text-amber-700 shrink-0 mt-0.5"></i>
                            <div class="text-xs text-slate-800">
                                <span class="font-bold text-amber-900">Alasan:</span>
                                <span class="italic text-slate-600">"{{ $swap->reason }}"</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 border-t border-slate-100">
                            @if ($swap->status === 'pending_target')
                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors"
                                        onclick="return confirm('Apakah Anda yakin ingin menolak permohonan pertukaran shift ini?')"
                                    >
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        <span>Tolak Permintaan</span>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('employee.swaps.respond', $swap) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button
                                        type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-card transition-colors"
                                    >
                                        <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                        <span>{{ $isTransfer ? 'Setujui Penyerahan' : 'Setujui Pertukaran Shift' }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="shield-alert" class="h-3 w-3 text-blue-600"></i>
                                    Telah Anda Setujui • Menunggu Persetujuan Manajer
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-card">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="inbox" class="h-5 w-5"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Kotak Masuk Permintaan Kosong</h3>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-sm mx-auto">
                        Belum ada permohonan pertukaran atau penyerahan shift dari rekan kerja saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- =====================================================
             TAB 2: PENGAJUAN SAYA (OUTGOING REQUESTS)
        ====================================================== --}}
        <div x-show="activeTab === 'outgoing'" class="space-y-3" style="display: none;">
            @forelse ($outgoingSwaps as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-card hover:border-slate-300 transition-all p-4 space-y-3">

                    {{-- Header Row --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            @if ($isTransfer)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                    Penyerahan Shift (1-Arah)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i data-lucide="arrow-left-right" class="h-3 w-3"></i>
                                    Pertukaran / Barter (2-Arah)
                                </span>
                            @endif

                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-slate-500">Diajukan {{ $swap->created_at->diffForHumans() }}</span>
                        </div>

                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold border {{ $statusClass }}">
                            <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Shift Flow Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-0.5">
                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-lg p-3 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-primary block">Shift Anda yang Dialihkan</span>
                            <p class="font-bold text-slate-900 text-xs">{{ $reqShift->title }}</p>
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <i data-lucide="calendar" class="h-3 w-3 text-slate-400"></i>
                                <span>{{ $reqShift->start_time->translatedFormat('l, d M Y') }}</span>
                            </p>
                            <p class="text-xs text-slate-500 flex items-center gap-1 font-mono">
                                <i data-lucide="clock" class="h-3 w-3 text-slate-400"></i>
                                <span>{{ $reqShift->start_time->format('H:i') }} – {{ $reqShift->end_time->format('H:i') }} WIB</span>
                            </p>
                        </div>

                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-lg p-3 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">
                                Rekan Tujuan: <strong class="text-slate-900">{{ $swap->targetUser->name }}</strong>
                            </span>
                            @if ($isTransfer)
                                <p class="text-xs text-slate-600 pt-0.5">
                                    Permohonan penyerahan tanggung jawab shift ini kepada rekan kerja.
                                </p>
                            @else
                                @if ($targetShift)
                                    <p class="font-bold text-slate-900 text-xs">{{ $targetShift->title }}</p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1">
                                        <i data-lucide="calendar" class="h-3 w-3 text-slate-400"></i>
                                        <span>{{ $targetShift->start_time->translatedFormat('l, d M Y') }}</span>
                                    </p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1 font-mono">
                                        <i data-lucide="clock" class="h-3 w-3 text-slate-400"></i>
                                        <span>{{ $targetShift->start_time->format('H:i') }} – {{ $targetShift->end_time->format('H:i') }} WIB</span>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Reason Strip --}}
                    <div class="text-xs text-slate-500 flex items-center gap-1.5 pt-0.5">
                        <i data-lucide="message-square" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                        <span>
                            <strong class="text-slate-700">Alasan:</strong> "{{ $swap->reason }}"
                        </span>
                    </div>

                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-card">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="send" class="h-5 w-5"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Belum Ada Pengajuan Aktif</h3>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-sm mx-auto">
                        Gunakan tombol "Ajukan Tukar Shift" di atas untuk mengajukan barter jadwal atau menyerahkan tugas ke rekan kerja.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- =====================================================
             TAB 3: RIWAYAT SWAP (AUDIT & HISTORICAL LOG)
        ====================================================== --}}
        <div x-show="activeTab === 'history'" class="space-y-2.5" style="display: none;">
            @forelse ($swapHistory as $swap)
                @php
                    $isTransfer = $swap->isTransfer();
                    $isRequester = $swap->requester_id === auth()->id();
                    $reqShift = $swap->shiftAssignment->shift;
                    $targetShift = $swap->targetShiftAssignment?->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$swap->status] ?? [$swap->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-card p-3.5 hover:border-slate-300 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[11px] font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-[11px] font-semibold text-slate-500">
                                    {{ $isTransfer ? 'Transfer 1-Arah' : 'Barter 2-Arah' }}
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm font-bold text-slate-900">
                                <span>{{ $isRequester ? 'Anda' : $swap->requester->name }}</span>
                                <span class="text-slate-400 mx-1 font-normal">&rarr;</span>
                                <span>{{ $swap->target_user_id === auth()->id() ? 'Anda' : $swap->targetUser->name }}</span>
                            </p>

                            <p class="text-xs text-slate-500">
                                Shift: <strong class="text-slate-800">{{ $reqShift->title }}</strong>
                                ({{ $reqShift->start_time->translatedFormat('d M Y, H:i') }})
                                @if (!$isTransfer && $targetShift)
                                    ditukar dengan <strong class="text-slate-800">{{ $targetShift->title }}</strong>
                                    ({{ $targetShift->start_time->translatedFormat('d M Y, H:i') }})
                                @endif
                            </p>

                            @if ($swap->approved_at)
                                <p class="text-[10px] text-slate-400">
                                    Ditinjau oleh <span class="font-medium text-slate-600">{{ $swap->approvedBy?->name ?? 'Manajer' }}</span> pada {{ $swap->approved_at->translatedFormat('d M Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-slate-500 sm:text-right shrink-0">
                            <p class="text-[10px] text-slate-400">Tanggal Pengajuan:</p>
                            <p class="font-medium text-slate-800">{{ $swap->created_at->translatedFormat('d M Y') }}</p>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-card">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="h-5 w-5"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Belum Ada Riwayat Pertukaran</h3>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-sm mx-auto">
                        Semua riwayat pertukaran maupun penyerahan shift yang telah disetujui atau ditolak akan tersimpan di sini.
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
                <div class="relative bg-white rounded-xl max-w-lg w-full shadow-xl border border-slate-200 overflow-hidden my-8">

                    {{-- Modal Header --}}
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm md:text-base font-bold text-slate-900">Ajukan Tukar / Serah Shift</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih mode pengalihan jadwal dan rekan kerja yang dituju.</p>
                        </div>
                        <button type="button" @click="showCreateModal = false"
                                class="p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>

                    {{-- Modal Form --}}
                    <form method="POST" action="{{ route('employee.swaps.request') }}" class="p-5 space-y-3.5">
                        @csrf

                        {{-- Mode Selector (Radio Cards) --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Mode Pertukaran <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <label
                                    class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all"
                                    :class="swapType === 'transfer' ? 'border-slate-900 bg-slate-50/80 shadow-card' : 'border-slate-200 hover:bg-slate-50'"
                                >
                                    <div class="flex items-start justify-between mb-1">
                                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0"
                                             :class="swapType === 'transfer' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'">
                                            <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                        </div>
                                        <input type="radio" name="swap_type" value="transfer" x-model="swapType" class="hidden">
                                        <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center mt-0.5"
                                              :class="swapType === 'transfer' ? 'border-slate-900 bg-slate-900' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'transfer'"></span>
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold" :class="swapType === 'transfer' ? 'text-slate-900' : 'text-slate-700'">
                                        Serahkan Saja (1-Arah)
                                    </span>
                                    <span class="text-[10px] text-slate-500 mt-0.5">Penyerahan tanpa barter balik.</span>
                                </label>

                                <label
                                    class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all"
                                    :class="swapType === 'exchange' ? 'border-slate-900 bg-slate-50/80 shadow-card' : 'border-slate-200 hover:bg-slate-50'"
                                >
                                    <div class="flex items-start justify-between mb-1">
                                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0"
                                             :class="swapType === 'exchange' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'">
                                            <i data-lucide="arrow-left-right" class="h-3 w-3"></i>
                                        </div>
                                        <input type="radio" name="swap_type" value="exchange" x-model="swapType" class="hidden">
                                        <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center mt-0.5"
                                              :class="swapType === 'exchange' ? 'border-slate-900 bg-slate-900' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="swapType === 'exchange'"></span>
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold" :class="swapType === 'exchange' ? 'text-slate-900' : 'text-slate-700'">
                                        Tukar Jadwal (2-Arah)
                                    </span>
                                    <span class="text-[10px] text-slate-500 mt-0.5">Barter 2-arah antar jadwal.</span>
                                </label>
                            </div>
                        </div>

                        {{-- Pilih Shift Saya --}}
                        <div>
                            <label for="shift_assignment_id" class="block text-xs font-semibold text-slate-700 mb-1">
                                Shift Anda yang Ingin Dialihkan: <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="shift_assignment_id"
                                name="shift_assignment_id"
                                required
                                class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 bg-white text-slate-900"
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
                            <label for="target_user_id" class="block text-xs font-semibold text-slate-700 mb-1">
                                Rekan Kerja Tujuan: <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="target_user_id"
                                name="target_user_id"
                                x-model="selectedColleagueId"
                                required
                                class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 bg-white text-slate-900"
                            >
                                <option value="" disabled selected>-- Pilih Rekan Kerja --</option>
                                <template x-for="c in colleagues" :key="c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + (c.position ? c.position.name : 'Staf') + ')'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Mode Exchange: Pilih Shift Rekan --}}
                        <div x-show="swapType === 'exchange'" style="display: none;">
                            <label for="target_shift_assignment_id" class="block text-xs font-semibold text-slate-700 mb-1">
                                Shift Rekan yang Ingin Diambil: <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="target_shift_assignment_id"
                                name="target_shift_assignment_id"
                                :required="swapType === 'exchange'"
                                class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 bg-white text-slate-900"
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
                            <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">
                                Alasan Permintaan: <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                id="reason"
                                name="reason"
                                rows="3"
                                required
                                placeholder="Jelaskan kebutuhan pergantian shift (misal: urusan keluarga mendesak, jadwal kuliah praktikum, dsb)."
                                class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 text-slate-900 resize-none"
                            ></textarea>
                        </div>

                        {{-- Footer Action Buttons --}}
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="px-3.5 py-2 text-xs font-semibold rounded-lg text-slate-700 hover:bg-slate-100 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-xs font-semibold rounded-lg text-white bg-slate-900 hover:bg-slate-800 shadow-card transition-colors"
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
