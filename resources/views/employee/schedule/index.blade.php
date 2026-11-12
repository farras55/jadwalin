<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <span class="text-[#6C5CE7] font-medium">Jadwal Saya</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Jadwal Saya
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Kalender jadwal kerja dan penugasan shift mingguan pribadi Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('employee.swaps') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#6C5CE7] bg-[#6C5CE7]/10 border border-[#6C5CE7]/20 hover:bg-[#6C5CE7]/15 transition-all">
                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                    Tukar Shift
                </a>
                <a href="{{ route('employee.open-shifts') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#5A4AD1] transition-all shadow-sm">
                    <i data-lucide="megaphone" class="h-3.5 w-3.5"></i>
                    Shift Terbuka
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $today = \Carbon\Carbon::today();

        // Hitung statistik mingguan dari data aktual
        $totalShifts = 0;
        $totalMinutes = 0;
        $offDaysCount = 0;

        foreach ($days as $day) {
            if ($day['assignments']->isEmpty()) {
                $offDaysCount++;
            } else {
                foreach ($day['assignments'] as $a) {
                    $totalShifts++;
                    if ($a->shift?->start_time && $a->shift?->end_time) {
                        $totalMinutes += max(0, $a->shift->start_time->diffInMinutes($a->shift->end_time, false));
                    }
                }
            }
        }
        $totalHours = round($totalMinutes / 60, 1);

        // Cari shift hari ini untuk panel briefing
        $todayDayIndex = null;
        foreach ($days as $idx => $day) {
            if ($day['date']->isSameDay($today)) {
                $todayDayIndex = $idx;
                break;
            }
        }
        $todayAssignments = $todayDayIndex !== null ? $days[$todayDayIndex]['assignments'] : collect();
    @endphp

    <div class="space-y-5">

        {{-- =====================================================
             INTEGRATED WEEK NAVIGATOR & METRICS BAR
        ====================================================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-3 sm:p-4 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                {{-- Left: Week Navigation Controls --}}
                <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-3">
                    <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset - 1]) }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold text-[#7C7896] bg-[#F5F3FF] border border-[#E2E0F7] hover:border-[#6C5CE7]/40 hover:text-[#6C5CE7] transition-all"
                       title="Minggu Sebelumnya">
                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                        <span class="hidden sm:inline">Minggu Lalu</span>
                    </a>

                    <div class="flex items-center gap-2 px-3 py-1.5 bg-[#F5F3FF]/70 rounded-xl border border-[#E2E0F7]/60">
                        <div class="w-7 h-7 rounded-lg bg-[#6C5CE7]/10 flex items-center justify-center text-[#6C5CE7]">
                            <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-xs md:text-sm font-bold text-[#2D2A3E] leading-tight">
                                {{ $weekLabel }}
                            </p>
                            <p class="text-[10px] font-semibold mt-0.5">
                                @if ($weekOffset === 0)
                                    <span class="text-[#6C5CE7]">Minggu Berjalan (Aktif)</span>
                                @elseif ($weekOffset < 0)
                                    <span class="text-[#7C7896]">{{ abs($weekOffset) }} minggu yang lalu</span>
                                @else
                                    <span class="text-[#7C7896]">{{ $weekOffset }} minggu mendatang</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset + 1]) }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold text-[#7C7896] bg-[#F5F3FF] border border-[#E2E0F7] hover:border-[#6C5CE7]/40 hover:text-[#6C5CE7] transition-all"
                       title="Minggu Berikutnya">
                        <span class="hidden sm:inline">Minggu Depan</span>
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </a>
                </div>

                {{-- Right: Compact Integrated Weekly Metrics --}}
                <div class="flex items-center gap-2 sm:gap-3 overflow-x-auto pb-1 md:pb-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#F5F3FF] rounded-xl border border-[#E2E0F7] shrink-0">
                        <i data-lucide="briefcase" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                        <span class="text-xs text-[#7C7896]">Total Shift:</span>
                        <span class="text-xs font-bold text-[#2D2A3E]">{{ $totalShifts }}</span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50/70 rounded-xl border border-blue-100 shrink-0">
                        <i data-lucide="clock" class="h-3.5 w-3.5 text-blue-600"></i>
                        <span class="text-xs text-blue-700">Jam Kerja:</span>
                        <span class="text-xs font-bold text-blue-900">{{ $totalHours }} jam</span>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50/70 rounded-xl border border-amber-100 shrink-0">
                        <i data-lucide="coffee" class="h-3.5 w-3.5 text-amber-600"></i>
                        <span class="text-xs text-amber-700">Hari Libur:</span>
                        <span class="text-xs font-bold text-amber-900">{{ $offDaysCount }} hari</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- =====================================================
             MAIN CONTENT: CALENDAR (HERO) + DAILY BRIEFING SIDEBAR
        ====================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- LEFT: 7-DAY WEEKLY CALENDAR (8 COLS ON DESKTOP) --}}
            <div class="lg:col-span-8 space-y-3">

                {{-- DESKTOP: 7-Column Grid --}}
                <div class="hidden md:grid grid-cols-7 gap-2.5">
                    @foreach ($days as $index => $day)
                        @php
                            $isToday = $day['date']->isSameDay($today);
                            $hasShifts = $day['assignments']->isNotEmpty();
                        @endphp

                        <div class="flex flex-col min-h-[260px] rounded-2xl border transition-all
                            {{ $isToday
                                ? 'border-[#6C5CE7] bg-[#6C5CE7]/[0.02] ring-2 ring-[#6C5CE7]/15 shadow-sm'
                                : ($hasShifts
                                    ? 'border-[#E2E0F7] bg-white shadow-2xs hover:border-[#6C5CE7]/30'
                                    : 'border-dashed border-slate-200 bg-slate-50/50') }}
                            p-2.5">

                            {{-- Day Column Header --}}
                            <div class="pb-2 border-b {{ $isToday ? 'border-[#6C5CE7]/20' : 'border-slate-100' }} flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider
                                        {{ $isToday ? 'text-[#6C5CE7]' : 'text-[#7C7896]' }}">
                                        {{ substr($dayNames[$index], 0, 3) }}
                                    </p>
                                    <p class="text-xs font-semibold text-slate-400">
                                        {{ $day['date']->translatedFormat('d M') }}
                                    </p>
                                </div>

                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $isToday ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#2D2A3E] bg-white border border-[#E2E0F7]' }}">
                                    {{ $day['date']->format('d') }}
                                </div>
                            </div>

                            {{-- Day Shift Items --}}
                            <div class="flex flex-col gap-2 pt-2.5 flex-1">
                                @if ($hasShifts)
                                    @foreach ($day['assignments'] as $assignment)
                                        @php
                                            $shiftStart = $assignment->shift->start_time;
                                            $shiftEnd = $assignment->shift->end_time;
                                            $color = $assignment->template_color ?? '#6C5CE7';
                                            $statusText = match($assignment->schedule_status) {
                                                'upcoming'  => 'Mendatang',
                                                'ongoing'   => 'Berjalan',
                                                'completed' => 'Selesai',
                                                default     => '-',
                                            };
                                            $statusBadge = match($assignment->schedule_status) {
                                                'upcoming'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'ongoing'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 font-bold',
                                                'completed' => 'bg-slate-100 text-slate-500 border-slate-200',
                                                default     => 'bg-slate-100 text-slate-500 border-slate-200',
                                            };
                                        @endphp
                                        <a href="{{ route('employee.schedule.show', $assignment) }}"
                                           class="group block bg-white rounded-xl border border-[#E2E0F7] p-2 shadow-2xs hover:border-[#6C5CE7]/50 hover:shadow-sm transition-all relative overflow-hidden">
                                            {{-- Color accent indicator stripe --}}
                                            <div class="absolute left-0 top-0 bottom-0 w-1" style="background-color: {{ $color }};"></div>

                                            <div class="pl-1">
                                                <p class="text-[11px] font-bold text-[#2D2A3E] truncate group-hover:text-[#6C5CE7] transition-colors">
                                                    {{ $assignment->shift->title }}
                                                </p>
                                                <p class="text-[10px] text-[#7C7896] mt-0.5 flex items-center gap-1">
                                                    <i data-lucide="clock" class="h-2.5 w-2.5 shrink-0 text-[#6C5CE7]"></i>
                                                    <span>{{ $shiftStart->format('H:i') }}–{{ $shiftEnd->format('H:i') }}</span>
                                                </p>
                                                <div class="mt-1.5 flex items-center justify-between">
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded-md border {{ $statusBadge }}">
                                                        {{ $statusText }}
                                                    </span>
                                                    <i data-lucide="chevron-right" class="h-3 w-3 text-slate-300 group-hover:text-[#6C5CE7] transition-colors"></i>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    {{-- Empty Day State (Hari Libur / Bebas Shift) --}}
                                    <div class="flex-1 flex flex-col items-center justify-center py-4 text-center">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-1">
                                            <i data-lucide="coffee" class="h-3.5 w-3.5"></i>
                                        </div>
                                        <span class="text-[11px] font-medium text-slate-400">Libur</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- MOBILE: Vertical Agenda / Timeline List --}}
                <div class="md:hidden space-y-3">
                    @foreach ($days as $index => $day)
                        @php
                            $isToday = $day['date']->isSameDay($today);
                            $hasShifts = $day['assignments']->isNotEmpty();
                        @endphp

                        <div class="rounded-2xl border p-4 transition-all
                            {{ $isToday
                                ? 'border-[#6C5CE7] bg-white ring-2 ring-[#6C5CE7]/15 shadow-sm'
                                : ($hasShifts ? 'border-[#E2E0F7] bg-white shadow-2xs' : 'border-dashed border-slate-200 bg-slate-50/60') }}">

                            {{-- Mobile Day Header --}}
                            <div class="flex items-center justify-between pb-3 border-b {{ $isToday ? 'border-[#6C5CE7]/20' : 'border-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center font-bold
                                        {{ $isToday ? 'bg-[#6C5CE7] text-white shadow-xs' : 'bg-white border border-[#E2E0F7] text-[#2D2A3E]' }}">
                                        <span class="text-[9px] uppercase leading-none">{{ substr($dayNames[$index], 0, 3) }}</span>
                                        <span class="text-sm leading-tight mt-0.5">{{ $day['date']->format('d') }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold {{ $isToday ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]' }}">
                                            {{ $dayNames[$index] }}, {{ $day['date']->translatedFormat('d F Y') }}
                                        </p>
                                        <p class="text-[11px] text-[#7C7896]">
                                            {{ $hasShifts ? $day['assignments']->count().' shift terjadwal' : 'Hari Libur / Tidak ada shift' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($isToday)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                                        Hari Ini
                                    </span>
                                @endif
                            </div>

                            {{-- Mobile Shift List --}}
                            <div class="pt-3 space-y-2">
                                @if ($hasShifts)
                                    @foreach ($day['assignments'] as $assignment)
                                        @php
                                            $shiftStart = $assignment->shift->start_time;
                                            $shiftEnd = $assignment->shift->end_time;
                                            $color = $assignment->template_color ?? '#6C5CE7';
                                            $statusText = match($assignment->schedule_status) {
                                                'upcoming'  => 'Mendatang',
                                                'ongoing'   => 'Sedang Berlangsung',
                                                'completed' => 'Selesai',
                                                default     => '-',
                                            };
                                            $statusBadge = match($assignment->schedule_status) {
                                                'upcoming'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'ongoing'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 font-bold',
                                                'completed' => 'bg-slate-100 text-slate-500 border-slate-200',
                                                default     => 'bg-slate-100 text-slate-500 border-slate-200',
                                            };
                                        @endphp
                                        <a href="{{ route('employee.schedule.show', $assignment) }}"
                                           class="block bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7] p-3 hover:border-[#6C5CE7]/40 transition-all">
                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $color }};"></span>
                                                    <span class="text-sm font-bold text-[#2D2A3E]">{{ $assignment->shift->title }}</span>
                                                </div>
                                                <span class="text-[10px] px-2 py-0.5 rounded-md border {{ $statusBadge }} shrink-0">
                                                    {{ $statusText }}
                                                </span>
                                            </div>

                                            <div class="space-y-1 text-xs text-[#7C7896]">
                                                <p class="flex items-center gap-1.5">
                                                    <i data-lucide="clock" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                                    <span>{{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }}</span>
                                                </p>
                                                @if ($assignment->location && $assignment->location !== '-')
                                                    <p class="flex items-center gap-1.5">
                                                        <i data-lucide="map-pin" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                                                        <span class="truncate">{{ $assignment->location }}</span>
                                                    </p>
                                                @endif
                                                @if ($assignment->coworkers_list->isNotEmpty())
                                                    <p class="flex items-center gap-1.5">
                                                        <i data-lucide="users" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                                                        <span class="truncate">{{ $assignment->coworkers_list->take(2)->implode(', ') }}{{ $assignment->coworkers_list->count() > 2 ? ' +'.($assignment->coworkers_list->count()-2).' lainnya' : '' }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <p class="text-xs text-slate-400 italic py-1">Tidak ada penugasan kerja pada hari ini.</p>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

            {{-- RIGHT: TODAY BRIEFING & ACTION SIDEBAR (4 COLS ON DESKTOP) --}}
            <div class="lg:col-span-4 space-y-4">

                {{-- WIDGET: SHIFT HARI INI (DAILY BRIEFING) --}}
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-[#E2E0F7] bg-gradient-to-r from-white to-[#F5F3FF] flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $todayAssignments->isNotEmpty() ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300' }}"></span>
                                <h3 class="text-sm font-bold text-[#2D2A3E]">Shift Hari Ini</h3>
                            </div>
                            <p class="text-xs text-[#7C7896] mt-0.5">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>

                        @if ($todayAssignments->isNotEmpty())
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $todayAssignments->count() }} Shift Aktif
                            </span>
                        @endif
                    </div>

                    <div class="p-5">
                        @if ($todayAssignments->isEmpty())
                            {{-- Off Today State --}}
                            <div class="text-center py-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="coffee" class="h-7 w-7"></i>
                                </div>
                                <h4 class="text-sm font-bold text-[#2D2A3E]">Hari Bebas Tugas</h4>
                                <p class="text-xs text-[#7C7896] mt-1 max-w-xs mx-auto">
                                    Tidak ada jadwal shift untuk Anda hari ini. Gunakan waktu untuk beristirahat atau cek Shift Terbuka jika ingin mengambil jam tambahan.
                                </p>
                                <a href="{{ route('employee.open-shifts') }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6C5CE7] mt-3 hover:underline">
                                    <span>Cari shift tambahan</span>
                                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </div>
                        @else
                            {{-- Active Today Shifts --}}
                            <div class="space-y-4">
                                @foreach ($todayAssignments as $assignment)
                                    @php
                                        $shiftStart = $assignment->shift->start_time;
                                        $shiftEnd = $assignment->shift->end_time;
                                        $durationH = round(max(0, $shiftStart->diffInMinutes($shiftEnd, false)) / 60, 1);
                                        $color = $assignment->template_color ?? '#6C5CE7';
                                    @endphp
                                    <div class="bg-[#F5F3FF]/70 border border-[#E2E0F7] rounded-xl p-4 relative overflow-hidden">
                                        <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: {{ $color }};"></div>

                                        <div class="pl-1 space-y-2.5">
                                            <div>
                                                <div class="flex items-center justify-between gap-2">
                                                    <h4 class="text-sm font-bold text-[#2D2A3E]">{{ $assignment->shift->title }}</h4>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white border border-[#E2E0F7] text-[#6C5CE7]">
                                                        {{ $durationH }} jam
                                                    </span>
                                                </div>
                                                <p class="text-xs font-semibold text-[#6C5CE7] mt-0.5 flex items-center gap-1.5">
                                                    <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                                                    <span>{{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }} WIB</span>
                                                </p>
                                            </div>

                                            @if ($assignment->location && $assignment->location !== '-')
                                                <div class="flex items-start gap-2 text-xs text-[#7C7896] bg-white/80 p-2 rounded-lg border border-slate-100">
                                                    <i data-lucide="map-pin" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0 mt-0.5"></i>
                                                    <div class="min-w-0">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi Kerja</span>
                                                        <span class="text-[#2D2A3E] font-medium truncate block">{{ $assignment->location }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="pt-1 flex items-center justify-between">
                                                <span class="text-[11px] text-slate-500">
                                                    Status: <strong class="text-[#2D2A3E]">{{ ucfirst(str_replace('_', ' ', $assignment->schedule_status)) }}</strong>
                                                </span>
                                                <a href="{{ route('employee.schedule.show', $assignment) }}"
                                                   class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline">
                                                    <span>Detail</span>
                                                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- WIDGET: AKSI CEPAT / SHORTCUTS --}}
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[#E2E0F7]">
                        <h3 class="text-sm font-bold text-[#2D2A3E]">Aksi Cepat</h3>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <a href="{{ route('employee.swaps') }}"
                           class="flex items-center justify-between px-5 py-3.5 hover:bg-[#F5F3FF] transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center shrink-0 group-hover:bg-[#6C5CE7] group-hover:text-white transition-all">
                                    <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#2D2A3E]">Ajukan Tukar Shift</p>
                                    <p class="text-[11px] text-[#7C7896]">Tukar atau serahkan shift ke rekan</p>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-[#6C5CE7] transition-colors"></i>
                        </a>

                        <a href="{{ route('employee.open-shifts') }}"
                           class="flex items-center justify-between px-5 py-3.5 hover:bg-[#F5F3FF] transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#2D2A3E]">Klaim Shift Terbuka</p>
                                    <p class="text-[11px] text-[#7C7896]">Cari jadwal shift tambahan</p>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-emerald-600 transition-colors"></i>
                        </a>
                    </div>
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
