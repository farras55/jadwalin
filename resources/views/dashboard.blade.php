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
        <div class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-[#2D2A3E]">
                    Ringkasan Operasional Platform
                </h2>

                <p class="text-sm text-[#7C7896] mt-1">
                    Ringkasan perusahaan dan aktivitas penjadwalan pada platform Jadwalin.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Total Perusahaan --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl">
                            <i data-lucide="building-2" class="h-5 w-5"></i>
                        </div>

                        <span class="text-xs font-semibold text-[#7C7896]">
                            Seluruh platform
                        </span>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Total Perusahaan</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($companyCount ?? 0) }}
                    </p>

                    <a href="{{ route('admin.companies.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] mt-3 hover:underline">
                        Kelola perusahaan
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Total Departemen --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <i data-lucide="network" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Total Departemen</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($departmentCount ?? 0) }}
                    </p>

                    <a href="{{ route('admin.departments.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] mt-3 hover:underline">
                        Kelola departemen
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Karyawan Aktif --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <i data-lucide="users" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Karyawan Aktif</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($employeeCount ?? 0) }}
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Seluruh perusahaan
                    </p>
                </div>

                {{-- Shift Bulan Ini --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                            <i data-lucide="calendar-days" class="h-5 w-5"></i>
                        </div>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Shift Bulan Ini</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($monthlyShiftCount ?? 0) }}
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Berdasarkan waktu mulai shift
                    </p>
                </div>
            </div>

            {{-- Status konfigurasi perusahaan --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 mt-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-[#F5F3FF] text-[#6C5CE7] rounded-lg">
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
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- DASHBOARD KHUSUS MANAGER --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Manager')
        <div class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-[#2D2A3E]">
                    Ringkasan Tim
                </h2>

                <p class="text-sm text-[#7C7896] mt-1">
                    Pantau anggota tim, kehadiran, jadwal, dan pengajuan yang membutuhkan persetujuan.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Anggota Tim --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl w-fit">
                        <i data-lucide="users" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Anggota Tim Aktif</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($teamCount ?? 0) }}
                    </p>

                    <a href="{{ route('manager.employees.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] mt-3 hover:underline">
                        Lihat anggota tim
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Kehadiran --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl w-fit">
                        <i data-lucide="user-check" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Kehadiran Hari Ini</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($presentStaffCount ?? 0) }}
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Penugasan berstatus hadir
                    </p>
                </div>

                {{-- Jadwal Mingguan --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl w-fit">
                        <i data-lucide="calendar-days" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Penugasan Minggu Ini</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($weeklyRosterCount ?? 0) }}
                    </p>

                    <a href="{{ route('manager.shifts.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] mt-3 hover:underline">
                        Lihat jadwal shift
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                </div>

                {{-- Persetujuan --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl w-fit">
                        <i data-lucide="clipboard-check" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Menunggu Persetujuan</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format(($pendingAvailabilityCount ?? 0) + ($pendingSwapCount ?? 0) + ($openShiftClaimCount ?? 0)) }}
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Ketersediaan, pertukaran, dan klaim shift
                    </p>
                </div>
            </div>

            {{-- Rincian permintaan yang menunggu --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">

                <a href="{{ route('manager.availability.index') }}"
                   class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7]/50 transition">
                    <p class="text-xs text-[#7C7896]">Pengajuan Ketersediaan</p>
                    <p class="text-xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($pendingAvailabilityCount ?? 0) }}
                    </p>
                    <p class="text-xs font-semibold text-[#6C5CE7] mt-2">
                        Tinjau pengajuan
                    </p>
                </a>

                <a href="{{ route('manager.swaps.index') }}"
                   class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7]/50 transition">
                    <p class="text-xs text-[#7C7896]">Permintaan Pertukaran Shift</p>
                    <p class="text-xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($pendingSwapCount ?? 0) }}
                    </p>
                    <p class="text-xs font-semibold text-[#6C5CE7] mt-2">
                        Tinjau pertukaran
                    </p>
                </a>

                <a href="{{ route('manager.open-shifts.index') }}"
                   class="bg-white p-4 rounded-2xl border border-[#E2E0F7] hover:border-[#6C5CE7]/50 transition">
                    <p class="text-xs text-[#7C7896]">Klaim Shift Terbuka</p>
                    <p class="text-xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($openShiftClaimCount ?? 0) }}
                    </p>
                    <p class="text-xs font-semibold text-[#6C5CE7] mt-2">
                        Tinjau klaim
                    </p>
                </a>
            </div>
        </div>

        {{-- Quick Alert Manager --}}
        <div class="w-full max-w-3xl mb-8">
            <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">

                <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#2D2A3E]">
                            Quick Alert
                        </h2>

                        <p class="text-xs text-[#7C7896] mt-0.5">
                            Perhatian yang membutuhkan tindakan Anda.
                        </p>
                    </div>

                    @if (($pendingAvailabilityCount ?? 0) > 0)
                        <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-bold">
                            {{ $pendingAvailabilityCount }} Menunggu
                        </span>
                    @else
                        <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-bold">
                            Tidak ada pengajuan
                        </span>
                    @endif
                </div>

                @if ($pendingAvailability ?? null)
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-4">

                            <div class="w-10 h-10 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                                <i data-lucide="calendar-check" class="h-5 w-5"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-[#2D2A3E]">
                                        Persetujuan Ketersediaan
                                    </h3>

                                    <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                        Menunggu
                                    </span>
                                </div>

                                <p class="text-xs text-[#7C7896] mt-1">
                                    {{ $pendingAvailability->user->name ?? 'Karyawan' }}

                                    <span class="mx-1">•</span>

                                    {{ $pendingAvailability->start_time?->translatedFormat('D, d M') ?? '-' }}

                                    <span class="mx-1">•</span>

                                    {{ $pendingAvailability->start_time?->format('H:i') ?? '-' }}
                                    -
                                    {{ $pendingAvailability->end_time?->format('H:i') ?? '-' }}
                                </p>
                            </div>

                            <a href="{{ route('manager.availability.index') }}"
                               class="shrink-0 inline-flex items-center gap-1.5 bg-[#6C5CE7] hover:bg-[#5B4BD5] text-white rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                Tinjau
                                <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="px-5 py-6 flex items-center gap-4">
                        <div class="w-10 h-10 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="h-5 w-5"></i>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-[#2D2A3E]">
                                Tidak ada pengajuan ketersediaan
                            </p>

                            <p class="text-xs text-[#7C7896] mt-1">
                                Saat ini tidak ada pengajuan ketersediaan yang menunggu.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- DASHBOARD KHUSUS KARYAWAN --}}
    {{-- ========================================================= --}}

    @if (($roleTitle ?? '') === 'Karyawan')
        <div class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-[#2D2A3E]">
                    Ringkasan Jadwal Saya
                </h2>

                <p class="text-sm text-[#7C7896] mt-1">
                    Lihat penugasan shift dan estimasi jam kerja terjadwal Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                {{-- Shift Hari Ini --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl w-fit">
                        <i data-lucide="calendar-check" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Shift Hari Ini</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ ($todayAssignments ?? collect())->count() }}
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Penugasan berdasarkan jadwal mulai shift
                    </p>
                </div>

                {{-- Jam Terjadwal --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl w-fit">
                        <i data-lucide="clock" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Jam Terjadwal Minggu Ini</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($weeklyScheduledHours ?? 0, 2, ',', '.') }}
                        <span class="text-sm font-medium">jam</span>
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Estimasi dari waktu mulai dan selesai shift
                    </p>
                </div>

                {{-- Sisa Batas Jam --}}
                <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl w-fit">
                        <i data-lucide="hourglass" class="h-5 w-5"></i>
                    </div>

                    <p class="text-sm text-[#7C7896] mt-4">Sisa Batas Jam Mingguan</p>

                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1">
                        {{ number_format($remainingWeeklyHours ?? 0, 2, ',', '.') }}
                        <span class="text-sm font-medium">jam</span>
                    </p>

                    <p class="text-xs text-[#7C7896] mt-3">
                        Dari batas {{ number_format($weeklyHourLimit ?? 40, 2, ',', '.') }} jam per minggu
                    </p>
                </div>
            </div>
        </div>

        {{-- Jadwal Shift Hari Ini --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">
                        Jadwal Shift Hari Ini
                    </h2>

                    <p class="text-xs text-[#7C7896] mt-1">
                        Daftar penugasan yang dimulai hari ini.
                    </p>
                </div>

                <a href="{{ route('employee.schedule') }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline">
                    Lihat jadwal lengkap
                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </div>

            <div class="divide-y divide-[#E2E0F7]">
                @forelse (($todayAssignments ?? collect()) as $assignment)
                    <div class="px-5 py-4 flex items-center gap-4">
                        <div class="w-11 h-11 shrink-0 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-[#2D2A3E]">
                                {{ $assignment->shift?->title ?? 'Shift' }}
                            </h3>

                            <p class="text-xs text-[#7C7896] mt-1">
                                {{ $assignment->shift?->start_time?->format('H:i') ?? '-' }}
                                -
                                {{ $assignment->shift?->end_time?->format('H:i') ?? '-' }}
                            </p>
                        </div>

                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $assignment->status === 'present'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-[#F5F3FF] text-[#6C5CE7]' }}">
                            {{ ucfirst(str_replace('_', ' ', $assignment->status ?? 'Terjadwal')) }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <div class="mx-auto w-12 h-12 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-x" class="h-6 w-6"></i>
                        </div>

                        <p class="text-sm font-semibold text-[#2D2A3E] mt-3">
                            Belum ada shift hari ini
                        </p>

                        <p class="text-xs text-[#7C7896] mt-1">
                            Penugasan shift Anda akan muncul di sini jika tersedia.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Lowongan Shift Terbuka --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">
                        Lowongan Shift Terbuka
                    </h2>

                    <p class="text-xs text-[#7C7896] mt-1">
                        Shift yang tersedia untuk diajukan.
                    </p>
                </div>

                <a href="{{ route('employee.open-shifts') }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-[#6C5CE7] hover:underline">
                    Lihat semua
                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </div>

            <div class="divide-y divide-[#E2E0F7]">
                @forelse (($openShifts ?? collect()) as $shift)
                    <div class="px-5 py-4 flex items-center gap-4">
                        <div class="w-11 h-11 shrink-0 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-plus" class="h-5 w-5"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-[#2D2A3E]">
                                {{ $shift->title }}
                            </h3>

                            <p class="text-xs text-[#7C7896] mt-1">
                                {{ $shift->start_time?->translatedFormat('D, d M Y') ?? '-' }}
                                <span class="mx-1">•</span>
                                {{ $shift->start_time?->format('H:i') ?? '-' }}
                                -
                                {{ $shift->end_time?->format('H:i') ?? '-' }}
                            </p>
                        </div>

                        <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                            Tersedia
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <div class="mx-auto w-12 h-12 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-search" class="h-6 w-6"></i>
                        </div>

                        <p class="text-sm font-semibold text-[#2D2A3E] mt-3">
                            Belum ada lowongan shift
                        </p>

                        <p class="text-xs text-[#7C7896] mt-1">
                            Lowongan shift yang tersedia akan muncul di sini.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</x-app-layout>