<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jadwalin') }}</title>

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

<body class="font-sans antialiased bg-[#F8F9FC] text-slate-800">
    <div class="min-h-screen flex flex-col justify-center items-center p-4">

        <!-- Logo & Subtitle Wrapper -->
        <div class="text-center w-full max-w-[448px] flex flex-col items-center">
            
            <!-- Logo Container -->
            <a href="/" class="flex items-center justify-center gap-2">
                <!-- Icon Box -->
                <div class="bg-[#0B6B5A] text-white rounded-lg w-9 h-9 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mt-0.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Logo Text -->
                <div class="text-[26px] tracking-tight flex items-baseline text-[#0B6B5A]">
                    <span class="font-bold">Jadwal</span>
                    <span class="font-bold">in</span>
                </div>
            </a>
            
            <!-- Subtitle -->
            <p class="mt-1 text-[13.5px] text-slate-500">
                Sistem Otomatisasi &amp; Penjadwalan Roster Kerja Terpadu
            </p>
        </div>

        <!-- Card Wrapper (Sistem Breeze akan memasukkan form login/register ke dalam variabel $slot ini) -->
        <div class="w-full max-w-[448px] mt-7 p-8 bg-white shadow-sm rounded-xl border border-slate-100">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-[13px] text-slate-400 font-medium">
            &copy; {{ date('Y') }} Jadwalin
        </div>
        
    </div>
</body>

</html>
