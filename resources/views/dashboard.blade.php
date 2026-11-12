<x-app-layout>
    <x-slot name="header">
        @if (($roleTitle ?? '') === 'Super Admin')
            <nav aria-label="Breadcrumb" class="mb-3">
                <ol class="flex items-center gap-2 text-xs font-medium text-[#7C7896]">
                    <li>
                        <span>
                            Super Admin
                        </span>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li aria-current="page" class="font-semibold text-[#6C5CE7]">
                        Dashboard
                    </li>
                </ol>
            </nav>
        @endif
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                @if (in_array(($roleTitle ?? ''), ['Karyawan', 'Manager'], true))
                    <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
                        <span>{{ $roleTitle }}</span>
                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        <span class="text-[#6C5CE7] font-medium">Dashboard</span>
                    </div>
                @endif
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Dashboard
                </h1>

                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Berikut adalah ringkasan aktivitas dan jadwal operasional Anda hari ini.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
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
        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="building-2" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">
                    Perusahaan
                </p>

                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                    {{ Auth::user()->company->name ?? 'Belum terhubung' }}
                </p>
            </div>
        </div>

        {{-- Departemen & Posisi --}}
        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="briefcase" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">
                    Departemen & Posisi
                </p>

                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                    {{ Auth::user()->position->name ?? (Auth::user()->department->name ?? 'Staf Operasional') }}
                </p>
            </div>
        </div>

        {{-- Status Keanggotaan --}}
        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="calendar" class="h-6 w-6"></i>
            </div>

            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">
                    Status Keanggotaan
                </p>

                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate capitalize">
                    {{ Auth::user()->status ?? 'Aktif' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- DASHBOARD KHUSUS SUPER ADMIN --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Super Admin')
        <section class="mb-8 space-y-5">
            <div class="flex flex-col gap-2 border-b border-[#E2E0F7] pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#6C5CE7]">
                        Ikhtisar platform
                    </p>
                    <h2 class="mt-1 text-lg font-bold tracking-tight text-[#2D2A3E]">
                        Ringkasan Operasional Platform
                    </h2>

                    <p class="mt-1 text-sm text-[#7C7896]">
                        Ringkasan perusahaan dan aktivitas penjadwalan pada platform Jadwalin.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Total Perusahaan --}}
                <div class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#E2E0F7] bg-[#F5F3FF] text-[#6C5CE7]">
                            <i data-lucide="building-2" class="h-5 w-5"></i>
                        </div>

                        <span class="rounded-full bg-[#F5F3FF] px-2.5 py-1 text-[11px] font-semibold text-[#7C7896]">
                            Seluruh platform
                        </span>
                    </div>

                    <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Total Perusahaan</p>

                    <p class="mt-1 text-3xl font-bold tracking-tight text-[#2D2A3E]">
                        {{ number_format($companyCount ?? 0) }}
                    </p>

                    <a href="{{ route('admin.companies.index') }}"
                       class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[#6C5CE7] transition hover:gap-2.5 hover:underline focus:outline-none focus-visible:rounded focus-visible:ring-2 focus-visible:ring-[#6C5CE7]">
                        Kelola perusahaan
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Total Departemen --}}
                <div class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#E2E0F7] bg-[#F5F3FF] text-[#6C5CE7]">
                            <i data-lucide="network" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Total Departemen</p>

                    <p class="mt-1 text-3xl font-bold tracking-tight text-[#2D2A3E]">
                        {{ number_format($departmentCount ?? 0) }}
                    </p>

                    <a href="{{ route('admin.departments.index') }}"
                       class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[#6C5CE7] transition hover:gap-2.5 hover:underline focus:outline-none focus-visible:rounded focus-visible:ring-2 focus-visible:ring-[#6C5CE7]">
                        Kelola departemen
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Karyawan Aktif --}}
                <div class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                          <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                              <i data-lucide="users" class="h-5 w-5"></i>
                          </div>
                    </div>

                    <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Karyawan Aktif</p>

                    <p class="mt-1 text-3xl font-bold tracking-tight text-[#2D2A3E]">
                          {{ number_format($employeeCount ?? 0) }}
                    </p>

                    <p class="mt-4 text-xs text-[#7C7896]">
                          Seluruh perusahaan
                    </p>
                </div>

                {{-- Shift Bulan Ini --}}
                <div class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                          <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600">
                              <i data-lucide="calendar-days" class="h-5 w-5"></i>
                          </div>
                    </div>

                    <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Shift Bulan Ini</p>

                    <p class="mt-1 text-3xl font-bold tracking-tight text-[#2D2A3E]">
                          {{ number_format($monthlyShiftCount ?? 0) }}
                    </p>

                    <p class="mt-4 text-xs text-[#7C7896]">
                        Berdasarkan waktu mulai shift
                    </p>
                </div>
            </div>

            {{-- Status konfigurasi perusahaan --}}
            <div class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#E2E0F7] bg-[#F5F3FF] text-[#6C5CE7]">
                          <i data-lucide="settings-2" class="h-5 w-5"></i>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-[#2D2A3E]">
                            Konfigurasi Perusahaan
                        </h3>

                        <p class="text-sm text-[#7C7896] mt-1">
                            {{ number_format($configuredCompanyCount ?? 0) }}
                            dari
                            {{ number_format($companyCount ?? 0) }}
                            perusahaan memiliki pengaturan perusahaan.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================= --}}
    {{-- DASHBOARD KHUSUS MANAGER --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Manager')
        @php
            $totalPending = ($pendingAvailabilityCount ?? 0) + ($pendingSwapCount ?? 0) + ($openShiftClaimCount ?? 0);
            $availCount = $pendingAvailabilityCount ?? 0;
            $swapCount = $pendingSwapCount ?? 0;
            $claimCount = $openShiftClaimCount ?? 0;

            // Perhitungan segment ring status pengajuan (SVG r=38, C ≈ 238.76)
            $circumference = 238.76;
            if ($totalPending > 0) {
                $availStroke = round(($availCount / $totalPending) * $circumference, 2);
                $swapStroke = round(($swapCount / $totalPending) * $circumference, 2);
                $claimStroke = round(($claimCount / $totalPending) * $circumference, 2);
            } else {
                $availStroke = 0;
                $swapStroke = 0;
                $claimStroke = 0;
            }
        @endphp

        <div class="mb-8">

            {{-- Hero Banner Manager --}}
            <div class="bg-gradient-to-r from-[#6C5CE7] via-[#5B4BD5] to-[#7B6BF0] text-white rounded-3xl p-6 sm:p-7 relative overflow-hidden shadow-xs mb-6">
                {{-- Decorative background shapes --}}
                <div class="absolute -right-8 -top-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-10 w-36 h-36 bg-[#A29BFE]/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-xl space-y-2.5">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white/15 text-white backdrop-blur-xs border border-white/20">
                            <i data-lucide="sparkles" class="h-3 w-3"></i>
                            Monitoring Real-time
                        </div>
                        <h3 class="text-xl md:text-2xl font-bold text-[#FFFFFF] tracking-tight">
                            Kelola Operasional Tim Lebih Efisien!
                        </h3>
                        <p class="text-xs sm:text-sm text-white/80 leading-relaxed">
                            Pantau jadwal shift, verifikasi pengajuan ketersediaan, serta pantau kehadiran tim secara terpusat dalam satu dashboard.
                        </p>
                        <div class="pt-2 flex flex-wrap items-center gap-2.5">
                            <a href="{{ route('manager.shifts.index') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-white text-[#6C5CE7] hover:bg-[#F5F3FF] transition shadow-xs">
                                <i data-lucide="calendar-days" class="h-3.5 w-3.5"></i>
                                <span>Lihat Jadwal Tim</span>
                            </a>
                            <a href="{{ route('manager.attendance.index') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-white/15 hover:bg-white/25 text-white border border-white/25 transition">
                                <i data-lucide="user-check" class="h-3.5 w-3.5"></i>
                                <span>Presensi Hari Ini</span>
                            </a>
                        </div>
                    </div>

                    {{-- Hero Floating Widget / Illustration --}}
                    <div class="hidden lg:flex items-center gap-3 shrink-0">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 text-center min-w-[130px] shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mx-auto mb-2 text-white">
                                <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                            </div>
                            <p class="text-2xl font-bold">{{ number_format($weeklyRosterCount ?? 0) }}</p>
                            <p class="text-[11px] text-white/80 font-medium">Shift Minggu Ini</p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 text-center min-w-[130px] shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mx-auto mb-2 text-white">
                                <i data-lucide="clipboard-check" class="h-5 w-5"></i>
                            </div>
                            <p class="text-2xl font-bold">{{ number_format($totalPending) }}</p>
                            <p class="text-[11px] text-white/80 font-medium">Menunggu Tindakan</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4 Stat Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                {{-- Anggota Tim --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/40 hover:shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl w-fit">
                                <i data-lucide="users" class="h-5 w-5"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-[#7C7896]">Departemen</span>
                        </div>
                        <p class="text-xs font-semibold text-[#7C7896] mt-4 uppercase tracking-wider">Anggota Tim Aktif</p>
                        <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                            {{ number_format($teamCount ?? 0) }}
                        </p>
                    </div>
                    <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-[#7C7896]">Staf operasional aktif</span>
                        <a href="{{ route('manager.employees.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline">
                            Lihat Tim
                            <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>

                {{-- Kehadiran Hari Ini --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/40 hover:shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl w-fit">
                                <i data-lucide="user-check" class="h-5 w-5"></i>
                            </div>
                            @if (($teamCount ?? 0) > 0)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    {{ round((($presentStaffCount ?? 0) / $teamCount) * 100) }}% Hadir
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-emerald-600">Hari ini</span>
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-[#7C7896] mt-4 uppercase tracking-wider">Kehadiran Hari Ini</p>
                        <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                            {{ number_format($presentStaffCount ?? 0) }}
                        </p>
                    </div>
                    <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-[#7C7896]">Status hadir terkonfirmasi</span>
                        <a href="{{ route('manager.attendance.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:underline">
                            Presensi
                            <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>

                {{-- Menunggu Persetujuan --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/40 hover:shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl w-fit">
                                <i data-lucide="clipboard-check" class="h-5 w-5"></i>
                            </div>
                            @if ($totalPending > 0)
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200">
                                    +{{ $totalPending }} baru
                                </span>
                            @else
                                <span class="text-emerald-600 text-[11px] font-semibold flex items-center gap-1">
                                    <i data-lucide="check" class="h-3 w-3"></i> Tuntas
                                </span>
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-[#7C7896] mt-4 uppercase tracking-wider">Menunggu Persetujuan</p>
                        <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                            {{ number_format($totalPending) }}
                        </p>
                    </div>
                    <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-[#7C7896]">Total antrean tindakan</span>
                        <a href="{{ route('manager.availability.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:underline">
                            Tinjau
                            <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>

                {{-- Penugasan Minggu Ini --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs hover:border-[#6C5CE7]/40 hover:shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl w-fit">
                                <i data-lucide="calendar-days" class="h-5 w-5"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-blue-600">Roster Aktif</span>
                        </div>
                        <p class="text-xs font-semibold text-[#7C7896] mt-4 uppercase tracking-wider">Penugasan Minggu Ini</p>
                        <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                            {{ number_format($weeklyRosterCount ?? 0) }}
                        </p>
                    </div>
                    <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-[#7C7896]">Shift terjadwal aktif</span>
                        <a href="{{ route('manager.shifts.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline">
                            Jadwal Shift
                            <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Two-Column Lower Section: Actions & Visual Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- Left Column: Approval Queues & Spotlight (7 cols) --}}
                <div class="lg:col-span-7 space-y-4">

                    {{-- 3 Approval Category Cards --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-[#2D2A3E] uppercase tracking-wider">
                                Antrean Persetujuan Tim
                            </h4>
                            <span class="text-xs text-[#7C7896]">Pilih kategori untuk memproses</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Pengajuan Ketersediaan --}}
                            <a href="{{ route('manager.availability.index') }}"
                               class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7] hover:shadow-xs transition-all flex flex-col justify-between group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center group-hover:bg-[#6C5CE7] group-hover:text-white transition-all">
                                        <i data-lucide="calendar-check" class="h-4 w-4"></i>
                                    </div>
                                    @if ($availCount > 0)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                            {{ $availCount }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-[#7C7896]">Ketersediaan</p>
                                    <p class="text-lg font-bold text-[#2D2A3E] mt-0.5">{{ number_format($availCount) }}</p>
                                </div>
                                <span class="text-[11px] font-semibold text-[#6C5CE7] mt-2 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    Tinjau
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                </span>
                            </a>

                            {{-- Permintaan Tukar Shift --}}
                            <a href="{{ route('manager.swaps.index') }}"
                               class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7] hover:shadow-xs transition-all flex flex-col justify-between group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <i data-lucide="arrow-left-right" class="h-4 w-4"></i>
                                    </div>
                                    @if ($swapCount > 0)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                            {{ $swapCount }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-[#7C7896]">Tukar Shift</p>
                                    <p class="text-lg font-bold text-[#2D2A3E] mt-0.5">{{ number_format($swapCount) }}</p>
                                </div>
                                <span class="text-[11px] font-semibold text-[#6C5CE7] mt-2 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    Tinjau
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                </span>
                            </a>

                            {{-- Klaim Shift Terbuka --}}
                            <a href="{{ route('manager.open-shifts.index') }}"
                               class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7] hover:shadow-xs transition-all flex flex-col justify-between group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                        <i data-lucide="megaphone" class="h-4 w-4"></i>
                                    </div>
                                    @if ($claimCount > 0)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                            {{ $claimCount }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-[#7C7896]">Klaim Terbuka</p>
                                    <p class="text-lg font-bold text-[#2D2A3E] mt-0.5">{{ number_format($claimCount) }}</p>
                                </div>
                                <span class="text-[11px] font-semibold text-[#6C5CE7] mt-2 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    Tinjau
                                    <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                </span>
                            </a>
                        </div>
                    </div>

                    {{-- Quick Alert Spotlight Card --}}
                    <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                        <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between bg-gradient-to-r from-white to-[#F5F3FF]">
                            <div>
                                <h3 class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                                    <i data-lucide="bell-ring" class="h-4 w-4 text-[#6C5CE7]"></i>
                                    Quick Alert: Prioritas Tindakan
                                </h3>
                                <p class="text-xs text-[#7C7896] mt-0.5">
                                    Pengajuan yang paling mendesak membutuhkan respons Anda.
                                </p>
                            </div>

                            @if (($pendingAvailabilityCount ?? 0) > 0)
                                <span class="bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full text-xs font-bold border border-amber-200">
                                    {{ $pendingAvailabilityCount }} Menunggu Ketersediaan
                                </span>
                            @else
                                <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full text-xs font-bold border border-emerald-200">
                                    Ketersediaan Bersih
                                </span>
                            @endif
                        </div>

                        @if ($pendingAvailability ?? null)
                            <div class="p-5">
                                <div class="bg-[#F5F3FF]/70 border border-[#E2E0F7] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-10 h-10 shrink-0 bg-white text-[#6C5CE7] border border-[#E2E0F7] rounded-xl flex items-center justify-center font-bold text-sm shadow-2xs">
                                            {{ strtoupper(substr($pendingAvailability->user->name ?? 'K', 0, 2)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-[#2D2A3E]">
                                                    {{ $pendingAvailability->user->name ?? 'Karyawan' }}
                                                </h4>
                                                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-md text-[10px] font-bold border border-amber-200">
                                                    Ketersediaan
                                                </span>
                                            </div>

                                            <p class="text-xs text-[#7C7896] mt-1 flex items-center gap-1.5 flex-wrap">
                                                <i data-lucide="calendar" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                                <span class="font-medium text-[#2D2A3E]">
                                                    {{ $pendingAvailability->start_time?->translatedFormat('l, d M Y') ?? '-' }}
                                                </span>
                                                <span class="text-slate-300">•</span>
                                                <span class="flex items-center gap-1">
                                                    <i data-lucide="clock" class="h-3 w-3 text-slate-400"></i>
                                                    {{ $pendingAvailability->start_time?->format('H:i') ?? '-' }} - {{ $pendingAvailability->end_time?->format('H:i') ?? '-' }} WIB
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <a href="{{ route('manager.availability.index') }}"
                                       class="shrink-0 inline-flex items-center justify-center gap-1.5 bg-[#6C5CE7] hover:bg-[#5B4BD5] text-white rounded-xl px-4 py-2.5 text-xs font-semibold transition shadow-xs">
                                        <span>Tinjau Sekarang</span>
                                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2.5">
                                    <i data-lucide="check-circle-2" class="h-6 w-6"></i>
                                </div>
                                <h4 class="text-sm font-bold text-[#2D2A3E]">Tidak Ada Pengajuan Menunggu</h4>
                                <p class="text-xs text-[#7C7896] mt-1 max-w-sm mx-auto">
                                    Seluruh permohonan ketersediaan telah diproses. Anda dapat memantau presensi tim atau jadwal kerja reguler.
                                </p>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Right Column: Status Pengajuan & Modul Pintas (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">

                    {{-- Visual Donut Breakdown Card --}}
                    <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-[#2D2A3E]">Status Pengajuan</h4>
                                <p class="text-xs text-[#7C7896] mt-0.5">Proporsi antrean yang menunggu keputusan</p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $totalPending > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $totalPending }} Tertunda
                            </span>
                        </div>

                        {{-- Donut Chart Visual --}}
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 py-2">
                            <div class="relative w-32 h-32 shrink-0">
                                <svg class="w-full h-full -rotate-90" viewBox="0 0 96 96">
                                    {{-- Background track --}}
                                    <circle cx="48" cy="48" r="38"
                                            stroke="#EDE9FE"
                                            stroke-width="9"
                                            fill="none" />

                                    @if ($totalPending > 0)
                                        {{-- Ketersediaan segment (Purple) --}}
                                        <circle cx="48" cy="48" r="38"
                                                stroke="#6C5CE7"
                                                stroke-width="9"
                                                fill="none"
                                                stroke-dasharray="{{ $availStroke }} {{ $circumference }}"
                                                stroke-dashoffset="0" />

                                        {{-- Tukar Shift segment (Emerald) --}}
                                        <circle cx="48" cy="48" r="38"
                                                stroke="#22C55E"
                                                stroke-width="9"
                                                fill="none"
                                                stroke-dasharray="{{ $swapStroke }} {{ $circumference }}"
                                                stroke-dashoffset="-{{ $availStroke }}" />

                                        {{-- Klaim Shift segment (Amber) --}}
                                        <circle cx="48" cy="48" r="38"
                                                stroke="#F59E0B"
                                                stroke-width="9"
                                                fill="none"
                                                stroke-dasharray="{{ $claimStroke }} {{ $circumference }}"
                                                stroke-dashoffset="-{{ $availStroke + $swapStroke }}" />
                                    @else
                                        {{-- Complete Green circle when empty --}}
                                        <circle cx="48" cy="48" r="38"
                                                stroke="#22C55E"
                                                stroke-width="9"
                                                fill="none" />
                                    @endif
                                </svg>

                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-bold text-[#2D2A3E]">
                                        {{ $totalPending }}
                                    </span>
                                    <span class="text-[10px] text-[#7C7896] uppercase font-semibold">Tertunda</span>
                                </div>
                            </div>

                            {{-- Donut Legend --}}
                            <div class="space-y-2.5 w-full sm:w-auto">
                                <div class="flex items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#6C5CE7]"></span>
                                        <span class="text-[#7C7896]">Ketersediaan</span>
                                    </div>
                                    <span class="font-bold text-[#2D2A3E]">{{ number_format($availCount) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#22C55E]"></span>
                                        <span class="text-[#7C7896]">Tukar Shift</span>
                                    </div>
                                    <span class="font-bold text-[#2D2A3E]">{{ number_format($swapCount) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                                        <span class="text-[#7C7896]">Klaim Terbuka</span>
                                    </div>
                                    <span class="font-bold text-[#2D2A3E]">{{ number_format($claimCount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Modules --}}
                    <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[#E2E0F7] flex items-center justify-between">
                            <h4 class="text-sm font-bold text-[#2D2A3E]">Akses Cepat Operasional</h4>
                            <span class="text-[11px] text-[#7C7896]">Modul Manager</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <a href="{{ route('manager.attendance.index') }}"
                               class="flex items-center justify-between px-5 py-3 hover:bg-[#F5F3FF] transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                                        <i data-lucide="user-check" class="h-4 w-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#2D2A3E]">Presensi Tim</p>
                                        <p class="text-[10px] text-[#7C7896]">Monitoring kehadiran real-time</p>
                                    </div>
                                </div>
                                <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-[#6C5CE7] transition"></i>
                            </a>

                            <a href="{{ route('manager.attendance.regularizations.index') }}"
                               class="flex items-center justify-between px-5 py-3 hover:bg-[#F5F3FF] transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                                        <i data-lucide="clock" class="h-4 w-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#2D2A3E]">Koreksi Kehadiran</p>
                                        <p class="text-[10px] text-[#7C7896]">Persetujuan koreksi jam staf</p>
                                    </div>
                                </div>
                                <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-[#6C5CE7] transition"></i>
                            </a>

                            <a href="{{ route('manager.timesheets.index') }}"
                               class="flex items-center justify-between px-5 py-3 hover:bg-[#F5F3FF] transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                                        <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#2D2A3E]">Timesheet & Laporan</p>
                                        <p class="text-[10px] text-[#7C7896]">Rekap jam kerja & ekspor data</p>
                                    </div>
                                </div>
                                <i data-lucide="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-[#6C5CE7] transition"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- DASHBOARD KHUSUS KARYAWAN --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Karyawan')
        @php
            // Hitung progress jam kerja
            $safeWeeklyHourLimit   = ($weeklyHourLimit ?? 0) > 0 ? $weeklyHourLimit : 40;
            $safeScheduledHours    = $weeklyScheduledHours ?? 0;
            $progressPercent       = min(100, round(($safeScheduledHours / $safeWeeklyHourLimit) * 100));
            $isOverLimit           = ($weeklyScheduledHours ?? 0) > $safeWeeklyHourLimit;

            // Progress ring (SVG circle, r=42, circumference ≈ 263.9)
            $circumference    = 263.9;
            $strokeDashoffset = round($circumference - ($progressPercent / 100) * $circumference, 2);

            // Sapaan berdasarkan waktu
            $hour = now()->hour;
            if ($hour >= 5 && $hour < 12) {
                $greeting = 'Selamat pagi';
            } elseif ($hour >= 12 && $hour < 15) {
                $greeting = 'Selamat siang';
            } elseif ($hour >= 15 && $hour < 18) {
                $greeting = 'Selamat sore';
            } else {
                $greeting = 'Selamat malam';
            }

            // Kelompokkan jadwal mingguan per tanggal dari shift->start_time
            $weekDays = [];
            $weekStart = now()->startOfWeek(); // Senin
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

        {{-- ===== MAIN WORKSPACE: Asymmetrical two-column ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">

            {{-- ===== PANEL KIRI — Weekly Work Summary (2/5) ===== --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs p-6 flex flex-col">

                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-widest mb-5">
                    Ringkasan Mingguan
                </p>

                {{-- Progress Ring --}}
                <div class="flex flex-col items-center mb-6">
                    <div class="relative w-28 h-28">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            {{-- Track --}}
                            <circle cx="48" cy="48" r="42"
                                    stroke="#E2E0F7"
                                    stroke-width="8"
                                    fill="none" />
                            {{-- Progress --}}
                            <circle cx="48" cy="48" r="42"
                                    stroke="{{ $isOverLimit ? '#EF4444' : '#6C5CE7' }}"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    fill="none"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $strokeDashoffset }}" />
                        </svg>
                        {{-- Label tengah --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-bold {{ $isOverLimit ? 'text-red-500' : 'text-[#6C5CE7]' }}">
                                {{ $progressPercent }}%
                            </span>
                            <span class="text-[10px] text-[#7C7896] leading-tight text-center">
                                Jam kerja
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-[#7C7896] mt-2">Progres mingguan</p>
                </div>

                {{-- Stats --}}
                <div class="space-y-4 flex-1">
                    {{-- Shift minggu ini --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#7C7896]">
                            <i data-lucide="calendar-days" class="h-4 w-4 shrink-0"></i>
                            <span class="text-xs">Shift minggu ini</span>
                        </div>
                        <span class="text-sm font-bold text-[#2D2A3E]">
                            {{ ($weeklyAssignments ?? collect())->count() }} shift
                        </span>
                    </div>

                    {{-- Total jam kerja --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#7C7896]">
                            <i data-lucide="clock" class="h-4 w-4 shrink-0"></i>
                            <span class="text-xs">Total jam kerja</span>
                        </div>
                        <span class="text-sm font-bold text-[#2D2A3E]">
                            {{ number_format($weeklyScheduledHours ?? 0, 1, ',', '.') }} jam
                        </span>
                    </div>

                    {{-- Sisa / batas --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#7C7896]">
                            <i data-lucide="hourglass" class="h-4 w-4 shrink-0"></i>
                            <span class="text-xs">Sisa / batas jam</span>
                        </div>
                        <span class="text-sm font-bold {{ $isOverLimit ? 'text-red-500' : 'text-[#2D2A3E]' }}">
                            @if ($isOverLimit)
                                Melebihi batas
                            @else
                                {{ number_format($remainingWeeklyHours ?? 0, 1, ',', '.') }} / {{ number_format($safeWeeklyHourLimit, 0, ',', '.') }} jam
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Progress bar linear (secondary indicator) --}}
                <div class="mt-5 pt-4 border-t border-[#E2E0F7]">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] text-[#7C7896]">{{ number_format($safeScheduledHours, 1, ',', '.') }} dari {{ number_format($safeWeeklyHourLimit, 0, ',', '.') }} jam</span>
                        @if ($isOverLimit)
                            <span class="text-[11px] font-semibold text-red-500">Melebihi batas</span>
                        @endif
                    </div>
                    <div class="h-1.5 bg-[#E2E0F7] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $isOverLimit ? 'bg-red-500' : 'bg-[#6C5CE7]' }}"
                             style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            </div>

            {{-- ===== PANEL KANAN — Greeting & Today's Activity (3/5) ===== --}}
            <div class="lg:col-span-3 flex flex-col gap-4">

                {{-- Greeting card --}}
                <div class="bg-[#6C5CE7] rounded-2xl p-6 text-white relative overflow-hidden">
                    {{-- Decorative circles --}}
                    <div class="absolute -top-6 -right-6 w-28 h-28 bg-white/10 rounded-full pointer-events-none"></div>
                    <div class="absolute -bottom-8 -right-2 w-20 h-20 bg-white/5 rounded-full pointer-events-none"></div>

                    <div class="relative">
                        <p class="text-sm font-medium text-white/70 uppercase tracking-widest mb-1">
                            Employee Workspace
                        </p>
                        <h2 class="text-2xl font-bold leading-tight">
                            {{ $greeting }},<br>
                            <span class="text-white/90">{{ Auth::user()->name }}!</span>
                        </h2>
                        <p class="text-sm text-white/70 mt-2 mb-4">
                            Semoga harimu produktif. Yuk, cek jadwal kerjamu!
                        </p>
                        <a href="{{ route('employee.schedule') }}"
                           class="inline-flex items-center gap-2 bg-white text-[#6C5CE7] font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-[#F5F3FF] transition-colors">
                            Lihat Jadwal Saya
                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </a>
                    </div>
                </div>

                {{-- Today's shift activity --}}
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden flex-1">
                    <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-[#2D2A3E]">Jadwal Shift Hari Ini</h3>
                            <p class="text-xs text-[#7C7896] mt-0.5">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                        @if (($todayAssignments ?? collect())->isNotEmpty())
                            <span class="inline-flex items-center gap-1 bg-[#6C5CE7]/10 text-[#6C5CE7] text-xs font-semibold px-2.5 py-1 rounded-full">
                                <i data-lucide="check-circle" class="h-3 w-3"></i>
                                {{ ($todayAssignments ?? collect())->count() }} shift
                            </span>
                        @endif
                    </div>

                    @if (($todayAssignments ?? collect())->isEmpty())
                        <div class="px-5 py-6 flex items-start gap-4">
                            <div class="w-10 h-10 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                                <i data-lucide="calendar-x" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[#2D2A3E]">Belum ada shift hari ini</p>
                                <p class="text-xs text-[#7C7896] mt-1">Jadwal berikutnya akan ditampilkan di sini.</p>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-[#E2E0F7]">
                            @foreach (($todayAssignments ?? collect()) as $assignment)
                                <div class="px-5 py-3.5 flex items-center gap-3">
                                    <div class="w-9 h-9 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-lg flex items-center justify-center">
                                        <i data-lucide="calendar-clock" class="h-4 w-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-[#2D2A3E] truncate">
                                            {{ $assignment->shift?->title ?? 'Shift' }}
                                        </p>
                                        <p class="text-xs text-[#7C7896]">
                                            {{ $assignment->shift?->start_time?->format('H:i') ?? '-' }}
                                            –
                                            {{ $assignment->shift?->end_time?->format('H:i') ?? '-' }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full
                                        {{ $assignment->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-[#F5F3FF] text-[#6C5CE7]' }}">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status ?? 'Terjadwal')) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== WEEKLY SCHEDULE PREVIEW ===== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">Jadwal Mingguan</h2>
                    <p class="text-xs text-[#7C7896] mt-0.5">
                        {{ now()->startOfWeek()->translatedFormat('d M') }} – {{ now()->endOfWeek()->translatedFormat('d M Y') }}
                    </p>
                </div>
                <a href="{{ route('employee.schedule') }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline whitespace-nowrap">
                    Lihat kalender lengkap
                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </div>

            {{-- Day columns (horizontal scroll on mobile) --}}
            <div class="grid grid-cols-7 divide-x divide-[#E2E0F7]">
                @foreach ($weekDays as $wd)
                    <div class="flex flex-col items-center py-4 px-1
                        {{ $wd['isToday'] ? 'bg-[#6C5CE7]/5' : '' }}">

                        {{-- Day label --}}
                        <span class="text-[10px] font-semibold tracking-wider uppercase
                            {{ $wd['isToday'] ? 'text-[#6C5CE7]' : 'text-[#7C7896]' }}">
                            {{ $wd['dayLabel'] }}
                        </span>

                        {{-- Day number --}}
                        <div class="mt-1.5 w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold
                            {{ $wd['isToday'] ? 'bg-[#6C5CE7] text-white' : 'text-[#2D2A3E]' }}">
                            {{ $wd['dayNum'] }}
                        </div>

                        {{-- Shift dots / pills --}}
                        <div class="mt-2 flex flex-col items-center gap-1 w-full px-1 min-h-[2rem]">
                            @if ($wd['assignments']->isEmpty())
                                <div class="w-1.5 h-1.5 rounded-full bg-[#E2E0F7]"></div>
                            @else
                                @foreach ($wd['assignments'] as $wa)
                                    <div class="w-full text-center">
                                        {{-- Pill for md+ screens --}}
                                        <div class="hidden md:block bg-[#6C5CE7]/10 text-[#6C5CE7] rounded px-1 py-0.5">
                                            <p class="text-[9px] font-semibold leading-tight truncate">
                                                {{ $wa->shift?->start_time?->format('H:i') ?? '?' }}
                                            </p>
                                            <p class="text-[9px] text-[#A29BFE] leading-tight truncate">
                                                {{ $wa->shift?->end_time?->format('H:i') ?? '?' }}
                                            </p>
                                        </div>
                                        {{-- Dot for small screens --}}
                                        <div class="md:hidden w-2 h-2 rounded-full bg-[#6C5CE7] mx-auto"></div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== OPEN SHIFT PREVIEW ===== --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">Lowongan Shift Terbuka</h2>
                    <p class="text-xs text-[#7C7896] mt-0.5">
                        Preview lowongan, waktu, dan ketersediaan slot.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if (($openShifts ?? collect())->isNotEmpty())
                        <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                            Tersedia
                        </span>
                    @endif
                    <a href="{{ route('employee.open-shifts') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline whitespace-nowrap">
                        Lihat semua lowongan
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>
            </div>

            @if (($openShifts ?? collect())->isEmpty())
                <div class="px-5 py-8 text-center">
                    <div class="mx-auto w-12 h-12 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar-search" class="h-6 w-6"></i>
                    </div>
                    <p class="text-sm font-semibold text-[#2D2A3E] mt-3">Belum ada lowongan shift</p>
                    <p class="text-xs text-[#7C7896] mt-1">
                        Lowongan shift yang tersedia akan muncul di sini.
                        <a href="{{ route('employee.open-shifts') }}" class="text-[#6C5CE7] font-medium hover:underline">Lihat halaman open shift</a>
                    </p>
                </div>
            @else
                {{-- Open shift table header (desktop only) --}}
                <div class="hidden md:grid grid-cols-12 px-5 py-2.5 bg-[#F5F3FF] border-b border-[#E2E0F7] text-[11px] font-semibold text-[#7C7896] uppercase tracking-wider">
                    <div class="col-span-5">Nama shift</div>
                    <div class="col-span-4">Waktu</div>
                    <div class="col-span-3 text-right">Slot tersedia</div>
                </div>

                <div class="divide-y divide-[#E2E0F7]">
                    @foreach (($openShifts ?? collect()) as $shift)
                        <div class="px-5 py-3.5 flex items-center gap-4 md:grid md:grid-cols-12">
                            {{-- Mobile icon (hidden on md) --}}
                            <div class="md:hidden w-9 h-9 shrink-0 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                                <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                            </div>

                            {{-- Name --}}
                            <div class="flex-1 min-w-0 md:col-span-5 flex items-center gap-3">
                                <div class="hidden md:flex w-8 h-8 shrink-0 bg-emerald-50 text-emerald-600 rounded-lg items-center justify-center">
                                    <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-[#2D2A3E] truncate">
                                        {{ $shift->title }}
                                    </p>
                                    <p class="text-xs text-[#7C7896] md:hidden">
                                        {{ $shift->start_time?->translatedFormat('D, d M Y') ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Time (desktop) --}}
                            <div class="hidden md:flex md:col-span-4 flex-col">
                                <span class="text-xs font-medium text-[#2D2A3E]">
                                    {{ $shift->start_time?->translatedFormat('D, d M Y') ?? '-' }}
                                </span>
                                <span class="text-xs text-[#7C7896]">
                                    {{ $shift->start_time?->format('H:i') ?? '-' }} – {{ $shift->end_time?->format('H:i') ?? '-' }}
                                </span>
                            </div>

                            {{-- Slot / status badge --}}
                            <div class="md:col-span-3 md:text-right shrink-0">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                    <i data-lucide="users" class="h-3 w-3"></i>
                                    {{ $shift->quota ?? '—' }} slot
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</x-app-layout>