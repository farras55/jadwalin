@php
    $statusMap = [
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-100 text-blue-800 border-blue-200', 'clock'],
        'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-800 border-red-200', 'x-circle'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <span class="text-[#6C5CE7] font-medium">Shift Terbuka / Open Shift</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Shift Terbuka
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Katalog lowongan shift tambahan yang dapat Anda klaim untuk menambah jam kerja.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $availableShifts->count() }} Lowongan Terbuka
                </span>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-5"
        x-data="{
            activeTab: 'available',
        }"
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
             JOB BOARD HERO BANNER WITH OPPORTUNITY COUNTERS
        ====================================================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">

                {{-- Left: Marketplace intro --}}
                <div class="max-w-xl">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-[#6C5CE7]/10 text-[#6C5CE7]">
                            Shift Marketplace
                        </span>
                        <span class="text-xs text-slate-400">•</span>
                        <span class="text-xs text-[#7C7896]">First-Come, First-Served</span>
                    </div>
                    <h2 class="text-base md:text-lg font-bold text-[#2D2A3E]">
                        Peluang Jam Kerja Tambahan
                    </h2>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 leading-relaxed">
                        Pilih shift yang sesuai dengan ketersediaan waktu Anda.
                    </p>
                </div>

                {{-- Right: Marketplace Quick Stats --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="p-3 bg-[#F5F3FF] rounded-xl border border-[#E2E0F7] text-center min-w-[90px]">
                        <p class="text-xl font-bold text-[#6C5CE7] leading-none">{{ $availableShifts->count() }}</p>
                        <p class="text-[10px] font-semibold text-[#7C7896] mt-1">Tersedia</p>
                    </div>

                    <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-100 text-center min-w-[90px]">
                        <p class="text-xl font-bold text-amber-700 leading-none">
                            {{ $myClaims->where('status', 'pending_manager')->count() }}
                        </p>
                        <p class="text-[10px] font-semibold text-amber-800 mt-1">Menunggu</p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center min-w-[90px]">
                        <p class="text-xl font-bold text-[#2D2A3E] leading-none">{{ $myClaims->total() }}</p>
                        <p class="text-[10px] font-semibold text-[#7C7896] mt-1">Riwayat Klaim</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- =====================================================
             TABS HEADER (JOB MARKETPLACE STYLE)
        ====================================================== --}}
        <div class="flex items-center justify-between border-b border-[#E2E0F7] pb-3">
            <div class="inline-flex p-1 bg-white border border-[#E2E0F7] rounded-xl shadow-2xs">
                <button
                    type="button"
                    @click="activeTab = 'available'"
                    :class="activeTab === 'available' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="relative px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    <span>Lowongan Shift</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                          :class="activeTab === 'available' ? 'bg-white text-[#6C5CE7]' : 'bg-[#6C5CE7]/10 text-[#6C5CE7]'">
                        {{ $availableShifts->count() }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'my_claims'"
                    :class="activeTab === 'my_claims' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="history" class="h-4 w-4"></i>
                    <span>Riwayat Pengajuan Saya</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                          :class="activeTab === 'my_claims' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-100 text-slate-700'">
                        {{ $myClaims->total() }}
                    </span>
                </button>
            </div>

            <p class="hidden sm:block text-xs text-[#7C7896]">
                <span class="font-medium text-[#2D2A3E]">{{ $availableShifts->count() }}</span> shift aktif untuk departemen Anda
            </p>
        </div>

        {{-- =====================================================
             TAB 1: AVAILABLE SHIFTS — HORIZONTAL JOB CARDS
        ====================================================== --}}
        <div x-show="activeTab === 'available'" class="space-y-3.5">
            @if ($availableShifts->isEmpty())
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-16 h-16 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-4">
                        <i data-lucide="inbox" class="h-8 w-8"></i>
                    </div>
                    <h3 class="font-bold text-[#2D2A3E] text-base">Belum Ada Shift Terbuka</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 max-w-md mx-auto">
                        Saat ini seluruh kuota shift telah terpenuhi atau belum ada publikasi lembur baru dari manajer. Silakan periksa kembali nanti.
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

                    {{-- Dynamic Job Board Opportunity Card --}}
                    <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/50 hover:shadow-md transition-all overflow-hidden group">
                        <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-5">

                            {{-- Left Column: Shift Details & Badges --}}
                            <div class="space-y-2.5 flex-1 min-w-0">

                                {{-- Badges Ribbon --}}
                                <div class="flex flex-wrap items-center gap-1.5">
                                    {{-- Instant vs Approval Tag --}}
                                    @if ($isInstant)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="zap" class="h-3 w-3"></i>
                                            Instant FCFS (Klaim Langsung)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                            Butuh Approval Manajer
                                        </span>
                                    @endif

                                    @if ($shift->is_holiday)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">
                                            Hari Libur
                                        </span>
                                    @endif

                                    @if ($shift->is_global_broadcast)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i data-lucide="globe" class="h-3 w-3"></i>
                                            Global Broadcast
                                        </span>
                                    @endif
                                </div>

                                {{-- Shift Title & Department --}}
                                <div>
                                    <h3 class="text-base font-bold text-[#2D2A3E] group-hover:text-[#6C5CE7] transition-colors leading-tight">
                                        {{ $shift->title }}
                                    </h3>
                                    <p class="text-xs text-[#7C7896] mt-0.5 flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                                        <span>
                                            @if ($shift->is_global_broadcast)
                                                <strong class="text-purple-700">Semua Departemen</strong> {{ $shift->department ? '('.$shift->department->name.')' : '' }}
                                            @else
                                                {{ $shift->department?->name ?? 'Departemen Terkait' }}
                                            @endif
                                        </span>
                                    </p>
                                </div>

                                {{-- Schedule Timing Details --}}
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 pt-1">
                                    <div class="inline-flex items-center gap-1.5 text-xs text-[#2D2A3E] bg-[#F5F3FF] px-2.5 py-1 rounded-lg border border-[#E2E0F7]">
                                        <i data-lucide="calendar" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                        <span class="font-semibold">{{ $shift->start_time->translatedFormat('l, d F Y') }}</span>
                                    </div>

                                    <div class="inline-flex items-center gap-1.5 text-xs text-[#7C7896]">
                                        <i data-lucide="clock" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                                        <span>{{ $shift->start_time->format('H:i') }} – {{ $shift->end_time->format('H:i') }} WIB</span>
                                        <span class="font-semibold text-[#2D2A3E]">({{ $durationHours }} jam)</span>
                                    </div>
                                </div>

                            </div>

                            {{-- Right Column: Quota Indicator & Claim CTA --}}
                            <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end justify-between gap-4 shrink-0 lg:min-w-[220px] pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">

                                {{-- Quota Meter --}}
                                <div class="w-full space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-[#7C7896] font-medium">Slot Tersedia:</span>
                                        <span class="font-bold text-[#2D2A3E]">
                                            <span class="{{ $remaining <= 1 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $remaining }} slot</span>
                                            <span class="text-slate-400">/ {{ $quota }} total</span>
                                        </span>
                                    </div>

                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/60">
                                        <div
                                            class="h-2 rounded-full transition-all duration-300 {{ $percentage >= 80 ? 'bg-amber-500' : 'bg-[#6C5CE7]' }}"
                                            style="width: {{ $percentage }}%"
                                        ></div>
                                    </div>

                                    @if ($remaining === 1)
                                        <p class="text-[10px] text-amber-600 font-semibold text-right">
                                            Tersisa 1 slot terakhir!
                                        </p>
                                    @endif
                                </div>

                                {{-- Claim Action Button --}}
                                <div class="w-full sm:w-auto lg:w-full">
                                    <form method="POST" action="{{ route('employee.open-shifts.claim', $shift) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-semibold text-white shadow-xs transition-all whitespace-nowrap
                                                {{ $isInstant
                                                    ? 'bg-[#6C5CE7] hover:bg-[#5A4AD1]'
                                                    : 'bg-indigo-600 hover:bg-indigo-700' }}"
                                            onclick="return confirm('Apakah Anda yakin ingin mengklaim shift ini? {{ $isInstant ? 'Jadwal akan langsung ditugaskan kepada Anda secara instan.' : 'Pengajuan akan dikirim ke manajer untuk persetujuan.' }}')"
                                        >
                                            <i data-lucide="{{ $isInstant ? 'zap' : 'user-plus' }}" class="h-4 w-4"></i>
                                            <span>{{ $isInstant ? 'Klaim Langsung' : 'Ajukan Klaim Shift' }}</span>
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
        <div x-show="activeTab === 'my_claims'" class="space-y-3" style="display: none;">
            @forelse ($myClaims as $claim)
                @php
                    $shift = $claim->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$claim->status] ?? [$claim->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/30 transition-all p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3.5 w-3.5"></i>
                                    {{ $statusLabel }}
                                </span>
                                @if (!$shift->requires_approval)
                                    <span class="text-[11px] text-emerald-700 bg-emerald-50 border border-emerald-200 font-semibold px-2 py-0.5 rounded-md flex items-center gap-1">
                                        <i data-lucide="zap" class="h-3 w-3"></i> Instant FCFS
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                                {{ $shift->title }}
                            </h4>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#7C7896]">
                                <span class="flex items-center gap-1 font-medium text-[#2D2A3E]">
                                    <i data-lucide="calendar" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                    {{ $shift->start_time->translatedFormat('d M Y, H:i') }} – {{ $shift->end_time->format('H:i') }} WIB
                                </span>
                                <span>•</span>
                                <span>
                                    {{ $shift->is_global_broadcast ? 'Global Broadcast' : ($shift->department?->name ?? 'Departemen') }}
                                </span>
                            </div>

                            @if ($claim->approved_by)
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Ditinjau oleh <span class="font-medium text-slate-600">{{ $claim->approvedBy?->name ?? 'Manajer' }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-[#7C7896] sm:text-right shrink-0">
                            <p class="text-[11px] text-slate-400">Waktu Pengajuan:</p>
                            <p class="font-medium text-[#2D2A3E]">
                                {{ $claim->applied_at ? $claim->applied_at->translatedFormat('d M Y, H:i') : $claim->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#E2E0F7] p-12 text-center shadow-2xs">
                    <div class="w-14 h-14 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-4">
                        <i data-lucide="history" class="h-7 w-7"></i>
                    </div>
                    <h3 class="font-bold text-[#2D2A3E] text-base">Belum Ada Riwayat Klaim</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1 max-w-sm mx-auto">
                        Pengajuan klaim shift terbuka yang Anda lakukan akan tersimpan di sini secara rinci beserta status persetujuannya.
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
