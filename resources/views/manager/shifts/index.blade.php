<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    @if ($isGlobalManager)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200 shadow-2xs">
                            <i data-lucide="globe" class="h-3 w-3"></i>
                            Global Manager (Multi-Departemen)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20 shadow-2xs">
                            <i data-lucide="shield" class="h-3 w-3"></i>
                            Manajer Departemen
                        </span>
                    @endif
                    <span class="text-xs text-[#7C7896]">PP 35/2021 Compliant Engine</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight mt-1.5">
                    Jadwal Kerja Tim (Roster)
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                    Susun kebutuhan kuota orang per shift dan jalankan penjadwalan cerdas otomatis tanpa konflik.
                </p>
            </div>

            {{-- Departemen Switcher / Badge --}}
            <div class="flex items-center gap-3">
                @if ($isGlobalManager)
                    {{-- Global Manager: Dropdown Pemilih Departemen --}}
                    <form method="GET" action="{{ route('manager.shifts.index') }}" class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <div class="flex items-center gap-2 px-2 text-xs font-semibold text-[#7C7896]">
                            <i data-lucide="building-2" class="h-4 w-4 text-[#6C5CE7]"></i>
                            <span>Departemen:</span>
                        </div>
                        <select name="department_id" onchange="this.form.submit()" 
                                class="text-xs font-bold bg-[#F5F3FF] border border-[#E2E0F7] rounded-xl px-3 py-1.5 text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7]">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $dept->id == $departmentId ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->draft_count ? $dept->draft_count . ' Draf, ' : '' }}{{ $dept->active_staff_count }} Staf)
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    {{-- Regular Manager: Terkunci pada Departemennya Sendiri --}}
                    <div class="bg-white border border-[#E2E0F7] rounded-2xl px-4 py-2 flex items-center gap-3 shadow-2xs">
                        <div class="h-8 w-8 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#7C7896]">Departemen Anda</p>
                            <p class="text-xs font-bold text-[#2D2A3E]">{{ $currentDepartment->name ?? 'Departemen Tim' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Notifikasi & Flash Messages --}}
    @if (session('success'))
        <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
            <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="font-bold">Berhasil!</p>
                <p class="mt-0.5 text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
            <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="font-bold">Perhatian</p>
                <p class="mt-0.5 text-amber-700">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
            <i data-lucide="info" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5"></i>
            <div class="flex-1 text-xs md:text-sm">
                <p class="mt-0.5 text-blue-700">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    {{-- Peringatan Hasil Auto-Schedule --}}
    @if (session('soft_warnings') && count(session('soft_warnings')) > 0)
        <div class="mb-5 bg-amber-50/90 border-l-4 border-amber-500 rounded-r-2xl p-4 shadow-2xs">
            <div class="flex items-center gap-2 text-amber-900 font-bold text-xs md:text-sm">
                <i data-lucide="alert-circle" class="h-4 w-4 text-amber-600"></i>
                <span>Catatan Jeda Istirahat (Soft Warning - Diizinkan dengan Review Manajer):</span>
            </div>
            <ul class="mt-2 space-y-1 text-xs text-amber-900 list-disc list-inside">
                @foreach (session('soft_warnings') as $sw)
                    <li>
                        <strong>{{ $sw['user_name'] }}</strong> pada shift <em>{{ $sw['shift_title'] }}</em> ({{ $sw['date'] }}): 
                        {{ $sw['reason'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('unfilled_slots') && count(session('unfilled_slots')) > 0)
        <div class="mb-5 bg-red-50/90 border-l-4 border-red-500 rounded-r-2xl p-4 shadow-2xs">
            <div class="flex items-center gap-2 text-red-900 font-bold text-xs md:text-sm">
                <i data-lucide="user-x" class="h-4 w-4 text-red-600"></i>
                <span>Terdapat {{ count(session('unfilled_slots')) }} slot shift kekurangan staf (Unfilled Slots):</span>
            </div>
            <p class="text-xs text-red-700 mt-1">
                Kandidat staf yang memenuhi syarat habis karena batas jam kerja atau jadwal halangan. Slot ini ditandai khusus dan dapat disiarkan sebagai <strong>Open Shift</strong> sukarela.
            </p>
        </div>
    @endif

    {{-- STATUS BANNER JADWAL: JELAS & MUDAH DIPAHAMI --}}
    @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
        <div class="mb-5 bg-gradient-to-r from-purple-50 via-indigo-50/50 to-white border border-purple-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="h-5 w-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-purple-950 flex items-center gap-2">
                        <span>Penyusunan Multi-Departemen Siap Ditinjau</span>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-purple-200 text-purple-800">
                            Total {{ $totalCompanyDraftShifts }} Draf di {{ $departments->where('draft_count', '>', 0)->count() }} Departemen
                        </span>
                    </h3>
                    <p class="text-xs text-purple-800/80 mt-0.5">
                        Jadwal seluruh departemen berhasil dibuild. Saat ini kalender menampilkan: <strong>Departemen {{ $currentDepartment->name }}</strong> ({{ $draftShiftsCount }} Draf). Gunakan bilah tab departemen di bawah untuk berpindah tinjauan tim.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="showPublishModal = true; publishScope = 'all'"
                        class="text-xs font-bold px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition flex items-center gap-1.5">
                    <i data-lucide="send" class="h-3.5 w-3.5"></i>
                    <span>Terbitkan Semua ({{ $totalCompanyDraftShifts }})</span>
                </button>
            </div>
        </div>
    @elseif ($draftShiftsCount > 0)
        <div class="mb-6 bg-gradient-to-r from-amber-50 via-amber-50/60 to-white border-2 border-amber-300 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i data-lucide="clipboard-pen" class="h-5 w-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                        <span>Mode Peninjauan Draf (Roster Belum Terbit)</span>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-200 text-amber-800">
                            {{ $draftShiftsCount }} Shift Draf
                        </span>
                    </h3>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Jadwal hasil susunan di bawah berstatus <strong>Draf</strong> dan <strong>belum dapat dilihat oleh karyawan</strong>. Tinjau susunan personel, lalu klik tombol <em>"Terbitkan Jadwal"</em> agar resmi aktif.
                    </p>
                </div>
            </div>
        </div>
    @elseif ($totalShifts > 0)
        <div class="mb-6 bg-gradient-to-r from-emerald-50 via-emerald-50/50 to-white border border-emerald-200 rounded-2xl p-4 flex items-center gap-3 shadow-xs">
            <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-check" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                    <span>Jadwal Resmi Aktif (Terbit)</span>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-800">
                        {{ $publishedShiftsCount }} Shift Published
                    </span>
                </h3>
                <p class="text-xs text-emerald-700 mt-0.5">
                    Seluruh shift pada rentang ini telah terbit dan dapat dilihat oleh karyawan di kalender kerja masing-masing.
                </p>
            </div>
        </div>
    @endif

    {{-- KARTU KPI RINGKASAN --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-[#E2E0F7] shadow-2xs">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#7C7896]">Total Shift</p>
                <span class="p-1.5 bg-[#F5F3FF] text-[#6C5CE7] rounded-lg">
                    <i data-lucide="layers" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <p class="text-xl font-bold text-[#2D2A3E] mt-2">{{ $totalShifts }}</p>
            <div class="flex items-center gap-2 mt-1 text-[11px]">
                <span class="text-amber-600 font-bold">{{ $draftShiftsCount }} Draf</span>
                <span class="text-slate-300">•</span>
                <span class="text-emerald-600 font-bold">{{ $publishedShiftsCount }} Terbit</span>
            </div>
            @if ($isGlobalManager)
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                    <span class="text-[#7C7896] truncate mr-1">Dept: <strong class="text-[#2D2A3E]">{{ $currentDepartment->name }}</strong></span>
                    <span class="text-[#6C5CE7] font-semibold shrink-0">Semua: {{ $totalCompanyShifts }}</span>
                </div>
            @endif
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#E2E0F7] shadow-2xs">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#7C7896]">Jam Terjadwal</p>
                <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <p class="text-xl font-bold text-[#2D2A3E] mt-2">{{ $totalScheduledHours }} <span class="text-xs font-normal text-[#7C7896]">Jam</span></p>
            <p class="text-[11px] text-[#7C7896] mt-1">{{ $totalAssignments }} slot penugasan</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#E2E0F7] shadow-2xs">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#7C7896]">Staf Ditugaskan</p>
                <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i data-lucide="users" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <p class="text-xl font-bold text-[#2D2A3E] mt-2">
                {{ $uniqueStaffCount }} <span class="text-xs font-normal text-[#7C7896]">/ {{ $activeStaff->count() }} Aktif</span>
            </p>
            <p class="text-[11px] text-emerald-600 font-semibold mt-1">Pemerataan Adil (Fairness)</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#E2E0F7] shadow-2xs">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#7C7896]">Slot Kurang Staf</p>
                <span class="p-1.5 {{ $unfilledSlotsCount > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} rounded-lg">
                    <i data-lucide="{{ $unfilledSlotsCount > 0 ? 'user-x' : 'check' }}" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <p class="text-xl font-bold {{ $unfilledSlotsCount > 0 ? 'text-red-600' : 'text-[#2D2A3E]' }} mt-2">
                {{ $unfilledSlotsCount }} <span class="text-xs font-normal text-[#7C7896]">Slot</span>
            </p>
            <p class="text-[11px] {{ $unfilledSlotsCount > 0 ? 'text-red-600 font-bold' : 'text-[#7C7896]' }} mt-1">
                {{ $unfilledSlotsCount > 0 ? 'Perlu Open Shift' : 'Seluruh kuota terisi' }}
            </p>
        </div>
    </div>

    {{-- KONTROL FILTER TANGGAL DINAMIS & TOOLBAR AKSI --}}
    <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 shadow-2xs mb-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4"
         x-data="{ showBuildModal: false, showPublishModal: false, showClearModal: false, publishScope: 'current', clearScope: 'current' }">
        
        {{-- Pemilih Rentang Tanggal Dinamis --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Tombol Navigasi Cepat Mundur --}}
            <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $prevStart, 'end_date' => $prevEnd]) }}"
               class="p-2 rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:text-[#6C5CE7] hover:bg-[#F5F3FF] transition"
               title="Periode Sebelumnya ({{ $daysCount }} Hari)">
                <i data-lucide="chevron-left" class="h-4 w-4"></i>
            </a>

            {{-- Form Input Tanggal Dinamis (Mulai s.d. Selesai) --}}
            <form method="GET" action="{{ route('manager.shifts.index') }}" class="flex items-center gap-2 bg-[#F5F3FF] border border-[#E2E0F7] rounded-xl px-3 py-1.5">
                <input type="hidden" name="department_id" value="{{ $departmentId }}">
                <i data-lucide="calendar" class="h-4 w-4 text-[#6C5CE7] shrink-0"></i>
                
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="text-xs font-bold text-[#2D2A3E] bg-white border border-[#E2E0F7] rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#6C5CE7]">
                
                <span class="text-xs font-semibold text-[#7C7896]">s.d.</span>
                
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="text-xs font-bold text-[#2D2A3E] bg-white border border-[#E2E0F7] rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#6C5CE7]">
                
                <button type="submit" 
                        class="text-xs font-bold px-2.5 py-1 bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white rounded-lg transition shadow-2xs">
                    Terapkan
                </button>
            </form>

            {{-- Tombol Navigasi Cepat Maju --}}
            <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $nextStart, 'end_date' => $nextEnd]) }}"
               class="p-2 rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:text-[#6C5CE7] hover:bg-[#F5F3FF] transition"
               title="Periode Berikutnya ({{ $daysCount }} Hari)">
                <i data-lucide="chevron-right" class="h-4 w-4"></i>
            </a>

            {{-- Shortcut Preset Rentang Waktu --}}
            <div class="hidden sm:flex items-center gap-1.5 ml-1">
                <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => $thisWeekStart, 'end_date' => $thisWeekEnd]) }}"
                   class="text-[11px] font-bold px-2.5 py-1.5 rounded-xl border border-[#E2E0F7] {{ $startDate->format('Y-m-d') === $thisWeekStart && $endDate->format('Y-m-d') === $thisWeekEnd ? 'bg-[#6C5CE7] text-white' : 'text-[#2D2A3E] hover:bg-[#F5F3FF]' }} transition">
                    Pekan Ini
                </a>
                <a href="{{ route('manager.shifts.index', ['department_id' => $departmentId, 'start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}"
                   class="text-[11px] font-bold px-2.5 py-1.5 rounded-xl border border-[#E2E0F7] text-[#2D2A3E] hover:bg-[#F5F3FF] transition">
                    Bulan Ini
                </a>
            </div>
        </div>

        {{-- Action Buttons: Build Jadwal, Publish, Clear --}}
        <div class="flex items-center flex-wrap gap-2.5">
            @if ($draftShiftsCount > 0 || ($isGlobalManager && $totalCompanyDraftShifts > 0))
                <button type="button" @click="showClearModal = true; clearScope = 'current'"
                        class="text-xs font-semibold px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                    <span>Hapus Draf ({{ $draftShiftsCount ?: $totalCompanyDraftShifts }})</span>
                </button>
            @endif

            {{-- TOMBOL UTAMA: BUILD JADWAL (MENGATUR KEBUTUHAN ORANG TERLEBIH DAHULU) --}}
            <button type="button" @click="showBuildModal = true"
                    class="text-xs font-bold px-4 py-2 rounded-xl bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white shadow-sm transition flex items-center gap-2">
                <i data-lucide="sliders" class="h-3.5 w-3.5"></i>
                <span>Atur Kebutuhan & Build Jadwal</span>
            </button>

            @if ($draftShiftsCount > 0 || ($isGlobalManager && $totalCompanyDraftShifts > 0))
                <button type="button" @click="showPublishModal = true; publishScope = 'current'"
                        class="text-xs font-bold px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center gap-2">
                    <i data-lucide="send" class="h-3.5 w-3.5"></i>
                    <span>Terbitkan Jadwal ({{ $draftShiftsCount ?: $totalCompanyDraftShifts }})</span>
                </button>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL 1: KONFIGURASI KEBUTUHAN JADWAL SEBELUM DI-GENERATE --}}
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
                 class="relative bg-white rounded-2xl sm:rounded-3xl max-w-full sm:max-w-4xl xl:max-w-5xl w-full shadow-2xl border border-[#E2E0F7] h-[92vh] sm:h-[82vh] sm:max-h-[720px] flex flex-col overflow-hidden my-auto"
                 x-data="{ 
                      activeDeptTab: {{ $departmentId ?: ($departments->first()?->id ?? 1) }},
                      showTemplateEditModal: false,
                      editingDeptId: null,
                      editingIndex: null,
                      isSubmitting: false,
                      loadingStep: 1,
                      loadingPercent: 15,
                      loadingSteps: [
                          { title: 'Inisialisasi & Preferensi Staf', desc: 'Menyaring ketersediaan, izin, cuti, dan preferensi waktu' },
                          { title: 'Validasi Regulasi PP 35/2021', desc: 'Memvalidasi batas maksimal 40 jam kerja reguler per minggu' },
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
                      editingTemplate: {
                          id: null,
                          template_id: null,
                          title: '',
                          start_time: '08:00',
                          end_time: '16:00',
                          quota: 1,
                          color_code: '#6C5CE7',
                          days: [1, 2, 3, 4, 5, 6, 7],
                          enabled: true,
                          is_custom: false
                      },
                      colorPalette: ['#6C5CE7', '#00B894', '#0984E3', '#E17055', '#FD79A8', '#F39C12', '#10B981', '#06B6D4'],
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
                                          color_code: @js($tmpl->color_code ?? '#6C5CE7'),
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
                          const palette = ['#6C5CE7', '#00B894', '#0984E3', '#E17055', '#FD79A8', '#F39C12'];
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
                      },
                      openTemplateEditModal(deptId, index) {
                          this.editingDeptId = deptId;
                          this.editingIndex = index;
                          const target = this.deptShifts[deptId][index];
                          this.editingTemplate = JSON.parse(JSON.stringify(target));
                          this.showTemplateEditModal = true;
                          this.$nextTick(() => {
                              if (window.lucide) lucide.createIcons();
                          });
                      },
                      closeTemplateEditModal() {
                          this.showTemplateEditModal = false;
                      },
                      saveTemplateEditModal() {
                          if (this.editingDeptId !== null && this.editingIndex !== null && this.editingTemplate) {
                              this.deptShifts[this.editingDeptId][this.editingIndex] = JSON.parse(JSON.stringify(this.editingTemplate));
                              this.showTemplateEditModal = false;
                              this.$nextTick(() => {
                                  if (window.lucide) lucide.createIcons();
                              });
                          }
                      },
                      toggleTemplateEditDay(day) {
                          const idx = this.editingTemplate.days.indexOf(day);
                          if (idx > -1) {
                              this.editingTemplate.days.splice(idx, 1);
                          } else {
                              this.editingTemplate.days.push(day);
                              this.editingTemplate.days.sort((a, b) => a - b);
                          }
                      },
                      calculateDuration(start, end) {
                          if (!start || !end) return '';
                          const [h1, m1] = start.split(':').map(Number);
                          const [h2, m2] = end.split(':').map(Number);
                          let diff = (h2 * 60 + m2) - (h1 * 60 + m1);
                          if (diff <= 0) diff += 24 * 60;
                          const hours = Math.floor(diff / 60);
                          const mins = diff % 60;
                          return hours + (mins > 0 ? '.' + Math.round((mins/60)*10) : '') + ' Jam';
                      }
                  }">

                    {{-- ======================================================== --}}
                    {{-- OVERLAY ANIMASI LOADING SMART BUILDER --}}
                    {{-- ======================================================== --}}
                    <div x-show="isSubmitting"
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-98"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-98"
                         class="absolute inset-0 z-50 bg-white/95 backdrop-blur-md flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 text-center select-none overflow-y-auto"
                         style="display: none;">
                        
                        {{-- Ambient Gradient Lighting --}}
                        <div class="absolute -top-24 -left-24 w-72 h-72 bg-purple-400/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
                        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none animate-pulse" style="animation-delay: 1s;"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#6C5CE7]/10 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="relative z-10 max-w-lg w-full flex flex-col items-center my-auto">
                            
                            {{-- AI Core Radar / Dual Rotating Rings --}}
                            <div class="relative mb-4 sm:mb-5 flex items-center justify-center">
                                {{-- Ambient Pulsing Ring --}}
                                <div class="absolute w-20 h-20 sm:w-24 sm:h-24 rounded-full border-2 border-[#6C5CE7]/30 animate-ping opacity-60"></div>
                                
                                {{-- Outer Rotating Dashed Ring --}}
                                <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full border-2 border-dashed border-[#6C5CE7] animate-spin" style="animation-duration: 6s;"></div>
                                
                                {{-- Inner Counter-Rotating Ring --}}
                                <div class="absolute w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-dashed border-[#00B894] animate-spin" style="animation-duration: 4s; animation-direction: reverse;"></div>
                                
                                {{-- Glowing Core Badge --}}
                                <div class="absolute w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-[#6C5CE7] via-[#8070F0] to-[#00B894] shadow-lg shadow-[#6C5CE7]/40 flex items-center justify-center text-white">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Title & Status --}}
                            <div class="space-y-1 mb-4 sm:mb-5">
                                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] text-[11px] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#6C5CE7] animate-ping"></span>
                                    <span>Smart Scheduling Engine Aktif</span>
                                </div>
                                <h3 class="text-base sm:text-lg md:text-xl font-extrabold text-[#2D2A3E] tracking-tight">
                                    Menyusun Penugasan Roster Otomatis
                                </h3>
                                <p class="text-[11px] sm:text-xs text-[#7C7896] max-w-sm sm:max-w-md mx-auto">
                                    Algoritma heuristik sedang menganalisis kepatuhan kerja, rotasi shift, dan pemerataan beban staf...
                                </p>
                            </div>

                            {{-- Dynamic Progress Bar --}}
                            <div class="w-full bg-slate-100 rounded-full h-2.5 mb-1.5 overflow-hidden border border-[#E2E0F7] shadow-inner relative">
                                <div class="h-full bg-gradient-to-r from-[#6C5CE7] via-[#8070F0] to-[#00B894] rounded-full transition-all duration-500 ease-out relative overflow-hidden"
                                     :style="'width: ' + loadingPercent + '%'">
                                    {{-- Shimmer effect bar --}}
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full animate-shimmer"></div>
                                </div>
                            </div>

                            {{-- Progress Percentage & Step Indicator --}}
                            <div class="w-full flex items-center justify-between text-[11px] font-bold text-[#7C7896] mb-3 sm:mb-4">
                                <span class="text-[#6C5CE7]" x-text="'Tahap ' + loadingStep + ' dari 5'"></span>
                                <span class="text-[#2D2A3E]" x-text="loadingPercent + '% Selesai'"></span>
                            </div>

                            {{-- Step Checklist Cards --}}
                            <div class="w-full space-y-1.5 sm:space-y-2 text-left bg-[#F8F7FF] border border-[#E2E0F7] rounded-2xl p-3 sm:p-3.5 shadow-2xs">
                                <template x-for="(step, sIndex) in loadingSteps" :key="sIndex">
                                    <div class="flex items-center gap-2.5 sm:gap-3 transition-all duration-300 py-0.5"
                                         :class="(sIndex + 1) === loadingStep ? 'opacity-100 font-bold scale-[1.01]' : ((sIndex + 1) < loadingStep ? 'opacity-90' : 'opacity-40')">
                                        
                                        {{-- Step State Badge --}}
                                        <div class="h-5 w-5 sm:h-6 sm:w-6 rounded-lg flex items-center justify-center shrink-0 transition-all text-xs"
                                             :class="(sIndex + 1) < loadingStep ? 'bg-emerald-500 text-white shadow-2xs' : ((sIndex + 1) === loadingStep ? 'bg-[#6C5CE7] text-white shadow-xs' : 'bg-slate-200 text-slate-400')">
                                            <template x-if="(sIndex + 1) < loadingStep">
                                                <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                            <template x-if="(sIndex + 1) === loadingStep">
                                                <svg class="animate-spin h-3 w-3 sm:h-3.5 sm:w-3.5" viewBox="0 0 24 24" fill="none">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="(sIndex + 1) > loadingStep">
                                                <span class="text-[10px] font-bold" x-text="sIndex + 1"></span>
                                            </template>
                                        </div>

                                        {{-- Step Description --}}
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] sm:text-xs truncate"
                                               :class="(sIndex + 1) === loadingStep ? 'text-[#6C5CE7] font-extrabold' : ((sIndex + 1) < loadingStep ? 'text-[#2D2A3E] font-semibold' : 'text-[#7C7896]')">
                                                <span x-text="step.title"></span>
                                            </p>
                                            <p class="text-[9px] sm:text-[10px] text-[#7C7896] truncate" x-text="step.desc"></p>
                                        </div>

                                        {{-- Step Status Pill --}}
                                        <template x-if="(sIndex + 1) < loadingStep">
                                            <span class="text-[9px] sm:text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0 border border-emerald-100">
                                                Selesai
                                            </span>
                                        </template>
                                        <template x-if="(sIndex + 1) === loadingStep">
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#6C5CE7] bg-[#6C5CE7]/10 px-2 py-0.5 rounded-md shrink-0 border border-[#6C5CE7]/20 animate-pulse">
                                                Memproses...
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer Note --}}
                            <div class="mt-3 sm:mt-4 flex items-center justify-center gap-1.5 text-[10px] sm:text-[11px] text-[#7C7896]">
                                <svg class="h-3.5 w-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Kepatuhan PP 35/2021 & istirahat 11 jam divalidasi otomatis • Harap tunggu</span>
                            </div>

                        </div>
                    </div>
                     
                    {{-- Header Modal (Sticky Top) --}}
                    <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-[#E2E0F7] shrink-0 bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <div class="p-2 sm:p-2.5 bg-[#6C5CE7]/10 text-[#6C5CE7] rounded-xl sm:rounded-2xl shrink-0">
                                <i data-lucide="sliders" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-base font-bold text-[#2D2A3E] truncate">
                                    @if ($isGlobalManager)
                                        Konfigurasi Kebutuhan Jadwal (Global Multi-Dept)
                                    @else
                                        Konfigurasi Kebutuhan Jadwal ({{ $currentDepartment?->name ?? 'Departemen Tim' }})
                                    @endif
                                </h3>
                                <p class="text-[10px] sm:text-xs text-[#7C7896] line-clamp-1 sm:line-clamp-none">
                                    Tentukan kuota personel per shift dan hari kerja sebelum algoritma menyusun penugasan.
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="!isSubmitting && (showBuildModal = false)" :disabled="isSubmitting"
                                class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition shrink-0 ml-2 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    {{-- Form Konfigurasi --}}
                    <form method="POST" action="{{ route('manager.shifts.generate') }}" @submit="if ($el.checkValidity()) startLoadingAnimation()" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        @csrf
                        <input type="hidden" name="view_department_id" value="{{ $departmentId }}">

                        {{-- Subheader: Rentang Tanggal (Kompak & Selalu Terlihat) --}}
                        <div class="px-4 py-2 sm:px-6 sm:py-2.5 bg-[#F5F3FF] border-b border-[#E2E0F7] shrink-0 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[11px] sm:text-xs font-bold text-[#2D2A3E] flex items-center gap-1.5 shrink-0">
                                    <i data-lucide="calendar-range" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-[#6C5CE7]"></i>
                                    <span>Rentang Roster:</span>
                                </span>
                                <div class="flex items-center gap-1.5 sm:gap-2 bg-white border border-[#E2E0F7] rounded-xl px-2 py-1 sm:px-2.5 shadow-2xs">
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" required
                                           class="text-[11px] sm:text-xs font-bold text-[#2D2A3E] border-0 p-0 focus:ring-0 w-28 sm:w-32">
                                    <span class="text-[11px] sm:text-xs font-semibold text-[#7C7896]">s.d.</span>
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" required
                                           class="text-[11px] sm:text-xs font-bold text-[#2D2A3E] border-0 p-0 focus:ring-0 w-28 sm:w-32">
                                </div>
                            </div>
                            
                            @if ($isGlobalManager)
                                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-semibold text-[#6C5CE7] bg-white border border-[#E2E0F7] px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-xl shadow-2xs">
                                    <i data-lucide="layers" class="h-3 w-3 sm:h-3.5 sm:w-3.5"></i>
                                    <span>Global Multi-Dept ({{ $departments->count() }})</span>
                                </div>
                            @else
                                <input type="hidden" name="department_id" value="{{ $departmentId }}">
                                <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-semibold text-[#6C5CE7]">
                                    <i data-lucide="building-2" class="h-3.5 w-3.5"></i>
                                    <span>{{ $currentDepartment?->name ?? 'Departemen Tim' }}</span>
                                </div>
                            @endif
                        </div>

                        @php
                            $departmentsToConfigure = $isGlobalManager ? $departments : ($currentDepartment ? collect([$currentDepartment]) : collect());
                        @endphp

                        @if ($isGlobalManager && $departmentsToConfigure->count() > 1)
                            {{-- TAB SELECTOR DEPARTEMEN INTERAKTIF (HORIZONTAL, KOMPAK & CEPAT) --}}
                            <div class="px-4 py-2 sm:px-6 sm:py-2.5 bg-white border-b border-[#E2E0F7] shrink-0">
                                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                                    <span class="text-[10px] sm:text-[11px] font-bold text-[#7C7896] uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                                        <span>Pilih Departemen Sasaran ({{ $departmentsToConfigure->count() }}):</span>
                                    </span>
                                    <div class="flex items-center gap-2 text-[10px] sm:text-[11px]">
                                        <button type="button" @click="document.querySelectorAll('input[name=\'department_ids[]\']').forEach(c => c.checked = true)" class="font-bold text-[#6C5CE7] hover:underline">Centang Semua</button>
                                        <span class="text-slate-300">•</span>
                                        <button type="button" @click="document.querySelectorAll('input[name=\'department_ids[]\']').forEach(c => c.checked = false)" class="font-medium text-[#7C7896] hover:underline">Kosongkan</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 scrollbar-thin">
                                    @foreach ($departmentsToConfigure as $dept)
                                        <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-xl border text-[11px] sm:text-xs font-semibold shrink-0 cursor-pointer transition shadow-2xs"
                                             :class="activeDeptTab === {{ $dept->id }} ? 'bg-[#6C5CE7] border-[#6C5CE7] text-white shadow-xs' : 'bg-slate-50 border-[#E2E0F7] text-[#2D2A3E] hover:bg-slate-100'"
                                             @click="activeDeptTab = {{ $dept->id }}">
                                            <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}"
                                                   {{ $dept->id == $departmentId ? 'checked' : '' }}
                                                   @click.stop
                                                   class="rounded text-[#6C5CE7] focus:ring-[#6C5CE7] h-3.5 w-3.5 cursor-pointer">
                                            <span>{{ $dept->name }}</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                                                  :class="activeDeptTab === {{ $dept->id }} ? 'bg-white/25 text-white' : 'bg-white text-[#7C7896]'"
                                                  x-text="(deptShifts[{{ $dept->id }}] || []).filter(s => s.enabled).length + ' Shift'">
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- AREA FORM CARD ATUR KEBUTUHAN SHIFT (SCROLLABLE, BERSIH, DINAMIS) --}}
                        <div class="flex-1 min-h-0 flex flex-col overflow-hidden bg-[#FAF9FF]/40">
                            @foreach ($departmentsToConfigure as $dept)
                                <div x-show="!{{ $isGlobalManager ? 'true' : 'false' }} || activeDeptTab === {{ $dept->id }}" 
                                     class="flex-1 min-h-0 flex flex-col overflow-hidden">
                                    
                                    {{-- Header Departemen Aktif & Tombol Tambah Shift --}}
                                    <div class="px-4 py-2 sm:px-6 sm:py-2.5 border-b border-[#E2E0F7] bg-white shrink-0 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 sm:h-2.5 sm:w-2.5 rounded-full bg-[#6C5CE7]"></span>
                                            <span class="text-xs sm:text-sm font-bold text-[#2D2A3E]">{{ $dept->name }}</span>
                                            <span class="text-[10px] sm:text-[11px] text-[#7C7896]">({{ $dept->active_staff_count }} Staf Aktif)</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] sm:text-[11px] font-semibold text-[#6C5CE7] bg-[#F5F3FF] px-2 py-0.5 rounded-lg border border-[#E2E0F7]"
                                                  x-text="(deptShifts[{{ $dept->id }}] || []).filter(s => s.enabled).length + ' Shift Aktif'">
                                            </span>
                                            <button type="button" @click="addShift({{ $dept->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1 bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white text-[11px] sm:text-xs font-bold rounded-xl shadow-2xs transition">
                                                <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                                                <span>Tambah Shift</span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Scrollable Container Kartu Shift --}}
                                    <div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 space-y-3 scrollbar-thin">
                                        {{-- State Kosong: Jika Tidak Ada Shift Dikonfigurasi --}}
                                        <div x-show="(deptShifts[{{ $dept->id }}] || []).length === 0" 
                                             class="p-6 sm:p-8 bg-white rounded-2xl border-2 border-dashed border-[#E2E0F7] text-center my-4 sm:my-6">
                                            <div class="inline-flex p-3 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mb-3">
                                                <i data-lucide="layers" class="h-6 w-6"></i>
                                            </div>
                                            <h4 class="text-xs sm:text-sm font-bold text-[#2D2A3E]">
                                                Belum Ada Shift untuk {{ $dept->name }}
                                            </h4>
                                            <p class="text-[11px] text-[#7C7896] mt-1 max-w-sm mx-auto">
                                                Tambahkan shift kerja dinamis untuk departemen ini agar algoritma dapat menyusun penugasan.
                                            </p>
                                            <div class="flex flex-wrap items-center justify-center gap-2 mt-4">
                                                <button type="button" @click="addShift({{ $dept->id }}, 'Shift Pagi', '07:00', '15:00')"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded-xl bg-[#F5F3FF] text-[#6C5CE7] hover:bg-[#EAE6FF] transition border border-[#E2E0F7]">
                                                    + Shift Pagi (07:00 – 15:00)
                                                </button>
                                                <button type="button" @click="addShift({{ $dept->id }}, 'Shift Middle', '11:00', '19:00')"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded-xl bg-[#F5F3FF] text-[#6C5CE7] hover:bg-[#EAE6FF] transition border border-[#E2E0F7]">
                                                    + Shift Middle (11:00 – 19:00)
                                                </button>
                                                <button type="button" @click="addShift({{ $dept->id }}, 'Shift Siang', '15:00', '23:00')"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded-xl bg-[#F5F3FF] text-[#6C5CE7] hover:bg-[#EAE6FF] transition border border-[#E2E0F7]">
                                                    + Shift Siang (15:00 – 23:00)
                                                </button>
                                                <button type="button" @click="addShift({{ $dept->id }})"
                                                        class="px-3 py-1 text-xs font-bold rounded-xl bg-[#6C5CE7] text-white hover:bg-[#5b4cdb] transition">
                                                    + Shift Kustom
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Daftar Kartu Shift Dinamis --}}
                                        <template x-for="(shift, sIdx) in (deptShifts[{{ $dept->id }}] || [])" :key="shift.id">
                                            <div class="bg-white p-3 sm:p-4 rounded-2xl border border-[#E2E0F7] shadow-2xs hover:shadow-xs transition relative"
                                                 :class="{ 'opacity-60 bg-slate-50/70': !shift.enabled }"
                                                 :style="'border-left: 4px solid ' + (shift.color_code || '#6C5CE7')">
                                                
                                                {{-- Baris Atas: Toggle, Judul, Jam, Kuota, & Aksi --}}
                                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 sm:gap-3">
                                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                                        {{-- Checkbox Aktif / Non-aktif --}}
                                                        <input type="checkbox" x-model="shift.enabled"
                                                               class="rounded text-[#6C5CE7] focus:ring-[#6C5CE7] h-4 w-4 cursor-pointer shrink-0"
                                                               title="Aktifkan / Non-aktifkan shift ini">
                                                        
                                                        <span class="h-3.5 w-3.5 rounded-full shrink-0 shadow-2xs cursor-pointer"
                                                              :style="'background-color: ' + (shift.color_code || '#6C5CE7')"
                                                              @click="openTemplateEditModal({{ $dept->id }}, sIdx)"
                                                              title="Klik untuk ubah warna / detail"></span>

                                                        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                                                            {{-- Input Nama Shift (Jelas & Terbuka) --}}
                                                            <div class="relative">
                                                                <input type="text" x-model="shift.title" required
                                                                       class="text-xs font-bold text-[#2D2A3E] bg-white border border-[#E2E0F7] hover:border-[#6C5CE7] focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] px-2.5 py-1.5 rounded-xl transition w-36 sm:w-44 shadow-2xs"
                                                                       placeholder="Nama Shift">
                                                            </div>
                                                            
                                                            {{-- Input Jam Kerja (Jelas & Terbuka) --}}
                                                            <div class="flex items-center gap-1 text-[11px] text-[#2D2A3E] bg-white border border-[#E2E0F7] rounded-xl px-2 py-1 shadow-2xs">
                                                                <svg class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <input type="time" x-model="shift.start_time" required
                                                                       class="border-0 p-0 text-[11px] font-bold text-[#2D2A3E] bg-transparent focus:ring-0 w-16 sm:w-18">
                                                                <span class="text-slate-400">–</span>
                                                                <input type="time" x-model="shift.end_time" required
                                                                       class="border-0 p-0 text-[11px] font-bold text-[#2D2A3E] bg-transparent focus:ring-0 w-16 sm:w-18">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2 self-end lg:self-auto shrink-0">
                                                        {{-- Kuota Orang --}}
                                                        <div class="flex items-center gap-1.5 bg-[#F5F3FF] px-2.5 py-1 sm:px-3 sm:py-1 rounded-xl border border-[#E2E0F7] shadow-2xs">
                                                            <span class="text-[11px] sm:text-xs font-semibold text-[#7C7896]">Kebutuhan:</span>
                                                            <button type="button" @click="if (shift.quota > 1) shift.quota--"
                                                                    class="px-1.5 py-0.5 text-xs font-bold text-[#7C7896] hover:bg-slate-200 rounded transition">-</button>
                                                            <input type="number" x-model.number="shift.quota" min="1" max="50" required
                                                                   class="w-10 text-center text-xs font-bold bg-white border border-[#E2E0F7] rounded-lg px-1 py-0.5 text-[#2D2A3E] focus:ring-1 focus:ring-[#6C5CE7]">
                                                            <button type="button" @click="shift.quota++"
                                                                    class="px-1.5 py-0.5 text-xs font-bold text-[#7C7896] hover:bg-slate-200 rounded transition">+</button>
                                                            <span class="text-xs font-bold text-[#2D2A3E]">Orang</span>
                                                        </div>

                                                        {{-- Tombol Edit Lengkap (Sub-Modal) --}}
                                                        <button type="button" @click="openTemplateEditModal({{ $dept->id }}, sIdx)"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 sm:px-3 sm:py-1.5 bg-[#F5F3FF] hover:bg-[#EAE6FF] text-[#6C5CE7] text-xs font-bold rounded-xl border border-[#E2E0F7] shadow-2xs transition"
                                                                title="Buka Form Edit Lengkap Template Shift">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            <span>Edit</span>
                                                        </button>

                                                        {{-- Tombol Hapus Shift --}}
                                                        <button type="button" @click="removeShift({{ $dept->id }}, sIdx)"
                                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition"
                                                                title="Hapus Shift Ini">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Baris Bawah: Pilihan Hari Kerja Dinamis --}}
                                                <div class="mt-2.5 pt-2.5 sm:mt-3 sm:pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px]">
                                                    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
                                                        <span class="text-[#7C7896] text-[10px] font-semibold mr-0.5 sm:mr-1">Hari:</span>
                                                        @php
                                                            $daysShort = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
                                                        @endphp
                                                        @foreach ($daysShort as $isoDay => $lbl)
                                                            <button type="button" 
                                                                    @click="toggleDay(shift, {{ $isoDay }})"
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-lg border text-[10px] font-semibold transition shadow-2xs"
                                                                    :class="shift.days.includes({{ $isoDay }}) ? 'bg-[#6C5CE7] border-[#6C5CE7] text-white shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-purple-50'">
                                                                <span>{{ $lbl }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>

                                                    {{-- Shortcut Cepat Pilihan Hari --}}
                                                    <div class="flex items-center gap-1.5 text-[10px] self-end sm:self-auto">
                                                        <button type="button" 
                                                                @click="selectAllDays(shift)"
                                                                class="px-2 py-0.5 rounded-md bg-[#F5F3FF] text-[#6C5CE7] hover:bg-[#EAE6FF] font-semibold transition">
                                                            Semua
                                                        </button>
                                                        <button type="button" 
                                                                @click="selectWeekdays(shift)"
                                                                class="px-2 py-0.5 rounded-md bg-slate-100 text-[#7C7896] hover:bg-slate-200 font-semibold transition">
                                                            Sen-Jum
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Hidden Inputs Dikirim Saat Form Submit --}}
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][template_id]'" :value="shift.template_id || ''">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][title]'" :value="shift.title">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][start_time]'" :value="shift.start_time">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][end_time]'" :value="shift.end_time">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][quota]'" :value="shift.quota">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][color_code]'" :value="shift.color_code || '#6C5CE7'">
                                                <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][enabled]'" :value="shift.enabled ? 1 : 0">
                                                <template x-for="d in shift.days" :key="d">
                                                    <input type="hidden" :name="'requirements[{{ $dept->id }}][' + sIdx + '][days][]'" :value="d">
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer Action (Sticky di Bawah Modal, Selalu Terlihat) --}}
                        <div class="px-4 py-3 sm:px-6 sm:py-3.5 border-t border-[#E2E0F7] bg-white shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                            <span class="text-[10px] sm:text-[11px] text-[#7C7896] flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                <span>Mematuhi batas 40h PP 35/2021 & rest period 11 jam</span>
                            </span>
                            
                            <div class="flex items-center justify-end gap-2 w-full sm:w-auto">
                                <button type="button" @click="!isSubmitting && (showBuildModal = false)" :disabled="isSubmitting"
                                        class="flex-1 sm:flex-initial px-3.5 sm:px-4 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:bg-slate-50 transition text-center disabled:opacity-50 disabled:cursor-not-allowed">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting"
                                        class="flex-1 sm:flex-initial px-4 sm:px-5 py-2 text-xs font-bold rounded-xl bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white shadow-md transition flex items-center justify-center gap-1.5 sm:gap-2 disabled:opacity-85 disabled:cursor-wait">
                                    <span x-show="!isSubmitting" class="inline-flex items-center gap-1.5 sm:gap-2">
                                        <i data-lucide="sparkles" class="h-3.5 w-3.5 shrink-0"></i>
                                        <span>Jalankan Auto-Scheduling</span>
                                    </span>
                                    <span x-show="isSubmitting" style="display: none;" class="inline-flex items-center gap-2">
                                        <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span>Menyusun Jadwal...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ======================================================== --}}
                    {{-- SUB-MODAL: EDIT TEMPLATE SHIFT LENGKAP --}}
                    {{-- ======================================================== --}}
                    <div x-show="showTemplateEditModal"
                         x-cloak
                         class="fixed inset-0 z-60 overflow-y-auto p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center"
                         style="display: none;"
                         @keydown.escape.window="closeTemplateEditModal()">
                        
                        <div @click.away="closeTemplateEditModal()"
                             class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-[#E2E0F7] overflow-hidden my-auto"
                             @click.stop>
                            
                            {{-- Header Sub-Modal --}}
                            <div class="px-5 py-4 border-b border-[#E2E0F7] bg-slate-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-[#2D2A3E]">Edit Template Shift</h4>
                                        <p class="text-[11px] text-[#7C7896]">Sesuaikan nama, jam kerja, kuota, warna dan hari aktif</p>
                                    </div>
                                </div>
                                <button type="button" @click="closeTemplateEditModal()"
                                        class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>

                            {{-- Body Sub-Modal --}}
                            <div class="p-5 sm:p-6 space-y-4">
                                {{-- Nama Shift --}}
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Nama Template Shift <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="editingTemplate.title" required
                                           class="w-full text-xs font-bold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition"
                                           placeholder="mis. Shift Pagi / Middle / Closing">
                                </div>

                                {{-- Jam Mulai & Jam Selesai --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                            Jam Mulai <span class="text-red-500">*</span>
                                        </label>
                                        <input type="time" x-model="editingTemplate.start_time" required
                                               class="w-full text-xs font-bold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                            Jam Selesai <span class="text-red-500">*</span>
                                        </label>
                                        <input type="time" x-model="editingTemplate.end_time" required
                                               class="w-full text-xs font-bold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-[#7C7896] bg-[#F5F3FF] p-2 rounded-xl border border-[#E2E0F7]">
                                    <span class="flex items-center gap-1 font-semibold">
                                        <i data-lucide="clock" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                                        Durasi Shift:
                                    </span>
                                    <span class="font-extrabold text-[#6C5CE7]" x-text="calculateDuration(editingTemplate.start_time, editingTemplate.end_time)"></span>
                                </div>

                                {{-- Kuota Personel --}}
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Kebutuhan Kuota Staf Per Hari <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex items-center border border-[#E2E0F7] rounded-xl bg-slate-50 overflow-hidden w-full">
                                        <button type="button" @click="if (editingTemplate.quota > 1) editingTemplate.quota--"
                                                class="px-3.5 py-2 text-xs font-bold text-[#7C7896] hover:bg-slate-200 transition">
                                            -
                                        </button>
                                        <input type="number" x-model.number="editingTemplate.quota" min="1" max="50" required
                                               class="w-full text-center text-xs font-bold text-[#2D2A3E] bg-transparent focus:outline-none py-2">
                                        <button type="button" @click="editingTemplate.quota++"
                                                class="px-3.5 py-2 text-xs font-bold text-[#7C7896] hover:bg-slate-200 transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Pilihan Warna Label --}}
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Warna Identitas Shift
                                    </label>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <template x-for="color in colorPalette" :key="color">
                                            <button type="button" @click="editingTemplate.color_code = color"
                                                    class="h-7 w-7 rounded-xl transition shadow-2xs border-2 flex items-center justify-center cursor-pointer"
                                                    :style="'background-color: ' + color + '; border-color: ' + (editingTemplate.color_code === color ? '#2D2A3E' : 'transparent')">
                                                <i x-show="editingTemplate.color_code === color" data-lucide="check" class="h-3.5 w-3.5 text-white"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Hari Kerja Aktif --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-[#2D2A3E]">
                                            Hari Operasional Aktif
                                        </label>
                                        <div class="flex items-center gap-1.5 text-[10px]">
                                            <button type="button" @click="editingTemplate.days = [1,2,3,4,5,6,7]" class="text-[#6C5CE7] font-bold hover:underline">Semua</button>
                                            <span class="text-slate-300">•</span>
                                            <button type="button" @click="editingTemplate.days = [1,2,3,4,5]" class="text-[#7C7896] font-semibold hover:underline">Sen-Jum</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1">
                                        <template x-for="day in [
                                            {id: 1, name: 'Sen'},
                                            {id: 2, name: 'Sel'},
                                            {id: 3, name: 'Rab'},
                                            {id: 4, name: 'Kam'},
                                            {id: 5, name: 'Jum'},
                                            {id: 6, name: 'Sab'},
                                            {id: 7, name: 'Min'}
                                        ]" :key="day.id">
                                            <button type="button" @click="toggleTemplateEditDay(day.id)"
                                                    class="py-1 text-center rounded-lg border text-[10px] font-bold transition"
                                                    :class="editingTemplate.days.includes(day.id) ? 'bg-[#6C5CE7] border-[#6C5CE7] text-white shadow-2xs' : 'bg-slate-50 border-[#E2E0F7] text-[#7C7896] hover:bg-slate-100'">
                                                <span x-text="day.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Sub-Modal --}}
                            <div class="px-5 py-3.5 border-t border-[#E2E0F7] bg-slate-50/70 flex items-center justify-between gap-2">
                                <button type="button" @click="closeTemplateEditModal()"
                                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:bg-white transition">
                                    Batal
                                </button>
                                <button type="button" @click="saveTemplateEditModal()"
                                        class="px-4 py-2 text-xs font-bold rounded-xl bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white shadow-sm transition flex items-center gap-1.5">
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                    <span>Terapkan Perubahan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- MODAL 2: KONFIRMASI TERBITKAN JADWAL --}}
            {{-- ======================================================== --}}
            <div x-show="showPublishModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto p-3 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-xs flex justify-center items-start sm:items-center"
                 style="display: none;">
                <div @click.away="showPublishModal = false" class="relative bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#E2E0F7] my-auto">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 sm:p-3 bg-emerald-50 text-emerald-600 rounded-xl sm:rounded-2xl shrink-0">
                            <i data-lucide="send" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#2D2A3E]">Terbitkan Jadwal Kerja</h3>
                            <p class="text-[11px] sm:text-xs text-[#7C7896]">
                                @if ($isGlobalManager)
                                    Publikasikan draf jadwal resmi ke staf
                                @else
                                    Publikasikan {{ $draftShiftsCount }} shift draf ke staf
                                @endif
                            </p>
                        </div>
                    </div>

                    <p class="my-4 text-xs text-[#7C7896] leading-relaxed">
                        Setelah diterbitkan, shift akan berstatus <strong>Published</strong> dan langsung tampil pada kalender kerja seluruh karyawan yang ditugaskan.
                    </p>

                    <form method="POST" action="{{ route('manager.shifts.publish') }}">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">

                        @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
                            <div class="mb-4 bg-[#F5F3FF] p-3 rounded-xl border border-[#E2E0F7] space-y-2">
                                <label class="block text-xs font-bold text-[#2D2A3E]">Pilih Cakupan Penerbitan:</label>
                                <label class="flex items-center gap-2 text-xs text-[#2D2A3E] cursor-pointer">
                                    <input type="radio" name="publish_scope" value="current" x-model="publishScope" class="text-[#6C5CE7] focus:ring-[#6C5CE7]">
                                    <span>Hanya Departemen <strong>{{ $currentDepartment->name }}</strong> ({{ $draftShiftsCount }} Draf)</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-[#2D2A3E] cursor-pointer">
                                    <input type="radio" name="publish_scope" value="all" x-model="publishScope" class="text-[#6C5CE7] focus:ring-[#6C5CE7]">
                                    <span>Seluruh Departemen (<strong>{{ $totalCompanyDraftShifts }} Draf</strong> di {{ count($departments) }} Dept)</span>
                                </label>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-2 sm:gap-2.5 mt-5">
                            <button type="button" @click="showPublishModal = false"
                                    class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:bg-slate-50 transition text-center">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 sm:flex-initial px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                <span>Ya, Terbitkan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- MODAL 3: KONFIRMASI HAPUS DRAF --}}
            {{-- ======================================================== --}}
            <div x-show="showClearModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto p-3 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-xs flex justify-center items-start sm:items-center"
                 style="display: none;">
                <div @click.away="showClearModal = false" class="relative bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#E2E0F7] my-auto">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 sm:p-3 bg-red-50 text-red-600 rounded-xl sm:rounded-2xl shrink-0">
                            <i data-lucide="trash-2" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#2D2A3E]">Hapus Seluruh Draf</h3>
                            <p class="text-[11px] sm:text-xs text-[#7C7896]">Hapus shift draf yang belum terbit</p>
                        </div>
                    </div>

                    <p class="my-4 text-xs text-[#7C7896] leading-relaxed">
                        Tindakan ini akan menghapus semua shift draf dan penugasan pada periode ini sehingga Anda dapat menyusun ulang jadwal dari awal.
                    </p>

                    <form method="POST" action="{{ route('manager.shifts.clear-drafts') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="department_id" value="{{ $departmentId }}">
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">

                        @if ($isGlobalManager && $totalCompanyDraftShifts > 0)
                            <div class="mb-4 bg-red-50/60 p-3 rounded-xl border border-red-200/70 space-y-2">
                                <label class="block text-xs font-bold text-red-950">Pilih Cakupan Penghapusan:</label>
                                <label class="flex items-center gap-2 text-xs text-[#2D2A3E] cursor-pointer">
                                    <input type="radio" name="clear_scope" value="current" x-model="clearScope" class="text-red-600 focus:ring-red-500">
                                    <span>Hanya Departemen <strong>{{ $currentDepartment->name }}</strong> ({{ $draftShiftsCount }} Draf)</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-[#2D2A3E] cursor-pointer">
                                    <input type="radio" name="clear_scope" value="all" x-model="clearScope" class="text-red-600 focus:ring-red-500">
                                    <span>Seluruh Departemen (<strong>{{ $totalCompanyDraftShifts }} Draf</strong>)</span>
                                </label>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-2 sm:gap-2.5 mt-5">
                            <button type="button" @click="showClearModal = false"
                                    class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:bg-slate-50 transition text-center">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 sm:flex-initial px-4 py-2 text-xs font-bold rounded-xl bg-red-600 hover:bg-red-700 text-white shadow-sm transition text-center">
                                Hapus Draf
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    {{-- ======================================================== --}}
    {{-- TAB NAVIGASI DEPARTEMEN UNTUK GLOBAL MANAGER --}}
    {{-- ======================================================== --}}
    @if ($isGlobalManager && count($departments) > 1)
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-3.5 shadow-2xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7]">
                        <i data-lucide="building-2" class="h-4 w-4"></i>
                    </span>
                    <div>
                        <h3 class="text-xs font-bold text-[#2D2A3E]">Pilih Departemen untuk Ditinjau di Kalender</h3>
                        <p class="text-[11px] text-[#7C7896]">Klik tab departemen di bawah untuk memeriksa matriks roster masing-masing tim</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs self-end sm:self-auto">
                    <span class="text-[#7C7896] font-medium">Total Draf Semua Tim:</span>
                    <span class="px-2.5 py-0.5 rounded-full font-extrabold bg-amber-100 text-amber-800 text-[11px] border border-amber-200">
                        {{ $totalCompanyDraftShifts }} Draf
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-thin">
                @foreach ($departments as $dept)
                    @php
                        $isActiveDept = ($dept->id == $departmentId);
                    @endphp
                    <a href="{{ route('manager.shifts.index', ['department_id' => $dept->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs whitespace-nowrap {{ $isActiveDept ? 'bg-[#6C5CE7] text-white ring-2 ring-[#6C5CE7]/30 shadow-sm' : 'bg-[#F5F3FF] hover:bg-[#EAE6FF] text-[#2D2A3E] border border-[#E2E0F7]' }}">
                        <i data-lucide="building" class="h-3.5 w-3.5 {{ $isActiveDept ? 'text-white' : 'text-[#6C5CE7]' }}"></i>
                        <span>{{ $dept->name }}</span>
                        @if ($dept->draft_count > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $isActiveDept ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                                {{ $dept->draft_count }} Draf
                            </span>
                        @elseif ($dept->published_count > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $isActiveDept ? 'bg-emerald-300 text-emerald-950' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $dept->published_count }} Terbit
                            </span>
                        @else
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $isActiveDept ? 'bg-purple-800/40 text-purple-100' : 'bg-slate-200 text-slate-500' }}">
                                0
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- KALENDER MATRIKS DINAMIS --}}
    {{-- ======================================================== --}}
    <div class="bg-white rounded-3xl border border-[#E2E0F7] shadow-2xs overflow-hidden"
         x-data="shiftEditCalendar()">
        {{-- Header Bar Kalender --}}
        <div class="p-4 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2.5">
                <div class="h-7 w-7 rounded-lg bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                </div>
                <div>
                    <h2 class="text-xs md:text-sm font-bold text-[#2D2A3E]">
                        Matriks Roster Mingguan: {{ $currentDepartment?->name ?? 'Departemen Tim' }}
                    </h2>
                    <span class="text-[11px] text-[#7C7896]">
                        Periode {{ $startDate->translatedFormat('d M') }} s.d. {{ $endDate->translatedFormat('d M Y') }} ({{ count($days) }} Hari)
                    </span>
                </div>
            </div>

            {{-- Legend Indikator & Hint --}}
            <div class="flex items-center flex-wrap gap-3 text-[11px] font-semibold text-[#7C7896]">
                <span class="text-[10px] text-[#6C5CE7] bg-[#6C5CE7]/10 px-2 py-0.5 rounded-full font-bold flex items-center gap-1">
                    <i data-lucide="mouse-pointer-click" class="h-3 w-3"></i> Klik shift untuk edit
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Draf
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Terbit
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="px-1 py-0.2 rounded bg-red-100 text-red-700 text-[9px] font-bold">OT</span> Lembur
                </span>
            </div>
        </div>

        {{-- Grid Kolom Hari Dinamis --}}
        <div class="overflow-x-auto">
            <div class="inline-flex min-w-full divide-x divide-[#E2E0F7]">
                @foreach ($days as $day)
                    @php
                        $dayShifts = $shiftsByDate[$day['date_str']] ?? collect();
                    @endphp
                    <div class="w-64 shrink-0 flex flex-col min-h-[550px] {{ $day['is_today'] ? 'bg-[#F5F3FF]/30' : 'bg-white' }}">
                        {{-- Header Tanggal Kolom --}}
                        <div class="p-3 text-center border-b border-[#E2E0F7] {{ $day['is_today'] ? 'bg-[#6C5CE7]/10' : 'bg-slate-50/40' }}">
                            <p class="text-xs font-bold uppercase tracking-wider {{ $day['is_today'] ? 'text-[#6C5CE7]' : 'text-[#7C7896]' }}">
                                {{ $day['day_name'] }}
                            </p>
                            <p class="text-sm font-extrabold {{ $day['is_today'] ? 'text-[#6C5CE7]' : 'text-[#2D2A3E]' }} mt-0.5">
                                {{ $day['formatted'] }}
                            </p>
                            @if ($day['is_today'])
                                <span class="inline-block mt-1 text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-[#6C5CE7] text-white shadow-2xs">
                                    Hari Ini
                                </span>
                            @endif
                        </div>

                        {{-- Daftar Shift Pada Hari Ini --}}
                        <div class="p-2.5 flex-1 space-y-2.5">
                            @forelse ($dayShifts as $shift)
                                @php
                                    $templateColor = $shift->template->color_code ?? '#6C5CE7';
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
                                <div class="rounded-2xl border border-[#E2E0F7] bg-white p-3 shadow-2xs hover:shadow-md hover:border-[#6C5CE7]/60 transition duration-150 relative overflow-hidden group cursor-pointer"
                                     style="border-left: 4px solid {{ $templateColor }};"
                                     @click="openEditModal({{ Js::from($shiftPayload) }})">
                                    
                                    {{-- Baris Judul, Status & Tombol Edit --}}
                                    <div class="flex items-start justify-between gap-1 mb-1.5">
                                        <div class="truncate">
                                            <p class="text-xs font-bold text-[#2D2A3E] truncate group-hover:text-[#6C5CE7] transition">{{ $shift->title }}</p>
                                            <span class="text-[10px] text-[#7C7896] flex items-center gap-1 mt-0.5">
                                                <i data-lucide="clock" class="h-3 w-3"></i>
                                                {{ $shift->start_time->format('H:i') }} – {{ $shift->end_time->format('H:i') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-md shrink-0 {{ $isDraft ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $shift->status }}
                                            </span>
                                            <button type="button"
                                                    @click.stop="openEditModal({{ Js::from($shiftPayload) }})"
                                                    class="h-5 w-5 rounded-md flex items-center justify-center text-[#7C7896] hover:text-[#6C5CE7] hover:bg-[#6C5CE7]/10 transition"
                                                    title="Edit Shift">
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

                                    {{-- Daftar Chip Staf yang Ditugaskan --}}
                                    <div class="space-y-1.5">
                                        @forelse ($shift->shiftAssignments as $assignment)
                                            @php
                                                $staf = $assignment->user;
                                                $initials = collect(explode(' ', $staf->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                                            @endphp
                                            <div class="flex items-center justify-between gap-1.5 bg-[#F5F3FF]/70 border border-[#E2E0F7] rounded-xl px-2 py-1 text-[11px]">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <div class="h-5 w-5 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-[9px] font-bold shrink-0">
                                                        {{ $initials }}
                                                    </div>
                                                    <span class="font-semibold text-[#2D2A3E] truncate">{{ $staf->name }}</span>
                                                </div>

                                                <div class="flex items-center gap-1 shrink-0">
                                                    <span class="text-[9px] font-bold text-[#7C7896] uppercase bg-white border border-[#E2E0F7] px-1 rounded">
                                                        {{ $staf->employment_type === 'full_time' ? 'FT' : ($staf->employment_type === 'part_time' ? 'PT' : 'FL') }}
                                                    </span>

                                                    @if ($assignment->is_overtime)
                                                        <span class="text-[8px] font-extrabold uppercase px-1 py-0.2 rounded bg-red-100 text-red-700 border border-red-200" title="Shift Bernilai Lembur">
                                                            OT
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-[10px] text-slate-400 italic text-center py-1">
                                                Belum ada staf ditugaskan
                                            </div>
                                        @endforelse

                                        {{-- Kotak Dashed Jika Kuota Kurang --}}
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
                                    <span class="text-[11px] text-[#7C7896] mt-1.5">Tidak ada shift</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL EDIT SHIFT KERJA & PENUGASAN PERSONEL --}}
        {{-- ======================================================== --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="closeEditModal()">
            {{-- Backdrop --}}
            <div x-show="showEditModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                 @click="closeEditModal()"></div>

            {{-- Modal Dialog Card --}}
            <div class="min-h-full flex items-center justify-center p-3 sm:p-4">
                <div x-show="showEditModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-xl max-h-[92vh] flex flex-col bg-white rounded-3xl shadow-2xl border border-[#E2E0F7] overflow-hidden z-10"
                     @click.stop>

                    {{-- Sticky Header --}}
                    <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between bg-slate-50/70 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center shrink-0">
                                <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#2D2A3E]">Edit Shift Kerja</h3>
                                <p class="text-xs text-[#7C7896]" x-text="editingShift.date_formatted"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeEditModal()"
                                class="h-8 w-8 rounded-xl flex items-center justify-center text-[#7C7896] hover:text-[#2D2A3E] hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>

                    {{-- Scrollable Form Body --}}
                    <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-4">
                        <form method="POST" :action="editingShift.update_url" id="edit-shift-form">
                            @csrf
                            @method('PUT')

                            {{-- Judul Shift & Status --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Nama Shift <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="title" x-model="editingShift.title" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition"
                                           placeholder="mis. Shift Pagi / Siang / Malam">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Status Shift
                                    </label>
                                    <select name="status" x-model="editingShift.status"
                                            class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition">
                                        <option value="draft">Draf (Draft)</option>
                                        <option value="published">Terbit (Published)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Jam Kerja & Kuota --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Jam Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="start_time" x-model="editingShift.start_time" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Jam Selesai <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="end_time" x-model="editingShift.end_time" required
                                           class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                        Kuota Staf <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex items-center border border-[#E2E0F7] rounded-xl bg-slate-50 overflow-hidden">
                                        <button type="button" @click="if (editingShift.quota > 1) editingShift.quota--"
                                                class="px-2.5 py-2 text-xs font-bold text-[#7C7896] hover:bg-slate-200 transition">
                                            -
                                        </button>
                                        <input type="number" name="quota" x-model.number="editingShift.quota" min="1" max="50" required
                                               class="w-full text-center text-xs font-bold text-[#2D2A3E] bg-transparent focus:outline-none py-2">
                                        <button type="button" @click="editingShift.quota++"
                                                class="px-2.5 py-2 text-xs font-bold text-[#7C7896] hover:bg-slate-200 transition">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-[#7C7896] mb-4 flex items-center gap-1.5">
                                <i data-lucide="info" class="h-3 w-3 text-[#6C5CE7]"></i>
                                Jam selesai lebih awal dari jam mulai otomatis dihitung sebagai shift overnight (lintas tengah malam).
                            </p>

                            {{-- Penugasan Personel / Staf --}}
                            <div class="border border-[#E2E0F7] rounded-2xl p-4 bg-slate-50/50">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                    <div>
                                        <label class="text-xs font-bold text-[#2D2A3E] flex items-center gap-1.5">
                                            <i data-lucide="users" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                                            Personel Ditugaskan
                                        </label>
                                        <p class="text-[11px] text-[#7C7896]">Centang staf departemen yang dialokasikan ke shift ini.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                              :class="editingShift.assigned_user_ids.length >= editingShift.quota ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                              x-text="editingShift.assigned_user_ids.length + ' / ' + editingShift.quota + ' Kuota Terisi'">
                                        </span>
                                    </div>
                                </div>

                                {{-- Search & Filter Staf --}}
                                <div class="flex items-center gap-2 mb-2.5">
                                    <div class="relative flex-1">
                                        <i data-lucide="search" class="h-3.5 w-3.5 text-[#7C7896] absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                                        <input type="text" x-model="searchStaff" placeholder="Cari nama karyawan..."
                                               class="w-full text-xs pl-8 pr-3 py-1.5 bg-white border border-[#E2E0F7] rounded-xl text-[#2D2A3E] focus:outline-none focus:ring-1 focus:ring-[#6C5CE7]">
                                    </div>
                                    <button type="button" @click="autoFillQuota()"
                                            class="text-[10px] font-bold px-2.5 py-1.5 rounded-xl border border-[#E2E0F7] bg-white hover:bg-[#F5F3FF] text-[#6C5CE7] transition shrink-0"
                                            title="Pilih otomatis sejumlah kuota">
                                        Pilih Sesuai Kuota
                                    </button>
                                    <button type="button" @click="clearStaff()"
                                            class="text-[10px] font-bold px-2.5 py-1.5 rounded-xl border border-[#E2E0F7] bg-white hover:bg-red-50 text-red-600 transition shrink-0"
                                            title="Kosongkan seluruh penugasan">
                                        Reset
                                    </button>
                                </div>

                                {{-- Daftar List Karyawan --}}
                                <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 border border-[#E2E0F7] rounded-xl p-2 bg-white">
                                    <template x-for="staff in filteredStaff()" :key="staff.id">
                                        <div @click="toggleStaff(staff.id)"
                                             class="flex items-center justify-between p-2 rounded-xl border transition cursor-pointer"
                                             :class="isStaffAssigned(staff.id) ? 'border-[#6C5CE7] bg-[#F5F3FF]/70' : 'border-slate-100 hover:border-[#E2E0F7] hover:bg-slate-50'">
                                            <div class="flex items-center gap-2.5 truncate">
                                                <input type="checkbox" name="assigned_user_ids[]" :value="staff.id"
                                                       :checked="isStaffAssigned(staff.id)"
                                                       @click.stop
                                                       @change="toggleStaff(staff.id)"
                                                       class="rounded border-[#E2E0F7] text-[#6C5CE7] focus:ring-[#6C5CE7] h-3.5 w-3.5">
                                                <div class="h-6 w-6 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-[10px] font-bold shrink-0"
                                                     x-text="staff.initials">
                                                </div>
                                                <span class="text-xs font-semibold text-[#2D2A3E] truncate" x-text="staff.name"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="text-[9px] font-bold text-[#7C7896] uppercase bg-slate-100 border border-[#E2E0F7] px-1.5 py-0.5 rounded"
                                                      x-text="staff.type_label">
                                                </span>
                                                <span x-show="isStaffAssigned(staff.id)" class="text-[9px] font-bold text-[#6C5CE7] bg-[#6C5CE7]/10 px-1.5 py-0.5 rounded">
                                                    Ditugaskan
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filteredStaff().length === 0" class="py-4 text-center text-xs text-[#7C7896]">
                                        Tidak ditemukan staf yang cocok dengan kata kunci pencarian.
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Hidden Delete Form --}}
                        <form id="delete-shift-form" method="POST" :action="editingShift.delete_url" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                    {{-- Sticky Footer --}}
                    <div class="px-5 py-3.5 border-t border-[#E2E0F7] flex items-center justify-between gap-3 bg-slate-50/70 shrink-0">
                        <button type="button" @click="confirmDelete()"
                                class="px-3.5 py-2 text-xs font-bold rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            <span>Hapus Shift</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeEditModal()"
                                    class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#7C7896] hover:bg-white transition">
                                Batal
                            </button>
                            <button type="submit" form="edit-shift-form"
                                    class="px-4 py-2 text-xs font-bold rounded-xl bg-[#6C5CE7] hover:bg-[#5b4cdb] text-white shadow-sm transition flex items-center gap-1.5">
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
