<header class="h-[64px] md:h-[72px] bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 shrink-0 z-10 sticky top-0">
    {{-- Bagian Kiri Navbar --}}
    <div class="flex items-center gap-3 md:gap-5">
        {{-- Tampil di mobile saja: Logo Singkat --}}
        <div class="md:hidden flex items-center gap-2">
            <div class="bg-[#0B6B5A] text-white rounded-lg p-1.5 flex items-center justify-center">
                <i data-lucide="check" class="h-4 w-4 stroke-[3]"></i>
            </div>
            <span class="text-[17px] font-bold text-slate-800 tracking-tight">Jadwal<span style="color: var(--c-primary, #0B6B5A);">In</span></span>
        </div>

        {{-- Nama Perusahaan / Cabang --}}
        <div class="flex items-center h-8 gap-2 text-[12px] md:text-[13px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg max-w-[200px] md:max-w-[320px]">
            <i data-lucide="store" class="h-4 w-4 shrink-0" style="color: var(--c-primary, #0B6B5A);"></i>
            <span class="truncate">{{ $companyName }}</span>
        </div>

        {{-- Jam & Tanggal Realtime (Sembunyi di mobile kecil) --}}
        <div class="hidden sm:flex items-center gap-2 text-[12px] text-slate-500 font-medium border-l border-slate-200 pl-4 md:pl-5">
            <i data-lucide="calendar-days" class="h-4 w-4 shrink-0 text-slate-400"></i>
            <span id="navbar-date">{{ $tanggalStr }}</span>
            <span class="hidden md:inline text-slate-300">|</span>
            <span id="navbar-clock" class="hidden md:inline font-mono font-semibold text-slate-600">{{ $now->format('H:i') }}</span>
            <span class="hidden md:inline text-[11px] text-slate-400 font-semibold">WIB</span>
        </div>
    </div>

    {{-- Bagian Kanan Navbar --}}
    <div class="flex items-center gap-2 md:gap-3">
        <button type="button" class="relative text-slate-400 hover:text-slate-700 transition p-2 rounded-lg hover:bg-slate-50" title="Notifikasi">
            <i data-lucide="bell" class="h-5 w-5"></i>
            <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

        <button type="button" class="hidden md:block text-slate-400 hover:text-slate-700 transition p-2 rounded-lg hover:bg-slate-50" title="Bantuan">
            <i data-lucide="help-circle" class="h-5 w-5"></i>
        </button>

        {{-- Dropdown Profil User (Alpine.js) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" 
                    type="button"
                    class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition focus:outline-none"
                    title="{{ $userName }}">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-8 w-8 rounded-full object-cover ring-2 ring-slate-100">
                @else
                    <div class="h-8 w-8 bg-[#0B6B5A] text-white rounded-full flex items-center justify-center font-bold text-[12px] ring-2 ring-slate-100">
                        {{ $initials }}
                    </div>
                @endif
                <i data-lucide="chevron-down" class="hidden md:block h-3.5 w-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>

            {{-- Dropdown Menu --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/50">
                    <p class="text-[12px] font-bold text-slate-800 truncate">{{ $userName }}</p>
                    <p class="text-[11px] text-slate-500 truncate capitalize">{{ $positionName }}</p>
                </div>

                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center gap-2.5 px-4 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 transition">
                    <i data-lucide="user" class="h-4 w-4 text-slate-400"></i>
                    <span>Edit Profil</span>
                </a>

                @auth
                <div class="border-t border-slate-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-[12.5px] text-red-600 hover:bg-red-50 transition text-left">
                        <i data-lucide="log-out" class="h-4 w-4 text-red-500"></i>
                        <span>Keluar</span>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</header>
