<header class="h-16 bg-white border-b border-slate-200 flex items-center px-6 sticky top-0 z-20">
    
    <!-- Mobile Hamburger (Hidden on Desktop) -->
    <div class="flex items-center md:hidden mr-4">
        <button class="text-slate-500 hover:text-slate-700 focus:outline-none">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Left side Context (Desktop) -->
    <div class="hidden md:flex items-center gap-5">
        <!-- Outlet Dropdown -->
        <button class="flex items-center h-[34px] px-3 gap-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0B6B5A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="text-[13px] font-bold text-slate-700">Senopati & Kemang (2 Cabang)</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="m6 9 6 6 6-6"></path>
            </svg>
        </button>

        <!-- Current Date -->
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                <line x1="16" x2="16" y1="2" y2="6"></line>
                <line x1="8" x2="8" y1="2" y2="6"></line>
                <line x1="3" x2="21" y1="10" y2="10"></line>
            </svg>
            <span id="topbar-clock" class="text-[13px] font-medium text-slate-600">
                Rabu, 23 September 2026 | 09:41 WIB
            </span>
        </div>
    </div>

    <!-- Right side Actions -->
    <div class="ml-auto flex items-center gap-3">
        <!-- Notification -->
        <button class="relative p-2 rounded-full text-slate-500 hover:bg-slate-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
        </button>

        <!-- Help -->
        <button class="p-2 rounded-full text-slate-500 hover:bg-slate-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <path d="M12 17h.01"></path>
            </svg>
        </button>

        <!-- Small Profile Avatar (Mobile or Extra) -->
        <button class="w-8 h-8 rounded-full bg-[#085245] ml-2 flex items-center justify-center text-white text-[12px] font-bold">
            {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'BS' }}
        </button>
    </div>
</header>
