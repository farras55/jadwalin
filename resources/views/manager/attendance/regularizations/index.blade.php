
<x-app-layout>
    <div
        class="space-y-6"
        x-data="{
            showApproveModal: false,
            showRejectModal: false,
            activeRegularization: null,
            approveUrl: '',
            rejectUrl: '',
            overtimeStatus: 'approved',
            approveNote: '',
            rejectNote: '',
            searchQuery: '',

            openApproveModal(reg) {
                this.activeRegularization = reg;
                this.approveUrl = '{{ url('/manager/attendance/regularizations') }}/' + reg.id + '/approve';
                this.overtimeStatus = 'approved';
                this.approveNote = '';
                this.showApproveModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeApproveModal() {
                this.showApproveModal = false;
                this.activeRegularization = null;
                this.approveUrl = '';
            },

            openRejectModal(reg) {
                this.activeRegularization = reg;
                this.rejectUrl = '{{ url('/manager/attendance/regularizations') }}/' + reg.id + '/reject';
                this.rejectNote = '';
                this.showRejectModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeRejectModal() {
                this.showRejectModal = false;
                this.activeRegularization = null;
                this.rejectUrl = '';
            },

            matchesSearch(name, reason, dateStr) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();

                return name.toLowerCase().includes(q) ||
                       reason.toLowerCase().includes(q) ||
                       dateStr.toLowerCase().includes(q);
            }
        }"
        @keydown.escape.window="closeApproveModal(); closeRejectModal()"
    >

        {{-- HEADER --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-2">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-[#6C5CE7] font-medium">Persetujuan Koreksi Kehadiran</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                        Persetujuan Koreksi Kehadiran
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                        Tinjau dan verifikasi permohonan koreksi jam pulang riil dari karyawan yang terkena auto clock-out.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                        <span>Menunggu Review:</span>
                        <strong class="font-bold text-[#6C5CE7]">{{ $counts['pending'] ?? 0 }}</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- FLASH ALERTS --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl border border-emerald-200 shadow-2xs flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="text-xs md:text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-200 shadow-2xs flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span class="text-xs md:text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-200 shadow-2xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs md:text-sm">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    <span>Terdapat kesalahan pada input formulir:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-6">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 4 KARTU STATISTIK --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total Pengajuan --}}
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'all', 'month' => $calendarMonth->format('Y-m')]) }}"
               class="bg-white rounded-2xl border p-4 shadow-2xs flex items-center justify-between transition-all hover:border-[#6C5CE7]/40 {{ $status === 'all' ? 'border-[#6C5CE7] ring-1 ring-[#6C5CE7]' : 'border-[#E2E0F7]' }}">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896]">Total Pengajuan</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1">{{ $counts['all'] ?? 0 }}</p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Sepanjang periode ini</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
            </a>

            {{-- Menunggu Review --}}
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'pending', 'month' => $calendarMonth->format('Y-m')]) }}"
               class="bg-white rounded-2xl border p-4 shadow-2xs flex items-center justify-between transition-all hover:border-amber-400 {{ $status === 'pending' ? 'border-amber-400 ring-1 ring-amber-400' : 'border-[#E2E0F7]' }}">
                <div>
                    <p class="text-xs font-semibold text-amber-700">Menunggu Review</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1">{{ $counts['pending'] ?? 0 }}</p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Perlu diproses</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </a>

            {{-- Disetujui --}}
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'approved', 'month' => $calendarMonth->format('Y-m')]) }}"
               class="bg-white rounded-2xl border p-4 shadow-2xs flex items-center justify-between transition-all hover:border-emerald-400 {{ $status === 'approved' ? 'border-emerald-400 ring-1 ring-emerald-400' : 'border-[#E2E0F7]' }}">
                <div>
                    <p class="text-xs font-semibold text-emerald-700">Disetujui</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1">{{ $counts['approved'] ?? 0 }}</p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Telah dikoreksi</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="check" class="w-5 h-5"></i>
                </div>
            </a>

            {{-- Ditolak --}}
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'rejected', 'month' => $calendarMonth->format('Y-m')]) }}"
               class="bg-white rounded-2xl border p-4 shadow-2xs flex items-center justify-between transition-all hover:border-rose-400 {{ $status === 'rejected' ? 'border-rose-400 ring-1 ring-rose-400' : 'border-[#E2E0F7]' }}">
                <div>
                    <p class="text-xs font-semibold text-rose-700">Ditolak</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1">{{ $counts['rejected'] ?? 0 }}</p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Tidak dapat diproses</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </div>
            </a>
        </div>

        {{-- KONTEN UTAMA --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- KALENDER --}}
            <div class="lg:col-span-4 bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs space-y-4">

                {{-- Header Kalender --}}
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E0F7]">

                    {{-- Bulan Sebelumnya --}}
                    <a
                        href="{{ route('manager.attendance.regularizations.index', ['status' => $status, 'month' => $calendarMonth->copy()->subMonth()->format('Y-m')]) }}"
                        class="text-xs font-bold text-[#7C7896] p-1.5 rounded-lg border border-[#E2E0F7] bg-[#F8F7FF] hover:bg-[#F5F3FF] hover:text-[#6C5CE7] transition"
                        aria-label="Bulan sebelumnya"
                    >
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>

                    <h3 class="text-sm font-bold text-[#2D2A3E]">
                        {{ $calendarMonth->translatedFormat('F Y') }}
                    </h3>

                    {{-- Bulan Berikutnya --}}
                    <a
                        href="{{ route('manager.attendance.regularizations.index', ['status' => $status, 'month' => $calendarMonth->copy()->addMonth()->format('Y-m')]) }}"
                        class="text-xs font-bold text-[#7C7896] p-1.5 rounded-lg border border-[#E2E0F7] bg-[#F8F7FF] hover:bg-[#F5F3FF] hover:text-[#6C5CE7] transition"
                        aria-label="Bulan berikutnya"
                    >
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                {{-- Data Kalender --}}
                @php
                    $startOfMonth = $calendarMonth->copy()->startOfMonth();
                    $daysInMonth = $calendarMonth->daysInMonth;
                    $firstDayOfWeek = $startOfMonth->dayOfWeekIso;
                    $today = now();

                    $isCurrentMonth = $calendarMonth->format('Y-m') === $today->format('Y-m');
                    $todayDate = $today->day;
                @endphp

                <div>
                    {{-- Nama Hari --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-[#7C7896] uppercase mb-2">
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                        <span>Min</span>
                    </div>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs">

                        {{-- Hari Kosong Sebelum Tanggal 1 --}}
                        @for ($i = 1; $i < $firstDayOfWeek; $i++)
                            <div class="h-8 flex items-center justify-center"></div>
                        @endfor

                        {{-- Tanggal Bulan Terpilih --}}
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $isToday = $isCurrentMonth && ($day === $todayDate);
                                $hasCorrection = in_array($day, $datesWithCorrections ?? []);
                            @endphp

                            <div class="h-8 relative flex flex-col items-center justify-center rounded-xl text-xs font-medium transition
                                {{ $isToday ? 'bg-[#6C5CE7] text-white font-bold shadow-2xs' : 'text-[#2D2A3E] hover:bg-[#F5F3FF]' }}">
                                <span>{{ $day }}</span>

                                @if ($hasCorrection && ! $isToday)
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#6C5CE7] absolute bottom-1"></span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Legenda --}}
                <div class="pt-4 border-t border-[#E2E0F7] space-y-2 text-xs">
                    <p class="font-bold text-[#2D2A3E] text-[11px]">Keterangan</p>

                    <div class="flex items-center gap-2 text-[#7C7896] text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-[#6C5CE7]"></span>
                        <span>Ada pengajuan koreksi</span>
                    </div>

                    <div class="flex items-center gap-2 text-[#7C7896] text-[11px]">
                        <span class="w-2.5 h-2.5 rounded-md bg-[#6C5CE7] inline-block"></span>
                        <span>Hari ini</span>
                    </div>
                </div>
            </div>

            {{-- DAFTAR PENGAJUAN --}}
            <div class="lg:col-span-8 space-y-4">

                {{-- Toolbar Pencarian & Filter --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 text-[#7C7896] absolute left-3.5 top-1/2 -translate-y-1/2"></i>

                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Cari nama staf, NIK, atau tanggal..."
                            class="w-full pl-9 pr-4 py-2 text-xs md:text-sm text-[#2D2A3E] placeholder-[#7C7896]/60 bg-[#F5F3FF]/40 border border-[#E2E0F7] rounded-xl focus:outline-none focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                        >
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#2D2A3E] bg-[#F8F7FF] border border-[#E2E0F7]">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                            <span>{{ $calendarMonth->copy()->startOfMonth()->format('d M') }} – {{ $calendarMonth->copy()->endOfMonth()->format('d M Y') }}</span>
                        </span>

                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#2D2A3E] bg-[#F8F7FF] border border-[#E2E0F7]">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                            <span>{{ auth()->user()->department->name ?? 'Semua Departemen' }}</span>
                        </span>
                    </div>
                </div>

                {{-- Container Tabel --}}
                <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">

                    <div class="px-5 py-4 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="text-sm md:text-base font-bold text-[#2D2A3E]">
                                Daftar Pengajuan Koreksi Kehadiran
                            </h2>
                            <p class="text-xs text-[#7C7896] mt-0.5">
                                Menampilkan {{ $regularizations->count() }} dari {{ $regularizations->total() }} pengajuan koreksi.
                            </p>
                        </div>

                        {{-- Filter Tabs --}}
                        <div class="inline-flex p-1 bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl text-[11px]">
                            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'pending', 'month' => $calendarMonth->format('Y-m')]) }}"
                               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'pending' ? 'bg-[#6C5CE7] text-white shadow-2xs' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                                Menunggu
                            </a>

                            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'approved', 'month' => $calendarMonth->format('Y-m')]) }}"
                               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'approved' ? 'bg-[#6C5CE7] text-white shadow-2xs' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                                Disetujui
                            </a>

                            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'rejected', 'month' => $calendarMonth->format('Y-m')]) }}"
                               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'rejected' ? 'bg-[#6C5CE7] text-white shadow-2xs' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                                Ditolak
                            </a>

                            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'all', 'month' => $calendarMonth->format('Y-m')]) }}"
                               class="px-2.5 py-1 rounded-lg font-semibold transition {{ $status === 'all' ? 'bg-[#6C5CE7] text-white shadow-2xs' : 'text-[#7C7896] hover:text-[#2D2A3E]' }}">
                                Semua
                            </a>
                        </div>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#F8F7FF] text-[#7C7896] font-bold border-b border-[#E2E0F7] uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3">Nama Staf</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Jam Semula</th>
                                    <th class="px-4 py-3">Jam Diajukan</th>
                                    <th class="px-4 py-3">Alasan</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[#E2E0F7]">
                                @forelse ($regularizations as $index => $reg)
                                    @php
                                        $empName = $reg->employee?->name ?? 'Karyawan';
                                        $initials = collect(explode(' ', $empName))
                                            ->map(fn($seg) => mb_substr($seg, 0, 1))
                                            ->take(2)
                                            ->implode('');

                                        $shift = $reg->shiftAssignment?->shift;

                                        $clockIn = $reg->shiftAssignment?->clock_in_time
                                            ? \Carbon\Carbon::parse($reg->shiftAssignment->clock_in_time)
                                            : null;

                                        $shiftEnd = $shift
                                            ? \App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($shift)
                                            : null;

                                        $requestedOut = $reg->requested_clock_out_time
                                            ? \Carbon\Carbon::parse($reg->requested_clock_out_time)
                                            : null;

                                        $dateFormatted = $shift?->start_time
                                            ? \Carbon\Carbon::parse($shift->start_time)->translatedFormat('d M Y')
                                            : ($requestedOut ? $requestedOut->translatedFormat('d M Y') : '-');

                                        $overtimeMinutes = ($requestedOut && $shiftEnd && $requestedOut->greaterThan($shiftEnd))
                                            ? $shiftEnd->diffInMinutes($requestedOut)
                                            : 0;

                                        $ovtHours = floor($overtimeMinutes / 60);
                                        $ovtRemMins = $overtimeMinutes % 60;

                                        $ovtStr = $ovtHours > 0
                                            ? "{$ovtHours} Jam " . str_pad($ovtRemMins, 2, '0', STR_PAD_LEFT) . " Menit"
                                            : "{$ovtRemMins} Menit";
                                    @endphp

                                    <tr
                                        x-show="matchesSearch(@js($empName), @js($reg->reason ?? ''), @js($dateFormatted))"
                                        class="hover:bg-[#F8F7FF]/50 transition"
                                    >
                                        {{-- No --}}
                                        <td class="px-4 py-3.5 text-center text-[#7C7896] font-medium">
                                            {{ $regularizations->firstItem() + $index }}
                                        </td>

                                        {{-- Nama Staf --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                    {{ strtoupper($initials) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-[#2D2A3E]">{{ $empName }}</p>
                                                    <p class="text-[11px] text-[#7C7896]">{{ $reg->employee?->position?->name ?? 'Barista' }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Tanggal --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 text-[#2D2A3E] font-medium">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                                <span>{{ $dateFormatted }}</span>
                                            </div>
                                        </td>

                                        {{-- Jam Semula --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap font-mono text-[#7C7896]">
                                            {{ $shiftEnd ? $shiftEnd->format('H:i') : ($clockIn ? $clockIn->format('H:i') : '-') }}
                                        </td>

                                        {{-- Jam Diajukan --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap font-mono font-bold text-[#2D2A3E]">
                                            {{ $requestedOut ? $requestedOut->format('H:i') : '-' }}
                                        </td>

                                        {{-- Alasan --}}
                                        <td class="px-4 py-3.5 max-w-[180px]">
                                            <p class="truncate text-[#7C7896] text-xs" title="{{ $reg->reason }}">
                                                {{ $reg->reason }}
                                            </p>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            @if ($reg->status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Menunggu
                                                </span>
                                            @elseif ($reg->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Disetujui
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Ditolak
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                            @if ($reg->status === 'pending')
                                                <div class="inline-flex items-center gap-1.5">
                                                    <button
                                                        type="button"
                                                        @click="openApproveModal({{ json_encode([
                                                            'id' => $reg->id,
                                                            'employee_name' => $empName,
                                                            'shift_title' => $shift?->title ?? 'Shift Regular',
                                                            'requested_time' => $requestedOut ? $requestedOut->format('d/m/Y H:i') : '-',
                                                            'overtime_str' => $ovtStr,
                                                            'reason' => $reg->reason
                                                        ]) }})"
                                                        class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center transition shadow-2xs"
                                                        title="Setujui Koreksi"
                                                    >
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="openRejectModal({{ json_encode([
                                                            'id' => $reg->id,
                                                            'employee_name' => $empName,
                                                            'shift_title' => $shift?->title ?? 'Shift Regular',
                                                            'reason' => $reg->reason
                                                        ]) }})"
                                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition shadow-2xs"
                                                        title="Tolak Koreksi"
                                                    >
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-[#F8F7FF] text-[#7C7896] border border-[#E2E0F7]" title="Pengajuan telah diproses">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-12 text-center text-[#7C7896]">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mb-1">
                                                    <i data-lucide="file-check" class="w-6 h-6"></i>
                                                </div>
                                                <p class="text-sm font-bold text-[#2D2A3E]">Tidak Ada Pengajuan Koreksi</p>
                                                <p class="text-xs text-[#7C7896] max-w-sm">
                                                    Semua permohonan koreksi jam pulang karyawan telah ditinjau atau belum ada pengajuan baru.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($regularizations->hasPages())
                        <div class="p-4 px-6 bg-[#F8F7FF] border-t border-[#E2E0F7] flex items-center justify-between">
                            {{ $regularizations->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MODAL APPROVE --}}
        <div
            x-show="showApproveModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-[#2D2A3E]/60 backdrop-blur-xs" @click="closeApproveModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E0F7] space-y-4">

                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E0F7]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#2D2A3E]">Setujui Koreksi Kehadiran</h3>
                                <p class="text-xs text-[#7C7896]">Perbarui jam pulang riil karyawan di sistem.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeApproveModal()" class="p-1.5 rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="approveUrl" method="POST" class="space-y-4">
                        @csrf

                        <div class="bg-[#F8F7FF] p-3.5 rounded-xl border border-[#E2E0F7] text-xs space-y-2">
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Karyawan:</span>
                                <span class="font-bold text-[#2D2A3E]" x-text="activeRegularization?.employee_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Shift:</span>
                                <span class="font-medium text-[#2D2A3E]" x-text="activeRegularization?.shift_title"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Jam Pulang Diajukan:</span>
                                <span class="font-mono font-bold text-emerald-700" x-text="activeRegularization?.requested_time"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Estimasi Tambahan Waktu:</span>
                                <span class="font-bold text-[#6C5CE7]" x-text="activeRegularization?.overtime_str"></span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D2A3E]">
                                Keputusan Kompensasi Lembur <span class="text-rose-500">*</span>
                            </label>

                            <select
                                name="overtime_status"
                                x-model="overtimeStatus"
                                required
                                class="w-full text-xs rounded-xl border border-[#E2E0F7] bg-[#F8F7FF]/50 p-2.5 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] font-medium text-[#2D2A3E]"
                            >
                                <option value="approved">Setujui Sebagai Lembur (Dihitung Jam Lembur Resmi)</option>
                                <option value="none">Setujui Jam Riil Saja (Tanpa Kompensasi Lembur)</option>
                                <option value="rejected">Tolak Klaim Lembur (Jam Kerja Di luar Shift Hangus)</option>
                            </select>

                            <p class="text-[11px] text-[#7C7896]">
                                Pilih opsi perhitungan lembur yang sesuai dengan kebijakan operasional.
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D2A3E]">
                                Catatan Manajer (Opsional)
                            </label>

                            <textarea
                                name="review_note"
                                x-model="approveNote"
                                rows="3"
                                placeholder="Tambahkan catatan untuk karyawan jika diperlukan..."
                                class="w-full text-xs rounded-xl border border-[#E2E0F7] bg-[#F8F7FF]/50 p-2.5 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] text-[#2D2A3E]"
                            ></textarea>
                        </div>

                        <div class="pt-3 border-t border-[#E2E0F7] flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                @click="closeApproveModal()"
                                class="px-4 py-2.5 rounded-xl border border-[#E2E0F7] text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition flex items-center gap-1.5"
                            >
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan &amp; Setujui</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL REJECT --}}
        <div
            x-show="showRejectModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-[#2D2A3E]/60 backdrop-blur-xs" @click="closeRejectModal()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E0F7] space-y-4">

                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E0F7]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#2D2A3E]">Tolak Koreksi Kehadiran</h3>
                                <p class="text-xs text-[#7C7896]">Catatan penolakan akan dikirimkan kepada karyawan.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeRejectModal()" class="p-1.5 rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="rejectUrl" method="POST" class="space-y-4">
                        @csrf

                        <div class="bg-rose-50/50 p-3.5 rounded-xl border border-rose-200/60 text-xs space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Karyawan:</span>
                                <span class="font-bold text-[#2D2A3E]" x-text="activeRegularization?.employee_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#7C7896]">Shift:</span>
                                <span class="font-medium text-[#2D2A3E]" x-text="activeRegularization?.shift_title"></span>
                            </div>
                            <div class="pt-1 text-[#2D2A3E]">
                                <strong>Alasan Karyawan:</strong>
                                <span class="italic text-[#7C7896]" x-text="activeRegularization?.reason"></span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D2A3E]">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                name="review_note"
                                x-model="rejectNote"
                                rows="3"
                                required
                                minlength="3"
                                placeholder="Tuliskan alasan mengapa koreksi jam pulang ditolak..."
                                class="w-full text-xs rounded-xl border border-[#E2E0F7] bg-[#F8F7FF]/50 p-2.5 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 text-[#2D2A3E]"
                            ></textarea>

                            <p class="text-[11px] text-[#7C7896]">Minimal 3 karakter.</p>
                        </div>

                        <div class="pt-3 border-t border-[#E2E0F7] flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                @click="closeRejectModal()"
                                class="px-4 py-2.5 rounded-xl border border-[#E2E0F7] text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-2xs transition flex items-center gap-1.5"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span>Tolak Pengajuan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>