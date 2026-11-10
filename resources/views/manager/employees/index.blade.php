
<x-app-layout>

    <div
        class="space-y-6"
        x-data="{ selectedType: 'all' }"
    >

        {{-- Header Group: Breadcrumb & Page Title --}}
        <div>

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-2">
                <span>Manager</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                <span class="text-[#6C5CE7] font-medium">Data Karyawan Tim</span>
            </div>

            {{-- Header Halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                        Data Karyawan Tim
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                        Kelola informasi dan tinjau aktivitas karyawan di departemen Anda.
                    </p>
                </div>
            </div>

        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl border border-emerald-200 shadow-2xs flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="text-xs md:text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ================================================================= --}}
        {{-- 4 KPI CARDS --}}
        {{-- ================================================================= --}}

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total Karyawan --}}
            <button
                type="button"
                @click="selectedType = 'all'"
                :class="selectedType === 'all'
                    ? 'ring-2 ring-[#6C5CE7] border-[#6C5CE7]'
                    : 'border-[#E2E0F7]'"
                class="w-full text-left bg-white rounded-2xl border p-5 shadow-2xs flex items-center justify-between transition hover:shadow-md"
                :aria-pressed="selectedType === 'all'"
            >
                <div>
                    <p class="text-xs font-semibold text-[#7C7896]">Total Karyawan</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ $employees->total() }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Departemen Anda</p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </button>

            {{-- Full-Time --}}
            <button
                type="button"
                @click="selectedType = 'full_time'"
                :class="selectedType === 'full_time'
                    ? 'ring-2 ring-emerald-500 border-emerald-500'
                    : 'border-[#E2E0F7]'"
                class="w-full text-left bg-white rounded-2xl border p-5 shadow-2xs flex items-center justify-between transition hover:shadow-md"
                :aria-pressed="selectedType === 'full_time'"
            >
                <div>
                    <p class="text-xs font-semibold text-emerald-700">Full-Time</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-emerald-600 mt-1 font-mono">
                        {{ $employees->getCollection()->where('employment_type', 'full_time')->count() }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Pada halaman ini</p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="briefcase" class="w-6 h-6"></i>
                </div>
            </button>

            {{-- Part-Time --}}
            <button
                type="button"
                @click="selectedType = 'part_time'"
                :class="selectedType === 'part_time'
                    ? 'ring-2 ring-indigo-500 border-indigo-500'
                    : 'border-[#E2E0F7]'"
                class="w-full text-left bg-white rounded-2xl border p-5 shadow-2xs flex items-center justify-between transition hover:shadow-md"
                :aria-pressed="selectedType === 'part_time'"
            >
                <div>
                    <p class="text-xs font-semibold text-[#4F46E5]">Part-Time</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-[#4F46E5] mt-1 font-mono">
                        {{ $employees->getCollection()->where('employment_type', 'part_time')->count() }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Pada halaman ini</p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </button>

            {{-- Freelance --}}
            <button
                type="button"
                @click="selectedType = 'freelance'"
                :class="selectedType === 'freelance'
                    ? 'ring-2 ring-amber-500 border-amber-500'
                    : 'border-[#E2E0F7]'"
                class="w-full text-left bg-white rounded-2xl border p-5 shadow-2xs flex items-center justify-between transition hover:shadow-md"
                :aria-pressed="selectedType === 'freelance'"
            >
                <div>
                    <p class="text-xs font-semibold text-amber-700">Freelance</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-amber-600 mt-1 font-mono">
                        {{ $employees->getCollection()->where('employment_type', 'freelance')->count() }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Pada halaman ini</p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
            </button>

        </div>

        {{-- ================================================================= --}}
        {{-- PENCARIAN & FILTER --}}
        {{-- ================================================================= --}}

        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 shadow-2xs">
            <form method="GET" action="{{ route('manager.employees.index') }}" class="space-y-3">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                    {{-- Input Pencarian --}}
                    <div class="md:col-span-6 relative">
                        <i data-lucide="search" class="w-4 h-4 text-[#7C7896] absolute left-3.5 top-1/2 -translate-y-1/2"></i>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Cari nama, email, atau nomor telepon..."
                            class="w-full pl-9 pr-4 py-2 text-xs md:text-sm text-[#2D2A3E] placeholder-[#7C7896]/60 bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                        >
                    </div>

                    {{-- Filter Posisi --}}
                    <div class="md:col-span-3">
                        <select
                            id="position_id"
                            name="position_id"
                            class="w-full px-3 py-2 text-xs md:text-sm text-[#2D2A3E] bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                        >
                            <option value="">Semua Posisi</option>

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

                    {{-- Filter Tipe Karyawan --}}
                    <div class="md:col-span-3">
                        <select
                            id="employment_type"
                            name="employment_type"
                            class="w-full px-3 py-2 text-xs md:text-sm text-[#2D2A3E] bg-[#F8F7FF] border border-[#E2E0F7] rounded-xl focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] transition"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="full_time" @selected($selectedEmploymentType === 'full_time')>Full-Time</option>
                            <option value="part_time" @selected($selectedEmploymentType === 'part_time')>Part-Time</option>
                            <option value="freelance" @selected($selectedEmploymentType === 'freelance')>Freelance</option>
                        </select>
                    </div>

                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2 pt-1">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#6C5CE7] hover:bg-[#5A4BD1] text-white text-xs font-semibold shadow-2xs transition"
                    >
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan Filter</span>
                    </button>

                    <a
                        href="{{ route('manager.employees.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#E2E0F7] bg-[#F8F7FF] hover:bg-[#F5F3FF] text-[#7C7896] hover:text-[#2D2A3E] text-xs font-semibold transition"
                    >
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </a>
                </div>

            </form>
        </div>

        {{-- ================================================================= --}}
        {{-- TABEL DIREKTORI KARYAWAN --}}
        {{-- ================================================================= --}}

        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">

            <div class="px-5 py-4 border-b border-[#E2E0F7] flex items-center justify-between">

                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-[#2D2A3E]">Direktori Karyawan</h2>
                        <p class="text-xs text-[#7C7896]">
                            Menampilkan {{ $employees->count() }} dari {{ $employees->total() }} karyawan.
                        </p>
                    </div>
                </div>

                {{-- Tombol Tampilkan Semua --}}
                <button
                    type="button"
                    x-show="selectedType !== 'all'"
                    @click="selectedType = 'all'"
                    x-cloak
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-[#E2E0F7] bg-[#F8F7FF] hover:bg-[#F5F3FF] text-[#6C5CE7] text-xs font-semibold transition"
                >
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Tampilkan Semua</span>
                </button>

            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">

                    <thead class="bg-[#F8F7FF] text-[#7C7896] font-bold border-b border-[#E2E0F7] uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center">No</th>
                            <th class="px-5 py-3.5">Karyawan</th>
                            <th class="px-5 py-3.5">Posisi</th>
                            <th class="px-5 py-3.5">Tipe</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#E2E0F7]">

                        @forelse ($employees as $index => $employee)

                            @php
                                $employmentLabels = [
                                    'full_time' => 'Full-Time',
                                    'part_time' => 'Part-Time',
                                    'freelance' => 'Freelance',
                                ];

                                $employmentClasses = [
                                    'full_time' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'part_time' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'freelance' => 'bg-purple-50 text-purple-700 border-purple-200',
                                ];

                                $statusLabels = [
                                    'active' => 'Aktif',
                                    'on_leave' => 'Cuti',
                                    'resigned' => 'Tidak Aktif',
                                ];

                                $statusClasses = [
                                    'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'on_leave' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'resigned' => 'bg-slate-100 text-slate-600 border-slate-200',
                                ];

                                $employeeStatus = $employee->status ?? 'active';
                                $employmentType = $employee->employment_type ?? 'full_time';
                                $initial = strtoupper(substr($employee->name, 0, 1));
                            @endphp

                            <tr
                                class="hover:bg-[#F8F7FF]/50 transition"
                                x-show="selectedType === 'all' || selectedType === @js($employmentType)"
                            >

                                {{-- No --}}
                                <td class="px-4 py-3.5 text-center text-[#7C7896] font-medium">
                                    {{ $employees->firstItem() + $index }}
                                </td>

                                {{-- Karyawan --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">

                                        @if ($employee->profile_photo)
                                            <img
                                                src="{{ asset('storage/' . $employee->profile_photo) }}"
                                                alt="{{ $employee->name }}"
                                                class="w-9 h-9 rounded-full object-cover shrink-0 shadow-2xs"
                                            >
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ $initial }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-bold text-[#2D2A3E]">{{ $employee->name }}</p>
                                            <p class="text-[11px] text-[#7C7896]">{{ $employee->email }}</p>
                                        </div>

                                    </div>
                                </td>

                                {{-- Posisi --}}
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-[#2D2A3E]">
                                    {{ $employee->position?->name ?? 'Belum ditentukan' }}
                                </td>

                                {{-- Tipe Karyawan --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border {{ $statusClasses[$employeeStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">

                                        @if ($employeeStatus === 'active')
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-600"></i>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        @endif

                                        {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}

                                    </span>
                                </td>

                                {{-- Aksi Detail --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <a
                                        href="{{ route('manager.employees.show', $employee) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#E2E0F7] bg-white hover:bg-[#F5F3FF] text-[#6C5CE7] text-xs font-semibold shadow-2xs transition"
                                    >
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-[#7C7896]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mb-1">
                                            <i data-lucide="users" class="w-6 h-6"></i>
                                        </div>

                                        <p class="text-sm font-bold text-[#2D2A3E]">Karyawan Tidak Ditemukan</p>

                                        <p class="text-xs text-[#7C7896] max-w-sm">
                                            Coba sesuaikan kata kunci pencarian atau reset filter posisi dan tipe karyawan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            @if ($employees->hasPages())
                <div class="p-4 sm:p-5 border-t border-[#E2E0F7] bg-[#F8F7FF]">
                    {{ $employees->links() }}
                </div>
            @endif

        </div>

    </div>

</x-app-layout>