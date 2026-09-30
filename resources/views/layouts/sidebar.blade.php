{{-- Mobile Backdrop Overlay --}}
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false" 
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"
     style="display: none;">
</div>

{{-- Sidebar Container (Desktop Sticky & Mobile Off-Canvas Drawer) --}}
<aside :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-[270px] md:w-[260px] h-screen flex-shrink-0 flex flex-col justify-between border-r border-[#E2E0F7] bg-white transition-transform duration-300 ease-in-out md:static md:translate-x-0 md:shadow-none">
    
    <div class="flex flex-col flex-1 min-h-0">
        {{-- Logo Header --}}
        <div class="px-5 md:px-6 py-4 md:py-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="h-8 w-auto shrink-0" />
                <span class="text-[19px] font-bold text-[#2D2A3E] tracking-tight">Jadwal<span style="color: #6C5CE7;">in</span></span>
            </div>
            
            {{-- Tombol Tutup (Mobile Only) --}}
            <button type="button" 
                    @click="sidebarOpen = false" 
                    class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-[#F5F3FF] transition focus:outline-none"
                    title="Tutup Menu">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        {{-- Role / Posisi Badge --}}
        <div class="px-5 mb-4">
            <div class="w-full flex items-center justify-between bg-[#F5F3FF] border border-[#E2E0F7] text-[#2D2A3E] rounded-lg px-2.5 py-1.5 text-[12px] font-semibold">
                <span class="flex items-center gap-2 truncate">
                    <i data-lucide="shield" class="h-3.5 w-3.5 shrink-0" style="color: #6C5CE7;"></i>
                    <span class="truncate">{{ $roleLabel ?? 'Pengguna' }}</span>
                </span>
                <span class="text-[10px] uppercase font-bold text-[#6C5CE7] bg-white px-1.5 py-0.5 rounded shrink-0 shadow-2xs border border-[#E2E0F7]">
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
                   @click="sidebarOpen = false"
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[12.5px] transition duration-200 
                          {{ $isActive 
                              ? 'bg-[#6C5CE7] text-white font-semibold shadow-sm' 
                              : 'text-[#2D2A3E] font-medium hover:bg-[#6C5CE7]/10 hover:text-[#6C5CE7]' }}">
                    <i data-lucide="{{ $menu['icon'] }}" class="h-4 w-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#7C7896]' }}"></i>
                    <span class="truncate">{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Profil Bawah Sidebar (Dinamis dari auth user) --}}
    <div class="p-4 border-t border-[#E2E0F7]">
        <div class="bg-[#F5F3FF]/50 rounded-lg border border-[#E2E0F7] p-2 h-14 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5 overflow-hidden">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-9 w-9 rounded-full object-cover shrink-0">
                @else
                    <div class="h-9 w-9 bg-[#6C5CE7] text-white rounded-full flex items-center justify-center font-bold text-[11px] shrink-0">
                        {{ $initials }}
                    </div>
                @endif
                <div class="truncate">
                    <p class="text-[12.5px] font-bold text-[#2D2A3E] truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-[#7C7896] truncate capitalize">{{ $positionName }}</p>
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
