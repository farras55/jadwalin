<x-app-layout>
    <div
        class="space-y-6"
        x-data="{
            drawerOpen: false,
            activeItem: null,
            decisionTab: 'approve', {{-- 'approve' atau 'reject' --}}
            overtimeStatus: 'approved',
            approveNote: '',
            rejectNote: '',
            searchQuery: '',

            openReview(payload, initialTab = 'approve') {
                this.activeItem = payload;
                this.decisionTab = initialTab;
                this.overtimeStatus = 'approved';
                this.approveNote = '';
                this.rejectNote = '';
                this.drawerOpen = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            closeDrawer() {
                this.drawerOpen = false;
                this.activeItem = null;
            },

            matchesSearch(name, reason, dateStr) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return name.toLowerCase().includes(q) ||
                       reason.toLowerCase().includes(q) ||
                       dateStr.toLowerCase().includes(q);
            }
        }"
        @keydown.escape.window="closeDrawer()"
    >

        {{-- ----------------------------------------------------------------- --}}
        {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                           --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <span>Manager</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-900 font-medium">Koreksi Kehadiran</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Persetujuan Koreksi Kehadiran
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Tinjau dan verifikasi permohonan jam pulang riil karyawan akibat auto clock-out atau kendala teknis.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 shadow-card">
                    <span class="h-2 w-2 rounded-full {{ ($counts['pending'] ?? 0) > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                    <span>Menunggu Review:</span>
                    <strong class="font-mono text-slate-900 font-bold">{{ $counts['pending'] ?? 0 }}</strong>
                </span>
            </div>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 2. FLASH NOTIFICATIONS                                            --}}
        {{-- ----------------------------------------------------------------- --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 flex items-center gap-3 text-xs sm:text-sm shadow-card">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 flex items-center gap-3 text-xs sm:text-sm shadow-card">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 text-rose-800 p-3.5 rounded-xl border border-rose-200 text-xs space-y-1 shadow-card">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>Terdapat kesalahan pada input formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 ml-6 text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- 3. UNIFIED TOOLBAR: SEGMENT FILTER PILLS & SEARCH BAR             --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/90 shadow-card flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            {{-- Horizontal Filter Segment Pills (Menggantikan 4 KPI Cards yang boros ruang) --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                {{-- Tab Menunggu --}}
                <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'pending']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $status === 'pending' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 border border-slate-200/80 text-slate-700 hover:bg-slate-100' }}">
                    <span>Menunggu Review</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold font-mono {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                        {{ $counts['pending'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab Disetujui --}}
                <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'approved']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $status === 'approved' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 border border-slate-200/80 text-slate-700 hover:bg-slate-100' }}">
                    <span>Disetujui</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold font-mono {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $counts['approved'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab Ditolak --}}
                <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'rejected']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $status === 'rejected' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 border border-slate-200/80 text-slate-700 hover:bg-slate-100' }}">
                    <span>Ditolak</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold font-mono {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                        {{ $counts['rejected'] ?? 0 }}
                    </span>
                </a>

                {{-- Tab Semua --}}
                <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'all']) }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition shrink-0 {{ $status === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 border border-slate-200/80 text-slate-700 hover:bg-slate-100' }}">
                    <span>Semua Status</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold font-mono {{ $status === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $counts['all'] ?? 0 }}
                    </span>
                </a>
            </div>

            {{-- Search Box & Departemen Indicator --}}
            <div class="flex items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Cari staf, NIK, tanggal..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                </div>

                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 whitespace-nowrap">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ auth()->user()->is_global_manager ? 'Semua Departemen' : (auth()->user()->department->name ?? 'Departemen') }}</span>
                </span>
            </div>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 4. REGULARIZATION DATA TABLE (HIGH DENSITY)                       --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Daftar Permohonan Koreksi Kehadiran
                    </h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan {{ $regularizations->count() }} dari {{ $regularizations->total() }} pengajuan.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[760px]">
                    <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 text-center w-12">No</th>
                            <th class="px-4 py-2.5">Nama Staf</th>
                            <th class="px-4 py-2.5">Tanggal Shift</th>
                            <th class="px-4 py-2.5">Jam Jadwal</th>
                            <th class="px-4 py-2.5">Jam Pulang Riil</th>
                            <th class="px-4 py-2.5">Alasan Pengajuan</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                            <th class="px-4 py-2.5 text-right w-36">Aksi &amp; Detail</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
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

                                $shiftStartTime = $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : null;
                                $shiftEndTime = $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : null;
                                $shiftSchedule = ($shiftStartTime && $shiftEndTime) ? "{$shiftStartTime} - {$shiftEndTime}" : '-';

                                $scheduledEndStr = $shiftEnd ? $shiftEnd->format('H:i') : ($clockIn ? $clockIn->format('H:i') : '-');
                                $requestedOutStr = $requestedOut ? $requestedOut->format('H:i') : '-';
                                $requestedOutFull = $requestedOut ? $requestedOut->format('d/m/Y H:i') : '-';
                                $clockInStr = $clockIn ? $clockIn->format('H:i') : '-';

                                $ovtStatusLabel = match ($reg->overtime_status) {
                                    'approved' => 'Dihitung Lembur Resmi',
                                    'regular' => 'Dihitung Jam Reguler',
                                    'none' => 'Jam Riil Saja (Tanpa Lembur)',
                                    'rejected' => 'Klaim Lembur Ditolak',
                                    default => '-',
                                };

                                $statusLabel = match ($reg->status) {
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => 'Menunggu Review',
                                };

                                $detailPayload = [
                                    'id' => $reg->id,
                                    'approve_url' => route('manager.attendance.regularizations.approve', $reg),
                                    'reject_url' => route('manager.attendance.regularizations.reject', $reg),
                                    'employee_name' => $empName,
                                    'employee_initials' => strtoupper($initials),
                                    'employee_position' => $reg->employee?->position?->name ?? 'Staf',
                                    'employee_department' => $shift?->department?->name ?? $reg->employee?->department?->name ?? '-',
                                    'shift_title' => $shift?->title ?? 'Shift Regular',
                                    'shift_schedule' => $shiftSchedule,
                                    'date' => $dateFormatted,
                                    'clock_in' => $clockInStr,
                                    'scheduled_out' => $scheduledEndStr,
                                    'requested_out' => $requestedOutStr,
                                    'requested_out_full' => $requestedOutFull,
                                    'requested_time' => $requestedOutFull,
                                    'overtime_str' => $ovtStr,
                                    'overtime_minutes' => $overtimeMinutes,
                                    'reason' => $reg->reason ?? '-',
                                    'status' => $reg->status,
                                    'status_label' => $statusLabel,
                                    'reviewer_name' => $reg->reviewer?->name ?? '-',
                                    'reviewed_at' => $reg->reviewed_at ? $reg->reviewed_at->translatedFormat('d M Y H:i') : null,
                                    'review_note' => $reg->review_note,
                                    'overtime_status' => $reg->overtime_status,
                                    'overtime_status_label' => $ovtStatusLabel,
                                    'created_at' => $reg->created_at ? $reg->created_at->translatedFormat('d M Y H:i') : '-',
                                ];
                            @endphp

                            <tr
                                x-show="matchesSearch(@js($empName), @js($reg->reason ?? ''), @js($dateFormatted))"
                                class="hover:bg-slate-50/70 transition"
                            >
                                {{-- No --}}
                                <td class="px-4 py-3 text-center text-slate-400 font-medium">
                                    {{ $regularizations->firstItem() + $index }}
                                </td>

                                {{-- Nama Staf --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-8 w-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $empName }}</p>
                                            <p class="text-[11px] text-slate-500 truncate">{{ $reg->employee?->position?->name ?? 'Staf' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tanggal --}}
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                    <span class="font-medium">{{ $dateFormatted }}</span>
                                </td>

                                {{-- Jam Semula --}}
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-slate-500">
                                    {{ $shiftEnd ? $shiftEnd->format('H:i') : ($clockIn ? $clockIn->format('H:i') : '-') }}
                                </td>

                                {{-- Jam Pulang Riil --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 font-mono">
                                        <span class="font-bold text-slate-900">{{ $requestedOut ? $requestedOut->format('H:i') : '-' }}</span>
                                        @if ($overtimeMinutes > 0)
                                            <span class="text-[10px] font-semibold text-purple-700 bg-purple-50 px-1 rounded border border-purple-200">
                                                +{{ $ovtStr }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Alasan --}}
                                <td class="px-4 py-3 max-w-[220px]">
                                    <p class="truncate text-slate-600 text-xs" title="{{ $reg->reason }}">
                                        {{ $reg->reason }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if ($reg->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu
                                        </span>
                                    @elseif ($reg->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi (Membuka Slide-Over Review Desk) --}}
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if ($reg->status === 'pending')
                                        <button
                                            type="button"
                                            @click="openReview({{ json_encode($detailPayload) }}, 'approve')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition shadow-xs"
                                        >
                                            <i data-lucide="file-pen" class="w-3.5 h-3.5"></i>
                                            <span>Tinjau</span>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            @click="openReview({{ json_encode($detailPayload) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition"
                                        >
                                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span>Detail</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                    <div class="h-10 w-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2.5">
                                        <i data-lucide="file-check" class="h-5 w-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">Tidak ada pengajuan koreksi kehadiran</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Semua data presensi telah terverifikasi dengan tepat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($regularizations->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $regularizations->links() }}
                </div>
            @endif
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 5. SLIDE-OVER REVIEW DESK (DRAWER KANAN MODERN)                   --}}
        {{-- ----------------------------------------------------------------- --}}
        {{-- Backdrop --}}
        <div
            x-show="drawerOpen"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeDrawer()"
            class="fixed inset-0 bg-slate-900/30 backdrop-blur-[1px] z-50 select-none"
            style="display: none;"
        ></div>

        {{-- Slide Drawer Container --}}
        <div
            x-show="drawerOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 w-full sm:w-[500px] bg-white border-l border-slate-200 shadow-xl flex flex-col justify-between overflow-hidden"
            style="display: none;"
        >
            {{-- Drawer Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-9 w-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        <span x-text="activeItem?.employee_initials"></span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 truncate" x-text="activeItem?.employee_name"></h3>
                        <p class="text-xs text-slate-500 truncate" x-text="activeItem?.employee_position + ' • ' + activeItem?.employee_department"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="activeItem?.status === 'pending'">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Menunggu
                        </span>
                    </template>
                    <template x-if="activeItem?.status === 'approved'">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Disetujui
                        </span>
                    </template>
                    <template x-if="activeItem?.status === 'rejected'">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            Ditolak
                        </span>
                    </template>

                    <button
                        type="button"
                        @click="closeDrawer()"
                        class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                        title="Tutup (ESC)"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Drawer Body (Scrollable) --}}
            <div class="p-5 overflow-y-auto space-y-4 flex-1">
                
                {{-- Waktu & Verifikasi Shift Grid --}}
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Informasi Penugasan &amp; Waktu
                    </p>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-3 bg-slate-50/70 rounded-lg border border-slate-100">
                            <span class="text-[11px] text-slate-400">Tanggal Shift</span>
                            <p class="font-semibold text-slate-900 mt-0.5" x-text="activeItem?.date"></p>
                        </div>

                        <div class="p-3 bg-slate-50/70 rounded-lg border border-slate-100">
                            <span class="text-[11px] text-slate-400">Nama Shift</span>
                            <p class="font-semibold text-slate-900 mt-0.5 truncate" x-text="activeItem?.shift_title"></p>
                        </div>

                        <div class="p-3 bg-slate-50/70 rounded-lg border border-slate-100">
                            <span class="text-[11px] text-slate-400">Clock-In Riil</span>
                            <p class="font-mono font-bold text-slate-900 mt-0.5" x-text="activeItem?.clock_in"></p>
                        </div>

                        <div class="p-3 bg-slate-50/70 rounded-lg border border-slate-100">
                            <span class="text-[11px] text-slate-400">Jadwal Selesai</span>
                            <p class="font-mono font-medium text-slate-600 mt-0.5" x-text="activeItem?.scheduled_out"></p>
                        </div>
                    </div>

                    {{-- Jam Pulang Riil Diajukan (Highlight) --}}
                    <div class="p-3.5 bg-purple-50/60 rounded-xl border border-purple-200/80 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[11px] text-purple-700 font-medium">Jam Pulang Riil Diajukan:</span>
                            <p class="font-mono font-bold text-base text-purple-900 mt-0.5" x-text="activeItem?.requested_out_full"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-purple-700 font-medium">Selisih Waktu:</span>
                            <p class="font-mono font-bold text-base text-purple-900 mt-0.5" x-text="'+ ' + activeItem?.overtime_str"></p>
                        </div>
                    </div>
                </div>

                {{-- Kotak Alasan Karyawan (Lengkap & Terbaca Jelas) --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold uppercase tracking-wider text-slate-500">Alasan dari Karyawan</span>
                        <span class="text-[11px] text-slate-400" x-text="'Diajukan: ' + activeItem?.created_at"></span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-wrap break-words min-h-[60px]">
                        <p x-text="activeItem?.reason"></p>
                    </div>
                </div>

                {{-- Status Review Jika Sudah Diproses --}}
                <template x-if="activeItem?.status !== 'pending'">
                    <div class="p-4 rounded-xl border space-y-2 text-xs"
                         :class="activeItem?.status === 'approved' ? 'bg-emerald-50/60 border-emerald-200 text-emerald-900' : 'bg-rose-50/60 border-rose-200 text-rose-900'">
                        <div class="flex items-center justify-between font-bold">
                            <span class="flex items-center gap-1.5">
                                <i :data-lucide="activeItem?.status === 'approved' ? 'check-circle' : 'x-circle'" class="w-4 h-4"></i>
                                <span x-text="activeItem?.status === 'approved' ? 'Pengajuan Telah Disetujui' : 'Pengajuan Telah Ditolak'"></span>
                            </span>
                            <span class="text-[11px] font-normal text-slate-500" x-text="activeItem?.reviewed_at"></span>
                        </div>

                        <div class="space-y-1 pt-1.5 border-t border-slate-200/60 text-slate-700">
                            <p>
                                Ditinjau oleh: <strong class="text-slate-900" x-text="activeItem?.reviewer_name"></strong>
                            </p>
                            <template x-if="activeItem?.overtime_status_label && activeItem?.overtime_status_label !== '-'">
                                <p>
                                    Status Lembur: <strong class="text-slate-900" x-text="activeItem?.overtime_status_label"></strong>
                                </p>
                            </template>
                            <template x-if="activeItem?.review_note">
                                <div class="mt-2 p-2.5 rounded-lg bg-white border border-slate-200/80">
                                    <strong class="block text-[11px] text-slate-400 mb-0.5">Catatan Reviewer:</strong>
                                    <span class="whitespace-pre-wrap break-words text-slate-800" x-text="activeItem?.review_note"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Decision Form (Khusus Pending) --}}
                <template x-if="activeItem?.status === 'pending'">
                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Keputusan Manajer
                            </span>

                            <div class="inline-flex p-0.5 rounded-lg bg-slate-100 text-xs">
                                <button
                                    type="button"
                                    @click="decisionTab = 'approve'"
                                    :class="decisionTab === 'approve' ? 'bg-white text-emerald-700 font-bold shadow-card' : 'text-slate-600'"
                                    class="px-3 py-1 rounded-md transition"
                                >
                                    Setujui
                                </button>
                                <button
                                    type="button"
                                    @click="decisionTab = 'reject'"
                                    :class="decisionTab === 'reject' ? 'bg-white text-rose-700 font-bold shadow-card' : 'text-slate-600'"
                                    class="px-3 py-1 rounded-md transition"
                                >
                                    Tolak
                                </button>
                            </div>
                        </div>

                        {{-- Form Setujui --}}
                        <div x-show="decisionTab === 'approve'" class="space-y-3">
                            <form :action="activeItem?.approve_url" method="POST" class="space-y-3">
                                @csrf

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-800">
                                        Kompensasi Lembur <span class="text-rose-500">*</span>
                                    </label>
                                    <select
                                        name="overtime_status"
                                        x-model="overtimeStatus"
                                        required
                                        class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 p-2.5 focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 font-medium text-slate-800"
                                    >
                                        <option value="approved">Setujui Sebagai Lembur (Dihitung Jam Lembur Resmi)</option>
                                        <option value="regular">Setujui Sebagai Jam Reguler (Kompensasi Jam Pengganti Keterlambatan)</option>
                                        <option value="none">Setujui Jam Riil Saja (Tanpa Kompensasi Lembur)</option>
                                        <option value="rejected">Tolak Klaim Lembur (Jam Di Luar Shift Hangus)</option>
                                    </select>
                                    <p class="text-[11px] text-slate-400">
                                        Mengacu pada plafon jam kerja PP 35 &amp; kebijakan lembur kantor.
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-800">
                                        Catatan Manajer (Opsional)
                                    </label>
                                    <textarea
                                        name="review_note"
                                        x-model="approveNote"
                                        rows="2"
                                        placeholder="Tambahkan catatan untuk staf jika diperlukan..."
                                        class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 p-2.5 focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 text-slate-800"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-1.5"
                                >
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Konfirmasi &amp; Setujui Koreksi</span>
                                </button>
                            </form>
                        </div>

                        {{-- Form Tolak --}}
                        <div x-show="decisionTab === 'reject'" class="space-y-3">
                            <form :action="activeItem?.reject_url" method="POST" class="space-y-3">
                                @csrf

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-800">
                                        Alasan Penolakan <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea
                                        name="review_note"
                                        x-model="rejectNote"
                                        rows="3"
                                        required
                                        minlength="3"
                                        placeholder="Tuliskan alasan mengapa klaim koreksi jam pulang ditolak..."
                                        class="w-full text-xs rounded-lg border border-slate-200 bg-slate-50 p-2.5 focus:bg-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 text-slate-800"
                                    ></textarea>
                                    <p class="text-[11px] text-slate-400">Minimal 3 karakter. Catatan akan dikirimkan ke staf.</p>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-1.5"
                                >
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    <span>Konfirmasi Tolak Pengajuan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Drawer Footer --}}
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs shrink-0">
                <span class="text-slate-400">Tekan ESC untuk menutup</span>
                <button
                    type="button"
                    @click="closeDrawer()"
                    class="font-semibold text-slate-700 hover:text-slate-900 transition"
                >
                    Tutup Laci
                </button>
            </div>
        </div>

    </div>
</x-app-layout>
