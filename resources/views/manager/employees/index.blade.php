
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                <i data-lucide="users" class="h-6 w-6"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Data Karyawan Tim
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola informasi dan tinjau aktivitas karyawan di departemen Anda.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Karyawan</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800">
                        {{ $employees->total() }}
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Full-Time</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">
                        {{ $employees->getCollection()->where('employment_type', 'full_time')->count() }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Pada halaman ini</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Part-Time dan Freelance</p>
                    <p class="mt-2 text-3xl font-bold text-violet-600">
                        {{ $employees->getCollection()->whereIn('employment_type', ['part_time', 'freelance'])->count() }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Pada halaman ini</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('manager.employees.index') }}"
                      class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">

                    <div class="md:col-span-2">
                        <label for="search" class="mb-2 block text-sm font-medium text-gray-700">
                            Cari Karyawan
                        </label>
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-gray-400"></i>
                            <input
                                id="search"
                                name="search"
                                type="text"
                                value="{{ $search }}"
                                placeholder="Nama, email, atau nomor telepon..."
                                class="w-full rounded-xl border-gray-200 py-2.5 pl-10 text-sm focus:border-violet-500 focus:ring-violet-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="position_id" class="mb-2 block text-sm font-medium text-gray-700">
                            Posisi
                        </label>
                        <select
                            id="position_id"
                            name="position_id"
                            class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-violet-500 focus:ring-violet-500"
                        >
                            <option value="">Semua posisi</option>
                            @foreach ($positions as $position)
                                <option
                                    value="{{ $position->id }}"
                                    @selected((string) $selectedPosition === (string) $position->id)
                                >
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="employment_type" class="mb-2 block text-sm font-medium text-gray-700">
                            Tipe Karyawan
                        </label>
                        <select
                            id="employment_type"
                            name="employment_type"
                            class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-violet-500 focus:ring-violet-500"
                        >
                            <option value="">Semua tipe</option>
                            <option value="full_time" @selected($selectedEmploymentType === 'full_time')>
                                Full-Time
                            </option>
                            <option value="part_time" @selected($selectedEmploymentType === 'part_time')>
                                Part-Time
                            </option>
                            <option value="freelance" @selected($selectedEmploymentType === 'freelance')>
                                Freelance
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-2 md:col-span-4">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700"
                        >
                            <i data-lucide="filter" class="h-4 w-4"></i>
                            Terapkan Filter
                        </button>

                        <a
                            href="{{ route('manager.employees.index') }}"
                            class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Direktori Karyawan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $employees->total() }} karyawan ditemukan
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Karyawan
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Posisi
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Tipe
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Kontak
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $employee)
                                @php
                                    $employmentLabels = [
                                        'full_time' => 'Full-Time',
                                        'part_time' => 'Part-Time',
                                        'freelance' => 'Freelance',
                                    ];

                                    $employmentClasses = [
                                        'full_time' => 'bg-indigo-50 text-indigo-700',
                                        'part_time' => 'bg-sky-50 text-sky-700',
                                        'freelance' => 'bg-purple-50 text-purple-700',
                                    ];

                                    $statusLabels = [
                                        'active' => 'Aktif',
                                        'on_leave' => 'Cuti',
                                        'resigned' => 'Tidak Aktif',
                                    ];

                                    $statusClasses = [
                                        'active' => 'bg-emerald-50 text-emerald-700',
                                        'on_leave' => 'bg-amber-50 text-amber-700',
                                        'resigned' => 'bg-gray-100 text-gray-600',
                                    ];

                                    $employeeStatus = $employee->status ?? 'active';
                                    $employmentType = $employee->employment_type ?? 'full_time';
                                @endphp

                                <tr class="transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($employee->profile_photo)
                                                <img
                                                    src="{{ asset('storage/' . $employee->profile_photo) }}"
                                                    alt="{{ $employee->name }}"
                                                    class="h-10 w-10 rounded-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 font-bold text-violet-700">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                            @endif

                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    {{ $employee->name }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $employee->email }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                                        {{ $employee->position?->name ?? 'Belum ditentukan' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $employmentClasses[$employmentType] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $employee->max_weekly_hours ?? '—' }} jam/minggu
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        @if ($employee->phone_number)
                                            <a
                                                href="https://wa.me/{{ preg_replace('/\D+/', '', $employee->phone_number) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-900"
                                            >
                                                <i data-lucide="phone" class="h-4 w-4"></i>
                                                {{ $employee->phone_number }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">Belum tersedia</span>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$employeeStatus] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a
                                            href="{{ route('manager.employees.show', $employee) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 px-3 py-2 text-sm font-medium text-violet-700 transition hover:bg-violet-50"
                                        >
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                            <i data-lucide="users" class="h-6 w-6"></i>
                                        </div>
                                        <p class="mt-3 font-semibold text-gray-700">
                                            Karyawan tidak ditemukan
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Coba ubah kata pencarian atau filter.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
