<x-app-layout>
    <div class="min-h-screen py-6 space-y-6">
        {{-- Header & Breadcrumbs --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-gray-500 font-medium mb-1">
                    <a href="{{ route('employee.attendance') }}" class="hover:text-[#6C5CE7] transition-colors">Presensi</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span class="text-[#6C5CE7] font-semibold">Riwayat Koreksi Kehadiran</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Riwayat Koreksi Kehadiran
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Daftar seluruh permohonan koreksi jam pulang riil yang Anda ajukan akibat penutupan shift otomatis (Auto Clock-Out).
                </p>
            </div>
            <div>
                <a href="{{ route('employee.attendance') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs md:text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:border-[#6C5CE7]/40 hover:text-[#6C5CE7] shadow-2xs transition-all">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Presensi</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 text-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Table Container --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl/5 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7]">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Daftar Pengajuan Koreksi</h2>
                        <p class="text-xs text-gray-400">Total {{ $regularizations->total() }} pengajuan tercatat di sistem</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                    <thead class="bg-slate-50/80 text-gray-700 font-bold border-b border-gray-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal & Shift</th>
                            <th class="px-5 py-3.5">Departemen</th>
                            <th class="px-5 py-3.5">Jam Pulang Resmi</th>
                            <th class="px-5 py-3.5">Jam Pulang Diajukan</th>
                            <th class="px-5 py-3.5">Alasan Pengajuan</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Catatan Reviewer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($regularizations as $reg)
                            @php
                                $assignment = $reg->shiftAssignment;
                                $shift = $assignment?->shift;
                                $dept = $shift?->department?->name ?? 'Operasional';
                                $shiftDate = $shift ? \Carbon\Carbon::parse($shift->start_time)->translatedFormat('d M Y') : '-';
                                $shiftTitle = $shift->title ?? 'Shift Regular';
                                $officialClockOut = $assignment?->clock_out_time ? \Carbon\Carbon::parse($assignment->clock_out_time)->format('H:i') . ' WIB' : '-';
                                $requestedOut = $reg->requested_clock_out_time ? \Carbon\Carbon::parse($reg->requested_clock_out_time)->format('d M Y, H:i') . ' WIB' : '-';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $shiftDate }}</div>
                                    <div class="text-[11px] text-gray-400 font-medium">{{ $shiftTitle }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-gray-600">
                                    {{ $dept }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-gray-700">
                                    {{ $officialClockOut }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono font-semibold text-gray-900">
                                    {{ $requestedOut }}
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="text-gray-700 line-clamp-2" title="{{ $reg->reason }}">{{ $reg->reason }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($reg->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            Disetujui
                                        </span>
                                    @elseif ($reg->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                            Menunggu Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    @if ($reg->reviewed_at)
                                        <div class="text-xs text-gray-700">
                                            <div class="font-medium text-gray-900">Oleh: {{ $reg->reviewer?->name ?? 'Manajer' }}</div>
                                            <p class="text-gray-500 italic mt-0.5 line-clamp-2" title="{{ $reg->review_note }}">{{ $reg->review_note ?: '-' }}</p>
                                            <span class="text-[10px] text-gray-400 font-mono">{{ $reg->reviewed_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Belum ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="file-check-2" class="w-10 h-10 text-gray-300"></i>
                                        <p class="text-sm font-semibold text-gray-600">Belum ada pengajuan koreksi kehadiran</p>
                                        <p class="text-xs text-gray-400 max-w-md">Jika Anda memiliki shift yang ditutup otomatis oleh sistem (Auto Clock-Out) dan lembur belum tercatat, Anda dapat mengajukan koreksi dari halaman Presensi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($regularizations->hasPages())
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-slate-50/50">
                    {{ $regularizations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
