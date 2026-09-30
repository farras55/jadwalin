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

    {{-- Kartu Ringkasan Informasi Pengguna --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="building-2" class="h-6 w-6"></i>
            </div>
            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Perusahaan</p>
                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                    {{ Auth::user()->company->name ?? 'Belum terhubung' }}
                </p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="briefcase" class="h-6 w-6"></i>
            </div>
            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Departemen & Posisi</p>
                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                    {{ Auth::user()->position->name ?? (Auth::user()->department->name ?? 'Staf Operasional') }}
                </p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center gap-4">
            <div class="p-3 bg-[#F5F3FF] text-[#6C5CE7] rounded-xl shrink-0">
                <i data-lucide="calendar" class="h-6 w-6"></i>
            </div>
            <div class="truncate">
                <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Status Keanggotaan</p>
                <p class="text-sm md:text-base font-bold text-[#2D2A3E] truncate capitalize">
                    {{ Auth::user()->status ?? 'Aktif' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Area Konten Modul Utama --}}
    <div class="bg-white rounded-2xl border border-[#E2E0F7] p-8 text-center shadow-2xs">
        <div class="max-w-md mx-auto flex flex-col items-center">
            <div class="w-14 h-14 bg-[#F5F3FF] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4">
                <i data-lucide="layout-dashboard" class="h-7 w-7"></i>
            </div>
            <h3 class="text-base font-semibold text-[#2D2A3E] mb-1">Ruang Kerja Dasbor Siap</h3>
            <p class="text-xs md:text-sm text-[#7C7896] mb-5">
                Layout dan navigasi telah terintegrasi dinamis dengan data pengguna. Modul jadwal, tukar shift, dan presensi dapat dihubungkan ke sini kapan saja tanpa mengubah layout lagi.
            </p>
        </div>
    </div>
</x-app-layout>
