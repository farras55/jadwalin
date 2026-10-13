<x-app-layout>

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Persetujuan Ketersediaan
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola pengajuan halangan karyawan di departemen Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-600">
                    Menunggu persetujuan
                </span>
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">
                    {{ $pendingCount }}
                </span>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter Status --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a
                href="{{ route('manager.availability.index', ['status' => 'pending_manager']) }}"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition
                    {{ $status === 'pending_manager'
                        ? 'bg-slate-900 text-white'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}"
            >
                Menunggu
            </a>

            <a
                href="{{ route('manager.availability.index', ['status' => 'approved']) }}"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition
                    {{ $status === 'approved'
                        ? 'bg-slate-900 text-white'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}"
            >
                Disetujui
            </a>

            <a
                href="{{ route('manager.availability.index', ['status' => 'rejected']) }}"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition
                    {{ $status === 'rejected'
                        ? 'bg-slate-900 text-white'
                        : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}"
            >
                Ditolak
            </a>
        </div>

        {{-- Availability List --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

            @if ($availabilities->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0119 8.414V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <h2 class="text-sm font-semibold text-slate-900">
                        Tidak ada pengajuan
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Belum ada pengajuan dengan status ini.
                    </p>
                </div>
            @else

                {{-- Desktop Table --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Karyawan
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Tanggal / Hari
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Waktu
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Alasan
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                @if ($status === 'pending_manager')
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Aksi
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($availabilities as $availability)
                                <tr class="hover:bg-slate-50">

                                    {{-- Employee --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-slate-500"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                    />
                                                </svg>
                                            </div>

                                            <div>
                                                <div class="font-semibold text-slate-900">
                                                    {{ $availability->user->name }}
                                                </div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $availability->user->position->name ?? 'Posisi tidak tersedia' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                        <div class="font-medium">
                                            {{ $availability->start_time->format('d M Y') }}
                                        </div>

                                        <div class="text-xs text-slate-500">
                                            {{ $availability->start_time->translatedFormat('l') }}
                                        </div>
                                    </td>

                                    {{-- Time --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                        <div class="font-medium">
                                            {{ $availability->start_time->format('H:i') }}
                                            -
                                            {{ $availability->end_time->format('H:i') }}
                                        </div>

                                        <div class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Termasuk buffer
                                        </div>
                                    </td>

                                    {{-- Reason --}}
                                    <td class="max-w-xs px-6 py-4 text-sm text-slate-600">
                                        {{ $availability->reason }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if ($availability->status === 'pending_manager')
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                Menunggu
                                            </span>
                                        @elseif ($availability->status === 'approved')
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                Disetujui
                                            </span>
                                        @elseif ($availability->status === 'rejected')
                                            <div>
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                    Ditolak
                                                </span>

                                                @if ($availability->rejection_reason)
                                                    <p class="mt-1 max-w-xs text-xs text-slate-500">
                                                        {{ $availability->rejection_reason }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    @if ($status === 'pending_manager')
                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">

                                                {{-- Approve --}}
                                                <form
                                                    action="{{ route('manager.availability.approve', $availability) }}"
                                                    method="POST"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600"
                                                    >
                                                        <span class="inline-flex items-center gap-1">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-3.5 w-3.5"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    d="M5 13l4 4L19 7"
                                                                />
                                                            </svg>
                                                            Approve
                                                        </span>
                                                    </button>
                                                </form>

                                                {{-- Reject --}}
                                                <button
                                                    type="button"
                                                    @click="openRejectModal({{ $availability->id }})"
                                                    class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600"
                                                >
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-3.5 w-3.5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M6 18L18 6M6 6l12 12"
                                                            />
                                                        </svg>
                                                        Reject
                                                    </span>
                                                </button>

                                            </div>
                                        </td>
                                    @endif

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card --}}
                <div class="divide-y divide-slate-200 md:hidden">
                    @foreach ($availabilities as $availability)
                        <div class="p-4">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-slate-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ $availability->user->name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $availability->user->position->name ?? 'Posisi tidak tersedia' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($availability->status === 'pending_manager')
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Menunggu
                                    </span>
                                @elseif ($availability->status === 'approved')
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        Ditolak
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Tanggal</span>
                                    <span class="font-medium text-slate-700">
                                        {{ $availability->start_time->format('d M Y') }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Waktu</span>
                                    <span class="font-medium text-slate-700">
                                        {{ $availability->start_time->format('H:i') }}
                                        -
                                        {{ $availability->end_time->format('H:i') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-slate-500">Alasan</span>
                                    <p class="mt-1 text-slate-700">
                                        {{ $availability->reason }}
                                    </p>
                                </div>

                                @if ($availability->status === 'rejected' && $availability->rejection_reason)
                                    <div>
                                        <span class="text-slate-500">Alasan penolakan</span>
                                        <p class="mt-1 text-red-600">
                                            {{ $availability->rejection_reason }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if ($status === 'pending_manager')
                                <div class="mt-4 flex gap-2">

                                    <form
                                        action="{{ route('manager.availability.approve', $availability) }}"
                                        method="POST"
                                        class="flex-1"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-600"
                                        >
                                            Approve
                                        </button>
                                    </form>

                                    <button
                                        type="button"
                                        @click="openRejectModal({{ $availability->id }})"
                                        class="flex-1 rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-600"
                                    >
                                        Reject
                                    </button>

                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div
    x-data="{
        showRejectModal: false,
        rejectUrl: '',
        rejectionReason: '',

        openRejectModal(id) {
            this.rejectUrl = '{{ url('/manager/availability') }}/' + id + '/reject';
            this.rejectionReason = '';
            this.showRejectModal = true;
        },

        closeRejectModal() {
            this.showRejectModal = false;
            this.rejectUrl = '';
            this.rejectionReason = '';
        }
    }"
    @keydown.escape.window="closeRejectModal()"
>
    <div
        x-show="showRejectModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
        <div
            @click.outside="closeRejectModal()"
            class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
        >
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">
                    Tolak Pengajuan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Masukkan alasan penolakan pengajuan karyawan.
                </p>
            </div>

            <form
                :action="rejectUrl"
                method="POST"
            >
                @csrf

                <label
                    for="rejection_reason"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Alasan Penolakan
                </label>

                <textarea
                    id="rejection_reason"
                    name="rejection_reason"
                    x-model="rejectionReason"
                    rows="4"
                    maxlength="255"
                    required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                    placeholder="Contoh: Jadwal kerja tidak dapat disesuaikan."
                ></textarea>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="closeRejectModal()"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600"
                    >
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>