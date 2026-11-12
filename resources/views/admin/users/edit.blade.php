<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-[#7C7896] mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-[#6C5CE7] transition">Pengguna</a>
                    <span>/</span>
                    <span class="text-[#2D2A3E]">Edit</span>
                    <span>/</span>
                    <span class="text-[#7C7896] truncate max-w-xs">{{ $user->name }}</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Edit Pengguna
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Perbarui informasi profil, peran, divisi, atau setel ulang kata sandi.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-white border border-[#E2E0F7] hover:bg-[#F5F3FF] transition shadow-2xs">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6"
         x-data="{
             selectedRole: '{{ old('role', $user->role) }}',
             selectedDepartment: '{{ old('department_id', $user->department_id ?? '') }}'
         }">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Edit Form Card --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            {{-- Header Profil Ringkas --}}
            <div class="p-6 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                             alt="{{ $user->name }}" 
                             class="w-12 h-12 rounded-full object-cover border border-[#E2E0F7] shadow-2xs shrink-0">
                    @else
                        @php
                            $initials = collect(explode(' ', $user->name))
                                ->filter()
                                ->take(2)
                                ->map(fn($part) => mb_substr($part, 0, 1))
                                ->join('');
                        @endphp
                        <div class="w-12 h-12 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] font-bold text-sm flex items-center justify-center border border-[#6C5CE7]/20 shrink-0 shadow-2xs">
                            {{ strtoupper($initials ?: 'U') }}
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-[#2D2A3E]">{{ $user->name }}</h2>
                            @if ($user->id === auth()->id())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#6C5CE7] text-white">Akun Anda</span>
                            @endif
                        </div>
                        <p class="text-xs text-[#7C7896] mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Role Badge --}}
                    @if ($user->role === 'superadmin')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                            <i data-lucide="shield" class="h-3.5 w-3.5 text-purple-600"></i>
                            <span>Super Admin</span>
                        </span>
                    @elseif ($user->role === 'manager')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <i data-lucide="user-check" class="h-3.5 w-3.5 text-indigo-600"></i>
                            <span>Manager</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i data-lucide="user" class="h-3.5 w-3.5 text-emerald-600"></i>
                            <span>Employee</span>
                        </span>
                    @endif
                </div>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                {{-- SECTION 1: Informasi Pribadi & Kontak --}}
                <div class="space-y-4">
                    <div class="border-b border-[#E2E0F7] pb-2">
                        <h3 class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                            <i data-lucide="user" class="h-4 w-4 text-[#6C5CE7]"></i>
                            <span>Informasi Pribadi &amp; Kontak</span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nama Lengkap --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   placeholder="Contoh: Budi Santoso" 
                                   required 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Alamat Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   placeholder="nama@perusahaan.com" 
                                   required 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                            @error('email')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label for="phone_number" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Nomor Telepon / WhatsApp <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="phone_number" 
                                   id="phone_number" 
                                   value="{{ old('phone_number', $user->phone_number) }}" 
                                   placeholder="Contoh: 081234567890" 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('phone_number') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                            @error('phone_number')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Peran & Penempatan Kerja --}}
                <div class="space-y-4">
                    <div class="border-b border-[#E2E0F7] pb-2">
                        <h3 class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                            <i data-lucide="briefcase" class="h-4 w-4 text-[#6C5CE7]"></i>
                            <span>Peran &amp; Penempatan Kerja</span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Peran (Role) --}}
                        <div class="md:col-span-2">
                            <label for="role" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Peran Akun (Role) <span class="text-red-500">*</span>
                            </label>
                            @if ($user->role === 'superadmin')
                                <input type="hidden" name="role" value="superadmin">
                                <div class="w-full px-4 py-2.5 text-sm rounded-xl border border-purple-200 bg-purple-50/60 text-purple-900 font-semibold flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="shield" class="h-4 w-4 text-purple-600"></i>
                                        <span>Super Admin</span>
                                    </span>
                                    <span class="text-xs text-purple-700 font-normal">Peran Super Admin terkunci untuk menjaga stabilitas sistem</span>
                                </div>
                            @else
                                <select name="role" 
                                        id="role" 
                                        x-model="selectedRole" 
                                        required 
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('role') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                    <option value="employee" @selected(old('role', $user->role) === 'employee')>Employee (Karyawan)</option>
                                    <option value="manager" @selected(old('role', $user->role) === 'manager')>Manager (Manajer)</option>
                                </select>
                                <p class="text-xs text-[#7C7896] mt-1">
                                    Peran pengguna dapat dialihkan antara Manager dan Employee.
                                </p>
                            @endif
                            @error('role')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Toggle Global Manager (Khusus jika role == manager) --}}
                        <div class="md:col-span-2 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100" x-show="selectedRole === 'manager'" x-cloak>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="is_global_manager" 
                                       value="1" 
                                       @checked(old('is_global_manager', $user->is_global_manager ?? false))
                                       class="mt-0.5 rounded border-[#E2E0F7] text-[#6C5CE7] focus:ring-[#6C5CE7]/30 h-4 w-4">
                                <div>
                                    <span class="text-xs font-bold text-[#2D2A3E]">Tetapkan sebagai Manajer Global</span>
                                    <p class="text-xs text-[#7C7896] mt-0.5">
                                        Manajer global dapat mengelola jadwal dan melihat kehadiran tim di seluruh departemen perusahaan.
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Departemen --}}
                        <div>
                            <label for="department_id" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Departemen <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                            </label>
                            <select name="department_id" 
                                    id="department_id" 
                                    x-model="selectedDepartment" 
                                    x-on:change="const p = document.getElementById('position_id'); if (p && p.selectedOptions[0] && p.selectedOptions[0].disabled) { p.value = ''; }"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('department_id') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                <option value="">Pilih Departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Posisi / Jabatan --}}
                        <div>
                            <label for="position_id" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Posisi Jabatan <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                            </label>
                            <select name="position_id" 
                                    id="position_id" 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('position_id') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                <option value="">Pilih Posisi Jabatan</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" 
                                            x-show="!selectedDepartment || selectedDepartment == '{{ $position->department_id }}'" 
                                            :disabled="selectedDepartment && selectedDepartment != '{{ $position->department_id }}'"
                                            :hidden="selectedDepartment && selectedDepartment != '{{ $position->department_id }}'"
                                            @selected(old('position_id', $user->position_id) == $position->id)>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-[#7C7896] mt-1">
                                Opsi posisi otomatis menyesuaikan dengan departemen.
                            </p>
                            @error('position_id')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Tipe Kepegawaian --}}
                        <div>
                            <label for="employment_type" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Tipe Kepegawaian <span class="text-red-500">*</span>
                            </label>
                            <select name="employment_type" 
                                    id="employment_type" 
                                    required 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('employment_type') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                <option value="full_time" @selected(old('employment_type', $user->employment_type) === 'full_time')>Full-Time (Penuh Waktu)</option>
                                <option value="part_time" @selected(old('employment_type', $user->employment_type) === 'part_time')>Part-Time (Paruh Waktu)</option>
                                <option value="freelance" @selected(old('employment_type', $user->employment_type) === 'freelance')>Freelance (Lepas)</option>
                            </select>
                            @error('employment_type')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Kredensial & Status Akun --}}
                <div class="space-y-4">
                    <div class="border-b border-[#E2E0F7] pb-2">
                        <h3 class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                            <i data-lucide="lock" class="h-4 w-4 text-[#6C5CE7]"></i>
                            <span>Kredensial &amp; Status Akun</span>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password Opsional --}}
                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Kata Sandi Baru <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   placeholder="Kosongkan jika tidak ingin mengubah" 
                                   minlength="8" 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                            <p class="text-[11px] text-[#7C7896] mt-1">
                                Minimal 8 karakter jika ingin mereset kata sandi akun pengguna ini.
                            </p>
                            @error('password')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Password Confirmation --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   placeholder="Ulangi kata sandi baru" 
                                   minlength="8" 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                        </div>

                        {{-- Status Akun --}}
                        <div class="md:col-span-2 mt-2">
                            <label for="status" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Status Akun <span class="text-red-500">*</span>
                            </label>
                            @if ($user->id === auth()->id())
                                <input type="hidden" name="status" value="active">
                                <div class="w-full md:w-1/2 px-4 py-2.5 text-sm rounded-xl border border-emerald-200 bg-emerald-50/60 text-emerald-900 font-semibold flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                    <span class="text-xs text-emerald-700 font-normal">Anda tidak dapat menonaktifkan akun sendiri</span>
                                </div>
                            @else
                                <select name="status" 
                                        id="status" 
                                        required 
                                        class="w-full md:w-1/2 px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('status') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                    <option value="active" @selected(old('status', $user->status) === 'active')>Aktif (Active)</option>
                                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Non-aktif (Inactive)</option>
                                </select>
                                <p class="text-xs text-[#7C7896] mt-1">
                                    Pengguna non-aktif tidak akan bisa masuk (login) ke dalam sistem perusahaan.
                                </p>
                            @endif
                            @error('status')
                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                    <a href="{{ route('admin.users.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] border border-[#E2E0F7] transition duration-150">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition duration-150 shadow-sm">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Danger Zone: Hapus Akun Pengguna --}}
        <div class="bg-white rounded-2xl border border-red-200 shadow-2xs overflow-hidden">
            <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-600 shrink-0">
                        <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-600">Zona Bahaya: Hapus Pengguna</h3>
                        <p class="text-xs text-[#7C7896] mt-0.5">
                            Menghapus akun pengguna dari sistem (Soft Delete). Akun yang dihapus tidak akan dapat masuk kembali ke sistem.
                        </p>
                    </div>
                </div>

                <div>
                    @if ($user->id === auth()->id())
                        <button type="button" 
                                disabled 
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-400 bg-gray-100 rounded-xl cursor-not-allowed">
                            <i data-lucide="lock" class="h-4 w-4"></i>
                            <span>Akun Anda Terkunci</span>
                        </button>
                    @else
                        <button type="button" 
                                x-data="" 
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')" 
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition duration-150 shadow-sm">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                            <span>Hapus Akun Pengguna</span>
                        </button>

                        {{-- Modal Konfirmasi Hapus --}}
                        <x-modal name="confirm-user-deletion-{{ $user->id }}" :show="false">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6 text-left">
                                @csrf
                                @method('DELETE')

                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-[#2D2A3E]">
                                            Konfirmasi Penghapusan Akun
                                        </h2>
                                        <p class="text-xs text-[#7C7896] mt-0.5">
                                            Tindakan ini akan menonaktifkan pengguna ini dari sistem Jadwalin.
                                        </p>
                                    </div>
                                </div>

                                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed mb-4">
                                    Apakah Anda yakin ingin menghapus akun <strong class="text-[#2D2A3E]">{{ $user->name }}</strong> ({{ $user->email }})?
                                </p>

                                <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
                                    <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600"></i>
                                    <span>Penghapusan akan ditolak otomatis jika pengguna ini masih memiliki penugasan shift yang aktif.</span>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                                    <button type="button" 
                                            x-on:click="$dispatch('close')" 
                                            class="px-4 py-2 text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] rounded-xl transition duration-150">
                                        Batal
                                    </button>
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition duration-150 shadow-sm">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                        <span>Ya, Hapus Pengguna</span>
                                    </button>
                                </div>
                            </form>
                        </x-modal>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>