<x-app-layout>
    {{-- Main Container with Alpine.js Reactive State --}}
    <div 
        x-data="{
            // Konstanta konfigurasi interval polling (20 detik)
            pollIntervalMs: 20000,
            feedUrl: '{{ route('manager.attendance.feed') }}',
            
            // Status konektivitas dan kontrol request
            isConnected: true,
            isFetching: false,
            isClientMounted: false,
            pollTimer: null,
            clockTimer: null,
            isTabActive: true,
            
            // Waktu server & live clock
            currentTimeStr: '{{ $serverTime }}',
            currentDateStr: '{{ $serverDate }}',
            lastSyncTime: '{{ $serverTime }}',
            
            // Metrik kehadiran agregat
            metrics: {
                scheduled: {{ $metrics['scheduled'] }},
                total_scheduled: {{ $metrics['total_scheduled'] }},
                on_time: {{ $metrics['on_time'] }},
                late: {{ $metrics['late'] }},
                absent: {{ $metrics['absent'] }},
                not_started: {{ $metrics['not_started'] }},
                not_present: {{ $metrics['not_present'] }},
                on_leave: {{ $metrics['on_leave'] }}
            },
            
            // Baris presensi staf
            rows: @json($rows),
            
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
        }"
        class="space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Monitoring Kehadiran Tim
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Pantau kehadiran staf secara realtime untuk jadwal shift kerja hari ini.
                </p>
            </div>

            {{-- Live Clock & Polling Status Indicator --}}
            <div class="flex items-center gap-2.5">
                {{-- Live Clock --}}
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-brand-border text-xs text-[#2D2A3E] font-medium shadow-2xs">
                    <i data-lucide="clock" class="h-4 w-4 text-[#6C5CE7]"></i>
                    <span class="font-mono font-bold text-sm" x-text="currentTimeStr">{{ $serverTime }}</span>
                    <span class="text-[10px] font-semibold text-[#7C7896]">WIB</span>
                </div>

                {{-- Status Polling Indicator --}}
                <div class="flex items-center bg-white px-3 py-1.5 rounded-xl border border-brand-border text-xs font-semibold shadow-2xs">
                    <template x-if="isConnected">
                        <span class="flex items-center text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse inline-block mr-1.5"></span>
                            Live
                        </span>
                    </template>
                    <template x-if="!isConnected">
                        <span class="flex items-center text-rose-500">
                            <span class="w-2 h-2 rounded-full bg-rose-500 inline-block mr-1.5"></span>
                            Terputus
                        </span>
                    </template>
                </div>

                {{-- Manual Refresh Button --}}
                <button 
                    type="button"
                    @click="fetchFeed()"
                    :disabled="isFetching"
                    class="p-2 bg-white hover:bg-[#F5F3FF] text-[#7C7896] hover:text-[#6C5CE7] rounded-xl border border-brand-border shadow-2xs transition focus:outline-none"
                    title="Segarkan Data Sekarang"
                >
                    <i data-lucide="refresh-cw" class="h-4 w-4" :class="isFetching ? 'animate-spin text-[#6C5CE7]' : ''"></i>
                </button>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 4 KARTU METRIK RINGKASAN KEHADIRAN --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Kartu 1: Dijadwalkan --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">
                        Dijadwalkan
                    </p>
                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1" x-text="metrics.scheduled">
                        {{ $metrics['scheduled'] }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">
                        Total staf hari ini
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center shrink-0">
                    <i data-lucide="activity" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 2: Hadir Tepat Waktu --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">
                        Hadir Tepat Waktu
                    </p>
                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1" x-text="metrics.on_time">
                        {{ $metrics['on_time'] }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">
                        Sesuai batas toleransi
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="user-check" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 3: Terlambat --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">
                        Terlambat
                    </p>
                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1" x-text="metrics.late">
                        {{ $metrics['late'] }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">
                        Melewati toleransi
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 4: Belum Hadir --}}
            <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">
                        Belum Hadir
                    </p>
                    <p class="text-2xl font-bold text-[#2D2A3E] mt-1" x-text="metrics.not_present">
                        {{ $metrics['not_present'] }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">
                        <span x-text="metrics.absent">{{ $metrics['absent'] }}</span> lewat batas, 
                        <span x-text="metrics.not_started">{{ $metrics['not_started'] }}</span> belum mulai
                    </p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="user-x" class="h-6 w-6"></i>
                </div>
            </div>
        </div>

        {{-- Info Banner Toleransi & Departemen --}}
        <div class="bg-white rounded-xl border border-brand-border px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 shadow-2xs text-xs text-[#7C7896]">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="h-4 w-4 text-[#6C5CE7] shrink-0"></i>
                <span>
                    Departemen: <strong class="text-[#2D2A3E]">{{ $manager->department->name ?? 'Semua Departemen' }}</strong>
                    &bull; Toleransi Keterlambatan: <strong class="text-[#2D2A3E]">{{ $gracePeriod }} Menit</strong>
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px]">
                <i data-lucide="calendar" class="h-3.5 w-3.5 text-[#7C7896]"></i>
                <span x-text="currentDateStr">{{ $serverDate }}</span>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TABEL MONITORING KEHADIRAN STAF --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-brand-border shadow-2xs overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E2E0F7] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">
                        Daftar Presensi Staf Hari Ini
                    </h2>
                    <p class="text-xs text-[#7C7896] mt-0.5">
                        Data otomatis disinkronisasi setiap 20 detik tanpa memuat ulang halaman.
                    </p>
                </div>
                <div class="text-xs text-[#7C7896] flex items-center gap-1">
                    <span>Sinkronisasi terakhir:</span>
                    <span class="font-mono font-semibold text-[#2D2A3E]" x-text="lastSyncTime">{{ $serverTime }}</span>
                </div>
            </div>

            {{-- Empty State (Blade SSR & Alpine Client) --}}
            <div 
                x-show="rows.length === 0" 
                class="px-6 py-16 text-center"
                @if(!empty($rows)) style="display: none;" @endif
            >
                <div class="h-16 w-16 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="calendar-x" class="h-8 w-8"></i>
                </div>
                <h3 class="text-base font-bold text-[#2D2A3E]">
                    Tidak Ada Jadwal Shift Hari Ini
                </h3>
                <p class="text-xs text-[#7C7896] max-w-md mx-auto mt-1.5 leading-relaxed">
                    Tidak ditemukan penugasan staf yang dijadwalkan pada hari ini di departemen Anda.
                    Pastikan jadwal shift kerja telah dibuat dan diterbitkan.
                </p>
                <div class="mt-5">
                    <a 
                        href="{{ route('manager.shifts.index') }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-[#6C5CE7] hover:bg-[#4F46E5] text-white shadow-sm transition"
                    >
                        <i data-lucide="calendar" class="h-4 w-4"></i>
                        <span>Kelola Jadwal Kerja Tim</span>
                    </a>
                </div>
            </div>

            {{-- Table View --}}
            <div 
                x-show="rows.length > 0" 
                class="overflow-x-auto"
                @if(empty($rows)) style="display: none;" @endif
            >
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F5F3FF]/60 text-[11px] uppercase font-bold text-[#7C7896] tracking-wider border-b border-[#E2E0F7]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Nama Staf</th>
                            <th scope="col" class="px-6 py-3.5">Shift</th>
                            <th scope="col" class="px-6 py-3.5">Waktu Clock-In</th>
                            <th scope="col" class="px-6 py-3.5">Waktu Clock-Out</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E0F7] text-xs">
                        {{-- 1. Server-Side Initial Render (Blade) --}}
                        {{-- Ditampilkan sebelum Alpine.js mount untuk performa, SEO, dan test assertions --}}
                        @foreach ($rows as $row)
                            <tr x-show="!isClientMounted" class="hover:bg-[#F5F3FF]/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($row['profile_photo_url']))
                                            <img src="{{ $row['profile_photo_url'] }}" alt="{{ $row['name'] }}" class="h-9 w-9 rounded-full object-cover shrink-0">
                                        @else
                                            <div class="h-9 w-9 bg-[#6C5CE7] text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ $row['initials'] }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-[#2D2A3E]">{{ $row['name'] }}</p>
                                            <p class="text-[11px] text-[#7C7896]">{{ $row['position'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[#2D2A3E]">{{ $row['shift'] }}</div>
                                    <div class="text-[11px] text-[#7C7896] flex items-center gap-1 mt-0.5">
                                        <i data-lucide="clock" class="h-3 w-3"></i>
                                        <span>{{ $row['shift_start'] }} - {{ $row['shift_end'] }}</span>
                                        @if ($row['is_overnight'])
                                            <span class="text-[9px] font-bold text-[#6C5CE7] bg-[#F5F3FF] px-1.5 py-0.5 rounded border border-[#E2E0F7]">
                                                Overnight
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($row['clock_in'])
                                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                            {{ $row['clock_in'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($row['clock_out'])
                                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                            {{ $row['clock_out'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-mono">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($row['status'] === 'on_time')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Tepat Waktu
                                        </span>
                                    @elseif ($row['status'] === 'late')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Terlambat @if($row['late_minutes'] > 0) (+{{ $row['late_minutes'] }}m) @endif
                                        </span>
                                    @elseif ($row['status'] === 'on_leave')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Izin / Cuti
                                        </span>
                                    @elseif ($row['status'] === 'not_started')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Belum Mulai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Belum Hadir
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- 2. Client-Side Reactive Render (Alpine.js x-for) --}}
                        {{-- Mengambil alih setelah JavaScript aktif dan diperbarui tiap polling realtime --}}
                        <template x-for="row in rows" :key="row.assignment_id">
                            <tr x-show="isClientMounted" class="hover:bg-[#F5F3FF]/40 transition" style="display: none;">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <template x-if="row.profile_photo_url">
                                            <img :src="row.profile_photo_url" :alt="row.name" class="h-9 w-9 rounded-full object-cover shrink-0">
                                        </template>
                                        <template x-if="!row.profile_photo_url">
                                            <div class="h-9 w-9 bg-[#6C5CE7] text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0" x-text="row.initials">
                                            </div>
                                        </template>
                                        <div>
                                            <p class="font-bold text-[#2D2A3E]" x-text="row.name"></p>
                                            <p class="text-[11px] text-[#7C7896]" x-text="row.position"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[#2D2A3E]" x-text="row.shift"></div>
                                    <div class="text-[11px] text-[#7C7896] flex items-center gap-1 mt-0.5">
                                        <i data-lucide="clock" class="h-3 w-3"></i>
                                        <span><span x-text="row.shift_start"></span> - <span x-text="row.shift_end"></span></span>
                                        <template x-if="row.is_overnight">
                                            <span class="text-[9px] font-bold text-[#6C5CE7] bg-[#F5F3FF] px-1.5 py-0.5 rounded border border-[#E2E0F7]">
                                                Overnight
                                            </span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <template x-if="row.clock_in">
                                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200" x-text="row.clock_in"></span>
                                    </template>
                                    <template x-if="!row.clock_in">
                                        <span class="text-slate-400 font-mono">-</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4">
                                    <template x-if="row.clock_out">
                                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200" x-text="row.clock_out"></span>
                                    </template>
                                    <template x-if="!row.clock_out">
                                        <span class="text-slate-400 font-mono">-</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4">
                                    {{-- Badge Tepat Waktu --}}
                                    <template x-if="row.status === 'on_time'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Tepat Waktu</span>
                                        </span>
                                    </template>

                                    {{-- Badge Terlambat --}}
                                    <template x-if="row.status === 'late'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>Terlambat <template x-if="row.late_minutes > 0"><span x-text="'(+' + row.late_minutes + 'm)'"></span></template></span>
                                        </span>
                                    </template>

                                    {{-- Badge Izin / Cuti --}}
                                    <template x-if="row.status === 'on_leave'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            <span>Izin / Cuti</span>
                                        </span>
                                    </template>

                                    {{-- Badge Belum Mulai --}}
                                    <template x-if="row.status === 'not_started'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Belum Mulai</span>
                                        </span>
                                    </template>

                                    {{-- Badge Belum Hadir (Absent) --}}
                                    <template x-if="row.status === 'absent'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
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
</x-app-layout>
