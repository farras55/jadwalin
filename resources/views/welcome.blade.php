<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwalin - Smart Shift Scheduling &amp; Labor Compliance Platform</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-brand-border" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo (Kiri) -->
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0 focus:outline-none">
                    <x-application-logo class="h-9 w-auto shrink-0" />
                    <span class="font-display font-bold text-xl tracking-tight">
                        <span class="text-brand-text">Jadwal</span><span class="text-brand-primary">in</span>
                    </span>
                </a>

                <!-- Navigasi Desktop (Tengah) -->
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="#fitur" class="text-sm font-medium text-gray-600 hover:text-brand-primary transition-colors">
                        Fitur Utama
                    </a>
                    <a href="#solusi" class="text-sm font-medium text-gray-600 hover:text-brand-primary transition-colors">
                        Solusi
                    </a>
                    <a href="#regulasi" class="text-sm font-medium text-gray-600 hover:text-brand-primary transition-colors">
                        Regulasi PP 35/2021
                    </a>
                    <a href="#tentang" class="text-sm font-medium text-gray-600 hover:text-brand-primary transition-colors">
                        Tentang Kami
                    </a>
                    <a href="#tim" class="text-sm font-medium text-gray-600 hover:text-brand-primary transition-colors">
                        Tim Pengembang
                    </a>
                </nav>

                <!-- Tombol Aksi Desktop (Kanan) -->
                <div class="hidden lg:flex items-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center bg-brand-primary hover:bg-brand-hover text-white rounded-xl px-5 min-h-[44px] font-semibold text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-brand-primary hover:bg-brand-hover text-white rounded-xl px-5 min-h-[44px] font-semibold text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                            Masuk Sistem
                        </a>
                    @endauth
                </div>

                <!-- Tombol Hamburger Mobile -->
                <div class="flex items-center lg:hidden">
                    <button 
                        type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        :aria-expanded="mobileMenuOpen.toString()" 
                        aria-label="Menu navigasi" 
                        class="inline-flex items-center justify-center w-[44px] h-[44px] rounded-xl text-brand-text hover:bg-brand-bg transition-colors focus:outline-none focus:ring-2 focus:ring-brand-primary"
                    >
                        <span x-show="!mobileMenuOpen" class="inline-flex items-center justify-center">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </span>
                        <span x-show="mobileMenuOpen" x-cloak class="inline-flex items-center justify-center">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel Dropdown Mobile -->
        <div 
            x-show="mobileMenuOpen" 
            x-cloak 
            @click.outside="mobileMenuOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-b border-brand-border bg-white shadow-lg"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-3 pb-6 space-y-3">
                <nav class="flex flex-col space-y-1">
                    <a href="#fitur" @click="mobileMenuOpen = false" class="px-3 py-2 text-base font-medium text-gray-600 hover:text-brand-primary hover:bg-brand-bg rounded-lg transition-colors">
                        Fitur Utama
                    </a>
                    <a href="#solusi" @click="mobileMenuOpen = false" class="px-3 py-2 text-base font-medium text-gray-600 hover:text-brand-primary hover:bg-brand-bg rounded-lg transition-colors">
                        Solusi
                    </a>
                    <a href="#regulasi" @click="mobileMenuOpen = false" class="px-3 py-2 text-base font-medium text-gray-600 hover:text-brand-primary hover:bg-brand-bg rounded-lg transition-colors">
                        Regulasi PP 35/2021
                    </a>
                    <a href="#tentang" @click="mobileMenuOpen = false" class="px-3 py-2 text-base font-medium text-gray-600 hover:text-brand-primary hover:bg-brand-bg rounded-lg transition-colors">
                        Tentang Kami
                    </a>
                    <a href="#tim" @click="mobileMenuOpen = false" class="px-3 py-2 text-base font-medium text-gray-600 hover:text-brand-primary hover:bg-brand-bg rounded-lg transition-colors">
                        Tim Pengembang
                    </a>
                </nav>

                <div class="pt-3 border-t border-brand-border flex flex-col">
                    @auth
                        <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="inline-flex items-center justify-center bg-brand-primary hover:bg-brand-hover text-white rounded-xl px-5 min-h-[44px] font-semibold text-sm transition-colors shadow-sm text-center">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="inline-flex items-center justify-center bg-brand-primary hover:bg-brand-hover text-white rounded-xl px-5 min-h-[44px] font-semibold text-sm transition-colors shadow-sm text-center">
                            Masuk Sistem
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="py-10 sm:py-14 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 xl:gap-12 items-center">
                    
                    <!-- Kolom Kiri: Copy & CTA -->
                    <div class="lg:col-span-6 xl:col-span-6 flex flex-col items-start">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/15 mb-6">
                            <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                            <span class="text-xs font-semibold text-brand-primary">
                                Platform Penjadwalan Shift B2B
                            </span>
                        </div>

                        <!-- Headline -->
                        <h1 class="font-display font-bold text-3xl sm:text-4xl md:text-5xl xl:text-[54px] text-brand-text tracking-tight leading-[1.15]">
                            Susun Jadwal Shift<br class="hidden sm:inline" />
                            Tanpa Bentrok,<br class="hidden sm:inline" />
                            Sesuai Aturan<br class="hidden sm:inline" />
                            Ketenagakerjaan.
                        </h1>

                        <!-- Subheadline -->
                        <p class="mt-5 text-base sm:text-lg text-[#7C7896] leading-relaxed max-w-xl font-sans">
                            Roster mingguan disusun otomatis dengan mempertimbangkan ketersediaan karyawan, batas jam kerja, dan PP No. 35 Tahun 2021.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 w-full sm:w-auto">
                            <!-- Primary CTA -->
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-hover text-white rounded-xl px-6 min-h-[48px] font-semibold text-sm sm:text-base transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                                <span>Masuk ke Workspace</span>
                                <span>&rarr;</span>
                            </a>

                            <!-- Secondary CTA -->
                            <a href="#fitur" class="inline-flex items-center justify-center bg-white hover:bg-brand-bg text-brand-text border border-brand-border rounded-xl px-6 min-h-[48px] font-semibold text-sm sm:text-base transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                                Lihat Fitur
                            </a>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Preview Roster Shift -->
                    <div class="lg:col-span-6 xl:col-span-6 w-full flex justify-center lg:justify-end">
                        <div class="w-full max-w-lg lg:max-w-none bg-white border border-brand-border rounded-2xl p-5 sm:p-7 shadow-sm">
                            
                            <!-- Card Header -->
                            <div class="flex items-center justify-between gap-3 pb-5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-primary shrink-0"></span>
                                    <h2 class="font-bold text-sm sm:text-base text-brand-text truncate">
                                        Roster Minggu Ini | Departemen F&amp;B
                                    </h2>
                                </div>
                                <span class="shrink-0 bg-brand-primary/10 text-brand-primary text-xs font-semibold px-2.5 py-1 rounded-full">
                                    Roster Terbit
                                </span>
                            </div>

                            <!-- Statistik -->
                            <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-5">
                                <!-- Statistik Kiri -->
                                <div class="bg-brand-bg/40 border border-brand-border rounded-xl p-3.5 sm:p-4">
                                    <span class="block text-[10px] sm:text-[11px] font-bold tracking-wider text-gray-500 uppercase">
                                        KONFLIK JADWAL
                                    </span>
                                    <span class="block text-xl sm:text-2xl font-bold text-brand-primary mt-1 leading-tight">
                                        0 Bentrok
                                    </span>
                                    <span class="block text-[11px] sm:text-xs text-gray-500 mt-1">
                                        Target sistem: tanpa jadwal ganda
                                    </span>
                                </div>

                                <!-- Statistik Kanan -->
                                <div class="bg-brand-bg/40 border border-brand-border rounded-xl p-3.5 sm:p-4">
                                    <span class="block text-[10px] sm:text-[11px] font-bold tracking-wider text-gray-500 uppercase">
                                        JAM KERJA MINGGU INI
                                    </span>
                                    <span class="block text-xl sm:text-2xl font-bold text-brand-text mt-1 leading-tight">
                                        32 / 40 jam
                                    </span>
                                    <span class="block text-[11px] sm:text-xs font-medium text-emerald-600 mt-1">
                                        Aman, sisa 8 jam
                                    </span>
                                </div>
                            </div>

                            <!-- Shift List -->
                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-2.5">
                                    <span class="text-[11px] sm:text-xs font-bold tracking-wider text-gray-500 uppercase">
                                        STAF SHIFT SIANG (14:00 - 22:00)
                                    </span>
                                    <span class="text-[11px] sm:text-xs text-gray-500 font-medium">
                                        Kuota terpenuhi 3/3
                                    </span>
                                </div>

                                <div class="space-y-2.5">
                                    <!-- Row 1: Budi Santoso -->
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-brand-border bg-white">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-full bg-brand-primary/10 text-brand-primary font-bold text-xs flex items-center justify-center shrink-0">
                                                BS
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-xs sm:text-sm text-brand-text truncate">
                                                    Budi Santoso
                                                </p>
                                                <p class="text-[11px] sm:text-xs text-gray-500 truncate">
                                                    Barista Senior
                                                </p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 bg-brand-primary/10 text-brand-primary text-xs font-semibold px-2.5 py-1 rounded-lg">
                                            Terjadwal
                                        </span>
                                    </div>

                                    <!-- Row 2: Siti Nurhaliza -->
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-brand-border bg-white">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-full bg-brand-primary/10 text-brand-primary font-bold text-xs flex items-center justify-center shrink-0">
                                                SN
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-xs sm:text-sm text-brand-text truncate">
                                                    Siti Nurhaliza
                                                </p>
                                                <p class="text-[11px] sm:text-xs text-gray-500 truncate">
                                                    Kasir
                                                </p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 bg-emerald-50 text-emerald-600 border border-emerald-200/60 text-xs font-semibold px-2.5 py-1 rounded-lg">
                                            Hadir 13:58
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar: Pemenuhan Jam Kerja -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-500">
                                        Pemenuhan Jam Kerja
                                    </span>
                                    <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        Seimbang
                                    </span>
                                </div>
                                <div class="w-full h-2.5 bg-brand-primary/20 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-primary rounded-full w-[80%]"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Section: Sektor Dinamis (Solusi) -->
        <section id="solusi" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Horizontal Bar: Cocok untuk bisnis shift -->
                <div class="max-w-3xl mx-auto bg-white border border-brand-border rounded-2xl py-3 px-5 sm:px-8 shadow-sm flex flex-wrap items-center justify-center gap-2 sm:gap-3 mb-14 sm:mb-16">
                    <span class="text-xs sm:text-sm font-medium text-gray-500">
                        Cocok untuk bisnis shift:
                    </span>
                    <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1 rounded-full">
                        Kafe &amp; F&amp;B
                    </span>
                    <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1 rounded-full">
                        Ritel
                    </span>
                    <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1 rounded-full">
                        Perhotelan
                    </span>
                    <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1 rounded-full">
                        Pariwisata
                    </span>
                </div>

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-12">
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/15 mb-4">
                        <span class="text-xs font-semibold text-brand-primary">
                            Skenario Penggunaan
                        </span>
                    </div>
                    <h2 class="font-display font-bold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                        Dirancang untuk Sektor yang Dinamis
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                        Disesuaikan dengan tantangan operasional nyata pada industri dengan mobilitas dan intensitas tinggi.
                    </p>
                </div>

                <!-- Dua Card Sektor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Card 1: F&B & Ritel -->
                    <div class="bg-white border border-brand-border rounded-2xl shadow-sm overflow-hidden flex flex-col">
                        <div class="relative w-full aspect-[16/10] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-fnb-ritel.png') }}" 
                                alt="Sektor F&amp;B dan Ritel" 
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/40 to-transparent pointer-events-none"></div>
                            <div class="absolute top-4 left-4">
                                <span class="bg-brand-primary text-white text-[11px] font-bold px-2.5 py-1 rounded-md shadow-sm">
                                    F&amp;B &amp; RITEL
                                </span>
                            </div>
                        </div>
                        <div class="p-6 sm:p-7 flex flex-col flex-1">
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text leading-snug">
                                Rotasi Kasir, Barista, dan Cook Tanpa Kekurangan Staf
                            </h3>
                            <p class="mt-3 text-sm text-[#7C7896] leading-relaxed flex-1">
                                Menyeimbangkan jam sibuk (rush hours) dengan ketersediaan tim kafe &amp; gerai ritel secara dinamis, serta fasilitasi pertukaran shift mandiri antar staf dengan persetujuan manajer.
                            </p>
                            <div class="flex flex-wrap gap-2 mt-6 pt-2">
                                <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1.5 rounded-full">
                                    Kuota Shift Otomatis
                                </span>
                                <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1.5 rounded-full">
                                    Tukar Shift Berjenjang
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Perhotelan & Pariwisata -->
                    <div class="bg-white border border-brand-border rounded-2xl shadow-sm overflow-hidden flex flex-col">
                        <div class="relative w-full aspect-[16/10] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-perhotelan-pariwisata.png') }}" 
                                alt="Sektor Perhotelan dan Pariwisata" 
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/40 to-transparent pointer-events-none"></div>
                            <div class="absolute top-4 left-4">
                                <span class="bg-brand-primary text-white text-[11px] font-bold px-2.5 py-1 rounded-md shadow-sm">
                                    PERHOTELAN &amp; PARIWISATA
                                </span>
                            </div>
                        </div>
                        <div class="p-6 sm:p-7 flex flex-col flex-1">
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text leading-snug">
                                Jaga Front Office dan Loket Tiket Tanpa Kelelahan Staf
                            </h3>
                            <p class="mt-3 text-sm text-[#7C7896] leading-relaxed flex-1">
                                Jeda istirahat minimal 11 jam antar shift diberi pengingat otomatis, dan lembur dibatasi sesuai PP 35/2021.
                            </p>
                            <div class="flex flex-wrap gap-2 mt-6 pt-2">
                                <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1.5 rounded-full">
                                    Peringatan Jeda Istirahat
                                </span>
                                <span class="bg-brand-primary/10 text-brand-primary text-xs font-semibold px-3 py-1.5 rounded-full">
                                    Batas Lembur PP 35/2021
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: 3 Peran, 1 Platform -->
        <section id="peran" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Section Header -->
                <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14">
                    <h2 class="font-display font-bold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                        3 Peran, 1 Platform
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                        Satu alur terpadu yang menghubungkan staf lini depan, pengawas harian, dan pembuat kebijakan.
                    </p>
                </div>

                <!-- Three Role Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">
                    
                    <!-- Card 1: Karyawan -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="smartphone" class="w-6 h-6"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-xl text-brand-text mb-5">
                                Karyawan
                            </h3>
                            <!-- List Items -->
                            <ul class="space-y-3.5">
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Lihat jadwal &amp; rotasi transparan dari perangkat
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Ajukan tukar shift dan ketersediaan waktu mandiri
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Pantau riwayat jam kerja &amp; akumulasi lembur sesuai aturan
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <!-- Bottom Link -->
                        <div class="mt-8 pt-4 border-t border-brand-border/60 flex justify-end">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Karyawan
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Karyawan
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Card 2: Manajer -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="user-cog" class="w-6 h-6"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-xl text-brand-text mb-5">
                                Manajer
                            </h3>
                            <!-- List Items -->
                            <ul class="space-y-3.5">
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Susun &amp; terbitkan roster mingguan dalam hitungan menit
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Deteksi otomatis bentrok jadwal dan batas maksimal jam kerja
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Persetujuan cepat untuk permohonan tukar shift karyawan
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <!-- Bottom Link -->
                        <div class="mt-8 pt-4 border-t border-brand-border/60 flex justify-end">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Manajer
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Manajer
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Card 3: Super Admin -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-xl text-brand-text mb-5">
                                Super Admin
                            </h3>
                            <!-- List Items -->
                            <ul class="space-y-3.5">
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Kelola perusahaan, cabang, departemen, dan posisi jabatan
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Kelola pengguna dan peran akses
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i data-lucide="circle-check" class="w-4 h-4 text-brand-primary shrink-0 mt-1"></i>
                                    <span class="text-sm text-[#7C7896] leading-relaxed">
                                        Atur batas jam kerja, lembur, jeda, dan toleransi presensi
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <!-- Bottom Link -->
                        <div class="mt-8 pt-4 border-t border-brand-border/60 flex justify-end">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Super Admin
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-primary hover:text-brand-hover transition-colors">
                                    Dashboard Super Admin
                                </a>
                            @endauth
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Section: Fitur Andalan Jadwalin -->
        <section id="fitur" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14">
                    <h2 class="font-display font-bold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                        Fitur Andalan Jadwalin
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                        Solusi otomasi roster presisi untuk mengakhiri kekacauan lembar kerja manual.
                    </p>
                </div>

                <!-- 3 Feature Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">

                    <!-- Card 1: Zero Scheduling Conflict -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                                <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Zero Scheduling Conflict
                            </h3>
                            <!-- Description -->
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Deteksi otomatis bentrok jadwal, keterbatasan ketersediaan, dan batas jam kerja sebelum roster diterbitkan.
                            </p>
                        </div>
                        <!-- Bottom Label -->
                        <div class="mt-7 pt-4 border-t border-brand-border/60">
                            <span class="text-xs font-semibold text-brand-primary">
                                Otomatis &amp; Presisi
                            </span>
                        </div>
                    </div>

                    <!-- Card 2: Kepatuhan PP 35/2021 -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Kepatuhan PP 35/2021
                            </h3>
                            <!-- Description -->
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Bantu memastikan roster mengikuti batas waktu kerja, jeda istirahat, dan ketentuan lembur sesuai PP No. 35 Tahun 2021.
                            </p>
                        </div>
                        <!-- Bottom Label -->
                        <div class="mt-7 pt-4 border-t border-brand-border/60">
                            <span class="text-xs font-semibold text-brand-primary">
                                Kepatuhan Hukum
                            </span>
                        </div>
                    </div>

                    <!-- Card 3: Fair & Open Shifts -->
                    <div class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <!-- Icon Box -->
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                            </div>
                            <!-- Card Title -->
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Fair &amp; Open Shifts
                            </h3>
                            <!-- Description -->
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Kelola open shift dan pertukaran shift secara transparan dengan persetujuan manajer agar pembagian jadwal lebih adil.
                            </p>
                        </div>
                        <!-- Bottom Label -->
                        <div class="mt-7 pt-4 border-t border-brand-border/60">
                            <span class="text-xs font-semibold text-brand-primary">
                                Distribusi Adil
                            </span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Section: Regulasi PP No. 35 Tahun 2021 -->
        <section id="regulasi" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-14">
                    <span class="inline-flex items-center rounded-full border border-brand-border bg-white px-3 py-1 text-xs font-semibold text-brand-primary">
                        PP No. 35 Tahun 2021
                    </span>
                    <h2 class="mt-4 font-display font-bold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                        Regulasi PP No. 35 Tahun 2021
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                        Jadwalin membantu penyusunan roster dengan mempertimbangkan ketentuan waktu kerja, waktu istirahat, dan lembur sesuai peraturan ketenagakerjaan.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">
                    <article class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="clock-3" class="w-5 h-5"></i>
                            </div>
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Waktu Kerja
                            </h3>
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Pengaturan roster mempertimbangkan batas waktu kerja agar pembagian shift tetap terstruktur.
                            </p>
                        </div>
                    </article>

                    <article class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="coffee" class="w-5 h-5"></i>
                            </div>
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Waktu Istirahat
                            </h3>
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Jadwal shift membantu memperhatikan kebutuhan jeda istirahat antar waktu kerja.
                            </p>
                        </div>
                    </article>

                    <article class="bg-white border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                           <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                                <i data-lucide="timer" class="w-5 h-5"></i>
                            </div>
                            <h3 class="font-display font-bold text-lg sm:text-xl text-brand-text mb-3 leading-snug">
                                Lembur
                            </h3>
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Pemantauan jadwal membantu mengendalikan lembur dan memberikan perhatian pada ketentuan yang berlaku.
                            </p>
                        </div>
                    </article>
                </div>

            </div>
        </section>

        <!-- Section: Testimonial dan Target Sistem -->
        <section id="target-sistem" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative isolate overflow-hidden rounded-[2rem] border border-brand-primary/10 bg-brand-bg p-5 sm:p-8 lg:p-10">
                    <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-14 h-48 w-48 rounded-full bg-brand-primary/10 sm:h-64 sm:w-64"></div>
                    <div aria-hidden="true" class="pointer-events-none absolute -bottom-16 left-[30%] h-40 w-40 rounded-full bg-brand-secondary/20 sm:h-56 sm:w-56"></div>
                    <div aria-hidden="true" class="pointer-events-none absolute bottom-12 right-[43%] h-12 w-12 rounded-full bg-brand-primary/10 sm:h-16 sm:w-16"></div>

                    <div class="relative z-10 grid grid-cols-1 items-stretch gap-5 sm:gap-7 lg:grid-cols-2 lg:gap-8">
                        <article class="flex min-w-0 flex-col rounded-2xl border border-brand-border/70 bg-white p-6 shadow-sm sm:p-8 lg:p-9">
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-primary">
                                Testimoni
                            </span>
                            <blockquote class="mt-5 flex-1 border-l-2 border-brand-primary/20 pl-4 font-display text-xl font-semibold leading-relaxed text-brand-text sm:pl-5 sm:text-2xl">
                                “Dengan jadwal yang tersusun otomatis, saya tidak perlu lagi mengecek bentrok satu per satu, dan tukar shift jadi tercatat resmi lewat persetujuan manajer.”
                            </blockquote>
                            <div class="mt-8 border-t border-brand-border/70 pt-5">
                                <p class="font-semibold text-brand-text">Rani Aditya</p>
                                <p class="mt-1 text-sm text-[#7C7896]">Manajer Operasional, Kafe</p>
                            </div>
                        </article>

                        <div class="min-w-0 rounded-2xl border border-brand-border/70 bg-white p-6 shadow-sm sm:p-8 lg:p-9">
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-primary">
                                Jadwalin
                            </span>
                            <h2 class="mt-3 font-display text-2xl font-bold tracking-tight text-brand-text sm:text-3xl">
                                TARGET SISTEM
                            </h2>
                           <div class="mt-6 flex flex-col gap-4">
                                <div class="w-full rounded-xl border border-brand-primary/20 bg-brand-primary/10 p-5 sm:p-6">
                                    <p class="font-display text-5xl font-bold tracking-tight text-brand-primary sm:text-6xl">
                                        0
                                    </p>
                                    <p class="mt-2 text-sm font-medium leading-relaxed text-brand-text">
                                        Jadwal Bentrok
                                    </p>
                                </div>
                                <div class="w-full rounded-xl border border-brand-primary/20 bg-brand-primary/10 p-5 sm:p-6">
                                    <p class="font-display text-5xl font-bold tracking-tight text-brand-primary sm:text-6xl">
                                        100%
                                    </p>
                                    <p class="mt-2 text-sm font-medium leading-relaxed text-brand-text">
                                        Klaim Open Shift Aman dari Rebutan
                                    </p>
                                </div>
                            </div>
                            <p class="mt-5 text-xs leading-relaxed text-[#7C7896]">
                                Target pengujian sistem.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Tentang Kami -->
        <section id="tentang" class="bg-brand-bg py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center sm:mb-14">
                    <span class="inline-flex items-center rounded-full border border-brand-border bg-white px-3 py-1 text-xs font-semibold text-brand-primary">
                        Tentang Kami
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-bold tracking-tight text-brand-text sm:text-3xl lg:text-4xl">
                        Tentang Jadwalin
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-[#7C7896] sm:text-base">
                        Jadwalin adalah platform penjadwalan shift B2B yang membantu bisnis berbasis shift menyusun roster otomatis, mengelola tukar shift dua tahap, membagikan open shift secara adil, dan mencatat presensi sesuai PP No. 35 Tahun 2021.
                    </p>
                </div>

                <div class="grid grid-cols-1 items-stretch gap-6 sm:gap-8 md:grid-cols-3">
                    <article class="min-w-0 rounded-2xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="building-2" class="h-5 w-5"></i>
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text sm:text-xl">
                            Multi-Tenant
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#7C7896]">
                            Data setiap perusahaan terpisah dan aman.
                        </p>
                    </article>

                    <article class="min-w-0 rounded-2xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="smartphone" class="h-5 w-5"></i>
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text sm:text-xl">
                            Mobile-First
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#7C7896]">
                            Nyaman diakses di ponsel maupun desktop.
                        </p>
                    </article>

                    <article class="min-w-0 rounded-2xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <div class="w-12 h-12 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="shield-check" class="h-5 w-5"></i>
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text sm:text-xl">
                            Sesuai PP 35/2021
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#7C7896]">
                            Batas jam kerja dan lembur dipantau otomatis.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Section: Tim Pengembang -->
        <section id="tim" class="py-12 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center sm:mb-14">
                    <span class="inline-flex items-center rounded-full border border-brand-border bg-white px-3 py-1 text-xs font-semibold text-brand-primary">
                        Tim Pengembang
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-bold tracking-tight text-brand-text sm:text-3xl lg:text-4xl">
                        Dikembangkan oleh Tim Polinema
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-[#7C7896] sm:text-base">
                        Jadwalin dikembangkan sebagai solusi penjadwalan shift berbasis teknologi oleh mahasiswa Politeknik Negeri Malang.
                    </p>
                </div>

                <div class="mx-auto grid max-w-5xl grid-cols-1 items-stretch gap-6 sm:grid-cols-2 sm:gap-7 xl:grid-cols-4">
                    <article class="flex h-full min-h-[250px] min-w-0 flex-col items-center rounded-2xl border border-brand-border bg-white px-5 py-8 text-center shadow-sm">
                        <div class="mb-5 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 font-display text-sm font-bold text-brand-primary">
                            FA
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text">
                            Muhammad Farras A. A.
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#7C7896]">
                            Project Manager &amp; Backend Developer
                        </p>
                    </article>

                    <article class="flex h-full min-h-[250px] min-w-0 flex-col items-center rounded-2xl border border-brand-border bg-white px-5 py-8 text-center shadow-sm">
                        <div class="mb-5 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 font-display text-sm font-bold text-brand-primary">
                            MY
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text">
                            Muhammad Yusuf
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#7C7896]">
                            Database Designer &amp; System Analyst
                        </p>
                    </article>

                    <article class="flex h-full min-h-[250px] min-w-0 flex-col items-center rounded-2xl border border-brand-border bg-white px-5 py-8 text-center shadow-sm">
                        <div class="mb-5 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 font-display text-sm font-bold text-brand-primary">
                            NA
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text">
                            Neyza Ratu Anastasya
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#7C7896]">
                            UI/UX Designer &amp; Technical Writer
                        </p>
                    </article>

                    <article class="flex h-full min-h-[250px] min-w-0 flex-col items-center rounded-2xl border border-brand-border bg-white px-5 py-8 text-center shadow-sm">
                        <div class="mb-5 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 font-display text-sm font-bold text-brand-primary">
                            UM
                        </div>
                        <h3 class="font-display text-lg font-bold leading-snug text-brand-text">
                            Umi Maharani
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#7C7896]">
                            Full-Stack Developer &amp; System Analyst
                        </p>
                    </article>
                </div>

                <div class="mt-8 rounded-2xl border border-brand-primary/20 bg-brand-primary/10 px-6 py-5 text-center sm:mt-10">
                    <p class="font-display font-bold text-brand-primary">
                        Politeknik Negeri Malang
                    </p>
                    <p class="mt-1 text-sm text-brand-primary/70">
                        Program Studi Teknologi Informasi
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 py-6 lg:flex-row lg:items-center lg:justify-between">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-2.5 focus:outline-none">
                    <x-application-logo class="h-9 w-auto shrink-0" />
                    <span class="font-display text-xl font-bold tracking-tight">
                        <span class="text-brand-text">Jadwal</span><span class="text-brand-primary">in</span>
                    </span>
                </a>

                <nav aria-label="Navigasi footer" class="flex flex-wrap items-center gap-6 text-sm text-[#7C7896]">
                    <a href="#fitur" class="transition-colors hover:text-brand-text">Fitur Utama </a>
                    <a href="#solusi" class="transition-colors hover:text-brand-text">Solusi </a>
                    <a href="#tentang" class="transition-colors hover:text-brand-text">Tentang Kami </a>
                    <a href="#" class="transition-colors hover:text-brand-text">Pusat Bantuan </a>
                    <a href="#" class="transition-colors hover:text-brand-text">Kebijakan Privasi </a>
                    <a href="#" class="transition-colors hover:text-brand-text">Syarat Ketentuan</a>
                </nav>
            </div>

            <div class="flex flex-col gap-3 border-t border-brand-border py-4 text-xs sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-brand-primary/10 px-3 py-1 font-medium text-brand-primary">Sesuai PP No. 35/2021</span>
                    <span class="rounded-full bg-brand-primary/10 px-3 py-1 font-medium text-brand-primary">Mobile-First</span>
                    <span class="rounded-full bg-brand-primary/10 px-3 py-1 font-medium text-brand-primary">Multi-Tenant</span>
                </div>
                <p class="text-[#7C7896]">
                    © 2026 Jadwalin. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </footer>

    <!-- Lucide Icons -->
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
