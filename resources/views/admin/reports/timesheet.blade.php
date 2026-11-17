<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-indigo-600">
                            Admin
                        </a>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li aria-current="page" class="font-semibold text-indigo-600">
                        Rekap Timesheet
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight mt-1">
                    Rekapitulasi Timesheet & Ekspor
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Monitoring agregasi jam kerja reguler, jam lembur, presensi kehadiran, dan keterlambatan seluruh karyawan.
                </p>
            </div>

            {{-- Tombol Aksi Ekspor --}}
            <div class="flex items-center flex-wrap gap-2.5">
                <a href="{{ route('admin.reports.timesheet.export-excel', request()->all()) }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-4 py-2.5 font-semibold text-sm shadow-card inline-flex items-center gap-2 transition duration-150"
                   title="Unduh File Rekap Excel (.xlsx)">
                    <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                    <span>Ekspor Excel</span>
                </a>

                <a href="{{ route('admin.reports.timesheet.export-pdf', request()->all()) }}" 
                   class="bg-rose-600 hover:bg-rose-700 text-white rounded-xl px-4 py-2.5 font-semibold text-sm shadow-card inline-flex items-center gap-2 transition duration-150"
                   title="Unduh File Rekap Dokumen PDF (.pdf)">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    <span>Ekspor PDF</span>
                </a>
            </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- KPI Cards Ringkasan Eksekutif --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            {{-- Total Karyawan & Kehadiran --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Karyawan & Kehadiran</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">{{ $totalMetrics->total_employees }} <span class="text-xs font-medium text-slate-500">Orang</span></h3>
                        <p class="text-[10px] sm:text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1 truncate">
                            <i data-lucide="check-circle-2" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>{{ $totalMetrics->total_attendance_shifts }} shift</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 border border-slate-200 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Jam Reguler --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Reguler</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totalMetrics->total_regular_hours, 2) }} <span class="text-xs font-medium text-slate-500">Jam</span></h3>
                        <p class="text-[10px] sm:text-xs text-slate-500 mt-1 truncate">Rata-rata {{ $totalMetrics->total_employees > 0 ? number_format($totalMetrics->total_regular_hours / $totalMetrics->total_employees, 1) : 0 }} jam</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Jam Lembur --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Lembur</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalMetrics->total_overtime_hours, 2) }} <span class="text-xs font-medium text-slate-500">Jam</span></h3>
                        <p class="text-[10px] sm:text-xs text-amber-600 font-medium mt-1 flex items-center gap-1 truncate">
                            <i data-lucide="trending-up" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>Lembur aktif</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="briefcase" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Keterlambatan --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Keterlambatan</p>
                        <h3 class="text-xl sm:text-2xl font-bold {{ $totalMetrics->total_late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                            {{ $totalMetrics->total_late_minutes }} <span class="text-xs font-medium text-slate-500">Menit</span>
                        </h3>
                        <p class="text-[10px] sm:text-xs text-slate-500 mt-1 truncate">
                            {{ $totalMetrics->total_late_minutes > 0 ? 'Waktu terlambat' : 'Tepat waktu' }}
                        </p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $totalMetrics->total_late_minutes > 0 ? 'bg-rose-50 border-rose-100 text-rose-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600' }} border flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card">
            <form method="GET" action="{{ route('admin.reports.timesheet') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    {{-- Input Pilih Bulan --}}
                    <div>
                        <label for="filter-month" class="block text-xs font-semibold text-slate-900 mb-1.5">
                            Bulan Periode
                        </label>
                        <div class="relative">
                            <select id="filter-month" name="month" 
                                    class="w-full bg-indigo-50/40 border border-slate-200 text-slate-900 text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition">
                                @foreach ($monthNames as $mNum => $mLabel)
                                    <option value="{{ $mNum }}" {{ (int) $month === (int) $mNum ? 'selected' : '' }}>
                                        {{ $mLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Input Pilih Tahun --}}
                    <div>
                        <label for="filter-year" class="block text-xs font-semibold text-slate-900 mb-1.5">
                            Tahun
                        </label>
                        <div class="relative">
                            <select id="filter-year" name="year" 
                                    class="w-full bg-indigo-50/40 border border-slate-200 text-slate-900 text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition">
                                @for ($y = now()->year + 1; $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Input Pilih Departemen --}}
                    <div>
                        <label for="filter-department" class="block text-xs font-semibold text-slate-900 mb-1.5">
                            Departemen
                        </label>
                        <div class="relative">
                            <select id="filter-department" name="department_id" 
                                    class="w-full bg-indigo-50/40 border border-slate-200 text-slate-900 text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition">
                                <option value="">Semua Departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2.5 shrink-0">
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition duration-150 shadow-sm">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    
                    @if (request()->hasAny(['month', 'year', 'department_id']))
                        <a href="{{ route('admin.reports.timesheet') }}" 
                           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-900 bg-indigo-50 hover:bg-[#E2E8F0] transition"
                           title="Reset Filter ke Periode Saat Ini">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Rekapitulasi Timesheet (Card Style with Brand Borders & Zebra Stripe) --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                        <i data-lucide="table" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm md:text-base font-bold text-slate-900">
                            Rekapitulasi Jam Kerja Karyawan
                        </h2>
                        <p class="text-xs text-slate-500">
                            Total {{ $reports->count() }} karyawan • Menampilkan periode {{ $periodLabel }}
                        </p>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-slate-200">
                        <i data-lucide="building" class="h-3.5 w-3.5"></i>
                        <span>{{ $selectedDepartment?->name ?? 'Seluruh Departemen' }}</span>
                    </span>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="p-12 text-center">
                    <div class="max-w-md mx-auto flex flex-col items-center">
                        <div class="w-16 h-16 bg-indigo-50 border border-slate-200 rounded-xl flex items-center justify-center text-indigo-600 mb-4 shadow-card">
                            <i data-lucide="file-question" class="h-8 w-8"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Belum Ada Data Rekap Timesheet</h3>
                        <p class="text-xs md:text-sm text-slate-500 leading-relaxed mb-4">
                            Tidak ditemukan data karyawan atau shift assignment pada periode dan kriteria filter yang dipilih.
                        </p>
                        <a href="{{ route('admin.reports.timesheet') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Kembali ke Periode Saat Ini</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm text-slate-900 min-w-[780px]">
                        <thead class="bg-indigo-50 text-slate-900 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-center w-14">No</th>
                                <th class="px-5 py-3.5">Nama Karyawan</th>
                                <th class="px-5 py-3.5">Departemen</th>
                                <th class="px-5 py-3.5 text-center whitespace-nowrap">Shift Dijadwalkan</th>
                                <th class="px-5 py-3.5 text-center whitespace-nowrap">Shift Dihadiri</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Jam Reguler</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Jam Lembur</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Keterlambatan</th>
                                <th class="px-5 py-3.5 text-center w-28 whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($reports as $item)
                                <tr class="even:bg-indigo-50/40 hover:bg-indigo-50/70 transition duration-150">
                                    {{-- No --}}
                                    <td class="px-5 py-4 text-center font-medium text-slate-500">
                                        {{ $loop->iteration }}
                                    </td>

                                    {{-- Nama Karyawan --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-card">
                                                {{ mb_strtoupper(mb_substr($item->employee_name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                                    {{ $item->employee_name }}
                                                </div>
                                                <div class="text-[11px] text-slate-500">
                                                    {{ $item->employee_email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Departemen --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-slate-900 border border-slate-200">
                                            <i data-lucide="layers" class="h-3 w-3 text-indigo-600"></i>
                                            <span>{{ $item->department_name }}</span>
                                        </span>
                                    </td>

                                    {{-- Shift Dijadwalkan --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap font-medium text-slate-500">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-900">
                                            {{ $item->scheduled_shifts }}
                                        </span>
                                        <span class="text-[11px]">Shift</span>
                                    </td>

                                    {{-- Shift Dihadiri --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="calendar-check" class="h-3 w-3"></i>
                                            <span>{{ $item->attended_shifts }} Shift</span>
                                        </span>
                                    </td>

                                    {{-- Jam Reguler --}}
                                    <td class="px-5 py-4 text-right whitespace-nowrap font-bold text-slate-900">
                                        <span>{{ number_format($item->total_regular_hours, 2) }}</span>
                                        <span class="text-xs font-normal text-slate-500">Jam</span>
                                    </td>

                                    {{-- Jam Lembur --}}
                                    <td class="px-5 py-4 text-right whitespace-nowrap font-bold">
                                        @if ($item->total_overtime_hours > 0)
                                            <span class="text-amber-600">{{ number_format($item->total_overtime_hours, 2) }}</span>
                                        @else
                                            <span class="text-slate-500">{{ number_format($item->total_overtime_hours, 2) }}</span>
                                        @endif
                                        <span class="text-xs font-normal text-slate-500">Jam</span>
                                    </td>

                                    {{-- Keterlambatan --}}
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        @if ($item->total_late_minutes > 0)
                                            <span class="inline-flex items-center gap-1 font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                                <i data-lucide="clock-alert" class="h-3 w-3"></i>
                                                <span>{{ $item->total_late_minutes }} mnt</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-xs">
                                                <i data-lucide="check" class="h-3 w-3"></i>
                                                <span>0 mnt</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        @if ($item->status === 'finalized')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="check-circle" class="h-3 w-3"></i>
                                                <span>Final</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="clock" class="h-3 w-3"></i>
                                                <span>Draft</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-indigo-50/60 font-bold border-t border-slate-200 text-slate-900">
                            <tr>
                                <td colspan="3" class="px-5 py-4 text-right uppercase tracking-wider text-xs text-slate-500">
                                    Total Akumulasi:
                                </td>
                                <td class="px-5 py-4 text-center">
                                    {{ $totalMetrics->total_scheduled_shifts }} Shift
                                </td>
                                <td class="px-5 py-4 text-center text-emerald-700">
                                    {{ $totalMetrics->total_attendance_shifts }} Shift
                                </td>
                                <td class="px-5 py-4 text-right text-indigo-600">
                                    {{ number_format($totalMetrics->total_regular_hours, 2) }} Jam
                                </td>
                                <td class="px-5 py-4 text-right text-amber-600">
                                    {{ number_format($totalMetrics->total_overtime_hours, 2) }} Jam
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600">
                                    {{ $totalMetrics->total_late_minutes }} Menit
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-slate-500">
                                    -
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
