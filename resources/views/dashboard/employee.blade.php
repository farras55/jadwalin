@php
    $safeWeeklyHourLimit = ($weeklyHourLimit ?? 0) > 0 ? $weeklyHourLimit : 40;
    $safeScheduledHours  = $weeklyScheduledHours ?? 0;
    $progressPercent     = min(100, round(($safeScheduledHours / $safeWeeklyHourLimit) * 100));
    $isOverLimit         = ($weeklyScheduledHours ?? 0) > $safeWeeklyHourLimit;

    // Kelompokkan jadwal mingguan per tanggal (Senin - Minggu)
    $weekDays = [];
    $weekStart = now()->startOfWeek();
    for ($i = 0; $i < 7; $i++) {
        $day = $weekStart->copy()->addDays($i);
        $weekDays[] = [
            'carbon'      => $day,
            'dateStr'     => $day->toDateString(),
            'dayLabel'    => strtoupper(substr($day->translatedFormat('D'), 0, 3)),
            'dayNum'      => $day->format('j'),
            'isToday'     => $day->isToday(),
            'assignments' => collect(),
        ];
    }

    foreach (($weeklyAssignments ?? collect()) as $wa) {
        $shiftDate = $wa->shift?->start_time?->toDateString();
        if (!$shiftDate) { continue; }
        foreach ($weekDays as &$wd) {
            if ($wd['dateStr'] === $shiftDate) {
                $wd['assignments']->push($wa);
                break;
            }
        }
        unset($wd);
    }
@endphp

