<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Jadwalin') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    
    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Lucide Icons (Bundled Locally for Offline Reliability) --}}
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    
    <style>
        :root {
            --c-primary: #6C5CE7;
            --c-primary-dark: #4F46E5;
            --c-secondary: #A29BFE;
            --c-ink: #2D2A3E;
            --c-ink-strong: #1B1828;
            --c-muted: #7C7896;
            --c-line: #E2E0F7;
            --bg-body: #F5F3FF;
        }

        body { 
            background-color: var(--bg-body); 
            color: var(--c-ink); 
            font-family: 'Inter', sans-serif; 
        }
    </style>
</head>

<body x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="flex h-screen overflow-hidden text-sm">

    {{-- ===== SIDEBAR (Desktop & Mobile Drawer) ===== --}}
    @include('layouts.sidebar')

    {{-- ===== MAIN WRAPPER ===== --}}
    <main class="flex-1 flex flex-col min-w-0 bg-[#F5F3FF] overflow-hidden">
        
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