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
<aside :class="{
           'translate-x-0 shadow-2xl': sidebarOpen,
           '-translate-x-full md:translate-x-0': !sidebarOpen,
           'md:w-[76px]': sidebarCollapsed,
           'md:w-[260px]': !sidebarCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 w-[270px] h-screen flex-shrink-0 flex flex-col justify-between border-r border-[#E2E0F7] bg-white transition-all duration-200 ease-in-out md:static md:translate-x-0 md:shadow-none select-none">
    
    <div class="flex flex-col flex-1 min-h-0">
        {{-- Logo Header --}}
        <div :class="sidebarCollapsed ? 'px-0 py-4 justify-center' : 'px-5 py-4 justify-between'"
             class="flex items-center border-b border-transparent transition-all duration-200 w-full">
            
            {{-- Bagian Logo: Saat collapsed hanya ikon di tengah --}}
            <div :class="sidebarCollapsed ? 'w-full flex items-center justify-center cursor-pointer' : 'flex items-center justify-start gap-2.5 overflow-hidden'"
                 @click="if (sidebarCollapsed) toggleSidebar()"
                 :title="sidebarCollapsed ? 'Buka Sidebar' : ''"
                 class="transition-all duration-200">
                <x-application-logo class="h-8 w-8 shrink-0 transition-all duration-200" />
                <span x-show="!sidebarCollapsed" 
                      :class="{ 'hidden': sidebarCollapsed }"
                      x-transition:enter="transition ease-out duration-150"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      class="text-[19px] font-bold text-[#2D2A3E] tracking-tight whitespace-nowrap">
                    Jadwal<span style="color: #6C5CE7;">in</span>
                </span>
            </div>
            
            {{-- Tombol Tutup (Mobile Only) --}}
            <button type="button" 
                    @click="sidebarOpen = false" 
                    class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-[#F5F3FF] transition focus:outline-none"
                    title="Tutup Menu">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>

            {{-- Tombol Toggle Collapse (Desktop Only, hanya tampil saat sidebar terbuka) --}}
            <button type="button" 
                    @click="toggleSidebar()" 
                    x-show="!sidebarCollapsed"
                    :class="{ 'hidden': sidebarCollapsed }"
                    class="hidden md:flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-[#F5F3FF] transition focus:outline-none"
                    title="Tutup Sidebar">
                <i data-lucide="chevron-left" class="h-4 w-4"></i>
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
