
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('manager.employees.index') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-600 transition hover:bg-gray-50"
                    aria-label="Kembali ke daftar karyawan"
                >
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </a>

                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        Profil Karyawan
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Informasi karyawan dan ringkasan aktivitas shift.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="h-28 bg-gradient-to-r from-violet-600 to-indigo-500"></div>

                <div class="px-6 pb-6">
                    <div class="-mt-12 flex flex-wrap items-end gap-4">
                        @if ($employee->profile_photo)
                            <img
                                src="{{ asset('storage/' . $employee->profile_photo) }}"
                                alt="{{ $employee->name }}"
                                class="h-24 w-24 rounded-2xl border-4 border-white bg-white object-cover shadow-sm"
                            >
                        @else
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl border-4 border-white bg-violet-100 text-3xl font-bold text-violet-700 shadow-sm">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1 pb-1">
                            <h3 class="text-2xl font-bold text-gray-800">
                                {{ $employee->name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $employee->position?->name ?? 'Posisi belum ditentukan' }}
                                @if ($employee->department)
                                    · {{ $employee->department->name }}
                                @endif
                            </p>
                        </div>

                        @php
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

                            $employeeStatus = $employee->status ?? 'active';
                            $employmentType = $employee->employment_type ?? 'full_time';
                        @endphp

                        <span class="mb-1 rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$employeeStatus] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Kehadiran 30 Hari
                        </p>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i data-lucide="calendar-check" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-800">
                        {{ $attendanceCount }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Shift berstatus hadir atau terlambat
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Jam Kerja Bulan Ini
                        </p>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <i data-lucide="clock" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-800">
                        {{ number_format((float) $monthlyHours, 1, ',', '.') }}
                        <span class="text-base font-medium text-gray-500">jam</span>
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Jam reguler ditambah jam lembur
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Total Shift Bulan Ini
                        </p>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <i data-lucide="calendar-days" class="h-5 w-5"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-800">
                        {{ $monthlyShiftCount }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Shift yang tercatat pada bulan berjalan
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-5 font-semibold text-gray-800">
                        Informasi Karyawan
                    </h3>

                    <div class="space-y-5">
                        <div class="flex items-start gap-3">
                            <i data-lucide="mail" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="mt-1 break-all text-sm font-medium text-gray-800">
                                    {{ $employee->email }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="phone" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">Nomor Telepon / WhatsApp</p>

                                @if ($employee->phone_number)
                                    <a
                                        href="https://wa.me/{{ preg_replace('/\D+/', '', $employee->phone_number) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-1 inline-block break-all text-sm font-medium text-emerald-700 hover:text-emerald-900"
                                    >
                                        {{ $employee->phone_number }}
                                    </a>
                                @else
                                    <p class="mt-1 text-sm text-gray-400">
                                        Belum tersedia
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="building-2" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Departemen</p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ $employee->department?->name ?? 'Belum ditentukan' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="briefcase-business" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Tipe Karyawan</p>
                                <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $employmentClasses[$employmentType] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="timer" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Kuota Jam Kerja Mingguan</p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ $employee->max_weekly_hours ?? 'Belum ditentukan' }}
                                    @if ($employee->max_weekly_hours !== null)
                                        jam/minggu
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="calendar" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Bergabung</p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ $employee->join_date?->format('d M Y') ?? 'Belum tersedia' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm lg:col-span-2">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="font-semibold text-gray-800">
                            Aktivitas Shift 30 Hari Terakhir
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Riwayat jadwal dan status kehadiran karyawan.
                        </p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($recentAssignments as $assignment)
                            @php
                                $shiftStatusLabels = [
                                    'scheduled' => 'Terjadwal',
                                    'present' => 'Hadir',
                                    'absent' => 'Tidak Hadir',
                                    'late' => 'Terlambat',
                                ];

                                $shiftStatusClasses = [
                                    'scheduled' => 'bg-sky-50 text-sky-700',
                                    'present' => 'bg-emerald-50 text-emerald-700',
                                    'absent' => 'bg-rose-50 text-rose-700',
                                    'late' => 'bg-amber-50 text-amber-700',
                                ];

                                $shiftStatus = $assignment->status ?? 'scheduled';
                            @endphp

                            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                        <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">
                                            {{ $assignment->shift?->title ?? 'Shift' }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            @if ($assignment->shift?->start_time)
                                                {{ $assignment->shift->start_time->format('d M Y, H:i') }}
                                            @else
                                                Waktu jadwal tidak tersedia
                                            @endif

                                            @if ($assignment->shift?->end_time)
                                                – {{ $assignment->shift->end_time->format('H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $shiftStatusClasses[$shiftStatus] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $shiftStatusLabels[$shiftStatus] ?? ucfirst($shiftStatus) }}
                                </span>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <i data-lucide="calendar-x" class="h-6 w-6"></i>
                                </div>
                                <p class="mt-3 font-semibold text-gray-700">
                                    Belum ada aktivitas shift
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Tidak ada jadwal yang tercatat dalam 30 hari terakhir.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <a
                    href="{{ route('manager.employees.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Daftar Karyawan
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
