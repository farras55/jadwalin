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
                    Shift Terbuka (Open Shift)
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Klaim jadwal shift tambahan atau lembur terbuka secara mandiri sebelum kuota terpenuhi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="megaphone" class="h-3.5 w-3.5"></i>
                    Open Shift
                </span>
            </div>
        </div>
    </x-slot>

    <div 
        class="space-y-6" 
        x-data="{
            activeTab: 'available',
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

        {{-- Tabs Header --}}
        <div class="flex items-center justify-between gap-4">
            <div class="inline-flex p-1 bg-white border border-brand-border rounded-xl shadow-2xs">
                <button 
                    type="button" 
                    @click="activeTab = 'available'"
                    :class="activeTab === 'available' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="relative px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    <span>Shift Tersedia</span>
                    @if ($availableShifts->count() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'available' ? 'bg-white text-[#6C5CE7]' : 'bg-[#6C5CE7] text-white'">
                            {{ $availableShifts->count() }}
                        </span>
                    @endif
                </button>

                <button 
                    type="button" 
                    @click="activeTab = 'my_claims'"
                    :class="activeTab === 'my_claims' ? 'bg-[#6C5CE7] text-white shadow-xs' : 'text-[#7C7896] hover:text-[#2D2A3E]'"
                    class="px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all flex items-center gap-2"
                >
                    <i data-lucide="history" class="h-4 w-4"></i>
                    <span>Riwayat Klaim Saya</span>
                    @if ($myClaims->total() > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'my_claims' ? 'bg-white text-[#6C5CE7]' : 'bg-slate-200 text-slate-700'">
                            {{ $myClaims->total() }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        {{-- TAB 1: AVAILABLE OPEN SHIFTS --}}
        <div x-show="activeTab === 'available'" class="space-y-4">
            @if ($availableShifts->isEmpty())
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="inbox" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Tidak ada shift terbuka saat ini</p>
                    <p class="text-xs text-[#7C7896] mt-1">Saat manajer mempublikasikan shift tambahan atau lembur dadakan, daftarnya akan muncul di sini untuk Anda klaim.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($availableShifts as $shift)
                        @php
                            $filledCount = $shift->shift_assignments_count;
                            $quota = $shift->quota;
                            $percentage = $quota > 0 ? min(100, round(($filledCount / $quota) * 100)) : 0;
                            $isInstant = !$shift->requires_approval;
                            $remaining = max(0, $quota - $filledCount);
                        @endphp
                        <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs hover:border-[#6C5CE7]/40 hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="space-y-3.5">
                                {{-- Badges Header --}}
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    @if ($isInstant)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="zap" class="h-3 w-3"></i>
                                            Instant FCFS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="shield-alert" class="h-3 w-3"></i>
                                            Butuh Approval
                                        </span>
                                    @endif

                                    @if ($shift->is_holiday)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-red-50 text-red-600 border border-red-200">
                                            Hari Libur
                                        </span>
                                    @endif
                                </div>

                                {{-- Shift Details --}}
                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="font-bold text-[#2D2A3E] text-base leading-tight">{{ $shift->title }}</h3>
                                        @if ($shift->is_global_broadcast)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i data-lucide="globe" class="h-3 w-3"></i> Global Broadcast
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#7C7896] mt-0.5">
                                        @if ($shift->is_global_broadcast)
                                            <span class="text-purple-700 font-semibold">Semua Departemen</span> {{ $shift->department ? '('.$shift->department->name.')' : '' }}
                                        @else
                                            {{ $shift->department?->name ?? 'Departemen' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs text-[#2D2A3E]">
                                        <i data-lucide="calendar" class="h-3.5 w-3.5 text-[#6C5CE7] shrink-0"></i>
                                        <span class="font-semibold">{{ $shift->start_time->translatedFormat('l, d M Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i data-lucide="clock" class="h-3.5 w-3.5 text-slate-400 shrink-0"></i>
                                        <span>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }} ({{ round($shift->start_time->diffInMinutes($shift->end_time) / 60, 1) }} jam)</span>
                                    </div>
                                </div>

                                {{-- Progress Kuota --}}
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-slate-600">Ketersediaan Slot</span>
                                        <span class="font-bold text-[#2D2A3E]">
                                            <span class="text-emerald-600">{{ $remaining }} slot</span> tersisa / {{ $quota }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div 
                                            class="h-2 rounded-full transition-all duration-300 {{ $percentage >= 80 ? 'bg-amber-500' : 'bg-[#6C5CE7]' }}" 
                                            style="width: {{ $percentage }}%"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <div class="pt-4 mt-4 border-t border-slate-100">
                                <form method="POST" action="{{ route('employee.open-shifts.claim', $shift) }}">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold text-white bg-[#6C5CE7] hover:bg-[#5A4AD1] shadow-xs transition"
                                        onclick="return confirm('Apakah Anda yakin ingin mengklaim shift ini? {{ $isInstant ? 'Jadwal akan langsung ditugaskan kepada Anda secara instan.' : 'Pengajuan akan dikirim ke manajer untuk persetujuan.' }}')"
                                    >
                                        <i data-lucide="user-plus" class="h-4 w-4"></i>
                                        <span>Klaim Shift Ini</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 2: MY CLAIMS HISTORY --}}
        <div x-show="activeTab === 'my_claims'" class="space-y-4" style="display: none;">
            @forelse ($myClaims as $claim)
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
                                @if (!$shift->requires_approval)
                                    <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                        <i data-lucide="zap" class="h-3 w-3"></i> Instant FCFS
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm font-bold text-[#2D2A3E]">
                                {{ $shift->title }}
                            </p>

                            <p class="text-xs text-slate-500">
                                Jadwal: <span class="font-medium text-slate-700">{{ $shift->start_time->translatedFormat('d M Y, H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                &bull; Departemen: {{ $shift->is_global_broadcast ? 'Global Broadcast' : ($shift->department?->name ?? '-') }}
                            </p>

                            @if ($claim->approved_by)
                                <p class="text-[11px] text-slate-400">
                                    Ditinjau oleh {{ $claim->approvedBy?->name ?? 'Manajer' }}
                                </p>
                            @endif
                        </div>

                        <div class="text-xs text-right text-slate-400">
                            Diajukan: {{ $claim->applied_at ? $claim->applied_at->translatedFormat('d M Y, H:i:s') : $claim->created_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="history" class="h-6 w-6"></i>
                    </div>
                    <p class="font-bold text-[#2D2A3E]">Belum ada riwayat klaim</p>
                    <p class="text-xs text-[#7C7896] mt-1">Seluruh pengajuan klaim open shift yang Anda lakukan akan tercatat di sini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $myClaims->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
