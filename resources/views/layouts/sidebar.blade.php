{{-- Sidebar Container (Desktop Only: hidden md:flex) --}}
<aside :class="sidebarCollapsed ? 'w-[76px]' : 'w-[260px]'"
       class="hidden md:flex h-screen flex-shrink-0 flex-col justify-between border-r border-[#E2E0F7] bg-white transition-all duration-200 ease-in-out static shadow-none select-none">
    
    <div class="flex flex-col flex-1 min-h-0">
        {{-- Logo & Toggle Header --}}
        <div :class="sidebarCollapsed ? 'flex-col gap-2.5 px-0 py-3.5 justify-center' : 'flex-row px-5 py-4 justify-between'"
             class="flex items-center border-b border-transparent transition-all duration-200 w-full">
            
            {{-- Bagian Logo: Saat collapsed hanya ikon di tengah --}}
            <div :class="sidebarCollapsed ? 'w-full flex items-center justify-center cursor-pointer' : 'flex items-center justify-start gap-2.5 overflow-hidden'"
                @click="if (sidebarCollapsed) toggleSidebar()"
                :title="sidebarCollapsed ? 'Buka Sidebar' : ''"
                class="transition-all duration-200">

                <x-application-logo class="h-8 w-8 shrink-0 transition-all duration-200" />

                <div x-show="!sidebarCollapsed"
                    :class="{ 'hidden': sidebarCollapsed }"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="flex flex-col">

                    <span class="font-display font-extrabold text-[19px] tracking-tight leading-none whitespace-nowrap">
                        <span class="text-[#2D2A3E]">Jadwal</span><span style="color: #6C5CE7;">in</span>
                    </span>
                    <span class="text-[8px] font-semibold tracking-wider text-[#6C5CE7]/80 uppercase mt-0.5 whitespace-nowrap">
                        Smart Shift Scheduling
                    </span>
                </div>
            </div>

            {{-- Tombol Panah Toggle Sidebar (Satu-satunya tombol untuk menutup / membuka sidebar) --}}
            <button type="button" 
                    @click="toggleSidebar()" 
                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-[#F5F3FF] border border-[#E2E0F7] text-[#6C5CE7] hover:bg-[#6C5CE7] hover:text-white hover:border-[#6C5CE7] shadow-2xs transition-all duration-200 focus:outline-none shrink-0"
                    :title="sidebarCollapsed ? 'Buka Sidebar' : 'Tutup Sidebar'">
                <i data-lucide="chevron-left" 
                   class="h-4 w-4 stroke-[2.5] transition-transform duration-200" 
                   :class="{ 'rotate-180': sidebarCollapsed }"></i>
            </button>
        </div>

        {{-- Role / Posisi Badge --}}
        <div :class="sidebarCollapsed ? 'px-2' : 'px-4'" class="mb-3 transition-all duration-200">
            <div x-show="!sidebarCollapsed" 
                 :class="{ 'hidden': sidebarCollapsed }"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="w-full flex items-center justify-between bg-[#F5F3FF] border border-[#E2E0F7] text-[#2D2A3E] rounded-lg px-2.5 py-1.5 text-[12px] font-semibold">
                <span class="flex items-center gap-2 truncate">
                    <i data-lucide="shield" class="h-3.5 w-3.5 shrink-0" style="color: #6C5CE7;"></i>
                    <span class="truncate">{{ $roleLabel ?? 'Pengguna' }}</span>
                </span>
                <span class="text-[10px] uppercase font-bold text-[#6C5CE7] bg-white px-1.5 py-0.5 rounded shrink-0 shadow-2xs border border-[#E2E0F7]">
                    {{ $userRole ?? 'guest' }}
                </span>
            </div>
            <div x-show="sidebarCollapsed" :class="{ 'hidden': !sidebarCollapsed }" class="flex justify-center items-center w-full py-0.5" title="{{ $roleLabel ?? 'Pengguna' }} ({{ $userRole ?? 'guest' }})">
                <span class="w-9 h-9 flex items-center justify-center bg-[#F5F3FF] rounded-xl border border-[#E2E0F7] text-[#6C5CE7] shadow-2xs">
                    <i data-lucide="shield" class="h-4 w-4"></i>
                </span>
            </div>
        </div>

        {{-- Menu Navigasi --}}
        <nav :class="sidebarCollapsed ? 'px-2' : 'px-3'" class="flex-1 overflow-y-auto space-y-1 transition-all duration-200">
            @foreach ($sidebarMenus as $menu)
                @php
                    $isActive = request()->is(ltrim($menu['active_pattern'], '/'));
                @endphp
                <a href="{{ $menu['url'] }}" 
                   @click="sidebarOpen = false"
                   :title="sidebarCollapsed ? '{{ $menu['label'] }}' : ''"
                   :class="sidebarCollapsed ? 'flex items-center justify-center w-full h-10 px-0' : 'flex items-center justify-start px-3 py-2.5 gap-2.5'"
                   class="rounded-xl text-[12.5px] transition-all duration-200 
                          {{ $isActive 
                              ? 'bg-[#6C5CE7] text-white font-semibold shadow-sm' 
                              : 'text-[#2D2A3E] font-medium hover:bg-[#6C5CE7]/10 hover:text-[#6C5CE7]' }}">
                    <i data-lucide="{{ $menu['icon'] }}" class="h-4 w-4 shrink-0 {{ $isActive ? 'text-white' : 'text-[#7C7896]' }}"></i>
                    <span x-show="!sidebarCollapsed" :class="{ 'hidden': sidebarCollapsed }" class="truncate whitespace-nowrap">{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Profil Bawah Sidebar (Dinamis dari auth user) --}}
    <div :class="sidebarCollapsed ? 'p-2' : 'p-3'" class="border-t border-[#E2E0F7] transition-all duration-200">
        <div :class="sidebarCollapsed ? 'flex flex-col items-center justify-center gap-2 p-2 w-full' : 'flex flex-row items-center justify-between p-2'" 
             class="bg-[#F5F3FF]/50 rounded-xl border border-[#E2E0F7] shadow-2xs transition-all duration-200">
            
            {{-- Avatar & Info Karyawan --}}
            <div :class="sidebarCollapsed ? 'w-full flex items-center justify-center' : 'flex items-center gap-2.5 overflow-hidden'">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-8 w-8 rounded-full object-cover shrink-0" :title="'{{ $userName }}'">
                @else
                    <div class="h-8 w-8 bg-[#6C5CE7] text-white rounded-full flex items-center justify-center font-bold text-[11px] shrink-0 shadow-2xs" :title="'{{ $userName }}'">
                        {{ $initials }}
                    </div>
                @endif
                <div x-show="!sidebarCollapsed" :class="{ 'hidden': sidebarCollapsed }" class="truncate">
                    <p class="text-[12px] font-bold text-[#2D2A3E] truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-[#7C7896] truncate capitalize">{{ $positionName }}</p>
                </div>
            </div>

            {{-- Logout Button --}}
            @auth
                <form method="POST" action="{{ route('logout') }}" :class="sidebarCollapsed ? 'w-full flex justify-center items-center' : 'ml-1'" class="shrink-0 flex" x-data="{ loggingOut: false }" @submit="if(loggingOut) { $event.preventDefault(); return false; } loggingOut = true;">
                    @csrf
                    <button type="submit" 
                            :disabled="loggingOut"
                            :class="loggingOut ? 'opacity-50 cursor-not-allowed' : ''"
                            class="text-slate-400 hover:text-red-600 transition p-1.5 rounded-lg hover:bg-red-50 flex items-center justify-center" 
                            title="Keluar">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                    </button>
                </form>
            @endauth
        </div>
    </div>
</aside>
