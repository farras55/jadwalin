<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            {{-- Breadcrumb Navigation --}}
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-xs font-medium text-[#7C7896]">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#6C5CE7]">Admin</a>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li aria-current="page" class="font-semibold text-[#6C5CE7]">Perusahaan &amp; Cabang</li>
                </ol>
            </nav>

            {{-- Page Header & Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                        Perusahaan &amp; Cabang
                    </h1>
                    <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                        Kelola identitas legalitas organisasi, lisensi tenant, dan zona waktu operasional.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#6C5CE7] bg-white border border-[#E2E0F7] hover:bg-[#F5F3FF] transition shadow-2xs">
                        <i data-lucide="sliders" class="h-4 w-4"></i>
                        <span>Pengaturan Regulasi PP 35</span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Ringkasan Statistik Organisasi & Lisensi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Card Identitas Organisasi --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-6 shadow-2xs flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center shrink-0">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Identitas Organisasi</p>
                    <h3 class="text-base md:text-lg font-bold text-[#2D2A3E] truncate mt-0.5">
                        {{ $company?->name ?? 'Perusahaan Tenant' }}
                    </h3>
                    <p class="text-xs text-[#7C7896] mt-1 flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                        <span>Zona Waktu: <strong class="text-[#2D2A3E]">{{ match($company?->timezone) { 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)', default => 'WIB (Asia/Jakarta)' } }}</strong></span>
                    </p>
                </div>
            </div>

            {{-- Card Lisensi & Kapasitas Operasional --}}
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-6 shadow-2xs flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#7C7896]">Lisensi &amp; Kapasitas Tenant</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Status Aktif
                        </span>
                        <span class="text-xs text-[#7C7896]">&bull; 1 Kantor Pusat</span>
                    </div>
                    <p class="text-xs text-[#7C7896] mt-1 flex items-center gap-2">
                        <span><strong class="text-[#2D2A3E]">{{ $company?->departments_count ?? $company?->departments()->count() ?? 0 }}</strong> Departemen</span>
                        <span>&bull;</span>
                        <span><strong class="text-[#2D2A3E]">{{ $company?->users_count ?? $company?->users()->count() ?? 0 }}</strong> Pengguna Terdaftar</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Profil & Legalitas Perusahaan --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-[#E2E0F7]">
                <h2 class="text-base font-bold text-[#2D2A3E]">Formulir Detail Perusahaan</h2>
                <p class="text-xs text-[#7C7896] mt-0.5">Perbarui informasi kontak kantor dan wilayah zona waktu operasional.</p>
            </div>

            <form action="{{ route('admin.companies.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama Legal --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-[#2D2A3E] mb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                <span>Nama Legal Perusahaan <span class="text-rose-500">*</span></span>
                            </span>
                        </label>
                        <input 
                            type="text" 
                            id="name"
                            name="name" 
                            value="{{ old('name', $company?->name) }}" 
                            required
                            placeholder="Contoh: PT Senja Wisata Nusantara"
                            class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] px-3.5 py-2.5 text-[#2D2A3E] placeholder-[#7C7896]/50 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 transition-all shadow-2xs"
                        >
                        @error('name') 
                            <p class="mt-1.5 text-xs font-medium text-rose-500 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p> 
                        @enderror
                    </div>

                    {{-- Email Perusahaan --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#2D2A3E] mb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                <span>Email Perusahaan <span class="text-rose-500">*</span></span>
                            </span>
                        </label>
                        <input 
                            type="email" 
                            id="email"
                            name="email" 
                            value="{{ old('email', $company?->email) }}" 
                            required
                            placeholder="Contoh: official@senjawisata.co.id"
                            class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] px-3.5 py-2.5 text-[#2D2A3E] placeholder-[#7C7896]/50 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 transition-all shadow-2xs"
                        >
                        @error('email') 
                            <p class="mt-1.5 text-xs font-medium text-rose-500 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p> 
                        @enderror
                    </div>

                    {{-- Telepon Kantor --}}
                    <div>
                        <label for="phone_number" class="block text-xs font-semibold text-[#2D2A3E] mb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                <span>No. Telepon Kantor <span class="text-rose-500">*</span></span>
                            </span>
                        </label>
                        <input 
                            type="text" 
                            id="phone_number"
                            name="phone_number" 
                            value="{{ old('phone_number', $company?->phone_number) }}" 
                            required
                            placeholder="Contoh: 021-7890123"
                            class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] px-3.5 py-2.5 text-[#2D2A3E] placeholder-[#7C7896]/50 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 transition-all shadow-2xs"
                        >
                        @error('phone_number') 
                            <p class="mt-1.5 text-xs font-medium text-rose-500 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p> 
                        @enderror
                    </div>

                    {{-- Zona Waktu --}}
                    <div>
                        <label for="timezone" class="block text-xs font-semibold text-[#2D2A3E] mb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                <span>Zona Waktu Operasional <span class="text-rose-500">*</span></span>
                            </span>
                        </label>
                        <select 
                            id="timezone"
                            name="timezone" 
                            required
                            class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] px-3.5 py-2.5 text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 transition-all shadow-2xs bg-white"
                        >
                            <option value="Asia/Jakarta" {{ old('timezone', $company?->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB - Waktu Indonesia Barat (Asia/Jakarta)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $company?->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA - Waktu Indonesia Tengah (Asia/Makassar)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $company?->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT - Waktu Indonesia Timur (Asia/Jayapura)</option>
                        </select>
                        @error('timezone') 
                            <p class="mt-1.5 text-xs font-medium text-rose-500 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p> 
                        @enderror
                    </div>

                    {{-- Alamat Fisik --}}
                    <div class="md:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-[#2D2A3E] mb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                <span>Alamat Fisik Outlet / Kantor Pusat <span class="text-rose-500">*</span></span>
                            </span>
                        </label>
                        <textarea 
                            id="address"
                            name="address" 
                            rows="3" 
                            required
                            placeholder="Alamat lengkap gedung, jalan, dan nomor kantor..."
                            class="w-full text-xs md:text-sm rounded-xl border border-[#E2E0F7] p-3.5 text-[#2D2A3E] placeholder-[#7C7896]/50 focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 transition-all shadow-2xs"
                        >{{ old('address', $company?->address) }}</textarea>
                        @error('address') 
                            <p class="mt-1.5 text-xs font-medium text-rose-500 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p> 
                        @enderror
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="mt-8 pt-5 border-t border-[#E2E0F7] flex items-center justify-end gap-3">
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs md:text-sm font-semibold text-white bg-[#6C5CE7] hover:bg-[#5A4AD1] transition-all shadow-sm cursor-pointer"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
