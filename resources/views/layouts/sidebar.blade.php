<aside class="hidden md:flex flex-col w-64 h-dvh bg-[#F4F6F9] border-r border-slate-200 shrink-0 sticky top-0">
    <!-- Logo -->
    <div class="flex items-center px-6 pt-6 gap-2">
        <div class="bg-[#0B6B5A] text-white rounded-lg w-8 h-8 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mt-0.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <div class="text-[20px] tracking-tight flex items-baseline text-[#0B6B5A]">
            <span class="font-bold">Jadwal</span>
            <span class="font-bold">in</span>
        </div>
    </div>

    <!-- Role Selector -->
    <div class="px-5 mt-5">
        <button class="w-full flex items-center justify-between h-[34px] px-3 rounded-lg bg-indigo-50/50 hover:bg-indigo-50 border border-indigo-100/50 transition-colors">
            <div class="flex items-center gap-2 overflow-hidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0B6B5A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                </svg>
                <span class="text-[12.5px] font-bold text-slate-700 truncate">
                    {{ auth()->check() ? (auth()->user()->role ?? 'Barista Senior') : 'Barista Senior' }}
                </span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="m7 15 5 5 5-5"></path>
                <path d="m7 9 5-5 5 5"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 min-h-0 overflow-y-auto px-4 mt-6 space-y-1">
        <!-- Dashboard -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl bg-[#0B6B5A] text-white transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
            </svg>
            <span class="text-[14px] font-semibold">Dashboard</span>
        </a>

        <!-- Jadwal Saya -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                <line x1="16" x2="16" y1="2" y2="6"></line>
                <line x1="8" x2="8" y1="2" y2="6"></line>
                <line x1="3" x2="21" y1="10" y2="10"></line>
            </svg>
            <span class="text-[14px] font-medium">Jadwal Saya</span>
        </a>

        <!-- Tukar Shift -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="m8 3-4 4 4 4"></path>
                <path d="M4 7h16"></path>
                <path d="m16 21 4-4-4-4"></path>
                <path d="M20 17H4"></path>
            </svg>
            <span class="text-[14px] font-medium">Tukar Shift</span>
        </a>

        <!-- Presensi / Absensi -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"></path>
                <path d="M14 13.12c0 2.38 0 6.38-1 8.88"></path>
                <path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"></path>
                <path d="M2 12a10 10 0 0 1 18-6"></path>
                <path d="M2 16h.01"></path>
                <path d="M21.8 16c.2-2 .131-5.354 0-6"></path>
                <path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"></path>
                <path d="M8.65 22c.21-.66.45-1.32.57-2"></path>
            </svg>
            <span class="text-[14px] font-medium">Presensi / Absensi</span>
        </a>

        <!-- Timesheet -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="text-[14px] font-medium">Timesheet</span>
        </a>

        <!-- Ketersediaan Waktu -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"></path>
                <path d="M16 2v4"></path>
                <path d="M8 2v4"></path>
                <path d="M3 10h5"></path>
                <path d="M17.5 17.5 16 16.3V14"></path>
                <circle cx="16" cy="16" r="6"></circle>
            </svg>
            <span class="text-[14px] font-medium">Ketersediaan Waktu</span>
        </a>

        <!-- Pengaturan -->
        <a href="#" class="flex items-center gap-3 px-3 h-11 rounded-xl text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <span class="text-[14px] font-medium">Pengaturan</span>
        </a>
    </nav>

    <!-- User Profile Card -->
    <div class="mt-auto m-4">
        <div class="flex items-center p-2.5 rounded-xl bg-white shadow-sm border border-slate-100">
            <div class="w-8 h-8 rounded-full bg-[#085245] flex items-center justify-center text-white text-[12px] font-bold shrink-0">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'BS' }}
            </div>
            
            <div class="overflow-hidden flex-1 flex flex-col justify-center ml-2.5">
                <p class="text-[13px] font-bold text-slate-800 truncate leading-tight">
                    {{ auth()->check() ? auth()->user()->name : 'Budi Santoso' }}
                </p>
                <p class="text-[11.5px] text-slate-500 truncate leading-tight mt-0.5">
                    {{ auth()->check() ? (auth()->user()->role ?? 'Barista Senior') : 'Barista Senior' }}
                </p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" class="ml-1 shrink-0 flex items-center">
                @csrf
                <button type="submit" class="p-1.5 hover:bg-slate-50 rounded-lg transition-colors text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" x2="9" y1="12" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
