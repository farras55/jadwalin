<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1">
                    <span>Karyawan</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-brand-primary font-semibold">Jadwal Saya</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Jadwal Saya
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Kalender jadwal kerja dan penugasan shift mingguan pribadi Anda
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('employee.swaps') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-card">
                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5 text-slate-400"></i>
                    Tukar Shift
                </a>
                <a href="{{ route('employee.open-shifts') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-card">
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

    <div class="space-y-4">

        {{-- =====================================================
             INTEGRATED WEEK NAVIGATOR & METRICS BAR (CLEAN HIGH DENSITY)
        ====================================================== --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-card">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                {{-- Left: Week Navigation Controls --}}
                <div class="flex items-center justify-between sm:justify-start gap-2">
                    <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset - 1]) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors"
                       title="Minggu Sebelumnya">
                        <i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>
                        <span class="hidden sm:inline">Minggu Lalu</span>
                    </a>

                    <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg border border-slate-200/80">
                        <i data-lucide="calendar" class="h-3.5 w-3.5 text-brand-primary"></i>
                        <div class="text-left">
                            <p class="text-xs font-bold text-slate-900 leading-tight">
                                {{ $weekLabel }}
                            </p>
                            <p class="text-[10px] font-medium text-slate-500">
                                @if ($weekOffset === 0)
                                    <span class="text-brand-primary font-semibold">Minggu Berjalan</span>
                                @elseif ($weekOffset < 0)
                                    <span>{{ abs($weekOffset) }} minggu lalu</span>
                                @else
                                    <span>{{ $weekOffset }} minggu depan</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset + 1]) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors"
                       title="Minggu Berikutnya">
                        <span class="hidden sm:inline">Minggu Depan</span>
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Right: Compact Integrated Weekly Metrics --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200/80 shrink-0 text-xs">
                        <span class="text-slate-500">Total Shift:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $totalShifts }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50/70 border border-blue-200/70 shrink-0 text-xs">
                        <span class="text-blue-700">Jam Kerja:</span>
                        <span class="font-bold text-blue-900 font-mono">{{ $totalHours }} jam</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200/80 shrink-0 text-xs">
                        <span class="text-slate-500">Hari Libur:</span>
                        <span class="font-bold text-slate-700 font-mono">{{ $offDaysCount }} hari</span>
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
                <div class="hidden md:grid grid-cols-7 gap-2">
                    @foreach ($days as $index => $day)
                        @php
                            $isToday = $day['date']->isSameDay($today);
                            $hasShifts = $day['assignments']->isNotEmpty();
                        @endphp

                        <div class="flex flex-col min-h-[250px] rounded-xl border transition-all
                            {{ $isToday
                                ? 'border-brand-primary bg-indigo-50/20 ring-1 ring-brand-primary/20 shadow-card'
                                : ($hasShifts
                                    ? 'border-slate-200/80 bg-white shadow-card hover:border-slate-300'
                                    : 'border-dashed border-slate-200 bg-slate-50/40') }}
                            p-2">

                            {{-- Day Column Header --}}
                            <div class="pb-2 border-b {{ $isToday ? 'border-brand-primary/20' : 'border-slate-100' }} flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider
                                        {{ $isToday ? 'text-brand-primary' : 'text-slate-500' }}">
                                        {{ substr($dayNames[$index], 0, 3) }}
                                    </p>
                                    <p class="text-[11px] font-medium text-slate-400">
                                        {{ $day['date']->translatedFormat('d M') }}
                                    </p>
                                </div>

                                <div class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold font-mono
                                    {{ $isToday ? 'bg-brand-primary text-white shadow-card' : 'text-slate-700 bg-slate-50 border border-slate-200' }}">
                                    {{ $day['date']->format('d') }}
                                </div>
                            </div>

                            {{-- Day Shift Items --}}
                            <div class="flex flex-col gap-1.5 pt-2 flex-1">
                                @if ($hasShifts)
                                    @foreach ($day['assignments'] as $assignment)
                                        @php
                                            $shiftStart = $assignment->shift->start_time;
                                            $shiftEnd = $assignment->shift->end_time;
                                            $color = $assignment->template_color ?? '#4F46E5';
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
                                           class="group block bg-white rounded-lg border border-slate-200/90 p-2 shadow-card hover:border-brand-primary/50 transition-all relative overflow-hidden">
                                            {{-- Color accent indicator stripe --}}
                                            <div class="absolute left-0 top-0 bottom-0 w-1" style="background-color: {{ $color }};"></div>

                                            <div class="pl-1">
                                                <p class="text-[11px] font-bold text-slate-900 truncate group-hover:text-brand-primary transition-colors">
                                                    {{ $assignment->shift->title }}
                                                </p>
                                                <p class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1 font-mono">
                                                    <i data-lucide="clock" class="h-2.5 w-2.5 shrink-0 text-slate-400"></i>
                                                    <span>{{ $shiftStart->format('H:i') }}–{{ $shiftEnd->format('H:i') }}</span>
                                                </p>
                                                <div class="mt-1 flex items-center justify-between">
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded border {{ $statusBadge }}">
                                                        {{ $statusText }}
                                                    </span>
                                                    <i data-lucide="chevron-right" class="h-3 w-3 text-slate-300 group-hover:text-brand-primary transition-colors"></i>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    {{-- Empty Day State (Hari Libur / Bebas Shift) --}}
                                    <div class="flex-1 flex flex-col items-center justify-center py-4 text-center">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-1">
                                            <i data-lucide="coffee" class="h-3 w-3"></i>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-400">Libur</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- MOBILE: Vertical Agenda / Timeline List --}}
                <div class="md:hidden space-y-2.5">
                    @foreach ($days as $index => $day)
                        @php
                            $isToday = $day['date']->isSameDay($today);
                            $hasShifts = $day['assignments']->isNotEmpty();
                        @endphp

                        <div class="rounded-xl border p-3.5 transition-all
                            {{ $isToday
                                ? 'border-brand-primary bg-white ring-1 ring-brand-primary/20 shadow-card'
                                : ($hasShifts ? 'border-slate-200/80 bg-white shadow-card' : 'border-dashed border-slate-200 bg-slate-50/50') }}">

                            {{-- Mobile Day Header --}}
                            <div class="flex items-center justify-between pb-2.5 border-b {{ $isToday ? 'border-brand-primary/20' : 'border-slate-100' }}">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex flex-col items-center justify-center font-bold font-mono
                                        {{ $isToday ? 'bg-brand-primary text-white shadow-card' : 'bg-slate-50 border border-slate-200 text-slate-700' }}">
                                        <span class="text-[8px] uppercase leading-none">{{ substr($dayNames[$index], 0, 3) }}</span>
                                        <span class="text-xs leading-tight mt-0.5">{{ $day['date']->format('d') }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold {{ $isToday ? 'text-brand-primary' : 'text-slate-900' }}">
                                            {{ $dayNames[$index] }}, {{ $day['date']->translatedFormat('d F Y') }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ $hasShifts ? $day['assignments']->count().' shift terjadwal' : 'Hari Libur / Tidak ada shift' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($isToday)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-brand-primary border border-indigo-200/70">
                                        Hari Ini
                                    </span>
                                @endif
                            </div>

                            {{-- Mobile Shift List --}}
                            <div class="pt-2.5 space-y-2">
                                @if ($hasShifts)
                                    @foreach ($day['assignments'] as $assignment)
                                        @php
                                            $shiftStart = $assignment->shift->start_time;
                                            $shiftEnd = $assignment->shift->end_time;
                                            $color = $assignment->template_color ?? '#4F46E5';
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
                                           class="block bg-slate-50/70 rounded-lg border border-slate-200 p-2.5 hover:border-brand-primary/40 transition-all">
                                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $color }};"></span>
                                                    <span class="text-xs font-bold text-slate-900">{{ $assignment->shift->title }}</span>
                                                </div>
                                                <span class="text-[10px] px-1.5 py-0.2 rounded border {{ $statusBadge }} shrink-0">
                                                    {{ $statusText }}
                                                </span>
                                            </div>

                                            <div class="space-y-1 text-xs text-slate-500">
                                                <p class="flex items-center gap-1.5 font-mono">
                                                    <i data-lucide="clock" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                                    <span>{{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }} {{ $timezoneAbbr ?? 'WIB' }}</span>
                                                </p>
                                                @if ($assignment->location && $assignment->location !== '-')
                                                    <p class="flex items-center gap-1.5">
                                                        <i data-lucide="map-pin" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                                        <span class="truncate">{{ $assignment->location }}</span>
                                                    </p>
                                                @endif
                                                @if ($assignment->coworkers_list->isNotEmpty())
                                                    <p class="flex items-center gap-1.5">
                                                        <i data-lucide="users" class="h-3 w-3 text-slate-400 shrink-0"></i>
                                                        <span class="truncate">{{ $assignment->coworkers_list->take(2)->implode(', ') }}{{ $assignment->coworkers_list->count() > 2 ? ' +'.($assignment->coworkers_list->count()-2).' lainnya' : '' }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <p class="text-xs text-slate-400 italic py-0.5">Tidak ada penugasan kerja pada hari ini.</p>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

            {{-- RIGHT: TODAY BRIEFING & ACTION SIDEBAR (4 COLS ON DESKTOP) --}}
            <div class="lg:col-span-4 space-y-4">

                {{-- WIDGET: SHIFT HARI INI (DAILY BRIEFING) --}}
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $todayAssignments->isNotEmpty() ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                <h3 class="text-xs md:text-sm font-bold text-slate-900">Shift Hari Ini</h3>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>

                        @if ($todayAssignments->isNotEmpty())
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $todayAssignments->count() }} Aktif
                            </span>
                        @endif
                    </div>

                    <div class="p-4">
                        @if ($todayAssignments->isEmpty())
                            {{-- Off Today State --}}
                            <div class="text-center py-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="coffee" class="h-5 w-5"></i>
                                </div>
                                <h4 class="text-xs font-bold text-slate-900">Hari Bebas Tugas</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 max-w-xs mx-auto">
                                    Tidak ada jadwal shift untuk Anda hari ini. Gunakan waktu untuk beristirahat atau cek Shift Terbuka.
                                </p>
                                <a href="{{ route('employee.open-shifts') }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary mt-2.5 hover:underline">
                                    <span>Cari shift tambahan</span>
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                </a>
                            </div>
                        @else
                            {{-- Active Today Shifts --}}
                            <div class="space-y-3">
                                @foreach ($todayAssignments as $assignment)
                                    @php
                                        $shiftStart = $assignment->shift->start_time;
                                        $shiftEnd = $assignment->shift->end_time;
                                        $durationH = round(max(0, $shiftStart->diffInMinutes($shiftEnd, false)) / 60, 1);
                                        $color = $assignment->template_color ?? '#4F46E5';
                                    @endphp
                                    <div class="bg-slate-50/70 border border-slate-200/80 rounded-lg p-3 relative overflow-hidden">
                                        <div class="absolute left-0 top-0 bottom-0 w-1" style="background-color: {{ $color }};"></div>

                                        <div class="pl-1 space-y-2">
                                            <div>
                                                <div class="flex items-center justify-between gap-2">
                                                    <h4 class="text-xs font-bold text-slate-900">{{ $assignment->shift->title }}</h4>
                                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-white border border-slate-200 text-slate-700 font-mono">
                                                        {{ $durationH }} jam
                                                    </span>
                                                </div>
                                                <p class="text-xs font-semibold text-brand-primary mt-0.5 flex items-center gap-1.5 font-mono">
                                                    <i data-lucide="clock" class="h-3 w-3"></i>
                                                    <span>{{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }} {{ $timezoneAbbr ?? 'WIB' }}</span>
                                                </p>
                                            </div>

                                            @if ($assignment->location && $assignment->location !== '-')
                                                <div class="flex items-start gap-1.5 text-xs text-slate-500 bg-white p-2 rounded border border-slate-100">
                                                    <i data-lucide="map-pin" class="h-3 w-3 text-slate-400 shrink-0 mt-0.5"></i>
                                                    <span class="text-slate-700 font-medium truncate block">{{ $assignment->location }}</span>
                                                </div>
                                            @endif

                                            <div class="pt-0.5 flex items-center justify-between">
                                                <span class="text-[11px] text-slate-500">
                                                    Status: <strong class="text-slate-800">{{ ucfirst(str_replace('_', ' ', $assignment->schedule_status)) }}</strong>
                                                </span>
                                                <a href="{{ route('employee.schedule.show', $assignment) }}"
                                                   class="inline-flex items-center gap-1 text-xs font-semibold text-brand-primary hover:underline">
                                                    <span>Detail</span>
                                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
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
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <h3 class="text-xs md:text-sm font-bold text-slate-900">Aksi Cepat</h3>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <a href="{{ route('employee.swaps') }}"
                           class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-brand-primary flex items-center justify-center shrink-0">
                                    <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Ajukan Tukar Shift</p>
                                    <p class="text-[11px] text-slate-500">Tukar atau serahkan shift ke rekan</p>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-slate-300 group-hover:text-brand-primary transition-colors"></i>
                        </a>

                        <a href="{{ route('employee.open-shifts') }}"
                           class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="megaphone" class="h-3.5 w-3.5"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Klaim Shift Terbuka</p>
                                    <p class="text-[11px] text-slate-500">Cari jadwal shift tambahan</p>
                                </div>
                            </div>
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-slate-300 group-hover:text-emerald-600 transition-colors"></i>
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
