{{-- ========================================================================= --}}
{{-- DASHBOARD SUPER ADMIN & COMPANY ADMIN (COCKPIT OPERASIONAL PLATFORM)      --}}
{{-- ========================================================================= --}}
<div class="space-y-6">

    {{-- --------------------------------------------------------------------- --}}
    {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                               --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Super Admin</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-slate-900 font-medium">Cockpit Operasional</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Ringkasan Platform &amp; Operasional
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kapasitas tenant, cakupan departemen, dan rekapitulasi shift aktif.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs">
                <i data-lucide="user-plus" class="h-3.5 w-3.5"></i>
                <span>Tambah Pengguna</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                <i data-lucide="sliders" class="h-3.5 w-3.5 text-slate-400"></i>
                <span>Pengaturan</span>
            </a>
        </div>
    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 2. METRIC CARDS (HIGH DENSITY FLAT CARDS)                             --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        {{-- Total Perusahaan & Cabang --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 flex flex-col justify-between hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Perusahaan &amp; Cabang</span>
                <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="building-2" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tracking-tight">
                    {{ number_format($companyCount ?? 0) }}
                </p>
                <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Tenant aktif</span>
                    <a href="{{ route('admin.companies.index') }}" 
                       class="font-medium text-[#6C5CE7] hover:underline inline-flex items-center gap-1">
                        <span>Kelola</span>
                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Total Departemen --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 flex flex-col justify-between hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Departemen</span>
                <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="network" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tracking-tight">
                    {{ number_format($departmentCount ?? 0) }}
                </p>
                <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Unit operasional</span>
                    <a href="{{ route('admin.departments.index') }}" 
                       class="font-medium text-[#6C5CE7] hover:underline inline-flex items-center gap-1">
                        <span>Kelola</span>
                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Karyawan Aktif --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 flex flex-col justify-between hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Karyawan Aktif</span>
                <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="users" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tracking-tight">
                    {{ number_format($employeeCount ?? 0) }}
                </p>
                <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Status aktif</span>
                    <a href="{{ route('admin.users.index') }}" 
                       class="font-medium text-slate-700 hover:text-slate-900 hover:underline inline-flex items-center gap-1">
                        <span>Daftar</span>
                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Shift Bulan Ini --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 flex flex-col justify-between hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Shift Bulan Ini</span>
                <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <i data-lucide="calendar-days" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tracking-tight">
                    {{ number_format($monthlyShiftCount ?? 0) }}
                </p>
                <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">{{ now()->translatedFormat('F Y') }}</span>
                    <a href="{{ route('admin.shifts.index') }}" 
                       class="font-medium text-slate-700 hover:text-slate-900 hover:underline inline-flex items-center gap-1">
                        <span>Template</span>
                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- --------------------------------------------------------------------- --}}
    {{-- 3. OPERATIONAL READINESS & CONFIGURATION STATUS PANEL                  --}}
    {{-- --------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Configuration Readiness (2 cols) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="h-7 w-7 rounded-md bg-purple-50 text-[#6C5CE7] flex items-center justify-center">
                        <i data-lucide="sliders" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Kesiapan Konfigurasi Operasional</h3>
                        <p class="text-xs text-slate-500">Status aturan regulasi kerja, toleransi absensi, dan geofencing.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold {{ ($configuredCompanyCount ?? 0) >= ($companyCount ?? 1) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ ($configuredCompanyCount ?? 0) >= ($companyCount ?? 1) ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ ($configuredCompanyCount ?? 0) >= ($companyCount ?? 1) ? 'Terstandarisasi' : 'Butuh Penyesuaian' }}
                </span>
            </div>

            <div class="space-y-3.5">
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-slate-600 font-medium">Perusahaan dengan Aturan Lengkap</span>
                        <span class="text-slate-900 font-bold font-mono">{{ number_format($configuredCompanyCount ?? 0) }} / {{ number_format($companyCount ?? 0) }}</span>
                    </div>
                    @php
                        $cfgPercent = ($companyCount ?? 0) > 0 ? round((($configuredCompanyCount ?? 0) / $companyCount) * 100) : 0;
                    @endphp
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#6C5CE7] rounded-full transition-all duration-300" style="width: {{ $cfgPercent }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Batas Jam Reguler</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">Sesuai PP 35 (40 Jam/Minggu)</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Toleransi Lembur</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">Maks. 4 Jam/Hari atau 18 Jam</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium">Validasi Geofencing</p>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">Radius GPS &amp; Cabang Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Administrative Actions (1 col) --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-1">Akses Cepat Pengawasan</h3>
                <p class="text-xs text-slate-500 mb-3.5">Modul tata kelola dan audit sistem.</p>

                <div class="space-y-2">
                    <a href="{{ route('admin.audit-logs.index') }}" 
                       class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 transition group text-xs text-slate-800">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="shield-check" class="h-4 w-4 text-slate-400 group-hover:text-slate-700"></i>
                            <span class="font-medium">Audit Log Aktivitas</span>
                        </span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('admin.reports.timesheet') }}" 
                       class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 transition group text-xs text-slate-800">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="h-4 w-4 text-slate-400 group-hover:text-slate-700"></i>
                            <span class="font-medium">Laporan &amp; Rekap Timesheet</span>
                        </span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>

                    <a href="{{ route('admin.positions.index') }}" 
                       class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 transition group text-xs text-slate-800">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="badge-check" class="h-4 w-4 text-slate-400 group-hover:text-slate-700"></i>
                            <span class="font-medium">Master Jabatan &amp; Level</span>
                        </span>
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5 text-slate-300 group-hover:text-slate-600 transition"></i>
                    </a>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Versi Platform</span>
                <span class="font-mono text-slate-600">v2.4.0-stable</span>
            </div>
        </div>

    </div>

</div>
