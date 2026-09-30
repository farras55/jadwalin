<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jadwalin') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts and Styles (Breeze Standard) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="font-sans antialiased bg-[#F5F3FF] text-[#2D2A3E]">
    <div class="min-h-screen flex flex-col justify-center items-center p-4">

        <!-- Logo & Subtitle Wrapper -->
        <div class="text-center w-full max-w-[448px] flex flex-col items-center">
            
            <!-- Logo Container -->
            <a href="/" class="flex items-center justify-center gap-2.5">
                <x-application-logo class="h-10 w-auto shrink-0" />
                <div class="text-[26px] tracking-tight flex items-baseline text-[#2D2A3E]">
                    <span class="font-bold">Jadwal</span>
                    <span class="font-bold" style="color: #6C5CE7;">in</span>
                </div>
            </a>
            
            <!-- Subtitle -->
            <p class="mt-1 text-[13.5px] text-[#7C7896]">
                Sistem Otomatisasi &amp; Penjadwalan Roster Kerja Terpadu
            </p>
        </div>

        <!-- Card Wrapper (Sistem Breeze akan memasukkan form login/register ke dalam variabel $slot ini) -->
        <div class="w-full max-w-[448px] mt-7 p-8 bg-white shadow-sm rounded-xl border border-[#E2E0F7]">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-[13px] text-[#7C7896] font-medium">
            &copy; {{ date('Y') }} Jadwalin
        </div>
        
    </div>
</body>

</html>
