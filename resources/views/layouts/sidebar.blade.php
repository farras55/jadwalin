{{-- Sidebar Container (Desktop Only: hidden md:flex) --}}
<aside :class="sidebarCollapsed ? 'w-[72px]' : 'w-[250px]'"
       class="hidden md:flex h-screen flex-shrink-0 flex-col justify-between border-r border-slate-200/80 bg-white transition-all duration-200 ease-in-out static shadow-none select-none z-20">
    
    <div class="flex flex-col flex-1 min-h-0">
        {{-- Logo & Toggle Header --}}
        <div :class="sidebarCollapsed ? 'flex-col gap-2 px-0 py-3.5 justify-center' : 'flex-row px-4 py-3.5 justify-between'"
             class="flex items-center border-b border-slate-100 transition-all duration-200 w-full">
            
            {{-- Bagian Logo: Saat collapsed hanya ikon di tengah --}}
            <div :class="sidebarCollapsed ? 'w-full flex items-center justify-center cursor-pointer' : 'flex items-center justify-start gap-2.5 overflow-hidden'"
                @click="if (sidebarCollapsed) toggleSidebar()"
                :title="sidebarCollapsed ? 'Buka Sidebar' : ''"
                class="transition-all duration-200">

                <x-application-logo class="h-7 w-7 shrink-0 transition-all duration-200" />

                <div x-show="!sidebarCollapsed"
                    :class="{ 'hidden': sidebarCollapsed }"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="flex flex-col">

                    <span class="font-display font-extrabold text-[17px] tracking-tight leading-none whitespace-nowrap">
                        <span class="text-slate-900">Jadwal</span><span class="text-[#6C5CE7]">in</span>
                    </span>
                    <span class="text-[9px] font-medium tracking-normal text-slate-400 mt-0.5 whitespace-nowrap">
                        Shift Management
                    </span>
                </div>
            </div>

            {{-- Tombol Panah Toggle Sidebar (Satu-satunya tombol untuk menutup / membuka sidebar) --}}
            <button type="button" 
                    @click="toggleSidebar()" 
                    class="w-6 h-6 flex items-center justify-center rounded-md bg-slate-50 border border-slate-200 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all duration-150 focus:outline-none shrink-0"
                    :title="sidebarCollapsed ? 'Buka Sidebar' : 'Tutup Sidebar'">
                <i data-lucide="chevron-left" 
                   class="h-3.5 w-3.5 stroke-[2] transition-transform duration-200" 
                   :class="{ 'rotate-180': sidebarCollapsed }"></i>
            </button>
        </div>

        {{-- Workspace / Role Indicator --}}
        <div :class="sidebarCollapsed ? 'px-2' : 'px-3'" class="mt-3 mb-2 transition-all duration-200">
            <div x-show="!sidebarCollapsed" 
                 :class="{ 'hidden': sidebarCollapsed }"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="w-full flex items-center justify-between bg-slate-50 border border-slate-200/70 text-slate-700 rounded-lg px-2.5 py-1.5 text-xs">
                <div class="flex items-center gap-2 truncate min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="truncate font-semibold text-slate-800 text-[11.5px]">{{ $roleLabel ?? 'Pengguna' }}</span>
                </div>
                <span class="text-[9.5px] uppercase font-mono font-bold text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200/80 shrink-0">
                    {{ $userRole ?? 'guest' }}
                </span>
            </div>
            <div x-show="sidebarCollapsed" :class="{ 'hidden': !sidebarCollapsed }" class="flex justify-center items-center w-full py-0.5" title="{{ $roleLabel ?? 'Pengguna' }} ({{ $userRole ?? 'guest' }})">
                <span class="w-8 h-8 flex items-center justify-center bg-slate-50 rounded-lg border border-slate-200 text-slate-600 shadow-2xs">
                    <i data-lucide="shield" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                </span>
            </div>
        </div>

        {{-- Menu Navigasi --}}
        <nav :class="sidebarCollapsed ? 'px-2' : 'px-2.5'" class="flex-1 overflow-y-auto space-y-0.5 transition-all duration-200 py-1">
            @foreach ($sidebarMenus as $menu)
                @php
                    $isActive = request()->is(ltrim($menu['active_pattern'], '/'));
                @endphp
                <a href="{{ $menu['url'] }}" 
                   @click="sidebarOpen = false"
                   :title="sidebarCollapsed ? '{{ $menu['label'] }}' : ''"
                   :class="sidebarCollapsed ? 'flex items-center justify-center w-full h-9 px-0' : 'flex items-center justify-start px-2.5 py-2 gap-2.5'"
                   class="rounded-lg text-[13px] transition-all duration-150 group
                          {{ $isActive 
                              ? 'bg-[#F5F3FF] text-[#6C5CE7] font-semibold border border-[#E2E0F7]' 
                              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100/80' }}">
                    <i data-lucide="{{ $menu['icon'] }}" class="h-4 w-4 shrink-0 transition-colors {{ $isActive ? 'text-[#6C5CE7]' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                    <span x-show="!sidebarCollapsed" :class="{ 'hidden': sidebarCollapsed }" class="truncate whitespace-nowrap">{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Profil Bawah Sidebar (Dinamis dari auth user) --}}
    <div :class="sidebarCollapsed ? 'p-2' : 'p-2.5'" class="border-t border-slate-200/80 transition-all duration-200 bg-slate-50/50">
        <div :class="sidebarCollapsed ? 'flex flex-col items-center justify-center gap-1.5 p-1 w-full' : 'flex flex-row items-center justify-between p-1.5 rounded-lg hover:bg-white border border-transparent hover:border-slate-200/70 transition-all'" 
             class="transition-all duration-200">
            
            {{-- Avatar & Info Karyawan --}}
            <div :class="sidebarCollapsed ? 'w-full flex items-center justify-center' : 'flex items-center gap-2.5 overflow-hidden min-w-0'">
                @if (!empty($profilePhotoUrl))
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="h-7 w-7 rounded-full object-cover shrink-0 ring-1 ring-slate-200" :title="'{{ $userName }}'">
                @else
                    <div class="h-7 w-7 bg-slate-800 text-white rounded-full flex items-center justify-center font-bold text-[10px] shrink-0" :title="'{{ $userName }}'">
                        {{ $initials }}
                    </div>
                @endif
                <div x-show="!sidebarCollapsed" :class="{ 'hidden': sidebarCollapsed }" class="truncate min-w-0">
                    <p class="text-[12px] font-bold text-slate-800 truncate leading-tight">{{ $userName }}</p>
                    <p class="text-[10.5px] text-slate-500 truncate capitalize mt-0.5">{{ $positionName }}</p>
                </div>
            </div>

            {{-- Logout Button --}}
            @auth
                <form method="POST" action="{{ route('logout') }}" :class="sidebarCollapsed ? 'w-full flex justify-center items-center mt-1' : 'ml-1'" class="shrink-0 flex" x-data="{ loggingOut: false }" @submit="if(loggingOut) { $event.preventDefault(); return false; } loggingOut = true;">
                    @csrf
                    <button type="submit" 
                            :disabled="loggingOut"
                            :class="loggingOut ? 'opacity-50 cursor-not-allowed' : ''"
                            class="text-slate-400 hover:text-rose-600 transition p-1.5 rounded-md hover:bg-rose-50 flex items-center justify-center" 
                            title="Keluar">
                        <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                    </button>
                </form>
            @endauth
        </div>
    </div>
</aside>
