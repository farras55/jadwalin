@php
    $availCount = $pendingAvailabilityCount ?? 0;
    $swapCount = $pendingSwapCount ?? 0;
    $claimCount = $openShiftClaimCount ?? 0;
    $totalPending = $availCount + $swapCount + $claimCount;

    $attendancePercent = ($teamCount ?? 0) > 0 ? round((($presentStaffCount ?? 0) / $teamCount) * 100) : 0;
@endphp

{{-- ========================================================================= --}}
{{-- DASHBOARD MANAGER (DAILY OPERATIONS COMMAND BOARD)                        --}}
{{-- ========================================================================= --}}
<div class="space-y-6">

    {{-- --------------------------------------------------------------------- --}}
    {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                               --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-slate-900 font-medium">Operasional Tim</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Pusat Kendali Operasional
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Monitoring presensi harian, antrean persetujuan, dan jadwal tim aktif.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('manager.shifts.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs">
                <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                <span>Jadwal Kerja Tim</span>
            </a>
            <a href="{{ route('manager.attendance.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                <i data-lucide="user-check" class="h-3.5 w-3.5 text-slate-400"></i>
                <span>Presensi Hari Ini</span>
            </a>
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 2. MORNING BRIEFING STRIP (1-BARIS OPERATIONAL SUMMARY)               --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
            
            {{-- Staf Bertugas --}}
            <div class="flex items-center gap-3 pt-2 lg:pt-0 lg:pl-0">
                <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="h-4.5 w-4.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Anggota Tim</p>
                    <p class="text-sm sm:text-base font-bold text-slate-900 font-mono">
                        {{ number_format($teamCount ?? 0) }} <span class="text-xs font-normal text-slate-500 font-sans">Staf Aktif</span>
                    </p>
                </div>
            </div>

            {{-- Kehadiran Hari Ini --}}
            <div class="flex items-center gap-3 pt-3 lg:pt-0 lg:pl-4">
                <div class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <i data-lucide="user-check" class="h-4.5 w-4.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Presensi Hari Ini</p>
                    <p class="text-sm sm:text-base font-bold text-slate-900 font-mono">
                        {{ number_format($presentStaffCount ?? 0) }} <span class="text-xs font-normal text-slate-500 font-sans">({{ $attendancePercent }}% Hadir)</span>
                    </p>
                </div>
            </div>

            {{-- Shift Pekan Ini --}}
            <div class="flex items-center gap-3 pt-3 lg:pt-0 lg:pl-4">
                <div class="h-9 w-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <i data-lucide="calendar-days" class="h-4.5 w-4.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Roster Pekan Ini</p>
                    <p class="text-sm sm:text-base font-bold text-slate-900 font-mono">
                        {{ number_format($weeklyRosterCount ?? 0) }} <span class="text-xs font-normal text-slate-500 font-sans">Shift Terjadwal</span>
                    </p>
                </div>
            </div>

            {{-- Antrean Tertunda --}}
            <div class="flex items-center gap-3 pt-3 lg:pt-0 lg:pl-4">
                <div class="h-9 w-9 rounded-lg {{ $totalPending > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $totalPending > 0 ? 'bell-ring' : 'check-circle-2' }}" class="h-4.5 w-4.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Antrean Tindakan</p>
                    <p class="text-sm sm:text-base font-bold {{ $totalPending > 0 ? 'text-amber-800' : 'text-slate-900' }} font-mono">
                        {{ number_format($totalPending) }} <span class="text-xs font-normal text-slate-500 font-sans">Pengajuan</span>
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 3. MAIN WORKSPACE: SPLIT ACTION INBOX & QUICK DESK                    --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- LEFT COLUMN: ACTION INBOX (7 COLS) --}}
        <div class="lg:col-span-7 space-y-4">
            
            {{-- Antrean Kategori Pengajuan (3 Cards Grid) --}}
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Antrean Keputusan Tim
                    </h3>
                    <span class="text-xs text-slate-400">Pilih kategori untuk memproses</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    {{-- 1. Ketersediaan Waktu --}}
                    <a href="{{ route('manager.availability.index') }}"
                       class="p-3.5 rounded-xl border transition flex flex-col justify-between gap-3 group {{ $availCount > 0 ? 'bg-white border-slate-200/90 hover:border-slate-400 shadow-2xs' : 'bg-slate-50/60 border-slate-200/60 hover:bg-white text-slate-600' }}">
                        <div class="flex items-center justify-between">
                            <div class="h-8 w-8 rounded-lg bg-slate-100 group-hover:bg-[#6C5CE7] group-hover:text-white text-slate-700 flex items-center justify-center transition">
                                <i data-lucide="calendar-check" class="h-4 w-4"></i>
                            </div>
                            @if ($availCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 font-mono">
                                    {{ $availCount }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800 leading-tight">Ketersediaan</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Jadwal kuliah &amp; izin</p>
                        </div>
                        <span class="text-[11px] font-semibold text-[#6C5CE7] inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform pt-1 border-t border-slate-100">
                            <span>Tinjau</span>
                            <i data-lucide="arrow-right" class="h-3 w-3"></i>
                        </span>
                    </a>

                    {{-- 2. Tukar Shift --}}
                    <a href="{{ route('manager.swaps.index') }}"
                       class="p-3.5 rounded-xl border transition flex flex-col justify-between gap-3 group {{ $swapCount > 0 ? 'bg-white border-slate-200/90 hover:border-slate-400 shadow-2xs' : 'bg-slate-50/60 border-slate-200/60 hover:bg-white text-slate-600' }}">
                        <div class="flex items-center justify-between">
                            <div class="h-8 w-8 rounded-lg bg-slate-100 group-hover:bg-[#6C5CE7] group-hover:text-white text-slate-700 flex items-center justify-center transition">
                                <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                            </div>
                            @if ($swapCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 font-mono">
                                    {{ $swapCount }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800 leading-tight">Tukar Shift</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Barter antar staf</p>
                        </div>
                        <span class="text-[11px] font-semibold text-[#6C5CE7] inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform pt-1 border-t border-slate-100">
                            <span>Tinjau</span>
                            <i data-lucide="arrow-right" class="h-3 w-3"></i>
                        </span>
                    </a>

                    {{-- 3. Klaim Shift Terbuka --}}
                    <a href="{{ route('manager.open-shifts.index') }}"
                       class="p-3.5 rounded-xl border transition flex flex-col justify-between gap-3 group {{ $claimCount > 0 ? 'bg-white border-slate-200/90 hover:border-slate-400 shadow-2xs' : 'bg-slate-50/60 border-slate-200/60 hover:bg-white text-slate-600' }}">
                        <div class="flex items-center justify-between">
                            <div class="h-8 w-8 rounded-lg bg-slate-100 group-hover:bg-[#6C5CE7] group-hover:text-white text-slate-700 flex items-center justify-center transition">
                                <i data-lucide="megaphone" class="h-4 w-4"></i>
                            </div>
                            @if ($claimCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 font-mono">
                                    {{ $claimCount }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800 leading-tight">Klaim Terbuka</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Permintaan jam ekstra</p>
                        </div>
                        <span class="text-[11px] font-semibold text-[#6C5CE7] inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform pt-1 border-t border-slate-100">
                            <span>Tinjau</span>
                            <i data-lucide="arrow-right" class="h-3 w-3"></i>
                        </span>
                    </a>

                </div>
            </div>

            {{-- Priority Action Desk (Spotlight Pengajuan Paling Mendesak) --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <i data-lucide="inbox" class="h-4 w-4 text-slate-500"></i>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Prioritas Peninjauan Teratas
                        </h4>
                    </div>
                    @if ($pendingAvailability)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Perlu Respon
                        </span>
                    @endif
                </div>

                @if ($pendingAvailability)
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/50">
                            <div class="flex items-start gap-3">
                                <div class="h-9 w-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($pendingAvailability->user->name ?? 'K', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h5 class="text-sm font-semibold text-slate-900 truncate">
                                            {{ $pendingAvailability->user->name ?? 'Karyawan' }}
                                        </h5>
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-purple-50 text-[#6C5CE7] border border-purple-200/60">
                                            Ketersediaan
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-slate-700">
                                            {{ $pendingAvailability->start_time?->translatedFormat('l, d M Y') ?? '-' }}
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="font-mono text-slate-600">
                                            {{ $pendingAvailability->start_time?->format('H:i') ?? '-' }} - {{ $pendingAvailability->end_time?->format('H:i') ?? '-' }} WIB
                                        </span>
                                    </p>
                                    @if ($pendingAvailability->reason)
                                        <p class="text-xs text-slate-600 mt-1.5 italic bg-white p-2 rounded border border-slate-200/60">
                                            &ldquo;{{ $pendingAvailability->reason }}&rdquo;
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('manager.availability.index') }}"
                               class="shrink-0 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                                <span>Tinjau Sekarang</span>
                                <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="h-10 w-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="check" class="h-5 w-5"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-800">Antrean Bersih</p>
                        <p class="text-xs text-slate-400 mt-0.5 max-w-sm mx-auto">
                            Semua pengajuan ketersediaan tim telah selesai ditinjau. Anda dapat fokus pada penyusunan roster dan monitoring presensi harian.
                        </p>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: STATUS BREAKDOWN & QUICK COMMANDS (5 COLS) --}}
        <div class="lg:col-span-5 space-y-4">
            
            {{-- Antrean Status Breakdown (High-Density List) --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3.5">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Distribusi Antrean
                    </h4>
                    <span class="text-xs font-mono font-bold {{ $totalPending > 0 ? 'text-amber-800' : 'text-slate-500' }}">
                        {{ $totalPending }} Tertunda
                    </span>
                </div>

                <div class="space-y-3">
                    {{-- Progress meter --}}
                    @php
                        $availPct = $totalPending > 0 ? round(($availCount / $totalPending) * 100) : 0;
                        $swapPct  = $totalPending > 0 ? round(($swapCount / $totalPending) * 100) : 0;
                        $claimPct = $totalPending > 0 ? round(($claimCount / $totalPending) * 100) : 0;
                    @endphp
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden flex">
                        @if ($totalPending > 0)
                            <div class="bg-[#6C5CE7] h-full" style="width: {{ $availPct }}%" title="Ketersediaan: {{ $availCount }}"></div>
                            <div class="bg-blue-600 h-full" style="width: {{ $swapPct }}%" title="Tukar Shift: {{ $swapCount }}"></div>
                            <div class="bg-amber-500 h-full" style="width: {{ $claimPct }}%" title="Klaim Shift: {{ $claimCount }}"></div>
                        @else
                            <div class="bg-emerald-500 h-full w-full"></div>
                        @endif
                    </div>

                    {{-- Legend items --}}
                    <div class="space-y-2 pt-1 text-xs">
                        <div class="flex items-center justify-between text-slate-700">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-[#6C5CE7]"></span>
                                <span>Ketersediaan Waktu</span>
                            </span>
                            <span class="font-bold font-mono text-slate-900">{{ number_format($availCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-700">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                <span>Permintaan Tukar Shift</span>
                            </span>
                            <span class="font-bold font-mono text-slate-900">{{ number_format($swapCount) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-700">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span>Klaim Shift Terbuka</span>
                            </span>
                            <span class="font-bold font-mono text-slate-900">{{ number_format($claimCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akses Cepat Operasional Manager --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Akses Cepat Operasional
                    </h4>
                </div>

                <div class="divide-y divide-slate-100">
                    <a href="{{ route('manager.shifts.index') }}" 
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition group text-xs">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="calendar" class="h-4 w-4 text-slate-400 group-hover:text-slate-800"></i>
                            <div>
                                <p class="font-medium text-slate-800">Jadwal Kerja Tim</p>
                                <p class="text-[10px] text-slate-400">Roster &amp; penugasan shift</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('manager.attendance.index') }}" 
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition group text-xs">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="user-check" class="h-4 w-4 text-slate-400 group-hover:text-slate-800"></i>
                            <div>
                                <p class="font-medium text-slate-800">Presensi Tim</p>
                                <p class="text-[10px] text-slate-400">Live monitor kehadiran</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('manager.attendance.regularizations.index') }}" 
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition group text-xs">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="file-pen-line" class="h-4 w-4 text-slate-400 group-hover:text-slate-800"></i>
                            <div>
                                <p class="font-medium text-slate-800">Koreksi Kehadiran</p>
                                <p class="text-[10px] text-slate-400">Persetujuan klaim jam presensi</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('manager.timesheets.index') }}" 
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition group text-xs">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="clock" class="h-4 w-4 text-slate-400 group-hover:text-slate-800"></i>
                            <div>
                                <p class="font-medium text-slate-800">Timesheet &amp; Rekapitulasi</p>
                                <p class="text-[10px] text-slate-400">Rekap jam kerja &amp; ekspor</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('manager.employees.index') }}" 
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition group text-xs">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="users" class="h-4 w-4 text-slate-400 group-hover:text-slate-800"></i>
                            <div>
                                <p class="font-medium text-slate-800">Data Staf &amp; Anggota</p>
                                <p class="text-[10px] text-slate-400">Daftar staf departemen</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