{{-- ========================================================================= --}}
{{-- DASHBOARD KARYAWAN (EMPLOYEE PUNCH & SHIFT TERMINAL)                      --}}
{{-- ========================================================================= --}}
<div class="space-y-6">

    {{-- --------------------------------------------------------------------- --}}
    {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                               --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Karyawan</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-slate-900 font-medium">Workspace Staf</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Terminal Shift &amp; Presensi
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Status jadwal hari ini, batas kapasitas jam kerja pekanan, dan shift terbuka.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('employee.schedule') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 transition shadow-2xs active:translate-y-px">
                <i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-400"></i>
                <span>Jadwal Saya</span>
            </a>
            <a href="{{ route('employee.attendance') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-xs shadow-indigo-600/20 active:translate-y-px">
                <i data-lucide="map-pin" class="h-3.5 w-3.5"></i>
                <span>Presensi Masuk/Pulang</span>
            </a>
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 2. TODAY'S SHIFT TERMINAL & WEEKLY CAPACITY (SPLIT 2 COLS)            --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- LEFT: TODAY'S SHIFT STATUS CARD (7 COLS) --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 border-l-4 border-l-indigo-600 shadow-card flex flex-col justify-between overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar-check" class="h-4 w-4 text-slate-500"></i>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Shift Hari Ini ({{ now()->translatedFormat('l, d F Y') }})
                    </h3>
                </div>
                @if (($todayAssignments ?? collect())->isNotEmpty())
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ ($todayAssignments ?? collect())->count() }} Shift Terjadwal
                    </span>
                @else
                    <span class="text-xs text-slate-400">Tidak ada penugasan</span>
                @endif
            </div>

            <div class="p-5 flex-1 flex flex-col justify-center">
                @if (($todayAssignments ?? collect())->isEmpty())
                    <div class="py-4 flex items-center gap-4">
                        <div class="h-11 w-11 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                            <i data-lucide="coffee" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Hari ini tidak ada shift aktif</p>
                            <p class="text-xs text-slate-500 mt-0.5">Waktu istirahat Anda. Cek lowongan shift terbuka jika Anda ingin mengambil jam tambahan.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach (($todayAssignments ?? collect()) as $assignment)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/40">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="clock" class="h-5 w-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 truncate">
                                            {{ $assignment->shift?->title ?? 'Shift Operasional' }}
                                        </h4>
                                        <p class="text-xs text-slate-500 mt-0.5 font-mono">
                                            {{ $assignment->shift?->start_time?->format('H:i') ?? '-' }} – {{ $assignment->shift?->end_time?->format('H:i') ?? '-' }} {{ $timezoneAbbr ?? 'WIB' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5 self-end sm:self-center">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md border
                                        {{ $assignment->status === 'present' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status ?? 'Terjadwal')) }}
                                    </span>
                                    <a href="{{ route('employee.attendance') }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shadow-xs shadow-indigo-600/20 active:translate-y-px">
                                        <span>Absen</span>
                                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                <span class="text-slate-500">Kebutuhan pergantian shift mendesak?</span>
                <a href="{{ route('employee.swaps') }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                    <span>Ajukan Tukar Shift</span>
                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                </a>
            </div>
        </div>

        {{-- RIGHT: WEEKLY CAPACITY & HOURS METER (5 COLS) --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 p-5 shadow-card flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Beban Jam Kerja Pekanan
                    </h3>
                    <span class="text-xs font-mono font-bold {{ $isOverLimit ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ number_format($safeScheduledHours, 1, ',', '.') }} / {{ number_format($safeWeeklyHourLimit, 0, ',', '.') }} Jam
                    </span>
                </div>

                {{-- Progress Bar --}}
                <div class="space-y-2 mb-5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Kapasitas Terpakai</span>
                        <span class="font-mono font-semibold {{ $isOverLimit ? 'text-rose-600' : 'text-indigo-600' }}">{{ $progressPercent }}%</span>
                    </div>
                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300 {{ $isOverLimit ? 'bg-rose-500' : 'bg-indigo-600' }}" 
                             style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>

                {{-- Stats breakdown --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Shift Pekan Ini</p>
                        <p class="text-base font-bold text-slate-900 font-mono mt-0.5">
                            {{ ($weeklyAssignments ?? collect())->count() }} <span class="text-xs font-normal text-slate-500 font-sans">Shift</span>
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Sisa Kuota Jam</p>
                        <p class="text-base font-bold font-mono mt-0.5 {{ $isOverLimit ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ number_format($remainingWeeklyHours ?? 0, 1, ',', '.') }} <span class="text-xs font-normal text-slate-500 font-sans">Jam</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Aturan jam mengacu PP 35</span>
                <a href="{{ route('employee.timesheet') }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                    <span>Lihat Timesheet</span>
                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 3. WEEKLY SCHEDULE STRIP (7-DAYS HORIZONTAL CALENDAR)                 --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Jadwal Sepekan</h3>
                <p class="text-xs text-slate-500">
                    {{ now()->startOfWeek()->translatedFormat('d M') }} – {{ now()->endOfWeek()->translatedFormat('d M Y') }}
                </p>
            </div>
            <a href="{{ route('employee.schedule') }}" 
               class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                <span>Buka Kalender Lengkap</span>
                <i data-lucide="arrow-right" class="h-3 w-3"></i>
            </a>
        </div>

        <div class="grid grid-cols-7 divide-x divide-slate-100">
            @foreach ($weekDays as $wd)
                <div class="flex flex-col items-center py-3.5 px-1.5 {{ $wd['isToday'] ? 'bg-indigo-50/40' : '' }}">
                    <span class="text-[10px] font-medium uppercase tracking-wider {{ $wd['isToday'] ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                        {{ $wd['dayLabel'] }}
                    </span>
                    
                    <div class="mt-1 w-7 h-7 flex items-center justify-center rounded-full text-xs font-bold font-mono
                        {{ $wd['isToday'] ? 'bg-indigo-600 text-white shadow-xs shadow-indigo-600/20' : 'text-slate-700' }}">
                        {{ $wd['dayNum'] }}
                    </div>

                    <div class="mt-2.5 flex flex-col items-center gap-1 w-full px-1 min-h-[2.5rem]">
                        @if ($wd['assignments']->isEmpty())
                            <span class="text-[10px] text-slate-300 font-mono mt-1">—</span>
                        @else
                            @foreach ($wd['assignments'] as $wa)
                                <div class="w-full text-center">
                                    <div class="hidden sm:block bg-indigo-50 border border-indigo-200/60 text-indigo-700 rounded px-1 py-0.5">
                                        <p class="text-[10px] font-semibold font-mono leading-tight">
                                            {{ $wa->shift?->start_time?->format('H:i') ?? '?' }}
                                        </p>
                                        <p class="text-[9px] text-slate-500 font-mono leading-tight">
                                            {{ $wa->shift?->end_time?->format('H:i') ?? '?' }}
                                        </p>
                                    </div>
                                    <div class="sm:hidden w-2 h-2 rounded-full bg-indigo-600 mx-auto" title="Shift Terjadwal"></div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 4. OPEN SHIFT OPPORTUNITIES (HIGH DENSITY TABLE)                      --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Lowongan Shift Terbuka</h3>
                <p class="text-xs text-slate-500">Klaim shift kosong untuk menambah jam kerja dan pendapatan.</p>
            </div>
            <a href="{{ route('employee.open-shifts') }}" 
               class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                <span>Lihat Semua ({{ $openShiftCount ?? 0 }})</span>
                <i data-lucide="arrow-right" class="h-3 w-3"></i>
            </a>
        </div>

        @if (($openShifts ?? collect())->isEmpty())
            <div class="p-8 text-center">
                <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                    <i data-lucide="calendar-x" class="h-4.5 w-4.5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-800">Tidak ada lowongan shift terbuka</p>
                <p class="text-xs text-slate-400 mt-0.5">Semua slot shift saat ini telah terpenuhi kuotanya.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach (($openShifts ?? collect()) as $shift)
                    <div class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition text-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $shift->title }}</p>
                                <p class="text-slate-400 text-[11px] mt-0.5">
                                    {{ $shift->start_time?->translatedFormat('l, d M Y') ?? '-' }} • 
                                    <span class="font-mono text-slate-600">{{ $shift->start_time?->format('H:i') ?? '-' }} – {{ $shift->end_time?->format('H:i') ?? '-' }} {{ $timezoneAbbr ?? 'WIB' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="users" class="h-3 w-3"></i>
                                <span>{{ $shift->quota ?? '—' }} slot</span>
                            </span>
                            <a href="{{ route('employee.open-shifts') }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition">
                                <span>Klaim</span>
                                <i data-lucide="arrow-right" class="h-3 w-3 text-slate-400"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
