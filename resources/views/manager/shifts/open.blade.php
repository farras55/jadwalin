@php
    $statusMap = [
        'pending_manager' => ['Menunggu Manajer', 'bg-blue-100 text-blue-800 border-blue-200', 'clock'],
        'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200', 'check-circle'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-800 border-red-200', 'x-circle'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Kelola Shift Terbuka (Open Shift)
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Publikasikan kuota shift lembur/tambahan dan tinjau klaim dari staf berdasarkan urutan waktu.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    Manajer
                </span>
            </div>
        </div>
    </x-slot>

    <div 
        class="space-y-6" 
        x-data="{
            activeTab: 'pending',
            showBroadcastModal: false,
            broadcastSource: 'new',
            isGlobalManager: {{ $isGlobalManager ? 'true' : 'false' }},
            broadcastScope: 'department',
            filterDeptId: '',
            templates: {{ Js::from($templates) }},
            existingShifts: {{ Js::from($existingUnderstaffedShifts) }},
            selectedTemplateId: '',
            selectedExistingShiftId: '',
            selectedDepartmentId: '{{ $departments->first()?->id ?? '' }}',
            openSlots: 1,
            isExistingGlobal: false,
            startTime: '',
            endTime: '',
            get filteredExistingShifts() {
                if (!this.filterDeptId) return this.existingShifts;
                return this.existingShifts.filter(s => s.dept_id == this.filterDeptId);
            },
            get selectedExistingShift() {
                return this.existingShifts.find(s => s.id == this.selectedExistingShiftId) || null;
            },
            onExistingShiftChange() {
                if (this.selectedExistingShift) {
                    this.openSlots = this.selectedExistingShift.remaining_slots || 1;
                }
            },
            onTemplateChange() {
                const tmpl = this.templates.find(t => t.id == this.selectedTemplateId);
                if (tmpl) {
                    const today = new Date().toISOString().slice(0, 10);
                    this.startTime = today + 'T' + tmpl.start_time.slice(0, 5);
                    this.endTime = today + 'T' + tmpl.end_time.slice(0, 5);
                }
            }
        }"
    >
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-800 p-4 rounded-xl border border-red-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl border border-blue-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="h-5 w-5 text-blue-600 shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('info') }}</span>
                </div>
            </div>
        @endif

        {{-- Tabs Header & Action Button --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-brand-border rounded-xl shadow-2xs">
                <button 
                    type="button" 
                    @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="relative px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="clock" class="h-4 w-4"></i>
                    <span>Antrean Klaim</span>
                    @if ($pendingClaims->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'pending' ? 'bg-white text-[#6C5CE7]' : 'bg-[#6C5CE7] text-white'">
                            {{ $pendingClaims->count() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'active_shifts'"
                    :class="activeTab === 'active_shifts' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    <span>Open Shift Aktif</span>
                    @if ($openShifts->total() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'active_shifts' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                            {{ $openShifts->total() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="history" class="h-4 w-4"></i>
                    <span>Riwayat Keputusan</span>
                    @if ($claimHistory->total() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'history' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                            {{ $claimHistory->total() }}
                        </span>
                    @endif
                </button>
            </div>

            <button 
                type="button" 
                @click="showBroadcastModal = true"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm"
            >
                <i data-lucide="megaphone" class="h-4 w-4"></i>
                <span>Broadcast Open Shift</span>
            </button>
        </div>

        {{-- TAB 1: PENDING CLAIMS QUEUE (ORDERED BY MICROSECOND APPLIED_AT) --}}
        <div x-show="activeTab === 'pending'" class="space-y-4">
            @forelse ($pendingClaims as $claim)
                @php
                    $shift = $claim->shift;
                    $currentAssignments = $shift->shiftAssignments()->count();
                    $remaining = max(0, $shift->quota - $currentAssignments);
                @endphp
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs hover:border-[#6C5CE7]/30 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                                    Menunggu Persetujuan
                                </span>
                                <span class="text-xs font-medium text-slate-500">
                                    Waktu Pengajuan: <strong class="text-slate-700">{{ $claim->applied_at ? $claim->applied_at->format('H:i:s.u') : $claim->created_at->format('H:i:s') }}</strong>
                                </span>
                                <span class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    Sisa Kuota: {{ $remaining }} / {{ $shift->quota }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                {{-- Karyawan Pemohon --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <div class="flex items-center gap-2.5 mb-1.5">
                                        <div class="w-7 h-7 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] font-bold text-xs flex items-center justify-center">
                                            {{ substr($claim->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2A3E] text-xs leading-none">{{ $claim->user->name }}</p>
                                            <p class="text-[10px] text-[#7C7896] mt-0.5">{{ $claim->user->position?->name ?? 'Staf' }} &bull; {{ $claim->user->department?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-2">
                                        Email: <span class="text-slate-700 font-mono">{{ $claim->user->email }}</span>
                                    </p>
                                </div>

                                {{-- Shift yang Diminta --}}
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                                    <p class="text-xs font-bold text-[#2D2A3E]">{{ $shift->title }}</p>
                                    <p class="text-[11px] text-[#7C7896] mt-0.5">
                                        {{ $shift->start_time->translatedFormat('l, d M Y') }} &bull; {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-2">
                                        Departemen: 
                                        @if ($shift->is_global_broadcast)
                                            <span class="inline-flex items-center gap-1 text-purple-700 font-bold">
                                                <i data-lucide="globe" class="h-3 w-3"></i> Global Broadcast
                                            </span>
                                        @else
                                            <span class="text-slate-700 font-medium">{{ $shift->department?->name ?? '-' }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2.5 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <form method="POST" action="{{ route('manager.open-shifts.claims.reject', $claim) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menolak klaim ini?')"
                                >
                                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                    Tolak
                                </button>
                            </form>

                            <form method="POST" action="{{ route('manager.open-shifts.claims.approve', $claim) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui klaim ini? Jadwal akan langsung ditugaskan kepada staf ini secara atomik.')"
                                >
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                    Setujui & Tugaskan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="check-circle" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Tidak ada klaim yang menunggu persetujuan</p>
                    <p class="text-xs text-[#7C7896] mt-1">Saat ada staf yang mengajukan klaim untuk open shift mode approval, daftarnya akan masuk ke antrean ini sesuai mikrodetik pendaftaran.</p>
                </div>
            @endforelse
        </div>

        {{-- TAB 2: ACTIVE OPEN SHIFTS --}}
        <div x-show="activeTab === 'active_shifts'" class="space-y-4" style="display: none;">
            @if ($openShifts->isEmpty())
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="megaphone" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum ada open shift aktif</p>
                    <p class="text-xs text-[#7C7896] mt-1">Klik tombol "Broadcast Open Shift" di kanan atas untuk mempublikasikan kuota shift baru ke tim.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($openShifts as $shift)
                        @php
                            $filledCount = $shift->shift_assignments_count;
                            $quota = $shift->quota;
                            $percentage = $quota > 0 ? min(100, round(($filledCount / $quota) * 100)) : 0;
                            $isInstant = !$shift->requires_approval;
                            $remaining = max(0, $quota - $filledCount);
                        @endphp
                        <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    @if ($isInstant)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="zap" class="h-3 w-3"></i> Instant FCFS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="shield-alert" class="h-3 w-3"></i> Butuh Approval
                                        </span>
                                    @endif

                                    <span class="text-xs font-bold text-slate-500">
                                        {{ $remaining }} slot tersisa
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4 class="font-bold text-[#2D2A3E] text-sm">{{ $shift->title }}</h4>
                                        @if ($shift->is_global_broadcast)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i data-lucide="globe" class="h-3 w-3"></i> Global Broadcast
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#7C7896]">
                                        @if ($shift->is_global_broadcast)
                                            Semua Departemen {{ $shift->department ? '('.$shift->department->name.')' : '' }}
                                        @else
                                            {{ $shift->department?->name ?? 'Departemen' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="text-xs text-slate-600 bg-slate-50 rounded-xl p-2.5 border border-slate-200/80">
                                    <p class="font-medium">{{ $shift->start_time->translatedFormat('d M Y') }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</p>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">Terisi: {{ $filledCount }} / {{ $quota }} Slot</span>
                                        <span class="font-semibold text-slate-700">{{ $percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full bg-[#6C5CE7]" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t border-slate-100">
                                <form method="POST" action="{{ route('manager.open-shifts.close', $shift) }}">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition"
                                        onclick="return confirm('Apakah Anda yakin ingin menutup pendaftaran shift terbuka ini?')"
                                    >
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                        Tutup Broadcast
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $openShifts->links() }}
                </div>
            @endif
        </div>

        {{-- TAB 3: RIWAYAT KEPUTUSAN --}}
        <div x-show="activeTab === 'history'" class="space-y-4" style="display: none;">
            @forelse ($claimHistory as $claim)
                @php
                    $shift = $claim->shift;
                    [$statusLabel, $statusClass, $statusIcon] = $statusMap[$claim->status] ?? [$claim->status, 'bg-slate-100 text-slate-700 border-slate-200', 'clock'];
                @endphp
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="h-3 w-3"></i>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $shift->title }} ({{ $shift->start_time->translatedFormat('d M Y, H:i') }})
                                </span>
                            </div>

                            <p class="text-sm font-bold text-[#2D2A3E]">
                                {{ $claim->user->name }}
                                <span class="text-xs font-normal text-slate-400">({{ $claim->user->position?->name ?? 'Staf' }})</span>
                            </p>

                            <p class="text-xs text-slate-400">
                                Diajukan: {{ $claim->applied_at ? $claim->applied_at->format('d M Y, H:i:s.u') : '-' }} &bull;
                                Ditinjau oleh: {{ $claim->approvedBy?->name ?? 'Sistem FCFS' }}
                            </p>
                        </div>

                        <div class="text-xs text-right text-slate-400">
                            {{ $claim->updated_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum ada riwayat keputusan klaim</p>
                    <p class="text-xs text-[#7C7896] mt-1">Daftar klaim yang telah disetujui atau ditolak akan tersimpan di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $claimHistory->links() }}
            </div>
        </div>

        {{-- MODAL BROADCAST OPEN SHIFT BARU --}}
        <div 
            x-show="showBroadcastModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showBroadcastModal = false"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-brand-border space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-[#2D2A3E]">Broadcast Open Shift Dadakan</h3>
                            <p class="text-xs text-[#7C7896] mt-0.5">Buka alokasi kuota shift tambahan untuk staf departemen.</p>
                        </div>
                        <button type="button" @click="showBroadcastModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('manager.open-shifts.broadcast') }}" class="space-y-4">
                        @csrf

                        {{-- 1. Pilihan Sumber Open Shift (Shift Baru vs Shift Kurang Staf) --}}
                        <div>
                            <label class="block text-xs font-bold text-[#2D2A3E] uppercase tracking-wider mb-2">
                                Tipe Open Shift: <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="broadcastSource === 'new' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 font-semibold text-[#6C5CE7]' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="plus-circle" class="h-4 w-4"></i>
                                            <span class="text-xs">Shift Tambahan Baru</span>
                                        </div>
                                        <input type="radio" name="broadcast_source" value="new" x-model="broadcastSource" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="broadcastSource === 'new' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="broadcastSource === 'new'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">Buat jadwal shift dadakan / lembur baru.</p>
                                </label>

                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="broadcastSource === 'existing' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 font-semibold text-[#6C5CE7]' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="alert-circle" class="h-4 w-4 text-amber-500"></i>
                                            <span class="text-xs">Shift Kurang Staf</span>
                                        </div>
                                        <input type="radio" name="broadcast_source" value="existing" x-model="broadcastSource" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="broadcastSource === 'existing' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="broadcastSource === 'existing'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">
                                        Buka slot dari shift terjadwal ({{ $existingUnderstaffedShifts->count() }} tersedia).
                                    </p>
                                </label>
                            </div>
                        </div>

                        {{-- ======================================================== --}}
                        {{-- OPSI A: SHIFT JADWAL YANG KEKURANGAN STAF                --}}
                        {{-- ======================================================== --}}
                        <div x-show="broadcastSource === 'existing'" class="space-y-4" style="display: none;">
                            @if ($existingUnderstaffedShifts->isEmpty())
                                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3.5 text-xs">
                                    <div class="flex items-start gap-2.5">
                                        <i data-lucide="info" class="h-4 w-4 text-amber-600 shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="font-bold">Tidak ada shift terjadwal yang kekurangan staf</p>
                                            <p class="text-[11px] text-amber-700 mt-0.5">Semua shift terjadwal mendatang saat ini sudah memiliki staf lengkap. Silakan pilih opsi "Shift Tambahan Baru" jika ingin menambah shift baru.</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="existing_shift_id" class="block text-xs font-semibold text-[#2D2A3E]">
                                            Pilih Shift yang Kekurangan Karyawan: <span class="text-red-500">*</span>
                                        </label>

                                        @if ($isGlobalManager)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] text-slate-500">Filter:</span>
                                                <select 
                                                    id="filter_dept" 
                                                    x-model="filterDeptId"
                                                    @change="selectedExistingShiftId = ''"
                                                    class="text-[11px] rounded-lg border border-slate-300 py-0.5 px-2 bg-white text-slate-700 focus:border-[#6C5CE7]"
                                                >
                                                    <option value="">Semua Dept ({{ $existingUnderstaffedShifts->count() }})</option>
                                                    @foreach ($departments as $dept)
                                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>

                                    <select 
                                        id="existing_shift_id" 
                                        name="existing_shift_id" 
                                        x-model="selectedExistingShiftId"
                                        @change="onExistingShiftChange()"
                                        :disabled="broadcastSource !== 'existing'"
                                        :required="broadcastSource === 'existing'"
                                        class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                    >
                                        <option value="" disabled selected>-- Pilih Shift Terjadwal --</option>
                                        <template x-for="s in filteredExistingShifts" :key="s.id">
                                            <option :value="s.id" x-text="s.formatted_option"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Preview Card Shift Terpilih --}}
                                <template x-if="selectedExistingShift">
                                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-3.5 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-[#2D2A3E] text-xs" x-text="selectedExistingShift.title"></p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Kurang <span x-text="selectedExistingShift.remaining_slots" class="mx-0.5"></span> Staf
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-[#7C7896]">
                                            <span x-text="selectedExistingShift.department ? selectedExistingShift.department.name : 'Lintas Departemen'"></span> &bull;
                                            Waktu: <span class="font-medium text-slate-700" x-text="selectedExistingShift.start_time.substring(0, 16) + ' s/d ' + selectedExistingShift.end_time.substring(11, 16)"></span>
                                        </p>
                                        <p class="text-[11px] text-slate-600">
                                            Staf terisi saat ini: <strong x-text="selectedExistingShift.shift_assignments_count"></strong> / <strong x-text="selectedExistingShift.quota"></strong> kuota total.
                                        </p>
                                    </div>
                                </template>

                                <div>
                                    <label for="open_slots" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                        Jumlah Slot Terbuka yang Dibutuhkan untuk Diisi: <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="open_slots" 
                                        name="open_slots" 
                                        min="1" 
                                        max="50" 
                                        x-model.number="openSlots"
                                        :disabled="broadcastSource !== 'existing'"
                                        placeholder="Contoh: 2"
                                        class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100 disabled:text-slate-400"
                                    >
                                    <template x-if="selectedExistingShift">
                                        <div class="mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                            <span>
                                                Staf terjadwal saat ini: <strong class="text-slate-800" x-text="selectedExistingShift.shift_assignments_count"></strong> orang + 
                                                Slot terbuka: <strong class="text-[#6C5CE7]" x-text="openSlots || 0"></strong> slot
                                            </span>
                                            <span class="font-bold text-[#2D2A3E]">
                                                Kuota total menjadi: <span class="text-[#6C5CE7]" x-text="parseInt(selectedExistingShift.shift_assignments_count || 0) + parseInt(openSlots || 0)"></span> staf
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                @if ($isGlobalManager)
                                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-purple-50/60 border border-purple-200/80">
                                        <input 
                                            type="checkbox" 
                                            id="is_global_broadcast_existing" 
                                            name="is_global_broadcast" 
                                            value="1" 
                                            x-model="isExistingGlobal"
                                            :disabled="broadcastSource !== 'existing'"
                                            class="rounded border-purple-300 text-purple-600 focus:ring-purple-500 mt-0.5"
                                        >
                                        <label for="is_global_broadcast_existing" class="text-xs text-purple-900 cursor-pointer">
                                            <span class="font-bold flex items-center gap-1.5">
                                                <i data-lucide="globe" class="h-3.5 w-3.5 text-purple-600"></i>
                                                Buka sebagai Global Broadcast
                                            </span>
                                            <p class="text-[11px] text-purple-700 mt-0.5">
                                                Izinkan staf dari departemen lain ikut mengklaim slot kosong shift ini (solusi cepat saat kekurangan personel di departemen tersebut).
                                            </p>
                                        </label>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- ======================================================== --}}
                        {{-- OPSI B: FORM BUAT SHIFT TAMBAHAN / BARU                  --}}
                        {{-- ======================================================== --}}
                        <div x-show="broadcastSource === 'new'" class="space-y-4">
                            {{-- Judul Shift --}}
                            <div>
                                <label for="title" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                    Judul Shift: <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    :disabled="broadcastSource !== 'new'"
                                    :required="broadcastSource === 'new'"
                                    placeholder="Contoh: Shift Tambahan Operasional Weekend"
                                    class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100 disabled:text-slate-400"
                                >
                            </div>

                            {{-- Pilihan Jangkauan & Departemen --}}
                            @if ($isGlobalManager)
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-[#2D2A3E]">
                                        Jangkauan & Departemen: <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <label 
                                            class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition text-xs"
                                            :class="broadcastScope === 'department' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 font-semibold text-[#6C5CE7]' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                        >
                                            <input type="radio" name="broadcast_scope" value="department" x-model="broadcastScope" class="text-[#6C5CE7] focus:ring-[#6C5CE7]">
                                            <div class="flex flex-col">
                                                <span class="flex items-center gap-1.5 font-medium">
                                                    <i data-lucide="building-2" class="h-3.5 w-3.5"></i>
                                                    Departemen Tertentu
                                                </span>
                                                <span class="text-[10px] text-slate-500 font-normal">Hanya staf departemen terpilih</span>
                                            </div>
                                        </label>

                                        <label 
                                            class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition text-xs"
                                            :class="broadcastScope === 'global' ? 'border-purple-600 bg-purple-50 font-semibold text-purple-700' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                        >
                                            <input type="radio" name="broadcast_scope" value="global" x-model="broadcastScope" class="text-purple-600 focus:ring-purple-500">
                                            <div class="flex flex-col">
                                                <span class="flex items-center gap-1.5 font-medium">
                                                    <i data-lucide="globe" class="h-3.5 w-3.5 text-purple-600"></i>
                                                    Global Broadcast
                                                </span>
                                                <span class="text-[10px] text-purple-600/80 font-normal">Semua karyawan perusahaan</span>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Dropdown Pilih Departemen Spesifik --}}
                                    <div x-show="broadcastScope === 'department'" class="pt-1">
                                        <label for="department_id" class="block text-[11px] font-medium text-slate-600 mb-1">
                                            Pilih Departemen:
                                        </label>
                                        <select 
                                            id="department_id" 
                                            name="department_id" 
                                            x-model="selectedDepartmentId"
                                            :disabled="broadcastSource !== 'new' || broadcastScope === 'global'"
                                            :required="broadcastSource === 'new' && broadcastScope === 'department'"
                                            class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                        >
                                            <option value="" disabled>-- Pilih Departemen --</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Banner Informasi Global Broadcast --}}
                                    <div x-show="broadcastScope === 'global'" class="bg-purple-50/70 border border-purple-200 rounded-xl p-3 text-xs text-purple-800 space-y-1" style="display: none;">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <i data-lucide="sparkles" class="h-3.5 w-3.5 text-purple-600"></i>
                                            <span>Shift Terbuka untuk Seluruh Departemen</span>
                                        </div>
                                        <p class="text-[11px] text-purple-700">
                                            Shift ini akan dipublikasikan secara global dan dapat diklaim oleh semua karyawan aktif dari departemen apapun.
                                        </p>
                                        <input type="hidden" name="is_global_broadcast" value="1" :disabled="broadcastSource !== 'new' || broadcastScope !== 'global'">
                                        <input type="hidden" name="department_id" value="global" :disabled="broadcastSource !== 'new' || broadcastScope !== 'global'">
                                    </div>
                                </div>
                            @else
                                {{-- Manager Departemen: Terkunci ke Departemennya Sendiri --}}
                                <div>
                                    <label class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                        Departemen:
                                    </label>
                                    <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-xs">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="building-2" class="h-4 w-4 text-slate-400"></i>
                                            <span class="font-semibold">{{ $departments->firstWhere('id', auth()->user()->department_id)?->name ?? 'Departemen Anda' }}</span>
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 font-medium bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                            <i data-lucide="lock" class="h-3 w-3 text-slate-400"></i>
                                            Terkunci
                                        </span>
                                    </div>
                                    <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}" :disabled="broadcastSource !== 'new'">
                                </div>
                            @endif

                            {{-- Template Shift (Opsional) --}}
                            <div>
                                <label for="template_id" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                    Gunakan Jam dari Template (Opsional):
                                </label>
                                <select 
                                    id="template_id" 
                                    name="template_id" 
                                    x-model="selectedTemplateId"
                                    @change="onTemplateChange()"
                                    :disabled="broadcastSource !== 'new'"
                                    class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] bg-white disabled:bg-slate-100 disabled:text-slate-400"
                                >
                                    <option value="">-- Atur Jam Manual --</option>
                                    @foreach ($templates as $tmpl)
                                        <option value="{{ $tmpl->id }}">
                                            {{ $tmpl->title }} ({{ substr($tmpl->start_time, 0, 5) }} - {{ substr($tmpl->end_time, 0, 5) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Waktu Mulai & Selesai --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="start_time" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                        Waktu Mulai: <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        id="start_time" 
                                        name="start_time" 
                                        x-model="startTime"
                                        :disabled="broadcastSource !== 'new'"
                                        :required="broadcastSource === 'new'"
                                        class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100 disabled:text-slate-400"
                                    >
                                </div>

                                <div>
                                    <label for="end_time" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                        Waktu Selesai: <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        id="end_time" 
                                        name="end_time" 
                                        x-model="endTime"
                                        :disabled="broadcastSource !== 'new'"
                                        :required="broadcastSource === 'new'"
                                        class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100 disabled:text-slate-400"
                                    >
                                </div>
                            </div>

                            {{-- Kuota Slot --}}
                            <div>
                                <label for="quota" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                                    Kuota Staf Dibutuhkan: <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="quota" 
                                    name="quota" 
                                    min="1" 
                                    max="100" 
                                    value="2" 
                                    :disabled="broadcastSource !== 'new'"
                                    :required="broadcastSource === 'new'"
                                    class="w-full text-xs md:text-sm rounded-xl border border-slate-300 px-3 py-2.5 focus:border-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100 disabled:text-slate-400"
                                >
                            </div>

                            {{-- Hari Libur / Tanggal Merah --}}
                            <div class="flex items-center gap-2 pt-1">
                                <input 
                                    type="checkbox" 
                                    id="is_holiday" 
                                    name="is_holiday" 
                                    value="1" 
                                    :disabled="broadcastSource !== 'new'"
                                    class="rounded border-slate-300 text-[#6C5CE7] focus:ring-[#6C5CE7] disabled:bg-slate-100"
                                >
                                <label for="is_holiday" class="text-xs text-slate-600">
                                    Tandai sebagai hari libur / tanggal merah (tarif lembur)
                                </label>
                            </div>
                        </div>

                        {{-- ======================================================== --}}
                        {{-- FIELD BERSAMA: METODE ALOKASI KLAIM                      --}}
                        {{-- ======================================================== --}}
                        <div>
                            <label class="block text-xs font-bold text-[#2D2A3E] uppercase tracking-wider mb-2">
                                Metode Alokasi Klaim: <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ mode: '0' }">
                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="mode === '0' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 font-semibold text-[#6C5CE7]' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="zap" class="h-4 w-4"></i>
                                            <span class="text-xs">Instant FCFS</span>
                                        </div>
                                        <input type="radio" name="requires_approval" value="0" x-model="mode" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="mode === '0' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="mode === '0'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">Siapa cepat langsung otomatis masuk jadwal.</p>
                                </label>

                                <label 
                                    class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="mode === '1' ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 font-semibold text-[#6C5CE7]' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="shield-alert" class="h-4 w-4"></i>
                                            <span class="text-xs">Butuh Approval</span>
                                        </div>
                                        <input type="radio" name="requires_approval" value="1" x-model="mode" class="hidden">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                              :class="mode === '1' ? 'border-[#6C5CE7] bg-[#6C5CE7]' : 'border-slate-300'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="mode === '1'"></span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal mt-1.5">Klaim ditampung & dipilih manual manajer.</p>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="showBroadcastModal = false"
                                class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-brand-primary hover:bg-brand-hover shadow-xs transition"
                            >
                                Publikasikan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
