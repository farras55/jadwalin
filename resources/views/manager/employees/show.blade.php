<x-app-layout>
    @php
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

        $employeeStatus = $employee->status ?? 'active';
        $employmentType = $employee->employment_type ?? 'full_time';
        $initial = strtoupper(substr($employee->name, 0, 1));
    @endphp

    <div class="space-y-6">
        {{-- Breadcrumb Standar Permanen Manager --}}
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <span>Manager</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span>Data Karyawan Tim</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-indigo-600 font-medium">Profil Karyawan</span>
        </div>

        {{-- Header Halaman dengan Tombol Kembali --}}
        <div class="flex items-center gap-3">
            <a
                href="{{ route('manager.employees.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-indigo-600 hover:bg-slate-50 shadow-card transition"
                aria-label="Kembali ke daftar karyawan"
            >
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>

            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Profil Karyawan
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                    Informasi karyawan dan ringkasan aktivitas shift.
                </p>
            </div>
        </div>

        {{-- Header Profil Card --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    @if ($employee->profile_photo)
                        <img
                            src="{{ asset('storage/' . $employee->profile_photo) }}"
                            alt="{{ $employee->name }}"
                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl border-2 border-white object-cover shadow-card"
                        >
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-2xl sm:text-3xl font-bold shadow-card">
                            {{ $initial }}
                        </div>
                    @endif

                    <div class="space-y-1">
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">
                            {{ $employee->name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-900">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5 text-indigo-600"></i>
                                {{ $employee->position?->name ?? 'Posisi belum ditentukan' }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 font-medium text-slate-500">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                                {{ $employee->department?->name ?? 'Tim' }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusClasses[$employeeStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Ringkasan Aktivitas (3 KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            {{-- Card 1: Kehadiran 30 Hari --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700">Kehadiran 30 Hari</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-mono">
                        {{ $attendanceCount }}
                    </p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Shift berstatus hadir atau terlambat</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0 shadow-card">
                    <i data-lucide="calendar-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            {{-- Card 2: Jam Kerja Bulan Ini --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-indigo-600">Jam Kerja Bulan Ini</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-mono">
                        {{ number_format((float) $monthlyHours, 1, ',', '.') }}
                        <span class="text-xs font-sans font-normal text-slate-500">jam</span>
                    </p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Jam reguler ditambah jam lembur</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-xl bg-indigo-50 text-indigo-600 border border-slate-200 flex items-center justify-center shrink-0 shadow-card">
                    <i data-lucide="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>

            {{-- Card 3: Total Shift Bulan Ini --}}
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-5 shadow-card flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700">Total Shift Bulan Ini</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-mono">
                        {{ $monthlyShiftCount }}
                    </p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Shift yang tercatat pada bulan berjalan</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-card">
                    <i data-lucide="calendar-days" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
            </div>
        </div>

        {{-- 2-Kolom Layout: Informasi Karyawan (Kiri) & Riwayat Shift (Kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            {{-- Kolom Kiri: Informasi Karyawan (5 Kolom) --}}
            <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-6 shadow-card space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-200">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Informasi Karyawan</h3>
                </div>

                <div class="space-y-4 text-xs">
                    {{-- Email --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Email</p>
                            <p class="font-bold text-slate-900 mt-0.5 break-all">
                                {{ $employee->email }}
                            </p>
                        </div>
                    </div>

                    {{-- Telepon / WhatsApp --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Nomor Telepon / WhatsApp</p>
                            @if ($employee->phone_number)
                                <a
                                    href="https://wa.me/{{ preg_replace('/\D+/', '', $employee->phone_number) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-bold text-emerald-600 hover:text-emerald-700 mt-0.5"
                                >
                                    <span>{{ $employee->phone_number }}</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            @else
                                <p class="text-slate-500/60 mt-0.5">Belum tersedia</p>
                            @endif
                        </div>
                    </div>

                    {{-- Departemen --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Departemen</p>
                            <p class="font-bold text-slate-900 mt-0.5">
                                {{ $employee->department?->name ?? 'Belum ditentukan' }}
                            </p>
                        </div>
                    </div>

                    {{-- Tipe Karyawan --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Tipe Karyawan</p>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Kuota Jam Kerja Mingguan --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="timer" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Kuota Jam Kerja Mingguan</p>
                            <p class="font-bold text-slate-900 mt-0.5">
                                {{ $employee->max_weekly_hours ?? 'Belum ditentukan' }}
                                @if ($employee->max_weekly_hours !== null)
                                    jam/minggu
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Tanggal Bergabung --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Tanggal Bergabung</p>
                            <p class="font-bold text-slate-900 mt-0.5">
                                {{ $employee->join_date?->format('d M Y') ?? 'Belum tersedia' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Aktivitas Shift 30 Hari Terakhir (7 Kolom) --}}
            <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Aktivitas Shift 30 Hari Terakhir</h3>
                            <p class="text-xs text-slate-500">Riwayat jadwal dan status kehadiran karyawan.</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($recentAssignments as $assignment)
                        @php
                            $shiftStatusLabels = [
                                'scheduled' => 'Terjadwal',
                                'present' => 'Hadir',
                                'absent' => 'Tidak Hadir',
                                'late' => 'Terlambat',
                            ];

                            $shiftStatusClasses = [
                                'scheduled' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'absent' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'late' => 'bg-amber-50 text-amber-700 border-amber-200',
                            ];

                            $shiftStatus = $assignment->status ?? 'scheduled';
                        @endphp

                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-slate-50/50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">
                                        {{ $assignment->shift?->title ?? 'Shift' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
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

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $shiftStatusClasses[$shiftStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $shiftStatusLabels[$shiftStatus] ?? ucfirst($shiftStatus) }}
                            </span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="calendar-x" class="w-6 h-6"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-900">Belum Ada Aktivitas Shift</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Tidak ada jadwal yang tercatat dalam 30 hari terakhir.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tombol Kembali di Bawah --}}
        <div>
            <a
                href="{{ route('manager.employees.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 text-xs font-semibold shadow-card transition"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Karyawan</span>
            </a>
        </div>
    </div>
</x-app-layout>
