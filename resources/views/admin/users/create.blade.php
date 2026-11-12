<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-[#7C7896] mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-[#6C5CE7] transition">Pengguna</a>
                    <span>/</span>
                    <span class="text-[#2D2A3E]">Tambah Baru</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Tambah Pengguna Baru
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Daftarkan akun staf baru untuk peran Manager atau Employee.
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

    <div class="max-w-4xl" 
         x-data="{
             selectedRole: '{{ old('role', 'employee') }}',
             selectedDepartment: '{{ old('department_id', '') }}'
         }">
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-[#E2E0F7] flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7]">
                    <i data-lucide="user-plus" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">Formulir Pendaftaran Pengguna</h2>
                    <p class="text-xs text-[#7C7896]">Lengkapi data pribadi, penempatan divisi, dan kredensial akun.</p>
                </div>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

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
                                   value="{{ old('name') }}" 
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
                                   value="{{ old('email') }}" 
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
                                   value="{{ old('phone_number') }}" 
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
                        {{-- Peran (Role: Manager & Employee saja) --}}
                        <div class="md:col-span-2">
                            <label for="role" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Peran Akun (Role) <span class="text-red-500">*</span>
                            </label>
                            <select name="role" 
                                    id="role" 
                                    x-model="selectedRole" 
                                    required 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('role') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                <option value="employee">Employee (Karyawan)</option>
                                <option value="manager">Manager (Manajer)</option>
                            </select>
                            <p class="text-xs text-[#7C7896] mt-1">
                                Sesuai kebijakan keamanan, pembuatan akun Super Admin dibatasi dan tidak tersedia pada formulir ini.
                            </p>
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
                                       @checked(old('is_global_manager'))
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
                                            @selected(old('position_id') == $position->id)>
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
                                <option value="full_time" @selected(old('employment_type', 'full_time') === 'full_time')>Full-Time (Penuh Waktu)</option>
                                <option value="part_time" @selected(old('employment_type') === 'part_time')>Part-Time (Paruh Waktu)</option>
                                <option value="freelance" @selected(old('employment_type') === 'freelance')>Freelance (Lepas)</option>
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
                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Kata Sandi (Password) <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   placeholder="Minimal 8 karakter" 
                                   required 
                                   minlength="8" 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
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
                                Konfirmasi Kata Sandi <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   placeholder="Ulangi kata sandi" 
                                   required 
                                   minlength="8" 
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                        </div>

                        {{-- Status Akun --}}
                        <div>
                            <label for="status" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Status Akun <span class="text-red-500">*</span>
                            </label>
                            <select name="status" 
                                    id="status" 
                                    required 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('status') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                <option value="active" @selected(old('status', 'active') === 'active')>Aktif (Active)</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Non-aktif (Inactive)</option>
                            </select>
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
                        <span>Simpan Pengguna</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>