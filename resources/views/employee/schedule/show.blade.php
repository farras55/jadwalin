<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('employee.schedule') }}"
                   class="p-2 rounded-xl text-[#7C7896] hover:bg-slate-100 hover:text-[#2D2A3E] transition">
                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                </a>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                        Detail Shift
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                        Informasi lengkap penugasan shift Anda.
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
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
            'ongoing'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'completed' => 'bg-slate-100 text-slate-500 border-slate-200',
            default     => 'bg-slate-100 text-slate-500 border-slate-200',
        };
    @endphp

    <div class="space-y-5 max-w-2xl">

        {{-- Main info card --}}
        <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs space-y-5">

            {{-- Color stripe + title + status --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-full shrink-0 mt-0.5" style="background-color: {{ $templateColor }};"></span>
                    <h2 class="text-lg font-bold text-[#2D2A3E]">{{ $assignment->shift->title }}</h2>
                </div>
                <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border {{ $statusBg }} shrink-0">
                    {{ $statusText }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Date --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="h-4 w-4 text-[#7C7896]"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider mb-0.5">Tanggal</p>
                        <p class="text-sm font-semibold text-[#2D2A3E]">
                            {{ $shiftStart->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Time --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="h-4 w-4 text-[#7C7896]"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider mb-0.5">Waktu</p>
                        <p class="text-sm font-semibold text-[#2D2A3E]">
                            {{ $shiftStart->format('H:i') }} – {{ $shiftEnd->format('H:i') }}
                        </p>
                        <p class="text-[11px] text-[#7C7896] mt-0.5">
                            {{ $shiftStart->diffInHours($shiftEnd) }} jam
                        </p>
                    </div>
                </div>

                {{-- Location --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="h-4 w-4 text-[#7C7896]"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider mb-0.5">Lokasi / Outlet</p>
                        <p class="text-sm font-semibold text-[#2D2A3E]">{{ $location }}</p>
                    </div>
                </div>

                {{-- Coworkers --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="h-4 w-4 text-[#7C7896]"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider mb-0.5">Rekan Seshift</p>
                        @if ($coworkers->isNotEmpty())
                            <ul class="space-y-0.5">
                                @foreach ($coworkers as $name)
                                    <li class="text-sm text-[#2D2A3E] font-semibold">{{ $name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-[#7C7896] italic">Tidak ada rekan dalam shift ini.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Manager Notes --}}
            @if ($assignment->manager_notes)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-amber-900 uppercase tracking-wider mb-1">Catatan Manajer</p>
                    <p class="text-sm text-amber-800">{{ $assignment->manager_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Swap CTA --}}
        <div class="bg-white rounded-2xl border border-brand-border p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-left-right" class="h-5 w-5 text-[#6C5CE7]"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#2D2A3E]">Perlu tukar shift ini?</p>
                    <p class="text-xs text-[#7C7896]">Ajukan permintaan tukar atau serah dengan rekan kerja.</p>
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
