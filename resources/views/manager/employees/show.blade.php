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
        <div class="flex items-center gap-2 text-xs text-[#7C7896] mb-2">
            <span>Manager</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span>Data Karyawan Tim</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-[#6C5CE7] font-medium">Profil Karyawan</span>
        </div>

        {{-- Header Halaman dengan Tombol Kembali --}}
        <div class="flex items-center gap-3">
            <a
                href="{{ route('manager.employees.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-2xl border border-[#E2E0F7] bg-white text-[#7C7896] hover:text-[#6C5CE7] hover:bg-[#F8F7FF] shadow-2xs transition"
                aria-label="Kembali ke daftar karyawan"
            >
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>

            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Profil Karyawan
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                    Informasi karyawan dan ringkasan aktivitas shift.
                </p>
            </div>
        </div>

        {{-- Header Profil Card (Modern Lavender Styling) --}}
        <div class="bg-gradient-to-r from-[#F5F3FF] via-white to-[#F8F7FF] rounded-3xl border border-[#E2E0F7] p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($employee->profile_photo)
                        <img
                            src="{{ asset('storage/' . $employee->profile_photo) }}"
                            alt="{{ $employee->name }}"
                            class="w-20 h-20 rounded-2xl border-2 border-white object-cover shadow-2xs"
                        >
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-[#6C5CE7] text-white flex items-center justify-center text-3xl font-bold shadow-2xs">
                            {{ $initial }}
                        </div>
                    @endif

                    <div class="space-y-1">
                        <h2 class="text-xl font-bold text-[#2D2A3E]">
                            {{ $employee->name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-[#7C7896]">
                            <span class="inline-flex items-center gap-1 font-semibold text-[#2D2A3E]">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                {{ $employee->position?->name ?? 'Posisi belum ditentukan' }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 font-medium text-[#7C7896]">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                {{ $employee->department?->name ?? 'Tim' }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusClasses[$employeeStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $statusLabels[$employeeStatus] ?? ucfirst($employeeStatus) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Ringkasan Aktivitas (3 KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Card 1: Kehadiran 30 Hari --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-700">Kehadiran 30 Hari</p>
                    <p class="text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ $attendanceCount }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Shift berstatus hadir atau terlambat</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Card 2: Jam Kerja Bulan Ini --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#6C5CE7]">Jam Kerja Bulan Ini</p>
                    <p class="text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ number_format((float) $monthlyHours, 1, ',', '.') }}
                        <span class="text-xs font-sans font-normal text-[#7C7896]">jam</span>
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Jam reguler ditambah jam lembur</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] border border-[#E2E0F7] flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Card 3: Total Shift Bulan Ini --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-700">Total Shift Bulan Ini</p>
                    <p class="text-3xl font-extrabold text-[#2D2A3E] mt-1 font-mono">
                        {{ $monthlyShiftCount }}
                    </p>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Shift yang tercatat pada bulan berjalan</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="calendar-days" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        {{-- 2-Kolom Layout: Informasi Karyawan (Kiri) & Riwayat Shift (Kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            {{-- Kolom Kiri: Informasi Karyawan (5 Kolom) --}}
            <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E2E0F7] p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-[#E2E0F7]">
                    <div class="w-8 h-8 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-[#2D2A3E]">Informasi Karyawan</h3>
                </div>

                <div class="space-y-4 text-xs">
                    {{-- Email --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Email</p>
                            <p class="font-bold text-[#2D2A3E] mt-0.5 break-all">
                                {{ $employee->email }}
                            </p>
                        </div>
                    </div>

                    {{-- Telepon / WhatsApp --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Nomor Telepon / WhatsApp</p>
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
                                <p class="text-[#7C7896]/60 mt-0.5">Belum tersedia</p>
                            @endif
                        </div>
                    </div>

                    {{-- Departemen --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Departemen</p>
                            <p class="font-bold text-[#2D2A3E] mt-0.5">
                                {{ $employee->department?->name ?? 'Belum ditentukan' }}
                            </p>
                        </div>
                    </div>

                    {{-- Tipe Karyawan --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Tipe Karyawan</p>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $employmentClasses[$employmentType] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $employmentLabels[$employmentType] ?? ucfirst($employmentType) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Kuota Jam Kerja Mingguan --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="timer" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Kuota Jam Kerja Mingguan</p>
                            <p class="font-bold text-[#2D2A3E] mt-0.5">
                                {{ $employee->max_weekly_hours ?? 'Belum ditentukan' }}
                                @if ($employee->max_weekly_hours !== null)
                                    jam/minggu
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Tanggal Bergabung --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#F8F7FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#7C7896]">Tanggal Bergabung</p>
                            <p class="font-bold text-[#2D2A3E] mt-0.5">
                                {{ $employee->join_date?->format('d M Y') ?? 'Belum tersedia' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Aktivitas Shift 30 Hari Terakhir (7 Kolom) --}}
            <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-[#E2E0F7] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#2D2A3E]">Aktivitas Shift 30 Hari Terakhir</h3>
                            <p class="text-xs text-[#7C7896]">Riwayat jadwal dan status kehadiran karyawan.</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-[#E2E0F7] text-xs">
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

                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-[#F8F7FF]/50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center shrink-0 border border-[#E2E0F7]">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-[#2D2A3E]">
                                        {{ $assignment->shift?->title ?? 'Shift' }}
                                    </p>
                                    <p class="text-[11px] text-[#7C7896] mt-0.5">
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
                        <div class="p-8 text-center text-[#7C7896]">
                            <div class="w-12 h-12 rounded-2xl bg-[#F5F3FF] text-[#6C5CE7] flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="calendar-x" class="w-6 h-6"></i>
                            </div>
                            <p class="text-xs font-bold text-[#2D2A3E]">Belum Ada Aktivitas Shift</p>
                            <p class="text-[11px] text-[#7C7896] mt-0.5">Tidak ada jadwal yang tercatat dalam 30 hari terakhir.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tombol Kembali di Bawah --}}
        <div>
            <a
                href="{{ route('manager.employees.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E0F7] bg-white text-[#2D2A3E] hover:bg-[#F8F7FF] text-xs font-semibold shadow-2xs transition"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Karyawan</span>
            </a>
        </div>
    </div>
</x-app-layout>
