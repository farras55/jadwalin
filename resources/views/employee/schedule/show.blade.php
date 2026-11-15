<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('employee.schedule') }}"
                   class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                </a>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                        Detail Shift
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                        Informasi lengkap penugasan shift kerja Anda
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                Karyawan
            </span>
        </div>
    </x-slot>

    @php
        $shiftStart = $assignment->shift->start_time;
        $shiftEnd   = $assignment->shift->end_time;

        $statusText = match($scheduleStatus) {
            'upcoming'  => 'Mendatang',
            'ongoing'   => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            default     => '-',
        };
        $statusBg = match($scheduleStatus) {
            'upcoming'  => 'bg-blue-50 text-blue-700 border-blue-200',
            'ongoing'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 font-bold',
            'completed' => 'bg-slate-100 text-slate-500 border-slate-200',
            default     => 'bg-slate-100 text-slate-500 border-slate-200',
        };
    @endphp

    <div class="space-y-4 max-w-2xl">

        {{-- Main info card --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card space-y-4">

            {{-- Color stripe + title + status --}}
            <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $templateColor }};"></span>
                    <h2 class="text-base font-bold text-slate-900">{{ $assignment->shift->title }}</h2>
                </div>
                <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-md border {{ $statusBg }} shrink-0">
                    {{ $statusText }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Date --}}
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="h-4 w-4 text-slate-500"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal</p>
                        <p class="text-xs font-bold text-slate-900">
                            {{ $shiftStart->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Time --}}
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="h-4 w-4 text-slate-500"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Waktu</p>
                        <p class="text-xs font-bold text-slate-900 font-mono">
                            {{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }} WIB
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            {{ $shiftStart->diffInHours($shiftEnd) }} jam kerja
                        </p>
                    </div>
                </div>

                {{-- Location --}}
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="h-4 w-4 text-slate-500"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Lokasi / Outlet</p>
                        <p class="text-xs font-bold text-slate-900">{{ $location }}</p>
                    </div>
                </div>

                {{-- Coworkers --}}
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="h-4 w-4 text-slate-500"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Rekan Seshift</p>
                        @if ($coworkers->isNotEmpty())
                            <ul class="space-y-0.5">
                                @foreach ($coworkers as $name)
                                    <li class="text-xs text-slate-800 font-semibold">{{ $name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-slate-400 italic">Tidak ada rekan dalam shift ini.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Manager Notes --}}
            @if ($assignment->manager_notes)
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-lg p-3">
                    <p class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mb-0.5">Catatan Manajer</p>
                    <p class="text-xs text-amber-800">{{ $assignment->manager_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Swap CTA --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-left-right" class="h-4 w-4 text-brand-primary"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900">Perlu tukar shift ini?</p>
                    <p class="text-[11px] text-slate-500">Ajukan permintaan barter atau serahkan tugas ke rekan kerja.</p>
                </div>
            </div>
            <a href="{{ route('employee.swaps') }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold
                      text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-card shrink-0">
                <i data-lucide="arrow-left-right" class="h-3.5 w-3.5"></i>
                Ajukan Tukar Shift
            </a>
        </div>
    </div>
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
