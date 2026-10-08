<section>
    <header class="border-b border-[#E2E0F7] pb-4 mb-6">
        <h2 class="text-lg font-bold text-[#2D2A3E] flex items-center gap-2">
            <i data-lucide="lock" class="w-5 h-5 text-[#6C5CE7]"></i>
            {{ __('Keamanan & Kata Sandi') }}
        </h2>
        <p class="mt-1 text-xs md:text-sm text-[#7C7896]">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="font-semibold text-xs text-[#2D2A3E]" />
            <x-text-input id="update_password_current_password" 
                          name="current_password" 
                          type="password" 
                          class="mt-1 block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                          autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="font-semibold text-xs text-[#2D2A3E]" />
            <x-text-input id="update_password_password" 
                          name="password" 
                          type="password" 
                          class="mt-1 block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="font-semibold text-xs text-[#2D2A3E]" />
            <x-text-input id="update_password_password_confirmation" 
                          name="password_confirmation" 
                          type="password" 
                          class="mt-1 block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-[#6C5CE7] focus:ring-[#6C5CE7]" 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center gap-4 pt-3">
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6C5CE7] hover:bg-[#5B4BC4] active:bg-[#4F46E5] text-white text-xs md:text-sm font-semibold rounded-xl shadow-xs transition focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:ring-offset-2">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>{{ __('Perbarui Sandi') }}</span>
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="inline-flex items-center gap-1.5 text-xs md:text-sm font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>{{ __('Sandi berhasil diperbarui.') }}</span>
                </p>
            @endif
        </div>
    </form>
</section>