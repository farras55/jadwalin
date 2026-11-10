<x-app-layout>

<div 
    class="space-y-6"
    x-data="{
        showRejectModal: false,
        rejectUrl: '',
        rejectionReason: '',
        searchQuery: '',

        openRejectModal(id) {
            this.rejectUrl = '{{ url('/manager/availability') }}/' + id + '/reject';
            this.rejectionReason = '';
            this.showRejectModal = true;
        },

        closeRejectModal() {
            this.showRejectModal = false;
            this.rejectUrl = '';
            this.rejectionReason = '';
        },

        matchesSearch(name, reason, position) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase();
            return name.toLowerCase().includes(q) ||
                   reason.toLowerCase().includes(q) ||
                   position.toLowerCase().includes(q);
        }
    }"
    @keydown.escape.window="closeRejectModal()"
>
    {{-- Header Group: Breadcrumb & Page Title --}}
    <div>
        {{-- Breadcrumb Standar Permanen --}}
        <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-1">
            <span>Manager</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-[#6C5CE7] font-medium">Persetujuan Ketersediaan</span>
        </div>

        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Persetujuan Ketersediaan
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                    Kelola pengajuan halangan karyawan di departemen Anda.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] shadow-2xs">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span>Menunggu Persetujuan:</span>
                    <span class="font-bold text-[#6C5CE7]">{{ $pendingCount }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="text-xs md:text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span class="text-xs md:text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Tab Filter Status Sesuai Desain Acuan --}}
    <div class="flex flex-wrap items-center gap-2.5">
        {{-- Menunggu --}}
        <a
            href="{{ route('manager.availability.index', ['status' => 'pending_manager']) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs md:text-sm font-semibold transition-all shadow-2xs
                {{ $status === 'pending_manager'
                    ? 'bg-[#6C5CE7] text-white shadow-xs'
                    : 'bg-white text-[#7C7896] hover:text-[#2D2A3E] border border-[#E2E0F7]' }}"
        >
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>Menunggu ({{ $pendingCount }})</span>
        </a>

        {{-- Disetujui --}}
        <a
            href="{{ route('manager.availability.index', ['status' => 'approved']) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs md:text-sm font-semibold transition-all shadow-2xs
                {{ $status === 'approved'
                    ? 'bg-[#6C5CE7] text-white shadow-xs'
                    : 'bg-white text-[#7C7896] hover:text-[#2D2A3E] border border-[#E2E0F7]' }}"
        >
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Disetujui</span>
        </a>

        {{-- Ditolak --}}
        <a
            href="{{ route('manager.availability.index', ['status' => 'rejected']) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs md:text-sm font-semibold transition-all shadow-2xs
                {{ $status === 'rejected'
                    ? 'bg-[#6C5CE7] text-white shadow-xs'
                    : 'bg-white text-[#7C7896] hover:text-[#2D2A3E] border border-[#E2E0F7]' }}"
        >
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            <span>Ditolak</span>
        </a>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 rounded-2xl border border-[#E2E0F7] shadow-2xs">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-[#7C7896] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                x-model="searchQuery"
                placeholder="Cari karyawan atau alasan..."
                class="w-full pl-9 pr-4 py-2 text-xs md:text-sm text-[#2D2A3E] placeholder-[#7C7896]/60 bg-[#F5F3FF]/40 border border-[#E2E0F7] rounded-xl focus:outline-none focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
            >
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-[#7C7896] bg-slate-50 border border-[#E2E0F7]">
                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                <span>{{ auth()->user()->department->name ?? 'Semua Departemen' }}</span>
            </span>
        </div>
    </div>

    {{-- Availability List Container --}}
    <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
        @if ($availabilities->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="calendar-check" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-[#2D2A3E]">
                    Tidak Ada Pengajuan Ketersediaan
                </h3>
                <p class="text-xs text-[#7C7896] max-w-sm mx-auto mt-1">
                    @if ($status === 'pending_manager')
                        Saat ini tidak ada pengajuan halangan yang sedang menunggu persetujuan Anda.
                    @elseif ($status === 'approved')
                        Belum ada riwayat pengajuan ketersediaan yang disetujui.
                    @else
                        Belum ada riwayat pengajuan ketersediaan yang ditolak.
                    @endif
                </p>
            </div>
        @else
            {{-- Desktop Table View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F5F3FF]/60 text-[11px] uppercase font-bold text-[#7C7896] tracking-wider border-b border-[#E2E0F7]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5">Karyawan</th>
                            <th scope="col" class="px-6 py-3.5">Tanggal / Hari</th>
                            <th scope="col" class="px-6 py-3.5">Waktu</th>
                            <th scope="col" class="px-6 py-3.5">Alasan</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            @if ($status === 'pending_manager')
                                <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E0F7] text-xs">
                        @foreach ($availabilities as $availability)
                            @php
                                $userName = $availability->user->name ?? 'Karyawan';
                                $initials = collect(explode(' ', $userName))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->implode('');
                                $posName = $availability->user->position->name ?? 'Staf';
                                $reasonText = addslashes($availability->reason ?? '');
                            @endphp
                            <tr
                                x-show="matchesSearch('{{ addslashes($userName) }}', '{{ $reasonText }}', '{{ addslashes($posName) }}')"
                                class="hover:bg-[#F5F3FF]/40 transition"
                            >
                                {{-- Karyawan --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#2D2A3E]">{{ $userName }}</p>
                                            <p class="text-[11px] text-[#7C7896]">{{ $posName }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tanggal --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-[#2D2A3E]">
                                        {{ $availability->start_time->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-[#7C7896]">
                                        {{ $availability->start_time->translatedFormat('l') }}
                                    </div>
                                </td>

                                {{-- Waktu --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-[#2D2A3E] flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                        <span>{{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}</span>
                                    </div>
                                    <div class="text-[10px] text-[#7C7896] mt-0.5">
                                        Termasuk buffer
                                    </div>
                                </td>

                                {{-- Alasan --}}
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-[#2D2A3E] font-medium line-clamp-2">
                                        {{ $availability->reason }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($availability->status === 'pending_manager')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu
                                        </span>
                                    @elseif ($availability->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Disetujui
                                        </span>
                                    @elseif ($availability->status === 'rejected')
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Ditolak
                                            </span>
                                            @if ($availability->rejection_reason)
                                                <p class="mt-1 text-[11px] text-rose-600 max-w-xs italic">
                                                    "{{ $availability->rejection_reason }}"
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                @if ($status === 'pending_manager')
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Reject --}}
                                            <button
                                                type="button"
                                                @click="openRejectModal({{ $availability->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                                            >
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                Tolak
                                            </button>

                                            {{-- Approve --}}
                                            <form
                                                action="{{ route('manager.availability.approve', $availability) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition"
                                                >
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    Setujui
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards View --}}
            <div class="divide-y divide-[#E2E0F7] md:hidden">
                @foreach ($availabilities as $availability)
                    @php
                        $userName = $availability->user->name ?? 'Karyawan';
                        $initials = collect(explode(' ', $userName))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->implode('');
                        $posName = $availability->user->position->name ?? 'Staf';
                        $reasonText = addslashes($availability->reason ?? '');
                    @endphp
                    <div
                        x-show="matchesSearch('{{ addslashes($userName) }}', '{{ $reasonText }}', '{{ addslashes($posName) }}')"
                        class="p-4 space-y-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    {{ strtoupper($initials) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#2D2A3E] text-sm">{{ $userName }}</p>
                                    <p class="text-xs text-[#7C7896]">{{ $posName }}</p>
                                </div>
                            </div>

                            @if ($availability->status === 'pending_manager')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu
                                </span>
                            @elseif ($availability->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Disetujui
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Ditolak
                                </span>
                            @endif
                        </div>

                        <div class="bg-[#F5F3FF]/40 border border-[#E2E0F7] rounded-xl p-3 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7896] flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                    Tanggal
                                </span>
                                <span class="font-semibold text-[#2D2A3E]">
                                    {{ $availability->start_time->translatedFormat('d M Y') }} ({{ $availability->start_time->translatedFormat('l') }})
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7896] flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                    Waktu
                                </span>
                                <span class="font-semibold text-[#2D2A3E]">
                                    {{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}
                                </span>
                            </div>

                            <div class="pt-1.5 border-t border-[#E2E0F7]">
                                <span class="text-[#7C7896]">Alasan:</span>
                                <p class="text-[#2D2A3E] font-medium mt-0.5">
                                    {{ $availability->reason }}
                                </p>
                            </div>

                            @if ($availability->status === 'rejected' && $availability->rejection_reason)
                                <div class="pt-1.5 border-t border-rose-200 text-rose-700">
                                    <span class="font-semibold">Alasan Penolakan:</span>
                                    <p class="mt-0.5 italic">
                                        "{{ $availability->rejection_reason }}"
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($status === 'pending_manager')
                            <div class="flex gap-2 pt-1">
                                <button
                                    type="button"
                                    @click="openRejectModal({{ $availability->id }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                                >
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    Tolak
                                </button>

                                <form
                                    action="{{ route('manager.availability.approve', $availability) }}"
                                    method="POST"
                                    class="flex-1"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition"
                                    >
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Setujui
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Reject Modal (Konfirmasi Penolakan) --}}
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
            <div
                @click.outside="closeRejectModal()"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#E2E0F7] space-y-4"
            >
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E0F7]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#2D2A3E]">
                                Tolak Pengajuan Ketersediaan
                            </h3>
                            <p class="text-xs text-[#7C7896]">
                                Berikan alasan penolakan untuk karyawan
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeRejectModal()"
                        class="p-1.5 rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form
                    :action="rejectUrl"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <label
                            for="rejection_reason"
                            class="block text-xs font-semibold text-[#2D2A3E] mb-1.5"
                        >
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>

                        <textarea
                            id="rejection_reason"
                            name="rejection_reason"
                            x-model="rejectionReason"
                            rows="4"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-[#E2E0F7] bg-[#F5F3FF]/30 px-3.5 py-2.5 text-xs md:text-sm text-[#2D2A3E] placeholder-[#7C7896]/60 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] focus:outline-none transition"
                            placeholder="Contoh: Jadwal kerja memerlukan staf penuh pada jam tersebut."
                        ></textarea>
                        <p class="text-[11px] text-[#7C7896] mt-1">Maksimal 255 karakter.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#E2E0F7]">
                        <button
                            type="button"
                            @click="closeRejectModal()"
                            class="px-4 py-2.5 text-xs font-semibold rounded-xl text-[#7C7896] hover:text-[#2D2A3E] hover:bg-[#F5F3FF] transition"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2.5 text-xs font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-2xs transition"
                        >
                            Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
