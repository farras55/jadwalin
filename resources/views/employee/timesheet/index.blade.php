<x-app-layout>
    <div class="min-h-screen py-6 space-y-6">
        {{-- Header & Breadcrumbs --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-gray-500 font-medium mb-1">
                    <a href="{{ route('employee.dashboard') }}" class="hover:text-[#6C5CE7] transition-colors">Karyawan</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span class="text-[#6C5CE7] font-semibold">Timesheet Pribadi</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Timesheet &amp; Rekap Jam Kerja
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Rekapitulasi akumulasi jam reguler, jam lembur (PP 35/2021), dan log riwayat presensi personal Anda.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('employee.timesheet.export.excel', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition-all">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Export Excel</span>
                </a>
                <a href="{{ route('employee.timesheet.export.pdf', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition-all">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Export PDF</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>{{ $user->name }}</span>
                </span>
            </div>
        </div>

        {{-- Filter Bulan Periode (Tailwind CSS & Alpine.js) --}}
        <div x-data="{ 
            selectedMonth: '{{ $month }}', 
            selectedYear: '{{ $year }}',
            submitFilter() {
                $refs.filterForm.submit();
            }
        }" class="bg-white rounded-2xl border border-brand-border p-4 shadow-2xs">
            <form x-ref="filterForm" method="GET" action="{{ route('employee.timesheet') }}" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 text-xs">
                {{-- Dropdown Pilih Bulan --}}
                <div class="flex-1">
                    <label for="filter-month" class="block font-semibold text-gray-700 mb-1">Bulan Periode</label>
                    <select id="filter-month" 
                            name="month" 
                            x-model="selectedMonth"
                            @change="submitFilter()"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 shadow-2xs py-2.5 font-medium text-gray-800">
                        @foreach ($monthNames as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ (int) $month === (int) $mNum ? 'selected' : '' }}>
                                {{ $mLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Pilih Tahun --}}
                <div class="w-full sm:w-36">
                    <label for="filter-year" class="block font-semibold text-gray-700 mb-1">Tahun</label>
                    <select id="filter-year" 
                            name="year" 
                            x-model="selectedYear"
                            @change="submitFilter()"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 shadow-2xs py-2.5 font-medium text-gray-800">
                        @for ($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold shadow-2xs transition-all w-full sm:w-auto">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('employee.timesheet') }}" title="Reset ke Bulan Berjalan" class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 font-semibold transition-all">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- 3 Card Ringkasan (PP 35/2021) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Card 1: Total Jam Reguler --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Jam Reguler</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1 font-mono">
                            {{ number_format($totalRegularHours, 2) }} <span class="text-sm font-sans font-medium text-gray-500">Jam</span>
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-indigo-50 text-[#6C5CE7]">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Maksimal 8 jam per hari atau 40 jam per minggu</p>
            </div>

            {{-- Card 2: Total Jam Lembur --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Jam Lembur</p>
                        <h3 class="text-2xl font-bold text-amber-600 mt-1 font-mono">
                            {{ number_format($totalOvertimeHours, 2) }} <span class="text-sm font-sans font-medium text-gray-500">Jam</span>
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Perhitungan lembur disetujui sesuai regulasi PP 35/2021</p>
            </div>

            {{-- Card 3: Persentase Kehadiran --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Persentase Kehadiran</p>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1 font-mono">
                            {{ number_format($attendanceRate, 1) }}%
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-check" class="w-6 h-6"></i>
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">{{ $attendedShifts }} dari {{ $totalShifts }} shift terjadwal dihadiri</p>
            </div>
        </div>

        {{-- Tabel Riwayat Kehadiran Personal --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl/5 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7]">
                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Riwayat Penugasan &amp; Jam Kerja</h2>
                        <p class="text-xs text-gray-400">Daftar shift dan presensi yang tercatat pada rentang filter aktif</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                    <thead class="bg-slate-50/80 text-gray-700 font-bold border-b border-gray-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal Shift</th>
                            <th class="px-5 py-3.5">Shift Kerja</th>
                            <th class="px-5 py-3.5">Jam Masuk</th>
                            <th class="px-5 py-3.5">Jam Pulang</th>
                            <th class="px-5 py-3.5 text-right">Jam Reguler</th>
                            <th class="px-5 py-3.5 text-right">Jam Lembur</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($assignments as $row)
                            @php
                                $shiftDate = $row->shift?->start_time
                                    ? \Carbon\Carbon::parse($row->shift->start_time)->translatedFormat('d M Y')
                                    : ($row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->translatedFormat('d M Y') : '-');
                                $shiftDay = $row->shift?->start_time
                                    ? \Carbon\Carbon::parse($row->shift->start_time)->translatedFormat('l')
                                    : ($row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->translatedFormat('l') : '-');
                                $cIn = $row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->format('H:i') . ' WIB' : '-';
                                $cOut = $row->clock_out_time ? \Carbon\Carbon::parse($row->clock_out_time)->format('H:i') . ' WIB' : '-';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $shiftDate }}</div>
                                    <div class="text-[11px] text-gray-400 font-medium">{{ $shiftDay }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-gray-800 font-medium">
                                    {{ $row->shift?->title ?? 'Shift Regular' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-gray-700">
                                    {{ $cIn }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-gray-700">
                                    {{ $cOut }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono font-semibold text-gray-900 text-right">
                                    {{ number_format($row->regular_hours, 2) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono font-semibold {{ $row->overtime_hours > 0 ? 'text-amber-600' : 'text-gray-400' }} text-right">
                                    {{ number_format($row->overtime_hours, 2) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($row->status === 'present' || $row->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                        </span>
                                    @elseif ($row->status === 'late')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Terlambat
                                        </span>
                                    @elseif ($row->status === 'absent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Tidak Hadir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Terjadwal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="calendar-x" class="w-10 h-10 text-gray-300"></i>
                                        <p class="text-sm font-semibold text-gray-600">Tidak ada data timesheet untuk periode ini</p>
                                        <p class="text-xs text-gray-400">Silakan ubah filter tanggal untuk melihat catatan kehadiran Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-slate-50/50">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
