@php
    $statusMap = [
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-50 text-blue-800 border-blue-200', 'clock'],
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-800 border-rose-200', 'x-circle'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-brand-primary font-semibold">Shift Terbuka</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Shift Terbuka
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Katalog lowongan shift tambahan yang dapat Anda klaim untuk menambah jam kerja
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $availableShifts->count() }} Lowongan Terbuka
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-4"
        x-data="{
            activeTab: 'available',
        }"
    >
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 shadow-2xs flex items-center gap-2.5">
                <i data-lucide="check-circle" class="h-4 w-4 text-emerald-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 shadow-2xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="h-4 w-4 text-rose-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-3.5 rounded-xl border border-blue-200 shadow-2xs flex items-center gap-2.5">
                <i data-lucide="info" class="h-4 w-4 text-blue-600 shrink-0"></i>
                <span class="text-xs font-medium">{{ session('info') }}</span>
            </div>
        @endif

        {{-- =====================================================
             TABS TOOLBAR & MARKETPLACE METRICS
        ====================================================== --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="inline-flex max-w-full overflow-x-auto p-1 bg-slate-100 rounded-lg border border-slate-200/80">
                    <button
                        type="button"
                        @click="activeTab = 'available'"
                        :class="activeTab === 'available' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors flex items-center gap-1.5 shrink-0"
                    >
                        <i data-lucide="megaphone" class="h-3.5 w-3.5"></i>
                        <span>Lowongan Shift</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'available' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ $availableShifts->count() }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'my_claims'"
                        :class="activeTab === 'my_claims' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors flex items-center gap-1.5 shrink-0"
                    >
                        <i data-lucide="history" class="h-3.5 w-3.5"></i>
                        <span>Riwayat Pengajuan</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'my_claims' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ $myClaims->total() }}
                        </span>
                    </button>
                </div>

                {{-- Quick Metrics Chips --}}
                <div class="flex items-center gap-2 overflow-x-auto text-xs">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-200 text-slate-600">
                        <span>Tersedia:</span>
                        <strong class="text-slate-900 font-mono">{{ $availableShifts->count() }}</strong>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 border border-amber-200 text-amber-800">
                        <span>Menunggu:</span>
                        <strong class="font-mono">{{ $myClaims->where('status', 'pending_manager')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             TAB 1: AVAILABLE SHIFTS — HIGH DENSITY CARDS
        ====================================================== --}}
        <div x-show="activeTab === 'available'" class="space-y-3">
            @if ($availableShifts->isEmpty())
                <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="inbox" class="h-5 w-5"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Belum Ada Shift Terbuka</h3>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-md mx-auto">
                        Saat ini seluruh kuota shift telah terpenuhi atau belum ada publikasi lembur baru dari manajer.
                    </p>
                </div>
            @else
                @foreach ($availableShifts as $shift)
                    @php
                        $filledCount = $shift->shift_assignments_count;
                        $quota = $shift->quota;
                        $percentage = $quota > 0 ? min(100, round(($filledCount / $quota) * 100)) : 0;
                        $isInstant = !$shift->requires_approval;
                        $remaining = max(0, $quota - $filledCount);
                        $durationHours = round(max(0, $shift->start_time->diffInMinutes($shift->end_time, false)) / 60, 1);
                    @endphp

                    {{-- Dynamic Opportunity Card --}}
                    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:border-slate-300 transition-all overflow-hidden group">
                        <div class="p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                            {{-- Left Column: Shift Details & Badges --}}
                            <div class="space-y-2 flex-1 min-w-0">

                                {{-- Badges Ribbon --}}
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($isInstant)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="zap" class="h-3 w-3"></i>
                                            Instant FCFS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                            Butuh Approval Manajer
                                        </span>
                                    @endif

                                    @if ($shift->is_holiday)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Hari Libur
                                        </span>
                                    @endif

                                    @if ($shift->is_global_broadcast)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i data-lucide="globe" class="h-3 w-3"></i>
                                            Semua Departemen
                                        </span>
                                    @endif
                                </div>

                                {{-- Shift Title & Department --}}
                                <div>
                                    <h3 class="text-sm md:text-base font-bold text-slate-900 group-hover:text-brand-primary transition-colors leading-tight">
                                        {{ $shift->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                        <span>
                                            @if ($shift->is_global_broadcast)
                                                <strong>Semua Departemen</strong> {{ $shift->department ? '('.$shift->department->name.')' : '' }}
                                            @else
                                                {{ $shift->department?->name ?? 'Departemen Terkait' }}
                                            @endif
                                        </span>
                                    </p>
                                </div>

                                {{-- Schedule Timing Details --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                        <i data-lucide="calendar" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                        <span class="font-medium text-slate-800">{{ $shift->start_time->translatedFormat('l, d F Y') }}</span>
                                    </div>

                                    <div class="inline-flex items-center gap-1 font-mono text-slate-500">
                                        <i data-lucide="clock" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                        <span>{{ $shift->start_time->format('H:i') }} – {{ $shift->end_time->format('H:i') }} WIB</span>
                                        <span class="font-sans font-semibold text-slate-700">({{ $durationHours }} jam)</span>
                                    </div>
                                </div>

                            </div>

                            {{-- Right Column: Quota Indicator & Claim CTA --}}
                            <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end justify-between gap-3 shrink-0 lg:min-w-[200px] pt-2.5 lg:pt-0 border-t lg:border-t-0 border-slate-100">

                                {{-- Quota Meter --}}
                                <div class="w-full space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500 font-medium">Slot:</span>
                                        <span class="font-bold text-slate-800 font-mono">
                                            <span class="{{ $remaining <= 1 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $remaining }} slot</span>
                                            <span class="text-slate-400">/ {{ $quota }}</span>
                                        </span>
                                    </div>

                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div
                                            class="h-1.5 rounded-full transition-all duration-300 {{ $percentage >= 80 ? 'bg-amber-500' : 'bg-emerald-500' }}"
                                            style="width: {{ $percentage }}%"
                                        ></div>
                                    </div>

                                    @if ($remaining === 1)
                                        <p class="text-[10px] text-amber-600 font-semibold text-right">
                                            1 slot terakhir!
                                        </p>
                                    @endif
                                </div>

                                {{-- Claim Action Button --}}
                                <div class="w-full sm:w-auto lg:w-full">
                                    <form method="POST" action="{{ route('employee.open-shifts.claim', $shift) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white shadow-2xs transition-colors whitespace-nowrap
                                                {{ $isInstant
                                                    ? 'bg-slate-900 hover:bg-slate-800'
                                                    : 'bg-indigo-600 hover:bg-indigo-700' }}"
                                            onclick="return confirm('Apakah Anda yakin ingin mengklaim shift ini? {{ $isInstant ? 'Jadwal akan langsung ditugaskan kepada Anda secara instan.' : 'Pengajuan akan dikirim ke manajer untuk persetujuan.' }}')"
                                        >
                                            <i data-lucide="{{ $isInstant ? 'zap' : 'user-plus' }}" class="h-3.5 w-3.5"></i>
                                            <span>{{ $isInstant ? 'Klaim Langsung' : 'Ajukan Klaim' }}</span>
                                        </button>
                                    </form>
                                </div>

                            </div>

                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- =====================================================
             TAB 2: MY CLAIMS HISTORY — APPLICATION LOG
        ====================================================== --}}
        <div x-show="activeTab === 'my_claims'" class="space-y-2.5" style="display: none;">
            @forelse ($myClaims as $claim)
                @php
                    $shift = $claim->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$claim->status] ?? [$claim->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:border-slate-300 transition-all p-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded text-[11px] font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                @if (!$shift->requires_approval)
                                    <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 font-semibold px-1.5 py-0.2 rounded flex items-center gap-1">
                                        <i data-lucide="zap" class="h-3 w-3"></i> Instant FCFS
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                {{ $shift->title }}
                            </h4>

                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1 font-mono text-slate-700">
                                    <i data-lucide="calendar" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                    {{ $shift->start_time->translatedFormat('d M Y, H:i') }} – {{ $shift->end_time->format('H:i') }} WIB
                                </span>
                                <span>•</span>
                                <span>
                                    {{ $shift->is_global_broadcast ? 'Semua Departemen' : ($shift->department?->name ?? 'Departemen') }}
                                </span>
                            </div>

                            @if ($claim->approved_by)
                                <p class="text-[10px] text-slate-400">
                                    Ditinjau oleh <span class="font-medium text-slate-600">{{ $claim->approvedBy?->name ?? 'Manajer' }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-slate-500 sm:text-right shrink-0">
                            <p class="text-[10px] text-slate-400">Waktu Pengajuan:</p>
                            <p class="font-medium text-slate-800">
                                {{ $claim->applied_at ? $claim->applied_at->translatedFormat('d M Y, H:i') : $claim->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="h-5 w-5"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Belum Ada Riwayat Klaim</h3>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-sm mx-auto">
                        Pengajuan klaim shift terbuka yang Anda lakukan akan tersimpan di sini secara rinci.
                    </p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $myClaims->links() }}
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
