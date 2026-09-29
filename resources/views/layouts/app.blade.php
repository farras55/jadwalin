<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'JadwalIn') }}</title>
    
    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Lucide Icons (Pinned Version) --}}
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    
    <style>
        :root {
            --c-primary: #0B6B5A;
            --c-primary-dark: #085244;
            --c-ink: #334155;
            --c-ink-strong: #0F172A;
            --c-muted: #64748B;
            --c-line: #E2E8F0;
            --bg-body: #F8F9FC;
        }

        body { 
            background-color: var(--bg-body); 
            color: var(--c-ink); 
            font-family: 'Inter', sans-serif; 
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden text-sm">

    {{-- ===== SIDEBAR (Hidden on Mobile) ===== --}}
    @include('layouts.sidebar')

    {{-- ===== MAIN WRAPPER ===== --}}
    <main class="flex-1 flex flex-col min-w-0 bg-[#F9FAFB] pb-[60px] md:pb-0 overflow-hidden">
        
        {{-- ===== TOP NAVBAR ===== --}}
        @include('layouts.topbar')

        {{-- ===== DYNAMIC CONTENT AREA ===== --}}
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            @if (isset($header))
                <div class="mb-6">
                    {{ $header }}
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    {{-- ===== BOTTOM NAVBAR (Mobile Only) ===== --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-[60px] bg-white border-t border-slate-200 flex justify-around items-center z-50 px-2 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] pb-safe">
        @foreach ($bottomMenus as $menu)
            @php
                $isActive = request()->is(ltrim($menu['active_pattern'], '/'));
            @endphp
            <a href="{{ $menu['url'] }}" class="flex flex-col items-center justify-center w-full h-full gap-1 transition-colors {{ $isActive ? 'text-[#0B6B5A]' : 'text-slate-400 hover:text-slate-600' }}">
                <i data-lucide="{{ $menu['icon'] }}" class="h-5 w-5 {{ $isActive ? 'fill-[#0B6B5A]/10' : '' }}"></i>
                <span class="text-[10px] font-medium">{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

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

        // Live Clock (WIB)
        (function() {
            const clockEl = document.getElementById('navbar-clock');
            if (!clockEl) return;

            function updateTime() {
                const now = new Date();
                const wibTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
                const hh = String(wibTime.getHours()).padStart(2, '0');
                const mm = String(wibTime.getMinutes()).padStart(2, '0');
                clockEl.textContent = hh + ':' + mm;
            }

            setInterval(updateTime, 1000);
        })();
    </script>
    
    @stack('scripts')
</body>
</html>