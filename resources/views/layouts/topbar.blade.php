<header class="h-14 md:h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 md:px-6 shrink-0 z-10 sticky top-0">
    {{-- Bagian Kiri Navbar --}}
    <div class="flex items-center gap-3 md:gap-4">
        {{-- Tampil di mobile saja: Logo Singkat (Tanpa Hamburger, Navigasi Pindah ke Bottom Bar) --}}
        <div class="md:hidden flex items-center gap-2">
            <x-application-logo class="h-6 w-auto shrink-0" />
            <span class="text-[16px] font-bold text-slate-900 tracking-tight">Jadwal<span class="text-[#6C5CE7]">in</span></span>
        </div>

        {{-- Nama Perusahaan / Cabang --}}
        <div class="hidden sm:flex items-center h-8 gap-2 text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg max-w-[180px] md:max-w-[320px] transition hover:bg-slate-100/80" title="{{ $companyName }}">
            <i data-lucide="building-2" class="h-3.5 w-3.5 shrink-0 text-slate-500"></i>
            <span class="truncate">{{ $companyName }}</span>
        </div>

        {{-- Jam & Tanggal Realtime (Sembunyi di mobile kecil) --}}
        @php
            $tzLabel = match($timezone ?? 'Asia/Jakarta') {
                'Asia/Makassar' => 'WITA',
                'Asia/Jayapura' => 'WIT',
                default => 'WIB',
            };
        @endphp
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 font-medium border-l border-slate-200 pl-3 md:pl-4">
            <i data-lucide="calendar" class="h-3.5 w-3.5 shrink-0 text-slate-400"></i>
            <span id="navbar-date">{{ $tanggalStr }}</span>
            <span class="hidden md:inline text-slate-200">|</span>
            <span id="navbar-clock" class="hidden md:inline font-mono font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">{{ $now->format('H:i') }}</span>
            <span class="hidden md:inline text-[10px] text-slate-400 font-semibold uppercase">{{ $tzLabel }}</span>
        </div>
    </div>

    {{-- Bagian Kanan Navbar --}}
    <div class="flex items-center gap-2 md:gap-2.5">

        {{-- Notifikasi --}}
        <div class="relative" x-data="{ showNotif: false }" @click.outside="showNotif = false">
            <button
                type="button"
                @click="showNotif = !showNotif"
                class="relative text-slate-400 hover:text-slate-700 transition p-2 rounded-lg hover:bg-slate-100 focus:outline-none"
                title="Notifikasi"
            >
                <i data-lucide="bell" class="h-4 w-4"></i>

                @if($unreadNotifCount > 0)
                    <span class="absolute 1 top-1 right-1 bg-rose-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-[16px] flex items-center justify-center ring-2 ring-white">
                        {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                    </span>
                @endif
            </button>

            {{-- Dropdown Notifikasi --}}
            <div
                x-show="showNotif"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-[calc(100vw-32px)] sm:w-[340px] max-h-[400px] overflow-y-auto bg-white rounded-xl border border-slate-200 shadow-xl z-50"
                style="display: none;"
            >
                {{-- Header Dropdown --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                            Notifikasi
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            {{ $unreadNotifCount }} pesan belum dibaca
                        </p>
                    </div>

                    <a
                        href="{{ route('notifications.index') }}"
                        class="text-[11px] font-semibold text-[#6C5CE7] hover:underline"
                    >
                        Lihat Semua
                    </a>
                </div>

                {{-- 5 Notifikasi Terbaru --}}
                @forelse($latestNotifications as $notification)
                    <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition">
                        <div class="flex items-start gap-2.5">
                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notification->is_read ? 'bg-slate-200' : 'bg-[#6C5CE7]' }}"></div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-slate-900 truncate">
                                    {{ $notification->title }}
                                </p>

                                <p class="mt-0.5 text-[11px] leading-relaxed text-slate-600 line-clamp-2">
                                    {{ $notification->message }}
                                </p>

                                <p class="mt-1 text-[10px] text-slate-400 font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>

                                @if(!$notification->is_read)
                                    <form
                                        action="{{ route('notifications.read', $notification) }}"
                                        method="POST"
                                        class="mt-1.5"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="text-[10px] font-semibold text-[#6C5CE7] hover:underline"
                                        >
                                            Tandai sudah dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-10 text-center">
                        <i data-lucide="inbox" class="h-7 w-7 text-slate-300 mb-2"></i>

                        <p class="text-xs font-semibold text-slate-700">
                            Belum ada notifikasi
                        </p>

                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Pemberitahuan aktivitas akan muncul di sini.
                        </p>
                    </div>
                @endforelse

                {{-- Footer --}}
                <div class="border-t border-slate-100 px-4 py-2 bg-slate-50/50">
                    <a
                        href="{{ route('notifications.index') }}"
                        class="block text-center text-[11px] font-semibold text-[#6C5CE7] hover:underline"
                    >
                        Buka Pusat Notifikasi &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Dropdown Profil User (Alpine.js) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" 
                    type="button"
                    class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100 transition focus:outline-none"
                    title="{{ $userName }}">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-7 w-7 rounded-full object-cover ring-1 ring-slate-200">
                @else
                    <div class="h-7 w-7 bg-slate-800 text-white rounded-full flex items-center justify-center font-bold text-[11px]">
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
                 class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50 overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/80">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ $userName }}</p>
                    <p class="text-[11px] text-slate-500 truncate capitalize mt-0.5">{{ $positionName }}</p>
                </div>

                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    <i data-lucide="user" class="h-3.5 w-3.5 text-slate-400"></i>
                    <span>Edit Profil</span>
                </a>

                @auth
                <div class="border-t border-slate-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}" x-data="{ loggingOut: false }" @submit="if(loggingOut) { $event.preventDefault(); return false; } loggingOut = true;">
                    @csrf
                    <button type="submit" 
                            :disabled="loggingOut"
                            :class="loggingOut ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition text-left">
                        <i data-lucide="log-out" class="h-3.5 w-3.5 text-rose-500"></i>
                        <span x-text="loggingOut ? 'Memproses Keluar...' : 'Keluar'">Keluar</span>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</header>
    </div>
</header>
