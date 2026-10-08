<section x-data="{
    photoName: null,
    photoPreview: null
}">
    <header class="border-b border-[#E2E0F7] pb-4 mb-6">
        <h2 class="text-lg font-bold text-[#2D2A3E] flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5 text-[#6C5CE7]"></i>
            {{ __('Informasi Profil & Kontak') }}
        </h2>
        <p class="mt-1 text-xs md:text-sm text-[#7C7896]">
            {{ __('Perbarui data identitas pribadi, foto profil, dan informasi kontak akun Anda.') }}
        </p>
    </header>

    @if (Route::has('verification.send'))
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
    @endif

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('patch')

        {{-- ===== FOTO PROFIL / AVATAR UPLOAD (Alpine.js Preview) ===== --}}
        <div class="p-4 rounded-xl bg-[#F5F3FF]/60 border border-[#E2E0F7]">
            <label class="block text-xs font-semibold text-[#2D2A3E] mb-3">
                {{ __('Foto Profil Pengguna') }}
            </label>
            
            <div class="flex flex-col sm:flex-row items-center gap-5">
                {{-- Foto Saat Ini --}}
                <div x-show="!photoPreview" class="relative shrink-0">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-[#6C5CE7] shadow-sm">
                    @else
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-[#6C5CE7] to-[#4F46E5] text-white flex items-center justify-center font-bold text-2xl ring-2 ring-[#E2E0F7] shadow-sm">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- Preview Foto Baru --}}
                <div x-show="photoPreview" style="display: none;" class="relative shrink-0">
                    <span class="block h-20 w-20 rounded-2xl bg-cover bg-no-repeat bg-center ring-2 ring-[#6C5CE7] shadow-sm"
                          :style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                {{-- Action Upload & Info --}}
                <div class="flex-1 space-y-2 text-center sm:text-left">
                    <input type="file" 
                           id="profile_photo" 
                           name="profile_photo" 
                           class="hidden" 
                           x-ref="photo"
                           accept="image/png, image/jpeg, image/jpg, image/webp"
                           x-on:change="
                                if ($refs.photo.files.length > 0) {
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                                }
                           ">

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <button type="button" 
                                x-on:click.prevent="$refs.photo.click()" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-[#E2E0F7] text-[#2D2A3E] hover:bg-[#F5F3FF] hover:border-[#6C5CE7] transition shadow-xs">
                            <i data-lucide="camera" class="w-4 h-4 text-[#6C5CE7]"></i>
                            <span>{{ __('Pilih Foto Baru') }}</span>
                        </button>

                        <button type="button" 
                                x-show="photoPreview" 
                                x-on:click="photoPreview = null; $refs.photo.value = null;" 
                                class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-xl text-red-600 hover:bg-red-50 transition"
                                style="display: none;">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>{{ __('Batal') }}</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-[#7C7896]">
                        Format yang didukung: JPG, PNG, atau WEBP. Ukuran file maksimal <strong>2MB</strong>.
                    </p>
                    <x-input-error class="mt-1" :messages="$errors->get('profile_photo')" />
                </div>
            </div>
        </div>

        {{-- ===== DATA FORM UTAMA ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Nama Lengkap --}}
            <div class="md:col-span-2">
                <x-input-label for="name" :value="__('Nama Lengkap')" class="font-semibold text-xs text-[#2D2A3E]" />
                <div class="relative mt-1">
                    <x-text-input id="name" 
                                  name="name" 
                                  type="text" 
                                  class="block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                                  :value="old('name', $user->name)" 
                                  required 
                                  autofocus 
                                  autocomplete="name" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            {{-- Alamat Email --}}
            <div>
                <x-input-label for="email" :value="__('Alamat Email')" class="font-semibold text-xs text-[#2D2A3E]" />
                <div class="relative mt-1">
                    <x-text-input id="email" 
                                  name="email" 
                                  type="email" 
                                  class="block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                                  :value="old('email', $user->email)" 
                                  required 
                                  autocomplete="username" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('email')" />

                @if (Route::has('verification.send') && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2 text-xs text-[#7C7896]">
                        <p>
                            {{ __('Email Anda belum terverifikasi.') }}
                            <button form="send-verification" class="text-[#6C5CE7] font-semibold underline hover:text-[#4F46E5] focus:outline-none">
                                {{ __('Kirim ulang email verifikasi.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-1 font-semibold text-green-600">
                                {{ __('Tautan verifikasi baru telah dikirimkan ke alamat email Anda.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Nomor Telepon / WhatsApp --}}
            <div>
                <x-input-label for="phone_number" :value="__('Nomor HP / WhatsApp')" class="font-semibold text-xs text-[#2D2A3E]" />
                <div class="relative mt-1">
                    <x-text-input id="phone_number" 
                                  name="phone_number" 
                                  type="tel" 
                                  placeholder="Contoh: 08123456789" 
                                  class="block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                                  :value="old('phone_number', $user->phone_number)" 
                                  autocomplete="tel" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('phone_number')" />
            </div>
        </div>

        {{-- ===== INFORMASI ORGANISASI & PEKERJAAN (READ-ONLY) ===== --}}
        <div class="pt-4 border-t border-[#E2E0F7]">
            <h3 class="text-xs font-bold text-[#7C7896] uppercase tracking-wider mb-3">
                {{ __('Informasi Organisasi & Penugasan (Dikelola oleh Admin/Manager)') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Perusahaan / Outlet') }}</span>
                    <span class="text-xs font-bold text-[#2D2A3E] mt-0.5 block truncate">{{ $user->company->name ?? 'Jadwalin System' }}</span>
                </div>

                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Departemen') }}</span>
                    <span class="text-xs font-bold text-[#2D2A3E] mt-0.5 block truncate">{{ $user->department->name ?? 'Semua Departemen' }}</span>
                </div>

                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Posisi / Jabatan') }}</span>
                    <span class="text-xs font-bold text-[#2D2A3E] mt-0.5 block truncate">{{ $user->position->name ?? 'Staff Umum' }}</span>
                </div>

                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Peran Sistem (Role)') }}</span>
                    <span class="text-xs font-bold text-[#6C5CE7] mt-0.5 block capitalize">{{ $user->role ?? 'Employee' }}</span>
                </div>

                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Tipe Karyawan') }}</span>
                    <span class="text-xs font-bold text-[#2D2A3E] mt-0.5 block capitalize">{{ str_replace('_', ' ', $user->employment_type ?? 'full_time') }}</span>
                </div>

                <div class="p-3 bg-[#F5F3FF]/40 rounded-xl border border-[#E2E0F7]">
                    <span class="block text-[10px] font-semibold text-[#7C7896] uppercase">{{ __('Batas Kerja Mingguan') }}</span>
                    <span class="text-xs font-bold text-[#2D2A3E] mt-0.5 block">
                        {{ $user->max_weekly_hours ? $user->max_weekly_hours . ' Jam / Minggu' : '40 Jam (PP 35/2021)' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ===== TOMBOL SIMPAN & NOTIFIKASI ===== --}}
        <div class="flex items-center gap-4 pt-3 border-t border-[#E2E0F7]">
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6C5CE7] hover:bg-[#5B4BC4] active:bg-[#4F46E5] text-white text-xs md:text-sm font-semibold rounded-xl shadow-xs transition focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:ring-offset-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>{{ __('Simpan Perubahan') }}</span>
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="inline-flex items-center gap-1.5 text-xs md:text-sm font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>{{ __('Perubahan profil berhasil disimpan.') }}</span>
                </p>
            @endif
        </div>
    </form>
</section>