<x-app-layout>
    <div x-data="attendanceMonitor" class="space-y-6">

        {{-- Header Group: Breadcrumb & Page Title --}}
        <div>
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-slate-900 font-medium">Presensi Tim</span>
            </div>

            {{-- Header Halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Monitoring Kehadiran Tim
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Pantau kehadiran staf secara real-time untuk jadwal shift kerja hari ini.
                    </p>
                </div>

                {{-- Live Clock & Polling Status Indicator --}}
                <div class="flex items-center gap-2">
                    {{-- Live Clock --}}
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 shadow-card">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span class="font-mono font-bold text-xs" x-text="currentTimeStr">{{ $serverTime }}</span>
                        <span class="text-[10px] font-semibold text-slate-400">{{ $timezoneAbbr ?? 'WIB' }}</span>
                    </div>

                    {{-- Status Polling Indicator --}}
                    <div class="flex items-center bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold shadow-card">
                        <template x-if="isConnected">
                            <span class="flex items-center text-emerald-700 text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1.5"></span>
                                Live
                            </span>
                        </template>

                        <template x-if="!isConnected">
                            <span class="flex items-center text-rose-600 text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block mr-1.5"></span>
                                Terputus
                            </span>
                        </template>
                    </div>

                    {{-- Manual Refresh Button --}}
                    <button
                        type="button"
                        @click="fetchFeed()"
                        :disabled="isFetching"
                        class="p-1.5 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-800 rounded-lg border border-slate-200 shadow-card transition focus:outline-none"
                        title="Segarkan Data Sekarang"
                    >
                        <i data-lucide="refresh-cw" class="w-4 h-4" :class="isFetching ? 'animate-spin text-indigo-600' : ''"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 4 KARTU METRIK RINGKASAN KEHADIRAN (HIGH DENSITY)         --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">

            {{-- Kartu 1: Dijadwalkan --}}
            <button
                type="button"
                @click="selectMetric('scheduled')"
                :aria-pressed="selectedMetric === 'scheduled'"
                :class="selectedMetric === 'scheduled'
                    ? 'ring-2 ring-indigo-600 border-indigo-600 bg-indigo-50/40 shadow-xs'
                    : 'border-slate-200/90 bg-white shadow-card hover:border-slate-300'"
                class="w-full text-left rounded-xl border p-3 sm:p-4 shadow-card flex items-center justify-between transition cursor-pointer focus:outline-none"
            >
                <div class="min-w-0 pr-1">
                    <p class="text-[11px] sm:text-xs font-medium text-slate-500 truncate">Dijadwalkan</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5 font-mono" x-text="metrics.scheduled">
                        {{ $metrics['scheduled'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5 truncate hidden xs:block">
                        Total staf shift hari ini
                    </p>
                </div>

                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </button>

            {{-- Kartu 2: Hadir Tepat Waktu --}}
            <button
                type="button"
                @click="selectMetric('on_time')"
                :aria-pressed="selectedMetric === 'on_time'"
                :class="selectedMetric === 'on_time'
                    ? 'ring-2 ring-emerald-600 border-emerald-600 bg-emerald-50/40'
                    : 'border-slate-200/80 bg-white hover:border-slate-300'"
                class="w-full text-left rounded-xl border p-3 sm:p-4 shadow-card flex items-center justify-between transition cursor-pointer focus:outline-none"
            >
                <div class="min-w-0 pr-1">
                    <p class="text-[11px] sm:text-xs font-medium text-emerald-700 truncate">Tepat Waktu</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5 font-mono" x-text="metrics.on_time">
                        {{ $metrics['on_time'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5 truncate hidden xs:block">
                        Sesuai batas toleransi
                    </p>
                </div>

                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </button>

            {{-- Kartu 3: Terlambat --}}
            <button
                type="button"
                @click="selectMetric('late')"
                :aria-pressed="selectedMetric === 'late'"
                :class="selectedMetric === 'late'
                    ? 'ring-2 ring-amber-600 border-amber-600 bg-amber-50/40'
                    : 'border-slate-200/80 bg-white hover:border-slate-300'"
                class="w-full text-left rounded-xl border p-3 sm:p-4 shadow-card flex items-center justify-between transition cursor-pointer focus:outline-none"
            >
                <div class="min-w-0 pr-1">
                    <p class="text-[11px] sm:text-xs font-medium text-amber-700 truncate">Terlambat</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5 font-mono" x-text="metrics.late">
                        {{ $metrics['late'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5 truncate hidden xs:block">
                        Melewati toleransi
                    </p>
                </div>

                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </button>

            {{-- Kartu 4: Belum Hadir --}}
            <button
                type="button"
                @click="selectMetric('not_present')"
                :aria-pressed="selectedMetric === 'not_present'"
                :class="selectedMetric === 'not_present'
                    ? 'ring-2 ring-rose-600 border-rose-600 bg-rose-50/40'
                    : 'border-slate-200/80 bg-white hover:border-slate-300'"
                class="w-full text-left rounded-xl border p-3 sm:p-4 shadow-card flex items-center justify-between transition cursor-pointer focus:outline-none"
            >
                <div class="min-w-0 pr-1">
                    <p class="text-[11px] sm:text-xs font-medium text-rose-700 truncate">Belum Hadir</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5 font-mono" x-text="metrics.not_present">
                        {{ $metrics['not_present'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5 truncate hidden xs:block">
                        <span x-text="metrics.absent">{{ $metrics['absent'] }}</span> lewat batas
                    </p>
                </div>

                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center shrink-0">
                    <i data-lucide="slash" class="w-4 h-4"></i>
                </div>
            </button>

        </div>

        {{-- Toolbar Departemen & Tanggal --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-card">
            <div class="flex flex-wrap items-center gap-2">
                {{-- Departemen Badge --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ $manager->department->name ?? 'Semua Departemen' }}</span>
                </div>

                {{-- Tanggal Badge --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span x-text="currentDateStr">{{ $serverDate }}</span>
                </div>

                {{-- Toleransi Menit --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500">
                    <span>Toleransi:</span>
                    <strong class="text-slate-900">{{ $gracePeriod }} Menit</strong>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Sinkronisasi terakhir:</span>
                <span class="font-mono font-bold text-slate-800" x-text="lastSyncTime">{{ $serverTime }}</span>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TABEL MONITORING KEHADIRAN STAF                           --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">

            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Daftar Presensi Staf Hari Ini
                    </h2>
                    <p class="text-xs text-slate-500">
                        Data otomatis disinkronisasi setiap 20 detik tanpa memuat ulang halaman.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Filter Aktif --}}
                    <template x-if="selectedMetric !== null">
                        <button
                            type="button"
                            @click="clearMetricFilter()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                        >
                            <i data-lucide="x" class="w-3.5 h-3.5 text-slate-400"></i>
                            Tampilkan Semua
                        </button>
                    </template>

                    {{-- Monitoring Aktif --}}
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Monitoring Aktif</span>
                    </div>
                </div>
            </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Daftar Presensi Staf Hari Ini
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Data otomatis disinkronisasi setiap 20 detik tanpa memuat ulang halaman.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Filter Aktif --}}
                    <template x-if="selectedMetric !== null">
                        <button
                            type="button"
                            @click="clearMetricFilter()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition"
                        >
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            Tampilkan Semua
                        </button>
                    </template>

                    {{-- Monitoring Aktif --}}
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Monitoring Aktif</span>
                    </div>
                </div>
            </div>

            {{-- Empty State --}}
            <div
                x-show="filteredRows.length === 0"
                class="px-6 py-16 text-center"
                @if(!empty($rows)) style="display: none;" @endif
            >
                <div class="w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="calendar-x" class="w-7 h-7"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900"
                    x-text="selectedMetric ? 'Tidak Ada Data pada Kategori Ini' : 'Tidak Ada Jadwal Shift Hari Ini'">
                    Tidak Ada Jadwal Shift Hari Ini
                </h3>

                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
                    <span x-show="selectedMetric !== null">
                        Tidak ditemukan staf yang sesuai dengan kategori kehadiran yang dipilih.
                    </span>
                    <span x-show="selectedMetric === null">
                        Tidak ditemukan penugasan staf yang dijadwalkan pada hari ini di departemen Anda.
                        Pastikan jadwal shift kerja telah dibuat dan diterbitkan.
                    </span>
                </p>

                <div class="mt-5" x-show="selectedMetric === null">
                    <a
                        href="{{ route('manager.shifts.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs shadow-indigo-600/20 active:translate-y-px transition"
                    >
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>Kelola Jadwal Kerja Tim</span>
                    </a>
                </div>

                <div class="mt-5" x-show="selectedMetric !== null">
                    <button
                        type="button"
                        @click="clearMetricFilter()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs shadow-indigo-600/20 active:translate-y-px transition"
                    >
                        Tampilkan Semua Data
                    </button>
                </div>
            </div>

            {{-- Table View --}}
            <div
                x-show="filteredRows.length > 0"
                class="overflow-x-auto"
                @if(empty($rows)) style="display: none;" @endif
            >
                <table class="w-full text-left text-sm min-w-[600px]">

                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 tracking-wider border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Nama Staf</th>
                            <th scope="col" class="px-6 py-3.5">Shift</th>
                            <th scope="col" class="px-6 py-3.5">Clock-In</th>
                            <th scope="col" class="px-6 py-3.5">Clock-Out</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-xs">

                        {{-- Server-Side Initial Render --}}
                        @foreach ($rows as $row)
                            <tr x-show="!isClientMounted" class="hover:bg-slate-50/50 transition">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($row['profile_photo_url']))
                                            <img
                                                src="{{ $row['profile_photo_url'] }}"
                                                alt="{{ $row['name'] }}"
                                                class="w-9 h-9 rounded-full object-cover shrink-0 shadow-card"
                                            >
                                        @else
                                            <div class="w-9 h-9 bg-slate-900 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-card">
                                                {{ $row['initials'] }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-bold text-slate-900">{{ $row['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $row['position'] }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900">{{ $row['shift'] }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="clock" class="w-3 h-3 text-indigo-600"></i>
                                        <span>{{ $row['shift_start'] }} - {{ $row['shift_end'] }}</span>

                                        @if ($row['is_overnight'])
                                            <span class="text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                                                Overnight
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($row['clock_in'])
                                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                            {{ $row['clock_in'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-500/60 font-mono text-base">-</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($row['clock_out'])
                                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                            {{ $row['clock_out'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-500/60 font-mono text-base">-</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($row['status'] === 'on_time')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Tepat Waktu
                                        </span>
                                    @elseif ($row['status'] === 'late')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Terlambat @if($row['late_minutes'] > 0) (+{{ $row['late_minutes'] }}m) @endif
                                        </span>
                                    @elseif ($row['status'] === 'on_leave')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Izin / Cuti
                                        </span>
                                    @elseif ($row['status'] === 'not_started')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Belum Mulai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Belum Hadir
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Client-Side Reactive Render --}}
                        <template x-for="row in filteredRows" :key="row.assignment_id">
                            <tr x-show="isClientMounted" class="hover:bg-slate-50/50 transition" style="display: none;">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">

                                        <template x-if="row.profile_photo_url">
                                            <img
                                                :src="row.profile_photo_url"
                                                :alt="row.name"
                                                class="w-9 h-9 rounded-full object-cover shrink-0 shadow-card"
                                            >
                                        </template>

                                        <template x-if="!row.profile_photo_url">
                                            <div class="w-9 h-9 bg-slate-900 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-card" x-text="row.initials"></div>
                                        </template>

                                        <div>
                                            <p class="font-bold text-slate-900" x-text="row.name"></p>
                                            <p class="text-[11px] text-slate-500" x-text="row.position"></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900" x-text="row.shift"></div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="clock" class="w-3 h-3 text-indigo-600"></i>
                                        <span>
                                            <span x-text="row.shift_start"></span> -
                                            <span x-text="row.shift_end"></span>
                                        </span>

                                        <template x-if="row.is_overnight">
                                            <span class="text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                                                Overnight
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="row.clock_in">
                                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200" x-text="row.clock_in"></span>
                                    </template>

                                    <template x-if="!row.clock_in">
                                        <span class="text-slate-500/60 font-mono text-base">-</span>
                                    </template>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="row.clock_out">
                                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200" x-text="row.clock_out"></span>
                                    </template>

                                    <template x-if="!row.clock_out">
                                        <span class="text-slate-500/60 font-mono text-base">-</span>
                                    </template>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <template x-if="row.status === 'on_time'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Tepat Waktu</span>
                                        </span>
                                    </template>

                                    <template x-if="row.status === 'late'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>
                                                Terlambat
                                                <template x-if="row.late_minutes > 0">
                                                    <span x-text="'(+' + row.late_minutes + 'm)'"></span>
                                                </template>
                                            </span>
                                        </span>
                                    </template>

                                    <template x-if="row.status === 'on_leave'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            <span>Izin / Cuti</span>
                                        </span>
                                    </template>

                                    <template x-if="row.status === 'not_started'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Belum Mulai</span>
                                        </span>
                                    </template>

                                    <template x-if="row.status === 'absent'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Belum Hadir</span>
                                        </span>
                                    </template>

                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- ALPINE.JS: REALTIME ATTENDANCE MONITOR --}}
    {{-- ========================================================= --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('attendanceMonitor', () => ({
                pollIntervalMs: 20000,
                feedUrl: '{{ route('manager.attendance.feed') }}',

                isConnected: true,
                isFetching: false,
                isClientMounted: false,
                pollTimer: null,
                clockTimer: null,
                isTabActive: true,

                currentTimeStr: '{{ $serverTime }}',
                currentDateStr: '{{ $serverDate }}',
                lastSyncTime: '{{ $serverTime }}',

                metrics: {!! json_encode($metrics) !!},
                rows: {!! json_encode($rows) !!},

                // Filter KPI yang sedang dipilih
                selectedMetric: null,

                // Data tabel yang sudah difilter
                get filteredRows() {
                    if (this.selectedMetric === null || this.selectedMetric === 'scheduled') {
                        return this.rows;
                    }

                    if (this.selectedMetric === 'on_time') {
                        return this.rows.filter(row => row.status === 'on_time');
                    }

                    if (this.selectedMetric === 'late') {
                        return this.rows.filter(row => row.status === 'late');
                    }

                    if (this.selectedMetric === 'not_present') {
                        return this.rows.filter(row =>
                            row.status === 'absent' || row.status === 'not_started'
                        );
                    }

                    return this.rows;
                },

                // Memilih kategori KPI
                selectMetric(metric) {
                    this.selectedMetric = metric;

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                // Menghapus filter KPI
                clearMetricFilter() {
                    this.selectedMetric = null;
                },

                init() {
                    this.isClientMounted = true;
                    this.startLiveClock();
                    this.setupVisibilityListener();
                    this.startPolling();

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                startLiveClock() {
                    this.clockTimer = setInterval(() => {
                        const now = new Date();
                        const hh = String(now.getHours()).padStart(2, '0');
                        const mm = String(now.getMinutes()).padStart(2, '0');
                        const ss = String(now.getSeconds()).padStart(2, '0');

                        this.currentTimeStr = hh + ':' + mm + ':' + ss;
                    }, 1000);
                },

                setupVisibilityListener() {
                    document.addEventListener('visibilitychange', () => {
                        this.isTabActive = !document.hidden;

                        if (this.isTabActive && !this.isFetching) {
                            this.fetchFeed();
                        }
                    });
                },

                startPolling() {
                    this.pollTimer = setInterval(() => {
                        if (this.isTabActive && !this.isFetching) {
                            this.fetchFeed();
                        }
                    }, this.pollIntervalMs);
                },

                destroy() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    if (this.clockTimer) clearInterval(this.clockTimer);
                },

                async fetchFeed() {
                    if (this.isFetching) return;

                    this.isFetching = true;

                    try {
                        const response = await fetch(this.feedUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('HTTP status: ' + response.status);
                        }

                        const data = await response.json();

                        if (data.metrics) {
                            this.metrics = data.metrics;
                        }

                        if (data.rows) {
                            this.rows = data.rows;
                        }

                        if (data.server_time) {
                            this.lastSyncTime = data.server_time;
                        }

                        this.isConnected = true;

                        this.$nextTick(() => {
                            if (window.lucide) window.lucide.createIcons();
                        });

                    } catch (err) {
                        console.warn('Realtime attendance polling error:', err);
                        this.isConnected = false;

                    } finally {
                        this.isFetching = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>