@php
    $role = auth()->user()?->role ?? 'employee';
    $isEmployee = ($role === 'employee');
    $isManager = ($role === 'manager');
    $isAdmin = in_array($role, ['superadmin', 'company_admin']);

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
    } elseif ($isAdmin) {
        $isSecondaryActive = request()->routeIs('admin.settings*')
            || request()->routeIs('admin.audit-logs*')
            || request()->routeIs('admin.reports*')
            || request()->routeIs('admin.companies*')
            || request()->routeIs('profile*');
    }
@endphp

{{-- ========================================================================= --}}
{{-- MOBILE BOTTOM NAVIGATION BAR & DRAWER "LAINNYA" (KHUSUS MOBILE md:hidden) --}}
{{-- ========================================================================= --}}
<div x-data="{ moreDrawerOpen: false }" class="md:hidden">

    {{-- --------------------------------------------------------------------- --}}
    {{-- BOTTOM SHEET DRAWER: MENU LAINNYA (SLIDE UP)                          --}}
    {{-- --------------------------------------------------------------------- --}}
    <div x-show="moreDrawerOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="moreDrawerOpen = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 select-none"
         style="display: none;">
    </div>

    <div x-show="moreDrawerOpen"
         x-transition:enter="transition ease-out duration-250 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl border-t border-slate-200 shadow-2xl overflow-hidden max-h-[85vh] flex flex-col"
         style="display: none; padding-bottom: env(safe-area-inset-bottom, 12px);">
        
        {{-- Drag Handle & Header --}}
        <div class="pt-3 px-4 pb-2.5 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div class="flex-1 flex flex-col items-center pl-7">
                <div class="w-8 h-1 rounded-full bg-slate-200 mb-1.5"></div>
                <h3 class="text-xs font-semibold text-slate-700 tracking-tight">Menu Lainnya</h3>
            </div>
            <button type="button" @click="moreDrawerOpen = false" 
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                    title="Tutup Menu">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        {{-- Isi Menu Tambahan (Grid Cards) --}}
        <div class="p-3.5 overflow-y-auto space-y-3 flex-1">
            @if ($isEmployee)
                <div class="grid grid-cols-2 gap-2">
                    {{-- 1. Shift Terbuka --}}
                    <a href="{{ route('employee.open-shifts') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('employee.open-shifts*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('employee.open-shifts*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="megaphone" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Shift Terbuka</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Klaim jam tambahan</p>
                        </div>
                    </a>

                    {{-- 2. Timesheet --}}
                    <a href="{{ route('employee.timesheet') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('employee.timesheet*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('employee.timesheet*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Timesheet</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Rekap jam kerja</p>
                        </div>
                    </a>

                    {{-- 3. Ketersediaan Waktu --}}
                    <a href="{{ route('employee.availability') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('employee.availability*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('employee.availability*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="calendar-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Ketersediaan</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Jadwal kuliah &amp; izin</p>
                        </div>
                    </a>

                    {{-- 4. Pengaturan Profil --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('profile*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('profile*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Profil Akun</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Data &amp; keamanan</p>
                        </div>
                    </a>
                </div>

            @elseif ($isManager)
                <div class="grid grid-cols-2 gap-2">
                    {{-- 1. Open Shift Manager --}}
                    <a href="{{ route('manager.open-shifts.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('manager.open-shifts*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.open-shifts*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="megaphone" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Shift Terbuka</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Siarkan lowongan</p>
                        </div>
                    </a>

                    {{-- 2. Persetujuan Ketersediaan --}}
                    <a href="{{ route('manager.availability.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('manager.availability*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.availability*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="calendar-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Ketersediaan</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Verifikasi kuliah</p>
                        </div>
                    </a>

                    {{-- 3. Koreksi Kehadiran --}}
                    <a href="{{ route('manager.attendance.regularizations.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('manager.attendance.regularizations*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.attendance.regularizations*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="file-pen-line" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Koreksi Presensi</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Klaim lupa absen</p>
                        </div>
                    </a>

                    {{-- 4. Timesheet & Laporan --}}
                    <a href="{{ route('manager.timesheets.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('manager.timesheets*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.timesheets*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Timesheet</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Rekap &amp; export</p>
                        </div>
                    </a>

                    {{-- 5. Data Karyawan Tim --}}
                    <a href="{{ route('manager.employees.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('manager.employees*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('manager.employees*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="users" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Data Karyawan</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Daftar staf tim</p>
                        </div>
                    </a>

                    {{-- 6. Pengaturan Profil --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('profile*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('profile*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Profil Akun</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Data &amp; keamanan</p>
                        </div>
                    </a>
                </div>

            @else
                <div class="grid grid-cols-2 gap-2">
                    {{-- 1. Pengaturan Perusahaan --}}
                    <a href="{{ route('admin.settings.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('admin.settings*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.settings*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="sliders" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Pengaturan</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Konfigurasi sistem</p>
                        </div>
                    </a>

                    {{-- 2. Audit Logs --}}
                    <a href="{{ route('admin.audit-logs.index') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('admin.audit-logs*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.audit-logs*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Audit Log</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Riwayat aktivitas</p>
                        </div>
                    </a>

                    {{-- 3. Laporan Timesheet --}}
                    <a href="{{ route('admin.reports.timesheet') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('admin.reports*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.reports*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="file-bar-chart" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Laporan</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Export data presensi</p>
                        </div>
                    </a>

                    {{-- 4. Pengaturan Profil --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="p-2.5 rounded-xl border transition flex flex-col justify-between gap-2 {{ request()->routeIs('profile*') ? 'bg-indigo-50/70 border-indigo-200 text-indigo-950 font-semibold' : 'bg-slate-50/60 border-slate-200/80 text-slate-800 hover:bg-white hover:border-slate-300' }}">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ request()->routeIs('profile*') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold leading-tight">Profil Akun</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Data &amp; keamanan</p>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Info User & Logout Bar --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0">
                        {{ auth()->user() ? mb_substr(auth()->user()->name, 0, 1) : 'U' }}
                    </div>
                    <div class="min-w-0 truncate">
                        <p class="text-xs font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                        <p class="text-[10px] text-slate-500 truncate capitalize">{{ auth()->user()->position->name ?? (auth()->user()->role ?? 'Staff') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            title="Keluar"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition shrink-0">
                        <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- BILAH NAVIGASI BAWAH UTAMA (5 SLOTS DENGAN CLEAN ACCENT CENTER ACTION) --}}
    {{-- --------------------------------------------------------------------- --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-2px_10px_rgba(0,0,0,0.03)] select-none"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="grid grid-cols-5 h-14 max-w-lg mx-auto px-1 items-center">
            
            @if ($isEmployee)
                {{-- Slot 1: Beranda --}}
                <a href="{{ route('employee.dashboard') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.dashboard*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="layout-grid" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Beranda</span>
                </a>

                {{-- Slot 2: Jadwal --}}
                <a href="{{ route('employee.schedule') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.schedule*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="calendar" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Jadwal</span>
                </a>

                {{-- Slot 3: Presensi --}}
                <a href="{{ route('employee.attendance') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.attendance*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                   title="Clock-In / Clock-Out">
                    <i data-lucide="map-pin" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Presensi</span>
                </a>

                {{-- Slot 4: Tukar Shift --}}
                <a href="{{ route('employee.swaps') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('employee.swaps*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="arrow-left-right" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Tukar</span>
                </a>

            @elseif ($isManager)
                {{-- Slot 1: Beranda Manajer --}}
                <a href="{{ route('manager.dashboard') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.dashboard*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="layout-grid" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Beranda</span>
                </a>

                {{-- Slot 2: Roster Jadwal Tim --}}
                <a href="{{ route('manager.shifts.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.shifts*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="calendar" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Roster</span>
                </a>

                {{-- Slot 3: Presensi Tim --}}
                <a href="{{ route('manager.attendance.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.attendance*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                   title="Monitoring Presensi Tim">
                    <i data-lucide="user-check" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Presensi</span>
                </a>

                {{-- Slot 4: Persetujuan Tukar --}}
                <a href="{{ route('manager.swaps.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('manager.swaps*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="arrow-left-right" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Tukar</span>
                </a>

            @else
                {{-- Admin (Superadmin & Company Admin) --}}
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('admin.dashboard*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="layout-grid" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('admin.users*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="users" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Pengguna</span>
                </a>

                {{-- Slot 3: Template Shift --}}
                <a href="{{ route('admin.shifts.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('admin.shifts*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                   title="Template Shift">
                    <i data-lucide="calendar" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Shift</span>
                </a>

                <a href="{{ route('admin.departments.index') }}" 
                   class="flex flex-col items-center justify-center gap-1 h-full transition {{ request()->routeIs('admin.departments*') ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    <i data-lucide="briefcase" class="h-4.5 w-4.5"></i>
                    <span class="text-[10px] leading-none">Departemen</span>
                </a>
            @endif

            {{-- Slot 5: Menu Lainnya (Triggers Bottom Sheet Popup) --}}
            <button type="button" 
                    @click="moreDrawerOpen = true"
                    class="flex flex-col items-center justify-center gap-1 h-full transition relative {{ $isSecondaryActive ? 'text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}"
                    title="Menu Lainnya">
                <div class="relative">
                    <i data-lucide="more-horizontal" class="h-4.5 w-4.5"></i>
                    @if ($isSecondaryActive)
                        <span class="absolute -top-1 -right-1 h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                    @endif
                </div>
                <span class="text-[10px] leading-none">Lainnya</span>
            </button>
        </div>
    </nav>
</div>
