@php
    $role = auth()->user()?->role ?? 'employee';
    $isEmployee = ($role === 'employee');
    $isManager = ($role === 'manager');
    $isAdmin = ($role === 'superadmin');

    // Route aktif untuk menu "Lainnya"
    $isSecondaryActive = false;
    if ($isEmployee) {
        $isSecondaryActive = request()->routeIs('employee.open-shifts*') 
            || request()->routeIs('employee.timesheet*') 
            || request()->routeIs('employee.availability*') 
            || request()->routeIs('profile*');
    } elseif ($isManager) {
        $isSecondaryActive = request()->routeIs('manager.open-shifts*') 
            || request()->routeIs('manager.availability*') 
            || request()->routeIs('manager.attendance.regularizations*') 
            || request()->routeIs('manager.timesheets*') 
            || request()->routeIs('manager.employees*') 
            || request()->routeIs('profile*');
    }
@endphp

{{-- ========================================================================= --}}
{{-- MOBILE BOTTOM NAVIGATION BAR & DRAWER "LAINNYA" (KHUSUS MOBILE md:hidden) --}}
{{-- ========================================================================= --}}
<div x-data="{ moreDrawerOpen: false }" class="md:hidden">

    {{-- --------------------------------------------------------------------- --}}
    {{-- BOTTOM SHEET DRAWER: MENU LAINNYA (SLIDE UP POPUP)                     --}}
    {{-- --------------------------------------------------------------------- --}}
    <div x-show="moreDrawerOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="moreDrawerOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 select-none"
         style="display: none;">
    </div>

    <div x-show="moreDrawerOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-3xl border-t border-[#E2E0F7] shadow-2xl overflow-hidden max-h-[85vh] flex flex-col"
         style="display: none; padding-bottom: env(safe-area-inset-bottom, 12px);">
        
        {{-- Drag Handle & Header --}}
        <div class="pt-3 px-5 pb-3 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div class="flex-1 flex flex-col items-center">
                <div class="w-10 h-1 rounded-full bg-slate-300 mb-2"></div>
                <h3 class="text-sm font-bold text-[#2D2A3E]">Menu Lengkap &amp; Pintasan</h3>
            </div>
            <button type="button" @click="moreDrawerOpen = false" 
                    class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-[#F5F3FF] transition"
                    title="Tutup Menu">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        {{-- Isi Menu Tambahan (Grid Cards) --}}
        <div class="p-4 overflow-y-auto space-y-3 flex-1">
            @if ($isEmployee)
                <div class="grid grid-cols-2 gap-2.5">
                    {{-- 1. Shift Terbuka --}}
                    <a href="{{ route('employee.open-shifts') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('employee.open-shifts*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7] shadow-2xs' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E] hover:bg-white' }}">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center {{ request()->routeIs('employee.open-shifts*') ? 'bg-[#6C5CE7] text-white' : 'bg-[#6C5CE7]/10 text-[#6C5CE7]' }}">
                            <i data-lucide="megaphone" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Shift Terbuka</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Klaim jam tambahan</p>
                        </div>
                    </a>

                    {{-- 2. Timesheet --}}
                    <a href="{{ route('employee.timesheet') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('employee.timesheet*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7] shadow-2xs' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E] hover:bg-white' }}">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center {{ request()->routeIs('employee.timesheet*') ? 'bg-[#6C5CE7] text-white' : 'bg-[#6C5CE7]/10 text-[#6C5CE7]' }}">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Timesheet</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Rekap jam kerja</p>
                        </div>
                    </a>

                    {{-- 3. Ketersediaan Waktu --}}
                    <a href="{{ route('employee.availability') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('employee.availability*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7] shadow-2xs' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E] hover:bg-white' }}">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center {{ request()->routeIs('employee.availability*') ? 'bg-[#6C5CE7] text-white' : 'bg-[#6C5CE7]/10 text-[#6C5CE7]' }}">
                            <i data-lucide="calendar-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Ketersediaan</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Jadwal kuliah &amp; izin</p>
                        </div>
                    </a>

                    {{-- 4. Pengaturan Profil --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('profile*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7] shadow-2xs' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E] hover:bg-white' }}">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center {{ request()->routeIs('profile*') ? 'bg-[#6C5CE7] text-white' : 'bg-[#6C5CE7]/10 text-[#6C5CE7]' }}">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Profil Akun</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Data &amp; keamanan</p>
                        </div>
                    </a>
                </div>

            @elseif ($isManager)
                <div class="grid grid-cols-2 gap-2.5">
                    {{-- 1. Open Shift Manager --}}
                    <a href="{{ route('manager.open-shifts.index') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('manager.open-shifts*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="megaphone" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Shift Terbuka</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Siarkan lowongan</p>
                        </div>
                    </a>

                    {{-- 2. Persetujuan Ketersediaan --}}
                    <a href="{{ route('manager.availability.index') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('manager.availability*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="calendar-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Ketersediaan</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Verifikasi kuliah</p>
                        </div>
                    </a>

                    {{-- 3. Koreksi Kehadiran --}}
                    <a href="{{ route('manager.attendance.regularizations.index') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('manager.attendance.regularizations*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="file-pen-line" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Koreksi Presensi</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Klaim lupa absen</p>
                        </div>
                    </a>

                    {{-- 4. Timesheet & Laporan --}}
                    <a href="{{ route('manager.timesheets.index') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('manager.timesheets*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Timesheet</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Rekap &amp; export</p>
                        </div>
                    </a>

                    {{-- 5. Data Karyawan Tim --}}
                    <a href="{{ route('manager.employees.index') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('manager.employees*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="users" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Data Karyawan</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Daftar staf tim</p>
                        </div>
                    </a>

                    {{-- 6. Pengaturan Profil --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="p-3 rounded-2xl border transition flex flex-col justify-between gap-2.5 {{ request()->routeIs('profile*') ? 'bg-[#F5F3FF] border-[#6C5CE7] text-[#6C5CE7]' : 'bg-slate-50/70 border-[#E2E0F7] text-[#2D2A3E]' }}">
                        <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight">Profil Akun</p>
                            <p class="text-[10px] text-[#7C7896] mt-0.5">Data &amp; keamanan</p>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Info User & Logout Bar --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="h-9 w-9 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-xs font-bold shrink-0">
                        {{ auth()->user() ? mb_substr(auth()->user()->name, 0, 1) : 'U' }}
                    </div>
                    <div class="min-w-0 truncate">
                        <p class="text-xs font-bold text-[#2D2A3E] truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                        <p class="text-[10px] text-[#7C7896] truncate capitalize">{{ auth()->user()->position->name ?? (auth()->user()->role ?? 'Staff') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition shrink-0">
                        <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- BILAH NAVIGASI BAWAH UTAMA (5 SLOTS DENGAN ELEVATED CENTER BUTTON)    --}}
    {{-- --------------------------------------------------------------------- --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#E2E0F7] shadow-[0_-4px_20px_rgba(0,0,0,0.05)] select-none"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="grid grid-cols-5 h-16 max-w-lg mx-auto px-1 items-center">
            
            @if ($isEmployee)
                {{-- Slot 1: Beranda --}}
                <a href="{{ route('employee.dashboard') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.dashboard*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                    <div class="relative">
                        <i data-lucide="layout-grid" class="h-5 w-5"></i>
                        @if (request()->routeIs('employee.dashboard*'))
                            <span class="absolute -bottom-1 inset-x-0 h-1 bg-[#6C5CE7] rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-[10px] leading-none">Beranda</span>
                </a>

                {{-- Slot 2: Jadwal --}}
                <a href="{{ route('employee.schedule') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.schedule*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                    <div class="relative">
                        <i data-lucide="calendar" class="h-5 w-5"></i>
                        @if (request()->routeIs('employee.schedule*'))
                            <span class="absolute -bottom-1 inset-x-0 h-1 bg-[#6C5CE7] rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-[10px] leading-none">Jadwal</span>
                </a>

                {{-- Slot 3: Presensi (Elevated Center Action Button) --}}
                <div class="flex items-center justify-center h-full relative -top-3">
                    <a href="{{ route('employee.attendance') }}" 
                       class="h-13 w-13 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#8C7AE6] text-white flex flex-col items-center justify-center shadow-lg shadow-[#6C5CE7]/30 ring-4 ring-white hover:scale-105 active:scale-95 transition-transform"
                       title="Clock-In / Clock-Out">
                        <i data-lucide="map-pin" class="h-5 w-5"></i>
                        <span class="text-[8px] font-extrabold uppercase mt-0.5 tracking-tighter">Absen</span>
                    </a>
                </div>

                {{-- Slot 4: Tukar Shift --}}
                <a href="{{ route('employee.swaps') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.swaps*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                    <div class="relative">
                        <i data-lucide="arrow-left-right" class="h-5 w-5"></i>
                        @if (request()->routeIs('employee.swaps*'))
                            <span class="absolute -bottom-1 inset-x-0 h-1 bg-[#6C5CE7] rounded-full"></span>
                        @endif
                    </div>
                    <span class="text-[10px] leading-none">Tukar</span>
                </a>

            @elseif ($isManager)
                {{-- Slot 1: Beranda Manajer --}}
                <a href="{{ route('manager.dashboard') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.dashboard*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896]' }}">
                    <i data-lucide="layout-grid" class="h-5 w-5"></i>
                    <span class="text-[10px] leading-none">Beranda</span>
                </a>

                {{-- Slot 2: Roster Jadwal Tim --}}
                <a href="{{ route('manager.shifts.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.shifts*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896]' }}">
                    <i data-lucide="calendar" class="h-5 w-5"></i>
                    <span class="text-[10px] leading-none">Roster</span>
                </a>

                {{-- Slot 3: Presensi Tim (Elevated Center) --}}
                <div class="flex items-center justify-center h-full relative -top-3">
                    <a href="{{ route('manager.attendance.index') }}" 
                       class="h-13 w-13 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#8C7AE6] text-white flex flex-col items-center justify-center shadow-lg shadow-[#6C5CE7]/30 ring-4 ring-white hover:scale-105 active:scale-95 transition-transform"
                       title="Monitoring Presensi Tim">
                        <i data-lucide="user-check" class="h-5 w-5"></i>
                        <span class="text-[8px] font-extrabold uppercase mt-0.5 tracking-tighter">Tim</span>
                    </a>
                </div>

                {{-- Slot 4: Persetujuan Tukar --}}
                <a href="{{ route('manager.swaps.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.swaps*') ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896]' }}">
                    <i data-lucide="arrow-left-right" class="h-5 w-5"></i>
                    <span class="text-[10px] leading-none">Tukar</span>
                </a>
            @else
                {{-- Fallback Superadmin --}}
                <a href="{{ route('admin.dashboard') }}" class="col-span-2 flex flex-col items-center justify-center gap-1 h-full text-[#6C5CE7] font-bold">
                    <i data-lucide="layout-grid" class="h-5 w-5"></i>
                    <span class="text-[10px]">Dashboard</span>
                </a>
                <div class="col-span-1"></div>
                <a href="{{ route('admin.users.index') }}" class="col-span-1 flex flex-col items-center justify-center gap-1 h-full text-[#7C7896]">
                    <i data-lucide="users" class="h-5 w-5"></i>
                    <span class="text-[10px]">Pengguna</span>
                </a>
            @endif

            {{-- Slot 5: Menu Lainnya (Triggers Bottom Sheet Popup) --}}
            <button type="button" 
                    @click="moreDrawerOpen = true"
                    class="flex flex-col items-center justify-center gap-1 h-full transition relative {{ $isSecondaryActive ? 'text-[#6C5CE7] font-bold' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}"
                    title="Menu Lainnya">
                <div class="relative">
                    <i data-lucide="more-horizontal" class="h-5 w-5"></i>
                    @if ($isSecondaryActive)
                        <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-[#6C5CE7]"></span>
                    @endif
                </div>
                <span class="text-[10px] leading-none">Lainnya</span>
            </button>
        </div>
    </nav>
</div>
