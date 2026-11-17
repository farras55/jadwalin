<x-app-layout>
    <div
        class="space-y-6"
        x-data="{ selectedType: 'all' }"
    >
        {{-- ----------------------------------------------------------------- --}}
        {{-- 1. HEADER SECTION & OPERATIONAL CONTEXT                           --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <span>Manager</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-900 font-medium">Data Karyawan Tim</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Data Karyawan Tim
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    @if ($isGlobalManager)
                        Direktori staf dan anggota tim di seluruh unit dan departemen operasional (Akses Global).
                    @else
                        Direktori staf dan anggota tim di unit <span class="font-semibold text-slate-800">{{ $currentDepartment?->name ?? 'Departemen Anda' }}</span>.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 shadow-card">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Total Staf:</span>
                    <strong class="font-mono text-slate-900 font-bold">{{ $employees->total() }}</strong>
                </span>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 flex items-center gap-3 text-xs sm:text-sm shadow-card">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- 2. PENCARIAN & FILTER TOOLBAR                                     --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-card">
            <form method="GET" action="{{ route('manager.employees.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5">

                    {{-- Input Pencarian --}}
                    <div class="{{ $isGlobalManager ? 'md:col-span-4' : 'md:col-span-5' }} relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Cari nama, email, NIK, telepon..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                        >
                    </div>

                    {{-- Filter Departemen (Khusus Global Manager) --}}
                    @if ($isGlobalManager)
                        <div class="md:col-span-3">
                            <select
                                id="department_id"
                                name="department_id"
                                class="w-full px-3 py-1.5 text-xs text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                            >
                                <option value="">Semua Departemen</option>
                                @foreach ($departments as $department)
                                    <option
                                        value="{{ $department->id }}"
                                        @selected((string) $selectedDepartment === (string) $department->id)
                                    >
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Filter Posisi --}}
                    <div class="md:col-span-3">
                        <select
                            id="position_id"
                            name="position_id"
                            class="w-full px-3 py-1.5 text-xs text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
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

                    {{-- Tombol Aksi Filter --}}
                    <div class="md:col-span-2 flex items-center gap-2">
                        <button
                            type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition"
                        >
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            <span>Filter</span>
                        </button>

                        <a
                            href="{{ route('manager.employees.index') }}"
                            class="inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition"
                            title="Reset Filter"
                        >
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- 3. TABEL DIREKTORI KARYAWAN (HIGH DENSITY)                        --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            
            {{-- Toolbar Atas Tabel: Title & Segmented Type Filter --}}
            <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Daftar Karyawan</h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan {{ $employees->count() }} dari {{ $employees->total() }} staf terdaftar.
                    </p>
                </div>

                {{-- Horizontal Filter Segment Pills (Menggantikan 4 KPI Cards raksasa) --}}
                <div class="inline-flex p-1 bg-white border border-slate-200 rounded-lg text-xs self-start sm:self-auto shadow-card">
                    <button
                        type="button"
                        @click="selectedType = 'all'"
                        :class="selectedType === 'all' ? 'bg-slate-900 text-white font-semibold shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-md transition"
                    >
                        Semua ({{ $employees->total() }})
                    </button>
                    <button
                        type="button"
                        @click="selectedType = 'full_time'"
                        :class="selectedType === 'full_time' ? 'bg-slate-900 text-white font-semibold shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-md transition"
                    >
                        Full-Time
                    </button>
                    <button
                        type="button"
                        @click="selectedType = 'part_time'"
                        :class="selectedType === 'part_time' ? 'bg-slate-900 text-white font-semibold shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-md transition"
                    >
                        Part-Time
                    </button>
                    <button
                        type="button"
                        @click="selectedType = 'freelance'"
                        :class="selectedType === 'freelance' ? 'bg-slate-900 text-white font-semibold shadow-card' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-md transition"
                    >
                        Freelance
                    </button>
                </div>
            </div>

            {{-- Tampilan Mobile: Kartu Responsif (sm:hidden) --}}
            <div class="sm:hidden divide-y divide-slate-100">
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
                    <div class="p-3.5 space-y-2.5" x-show="selectedType === 'all' || selectedType === @js($employmentType)">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if ($employee->profile_photo)
                                    <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->name }}" class="w-9 h-9 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ $initial }}
                                    </div>
                                @endif
                                <div class="min-w-0 truncate">
                                    <p class="font-bold text-slate-900 text-xs truncate">{{ $employee->name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ $employee->email }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold border {{ $statusClasses[$employeeStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }} shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $employeeStatus === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-medium text-slate-800 text-xs">{{ $employee->position?->name ?? 'Belum ditentukan' }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                </span>
                            </div>

                            <a href="{{ route('manager.employees.show', $employee) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition shrink-0">
                                <span>Detail</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        <p class="text-xs font-semibold text-slate-800">Karyawan Tidak Ditemukan</p>
                        <p class="text-xs text-slate-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                    </div>
                @endforelse
            </div>

            {{-- Tampilan Desktop: Tabel Lengkap (hidden sm:block) --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5 text-center w-12">No</th>
                            <th class="px-4 py-2.5">Karyawan</th>
                            <th class="px-4 py-2.5">Posisi &amp; Unit</th>
                            <th class="px-4 py-2.5">Tipe Penugasan</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right w-24">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
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
                                class="hover:bg-slate-50/70 transition"
                                x-show="selectedType === 'all' || selectedType === @js($employmentType)"
                            >
                                {{-- No --}}
                                <td class="px-4 py-3 text-center text-slate-400 font-medium">
                                    {{ $employees->firstItem() + $index }}
                                </td>

                                {{-- Karyawan --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        @if ($employee->profile_photo)
                                            <img
                                                src="{{ asset('storage/' . $employee->profile_photo) }}"
                                                alt="{{ $employee->name }}"
                                                class="w-8 h-8 rounded-full object-cover shrink-0"
                                            >
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ $initial }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $employee->name }}</p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ $employee->email }}
                                                @if ($employee->phone_number)
                                                    &bull; <span class="font-mono text-[10px]">{{ $employee->phone_number }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Posisi & Departemen --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="font-medium text-slate-800">{{ $employee->position?->name ?? 'Belum ditentukan' }}</p>
                                    @if ($isGlobalManager && $employee->department)
                                        <p class="text-[11px] text-indigo-600 font-semibold flex items-center gap-1 mt-0.5">
                                            <i data-lucide="building-2" class="w-3 h-3"></i>
                                            <span>{{ $employee->department->name }}</span>
                                        </p>
                                    @endif
                                </td>

                                {{-- Tipe Karyawan --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold border {{ $statusClasses[$employeeStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $employeeStatus === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                                    </span>
                                </td>

                                {{-- Aksi Detail --}}
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <a
                                        href="{{ route('manager.employees.show', $employee) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-card transition"
                                    >
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="h-10 w-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2.5">
                                        <i data-lucide="users" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">Karyawan Tidak Ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($employees->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
