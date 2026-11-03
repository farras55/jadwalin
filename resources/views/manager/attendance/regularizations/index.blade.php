<x-app-layout>
    <div 
        class="min-h-screen py-6 space-y-6"
        x-data="{
            showApproveModal: false,
            showRejectModal: false,
            activeRegularization: null,
            approveUrl: '',
            rejectUrl: '',
            overtimeStatus: 'approved',
            approveNote: '',
            rejectNote: '',

            openApproveModal(reg) {
                this.activeRegularization = reg;
                this.approveUrl = '{{ url('/manager/attendance/regularizations') }}/' + reg.id + '/approve';
                this.overtimeStatus = 'approved';
                this.approveNote = '';
                this.showApproveModal = true;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
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
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            closeRejectModal() {
                this.showRejectModal = false;
                this.activeRegularization = null;
                this.rejectUrl = '';
            }
        }"
        @keydown.escape.window="closeApproveModal(); closeRejectModal()"
    >
        {{-- Header Page --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-gray-500 font-medium mb-1">
                    <span class="hover:text-gray-700">Manajer</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span class="hover:text-gray-700">Presensi</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span class="text-[#6C5CE7] font-semibold">Persetujuan Koreksi Kehadiran</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Persetujuan Koreksi Kehadiran
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Tinjau dan verifikasi permohonan koreksi jam pulang riil dari karyawan yang terkena auto clock-out.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-600">Menunggu Persetujuan:</span>
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-200">
                    {{ $counts['pending'] ?? 0 }}
                </span>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl border border-emerald-200 shadow-xs flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-200 shadow-xs flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-200 shadow-xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-sm">
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

        {{-- Filter Tabs --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-2">
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'pending']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'pending' ? 'bg-[#0B3B3C] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                <span>Menunggu ({{ $counts['pending'] ?? 0 }})</span>
            </a>
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'approved']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'approved' ? 'bg-[#0B3B3C] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                <span>Disetujui ({{ $counts['approved'] ?? 0 }})</span>
            </a>
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'rejected']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'rejected' ? 'bg-[#0B3B3C] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                <span>Ditolak ({{ $counts['rejected'] ?? 0 }})</span>
            </a>
            <a href="{{ route('manager.attendance.regularizations.index', ['status' => 'all']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-[#0B3B3C] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                <span>Semua ({{ $counts['all'] ?? 0 }})</span>
            </a>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-slate-50/80 text-gray-700 font-bold border-b border-gray-200 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Karyawan</th>
                            <th class="px-5 py-3.5">Shift &amp; Tanggal</th>
                            <th class="px-5 py-3.5">Waktu Shift Resmi</th>
                            <th class="px-5 py-3.5">Jam Pulang Diajukan</th>
                            <th class="px-5 py-3.5">Estimasi Lembur</th>
                            <th class="px-5 py-3.5">Alasan Karyawan</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($regularizations as $reg)
                            @php
                                $shift = $reg->shiftAssignment?->shift;
                                $clockIn = $reg->shiftAssignment?->clock_in_time ? \Carbon\Carbon::parse($reg->shiftAssignment->clock_in_time) : null;
                                $shiftEnd = $shift ? \App\Console\Commands\AutoClockOutCommand::resolveShiftEndDateTime($shift) : null;
                                $requestedOut = $reg->requested_clock_out_time ? \Carbon\Carbon::parse($reg->requested_clock_out_time) : null;

                                // Hitung durasi selisih lembur (requestedOut - shiftEnd)
                                $overtimeMinutes = ($requestedOut && $shiftEnd && $requestedOut->greaterThan($shiftEnd))
                                    ? $shiftEnd->diffInMinutes($requestedOut)
                                    : 0;
                                $ovtHours = floor($overtimeMinutes / 60);
                                $ovtRemMins = $overtimeMinutes % 60;
                                $ovtStr = $ovtHours > 0
                                    ? "{$ovtHours} Jam " . str_pad($ovtRemMins, 2, '0', STR_PAD_LEFT) . " Menit"
                                    : "{$ovtRemMins} Menit";
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                {{-- Karyawan --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $reg->employee?->name ?? 'Karyawan' }}</div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $reg->employee?->position?->name ?? 'Staff' }} • {{ $reg->employee?->department?->name ?? '-' }}
                                    </div>
                                </td>

                                {{-- Shift & Tanggal --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">{{ $shift?->title ?? 'Shift Regular' }}</div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->translatedFormat('d M Y') : '-' }}
                                    </div>
                                </td>

                                {{-- Waktu Shift Resmi --}}
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-[11.5px]">
                                    <div>In: {{ $clockIn ? $clockIn->format('H:i') : '-' }} WIB</div>
                                    <div class="text-gray-400 text-[10.5px]">Shift End: {{ $shiftEnd ? $shiftEnd->format('H:i') : '-' }} WIB</div>
                                </td>

                                {{-- Jam Pulang Diajukan --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-mono font-bold text-gray-900 text-[12px]">
                                        {{ $requestedOut ? $requestedOut->format('H:i') : '-' }} WIB
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $requestedOut ? $requestedOut->translatedFormat('d M Y') : '' }}
                                    </div>
                                </td>

                                {{-- Estimasi Lembur --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[11px] border border-blue-200">
                                        +{{ $ovtStr }}
                                    </span>
                                </td>

                                {{-- Alasan Karyawan --}}
                                <td class="px-5 py-4 max-w-xs">
                                    <p class="text-xs text-gray-700 leading-relaxed truncate hover:whitespace-normal" title="{{ $reg->reason }}">
                                        {{ $reg->reason }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($reg->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                    @elseif ($reg->status === 'approved')
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                                Disetujui
                                            </span>
                                            @if ($reg->overtime_status === 'approved')
                                                <div class="text-[10px] text-emerald-600 font-semibold">• Lembur Disetujui</div>
                                            @else
                                                <div class="text-[10px] text-slate-500 font-medium">• Tanpa Lembur</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i data-lucide="x" class="w-3 h-3 text-rose-600"></i>
                                                Ditolak
                                            </span>
                                            @if ($reg->review_note)
                                                <p class="text-[10px] text-rose-500 max-w-[140px] truncate" title="{{ $reg->review_note }}">
                                                    {{ $reg->review_note }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($reg->status === 'pending')
                                        <div class="inline-flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                @click="openApproveModal({{ json_encode([
                                                    'id' => $reg->id,
                                                    'employee_name' => $reg->employee?->name,
                                                    'shift_title' => $shift?->title,
                                                    'requested_time' => $requestedOut ? $requestedOut->format('d/m/Y H:i') : '-',
                                                    'overtime_str' => $ovtStr,
                                                    'reason' => $reg->reason
                                                ]) }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition"
                                                title="Setujui Koreksi Kehadiran"
                                            >
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Setujui</span>
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="openRejectModal({{ json_encode([
                                                    'id' => $reg->id,
                                                    'employee_name' => $reg->employee?->name,
                                                    'shift_title' => $shift?->title,
                                                    'reason' => $reg->reason
                                                ]) }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition"
                                                title="Tolak Koreksi Kehadiran"
                                            >
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="file-check" class="w-10 h-10 text-gray-300"></i>
                                        <p class="text-sm font-semibold text-gray-700">Tidak ada pengajuan koreksi kehadiran.</p>
                                        <p class="text-xs text-gray-400">Semua permohonan koreksi jam pulang karyawan telah ditinjau.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if ($regularizations->hasPages())
                <div class="p-4 px-6 bg-slate-50 border-t border-gray-100 flex items-center justify-between">
                    {{ $regularizations->withQueryString()->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL APPROVE (PERSETUJUAN KOREKSI KEHADIRAN)                             --}}
        {{-- ========================================================================= --}}
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showApproveModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="closeApproveModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showApproveModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    
                    <form :action="approveUrl" method="POST" class="p-6 sm:p-7 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">Setujui Koreksi Kehadiran</h3>
                                    <p class="text-xs text-gray-500">Perbarui jam pulang riil karyawan di sistem.</p>
                                </div>
                            </div>
                            <button type="button" @click="closeApproveModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        {{-- Ringkasan Pengajuan --}}
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/70 text-xs space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Karyawan:</span>
                                <span class="font-bold text-gray-900" x-text="activeRegularization?.employee_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Shift:</span>
                                <span class="font-medium text-gray-800" x-text="activeRegularization?.shift_title"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Jam Pulang Diajukan:</span>
                                <span class="font-mono font-bold text-emerald-700" x-text="activeRegularization?.requested_time"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Estimasi Tambahan Waktu:</span>
                                <span class="font-bold text-blue-600" x-text="activeRegularization?.overtime_str"></span>
                            </div>
                        </div>

                        {{-- Pilihan Status Lembur --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-800">
                                Keputusan Kompensasi Lembur <span class="text-rose-500">*</span>
                            </label>
                            <select name="overtime_status" x-model="overtimeStatus" required
                                    class="w-full text-xs rounded-xl border-gray-300 shadow-2xs focus:border-[#6C5CE7] focus:ring focus:ring-[#6C5CE7]/20 font-medium">
                                <option value="approved">Setujui Sebagai Lembur (Dihitung Jam Lembur Resmi)</option>
                                <option value="none">Setujui Jam Riil Saja (Tanpa Kompensasi Lembur)</option>
                                <option value="rejected">Tolak Klaim Lembur (Jam Kerja Di luar Shift Hangus)</option>
                            </select>
                            <p class="text-[11px] text-gray-400">Pilih opsi perhitungan lembur yang sesuai dengan kebijakan operasional.</p>
                        </div>

                        {{-- Catatan Peninjauan --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-800">
                                Catatan Manajer (Opsional)
                            </label>
                            <textarea name="review_note" x-model="approveNote" rows="3"
                                      placeholder="Tambahkan catatan untuk karyawan jika diperlukan..."
                                      class="w-full text-xs rounded-xl border-gray-300 shadow-2xs focus:border-[#6C5CE7] focus:ring focus:ring-[#6C5CE7]/20"></textarea>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="closeApproveModal()"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Simpan &amp; Setujui</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL REJECT (PENOLAKAN KOREKSI KEHADIRAN)                               --}}
        {{-- ========================================================================= --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRejectModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="closeRejectModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showRejectModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    
                    <form :action="rejectUrl" method="POST" class="p-6 sm:p-7 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">Tolak Koreksi Kehadiran</h3>
                                    <p class="text-xs text-gray-500">Catatan penolakan akan dikirimkan kepada karyawan.</p>
                                </div>
                            </div>
                            <button type="button" @click="closeRejectModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        {{-- Ringkasan Pengajuan --}}
                        <div class="bg-rose-50/50 p-3.5 rounded-xl border border-rose-100 text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Karyawan:</span>
                                <span class="font-bold text-gray-900" x-text="activeRegularization?.employee_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Shift:</span>
                                <span class="font-medium text-gray-800" x-text="activeRegularization?.shift_title"></span>
                            </div>
                            <div class="pt-1 text-gray-600">
                                <strong>Alasan Karyawan:</strong>
                                <span class="italic" x-text="activeRegularization?.reason"></span>
                            </div>
                        </div>

                        {{-- Alasan Penolakan --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-800">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="review_note" x-model="rejectNote" rows="3" required minlength="3"
                                      placeholder="Tuliskan alasan mengapa koreksi jam pulang ditolak..."
                                      class="w-full text-xs rounded-xl border-gray-300 shadow-2xs focus:border-rose-500 focus:ring focus:ring-rose-500/20"></textarea>
                            <p class="text-[11px] text-gray-400">Minimal 3 karakter.</p>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="closeRejectModal()"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
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
