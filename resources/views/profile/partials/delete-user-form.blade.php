<section class="space-y-4">
    <header class="border-b border-red-200 pb-3">
        <h2 class="text-base font-bold text-red-700 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
            {{ __('Zona Berbahaya: Hapus Akun') }}
        </h2>
        <p class="mt-1 text-xs text-red-600/80">
            {{ __('Setelah akun dihapus, seluruh data dan hak akses Anda akan dinonaktifkan secara permanen.') }}
        </p>
    </header>

    <div>
        <button type="button"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            <span>{{ __('Hapus Akun Saya') }}</span>
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-100 text-red-600 rounded-2xl shrink-0">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#2D2A3E]">
                        {{ __('Konfirmasi Penghapusan Akun') }}
                    </h2>
                    <p class="mt-1 text-xs text-[#7C7896]">
                        {{ __('Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi bahwa Anda benar-benar ingin menghapus akun ini.') }}
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <x-input-label for="password" value="{{ __('Kata Sandi Konfirmasi') }}" class="text-xs font-semibold text-[#2D2A3E]" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full text-xs md:text-sm rounded-xl border-[#E2E0F7] focus:border-red-500 focus:ring-red-500"
                    placeholder="{{ __('Masukkan kata sandi Anda') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        x-on:click="$dispatch('close')" 
                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-[#E2E0F7] text-[#2D2A3E] hover:bg-[#F5F3FF] transition">
                    {{ __('Batal') }}
                </button>

                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>{{ __('Ya, Hapus Akun') }}</span>
                </button>
            </div>
        </form>
    </x-modal>
</section>