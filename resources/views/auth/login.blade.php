<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-[22px] font-bold text-slate-800">Selamat Datang di Jadwalin</h2>
        <p class="text-[13.5px] text-slate-500 mt-2 max-w-[320px] mx-auto leading-relaxed">
            Masuk untuk mengelola dan memantau jadwal kerja Anda secara lebih teratur
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-bold text-[13px] text-slate-700 mb-1.5">
                Email / Username
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="user@perusahaan.com"
                    class="block w-full pl-10 pr-4 py-2.5 bg-[#F4F6F9] border-transparent rounded-lg text-[14px] text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#0B6B5A] focus:ring focus:ring-[#0B6B5A]/20 transition-colors shadow-none" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block font-bold text-[13px] text-slate-700">
                    Kata Sandi
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[12.5px] font-bold text-[#0B6B5A] hover:text-[#085245] transition-colors">
                        Lupa Kata Sandi?
                    </a>
                @endif
            </div>
            
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                    class="block w-full pl-10 pr-10 py-2.5 bg-[#F4F6F9] border-transparent rounded-lg text-[14px] text-slate-800 placeholder-slate-400 tracking-widest focus:bg-white focus:border-[#0B6B5A] focus:ring focus:ring-[#0B6B5A]/20 transition-colors shadow-none" />
                
                <!-- Toggle Password Visibility -->
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                    <!-- Eye Icon (Show) -->
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <!-- Eye Off Icon (Hide) -->
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                        <line x1="2" x2="22" y1="2" y2="22"></line>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-7">
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-[#0B6B5A] hover:bg-[#085245] text-white text-[14px] font-bold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#0B6B5A] focus:ring-offset-2">
                Masuk ke Workspace
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"></path>
                    <path d="m12 5 7 7-7 7"></path>
                </svg>
            </button>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-[12.5px] text-slate-500">
                Belum memiliki Akun? 
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="font-bold text-[#0B6B5A] hover:text-[#085245] ml-0.5">Daftar Sekarang ></a>
                @else
                    <a href="#" class="font-bold text-[#0B6B5A] hover:text-[#085245] ml-0.5">Daftar Sekarang ></a>
                @endif
            </p>
        </div>
    </form>
</x-guest-layout>
