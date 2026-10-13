<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Selamat Datang, {{ Auth::user()->name }}!
                </h1>

                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Berikut adalah ringkasan aktivitas dan jadwal operasional Anda hari ini.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20"
                >
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>

                    {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </div>
    </x-slot>


    {{-- ========================================================= --}}
    {{-- KARTU RINGKASAN INFORMASI PENGGUNA --}}
    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Perusahaan --}}
        <div
            class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4"
        >
            <div
                class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0"
            >
                <i data-lucide="building-2" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p
                    class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider"
                >
                    Perusahaan
                </p>

                <p
                    class="text-sm md:text-base font-bold text-[#2D2A3E] truncate"
                >
                    {{ Auth::user()->company->name ?? 'Belum terhubung' }}
                </p>
            </div>
        </div>


        {{-- Departemen & Posisi --}}
        <div
            class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4"
        >
            <div
                class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0"
            >
                <i data-lucide="briefcase" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p
                    class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider"
                >
                    Departemen & Posisi
                </p>

                <p
                    class="text-sm md:text-base font-bold text-[#2D2A3E] truncate"
                >
                    {{ Auth::user()->position->name ?? (Auth::user()->department->name ?? 'Staf Operasional') }}
                </p>
            </div>
        </div>


        {{-- Status Keanggotaan --}}
        <div
            class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4"
        >
            <div
                class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0"
            >
                <i data-lucide="calendar" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p
                    class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider"
                >
                    Status Keanggotaan
                </p>

                <p
                    class="text-sm md:text-base font-bold text-[#2D2A3E] truncate capitalize"
                >
                    {{ Auth::user()->status ?? 'Aktif' }}
                </p>
            </div>
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- QUICK ALERT - KHUSUS MANAGER --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Manager')

        <div class="w-full max-w-3xl">

            <div
                class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden"
            >

                {{-- Header Quick Alert --}}
                <div
                    class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between"
                >

                    <div>
                        <h2 class="text-base font-bold text-[#2D2A3E]">
                            Quick Alert
                        </h2>

                        <p class="text-xs text-[#7C7896] mt-0.5">
                            Perhatian yang membutuhkan tindakan Anda.
                        </p>
                    </div>


                    {{-- Counter --}}
                    @if (($pendingAvailabilityCount ?? 0) > 0)

                        <span
                            class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-bold"
                        >
                            {{ $pendingAvailabilityCount }} Menunggu
                        </span>

                    @else

                        <span
                            class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-bold"
                        >
                            Semua Diproses
                        </span>

                    @endif

                </div>


                {{-- ================================================= --}}
                {{-- PENGAJUAN KETERSEDIAAN --}}
                {{-- ================================================= --}}

                @if ($pendingAvailability ?? null)

                    <div class="px-5 py-4">

                        <div class="flex items-center gap-4">

                            {{-- Icon --}}
                            <div
                                class="w-10 h-10 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center"
                            >
                                <i
                                    data-lucide="calendar-check"
                                    class="h-5 w-5"
                                ></i>
                            </div>


                            {{-- Detail --}}
                            <div class="flex-1 min-w-0">

                                <div
                                    class="flex items-center gap-2 flex-wrap"
                                >

                                    <h3
                                        class="text-sm font-bold text-[#2D2A3E]"
                                    >
                                        Persetujuan Ketersediaan
                                    </h3>

                                    <span
                                        class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-[11px] font-bold"
                                    >
                                        Menunggu
                                    </span>

                                </div>


                                <p
                                    class="text-xs text-[#7C7896] mt-1"
                                >
                                    {{ $pendingAvailability->user->name ?? 'Karyawan' }}

                                    <span class="mx-1">•</span>

                                    {{ $pendingAvailability->start_time?->translatedFormat('D, d M') ?? '-' }}

                                    <span class="mx-1">•</span>

                                    {{ $pendingAvailability->start_time?->format('H:i') ?? '-' }}

                                    -

                                    {{ $pendingAvailability->end_time?->format('H:i') ?? '-' }}
                                </p>

                            </div>


                            {{-- Tombol Tinjau --}}
                            <a
                                href="{{ route('manager.availability.index') }}"
                                class="shrink-0 inline-flex items-center gap-1.5 bg-[#6C5CE7] hover:bg-[#5B4BD5] text-white rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                            >
                                Tinjau

                                <i
                                    data-lucide="arrow-right"
                                    class="h-3.5 w-3.5"
                                ></i>
                            </a>

                        </div>

                    </div>


                @else

                    {{-- ================================================= --}}
                    {{-- TIDAK ADA PENGAJUAN --}}
                    {{-- ================================================= --}}

                    <div
                        class="px-5 py-6 flex items-center gap-4"
                    >

                        <div
                            class="w-10 h-10 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center"
                        >
                            <i
                                data-lucide="check-circle"
                                class="h-5 w-5"
                            ></i>
                        </div>


                        <div>

                            <p
                                class="text-sm font-semibold text-[#2D2A3E]"
                            >
                                Tidak ada pengajuan ketersediaan
                            </p>

                            <p
                                class="text-xs text-[#7C7896] mt-1"
                            >
                                Semua pengajuan ketersediaan sudah diproses.
                            </p>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    @endif

</x-app-layout>