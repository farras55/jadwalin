<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Jadwal Saya
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Kalender jadwal kerja dan penugasan shift harian Anda.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                Karyawan
            </span>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- ====================================================
             WEEK NAVIGATION BAR
        ===================================================== --}}
        <div class="bg-white rounded-2xl border border-brand-border p-4 shadow-2xs">
            <div class="flex items-center justify-between gap-3">

                {{-- Previous Week --}}
                <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset - 1]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold
                          text-[#7C7896] bg-slate-50 border border-brand-border
                          hover:border-brand-primary/40 hover:text-[#6C5CE7] transition-all">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    <span class="hidden sm:inline">Minggu Lalu</span>
                </a>

                {{-- Week Label --}}
                <div class="flex flex-col items-center">
                    <span class="text-sm md:text-base font-bold text-[#2D2A3E]">
                        {{ $weekLabel }}
                    </span>
                    @if ($weekOffset === 0)
                        <span class="text-[11px] text-[#6C5CE7] font-semibold mt-0.5">Minggu Ini</span>
                    @elseif ($weekOffset < 0)
                        <span class="text-[11px] text-[#7C7896] font-semibold mt-0.5">Minggu Lalu</span>
                    @else
                        <span class="text-[11px] text-[#7C7896] font-semibold mt-0.5">Minggu Mendatang</span>
                    @endif
                </div>

                {{-- Next Week --}}
                <a href="{{ route('employee.schedule', ['week_offset' => $weekOffset + 1]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold
                          text-[#7C7896] bg-slate-50 border border-brand-border
                          hover:border-brand-primary/40 hover:text-[#6C5CE7] transition-all">
                    <span class="hidden sm:inline">Minggu Depan</span>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>

        {{-- ====================================================
             DESKTOP: 7-DAY GRID (hidden on mobile)
        ===================================================== --}}
        @php
            $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $today = \Carbon\Carbon::today();
        @endphp

        <div class="hidden md:grid grid-cols-7 gap-3">
            @foreach ($days as $index => $day)
                @php
                    $isToday = $day['date']->isSameDay($today);
                @endphp
                <div class="flex flex-col min-h-[160px] rounded-2xl border
                    {{ $isToday ? 'border-brand-primary bg-brand-primary/5' : 'border-brand-border bg-white' }}
                    p-3 shadow-2xs">

                    {{-- Day header --}}
                    <div class="mb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider
                            {{ $isToday ? 'text-[#6C5CE7]' : 'text-[#7C7896]' }}">
                            {{ $dayNames[$index] }}
                        </p>
                        <p class="text-lg font-bold
                            {{ $isToday ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]' }}">
                            {{ $day['date']->format('d') }}
                        </p>
                    </div>

                    {{-- Shift cards --}}
                    <div class="flex flex-col gap-2 flex-1">
                        @forelse ($day['assignments'] as $assignment)
                            @php
                                $shiftStart   = $assignment->shift->start_time;
                                $shiftEnd     = $assignment->shift->end_time;
                                $color        = $assignment->template_color;
                                $statusText   = match($assignment->schedule_status) {
                                    'upcoming'  => 'Mendatang',
                                    'ongoing'   => 'Sedang Berlangsung',
                                    'completed' => 'Selesai',
                                    default     => '-',
                                };
                                $statusBg = match($assignment->schedule_status) {
                                    'upcoming'  => 'bg-blue-50 text-blue-700',
                                    'ongoing'   => 'bg-emerald-50 text-emerald-700',
                                    'completed' => 'bg-slate-100 text-slate-500',
                                    default     => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <a href="{{ route('employee.schedule.show', $assignment) }}"
                               class="block bg-white rounded-xl border border-brand-border p-2 shadow-2xs
                                      hover:border-brand-primary/40 transition-all group">

                                {{-- Color stripe + title --}}
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $color }};"></span>
                                    <p class="text-[11px] font-bold text-[#2D2A3E] truncate leading-tight">
                                        {{ $assignment->shift->title }}
                                    </p>
                                </div>

                                {{-- Time --}}
                                <p class="text-[10px] text-[#7C7896] mb-1 flex items-center gap-0.5">
                                    <i data-lucide="clock" class="h-2.5 w-2.5 shrink-0"></i>
                                    {{ $shiftStart->format('H:i') }}–{{ $shiftEnd->format('H:i') }}
                                </p>

                                {{-- Status badge --}}
                                <span class="inline-block text-[9px] font-semibold px-1.5 py-0.5 rounded-full {{ $statusBg }}">
                                    {{ $statusText }}
                                </span>
                            </a>
                        @empty
                            <div class="flex-1 flex items-center justify-center">
                                <p class="text-[11px] text-slate-300 text-center">–</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ====================================================
             MOBILE: Vertical card list (visible on mobile, hidden on md+)
        ===================================================== --}}
        <div class="md:hidden space-y-4">
            @foreach ($days as $index => $day)
                @php
                    $isToday = $day['date']->isSameDay($today);
                @endphp

                {{-- Day section header --}}
                <div class="flex items-center gap-3">
                    <div class="flex flex-col items-center justify-center w-12 h-12 rounded-xl
                        {{ $isToday ? 'bg-brand-primary text-white' : 'bg-white border border-brand-border text-[#2D2A3E]' }}
                        shadow-2xs shrink-0">
                        <span class="text-[10px] font-bold uppercase leading-none">
                            {{ substr($dayNames[$index], 0, 3) }}
                        </span>
                        <span class="text-lg font-bold leading-none mt-0.5">
                            {{ $day['date']->format('d') }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-bold {{ $isToday ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]' }}">
                            {{ $dayNames[$index] }}, {{ $day['date']->translatedFormat('d M Y') }}
                        </p>
                        @if ($isToday)
                            <span class="text-[10px] text-[#6C5CE7] font-semibold">Hari Ini</span>
                        @endif
                    </div>
                </div>

                @if ($day['assignments']->isEmpty())
                    <div class="ml-15 pl-3 border-l-2 border-slate-100 pb-2">
                        <p class="text-xs text-slate-400 italic py-2">Tidak ada shift pada hari ini.</p>
                    </div>
                @else
                    <div class="ml-3 pl-3 border-l-2 {{ $isToday ? 'border-brand-primary/30' : 'border-slate-100' }} space-y-3 pb-2">
                        @foreach ($day['assignments'] as $assignment)
                            @php
                                $shiftStart = $assignment->shift->start_time;
                                $shiftEnd   = $assignment->shift->end_time;
                                $color      = $assignment->template_color;
                                $statusText = match($assignment->schedule_status) {
                                    'upcoming'  => 'Mendatang',
                                    'ongoing'   => 'Sedang Berlangsung',
                                    'completed' => 'Selesai',
                                    default     => '-',
                                };
                                $statusBg = match($assignment->schedule_status) {
                                    'upcoming'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'ongoing'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'completed' => 'bg-slate-100 text-slate-500 border-slate-200',
                                    default     => 'bg-slate-100 text-slate-500 border-slate-200',
                                };
                            @endphp
                            <a href="{{ route('employee.schedule.show', $assignment) }}"
                               class="block bg-white rounded-2xl border border-brand-border p-4 shadow-2xs
                                      hover:border-brand-primary/40 transition-all">

                                {{-- Header row --}}
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-0.5" style="background-color: {{ $color }};"></span>
                                        <p class="text-sm font-bold text-[#2D2A3E]">{{ $assignment->shift->title }}</p>
                                    </div>
                                    <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $statusBg }} shrink-0">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                {{-- Details --}}
                                <div class="space-y-1.5">
                                    {{-- Time --}}
                                    <div class="flex items-center gap-2 text-xs text-[#7C7896]">
                                        <i data-lucide="clock" class="h-3.5 w-3.5 shrink-0"></i>
                                        <span>{{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }}</span>
                                    </div>

                                    {{-- Location --}}
                                    @if ($assignment->location && $assignment->location !== '-')
                                        <div class="flex items-start gap-2 text-xs text-[#7C7896]">
                                            <i data-lucide="map-pin" class="h-3.5 w-3.5 shrink-0 mt-0.5"></i>
                                            <span class="line-clamp-1">{{ $assignment->location }}</span>
                                        </div>
                                    @endif

                                    {{-- Coworkers --}}
                                    @if ($assignment->coworkers_list->isNotEmpty())
                                        <div class="flex items-center gap-2 text-xs text-[#7C7896]">
                                            <i data-lucide="users" class="h-3.5 w-3.5 shrink-0"></i>
                                            <span>{{ $assignment->coworkers_list->take(2)->implode(', ') }}{{ $assignment->coworkers_list->count() > 2 ? ' +'.($assignment->coworkers_list->count()-2).' lainnya' : '' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

        {{-- ====================================================
             SWAP SHORTCUT
        ===================================================== --}}
        <div class="bg-white rounded-2xl border border-brand-border p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-left-right" class="h-5 w-5 text-[#6C5CE7]"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#2D2A3E]">Perlu tukar shift?</p>
                    <p class="text-xs text-[#7C7896]">Ajukan permintaan tukar atau serah shift dengan rekan kerja Anda.</p>
                </div>
            </div>
            <a href="{{ route('employee.swaps') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold
                      text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm shrink-0">
                <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                Ajukan Tukar Shift
            </a>
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
