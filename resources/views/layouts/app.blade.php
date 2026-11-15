<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Jadwalin') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    
    {{-- Google Fonts: Inter & Outfit --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Dark Mode Initializer Script (Defaults to Light, enables Dark only if selected) --}}
    <script>
        if (localStorage.getItem('jadwalin_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Tailwind CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Lucide Icons (Bundled Locally for Offline Reliability) --}}
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    
    <style>
        :root {
            --c-primary: #4F46E5;
            --c-primary-dark: #4338CA;
            --c-secondary: #6366F1;
            --c-ink: #0F172A;
            --c-ink-strong: #020617;
            --c-muted: #64748B;
            --c-line: #E2E8F0;
            --bg-body: #EAEEF4;
        }

        [x-cloak] { display: none !important; }

        body { 
            background-color: var(--bg-body); 
            color: var(--c-ink); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
        }
    </style>
</head>

<body x-data="{ 
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('jadwalin_sidebar_collapsed') === 'true',
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('jadwalin_sidebar_collapsed', this.sidebarCollapsed);
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 310);
    }
}" @keydown.escape.window="sidebarOpen = false" class="flex h-screen overflow-hidden text-sm antialiased selection:bg-indigo-600/20 selection:text-indigo-700">

    {{-- ===== SIDEBAR (Desktop & Mobile Drawer) ===== --}}
    @include('layouts.sidebar')

    {{-- ===== MAIN WRAPPER ===== --}}
    <main class="flex-1 flex flex-col min-w-0 bg-[#EAEEF4] overflow-hidden relative">
        
        {{-- ===== GLOBAL FLOATING TOAST NOTIFICATIONS ===== --}}
        <div class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0" aria-live="polite">
            {{-- Toast Sukses --}}
            @if (session('success'))
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95 translate-x-4"
                    class="pointer-events-auto w-full rounded-xl shadow-lg border border-emerald-200/80 bg-white/95 backdrop-blur-md p-3.5 text-slate-800 flex items-start gap-3 transition-all"
                >
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/60">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900">Berhasil</p>
                        <p class="text-xs mt-0.5 leading-relaxed text-slate-600 font-medium">{{ session('success') }}</p>
                    </div>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition" title="Tutup">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            {{-- Toast Status --}}
            @if (session('status'))
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95 translate-x-4"
                    class="pointer-events-auto w-full rounded-xl shadow-lg border border-slate-200/80 bg-white/95 backdrop-blur-md p-3.5 text-slate-800 flex items-start gap-3 transition-all"
                >
                    <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200/60">
                        <i data-lucide="info" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900">Status</p>
                        <p class="text-xs mt-0.5 leading-relaxed text-slate-600 font-medium">{{ session('status') }}</p>
                    </div>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition" title="Tutup">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            {{-- Toast Error / Peringatan --}}
            @if (session('error'))
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => show = false, 5000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95 translate-x-4"
                    class="pointer-events-auto w-full rounded-xl shadow-lg border border-rose-200/80 bg-white/95 backdrop-blur-md p-3.5 text-slate-800 flex items-start gap-3 transition-all"
                >
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200/60">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900">Peringatan</p>
                        <p class="text-xs mt-0.5 leading-relaxed text-slate-600 font-medium">{{ session('error') }}</p>
                    </div>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition" title="Tutup">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            {{-- Toast Info --}}
            @if (session('info'))
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95 translate-x-4"
                    class="pointer-events-auto w-full rounded-xl shadow-lg border border-slate-200/80 bg-white/95 backdrop-blur-md p-3.5 text-slate-800 flex items-start gap-3 transition-all"
                >
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-200/60">
                        <i data-lucide="info" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900">Informasi</p>
                        <p class="text-xs mt-0.5 leading-relaxed text-slate-600 font-medium">{{ session('info') }}</p>
                    </div>
                    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition" title="Tutup">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif
        </div>
        
        {{-- ===== TOP NAVBAR ===== --}}
        @include('layouts.topbar')

        {{-- ===== DYNAMIC CONTENT AREA ===== --}}
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-7 pb-28 md:pb-8">
            @if (isset($header))
                <div class="mb-5 md:mb-6">
                    {{ $header }}
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </div>

        {{-- ===== MOBILE BOTTOM NAVIGATION BAR (md:hidden) ===== --}}
        @include('layouts.mobile-bottom-nav')
    </main>

    {{-- Initialize Icons & Scripts --}}
    <script>
        // Inisialisasi Lucide Icons
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        // Re-inisialisasi ikon saat dropdown Alpine terbuka
        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                window.Alpine.effect(() => {
                    setTimeout(() => {
                        if (window.lucide) window.lucide.createIcons();
                    }, 50);
                });
            }
        });

        // Live Clock (Dynamic Timezone)
        (function() {
            const clockEl = document.getElementById('navbar-clock');
            if (!clockEl) return;

            const companyTimezone = '{{ $timezone ?? "Asia/Jakarta" }}';

            function updateTime() {
                const now = new Date();
                const tzTime = new Date(now.toLocaleString('en-US', { timeZone: companyTimezone }));
                const hh = String(tzTime.getHours()).padStart(2, '0');
                const mm = String(tzTime.getMinutes()).padStart(2, '0');
                clockEl.textContent = hh + ':' + mm;
            }

            setInterval(updateTime, 1000);
        })();
    </script>
    
    @stack('scripts')
</body>
</html>