<x-app-layout>
    @php
        // Agregasi analitik data riil dari $assignments
        $dailyData = [];
        $uniqueEmployees = $assignments->getCollection()->pluck('user_id')->unique()->count();
        $totalEmpCount = $uniqueEmployees > 0 ? $uniqueEmployees : ($department?->users_count ?? \App\Models\User::where('company_id', $manager->company_id)->where('role', 'employee')->count());

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
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-400"></i>
                <span class="text-indigo-600 font-medium">Timesheet &amp; Laporan</span>
            </div>

            {{-- Header Halaman & Export Buttons --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                        Timesheet &amp; Laporan
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">
                        Lihat rekap jam kerja, lembur, dan laporan kehadiran tim dalam periode tertentu.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-2.5 w-full sm:w-auto">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route('manager.timesheets.export.excel', array_filter(['from' => $from, 'to' => $to, 'department_id' => request('department_id')])) }}"
                       class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:translate-y-px">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Export Excel</span>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route('manager.timesheets.export.pdf', array_filter(['from' => $from, 'to' => $to, 'department_id' => request('department_id')])) }}"
                       class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition active:translate-y-px">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- FILTER AREA SESUAI DESAIN ACUAN                                   --}}
        {{-- ================================================================= --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-card">
            <form method="GET" action="{{ route('manager.timesheets.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                    {{-- Periode Tanggal --}}
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                        <span class="font-bold text-slate-800 shrink-0 text-xs">Periode:</span>
                        <div class="relative flex items-center max-w-full">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-600 absolute left-2.5 pointer-events-none hidden xs:block"></i>
                            <input
                                type="date"
                                name="from"
                                value="{{ $from }}"
                                class="xs:pl-8 px-2 py-1.5 sm:py-2 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition max-w-[125px] sm:max-w-none"
                            >
                            <span class="mx-1 text-slate-400 text-xs">–</span>
                            <input
                                type="date"
                                name="to"
                                value="{{ $to }}"
                                class="px-2 py-1.5 sm:py-2 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition max-w-[125px] sm:max-w-none"
                            >
                        </div>
                    </div>

                    {{-- Departemen Filter/Info --}}
                    @if ($manager->is_global_manager)
                        <div class="relative flex items-center">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600 absolute left-3 pointer-events-none"></i>
                            <select
                                name="department_id"
                                class="pl-8 pr-8 py-1.5 sm:py-2 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                            >
                                <option value="">Semua Departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string) $selectedDepartmentId === (string) $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                            <span>{{ $department?->name ?? 'Semua Tim' }}</span>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons Filter --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button
                        type="submit"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition"
                    >
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>

                    <a
                        href="{{ route('manager.timesheets.index') }}"
                        class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-500 hover:text-indigo-600 transition shrink-0 text-xs font-semibold"
                        title="Reset Filter"
                    >
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- ================================================================= --}}
        {{-- 4 KPI CARDS SESUAI SCREENSHOT REFERENSI                           --}}
        {{-- ================================================================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            {{-- Kartu 1: Total Jam Reguler --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 truncate">Total Reguler</p>
                    <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="mt-2.5 sm:mt-3">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight">
                        {{ number_format($totalRegularHours, 2) }} <span class="text-xs font-sans font-normal text-slate-500">Jam</span>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1 truncate">Akumulasi jam reguler</p>
                </div>
            </div>

            {{-- Kartu 2: Total Jam Lembur --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 truncate">Total Lembur</p>
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                </div>
                <div class="mt-2.5 sm:mt-3">
                    <p class="text-2xl sm:text-3xl font-extrabold text-amber-600 font-mono tracking-tight">
                        {{ number_format($totalOvertimeHours, 2) }} <span class="text-xs font-sans font-normal text-slate-500">Jam</span>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1 truncate">Total jam lembur</p>
                </div>
            </div>

            {{-- Kartu 3: Rasio Kehadiran --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 truncate">Tingkat Kehadiran</p>
                    <i data-lucide="calendar-check" class="w-4 h-4 text-emerald-500"></i>
                </div>
                <div class="mt-2.5 sm:mt-3">
                    <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 font-mono tracking-tight">
                        {{ number_format($attendanceRate, 1) }}%
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1 truncate">{{ $attendedShifts }}/{{ $totalShifts }} shift terpenuhi</p>
                </div>
            </div>

            {{-- Kartu 4: Total Karyawan --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 truncate">Total Karyawan</p>
                    <i data-lucide="users" class="w-4 h-4 text-indigo-500"></i>
                </div>
                <div class="mt-2.5 sm:mt-3">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight">
                        {{ $totalEmpCount }} <span class="text-xs font-sans font-normal text-slate-500">Staf</span>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1 truncate">Staf tim ini</p>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- RINCIAN PRESENSI STAF DEPARTEMEN (TABEL UTAMA)                    --}}
        {{-- ================================================================= --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-slate-200/80 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 truncate">
                            {{ $department ? 'Rincian Presensi Divisi ' . $department->name : ($manager->is_global_manager ? 'Rincian Presensi Seluruh Departemen' : 'Rincian Presensi Staf Departemen') }}
                        </h2>
                        <p class="text-xs text-slate-500 truncate hidden sm:block">
                            {{ $department ? 'Data shift dan jam kerja seluruh staf yang berada di bawah divisi ' . $department->name . '.' : 'Data shift dan jam kerja seluruh staf di perusahaan Anda.' }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('manager.timesheets.index') }}" 
                   class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 text-xs font-semibold shrink-0 transition" 
                   title="Segarkan Data">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Segarkan</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[720px]">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
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
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assignments as $index => $row)
                            @php
                                $userName = $row->user?->name ?? 'Karyawan';
                                $initials = collect(explode(' ', $userName))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->implode('');
                                $shiftDate = $row->shift?->start_time
                                    ? \Carbon\Carbon::parse($row->shift->start_time)->translatedFormat('d M Y')
                                    : ($row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->translatedFormat('d M Y') : '-');
                                $cIn = $row->clock_in_time ? \Carbon\Carbon::parse($row->clock_in_time)->format('H:i') . ' ' . ($timezoneAbbr ?? 'WIB') : '-';
                                $cOut = $row->clock_out_time ? \Carbon\Carbon::parse($row->clock_out_time)->format('H:i') . ' ' . ($timezoneAbbr ?? 'WIB') : '-';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                {{-- No --}}
                                <td class="px-4 py-3.5 text-center text-slate-400 font-medium">
                                    {{ $assignments->firstItem() + $index }}
                                </td>

                                {{-- Nama Karyawan --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-slate-900">{{ $userName }}</p>
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
                                            <p class="text-[11px] text-slate-400">{{ $row->user?->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tanggal Shift --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-900 font-medium">
                                    {{ $shiftDate }}
                                </td>

                                {{-- Shift Kerja --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-800">
                                    <span class="font-medium">{{ $row->shift?->title ?? 'Shift Regular' }}</span>
                                </td>

                                {{-- Jam Masuk --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-slate-600">
                                    {{ $cIn }}
                                </td>

                                {{-- Jam Pulang --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-slate-600">
                                    {{ $cOut }}
                                </td>

                                {{-- Jam Reguler --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-slate-900 text-right">
                                    {{ number_format($row->regular_hours, 2) }}
                                </td>

                                {{-- Jam Lembur --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold {{ $row->overtime_hours > 0 ? 'text-amber-600' : 'text-slate-400' }} text-right">
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
                                <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-1">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $department ? 'Tidak Ada Riwayat Timesheet Pada Divisi Ini' : 'Belum Ada Riwayat Timesheet' }}
                                        </p>
                                        <p class="text-xs text-slate-500 max-w-sm">Belum ada aktivitas shift staf yang tercatat pada rentang tanggal filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="p-4 sm:p-5 border-t border-slate-200/80 bg-slate-50/60">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
