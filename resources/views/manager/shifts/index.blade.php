<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    @if ($isGlobalManager)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200 shadow-card">
                            <i data-lucide="globe" class="h-3 w-3"></i>
                            Global Manager (Multi-Departemen)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 shadow-card">
                            <i data-lucide="shield" class="h-3 w-3"></i>
                            Manajer Departemen
                        </span>
                    @endif
                    <span class="text-xs text-slate-500 font-medium">Penjadwalan &amp; Roster Mingguan</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight mt-1.5">
                    Jadwal Kerja Tim (Roster)
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Kelola penugasan shift tim, kuota staf harian, dan publikasi roster kerja operasional.
                </p>
            </div>

            {{-- Departemen Switcher Utama (Single Source of Truth) --}}
            <div class="flex items-center gap-3">
                @if ($isGlobalManager)
                    {{-- Global Manager: Dropdown Pemilih Departemen --}}
                    <form method="GET" action="{{ route('manager.shifts.index') }}" class="flex items-center gap-2 bg-white p-1 rounded-xl border border-slate-200/80 shadow-card">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <div class="flex items-center gap-2 px-2 text-xs font-semibold text-slate-500">
                            <i data-lucide="building-2" class="h-4 w-4 text-indigo-600"></i>
                            <span class="hidden sm:inline">Departemen:</span>
                        </div>
                        <select name="department_id" onchange="this.form.submit()" 
                                class="text-xs font-bold bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-600 cursor-pointer">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $dept->id == $departmentId ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->draft_count ? $dept->draft_count . ' Draf, ' : '' }}{{ $dept->active_staff_count }} Staf)
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    {{-- Regular Manager: Terkunci pada Departemennya Sendiri --}}
                    <div class="bg-white border border-slate-200/80 rounded-xl px-3.5 py-2 flex items-center gap-3 shadow-card">
                        <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Departemen Anda</p>
                            <p class="text-xs font-bold text-slate-800">{{ $currentDepartment->name ?? 'Departemen Tim' }} ({{ $activeStaff->count() }} Staf)</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Notifikasi Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3.5 flex items-start gap-3 shadow-card">
            <i data-lucide="check-circle" class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="font-bold">Berhasil!</p>
                <p class="mt-0.5 text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3.5 flex items-start gap-3 shadow-card">
            <i data-lucide="alert-triangle" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="font-bold">Perhatian</p>
                <p class="mt-0.5 text-amber-700">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-3.5 flex items-start gap-3 shadow-card">
            <i data-lucide="info" class="h-4 w-4 text-blue-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="mt-0.5 text-blue-700">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    {{-- ALERT BAR CATATAN AUTO-SCHEDULE (COLLAPSIBLE / RINGKAS & TIDAK MENUMPUK) --}}
    @php
        $softWarns = session('soft_warnings', []);
        $unfilledWarns = session('unfilled_slots', []);
        $hasSchedulingAlerts = count($softWarns) > 0 || count($unfilledWarns) > 0;
    @endphp

    @if ($hasSchedulingAlerts)
        <div x-data="{ expanded: false }" class="mb-4 bg-amber-50/90 border border-amber-200 rounded-xl p-3.5 shadow-card transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 text-xs font-bold text-amber-900">
                    <span class="p-1 rounded-lg bg-amber-200/80 text-amber-800">
                        <i data-lucide="alert-circle" class="h-4 w-4"></i>
                    </span>
                    <span>
                        Catatan Hasil Auto-Schedule: 
                        @if (count($softWarns) > 0)
                            <span class="text-amber-800 underline font-semibold">{{ count($softWarns) }} Jeda Istirahat Singkat</span>
                        @endif
                        @if (count($softWarns) > 0 && count($unfilledWarns) > 0) • @endif
                        @if (count($unfilledWarns) > 0)
                            <span class="text-red-700 font-extrabold">{{ count($unfilledWarns) }} Slot Kurang Staf</span>
                        @endif
                    </span>
                </div>
                <button type="button" @click="expanded = !expanded" 
                        class="text-xs font-bold text-amber-800 hover:text-amber-950 flex items-center gap-1 self-end sm:self-auto px-2.5 py-1 rounded-xl bg-amber-100/70 hover:bg-amber-100 transition">
                    <span x-text="expanded ? 'Sembunyikan Rincian' : 'Lihat Rincian'"></span>
                    <i data-lucide="chevron-down" class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                </button>
            </div>

            <div x-show="expanded" x-collapse class="mt-3 pt-3 border-t border-amber-200 space-y-2.5 text-xs text-amber-900">
                @if (count($softWarns) > 0)
                    <div>
                        <p class="font-bold flex items-center gap-1.5 text-amber-950">
                            <i data-lucide="clock" class="h-3.5 w-3.5 text-amber-700"></i>
                            Jeda Istirahat Kurang dari Standar (Diizinkan dengan Review Manajer):
                        </p>
                        <ul class="mt-1 space-y-1 pl-5 list-disc text-amber-800 text-[11px]">
                            @foreach ($softWarns as $sw)
                                <li>
                                    <strong>{{ $sw['user_name'] }}</strong> pada shift <em>{{ $sw['shift_title'] }}</em> ({{ $sw['date'] }}): 
                                    {{ $sw['reason'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (count($unfilledWarns) > 0)
                    <div class="pt-2 border-t border-amber-200/60">
                        <p class="font-bold flex items-center gap-1.5 text-red-900">
                            <i data-lucide="user-x" class="h-3.5 w-3.5 text-red-600"></i>
                            Slot Kekurangan Staf (Unfilled Slots):
                        </p>
                        <p class="mt-1 text-[11px] text-red-700">
                            Kandidat staf memenuhi syarat habis karena batas jam kerja atau izin halangan. Slot ini ditandai khusus pada kalender dan dapat disiarkan langsung sebagai <strong>Open Shift</strong> sukarela.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- KOMPONEN KONTROL UTAMA: CONTEXT BAR & GUIDED WORKFLOW    --}}
    {{-- ======================================================== --}}
    <div x-data="{ 
            showBuildModal: false, 
            showPublishModal: false, 
            showClearModal: false, 
            publishScope: 'current', 
            clearScope: 'current', 
            overwriteMode: '1' 
         }">

        {{-- 1. CONTEXT BAR: NAVIGASI WAKTU, PRESETS & QUICK STATS --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3 sm:p-4 shadow-card mb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Navigasi Tanggal --}}
            <div class="flex flex-wrap items-center gap-2 max-w-full">
                <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $prevStart, 'end_date' => $prevEnd]) }}"
                   class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shrink-0"
                   title="Periode Sebelumnya">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                </a>

                <form method="GET" action="{{ route('manager.shifts.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2 sm:px-2.5 py-1 max-w-full">
                    <input type="hidden" name="department_id" value="{{ $departmentId }}">
                    <i data-lucide="calendar" class="h-4 w-4 text-indigo-600 shrink-0 hidden xs:inline"></i>
                    
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded px-1.5 sm:px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-600 max-w-[125px] sm:max-w-none">
                    
                    <span class="text-xs font-semibold text-slate-400">s.d.</span>
                    
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded px-1.5 sm:px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-600 max-w-[125px] sm:max-w-none">
                    
                    <button type="submit" 
                            class="text-xs font-semibold px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded transition shadow-card shrink-0">
                        Terapkan
                    </button>
                </form>

                <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $nextStart, 'end_date' => $nextEnd]) }}"
                   class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shrink-0"
                   title="Periode Berikutnya">
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </a>

                {{-- Presets Cepat --}}
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $thisWeekStart, 'end_date' => $thisWeekEnd]) }}"
                       class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border {{ $startDate->format('Y-m-d') === $thisWeekStart && $endDate->format('Y-m-d') === $thisWeekEnd ? 'bg-slate-900 border-slate-900 text-white shadow-card' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50' }} transition">
                        Pekan Ini
                    </a>
                    <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $thisMonthStart, 'end_date' => $thisMonthEnd]) }}"
                       class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border {{ $startDate->format('Y-m-d') === $thisMonthStart && $endDate->format('Y-m-d') === $thisMonthEnd ? 'bg-slate-900 border-slate-900 text-white shadow-card' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50' }} transition">
                        Bulan Ini
                    </a>
                </div>
            </div>

            {{-- Ringkasan Metrik Singkat (KPI Inline) --}}
            <div class="flex items-center gap-2 sm:gap-3 text-xs w-full md:w-auto overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg">
                    <i data-lucide="layers" class="h-3.5 w-3.5 text-indigo-600"></i>
                    <span class="text-slate-500">Total Shift:</span>
                    <strong class="text-slate-900 font-mono">{{ $totalShifts }}</strong>
                </div>

                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg">
                    <i data-lucide="clock" class="h-3.5 w-3.5 text-blue-600"></i>
                    <span class="text-slate-500">Jam:</span>
                    <strong class="text-slate-900 font-mono">{{ $totalScheduledHours }} Jam</strong>
                </div>

                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg">
                    <i data-lucide="users" class="h-3.5 w-3.5 text-emerald-600"></i>
                    <span class="text-slate-500">Staf:</span>
                    <strong class="text-slate-900 font-mono">{{ $uniqueStaffCount }}/{{ $activeStaff->count() }}</strong>
                </div>

                @if ($unfilledSlotsCount > 0)
                    <div class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-lg text-rose-700">
                        <i data-lucide="user-x" class="h-3.5 w-3.5"></i>
                        <span>Kurang:</span>
                        <strong class="font-mono">{{ $unfilledSlotsCount }}</strong>
                    </div>
                @endif
            </div>
        </div>

        {{-- 2. GUIDED WORKFLOW ACTION CARD (STATE-BASED: JELAS & ELEGAN) --}}
        @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
            {{-- State: Global Manager Ada Draf Siap Terbit --}}
            <div class="mb-4 bg-white border border-purple-200 rounded-xl p-4 shadow-card flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-purple-100 text-purple-800">
                                Mode Multi-Departemen
                            </span>
                            <span class="text-xs font-semibold text-purple-900">
                                Total {{ $totalCompanyDraftShifts }} Draf Siap Terbit di {{ $departments->where('draft_count', '>', 0)->count() }} Departemen
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mt-1">
                            Tinjau Kalender Tim &bull; Departemen {{ $currentDepartment->name }} ({{ $draftShiftsCount }} Draf)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Jadwal berstatus <strong>Draf</strong> (staf belum dapat melihat). Periksa matriks jadwal di bawah, lalu klik <strong>Terbitkan</strong> agar resmi aktif.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center flex-wrap gap-2 shrink-0 self-end md:self-auto">
                    <button type="button" @click="showClearModal = true; clearScope = 'current'"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                        <span>Hapus Draf</span>
                    </button>

                    <button type="button" @click="showBuildModal = true"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                        <i data-lucide="sliders" class="h-3.5 w-3.5 text-slate-500"></i>
                        <span>Atur Kebutuhan</span>
                    </button>

                    <button type="button" @click="showPublishModal = true; publishScope = 'all'"
                            class="text-xs font-semibold px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="send" class="h-3.5 w-3.5"></i>
                        <span>Terbitkan Semua ({{ $totalCompanyDraftShifts }})</span>
                    </button>
                </div>
            </div>
        @elseif ($draftShiftsCount > 0)
            {{-- State: Regular Manager Ada Draf Belum Terbit --}}
            <div class="mb-4 bg-white border border-amber-200 rounded-xl p-4 shadow-card flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="clipboard-pen" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                                Mode Peninjauan Draf
                            </span>
                            <span class="text-xs font-semibold text-amber-900">
                                {{ $draftShiftsCount }} Shift Draf Belum Terbit
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mt-1">
                            Jadwal Siap Ditinjau Sebelum Diterbitkan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Karyawan <strong>belum dapat melihat</strong> jadwal ini. Klik pada shift untuk modifikasi manual jika diperlukan, lalu klik <strong>Terbitkan Jadwal</strong>.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center flex-wrap gap-2 shrink-0 self-end md:self-auto">
                    <button type="button" @click="showClearModal = true; clearScope = 'current'"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                        <span>Hapus Draf</span>
                    </button>

                    <button type="button" @click="showBuildModal = true"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                        <i data-lucide="sliders" class="h-3.5 w-3.5 text-slate-500"></i>
                        <span>Atur Kebutuhan</span>
                    </button>

                    <button type="button" @click="showPublishModal = true; publishScope = 'current'"
                            class="text-xs font-semibold px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="send" class="h-3.5 w-3.5"></i>
                        <span>Terbitkan Jadwal ({{ $draftShiftsCount }})</span>
                    </button>
                </div>
            </div>
        @elseif ($totalShifts > 0)
            {{-- State: Jadwal Resmi Aktif (Semua Published) --}}
            <div class="mb-4 bg-white border border-slate-200/80 rounded-xl p-4 shadow-card flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-check" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                Jadwal Resmi Aktif
                            </span>
                            <span class="text-xs font-semibold text-emerald-800">
                                {{ $publishedShiftsCount }} Shift Terbit
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mt-1">
                            Roster Aktif dan Dapat Diakses Seluruh Karyawan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Seluruh staf departemen dapat melihat jadwal shift dan melakukan presensi kehadiran sesuai kalender ini.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center flex-wrap gap-2 shrink-0 self-end md:self-auto">
                    <button type="button" @click="showBuildModal = true"
                            class="text-xs font-semibold px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition flex items-center gap-2">
                        <i data-lucide="sliders" class="h-3.5 w-3.5"></i>
                        <span>Susun Ulang Roster</span>
                    </button>
                </div>
            </div>
        @else
            {{-- State: Kosong / Belum Ada Jadwal --}}
            <div class="mb-4 bg-white border border-slate-200/80 rounded-xl p-4 sm:p-5 shadow-card flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                Belum Ada Jadwal
                            </span>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 mt-1">
                            Susun Jadwal untuk {{ $currentDepartment->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5 max-w-xl">
                            Susun jadwal otomatis sesuai kebutuhan kuota shift harian, membagi jam kerja secara merata, dan mematuhi batas regulasi.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 self-end md:self-auto">
                    <button type="button" @click="showBuildModal = true"
                            class="text-xs font-semibold px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition flex items-center gap-2">
                        <i data-lucide="sliders" class="h-3.5 w-3.5"></i>
                        <span>Mulai Susun Jadwal</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- 3. TAB NAVIGASI DEPARTEMEN RINGKAS (UNTUK GLOBAL MANAGER) --}}
        @if ($isGlobalManager && count($departments) > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-4 scrollbar-thin">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
                    <i data-lucide="building-2" class="h-3.5 w-3.5 text-indigo-600"></i>
                    <span>Tinjau Tim:</span>
                </span>
                @foreach ($departments as $dept)
                    @php
                        $isActiveDept = ($dept->id == $departmentId);
                    @endphp
                    <a href="{{ route('manager.shifts.index', ['department_id' => $dept->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-card whitespace-nowrap {{ $isActiveDept ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white hover:bg-indigo-50 text-slate-900 border border-slate-200/80' }}">
                        <span>{{ $dept->name }}</span>
                        @if ($dept->draft_count > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold {{ $isActiveDept ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                                {{ $dept->draft_count }} Draf
                            </span>
                        @elseif ($dept->published_count > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold {{ $isActiveDept ? 'bg-emerald-300 text-emerald-950' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $dept->published_count }} Terbit
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MODAL BUILDER: MULTI-DEPARTEMEN FLEKSIBEL (1 S.D SEMUA)   --}}
        {{-- ======================================================== --}}
        @php
            $departmentsToConfigure = $isGlobalManager ? $departments : ($currentDepartment ? collect([$currentDepartment]) : collect());
        @endphp

        <div x-show="showBuildModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto p-2 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-xs flex justify-center items-start sm:items-center"
             style="display: none;">
            
            <div @click.away="!isSubmitting && (showBuildModal = false)" 
                 class="relative bg-white rounded-xl max-w-full sm:max-w-3xl xl:max-w-4xl w-full shadow-xl border border-slate-200/80 h-[92vh] sm:h-[86vh] sm:max-h-[720px] flex flex-col overflow-hidden my-auto"
                 x-data="{ 
                      allDeptIds: @js($departmentsToConfigure->pluck('id')->all()),
                      selectedDeptIds: @js($isGlobalManager ? $departmentsToConfigure->pluck('id')->all() : [$departmentId]),
                      activeDeptTab: {{ $departmentId ?: ($departments->first()?->id ?? 1) }},
                      buildMode: 'quick', {{-- 'quick' (template rutin) atau 'custom' (atur kartu) --}}
                      isSubmitting: false,
                      loadingStep: 1,
                      loadingPercent: 15,
                      loadingSteps: [
                          { title: 'Inisialisasi & Preferensi Staf', desc: 'Menyaring ketersediaan, izin, cuti, dan preferensi waktu' },
                          { title: 'Validasi Batas Beban Kerja', desc: 'Memvalidasi batas maksimal jam kerja reguler agar staf tidak overwork' },
                          { title: 'Pemeriksaan Rest Period 11 Jam', desc: 'Menghitung jeda istirahat minimum antar pergantian shift' },
                          { title: 'Optimasi Alokasi Berkeadilan', desc: 'Mendistribusikan penugasan secara berimbang dan adil' },
                          { title: 'Finalisasi & Penyimpanan Draf', desc: 'Menyimpan draf jadwal baru ke database' }
                      ],
                      startLoadingAnimation() {
                          this.isSubmitting = true;
                          this.loadingStep = 1;
                          this.loadingPercent = 18;
                          const interval = setInterval(() => {
                              if (this.loadingStep < 5) {
                                  this.loadingStep++;
                                  this.loadingPercent = Math.min(95, this.loadingStep * 20);
                              } else {
                                  this.loadingPercent = 98;
                                  clearInterval(interval);
                              }
                          }, 700);
                      },
                      toggleDept(deptId) {
                          const idx = this.selectedDeptIds.indexOf(deptId);
                          if (idx > -1) {
                              if (this.selectedDeptIds.length > 1) {
                                  this.selectedDeptIds.splice(idx, 1);
                                  if (this.activeDeptTab === deptId) {
                                      this.activeDeptTab = this.selectedDeptIds[0];
                                  }
                              }
                          } else {
                              this.selectedDeptIds.push(deptId);
                              this.activeDeptTab = deptId;
                          }
                      },
                      selectAllDepts() {
                          this.selectedDeptIds = [...this.allDeptIds];
                      },
                      selectOnlyCurrentDept() {
                          this.selectedDeptIds = [{{ $departmentId }}];
                          this.activeDeptTab = {{ $departmentId }};
                      },
                      colorPalette: ['#4F46E5', '#10B981', '#3B82F6', '#F97316', '#EC4899', '#F59E0B'],
                      deptShifts: {
                          @foreach ($departmentsToConfigure as $dept)
                              {{ $dept->id }}: [
                                  @foreach ($dept->shiftTemplates as $tmpl)
                                      {
                                          id: 'tmpl_{{ $tmpl->id }}',
                                          template_id: {{ $tmpl->id }},
                                          title: @js($tmpl->title),
                                          start_time: @js(substr($tmpl->start_time, 0, 5)),
                                          end_time: @js(substr($tmpl->end_time, 0, 5)),
                                          quota: {{ $tmpl->default_quota ?? 1 }},
                                          color_code: @js($tmpl->color_code ?? '#4F46E5'),
                                          days: [1, 2, 3, 4, 5, 6, 7],
                                          enabled: true,
                                          is_custom: false
                                      },
                                  @endforeach
                              ],
                          @endforeach
                      },
                      addShift(deptId, presetTitle = null, presetStart = null, presetEnd = null) {
                          if (!this.deptShifts[deptId]) this.deptShifts[deptId] = [];
                          const count = this.deptShifts[deptId].length + 1;
                          const palette = ['#4F46E5', '#10B981', '#3B82F6', '#F97316', '#EC4899', '#F59E0B'];
                          const color = palette[(count - 1) % palette.length];
                          this.deptShifts[deptId].push({
                              id: 'custom_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                              template_id: null,
                              title: presetTitle || ('Shift ' + count),
                              start_time: presetStart || '08:00',
                              end_time: presetEnd || '16:00',
                              quota: 1,
                              color_code: color,
                              days: [1, 2, 3, 4, 5, 6, 7],
                              enabled: true,
                              is_custom: true
                          });
                          this.$nextTick(() => {
                              if (window.lucide) lucide.createIcons();
                          });
                      },
                      removeShift(deptId, index) {
                          this.deptShifts[deptId].splice(index, 1);
                      },
                      selectAllDays(shift) {
                          shift.days = [1, 2, 3, 4, 5, 6, 7];
                      },
                      selectWeekdays(shift) {
                          shift.days = [1, 2, 3, 4, 5];
                      },
                      toggleDay(shift, day) {
                          const idx = shift.days.indexOf(day);
                          if (idx > -1) {
                              shift.days.splice(idx, 1);
                          } else {
                              shift.days.push(day);
                              shift.days.sort((a, b) => a - b);
                          }
                      }
                 }">

                {{-- ANIMASI LOADING SMART BUILDER --}}
                <div x-show="isSubmitting"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-98"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute inset-0 z-50 bg-white/95 backdrop-blur-md flex flex-col items-center justify-center p-4 sm:p-6 text-center select-none overflow-y-auto"
                     style="display: none;">
                    
                    <div class="relative z-10 max-w-lg w-full flex flex-col items-center my-auto">
                        <div class="mb-4 flex items-center justify-center">
                            <div class="h-11 w-11 rounded-full border-3 border-slate-200 border-t-indigo-600 animate-spin"></div>
                        </div>

                        <div class="space-y-1 mb-4 text-center">
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                Menyusun Jadwal Kerja Otomatis
                            </h3>
                            <p class="text-xs text-slate-500">
                                Memproses ketersediaan staf, batas jam reguler, dan rotasi shift tim...
                            </p>
                        </div>

                        <div class="w-full bg-slate-100 rounded-full h-2 mb-2 overflow-hidden border border-slate-200/80">
                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-500 ease-out"
                                 :style="'width: ' + loadingPercent + '%'"></div>
                        </div>

                        <div class="w-full flex items-center justify-between text-[11px] font-bold text-slate-500 mb-3">
                            <span class="text-indigo-600 font-mono" x-text="'Tahap ' + loadingStep + ' dari 5'"></span>
                            <span class="text-slate-900 font-mono" x-text="loadingPercent + '% Selesai'"></span>
                        </div>

                        <div class="w-full space-y-1.5 text-left bg-slate-50 border border-slate-200/80 rounded-xl p-3">
                            <template x-for="(step, sIndex) in loadingSteps" :key="sIndex">
                                <div class="flex items-center gap-2.5 py-0.5"
                                     :class="(sIndex + 1) === loadingStep ? 'opacity-100 font-bold' : ((sIndex + 1) < loadingStep ? 'opacity-90' : 'opacity-40')">
                                    <div class="h-5 w-5 rounded-lg flex items-center justify-center text-xs shrink-0"
                                         :class="(sIndex + 1) < loadingStep ? 'bg-emerald-600 text-white' : ((sIndex + 1) === loadingStep ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-400')">
                                        <template x-if="(sIndex + 1) < loadingStep">
                                            <i data-lucide="check" class="h-3 w-3"></i>
                                        </template>
                                        <template x-if="(sIndex + 1) === loadingStep">
                                            <span class="text-[10px] font-bold">&bull;</span>
                                        </template>
                                        <template x-if="(sIndex + 1) > loadingStep">
                                            <span class="text-[10px] font-bold" x-text="sIndex + 1"></span>
                                        </template>
                                    </div>
                                    <span class="text-xs truncate flex-1 text-slate-900" x-text="step.title"></span>
                                    <span x-show="(sIndex + 1) < loadingStep" class="text-[10px] font-bold text-emerald-600">Selesai</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Header Modal --}}
                <div class="px-5 py-4 border-b border-slate-200/80 bg-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl shrink-0">
                            <i data-lucide="sliders" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                Atur Kebutuhan &amp; Build Jadwal
                            </h3>
                            <p class="text-xs text-slate-500">
                                Susun penugasan cerdas otomatis sesuai kebutuhan shift masing-masing departemen.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="!isSubmitting && (showBuildModal = false)"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                {{-- Form Konfigurasi --}}
                <form method="POST" action="{{ route('manager.shifts.generate') }}" @submit="if ($el.checkValidity()) startLoadingAnimation()" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    <input type="hidden" name="view_department_id" :value="activeDeptTab">

                    {{-- Baris Rentang Tanggal & Mode Selector --}}
                    <div class="px-5 py-2.5 bg-indigo-50 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-2.5 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i data-lucide="calendar-range" class="h-3.5 w-3.5 text-indigo-600"></i>
                                <span>Periode Roster:</span>
                            </span>
                            <div class="flex items-center gap-1.5 bg-white border border-slate-200/80 rounded-xl px-2.5 py-1 shadow-card">
                                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" required
                                       class="text-xs font-bold text-slate-900 border-0 p-0 focus:ring-0">
                                <span class="text-xs font-semibold text-slate-500">s.d.</span>
                                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" required
                                       class="text-xs font-bold text-slate-900 border-0 p-0 focus:ring-0">
                            </div>
                        </div>

                        {{-- Mode Selector: Rutin (Cepat) vs Kustom Shift --}}
                        <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200/80 shadow-card">
                            <button type="button" @click="buildMode = 'quick'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="buildMode === 'quick' ? 'bg-indigo-600 text-white shadow-card' : 'text-slate-500 hover:text-slate-900'">
                                Mode Rutin (Cepat)
                            </button>
                            <button type="button" @click="buildMode = 'custom'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                    :class="buildMode === 'custom' ? 'bg-indigo-600 text-white shadow-card' : 'text-slate-500 hover:text-slate-900'">
                                Kustom Shift
                            </button>
                        </div>
                    </div>

                    {{-- Pemilih Multi-Departemen Fleksibel (Khusus Global Manager: Bisa 1, Beberapa, atau Semua) --}}
                    @if ($isGlobalManager && $departmentsToConfigure->count() > 1)
                        <div class="px-5 py-3 bg-white border-b border-slate-200/80 shrink-0 space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="h-4 w-4 text-indigo-600"></i>
                                        <span>Pilih Departemen Sasaran:</span>
                                    </span>
                                    <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600"
                                          x-text="selectedDeptIds.length + ' dari ' + allDeptIds.length + ' Departemen Dipilih'"></span>
                                </div>

                                {{-- Shortcut Tombol Cepat --}}
                                <div class="flex items-center gap-2 text-[11px]">
                                    <button type="button" @click="selectAllDepts()" 
                                            class="font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                        Centang Semua ({{ count($departmentsToConfigure) }})
                                    </button>
                                    <span class="text-slate-300">•</span>
                                    <button type="button" @click="selectOnlyCurrentDept()" 
                                            class="text-slate-500 hover:text-slate-900 hover:underline">
                                        Hanya {{ $currentDepartment->name }}
                                    </button>
                                </div>
                            </div>

                            {{-- Grid Kartu Checklist Departemen --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                @foreach ($departmentsToConfigure as $dept)
                                    @php
                                        $tmplCount = $dept->shiftTemplates->count();
                                    @endphp
                                    <div @click="toggleDept({{ $dept->id }})"
                                         class="p-2 sm:p-2.5 rounded-xl border transition cursor-pointer select-none relative flex flex-col justify-between"
                                         :class="selectedDeptIds.includes({{ $dept->id }}) 
                                            ? (activeDeptTab === {{ $dept->id }} ? 'bg-indigo-50 border-indigo-600 ring-2 ring-[#4F46E5]/40 shadow-xs' : 'bg-purple-50/50 border-indigo-600/50 shadow-card') 
                                            : 'bg-slate-50 border-slate-200 opacity-50 hover:opacity-80'">
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="text-xs font-bold truncate text-slate-900">{{ $dept->name }}</span>
                                            <input type="checkbox" :checked="selectedDeptIds.includes({{ $dept->id }})" @click.stop="toggleDept({{ $dept->id }})"
                                                   class="rounded text-indigo-600 focus:ring-indigo-600 h-3.5 w-3.5 cursor-pointer">
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-slate-500">
                                            <span class="font-bold" :class="selectedDeptIds.includes({{ $dept->id }}) ? 'text-indigo-600' : 'text-slate-500'">{{ $tmplCount }} Shift</span>
                                            <span>{{ $dept->active_staff_count }} Staf</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Tab Pemilih Tim untuk Pratinjau / Kustomisasi --}}
                            <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                                <span class="text-[10px] font-bold text-slate-500 uppercase shrink-0 mr-1">Tinjau Shift:</span>
                                @foreach ($departmentsToConfigure as $dept)
                                    <button type="button" 
                                            x-show="selectedDeptIds.includes({{ $dept->id }})"
                                            @click="activeDeptTab = {{ $dept->id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border text-xs font-bold transition whitespace-nowrap"
                                            :class="activeDeptTab === {{ $dept->id }} ? 'bg-indigo-600 border-indigo-600 text-white shadow-card' : 'bg-white border-slate-200/80 text-slate-900 hover:bg-slate-50'">
                                        <span>{{ $dept->name }}</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold"
                                              :class="activeDeptTab === {{ $dept->id }} ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600'">
                                            {{ $dept->shiftTemplates->count() }} Shift
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Hidden inputs pengirim departemen --}}
                        <template x-for="dId in selectedDeptIds" :key="dId">
                            <input type="hidden" name="department_ids[]" :value="dId">
                        </template>
                    @else
                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                    @endif

                    {{-- Konten Utama Modal (Scrollable) --}}
                    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">
                        {{-- MODE A: CEPAT / STANDAR RUTIN --}}
                        <div x-show="buildMode === 'quick'" class="space-y-4">
                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg mt-0.5">
                                        <i data-lucide="info" class="h-4 w-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">Gunakan Template Standar Masing-Masing Departemen</h4>
                                        <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                                            Sistem akan menyusun penugasan otomatis menggunakan master shift template masing-masing departemen. Setiap divisi memiliki jumlah shift dan jam kerja yang berbeda sesuai operasionalnya.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($isGlobalManager && $departmentsToConfigure->count() > 1)
                                {{-- Tinjauan Departemen Terpilih: Tampilkan Kartu Seluruh Departemen yang Dipilih & Shift Aslinya --}}
                                <div>
                                    <h5 class="text-xs font-bold text-slate-900 mb-2.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i data-lucide="layers" class="h-3.5 w-3.5 text-indigo-600"></i>
                                            <span>Rincian Shift Departemen Terpilih:</span>
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-normal">Klik salah satu tim untuk pratinjau detail</span>
                                    </h5>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                        @foreach ($departmentsToConfigure as $dept)
                                            <div x-show="selectedDeptIds.includes({{ $dept->id }})"
                                                 @click="activeDeptTab = {{ $dept->id }}" 
                                                 class="p-3 bg-white rounded-xl border transition cursor-pointer hover:border-indigo-600"
                                                 :class="activeDeptTab === {{ $dept->id }} ? 'border-indigo-600 ring-2 ring-[#4F46E5]/30 bg-indigo-50/40 shadow-xs' : 'border-slate-200/80 shadow-card'">
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <span class="text-xs font-bold text-slate-900 truncate mr-1">{{ $dept->name }}</span>
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 shrink-0">
                                                        {{ $dept->shiftTemplates->count() }} Shift
                                                    </span>
                                                </div>
                                                <div class="space-y-1 text-[11px] text-slate-500">
                                                    @forelse ($dept->shiftTemplates as $t)
                                                        <div class="flex items-center justify-between">
                                                            <span class="truncate">&bull; {{ $t->title }}</span>
                                                            <span class="font-bold text-slate-900 shrink-0 ml-1">{{ $t->default_quota }} Org</span>
                                                        </div>
                                                    @empty
                                                        <span class="text-slate-400 italic text-[10px]">Belum ada template shift</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Shift Detail Departemen yang Sedang Dipilih --}}
                            <div>
                                @foreach ($departmentsToConfigure as $dept)
                                    <div x-show="activeDeptTab === {{ $dept->id }}" class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                                <i data-lucide="layers" class="h-3.5 w-3.5 text-indigo-600"></i>
                                                <span>Shift yang Akan Disusun untuk <strong>{{ $dept->name }}</strong> ({{ $dept->shiftTemplates->count() }} Shift):</span>
                                            </h5>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            @forelse ($dept->shiftTemplates as $tmpl)
                                                <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-card flex items-center justify-between"
                                                     style="border-left: 4px solid {{ $tmpl->color_code ?? '#4F46E5' }};">
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-900">{{ $tmpl->title }}</p>
                                                        <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                            <i data-lucide="clock" class="h-3 w-3"></i>
                                                            {{ substr($tmpl->start_time, 0, 5) }} – {{ substr($tmpl->end_time, 0, 5) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-xs font-extrabold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-xl border border-slate-200/80">
                                                        {{ $tmpl->default_quota ?? 1 }} Orang/Hari
                                                    </span>
                                                </div>
                                            @empty
                                                <div class="col-span-2 p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                                    <p class="text-xs text-slate-500">Belum ada template shift standar pada departemen {{ $dept->name }}.</p>
                                                    <button type="button" @click="buildMode = 'custom'" class="mt-2 text-xs font-bold text-indigo-600 hover:underline">
                                                        Gunakan Mode Kustom untuk Menambah Shift &rarr;
                                                    </button>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-[11px] text-slate-500 space-y-1">
                                <p class="font-bold text-slate-900 flex items-center gap-1">
                                    <i data-lucide="shield-check" class="h-3.5 w-3.5 text-emerald-600"></i>
                                    Aturan Kepatuhan Otomatis:
                                </p>
                                <p>&bull; Jeda istirahat minimum 11 jam divalidasi antar shift</p>
                                <p>&bull; Staf yang berhalangan kerja (kuliah/izin yang disetujui) tidak akan dijadwalkan</p>
                                <p>&bull; Jam kerja didistribusikan secara berimbang (adil)</p>
                            </div>
                        </div>

                        {{-- MODE B: KUSTOM SHIFT (INLINE KARTU, TANPA MODAL BERTUMPUK) --}}
                        <div x-show="buildMode === 'custom'" class="space-y-3">
                            {{-- Daftar Kartu Shift Berdasarkan Departemen yang Sedang Dipilih --}}
                            @foreach ($departmentsToConfigure as $dept)
                                <div x-show="activeDeptTab === {{ $dept->id }}" class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900">Konfigurasi Shift: {{ $dept->name }}</span>
                                            <p class="text-[11px] text-slate-500">Sesuaikan jam kerja dan kebutuhan orang per shift</p>
                                        </div>
                                        <button type="button" @click="addShift({{ $dept->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-card transition">
                                            <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                                            <span>Tambah Shift untuk {{ $dept->name }}</span>
                                        </button>
                                    </div>

                                    <div x-show="(deptShifts[{{ $dept->id }}] || []).length === 0" class="p-6 text-center bg-white rounded-xl border-2 border-dashed border-slate-200/80">
                                        <p class="text-xs text-slate-500">Belum ada shift dikonfigurasi untuk {{ $dept->name }}.</p>
                                        <button type="button" @click="addShift({{ $dept->id }})" class="mt-2 text-xs font-bold text-indigo-600 hover:underline">
                                            + Tambah Shift Sekarang
                                        </button>
                                    </div>

                                    <template x-for="(shift, sIdx) in (deptShifts[{{ $dept->id }}] || [])" :key="shift.id">
                                        <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-card relative"
                                             :style="'border-left: 4px solid ' + (shift.color_code || '#4F46E5')">
                                            
                                            {{-- Baris Atas: Nama, Jam, Kuota, Hapus --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                <div class="flex items-center gap-2 flex-1">
                                                    <input type="text" x-model="shift.title" required
                                                           class="text-xs font-bold text-slate-900 bg-slate-50 border border-slate-200/80 px-2.5 py-1.5 rounded-xl w-36 sm:w-44 focus:ring-1 focus:ring-indigo-600"
                                                           placeholder="Nama Shift">

                                                    <div class="flex items-center gap-1 text-xs bg-slate-50 border border-slate-200/80 rounded-xl px-2 py-1">
                                                        <input type="time" x-model="shift.start_time" required
                                                               class="border-0 p-0 text-xs font-bold text-slate-900 bg-transparent focus:ring-0 w-16">
                                                        <span class="text-slate-400">–</span>
                                                        <input type="time" x-model="shift.end_time" required
                                                               class="border-0 p-0 text-xs font-bold text-slate-900 bg-transparent focus:ring-0 w-16">
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                                    {{-- Kuota Stepper --}}
                                                    <div class="flex items-center gap-1 bg-indigo-50 px-2.5 py-1 rounded-xl border border-slate-200/80">
                                                        <span class="text-[11px] font-semibold text-slate-500">Kuota:</span>
                                                        <button type="button" @click="if (shift.quota > 1) shift.quota--"
                                                                class="px-1.5 text-xs font-bold text-slate-500 hover:bg-slate-200 rounded">-</button>
                                                        <input type="number" x-model.number="shift.quota" min="1" max="50" required
                                                               class="w-8 text-center text-xs font-bold bg-white border border-slate-200/80 rounded px-0.5 py-0.5">
                                                        <button type="button" @click="shift.quota++"
                                                                class="px-1.5 text-xs font-bold text-slate-500 hover:bg-slate-200 rounded">+</button>
                                                        <span class="text-xs font-bold text-slate-900">Org</span>
                                                    </div>

                                                    <button type="button" @click="removeShift({{ $dept->id }}, sIdx)"
                                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition"
                                                            title="Hapus Shift">
                                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Baris Bawah: Hari Aktif --}}
                                            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <span class="text-[10px] text-slate-500 mr-1">Hari:</span>
                                                    @foreach ([1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'] as $isoDay => $lbl)
                                                        <button type="button" 
                                                                @click="toggleDay(shift, {{ $isoDay }})"
                                                                class="px-2 py-0.5 rounded-lg border text-[10px] font-semibold transition"
                                                                :class="shift.days.includes({{ $isoDay }}) ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-slate-50 border-slate-200 text-slate-600'">
                                                            {{ $lbl }}
                                                        </button>
                                                    @endforeach
                                                </div>

                                                <div class="flex items-center gap-1 text-[10px]">
                                                    <button type="button" @click="selectAllDays(shift)" class="text-indigo-600 font-bold hover:underline">Semua</button>
                                                    <span class="text-slate-300">•</span>
                                                    <button type="button" @click="selectWeekdays(shift)" class="text-slate-500 hover:underline">Sen-Jum</button>
                                                </div>
                                            </div>

                                            {{-- Hidden Inputs Dikirim Saat Mode Kustom --}}
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][template_id]'" :value="shift.template_id || ''">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][title]'" :value="shift.title">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][start_time]'" :value="shift.start_time">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][end_time]'" :value="shift.end_time">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][quota]'" :value="shift.quota">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][color_code]'" :value="shift.color_code || '#4F46E5'">
                                            <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][enabled]'" :value="shift.enabled ? 1 : 0">
                                            <template x-for="d in shift.days" :key="d">
                                                <input type="hidden" :disabled="buildMode === 'quick'" :name="'requirements[{{ $dept->id }}][' + sIdx + '][days][]'" :value="d">
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sticky Footer Modal --}}
                    <div class="px-5 py-3.5 border-t border-slate-200/80 bg-white flex items-center justify-between gap-3 shrink-0">
                        <span class="text-xs text-slate-500 hidden sm:flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="h-3.5 w-3.5 text-emerald-600"></i>
                            <span>Batas 40 jam &amp; jeda istirahat 11 jam terjaga otomatis</span>
                        </span>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <button type="button" @click="!isSubmitting && (showBuildModal = false)"
                                    class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200/80 text-slate-500 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="isSubmitting"
                                    class="px-5 py-2 text-xs font-extrabold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition flex items-center gap-1.5">
                                <i data-lucide="play" class="h-4 w-4"></i>
                                <span>Jalankan Auto-Scheduling</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL KONFIRMASI TERBITKAN JADWAL                        --}}
        {{-- ======================================================== --}}
        <div x-show="showPublishModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto p-4 bg-slate-900/60 backdrop-blur-xs flex justify-center items-center"
             style="display: none;">
            <div @click.away="showPublishModal = false" class="bg-white rounded-xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl shrink-0">
                        <i data-lucide="send" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Terbitkan Jadwal Resmi</h3>
                        <p class="text-xs text-slate-500">Publikasikan jadwal kerja ke seluruh staf</p>
                    </div>
                </div>

                <p class="my-4 text-xs text-slate-500 leading-relaxed">
                    Setelah diterbitkan, shift akan berstatus <strong>Published</strong> dan langsung tampil pada kalender kerja karyawan.
                </p>

                <form method="POST" action="{{ route('manager.shifts.publish') }}">
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $departmentId }}">
                    <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">

                    @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
                        <div class="mb-4 bg-indigo-50 p-3 rounded-xl border border-slate-200/80 space-y-2">
                            <label class="block text-xs font-bold text-slate-900">Pilih Cakupan:</label>
                            <label class="flex items-center gap-2 text-xs text-slate-900 cursor-pointer">
                                <input type="radio" name="publish_scope" value="current" x-model="publishScope" class="text-indigo-600 focus:ring-indigo-600">
                                <span>Hanya Departemen <strong>{{ $currentDepartment->name }}</strong> ({{ $draftShiftsCount }} Draf)</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-900 cursor-pointer">
                                <input type="radio" name="publish_scope" value="all" x-model="publishScope" class="text-indigo-600 focus:ring-indigo-600">
                                <span>Seluruh Departemen (<strong>{{ $totalCompanyDraftShifts }} Draf</strong>)</span>
                            </label>
                        </div>
                    @endif

                    @php
                        $existingPublished = $isGlobalManager ? $totalCompanyPublishedShifts : $publishedShiftsCount;
                    @endphp
                    @if ($existingPublished > 0)
                        <div class="mb-4 bg-amber-50 p-3 rounded-xl border border-amber-200">
                            <p class="text-xs font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                                <i data-lucide="alert-triangle" class="h-4 w-4 text-amber-600"></i>
                                <span>Terdapat {{ $existingPublished }} shift terbit lama</span>
                            </p>
                            <label class="flex items-center gap-2 text-xs text-amber-950 cursor-pointer">
                                <input type="radio" name="overwrite_published" value="1" x-model="overwriteMode" class="text-emerald-600 focus:ring-emerald-500">
                                <span>Timpa &amp; Gantikan Jadwal Lama (Direkomendasikan)</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs text-amber-950 cursor-pointer mt-1">
                                <input type="radio" name="overwrite_published" value="0" x-model="overwriteMode" class="text-amber-600 focus:ring-amber-500">
                                <span>Tetap Gabungkan (Shift Tambahan)</span>
                            </label>
                        </div>
                    @else
                        <input type="hidden" name="overwrite_published" value="0">
                    @endif

                    <div class="flex items-center justify-end gap-2 mt-5">
                        <button type="button" @click="showPublishModal = false"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200/80 text-slate-500 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center gap-1.5">
                            <i data-lucide="check" class="h-4 w-4"></i>
                            <span>Ya, Terbitkan Jadwal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL KONFIRMASI HAPUS DRAF (HANYA INI ATAU KESELURUHAN) --}}
        {{-- ======================================================== --}}
        <div x-show="showClearModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto p-4 bg-slate-900/60 backdrop-blur-xs flex justify-center items-center"
             style="display: none;">
            <div @click.away="showClearModal = false" class="bg-white rounded-xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-red-100 text-red-600 rounded-xl shrink-0">
                        <i data-lucide="trash-2" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Hapus Draf Jadwal</h3>
                        <p class="text-xs text-slate-500">Hapus susunan draf yang belum terbit</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('manager.shifts.clear-drafts') }}" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">

                    @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
                        <div class="mb-4 bg-red-50/70 p-3.5 rounded-xl border border-red-200/80 space-y-2.5">
                            <label class="block text-xs font-bold text-red-950">Pilih Cakupan Penghapusan Draf:</label>
                            
                            {{-- Pilihan 1: Hanya Departemen Ini Saja --}}
                            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="clearScope === 'current' ? 'bg-white border-red-400 shadow-card' : 'bg-red-50/40 border-transparent hover:bg-white'">
                                <input type="radio" name="clear_scope" value="current" x-model="clearScope" 
                                       class="mt-0.5 text-red-600 focus:ring-red-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900">Hanya Departemen {{ $currentDepartment->name }} ({{ $draftShiftsCount }} Draf)</span>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Hanya membersihkan draf tim ini. Draf di departemen lain tetap aman.</p>
                                </div>
                            </label>

                            {{-- Pilihan 2: Keseluruhan Draf Seluruh Departemen --}}
                            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border cursor-pointer transition"
                                   :class="clearScope === 'all' ? 'bg-white border-red-400 shadow-card' : 'bg-red-50/40 border-transparent hover:bg-white'">
                                <input type="radio" name="clear_scope" value="all" x-model="clearScope" 
                                       class="mt-0.5 text-red-600 focus:ring-red-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900">Seluruh Departemen (Total {{ $totalCompanyDraftShifts }} Draf)</span>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Menghapus semua draf di seluruh departemen perusahaan pada rentang tanggal ini.</p>
                                </div>
                            </label>
                        </div>
                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                    @else
                        <input type="hidden" name="clear_scope" value="current">
                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                        <p class="mb-4 text-xs text-slate-500 leading-relaxed">
                            Tindakan ini akan menghapus <strong>{{ $draftShiftsCount }} shift draf</strong> pada departemen <strong>{{ $currentDepartment->name }}</strong> agar Anda dapat menyusun ulang jadwal dari awal.
                        </p>
                    @endif

                    <div class="flex items-center justify-end gap-2 mt-5">
                        <button type="button" @click="showClearModal = false"
                                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200/80 text-slate-500 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold rounded-xl bg-red-600 hover:bg-red-700 text-white shadow-sm transition">
                            @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
                                <span x-text="clearScope === 'all' ? 'Ya, Hapus Semua Draf ({{ $totalCompanyDraftShifts }})' : 'Ya, Hapus Draf {{ $currentDepartment->name }} ({{ $draftShiftsCount }})'"></span>
                            @else
                                <span>Ya, Hapus Draf</span>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- KALENDER MATRIKS ROSTER MINGGUAN                         --}}
    {{-- ======================================================== --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden"
         x-data="shiftEditCalendar()">
        
        {{-- Header Bar Kalender --}}
        <div class="p-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2.5">
                <div class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                </div>
                <div>
                    <h2 class="text-xs md:text-sm font-bold text-slate-900">
                        Matriks Roster Mingguan: {{ $currentDepartment?->name ?? 'Departemen Tim' }}
                    </h2>
                    <span class="text-[11px] text-slate-500">
                        {{ $startDate->translatedFormat('d M') }} s.d. {{ $endDate->translatedFormat('d M Y') }} ({{ count($days) }} Hari)
                    </span>
                </div>
            </div>

            {{-- Legend Indikator & Hint --}}
            <div class="flex items-center flex-wrap gap-3 text-[11px] font-semibold text-slate-500">
                <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1">
                    <i data-lucide="mouse-pointer-click" class="h-3 w-3"></i> Klik shift untuk edit staf
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Draf
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Terbit
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span> Kurang Staf
                </span>
            </div>
        </div>

        {{-- Grid Kolom Hari --}}
        <div class="overflow-x-auto">
            <div class="inline-flex min-w-full divide-x divide-slate-200/80">
                @foreach ($days as $day)
                    @php
                        $dayShifts = $shiftsByDate[$day['date_str']] ?? collect();
                    @endphp
                    <div class="w-64 shrink-0 flex flex-col min-h-[550px] {{ $day['is_today'] ? 'bg-indigo-50/30' : 'bg-white' }}">
                        {{-- Header Hari --}}
                        <div class="p-3 text-center border-b border-slate-200/80 {{ $day['is_today'] ? 'bg-indigo-50' : 'bg-slate-50/40' }}">
                            <p class="text-xs font-bold uppercase tracking-wider {{ $day['is_today'] ? 'text-indigo-600' : 'text-slate-500' }}">
                                {{ $day['day_name'] }}
                            </p>
                            <p class="text-sm font-extrabold {{ $day['is_today'] ? 'text-indigo-600' : 'text-slate-900' }} mt-0.5">
                                {{ $day['formatted'] }}
                            </p>
                            @if ($day['is_today'])
                                <span class="inline-block mt-1 text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-600 text-white">
                                    Hari Ini
                                </span>
                            @endif
                        </div>

                        {{-- Daftar Kartu Shift Pada Hari Ini --}}
                        <div class="p-2.5 flex-1 space-y-2.5">
                            @forelse ($dayShifts as $shift)
                                @php
                                    $templateColor = $shift->template->color_code ?? '#4F46E5';
                                    $assignedCount = $shift->shiftAssignments->count();
                                    $isFull = $assignedCount >= $shift->quota;
                                    $isDraft = $shift->status === 'draft';
                                    $shiftPayload = [
                                        'id' => $shift->id,
                                        'title' => $shift->title,
                                        'date_formatted' => $shift->start_time->translatedFormat('l, d F Y'),
                                        'start_time' => $shift->start_time->format('H:i'),
                                        'end_time' => $shift->end_time->format('H:i'),
                                        'quota' => $shift->quota,
                                        'status' => $shift->status,
                                        'assigned_user_ids' => $shift->shiftAssignments->pluck('user_id')->values()->toArray(),
                                        'update_url' => route('manager.shifts.update', $shift->id),
                                        'delete_url' => route('manager.shifts.destroy', $shift->id),
                                    ];
                                @endphp
                                <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-card hover:shadow-md hover:border-indigo-400 transition duration-150 relative overflow-hidden group cursor-pointer"
                                     style="border-left: 4px solid {{ $templateColor }};"
                                     @click="openEditModal({{ Js::from($shiftPayload) }})">
                                    
                                    {{-- Baris Judul & Status --}}
                                    <div class="flex items-start justify-between gap-1 mb-1.5">
                                        <div class="truncate">
                                            <p class="text-xs font-bold text-slate-900 truncate group-hover:text-indigo-600 transition">{{ $shift->title }}</p>
                                            <span class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                <i data-lucide="clock" class="h-3 w-3"></i>
                                                {{ $shift->start_time->format('H:i') }} – {{ $shift->end_time->format('H:i') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-md {{ $isDraft ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $shift->status }}
                                            </span>
                                            <button type="button" class="h-5 w-5 rounded-md flex items-center justify-center text-slate-500 hover:text-indigo-600">
                                                <i data-lucide="edit-3" class="h-3 w-3"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Status Kuota --}}
                                    <div class="flex items-center justify-between text-[10px] py-1 px-1.5 rounded-lg mb-2 {{ $isFull ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
                                        <span class="font-bold flex items-center gap-1">
                                            <i data-lucide="{{ $isFull ? 'check-circle' : 'alert-circle' }}" class="h-3 w-3"></i>
                                            {{ $isFull ? 'Kuota Lengkap' : 'Kurang ' . ($shift->quota - $assignedCount) . ' Staf' }}
                                        </span>
                                        <span class="font-extrabold">{{ $assignedCount }}/{{ $shift->quota }}</span>
                                    </div>

                                    {{-- Daftar Staf yang Ditugaskan --}}
                                    <div class="space-y-1.5">
                                        @forelse ($shift->shiftAssignments as $assignment)
                                            @php
                                                $staf = $assignment->user;
                                                $initials = collect(explode(' ', $staf->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                                                $typeBadgeClasses = match ($staf->employment_type) {
                                                    'full_time' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                    'part_time' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                    default => 'bg-purple-50 text-purple-700 border-purple-200',
                                                };
                                                $typeLabel = match ($staf->employment_type) {
                                                    'full_time' => 'FT',
                                                    'part_time' => 'PT',
                                                    default => 'FL',
                                                };
                                            @endphp
                                            <div class="flex items-center justify-between gap-1.5 bg-indigo-50/70 border border-slate-200/80 rounded-xl px-2 py-1 text-[11px]">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <div class="h-5 w-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[9px] font-bold shrink-0">
                                                        {{ $initials }}
                                                    </div>
                                                    <span class="font-semibold text-slate-900 truncate">{{ $staf->name }}</span>
                                                </div>
                                                <span class="text-[9px] font-bold uppercase border px-1.5 py-0.5 rounded-md {{ $typeBadgeClasses }}">
                                                    {{ $typeLabel }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-[10px] text-slate-400 italic text-center py-1">
                                                Belum ada staf ditugaskan
                                            </div>
                                        @endforelse

                                        {{-- Slot Kosong --}}
                                        @if ($assignedCount < $shift->quota)
                                            @for ($i = 0; $i < ($shift->quota - $assignedCount); $i++)
                                                <div class="border border-dashed border-red-200 bg-red-50/40 rounded-xl p-1.5 text-[10px] text-red-600 flex items-center justify-center gap-1 font-semibold">
                                                    <i data-lucide="user-x" class="h-3 w-3"></i>
                                                    <span>1 Slot Kosong</span>
                                                </div>
                                            @endfor
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="h-48 flex flex-col items-center justify-center text-slate-300">
                                    <i data-lucide="calendar-x" class="h-8 w-8 stroke-1"></i>
                                    <span class="text-[11px] text-slate-500 mt-1.5">Tidak ada shift</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL EDIT SHIFT & PENUGASAN PERSONEL                    --}}
        {{-- ======================================================== --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="closeEditModal()">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeEditModal()"></div>

            <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
                <div class="relative w-full max-w-xl max-h-[92vh] flex flex-col bg-white rounded-xl shadow-xl border border-slate-200/80 overflow-hidden z-10"
                     @click.stop>
                    
                    {{-- Modal Header --}}
                    <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/70 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Edit Shift Kerja</h3>
                                <p class="text-xs text-slate-500" x-text="editingShift.date_formatted"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeEditModal()"
                                class="h-8 w-8 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>

                    {{-- Form Body --}}
                    <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-4">
                        <form method="POST" :action="editingShift.update_url" id="edit-shift-form">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-900 mb-1.5">
                                        Nama Shift <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="title" x-model="editingShift.title" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-900 mb-1.5">Status</label>
                                    <select name="status" x-model="editingShift.status"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                                        <option value="draft">Draf (Draft)</option>
                                        <option value="published">Terbit (Published)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-900 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                                    <input type="time" name="start_time" x-model="editingShift.start_time" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-900 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                                    <input type="time" name="end_time" x-model="editingShift.end_time" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-900 mb-1.5">Kuota Staf <span class="text-red-500">*</span></label>
                                    <div class="flex items-center border border-slate-200/80 rounded-xl bg-slate-50 overflow-hidden">
                                        <button type="button" @click="if (editingShift.quota > 1) editingShift.quota--"
                                                class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:bg-slate-200 transition">-</button>
                                        <input type="number" name="quota" x-model.number="editingShift.quota" min="1" max="50" required
                                               class="w-full text-center text-xs font-bold text-slate-900 bg-transparent focus:outline-none py-2">
                                        <button type="button" @click="editingShift.quota++"
                                                class="px-2.5 py-2 text-xs font-bold text-slate-500 hover:bg-slate-200 transition">+</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Penugasan Staf --}}
                            <div class="border border-slate-200/80 rounded-xl p-4 bg-slate-50/50">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                            <i data-lucide="users" class="h-3.5 w-3.5 text-indigo-600"></i>
                                            Personel Ditugaskan
                                        </label>
                                        <p class="text-[11px] text-slate-500">Centang staf yang dialokasikan ke shift ini.</p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                          :class="editingShift.assigned_user_ids.length >= editingShift.quota ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                          x-text="editingShift.assigned_user_ids.length + ' / ' + editingShift.quota + ' Kuota Terisi'">
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mb-2.5">
                                    <div class="relative flex-1">
                                        <i data-lucide="search" class="h-3.5 w-3.5 text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                                        <input type="text" x-model="searchStaff" placeholder="Cari nama karyawan..."
                                               class="w-full text-xs pl-8 pr-3 py-1.5 bg-white border border-slate-200/80 rounded-xl text-slate-900 focus:outline-none focus:ring-1 focus:ring-indigo-600">
                                    </div>
                                    <button type="button" @click="autoFillQuota()"
                                            class="text-[10px] font-bold px-2.5 py-1.5 rounded-xl border border-slate-200/80 bg-white hover:bg-indigo-50 text-indigo-600 transition shrink-0">
                                        Pilih Sesuai Kuota
                                    </button>
                                    <button type="button" @click="clearStaff()"
                                            class="text-[10px] font-bold px-2.5 py-1.5 rounded-xl border border-slate-200/80 bg-white hover:bg-red-50 text-red-600 transition shrink-0">
                                        Reset
                                    </button>
                                </div>

                                <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 border border-slate-200/80 rounded-xl p-2 bg-white">
                                    <template x-for="staff in filteredStaff()" :key="staff.id">
                                        <div @click="toggleStaff(staff.id)"
                                             class="flex items-center justify-between p-2 rounded-xl border transition cursor-pointer"
                                             :class="isStaffAssigned(staff.id) ? 'border-indigo-600 bg-indigo-50/70' : 'border-slate-100 hover:border-slate-200/80 hover:bg-slate-50'">
                                            <div class="flex items-center gap-2.5 truncate">
                                                <input type="checkbox" name="assigned_user_ids[]" :value="staff.id"
                                                       :checked="isStaffAssigned(staff.id)"
                                                       @click.stop
                                                       @change="toggleStaff(staff.id)"
                                                       class="rounded border-slate-200/80 text-indigo-600 focus:ring-indigo-600 h-3.5 w-3.5">
                                                <div class="h-6 w-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0"
                                                     x-text="staff.initials">
                                                </div>
                                                <span class="text-xs font-semibold text-slate-900 truncate" x-text="staff.name"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="text-[9px] font-bold uppercase border px-1.5 py-0.5 rounded-md"
                                                      :class="staff.type_class"
                                                      :title="staff.type_title"
                                                      x-text="staff.type_label">
                                                </span>
                                                <span x-show="isStaffAssigned(staff.id)" class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                                                    Ditugaskan
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </form>

                        <form id="delete-shift-form" method="POST" :action="editingShift.delete_url" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="px-5 py-3.5 border-t border-slate-200/80 flex items-center justify-between gap-3 bg-slate-50/70 shrink-0">
                        <button type="button" @click="confirmDelete()"
                                class="px-3.5 py-2 text-xs font-bold rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            <span>Hapus Shift</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeEditModal()"
                                    class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200/80 text-slate-500 hover:bg-white transition">
                                Batal
                            </button>
                            <button type="submit" form="edit-shift-form"
                                    class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
                                <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Handler untuk Edit Shift Modal --}}
    <script>
        function shiftEditCalendar() {
            return {
                showEditModal: false,
                searchStaff: '',
                editingShift: {
                    id: null,
                    title: '',
                    date_formatted: '',
                    start_time: '08:00',
                    end_time: '16:00',
                    quota: 1,
                    status: 'draft',
                    assigned_user_ids: [],
                    update_url: '',
                    delete_url: '',
                },
                staffList: {{ Js::from($activeStaff->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'type' => $s->employment_type,
                    'type_label' => match($s->employment_type) { 'full_time' => 'FT', 'part_time' => 'PT', default => 'FL' },
                    'type_title' => match($s->employment_type) { 'full_time' => 'Staf Penuh Waktu (Full-Time)', 'part_time' => 'Staf Paruh Waktu (Part-Time)', default => 'Pekerja Lepas (Freelance)' },
                    'type_class' => match($s->employment_type) {
                        'full_time' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'part_time' => 'bg-sky-50 text-sky-700 border-sky-200',
                        default => 'bg-purple-50 text-purple-700 border-purple-200',
                    },
                    'initials' => collect(explode(' ', $s->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
                ])) }},
                openEditModal(shift) {
                    this.editingShift = JSON.parse(JSON.stringify(shift));
                    this.searchStaff = '';
                    this.showEditModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },
                closeEditModal() {
                    this.showEditModal = false;
                },
                toggleStaff(id) {
                    const idx = this.editingShift.assigned_user_ids.indexOf(id);
                    if (idx > -1) {
                        this.editingShift.assigned_user_ids.splice(idx, 1);
                    } else {
                        this.editingShift.assigned_user_ids.push(id);
                    }
                },
                isStaffAssigned(id) {
                    return this.editingShift.assigned_user_ids.includes(id);
                },
                autoFillQuota() {
                    const quota = parseInt(this.editingShift.quota) || 1;
                    const candidates = this.staffList.slice(0, quota).map(s => s.id);
                    this.editingShift.assigned_user_ids = candidates;
                },
                clearStaff() {
                    this.editingShift.assigned_user_ids = [];
                },
                filteredStaff() {
                    if (!this.searchStaff || !this.searchStaff.trim()) {
                        return this.staffList;
                    }
                    const q = this.searchStaff.toLowerCase().trim();
                    return this.staffList.filter(s => s.name.toLowerCase().includes(q));
                },
                confirmDelete() {
                    if (confirm('Yakin ingin menghapus shift "' + this.editingShift.title + '"? Seluruh penugasan karyawan pada shift ini akan dibatalkan.')) {
                        document.getElementById('delete-shift-form').submit();
                    }
                }
            };
        }
    </script>
</x-app-layout>
