<aside class="hidden md:flex w-[260px] h-screen flex-shrink-0 flex-col justify-between border-r border-slate-200 bg-[#F8FAFC] sticky top-0">
    <div class="flex flex-col flex-1 min-h-0">
        {{-- Logo --}}
        <div class="px-6 py-5 flex items-center gap-2.5">
            <div class="bg-[#0B6B5A] text-white rounded-lg p-1.5 flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="check" class="h-4 w-4 stroke-[3]"></i>
            </div>
            <span class="text-[19px] font-bold text-slate-800 tracking-tight">Jadwal<span style="color: var(--c-primary, #0B6B5A);">In</span></span>
        </div>

        {{-- Role / Posisi Badge --}}
        <div class="px-5 mb-4">
            <div class="w-full flex items-center justify-between bg-[#EEF0FB] border border-[#DDE1F5] text-slate-700 rounded-lg px-2.5 py-1.5 text-[12px] font-semibold">
                <span class="flex items-center gap-2 truncate">
                    <i data-lucide="shield" class="h-3.5 w-3.5 shrink-0" style="color: var(--c-primary, #0B6B5A);"></i>
                    <span class="truncate">{{ $roleLabel ?? 'Pengguna' }}</span>
                </span>
                <span class="text-[10px] uppercase font-bold text-slate-400 bg-white/70 px-1.5 py-0.5 rounded shrink-0">
                    {{ $userRole ?? 'guest' }}
                </span>
            </div>
        </div>

        {{-- Menu Navigasi --}}
        <nav class="flex-1 overflow-y-auto px-4 space-y-1">
            @foreach ($sidebarMenus as $menu)
                @php
                    $isActive = request()->is(ltrim($menu['active_pattern'], '/'));
                @endphp
                <a href="{{ $menu['url'] }}" 
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[12.5px] transition duration-200 
                          {{ $isActive 
                              ? 'bg-[#0B6B5A] text-white font-semibold shadow-sm' 
                              : 'text-slate-600 font-medium hover:bg-[#0B6B5A]/10 hover:text-[#0B6B5A]' }}">
                    <i data-lucide="{{ $menu['icon'] }}" class="h-4 w-4 shrink-0"></i>
                    <span class="truncate">{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Profil Bawah Sidebar (Dinamis dari auth user) --}}
    <div class="p-4 border-t border-slate-100">
        <div class="bg-white rounded-lg border border-slate-200 p-2 h-14 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5 overflow-hidden">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-9 w-9 rounded-full object-cover shrink-0">
                @else
                    <div class="h-9 w-9 bg-[#0B6B5A] text-white rounded-full flex items-center justify-center font-bold text-[11px] shrink-0">
                        {{ $initials }}
                    </div>
                @endif
                <div class="truncate">
                    <p class="text-[12.5px] font-bold text-slate-800 truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-slate-500 truncate capitalize">{{ $positionName }}</p>
                </div>
            </div>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="shrink-0 flex ml-1">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-600 transition p-1.5 rounded-lg hover:bg-red-50" title="Keluar">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                    </button>
                </form>
            @endauth
        </div>
    </div>
</aside>
