<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                        PBL-21 Modul Timesheet
                    </span>
                    <span class="text-xs text-[#7C7896]">•</span>
                    <span class="text-xs font-medium text-[#7C7896]">{{ $periodLabel }}</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight mt-1">
                    Rekapitulasi Timesheet & Ekspor
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                    Monitoring agregasi jam kerja reguler, jam lembur, presensi kehadiran, dan keterlambatan seluruh karyawan.
                </p>
            </div>

            {{-- Tombol Aksi Ekspor --}}
            <div class="flex items-center flex-wrap gap-2.5">
                <a href="{{ route('admin.reports.timesheet.export-excel', request()->all()) }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-4 py-2.5 font-semibold text-sm shadow-2xs inline-flex items-center gap-2 transition duration-150"
                   title="Unduh File Rekap Excel (.xlsx)">
                    <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                    <span>Ekspor Excel</span>
                </a>

                <a href="{{ route('admin.reports.timesheet.export-pdf', request()->all()) }}" 
                   class="bg-rose-600 hover:bg-rose-700 text-white rounded-xl px-4 py-2.5 font-semibold text-sm shadow-2xs inline-flex items-center gap-2 transition duration-150"
                   title="Unduh File Rekap Dokumen PDF (.pdf)">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    <span>Ekspor PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- KPI Cards Ringkasan Eksekutif --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Karyawan & Kehadiran --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Karyawan & Kehadiran</p>
                        <h3 class="text-2xl font-bold text-[#2D2A3E] mt-1">{{ $totalMetrics->total_employees }} <span class="text-xs font-medium text-[#7C7896]">Orang</span></h3>
                        <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                            <i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i>
                            <span>{{ $totalMetrics->total_attendance_shifts }} shift dihadiri</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#F5F3FF] border border-[#E2E0F7] text-[#6C5CE7] flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="h-6 w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Jam Reguler --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Jam Reguler</p>
                        <h3 class="text-2xl font-bold text-[#4F46E5] mt-1">{{ number_format($totalMetrics->total_regular_hours, 2) }} <span class="text-xs font-medium text-[#7C7896]">Jam</span></h3>
                        <p class="text-xs text-[#7C7896] mt-1">Rata-rata {{ $totalMetrics->total_employees > 0 ? number_format($totalMetrics->total_regular_hours / $totalMetrics->total_employees, 1) : 0 }} jam / staf</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-[#4F46E5] flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="h-6 w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Jam Lembur --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Jam Lembur (Overtime)</p>
                        <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalMetrics->total_overtime_hours, 2) }} <span class="text-xs font-medium text-[#7C7896]">Jam</span></h3>
                        <p class="text-xs text-amber-600 font-medium mt-1 flex items-center gap-1">
                            <i data-lucide="trending-up" class="h-3.5 w-3.5"></i>
                            <span>Kompensasi lembur aktif</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="briefcase" class="h-6 w-6"></i>
                    </div>
                </div>
            </div>

            {{-- Total Keterlambatan --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Keterlambatan Presensi</p>
                        <h3 class="text-2xl font-bold {{ $totalMetrics->total_late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                            {{ $totalMetrics->total_late_minutes }} <span class="text-xs font-medium text-[#7C7896]">Menit</span>
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-1">
                            {{ $totalMetrics->total_late_minutes > 0 ? 'Akumulasi waktu terlambat' : 'Disiplin hadir tepat waktu' }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ $totalMetrics->total_late_minutes > 0 ? 'bg-rose-50 border-rose-100 text-rose-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600' }} border flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="h-6 w-6"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs">
            <form method="GET" action="{{ route('admin.reports.timesheet') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    {{-- Input Pilih Bulan --}}
                    <div>
                        <label for="filter-month" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Bulan Periode
                        </label>
                        <div class="relative">
                            <select id="filter-month" name="month" 
                                    class="w-full bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition">
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
                        <label for="filter-year" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Tahun
                        </label>
                        <div class="relative">
                            <select id="filter-year" name="year" 
                                    class="w-full bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition">
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
                        <label for="filter-department" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Departemen
                        </label>
                        <div class="relative">
                            <select id="filter-department" name="department_id" 
                                    class="w-full bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs md:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition">
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
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition duration-150 shadow-sm">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    
                    @if (request()->hasAny(['month', 'year', 'department_id']))
                        <a href="{{ route('admin.reports.timesheet') }}" 
                           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-medium text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] transition"
                           title="Reset Filter ke Periode Saat Ini">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Rekapitulasi Timesheet (Card Style with Brand Borders & Zebra Stripe) --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#F5F3FF]/20">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#6C5CE7]/10 border border-[#6C5CE7]/20 flex items-center justify-center text-[#6C5CE7]">
                        <i data-lucide="table" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm md:text-base font-bold text-[#2D2A3E]">
                            Rekapitulasi Jam Kerja Karyawan
                        </h2>
                        <p class="text-xs text-[#7C7896]">
                            Total {{ $reports->count() }} karyawan • Menampilkan periode {{ $periodLabel }}
                        </p>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7]">
                        <i data-lucide="building" class="h-3.5 w-3.5"></i>
                        <span>{{ $selectedDepartment?->name ?? 'Seluruh Departemen' }}</span>
                    </span>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="p-12 text-center">
                    <div class="max-w-md mx-auto flex flex-col items-center">
                        <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4 shadow-2xs">
                            <i data-lucide="file-question" class="h-8 w-8"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">Belum Ada Data Rekap Timesheet</h3>
                        <p class="text-xs md:text-sm text-[#7C7896] leading-relaxed mb-4">
                            Tidak ditemukan data karyawan atau shift assignment pada periode dan kriteria filter yang dipilih.
                        </p>
                        <a href="{{ route('admin.reports.timesheet') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-[#6C5CE7] bg-[#6C5CE7]/10 hover:bg-[#6C5CE7] hover:text-white transition">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Kembali ke Periode Saat Ini</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm text-[#2D2A3E]">
                        <thead class="bg-[#F5F3FF] text-[#2D2A3E] font-semibold border-b border-[#E2E0F7]">
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
                        <tbody class="divide-y divide-[#E2E0F7]">
                            @foreach ($reports as $item)
                                <tr class="even:bg-[#F5F3FF]/40 hover:bg-[#F5F3FF]/70 transition duration-150">
                                    {{-- No --}}
                                    <td class="px-5 py-4 text-center font-medium text-[#7C7896]">
                                        {{ $loop->iteration }}
                                    </td>

                                    {{-- Nama Karyawan --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ mb_strtoupper(mb_substr($item->employee_name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-[#2D2A3E] hover:text-[#6C5CE7] transition">
                                                    {{ $item->employee_name }}
                                                </div>
                                                <div class="text-[11px] text-[#7C7896]">
                                                    {{ $item->employee_email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Departemen --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5F3FF] text-[#2D2A3E] border border-[#E2E0F7]">
                                            <i data-lucide="layers" class="h-3 w-3 text-[#6C5CE7]"></i>
                                            <span>{{ $item->department_name }}</span>
                                        </span>
                                    </td>

                                    {{-- Shift Dijadwalkan --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap font-medium text-[#7C7896]">
                                        <span class="inline-flex items-center gap-1 font-semibold text-[#2D2A3E]">
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
                                    <td class="px-5 py-4 text-right whitespace-nowrap font-bold text-[#2D2A3E]">
                                        <span>{{ number_format($item->total_regular_hours, 2) }}</span>
                                        <span class="text-xs font-normal text-[#7C7896]">Jam</span>
                                    </td>

                                    {{-- Jam Lembur --}}
                                    <td class="px-5 py-4 text-right whitespace-nowrap font-bold">
                                        @if ($item->total_overtime_hours > 0)
                                            <span class="text-amber-600">{{ number_format($item->total_overtime_hours, 2) }}</span>
                                        @else
                                            <span class="text-[#7C7896]">{{ number_format($item->total_overtime_hours, 2) }}</span>
                                        @endif
                                        <span class="text-xs font-normal text-[#7C7896]">Jam</span>
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
                        <tfoot class="bg-[#F5F3FF]/60 font-bold border-t border-[#E2E0F7] text-[#2D2A3E]">
                            <tr>
                                <td colspan="3" class="px-5 py-4 text-right uppercase tracking-wider text-xs text-[#7C7896]">
                                    Total Akumulasi:
                                </td>
                                <td class="px-5 py-4 text-center">
                                    {{ $totalMetrics->total_scheduled_shifts }} Shift
                                </td>
                                <td class="px-5 py-4 text-center text-emerald-700">
                                    {{ $totalMetrics->total_attendance_shifts }} Shift
                                </td>
                                <td class="px-5 py-4 text-right text-[#4F46E5]">
                                    {{ number_format($totalMetrics->total_regular_hours, 2) }} Jam
                                </td>
                                <td class="px-5 py-4 text-right text-amber-600">
                                    {{ number_format($totalMetrics->total_overtime_hours, 2) }} Jam
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600">
                                    {{ $totalMetrics->total_late_minutes }} Menit
                                </td>
                                <td class="px-5 py-4 text-center text-xs text-[#7C7896]">
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
