<x-app-layout>
    @php
        // Agregasi analitik data riil dari $assignments
        $dailyData = [];
        $uniqueEmployees = $assignments->getCollection()->pluck('user_id')->unique()->count();
        $totalEmpCount = $uniqueEmployees > 0 ? $uniqueEmployees : ($department?->users_count ?? 1);

        foreach ($assignments as $a) {
            $dateKey = $a->shift?->start_time
                ? \Carbon\Carbon::parse($a->shift->start_time)->format('d M')
                : ($a->clock_in_time ? \Carbon\Carbon::parse($a->clock_in_time)->format('d M') : null);

            if ($dateKey) {
                if (!isset($dailyData[$dateKey])) {
                    $dailyData[$dateKey] = [
                        'sign_in' => 0,
                        'sign_out' => 0,
                        'overtime_hours' => 0.0,
                    ];
                }
                if ($a->clock_in_time) {
                    $dailyData[$dateKey]['sign_in']++;
                }
                if ($a->clock_out_time) {
                    $dailyData[$dateKey]['sign_out']++;
                }
                $dailyData[$dateKey]['overtime_hours'] += (float) ($a->overtime_hours ?? 0);
            }
        }

        // Ambil maksimal 7 hari untuk grafik analitik
        $chartDates = array_slice(array_keys($dailyData), 0, 7);
        $maxOvertime = 0.0;
        foreach ($dailyData as $d) {
            if ($d['overtime_hours'] > $maxOvertime) {
                $maxOvertime = $d['overtime_hours'];
            }
        }
        $maxOvertime = max($maxOvertime, 1.0);
    @endphp

    <div class="space-y-6">
        {{-- Header Group: Breadcrumb & Page Title --}}
        <div>
            {{-- Breadcrumb Standar Permanen Manager --}}
            <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-2">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-[#6C5CE7] font-medium">Timesheet &amp; Laporan</span>
            </div>

            {{-- Header Halaman & Export Buttons --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                        Timesheet &amp; Laporan
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                        Lihat rekap jam kerja, lembur, dan laporan kehadiran tim dalam periode tertentu.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route('manager.timesheets.export.excel', ['from' => $from, 'to' => $to]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Export Excel</span>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route('manager.timesheets.export.pdf', ['from' => $from, 'to' => $to]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- FILTER AREA SESUAI DESAIN ACUAN                                   --}}
        {{-- ================================================================= --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 shadow-2xs">
            <form method="GET" action="{{ route('manager.timesheets.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                    {{-- Periode Tanggal --}}
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#2D2A3E] shrink-0">Periode</span>
                        <div class="relative flex items-center">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#6C5CE7] absolute left-3 pointer-events-none"></i>
                            <input
                                type="date"
                                name="from"
                                value="{{ $from }}"
                                class="pl-8 pr-2.5 py-2 text-xs rounded-xl border border-[#E2E0F7] bg-[#F8F7FF] text-[#2D2A3E] font-medium focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                            >
                            <span class="mx-1.5 text-[#7C7896]">–</span>
                            <input
                                type="date"
                                name="to"
                                value="{{ $to }}"
                                class="px-3 py-2 text-xs rounded-xl border border-[#E2E0F7] bg-[#F8F7FF] text-[#2D2A3E] font-medium focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                            >
                        </div>
                    </div>

                    {{-- Departemen Info Badge --}}
                    <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#F8F7FF] border border-[#E2E0F7] text-[#2D2A3E] font-semibold">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                        <span>{{ $department?->name ?? 'Semua Tim' }}</span>
                    </div>
                </div>

                {{-- Action Buttons Filter --}}
                <div class="flex items-center gap-2 justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-5 py-2 rounded-xl bg-[#6C5CE7] hover:bg-[#5A4BD1] text-white font-semibold shadow-2xs transition"
                    >
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>

                    <a
                        href="{{ route('manager.timesheets.index') }}"
                        class="inline-flex items-center justify-center p-2 rounded-xl bg-[#F8F7FF] hover:bg-[#F5F3FF] border border-[#E2E0F7] text-[#7C7896] hover:text-[#6C5CE7] transition"
                        title="Reset Filter"
                    >
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- ================================================================= --}}
        {{-- 4 KPI CARDS SESUAI SCREENSHOT REFERENSI                           --}}
        {{-- ================================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Kartu 1: Total Jam Reguler --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896]">Total Jam Reguler</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ number_format($totalRegularHours, 2) }} <span class="text-xs font-sans font-normal text-[#7C7896]">Jam</span>
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Akumulasi jam kerja reguler tim</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Kartu 2: Total Jam Lembur --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700">Total Jam Lembur</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-amber-600 mt-1 font-mono">
                        {{ number_format($totalOvertimeHours, 2) }} <span class="text-xs font-sans font-normal text-[#7C7896]">Jam</span>
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Total jam lembur (PP 35/2021)</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Kartu 3: Rasio Kehadiran --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700">Rasio Kehadiran</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-emerald-600 mt-1 font-mono">
                        {{ number_format($attendanceRate, 1) }}%
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">{{ $attendedShifts }} dari {{ $totalShifts }} total shift penugasan</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Kartu 4: Total Karyawan --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#4F46E5]">Total Karyawan</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ $totalEmpCount }} <span class="text-xs font-sans font-normal text-[#7C7896]">Orang</span>
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Dalam tim ini</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- RINCIAN PRESENSI STAF DEPARTEMEN (TABEL UTAMA)                    --}}
        {{-- ================================================================= --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#2D2A3E]">Rincian Presensi Staf Departemen</h2>
                        <p class="text-xs text-[#7C7896]">Data shift dan jam kerja seluruh staf yang berada di bawah divisi Anda.</p>
                    </div>
                </div>

                <a href="{{ route('manager.timesheets.index') }}" class="p-1.5 rounded-lg border border-[#E2E0F7] text-[#7C7896] hover:text-[#6C5CE7] bg-[#F8F7FF] transition" title="Segarkan Data">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8F7FF] text-[#7C7896] font-bold border-b border-[#E2E0F7] uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center">No</th>
                            <th class="px-5 py-3.5">Nama Karyawan</th>
                            <th class="px-5 py-3.5">Tanggal Shift</th>
                            <th class="px-5 py-3.5">Shift Kerja</th>
                            <th class="px-5 py-3.5">Jam Masuk</th>
                            <th class="px-5 py-3.5">Jam Pulang</th>
                            <th class="px-5 py-3.5 text-right">Jam Reguler</th>
                            <th class="px-5 py-3.5 text-right">Jam Lembur</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E0F7]">
                        @forelse ($assignments as $index => $row)
                            @php
                                $userName = $row->user?->name ?? 'Karyawan';
                                $initials = collect(explode(' ', $userName))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->implode('');
                                $shiftDate = $row->shift?->start_time
                                    ? \Carbon\Carbon::parse($row->shift->start_time)->translatedFormat('d M Y')
                                    : ($row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->translatedFormat('d M Y') : '-');
                                $cIn = $row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->format('H:i') . ' WIB' : '-';
                                $cOut = $row->clock_out_time ? \Carbon\Carbon::parse($row->clock_out_time)->format('H:i') . ' WIB' : '-';
                            @endphp
                            <tr class="hover:bg-[#F8F7FF]/50 transition">
                                {{-- No --}}
                                <td class="px-4 py-3.5 text-center text-[#7C7896] font-medium">
                                    {{ $assignments->firstItem() + $index }}
                                </td>

                                {{-- Nama Karyawan --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-[#2D2A3E]">{{ $userName }}</p>
                                                @if ($row->user)
                                                    @php
                                                        $tsType = $row->user->employment_type ?? 'full_time';
                                                        $tsBadge = match ($tsType) {
                                                            'full_time' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                            'part_time' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                            default => 'bg-purple-50 text-purple-700 border-purple-200',
                                                        };
                                                        $tsLabel = match ($tsType) {
                                                            'full_time' => 'FT',
                                                            'part_time' => 'PT',
                                                            default => 'FL',
                                                        };
                                                    @endphp
                                                    <span class="text-[9px] font-bold uppercase border px-1.5 py-0.2 rounded-md {{ $tsBadge }}">
                                                        {{ $tsLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-[#7C7896]">{{ $row->user?->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tanggal Shift --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-[#2D2A3E] font-medium">
                                    {{ $shiftDate }}
                                </td>

                                {{-- Shift Kerja --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-[#2D2A3E]">
                                    <span class="font-medium">{{ $row->shift?->title ?? 'Shift Regular' }}</span>
                                </td>

                                {{-- Jam Masuk --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-[#7C7896]">
                                    {{ $cIn }}
                                </td>

                                {{-- Jam Pulang --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-[#7C7896]">
                                    {{ $cOut }}
                                </td>

                                {{-- Jam Reguler --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-[#2D2A3E] text-right">
                                    {{ number_format($row->regular_hours, 2) }}
                                </td>

                                {{-- Jam Lembur --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold {{ $row->overtime_hours > 0 ? 'text-amber-600' : 'text-[#7C7896]' }} text-right">
                                    {{ number_format($row->overtime_hours, 2) }}
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                    @if ($row->status === 'present' || $row->status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                        </span>
                                    @elseif ($row->status === 'late')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Terlambat
                                        </span>
                                    @elseif ($row->status === 'absent')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Tidak Hadir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Terjadwal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-[#7C7896]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mb-1">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-bold text-[#2D2A3E]">Tidak Ada Riwayat Timesheet Pada Divisi Ini</p>
                                        <p class="text-xs text-[#7C7896] max-w-sm">Belum ada aktivitas shift staf yang tercatat pada rentang tanggal filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="p-4 sm:p-5 border-t border-[#E2E0F7] bg-[#F8F7FF]">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
