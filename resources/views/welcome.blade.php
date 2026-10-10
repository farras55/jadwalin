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
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .font-script { font-family: 'Caveat', cursive; }
        
        /* 3D Glass & Neumorphic subtle elevation */
        .card-elevated {
            box-shadow: 0 10px 30px -5px rgba(108, 92, 231, 0.08), 0 4px 12px -2px rgba(45, 42, 62, 0.04);
        }
        .card-elevated-hover {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-elevated-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px -8px rgba(108, 92, 231, 0.16), 0 8px 16px -4px rgba(45, 42, 62, 0.06);
        }
        
        /* Subtle Origami Paper Plane Vector Path Glow */
        .flight-path {
            stroke-dasharray: 6 6;
            animation: dash 35s linear infinite;
        }
        @keyframes dash {
            to {
                stroke-dashoffset: -1000;
            }
        }

        /* Scroll Reveal Animations */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
    </style>
</head>
<body 
    x-data="{
        activeNav: 'hero',
        mobileMenuOpen: false,
        selectedSectorModal: null,
        selectedPlanModal: null,
        
        // Simulation Widget State
        simTab: 'roster',
        sektor: 'fnb',
        stafCount: '12',
        shiftModel: '3shift',
        periode: '7hari',
        isSimulating: false,
        showRosterResult: true,

        // Swap simulator animation state
        swapRotated: false,
        rotateSwap() {
            this.swapRotated = !this.swapRotated;
            if (this.shiftModel === '2shift') this.shiftModel = '3shift';
            else if (this.shiftModel === '3shift') this.shiftModel = 'split';
            else this.shiftModel = '2shift';
        },

        // Trigger simulation
        triggerSimulation() {
            this.isSimulating = true;
            this.showRosterResult = false;
            setTimeout(() => {
                this.isSimulating = false;
                this.showRosterResult = true;
            }, 500);
        },

        // PP 35 interactive calculator state
        ppJamKerja: 8,
        ppHariKerja: 5,
        ppJedaIstirahat: 12,
        get ppTotalJam() {
            return this.ppJamKerja * this.ppHariKerja;
        },
        get ppIsCompliant() {
            return this.ppTotalJam <= 40 && this.ppJedaIstirahat >= 11;
        },

        // Tukar shift simulator state
        swapStep: 1,
        swapApproved: false,

        // Lembur simulator state
        overtimeHoursDay: 2,
        overtimeDaysWeek: 2,
        get totalOvertimeWeek() {
            return this.overtimeHoursDay * this.overtimeDaysWeek;
        },
        get overtimeMultiplier() {
            return (1 * 1.5) + ((this.overtimeHoursDay - 1) * 2.0);
        },

        // Sector filter
        sectorFilter: 'all',

        // Features Showcase State
        featureFilter: 'all',
        selectedFeatureModal: null,
        roleSpotlight: 'manager'
    }"
    x-init="
        $watch('selectedFeatureModal', () => { $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); });
        $watch('roleSpotlight', () => { $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); });
        $watch('featureFilter', () => { $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); });
    "
    class="bg-brand-bg text-brand-text font-sans antialiased overflow-x-hidden selection:bg-brand-primary selection:text-white"
>

    <!-- ========================================================= -->
    <!-- 1. HEADER / NAVBAR (Borderless Logo & Dynamic Active Menu)-->
    <!-- ========================================================= -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-brand-border/80 transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo Brand (Borderless clean icon & brand text) -->
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0 group focus:outline-none">
                    <x-application-logo class="h-9 w-auto text-brand-primary group-hover:scale-105 transition-transform duration-200" />
                    <div class="flex flex-col">
                        <span class="font-display font-extrabold text-2xl tracking-tight leading-none">
                            <span class="text-brand-text">Jadwal</span><span class="text-brand-primary">in</span>
                        </span>
                        <span class="text-[10px] font-semibold tracking-wider text-brand-primary/80 uppercase mt-0.5">
                            Shift Compliance
                        </span>
                    </div>
                </a>

                <!-- Navigasi Desktop (Tengah - Hanya menu-menu penting) -->
                <nav class="hidden lg:flex items-center gap-1 bg-brand-bg/80 p-1.5 rounded-full border border-brand-border">
                    <a 
                        href="#hero" 
                        @click="activeNav = 'hero'"
                        :class="activeNav === 'hero' ? 'bg-brand-primary text-white shadow-sm font-semibold' : 'text-[#7C7896] hover:text-brand-primary hover:bg-white/80 font-medium'"
                        class="px-4 py-2 text-sm rounded-full transition-all duration-200"
                    >
                        Beranda
                    </a>
                    <a 
                        href="#simulator" 
                        @click="activeNav = 'simulator'"
                        :class="activeNav === 'simulator' ? 'bg-brand-primary text-white shadow-sm font-semibold' : 'text-[#7C7896] hover:text-brand-primary hover:bg-white/80 font-medium'"
                        class="px-4 py-2 text-sm rounded-full transition-all duration-200"
                    >
                        Simulasi Shift
                    </a>
                    <a 
                        href="#fitur" 
                        @click="activeNav = 'fitur'"
                        :class="activeNav === 'fitur' ? 'bg-brand-primary text-white shadow-sm font-semibold' : 'text-[#7C7896] hover:text-brand-primary hover:bg-white/80 font-medium'"
                        class="px-4 py-2 text-sm rounded-full transition-all duration-200"
                    >
                        Fitur
                    </a>
                    <a 
                        href="#sektor" 
                        @click="activeNav = 'sektor'"
                        :class="activeNav === 'sektor' ? 'bg-brand-primary text-white shadow-sm font-semibold' : 'text-[#7C7896] hover:text-brand-primary hover:bg-white/80 font-medium'"
                        class="px-4 py-2 text-sm rounded-full transition-all duration-200"
                    >
                        Sektor Bisnis
                    </a>
                    <a 
                        href="#cara-kerja" 
                        @click="activeNav = 'cara-kerja'"
                        :class="activeNav === 'cara-kerja' ? 'bg-brand-primary text-white shadow-sm font-semibold' : 'text-[#7C7896] hover:text-brand-primary hover:bg-white/80 font-medium'"
                        class="px-4 py-2 text-sm rounded-full transition-all duration-200"
                    >
                        Cara Kerja
                    </a>
                </nav>

                <!-- Tombol Sign In / Aksi Desktop (Kanan) -->
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-hover text-white rounded-full px-6 py-2.5 font-semibold text-sm transition-all shadow-md shadow-brand-primary/20 hover:shadow-lg hover:shadow-brand-primary/30">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 border-2 border-brand-primary text-brand-primary hover:bg-brand-primary hover:text-white rounded-full px-6 py-2.5 font-semibold text-sm transition-all duration-200 shadow-sm">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Masuk Sistem</span>
                        </a>
                    @endauth
                </div>

                <!-- Tombol Hamburger Mobile -->
                <div class="flex items-center lg:hidden">
                    <button 
                        type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="inline-flex items-center justify-center w-11 h-11 rounded-xl text-brand-text bg-white border border-brand-border hover:bg-brand-bg transition-colors focus:outline-none"
                        aria-label="Menu navigasi"
                    >
                        <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Mobile Dropdown (Hanya menu-menu penting) -->
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
            class="lg:hidden border-b border-brand-border bg-white/95 backdrop-blur-lg shadow-xl"
        >
            <div class="max-w-7xl mx-auto px-5 pt-4 pb-6 space-y-2">
                <nav class="flex flex-col space-y-1">
                    <a href="#hero" @click="activeNav = 'hero'; mobileMenuOpen = false" :class="activeNav === 'hero' ? 'bg-brand-primary text-white font-semibold' : 'text-gray-700 hover:text-brand-primary hover:bg-brand-bg font-medium'" class="px-4 py-2.5 text-base rounded-xl">Beranda</a>
                    <a href="#simulator" @click="activeNav = 'simulator'; mobileMenuOpen = false" :class="activeNav === 'simulator' ? 'bg-brand-primary text-white font-semibold' : 'text-gray-700 hover:text-brand-primary hover:bg-brand-bg font-medium'" class="px-4 py-2.5 text-base rounded-xl transition-colors">Simulasi Shift</a>
                    <a href="#fitur" @click="activeNav = 'fitur'; mobileMenuOpen = false" :class="activeNav === 'fitur' ? 'bg-brand-primary text-white font-semibold' : 'text-gray-700 hover:text-brand-primary hover:bg-brand-bg font-medium'" class="px-4 py-2.5 text-base rounded-xl transition-colors">Fitur</a>
                    <a href="#sektor" @click="activeNav = 'sektor'; mobileMenuOpen = false" :class="activeNav === 'sektor' ? 'bg-brand-primary text-white font-semibold' : 'text-gray-700 hover:text-brand-primary hover:bg-brand-bg font-medium'" class="px-4 py-2.5 text-base rounded-xl transition-colors">Sektor Bisnis</a>
                    <a href="#cara-kerja" @click="activeNav = 'cara-kerja'; mobileMenuOpen = false" :class="activeNav === 'cara-kerja' ? 'bg-brand-primary text-white font-semibold' : 'text-gray-700 hover:text-brand-primary hover:bg-brand-bg font-medium'" class="px-4 py-2.5 text-base rounded-xl transition-colors">Cara Kerja</a>
                </nav>
                <div class="pt-3 border-t border-brand-border flex flex-col">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full text-center bg-brand-primary text-white py-3 rounded-xl font-semibold shadow-md">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="w-full text-center bg-brand-primary text-white py-3 rounded-xl font-semibold shadow-md">Masuk Sistem</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- ========================================================= -->
    <!-- 2. HERO SECTION (Clean 3D Origami Airplane - No Floating Cards) -->
    <!-- ========================================================= -->
    <section id="hero" class="relative pt-12 pb-24 sm:pt-16 sm:pb-32 overflow-hidden bg-gradient-to-b from-[#EDE9FE] via-[#F5F3FF] to-[#FAF8FF]">
        <!-- Decorative Ambient Background Gradients -->
        <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-brand-primary/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
        <div class="absolute top-20 right-10 w-[500px] h-[500px] bg-brand-secondary/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Hero Left: Typography & Call To Action -->
                <div class="lg:col-span-7 flex flex-col items-start reveal">
                    
                    <!-- Top Pill Tag -->
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white/80 border border-brand-primary/20 shadow-sm backdrop-blur-sm mb-6">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-primary"></span>
                        </span>
                        <span class="text-xs font-bold tracking-wide text-brand-primary uppercase">
                            Smart Roster &amp; Labor Compliance B2B
                        </span>
                    </div>

                    <!-- Main Huge Typography -->
                    <h1 class="font-display font-extrabold text-4xl sm:text-5xl md:text-6xl xl:text-[64px] text-brand-text tracking-tight leading-[1.08]">
                        MANAJEMEN SHIFT<br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-primary via-[#5B48E0] to-[#4F46E5]">
                            LEBIH CERDAS &amp; OTOMATIS
                        </span>
                    </h1>

                    <!-- Script / Handwritten Slogan Accent -->
                    <p class="font-script text-2xl sm:text-3xl text-brand-primary font-bold mt-3 tracking-wide transform -rotate-1">
                        Presisi. Tanpa Bentrok. Patuh Regulasi.
                    </p>

                    <!-- Paragraph description -->
                    <p class="mt-4 text-base sm:text-lg text-[#7C7896] leading-relaxed max-w-xl font-normal">
                        Roster mingguan tersusun otomatis bebas jadwal ganda untuk bisnis F&amp;B, ritel, perhotelan, hingga fasilitas medis. Terintegrasi penuh dengan batas jam kerja &amp; hak istirahat PP No. 35 Tahun 2021.
                    </p>

                    <!-- Action Button & Trust Point -->
                    <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full sm:w-auto">
                        <a href="#simulator" class="inline-flex items-center justify-center gap-3 bg-brand-primary hover:bg-brand-hover text-white rounded-full px-8 py-4 font-bold text-base transition-all shadow-lg shadow-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/40 hover:-translate-y-0.5">
                            <span>Mulai Simulasi Jadwal</span>
                            <i data-lucide="send" class="w-4 h-4 transform rotate-45"></i>
                        </a>

                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-brand-bg text-brand-text border border-brand-border rounded-full px-7 py-4 font-semibold text-base transition-all shadow-sm hover:border-brand-primary/40">
                            <i data-lucide="lock" class="w-4 h-4 text-brand-primary"></i>
                            <span>Masuk Workspace</span>
                        </a>
                    </div>

                    <!-- Mini Trust Highlights -->
                    <div class="mt-10 flex flex-wrap items-center gap-6 text-xs text-[#7C7896] pt-6 border-t border-brand-border/80">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                            <span class="font-medium">PP No. 35/2021 Ready</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                            <span class="font-medium">0 Konflik Jam Kerja</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                            <span class="font-medium">Tukar Shift 2 Arah</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Right: Clean 3D Origami Airplane Artwork (Cards Removed as Requested) -->
                <div class="lg:col-span-5 relative flex justify-center items-center reveal delay-200">
                    
                    <!-- Paper Plane Flight Path SVG -->
                    <svg class="absolute -top-12 -left-20 w-[480px] h-[340px] pointer-events-none hidden sm:block" viewBox="0 0 480 340" fill="none">
                        <path class="flight-path" d="M 40,280 C 120,320 220,180 200,80 C 180,-20 340,20 440,60" stroke="#A29BFE" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>

                    <!-- Main Clean Artwork Composition -->
                    <div class="relative w-full max-w-md sm:max-w-lg aspect-square flex items-center justify-center">
                        
                        <!-- 3D Poly Origami Paper Plane (Clean, unobstructed geometry) -->
                        <div class="relative z-20 w-80 sm:w-96 h-80 sm:h-96 transform hover:scale-105 transition-transform duration-500">
                            <svg viewBox="0 0 320 320" class="w-full h-full drop-shadow-2xl" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Origami Main Left Wing -->
                                <polygon points="160,30 20,240 160,200" fill="#FFFFFF" />
                                <polygon points="160,30 160,200 130,220" fill="#EAE6FE" />
                                <!-- Origami Main Right Wing -->
                                <polygon points="160,30 300,240 160,200" fill="#DDD6FE" />
                                <polygon points="160,30 160,200 190,220" fill="#C4B5FD" />
                                <!-- Origami Central Keel / Fold -->
                                <polygon points="160,200 160,270 140,220" fill="#8B5CF6" />
                                <polygon points="160,200 160,270 180,220" fill="#6C5CE7" />
                                <!-- Origami Soft Under-Shadow -->
                                <polygon points="20,240 160,200 160,220" fill="#EDE9FE" opacity="0.8"/>
                                <polygon points="300,240 160,200 160,220" fill="#A78BFA" opacity="0.6"/>
                            </svg>
                        </div>

                        <!-- Origami Geometric City Landmark Peaks (Stylized poly mountains behind) -->
                        <div class="absolute bottom-0 inset-x-4 h-36 flex items-end justify-center opacity-30 pointer-events-none">
                            <svg viewBox="0 0 400 150" class="w-full h-full" fill="none">
                                <polygon points="50,150 90,50 130,150" fill="#6C5CE7" />
                                <polygon points="120,150 180,20 240,150" fill="#8B5CF6" />
                                <polygon points="230,150 280,60 330,150" fill="#A29BFE" />
                                <polygon points="310,150 350,80 390,150" fill="#DDD6FE" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 3. INTERACTIVE SIMULATION & SHIFT PLANNER WIDGET          -->
    <!-- (Fully Functional Tabs, Styled Dropdowns, Detailed Results)-->
    <!-- ========================================================= -->
    <div id="simulator" class="relative z-30 -mt-16 sm:-mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        <div class="bg-white rounded-3xl border border-brand-border shadow-2xl overflow-hidden card-elevated">
            
            <!-- Top Tab Bar (All 4 Tabs Fully Functional) -->
            <div class="flex flex-wrap items-center border-b border-brand-border bg-slate-50/80 px-4 sm:px-8 pt-3 gap-2">
                <button 
                    type="button" 
                    @click="simTab = 'roster'"
                    :class="simTab === 'roster' ? 'bg-white text-brand-primary border-brand-border border-b-white -mb-px font-bold shadow-sm' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                    class="flex items-center gap-2.5 px-6 py-3.5 rounded-t-2xl border-t border-x text-sm transition-all"
                >
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Otomasi Roster</span>
                </button>

                <button 
                    type="button" 
                    @click="simTab = 'kepatuhan'"
                    :class="simTab === 'kepatuhan' ? 'bg-white text-brand-primary border-brand-border border-b-white -mb-px font-bold shadow-sm' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                    class="flex items-center gap-2.5 px-6 py-3.5 rounded-t-2xl border-t border-x text-sm transition-all"
                >
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Kepatuhan PP 35/2021</span>
                </button>

                <button 
                    type="button" 
                    @click="simTab = 'tukar'"
                    :class="simTab === 'tukar' ? 'bg-white text-brand-primary border-brand-border border-b-white -mb-px font-bold shadow-sm' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                    class="flex items-center gap-2.5 px-6 py-3.5 rounded-t-2xl border-t border-x text-sm transition-all"
                >
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                    <span>Tukar Shift 2 Arah</span>
                </button>

                <button 
                    type="button" 
                    @click="simTab = 'lembur'"
                    :class="simTab === 'lembur' ? 'bg-white text-brand-primary border-brand-border border-b-white -mb-px font-bold shadow-sm' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                    class="flex items-center gap-2.5 px-6 py-3.5 rounded-t-2xl border-t border-x text-sm transition-all"
                >
                    <i data-lucide="timer" class="w-4 h-4"></i>
                    <span>Estimasi Lembur</span>
                </button>
            </div>

            <!-- Tab 1: Otomasi Roster Simulator -->
            <div x-show="simTab === 'roster'" class="p-5 sm:p-7">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                    
                    <!-- Field 1: Sektor Usaha (Custom Styled Dropdown) -->
                    <div class="lg:col-span-3 bg-white border border-brand-border rounded-2xl p-3 shadow-sm hover:border-brand-primary/50 transition-colors">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-brand-primary mb-1">
                            Sektor Usaha
                        </label>
                        <div class="relative">
                            <select x-model="sektor" class="w-full appearance-none bg-slate-50/70 border border-brand-border rounded-xl px-3 py-2 text-sm font-bold text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer pr-8">
                                <option value="fnb">Kafe &amp; Resto (F&amp;B)</option>
                                <option value="ritel">Minimarket &amp; Ritel</option>
                                <option value="hotel">Hotel &amp; Resor 24/7</option>
                                <option value="medis">Klinik &amp; Tenaga Medis</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-brand-primary">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Swap Icon (Interactive Functional Button that toggles/rotates shift model) -->
                    <div class="hidden lg:flex lg:col-span-1 justify-center">
                        <button 
                            type="button" 
                            @click="rotateSwap()" 
                            class="w-11 h-11 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all duration-300 shadow-sm"
                            :class="swapRotated ? 'rotate-180' : 'rotate-0'"
                            title="Rotasi Pola Shift"
                        >
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Field 2: Total Staf Tim (Custom Styled Dropdown) -->
                    <div class="lg:col-span-2 bg-white border border-brand-border rounded-2xl p-3 shadow-sm hover:border-brand-primary/50 transition-colors">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-brand-primary mb-1">
                            Anggota Staf
                        </label>
                        <div class="relative">
                            <select x-model="stafCount" class="w-full appearance-none bg-slate-50/70 border border-brand-border rounded-xl px-3 py-2 text-sm font-bold text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer pr-8">
                                <option value="6">6 Orang Staf</option>
                                <option value="12">12 Orang Staf</option>
                                <option value="24">24 Orang Staf</option>
                                <option value="48">48+ Orang Staf</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-brand-primary">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Field 3: Pola Shift (Custom Styled Dropdown) -->
                    <div class="lg:col-span-2 bg-white border border-brand-border rounded-2xl p-3 shadow-sm hover:border-brand-primary/50 transition-colors">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-brand-primary mb-1">
                            Pola Shift
                        </label>
                        <div class="relative">
                            <select x-model="shiftModel" class="w-full appearance-none bg-slate-50/70 border border-brand-border rounded-xl px-3 py-2 text-sm font-bold text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer pr-8">
                                <option value="2shift">2 Shift (Pagi &amp; Sore)</option>
                                <option value="3shift">3 Shift (Pagi, Siang, Malam)</option>
                                <option value="split">Split Shift (Jam Sibuk)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-brand-primary">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Field 4: Periode Roster (Custom Styled Dropdown) -->
                    <div class="lg:col-span-2 bg-white border border-brand-border rounded-2xl p-3 shadow-sm hover:border-brand-primary/50 transition-colors">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-brand-primary mb-1">
                            Periode Roster
                        </label>
                        <div class="relative">
                            <select x-model="periode" class="w-full appearance-none bg-slate-50/70 border border-brand-border rounded-xl px-3 py-2 text-sm font-bold text-brand-text focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer pr-8">
                                <option value="7hari">7 Hari Kalender</option>
                                <option value="14hari">14 Hari (2 Minggu)</option>
                                <option value="30hari">1 Bulan Penuh</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-brand-primary">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Field 5: Action Button -->
                    <div class="lg:col-span-2">
                        <button 
                            type="button" 
                            @click="triggerSimulation()"
                            class="w-full h-14 bg-brand-primary hover:bg-brand-hover text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-brand-primary/25 hover:shadow-xl hover:shadow-brand-primary/35 transition-all"
                        >
                            <span x-show="!isSimulating" class="flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Simulasikan Roster</span>
                            </span>
                            <span x-show="isSimulating" x-cloak class="flex items-center gap-2">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span>Mengalkulasi...</span>
                            </span>
                        </button>
                    </div>

                </div>

                <!-- Hasil Simulasi Yang Jelas & Terstruktur (Clear Simulation Results) -->
                <div x-show="showRosterResult" x-cloak class="mt-6 pt-6 border-t border-brand-border">
                    <!-- Top Metric Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Status Roster</span>
                            <span class="text-base sm:text-lg font-extrabold text-emerald-800 flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>0 Konflik Bentrok</span>
                            </span>
                        </div>
                        <div class="bg-brand-bg border border-brand-border rounded-2xl p-3.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#7C7896] block">Pemenuhan Staf</span>
                            <span class="text-base sm:text-lg font-extrabold text-brand-primary mt-0.5 block">
                                <span x-text="stafCount"></span>/<span x-text="stafCount"></span> Terjadwal Penuh
                            </span>
                        </div>
                        <div class="bg-brand-bg border border-brand-border rounded-2xl p-3.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#7C7896] block">Rata-rata Jam Kerja</span>
                            <span class="text-base sm:text-lg font-extrabold text-brand-text mt-0.5 block">
                                38.5 Jam / Minggu
                            </span>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-2xl p-3.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 block">Jeda Antar Shift</span>
                            <span class="text-base sm:text-lg font-extrabold text-purple-800 mt-0.5 block">
                                &ge; 12 Jam (Aman PP 35)
                            </span>
                        </div>
                    </div>

                    <!-- Visual Shift Schedule Breakdown Matrix -->
                    <div class="bg-slate-50/70 border border-brand-border rounded-2xl p-4 sm:p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                                <h4 class="text-xs font-bold text-brand-text uppercase tracking-wider">
                                    Sampel Distribusi Shift Hasil Simulasi (<span class="capitalize" x-text="sektor"></span>)
                                </h4>
                            </div>
                            <span class="text-xs text-emerald-600 font-semibold bg-emerald-100/70 px-2.5 py-0.5 rounded-full">
                                Siap Diterbitkan
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Shift Pagi -->
                            <div class="bg-white border border-brand-border rounded-xl p-3.5 shadow-sm">
                                <div class="flex items-center justify-between text-xs pb-2 border-b border-brand-border/60">
                                    <span class="font-bold text-brand-text">Shift Pagi (07:00 - 15:00)</span>
                                    <span class="font-semibold text-brand-primary">Kuota: 100%</span>
                                </div>
                                <p class="text-xs text-[#7C7896] mt-2">
                                    Staf: <span class="font-semibold text-brand-text">Budi S., Rian P., Nina W., Andi K.</span>
                                </p>
                                <span class="inline-block mt-2 text-[10px] bg-brand-primary/10 text-brand-primary font-bold px-2 py-0.5 rounded">
                                    Rush Hour Pagi Terpenuhi
                                </span>
                            </div>

                            <!-- Shift Siang/Sore -->
                            <div class="bg-white border border-brand-border rounded-xl p-3.5 shadow-sm">
                                <div class="flex items-center justify-between text-xs pb-2 border-b border-brand-border/60">
                                    <span class="font-bold text-brand-text">Shift Siang (14:00 - 22:00)</span>
                                    <span class="font-semibold text-brand-primary">Kuota: 100%</span>
                                </div>
                                <p class="text-xs text-[#7C7896] mt-2">
                                    Staf: <span class="font-semibold text-brand-text">Siti N., Maya A., Farhan T., Dewi S.</span>
                                </p>
                                <span class="inline-block mt-2 text-[10px] bg-brand-primary/10 text-brand-primary font-bold px-2 py-0.5 rounded">
                                    Overlapping Handover 1 Jam
                                </span>
                            </div>

                            <!-- Shift Malam/Closing -->
                            <div class="bg-white border border-brand-border rounded-xl p-3.5 shadow-sm">
                                <div class="flex items-center justify-between text-xs pb-2 border-b border-brand-border/60">
                                    <span class="font-bold text-brand-text">Shift Malam (16:00 - 24:00)</span>
                                    <span class="font-semibold text-brand-primary">Kuota: 100%</span>
                                </div>
                                <p class="text-xs text-[#7C7896] mt-2">
                                    Staf: <span class="font-semibold text-brand-text">Dimas R., Citra L., Eko P., Kevin M.</span>
                                </p>
                                <span class="inline-block mt-2 text-[10px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded">
                                    Terkunci: Jeda Istirahat &ge; 11 Jam
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kepatuhan PP 35 Simulator -->
            <div x-show="simTab === 'kepatuhan'" x-cloak class="p-5 sm:p-7">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white border border-brand-border rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-bold text-brand-primary mb-1">Jam Kerja per Hari</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="range" min="6" max="10" x-model.number="ppJamKerja" class="w-full accent-brand-primary cursor-pointer">
                            <span class="font-display font-extrabold text-lg text-brand-text w-12" x-text="ppJamKerja + ' Jam'"></span>
                        </div>
                        <p class="text-[11px] text-[#7C7896] mt-1">PP 35: Maksimal 7 jam (6 hari kerja) / 8 jam (5 hari kerja).</p>
                    </div>

                    <div class="bg-white border border-brand-border rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-bold text-brand-primary mb-1">Hari Kerja per Minggu</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="range" min="4" max="6" x-model.number="ppHariKerja" class="w-full accent-brand-primary cursor-pointer">
                            <span class="font-display font-extrabold text-lg text-brand-text w-12" x-text="ppHariKerja + ' Hari'"></span>
                        </div>
                        <p class="text-[11px] text-[#7C7896] mt-1">PP 35: Wajib memberikan 1 atau 2 hari istirahat mingguan.</p>
                    </div>

                    <div class="bg-white border border-brand-border rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-bold text-brand-primary mb-1">Jeda Istirahat Antar Shift</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="range" min="8" max="16" x-model.number="ppJedaIstirahat" class="w-full accent-brand-primary cursor-pointer">
                            <span class="font-display font-extrabold text-lg text-brand-text w-12" x-text="ppJedaIstirahat + ' Jam'"></span>
                        </div>
                        <p class="text-[11px] text-[#7C7896] mt-1">PP 35: Wajib istirahat &ge; 11 jam sebelum shift berikutnya.</p>
                    </div>
                </div>

                <!-- Hasil Kalkulasi Kepatuhan PP 35 -->
                <div class="p-5 rounded-2xl border" :class="ppIsCompliant ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : 'bg-rose-50 border-rose-200 text-rose-950'">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-full" :class="ppIsCompliant ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'" x-text="ppIsCompliant ? '100% SESUAI PP NO. 35/2021' : 'PERINGATAN PELANGGARAN REGULASI'"></span>
                            <h4 class="font-display font-extrabold text-xl mt-2" x-text="'Total Jam: ' + ppTotalJam + ' Jam/Minggu (Batas: 40 Jam)'"></h4>
                            <p class="text-xs mt-1" :class="ppIsCompliant ? 'text-emerald-700' : 'text-rose-700'" x-text="ppIsCompliant ? 'Ketentuan jam kerja reguler dan jeda istirahat antar shift staf telah memenuhi standar ketenagakerjaan Republik Indonesia.' : 'Jadwal melebihi batas 40 jam kerja per minggu atau jeda istirahat kurang dari 11 jam. Harus dikonversi menjadi lembur resmi.'"></p>
                        </div>
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs shrink-0 transition-colors shadow-sm" :class="ppIsCompliant ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'">
                            Gunakan Validator Ini
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Tukar Shift Simulator -->
            <div x-show="simTab === 'tukar'" x-cloak class="p-5 sm:p-7">
                <div class="bg-slate-50 border border-brand-border rounded-2xl p-5 mb-5">
                    <h4 class="font-display font-bold text-base text-brand-text mb-1">Alur Simulasi Tukar Shift Dua Arah</h4>
                    <p class="text-xs text-[#7C7896]">Coba alur pengajuan mandiri karyawan hingga persetujuan manajer di bawah ini:</p>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4 text-xs">
                        <div class="p-3 bg-white border border-brand-border rounded-xl">
                            <span class="font-bold text-brand-primary block">1. Staf A Mengajukan</span>
                            <p class="text-[#7C7896] mt-1">Budi (Barista) ajukan tukar shift Rabu Siang.</p>
                        </div>
                        <div class="p-3 bg-white border border-brand-border rounded-xl">
                            <span class="font-bold text-brand-primary block">2. Staf B Menerima</span>
                            <p class="text-[#7C7896] mt-1">Siti (Kasir) menyetujui pertukaran.</p>
                        </div>
                        <div class="p-3 bg-white border border-brand-border rounded-xl">
                            <span class="font-bold text-emerald-600 block">3. Validasi Otomatis</span>
                            <p class="text-[#7C7896] mt-1">Sistem cek: 0 bentrok, jam kerja aman.</p>
                        </div>
                        <div class="p-3 bg-white border border-brand-border rounded-xl">
                            <span class="font-bold text-purple-700 block">4. Approval Manajer</span>
                            <p class="text-[#7C7896] mt-1">Manajer verifikasi 1 klik.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 rounded-2xl bg-white border border-brand-border shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center">
                            <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-brand-text">Simulasi: Permohonan Tukar Budi Santoso &harr; Siti Nurhaliza</p>
                            <p class="text-[11px] text-[#7C7896]">Rabu, 15 Okt 2026 • Shift Siang ditukar Shift Pagi</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="swapApproved = !swapApproved"
                        class="px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-sm"
                        :class="swapApproved ? 'bg-emerald-600 text-white' : 'bg-brand-primary hover:bg-brand-hover text-white'"
                    >
                        <span x-text="swapApproved ? '✓ Disetujui Manajer' : 'Klik Setujui Permohonan'"></span>
                    </button>
                </div>
            </div>

            <!-- Tab 4: Estimasi Lembur Simulator -->
            <div x-show="simTab === 'lembur'" x-cloak class="p-5 sm:p-7">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div class="bg-white border border-brand-border rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-bold text-brand-primary mb-1">Jam Lembur per Hari</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="range" min="1" max="5" x-model.number="overtimeHoursDay" class="w-full accent-brand-primary cursor-pointer">
                            <span class="font-display font-extrabold text-lg text-brand-text w-12" x-text="overtimeHoursDay + ' Jam'"></span>
                        </div>
                        <p class="text-[11px] text-[#7C7896] mt-1">Batas PP 35 Pasal 26: Maksimal 4 jam lembur dalam 1 hari.</p>
                    </div>

                    <div class="bg-white border border-brand-border rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-bold text-brand-primary mb-1">Frekuensi Hari Lembur / Minggu</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="range" min="1" max="5" x-model.number="overtimeDaysWeek" class="w-full accent-brand-primary cursor-pointer">
                            <span class="font-display font-extrabold text-lg text-brand-text w-12" x-text="overtimeDaysWeek + ' Hari'"></span>
                        </div>
                        <p class="text-[11px] text-[#7C7896] mt-1">Batas PP 35 Pasal 26: Maksimal 18 jam lembur dalam 1 minggu.</p>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border bg-purple-50/60 border-purple-200 text-purple-950">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-purple-700 uppercase tracking-wider">Hasil Simulasi Lembur Karyawan:</span>
                            <h4 class="font-display font-extrabold text-xl mt-1 text-brand-text" x-text="'Total: ' + totalOvertimeWeek + ' Jam Lembur / Minggu'"></h4>
                            <p class="text-xs text-purple-800 mt-1">
                                Koefisien Upah Lembur PP 35: <span class="font-bold" x-text="overtimeMultiplier + 'x Upah Sejam per Hari'"></span> (Jam 1: 1.5x, Jam berikutnya: 2.0x).
                            </p>
                        </div>
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold shrink-0" :class="totalOvertimeWeek <= 18 && overtimeHoursDay <= 4 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'" x-text="totalOvertimeWeek <= 18 && overtimeHoursDay <= 4 ? 'Aman &le; 18 Jam/Minggu' : 'Melebihi Batas PP 35!'"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 4. 4 TRUST / FEATURE BADGES                               -->
    <!-- ========================================================= -->
    <section class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Badge 1: 0 Bentrok Jadwal -->
                <div class="bg-white rounded-2xl border border-brand-border p-5 flex items-center gap-4 card-elevated card-elevated-hover reveal delay-100">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] border border-brand-primary/20 flex items-center justify-center shrink-0 text-brand-primary shadow-inner">
                        <i data-lucide="calendar-x-2" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-brand-text">
                            0 Bentrok Jadwal
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-0.5 leading-relaxed">
                            Algoritma deteksi instan jadwal ganda antar staf.
                        </p>
                    </div>
                </div>

                <!-- Badge 2: Kepatuhan PP 35/2021 -->
                <div class="bg-white rounded-2xl border border-brand-border p-5 flex items-center gap-4 card-elevated card-elevated-hover reveal delay-200">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] border border-brand-primary/20 flex items-center justify-center shrink-0 text-brand-primary shadow-inner">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-brand-text">
                            Kepatuhan PP 35
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-0.5 leading-relaxed">
                            Batas jam kerja 40 jam &amp; jeda 11 jam terpantau otomatis.
                        </p>
                    </div>
                </div>

                <!-- Badge 3: Tukar Shift Mandiri -->
                <div class="bg-white rounded-2xl border border-brand-border p-5 flex items-center gap-4 card-elevated card-elevated-hover reveal delay-300">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] border border-brand-primary/20 flex items-center justify-center shrink-0 text-brand-primary shadow-inner">
                        <i data-lucide="headphones" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-brand-text">
                            Tukar Shift Mandiri
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-0.5 leading-relaxed">
                            Alur tukar shift resmi 2 arah dengan izin manajer.
                        </p>
                    </div>
                </div>

                <!-- Badge 4: Multi-Cabang & Outlet -->
                <div class="bg-white rounded-2xl border border-brand-border p-5 flex items-center gap-4 card-elevated card-elevated-hover reveal delay-400">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] border border-brand-primary/20 flex items-center justify-center shrink-0 text-brand-primary shadow-inner">
                        <i data-lucide="briefcase" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-brand-text">
                            Multi-Cabang &amp; Tim
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-0.5 leading-relaxed">
                            Kelola banyak outlet &amp; divisi tanpa batasan.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 4.5 ALL FEATURES SHOWCASE (SEMUA FITUR JADWALIN)          -->
    <!-- 12 Modul Lengkap, Filter Kategori, Spotlight Peran, & RBAC Matrix -->
    <!-- ========================================================= -->
    <section id="fitur" class="py-16 sm:py-24 bg-gradient-to-b from-white via-brand-bg/30 to-white relative overflow-hidden border-t border-brand-border">
        <!-- Ambient Decorative Glows -->
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-brand-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-14 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-xs font-bold text-brand-primary uppercase tracking-wider mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Ekosistem Lengkap Platform</span>
                </div>
                <h2 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-5xl text-brand-text tracking-tight">
                    SEMUA FITUR JADWALIN
                </h2>
                <p class="font-script text-2xl text-brand-primary font-bold mt-2">
                    Otomatis. Adil. Terlindungi Hukum.
                </p>
                <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed max-w-2xl mx-auto">
                    Kumpulan fitur terpadu untuk mengelola operasional kerja shift: dari penyusunan roster tim, validasi hukum PP No. 35/2021, presensi digital mandiri, hingga laporan timesheet kerja.
                </p>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5 mb-10 sm:mb-12 reveal delay-100">
                <button 
                    type="button"
                    @click="featureFilter = 'all'" 
                    :class="featureFilter === 'all' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200"
                >
                    Semua Fitur (12)
                </button>
                <button 
                    type="button"
                    @click="featureFilter = 'scheduling'" 
                    :class="featureFilter === 'scheduling' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5"
                >
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Penjadwalan Roster</span>
                </button>
                <button 
                    type="button"
                    @click="featureFilter = 'compliance'" 
                    :class="featureFilter === 'compliance' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5"
                >
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Kepatuhan PP 35</span>
                </button>
                <button 
                    type="button"
                    @click="featureFilter = 'swap'" 
                    :class="featureFilter === 'swap' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5"
                >
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                    <span>Tukar &amp; Open Shift</span>
                </button>
                <button 
                    type="button"
                    @click="featureFilter = 'attendance'" 
                    :class="featureFilter === 'attendance' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5"
                >
                    <i data-lucide="fingerprint" class="w-4 h-4"></i>
                    <span>Presensi &amp; Timesheet</span>
                </button>
                <button 
                    type="button"
                    @click="featureFilter = 'admin'" 
                    :class="featureFilter === 'admin' ? 'bg-brand-primary text-white shadow-md shadow-brand-primary/25 font-bold' : 'bg-white text-[#7C7896] hover:text-brand-text border border-brand-border hover:bg-slate-50 font-medium'" 
                    class="px-4 sm:px-5 py-2.5 rounded-full text-xs sm:text-sm transition-all duration-200 flex items-center gap-1.5"
                >
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Struktur &amp; RBAC</span>
                </button>
            </div>

            <!-- 12 Rich Feature Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                
                <!-- Fitur 1: Smart Roster & Auto-Scheduling -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'scheduling'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-100 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="calendar-check" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-violet-100 text-violet-800">
                                    Penjadwalan Roster
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Role: Manager / Admin
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Smart Roster &amp; Auto-Scheduling
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Algoritma cerdas yang menyusun roster kerja mingguan tanpa risiko bentrok. Menghitung kuota staf, kompetensi divisi, dan preferensi waktu secara presisi.
                        </p>

                        <!-- Key Highlights Bullets -->
                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Deteksi bentrok jadwal ganda secara instan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Auto-fill kuota posisi (kasir, barista, perawat)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Tampilan grid kalender mingguan &amp; bulanan</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            0 Konflik Jadwal
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'smart-roster'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 2: Master Template Shift Fleksibel -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'scheduling'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-200 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="calendar-days" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    Penjadwalan Roster
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Role: Super Admin / Mgr
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Master Template Shift Fleksibel
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Konfigurasi template jam kerja: pola 2-shift, 3-shift nonstop 24/7, hingga Split Shift jam sibuk (rush hour resto). Lengkap dengan batas durasi dan toleransi terlambat.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Mendukung Split Shift (siang &amp; malam)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Preset toleransi keterlambatan menit kerja</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Rotasi otomatis shift bergulir mingguan</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            24/7 &amp; Split Shift
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'shift-templates'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 3: Kepatuhan Jam Kerja PP No. 35/2021 -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'compliance'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-300 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="shield-check" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Kepatuhan PP 35
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Semua Peran (Legal RI)
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Kepatuhan Jam Kerja PP 35/2021
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Mencegah pelanggaran Pasal 21 PP No. 35 Tahun 2021. Menjaga batas 7 jam/hari (6 hari kerja) atau 8 jam/hari (5 hari kerja) dengan akumulasi maksimal 40 jam per minggu.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Kunci otomatis akumulasi maks 40 jam/minggu</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Peringatan dini visual sebelum jam berlebih</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Jaminan kepatuhan audit Disnaker &amp; Kemenaker</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Batas Maks 40 Jam
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'pp35-hours'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 4: Proteksi Jeda Istirahat Minimal 11 Jam -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'compliance'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-100 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white flex items-center justify-center shadow-md shadow-cyan-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="coffee" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800">
                                    Kepatuhan PP 35
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    K3 &amp; Kesehatan Staf
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Proteksi Jeda Istirahat &ge; 11 Jam
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Mencegah staf yang selesai dinas shift malam (closing) langsung dipaksa shift pagi (opening) keesokan harinya. Menjamin hak pemulihan fisik minimal 11 jam terpenuhi.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Blokir otomatis closing-to-opening berbahaya</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Menurunkan fatigue &amp; risiko kecelakaan kerja</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Rekomendasi staf pengganti dengan jeda cukup</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                            Jeda 11 Jam Wajib
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'rest-period'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 5: Pengendalian Lembur & Formula Koefisien -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'compliance'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-200 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="timer" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                    Kepatuhan PP 35
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Pasal 26 PP 35
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Pencatatan &amp; Batasan Jam Lembur
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Membatasi lembur maksimal 4 jam/hari dan 18 jam/minggu sesuai Pasal 26 PP 35/2021. Jam kerja di atas batas reguler otomatis diakumulasikan sebagai jam lembur.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Batas ketat maksimal 18 jam lembur/minggu</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Pemisahan jam reguler dan jam lembur saat clock-out</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Riwayat jam lembur tercatat dalam laporan timesheet</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Maks 18 Jam Lembur
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'overtime-control'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 6: Tukar Shift Mandiri (Peer-to-Peer) -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'swap'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-300 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 text-white flex items-center justify-center shadow-md shadow-pink-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="arrow-left-right" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-800">
                                    Tukar &amp; Open Shift
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Staf &amp; Manajer
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Tukar Shift Mandiri (Peer-to-Peer)
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Karyawan dapat mengajukan tukar jadwal langsung ke rekan kerja lewat smartphone. Sistem memvalidasi bebas bentrok dan manajer menyetujui dalam 1 kali klik.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Persetujuan 2 arah (Rekan &amp; Manajer)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Validasi otomatis bebas jadwal tumpang tindih</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Notifikasi real-time &amp; riwayat swap rapi</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                            Approval 2 Arah
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'peer-swap'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 7: Bursa Klaim Shift Terbuka (Open Shifts) -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'swap'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-100 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center shadow-md shadow-rose-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="layers" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800">
                                    Tukar &amp; Open Shift
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Operasional Darurat
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Bursa Klaim Shift Terbuka
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Manajer dapat mempublikasikan shift darurat ke papan bursa terbuka. Karyawan yang berstatus libur dapat mengklaimnya secara adil tanpa melanggar batas 40 jam.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Cover kekosongan staf mendadak hitungan menit</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Distribusi jam kerja tambahan yang transparan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Proteksi: Hanya staf di bawah 40 jam yang bisa klaim</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Cover Shift Kilat
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'open-shifts'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 8: Presensi Digital Mandiri -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'attendance'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-200 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-emerald-600 to-green-700 text-white flex items-center justify-center shadow-md shadow-green-600/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="fingerprint" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Presensi &amp; Timesheet
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Clock-In / Clock-Out
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Presensi Digital Mandiri
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Pencatatan kehadiran clock-in dan clock-out mandiri bagi staf yang memiliki jadwal tugas hari ini, lengkap dengan toleransi keterlambatan (grace period).
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Clock-in &amp; clock-out langsung sesuai shift aktif</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Deteksi keterlambatan &amp; hitung late minutes otomatis</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Pemisahan jam kerja reguler &amp; lembur saat clock-out</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Clock-In &amp; Toleransi
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'attendance'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 9: Laporan Timesheet & Rekapitulasi Jam Kerja -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'attendance'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-300 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-blue-600/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="clock" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    Presensi &amp; Timesheet
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Laporan Bulanan
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Laporan Timesheet Kerja
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Rekapitulasi total jam kerja reguler dan jam lembur per periode bulan dan tahun. Memudahkan manajer dan staf meninjau riwayat jam kerja secara transparan.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Rekap total jam kerja reguler bulanan per staf</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Akumulasi jam lembur tercatat terpisah &amp; akurat</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Arsip riwayat timesheet per periode bulan &amp; tahun</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-800 bg-blue-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Timesheet Bulanan
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'timesheet-reports'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 10: Manajemen Ketersediaan Waktu Staf -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'scheduling'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-100 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center shadow-md shadow-teal-500/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="calendar-check-2" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800">
                                    Penjadwalan Roster
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Karyawan &amp; Manajer
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Ketersediaan Waktu &amp; Libur Staf
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Staf dapat menandai hari kuliah, urusan keluarga, atau hari libur pilihan. Informasi langsung muncul di grid kalender manajer untuk mencegah izin dadakan.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Menekan angka absen mendadak hingga 80%</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Karyawan input preferensi waktu luang mandiri</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Jaminan hak istirahat mingguan pekerja</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-800 bg-teal-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            Minimalkan Izin Dadakan
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'staff-availability'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 11: Multi-Cabang Outlet & Struktur Departemen -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'admin'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-200 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-purple-800 text-white flex items-center justify-center shadow-md shadow-violet-600/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="building-2" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                    Struktur &amp; RBAC
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    Super Admin
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            Multi-Cabang &amp; Departemen
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Hirarki organisasi lengkap: Perusahaan, Cabang Outlet, Departemen, dan Posisi Jabatan (CRUD). Mendukung zona waktu berbeda (WIB, WITA, WIT) dalam satu akun.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Manajemen cabang waralaba &amp; multi-outlet</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>CRUD Departemen &amp; Posisi Jabatan staf</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Isolasi data tenant yang aman &amp; terenkripsi</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-800 bg-purple-50 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            Multi-Outlet Ready
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'multi-branch'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Fitur 12: Role-Based Access Control & Audit Log -->
                <div 
                    x-show="featureFilter === 'all' || featureFilter === 'admin'" 
                    class="bg-white rounded-3xl border border-brand-border p-6 sm:p-7 flex flex-col justify-between card-elevated card-elevated-hover reveal delay-300 group"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center shadow-md shadow-slate-900/20 group-hover:scale-105 transition-transform">
                                <i data-lucide="shield" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800">
                                    Struktur &amp; RBAC
                                </span>
                                <span class="text-[11px] font-bold text-[#7C7896]">
                                    3 Peran Terdedikasi
                                </span>
                            </div>
                        </div>

                        <h3 class="font-display font-bold text-xl text-brand-text group-hover:text-brand-primary transition-colors">
                            3 Peran RBAC &amp; Audit Trail Digital
                        </h3>
                        <p class="text-xs sm:text-[13px] text-[#7C7896] mt-2.5 leading-relaxed">
                            Hak akses terproteksi untuk Super Admin, Manager, dan Karyawan. Rekam jejak audit digital mencatat setiap perubahan roster shift demi akuntabilitas mutlak.
                        </p>

                        <ul class="mt-4 space-y-2 text-xs text-brand-text/90 pt-3 border-t border-brand-border/70">
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>3 Tingkat hak akses: Admin, Manager, Karyawan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Jejak log aktivitas siapa &amp; kapan mengubah roster</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span>Keamanan data ketenagakerjaan berstandar industri</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                            Audit Trail Aktif
                        </span>
                        <button 
                            type="button" 
                            @click="selectedFeatureModal = 'rbac-security'" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-brand-primary hover:text-brand-hover group-hover:translate-x-0.5 transition-transform"
                        >
                            <span>Detail Alur</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- ===================================================== -->
            <!-- SPOTLIGHT INTERAKTIF: PENGALAMAN KERJA 3 PERAN SISTEM -->
            <!-- ===================================================== -->
            <div class="mt-16 sm:mt-24 pt-12 sm:pt-16 border-t border-brand-border reveal">
                <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
                    <span class="text-xs font-extrabold text-brand-primary uppercase tracking-widest">
                        Alur Kerja Nyata
                    </span>
                    <h3 class="font-display font-extrabold text-2xl sm:text-3xl text-brand-text mt-1">
                        PENGALAMAN KERJA SESUAI PERAN PENGGUNA
                    </h3>
                    <p class="text-xs sm:text-sm text-[#7C7896] mt-2">
                        Pilih peran di bawah ini untuk melihat antarmuka dan tugas harian yang dipermudah oleh Jadwalin:
                    </p>
                </div>

                <!-- 3 Role Selector Tabs -->
                <div class="flex justify-center mb-8">
                    <div class="inline-flex p-1.5 bg-slate-100/90 rounded-2xl border border-brand-border gap-1">
                        <button 
                            type="button" 
                            @click="roleSpotlight = 'manager'" 
                            :class="roleSpotlight === 'manager' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                            class="px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2"
                        >
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            <span>👔 Manager Operasional</span>
                        </button>
                        <button 
                            type="button" 
                            @click="roleSpotlight = 'employee'" 
                            :class="roleSpotlight === 'employee' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                            class="px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2"
                        >
                            <i data-lucide="smartphone" class="w-4 h-4"></i>
                            <span>👤 Karyawan / Staf</span>
                        </button>
                        <button 
                            type="button" 
                            @click="roleSpotlight = 'superadmin'" 
                            :class="roleSpotlight === 'superadmin' ? 'bg-white text-brand-primary shadow-sm font-bold' : 'text-[#7C7896] hover:text-brand-text font-medium'"
                            class="px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center gap-2"
                        >
                            <i data-lucide="crown" class="w-4 h-4"></i>
                            <span>👑 Super Admin</span>
                        </button>
                    </div>
                </div>

                <!-- Role Preview Container (Interactive Mock Interface) -->
                <div class="bg-white rounded-3xl border border-brand-border shadow-xl p-6 sm:p-10 card-elevated">
                    
                    <!-- Role View 1: MANAGER OPERASIONAL -->
                    <div x-show="roleSpotlight === 'manager'" class="space-y-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-brand-border">
                            <div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-brand-primary bg-brand-primary/10 px-3 py-1 rounded-full">
                                    Workspace Manajer Cabang
                                </span>
                                <h4 class="font-display font-extrabold text-2xl text-brand-text mt-2">
                                    Penyusunan Roster &amp; Pengawasan Tim Real-Time
                                </h4>
                                <p class="text-xs sm:text-sm text-[#7C7896] mt-1">
                                    Manajer memegang kendali penuh atas plotting shift, validasi kepatuhan PP 35, approval tukar shift, dan rekap absensi.
                                </p>
                            </div>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold px-6 py-3 rounded-xl shadow-md shrink-0">
                                <span>Coba Dasbor Manajer</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>

                        <!-- Mock Data Interactive Widgets -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <div class="flex items-center justify-between text-xs text-[#7C7896] mb-1">
                                    <span class="font-bold uppercase tracking-wider">Status Roster Tim</span>
                                    <span class="text-emerald-600 font-bold">Aktif Terbit</span>
                                </div>
                                <p class="font-display font-extrabold text-xl text-brand-text mt-1">Minggu Ke-42 (24 Staf)</p>
                                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>100% Kuota Terpenuhi • 0 Konflik</span>
                                </p>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <div class="flex items-center justify-between text-xs text-[#7C7896] mb-1">
                                    <span class="font-bold uppercase tracking-wider">Presensi Tim Hari Ini</span>
                                    <span class="text-brand-primary font-bold">Clock-In Realtime</span>
                                </div>
                                <p class="font-display font-extrabold text-xl text-brand-text mt-1">18 / 18 Staf Terjadwal</p>
                                <p class="text-xs text-[#7C7896] mt-1 flex items-center gap-1">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                    <span>Tercatat hadir sesuai jadwal shift hari ini</span>
                                </p>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <div class="flex items-center justify-between text-xs text-[#7C7896] mb-1">
                                    <span class="font-bold uppercase tracking-wider">Permohonan Menunggu</span>
                                    <span class="text-amber-600 font-bold">1 Tindakan</span>
                                </div>
                                <p class="font-display font-extrabold text-xl text-brand-text mt-1">1 Tukar Shift Barista</p>
                                <p class="text-xs text-amber-700 mt-1 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>Budi Santoso &harr; Siti N. (Verifikasi aman)</span>
                                </p>
                            </div>
                        </div>

                        <!-- 4 Capability Pills -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-2">
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                <span class="font-semibold text-brand-text">Generate Jadwal 5 Menit</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Audit 40 Jam PP 35 Otomatis</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="arrow-left-right" class="w-4 h-4 text-purple-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Approval Swap 1-Klik</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-blue-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Laporan Timesheet Tim</span>
                            </div>
                        </div>
                    </div>

                    <!-- Role View 2: KARYAWAN LAPANGAN -->
                    <div x-show="roleSpotlight === 'employee'" x-cloak class="space-y-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-brand-border">
                            <div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-pink-700 bg-pink-100 px-3 py-1 rounded-full">
                                    Aplikasi Mobile Staf
                                </span>
                                <h4 class="font-display font-extrabold text-2xl text-brand-text mt-2">
                                    Jadwal di Saku, Tukar Shift Mandiri &amp; Presensi Mandiri
                                </h4>
                                <p class="text-xs sm:text-sm text-[#7C7896] mt-1">
                                    Karyawan melihat kalender jadwal personal, melakukan presensi clock-in/out mandiri, serta mengajukan pergantian shift dengan rekan kerja kapan saja.
                                </p>
                            </div>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold px-6 py-3 rounded-xl shadow-md shrink-0">
                                <span>Coba Akses Karyawan</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>

                        <!-- Mock Employee Smartphone Interface -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-br from-violet-50 to-indigo-50 border border-brand-primary/20 rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider">Shift Anda Hari Ini</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">Shift Siang (14:00 - 22:00)</p>
                                <p class="text-xs text-[#7C7896] mt-1">Outlet: Kopi Nusantara Malang Pusat • Posisi: Barista</p>
                                <span class="inline-block mt-3 text-[10px] font-extrabold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">
                                    Jeda Kemarin: 15 Jam (Aman)
                                </span>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-[#7C7896] uppercase tracking-wider">Presensi Shift Hari Ini</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">Status: Siap Clock-In</p>
                                <p class="text-xs text-[#7C7896] mt-1">Toleransi keterlambatan 15 menit dari jam shift.</p>
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="button" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm">
                                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                        <span>Clock In</span>
                                    </button>
                                    <button type="button" class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                        <span>Clock Out</span>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-[#7C7896] uppercase tracking-wider">Bursa Open Shift</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">2 Shift Tersedia</p>
                                <p class="text-xs text-[#7C7896] mt-1">Sabtu 18 Okt: Shift Pagi (Butuh 1 Kasir)</p>
                                <button type="button" class="mt-3 px-3 py-1.5 rounded-lg bg-brand-primary text-white font-bold text-xs flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="hand-metal" class="w-3.5 h-3.5"></i>
                                    <span>Klaim Shift Ini</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-2">
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                <span class="font-semibold text-brand-text">Cek Kalender Jadwal Saya</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="arrow-left-right" class="w-4 h-4 text-pink-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Ajukan Tukar Rekan Kerja</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Clock-In &amp; Clock-Out</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="calendar-check" class="w-4 h-4 text-blue-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Atur Ketersediaan &amp; Libur</span>
                            </div>
                        </div>
                    </div>

                    <!-- Role View 3: SUPER ADMIN -->
                    <div x-show="roleSpotlight === 'superadmin'" x-cloak class="space-y-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-brand-border">
                            <div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-800 bg-slate-100 px-3 py-1 rounded-full">
                                    Pusat Kendali Perusahaan
                                </span>
                                <h4 class="font-display font-extrabold text-2xl text-brand-text mt-2">
                                    Manajemen Multi-Tenant, Cabang Outlet &amp; Audit Log
                                </h4>
                                <p class="text-xs sm:text-sm text-[#7C7896] mt-1">
                                    Super Admin mengatur struktur departemen &amp; posisi jabatan (CRUD), mengelola master template shift, pengaturan lisensi, dan log audit global.
                                </p>
                            </div>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold px-6 py-3 rounded-xl shadow-md shrink-0">
                                <span>Coba Dasbor Super Admin</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-[#7C7896] uppercase tracking-wider">Perusahaan &amp; Cabang</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">5 Outlet Aktif</p>
                                <p class="text-xs text-[#7C7896] mt-1">Malang Pusat, Soekarno-Hatta, Surabaya Barat, Batu, Ijen</p>
                                <span class="inline-block mt-3 text-[10px] font-extrabold text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full">
                                    Paket Business (100 Staf)
                                </span>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-[#7C7896] uppercase tracking-wider">Departemen &amp; Posisi</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">4 Dept / 12 Posisi</p>
                                <p class="text-xs text-[#7C7896] mt-1">Bar &amp; Coffee, Kitchen, Kasir &amp; Service, Kebersihan</p>
                                <span class="inline-block mt-3 text-[10px] font-extrabold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">
                                    CRUD PBL-9 Terintegrasi
                                </span>
                            </div>

                            <div class="bg-slate-50 border border-brand-border rounded-2xl p-4">
                                <span class="text-[10px] font-bold text-[#7C7896] uppercase tracking-wider">Digital Audit Log</span>
                                <p class="font-display font-extrabold text-lg text-brand-text mt-1">2,480 Riwayat Tercatat</p>
                                <p class="text-xs text-[#7C7896] mt-1">Semua aksi persetujuan jadwal &amp; shift tercatat aman.</p>
                                <span class="inline-block mt-3 text-[10px] font-extrabold text-blue-700 bg-blue-100 px-2.5 py-0.5 rounded-full">
                                    Enkripsi Integritas Data
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-2">
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="building-2" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                <span class="font-semibold text-brand-text">Kelola Outlet Multi-Cabang</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="briefcase" class="w-4 h-4 text-purple-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">CRUD Departemen &amp; Posisi</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="calendar-days" class="w-4 h-4 text-blue-600 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Master Shift &amp; Toleransi</span>
                            </div>
                            <div class="p-3 bg-brand-bg/60 rounded-xl border border-brand-border flex items-center gap-2">
                                <i data-lucide="shield" class="w-4 h-4 text-slate-800 shrink-0"></i>
                                <span class="font-semibold text-brand-text">Audit Log &amp; Pengaturan Platform</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>



        </div>
    </section>

    <!-- ========================================================= -->
    <!-- MODAL DETAIL ALUR FITUR (INTERACTIVE POPUP)              -->
    <!-- ========================================================= -->
    <div 
        x-show="selectedFeatureModal" 
        x-cloak 
        @keydown.escape.window="selectedFeatureModal = null"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-brand-text/50 backdrop-blur-sm"
    >
        <div 
            @click.outside="selectedFeatureModal = null"
            class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-brand-border relative"
        >
            <button 
                type="button" 
                @click="selectedFeatureModal = null" 
                class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-brand-text flex items-center justify-center transition-colors"
                aria-label="Tutup Detail Fitur"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Modal Content 1: Smart Roster -->
            <div x-show="selectedFeatureModal === 'smart-roster'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white flex items-center justify-center">
                        <i data-lucide="calendar-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider">Modul Penjadwalan</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Smart Roster &amp; Auto-Scheduling</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Sistem otomatisasi penyusunan jadwal kerja yang memperhitungkan kualifikasi posisi (barista, kasir, koki, perawat), kebutuhan jam operasional, dan ketersediaan waktu staf tanpa pernah menghasilkan jadwal bentrok.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Kerja 3 Tahap:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Penentuan Periode:</strong> Manajer memilih rentang waktu roster (mingguan/bulanan) dan cabang outlet.</p>
                        <p><strong class="text-brand-text">2. Auto-Plotting:</strong> Algoritma mengisi kuota shift dengan staf yang tersedia dan memiliki jeda istirahat aman.</p>
                        <p><strong class="text-brand-text">3. Publikasi 1-Klik:</strong> Jadwal terbit seketika ke ponsel seluruh staf; notifikasi otomatis dikirim.</p>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Terkunci ke batas 40 jam kerja per minggu sesuai PP No. 35/2021.</span>
                </div>
            </div>

            <!-- Modal Content 2: Shift Templates -->
            <div x-show="selectedFeatureModal === 'shift-templates'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center">
                        <i data-lucide="calendar-days" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Modul Template Shift</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Master Template Shift Fleksibel</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Menyediakan fleksibilitas penuh bagi bisnis untuk mendefinisikan template jam kerja standar (Pagi, Siang), 3 shift nonstop 24/7 (Morning, Middle, Night), serta Split Shift jam sibuk.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Kerja 3 Tahap:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Pembuatan Preset:</strong> Super Admin atau Manajer mengatur jam mulai, jam berakhir, dan toleransi keterlambatan.</p>
                        <p><strong class="text-brand-text">2. Alokasi Departemen:</strong> Template ditugaskan ke divisi yang relevan (misal: Split Shift untuk Divisi Dapur &amp; Bar).</p>
                        <p><strong class="text-brand-text">3. Reusable Pattern:</strong> Cukup pilih template untuk mengisi ratusan slot shift secara berulang.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 3: PP 35 Hours -->
            <div x-show="selectedFeatureModal === 'pp35-hours'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Kepatuhan Hukum RI</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Kepatuhan Batas 40 Jam PP 35</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Sistem pemantau kepatuhan ketenagakerjaan yang memastikan jam kerja kumulatif staf tidak melanggar Pasal 21 PP No. 35 Tahun 2021 (maksimal 7 jam/hari untuk 6 hari kerja, atau 8 jam/hari untuk 5 hari kerja, total 40 jam per minggu).
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Proteksi Otomatis:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Live Calculator:</strong> Menghitung total akumulasi jam kerja saat manajer menempatkan staf di jadwal.</p>
                        <p><strong class="text-brand-text">2. Warning Guard:</strong> Menampilkan peringatan visual jika staf telah mencapai 38-40 jam kerja di minggu tersebut.</p>
                        <p><strong class="text-brand-text">3. Hard Lock:</strong> Menolak penambahan shift reguler baru jika melebihi 40 jam tanpa prosedur lembur resmi.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 4: Rest Period -->
            <div x-show="selectedFeatureModal === 'rest-period'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white flex items-center justify-center">
                        <i data-lucide="coffee" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-cyan-700 uppercase tracking-wider">K3 &amp; Kesehatan Kerja</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Proteksi Jeda Istirahat Minimal 11 Jam</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Menjaga keselamatan dan kesehatan staf dengan memblokir jadwal beruntun yang berjarak kurang dari 11 jam. Sangat krusial untuk mencegah kelelahan fatal staf yang bekerja shift malam.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Proteksi Otomatis:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Deteksi Waktu Selesai:</strong> Menghitung jam kepulangan shift malam terakhir karyawan.</p>
                        <p><strong class="text-brand-text">2. Blokir Slot Pagi:</strong> Mengunci slot shift pagi berikutnya yang berjarak di bawah 11 jam dari waktu kepulangan.</p>
                        <p><strong class="text-brand-text">3. Saran Pengganti:</strong> Memberikan daftar nama staf lain yang memiliki jeda istirahat memadai.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 5: Overtime Control -->
            <div x-show="selectedFeatureModal === 'overtime-control'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center">
                        <i data-lucide="timer" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Kompensasi Kerja</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Pengendalian &amp; Kalkulator Lembur</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Membatasi kerja lembur maksimal 4 jam dalam 1 hari dan 18 jam dalam 1 minggu sesuai Pasal 26 PP 35/2021. Menghitung koefisien upah lembur resmi: 1.5x upah per jam di jam pertama dan 2.0x di jam lembur berikutnya.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Kerja 3 Tahap:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Penugasan Lembur:</strong> Manajer membuat tugas lembur dengan batas maksimal 4 jam per hari.</p>
                        <p><strong class="text-brand-text">2. Verifikasi Batas Minggu:</strong> Sistem memverifikasi bahwa total lembur staf belum melebihi 18 jam per minggu.</p>
                        <p><strong class="text-brand-text">3. Pencatatan Timesheet:</strong> Akumulasi jam kerja reguler dan jam lembur otomatis tersimpan rapi pada laporan timesheet bulanan.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 6: Peer Swap -->
            <div x-show="selectedFeatureModal === 'peer-swap'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 text-white flex items-center justify-center">
                        <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-pink-700 uppercase tracking-wider">Fleksibilitas Staf</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Tukar Shift Mandiri (Peer-to-Peer)</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Memberdayakan karyawan untuk saling bertukar jadwal dinas dengan rekan kerja tanpa mengacaukan operasional. Sistem memvalidasi kelayakan jadwal secara otomatis sebelum diteruskan ke manajer.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur 3 Langkah:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Pengajuan:</strong> Staf A memilih shift miliknya dan mengajukan tukar ke Staf B melalui ponsel.</p>
                        <p><strong class="text-brand-text">2. Konfirmasi Rekan:</strong> Staf B menerima notifikasi dan menyetujui pertukaran.</p>
                        <p><strong class="text-brand-text">3. Otorisasi Manajer:</strong> Manajer menerima ringkasan validasi (0 bentrok, jam kerja aman) dan menekan "Setujui".</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 7: Open Shifts -->
            <div x-show="selectedFeatureModal === 'open-shifts'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Marketplace Jadwal</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Bursa Klaim Shift Terbuka</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Solusi cepat saat terjadi kekurangan staf darurat. Manajer dapat melepas shift kosong ke bursa terbuka, dan staf yang memenuhi syarat dapat mengklaimnya secara adil.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Kerja 3 Tahap:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Terbitkan Shift:</strong> Manajer membuat shift dengan label "Open Shift" untuk kuota yang kosong.</p>
                        <p><strong class="text-brand-text">2. Notifikasi Terbuka:</strong> Karyawan yang sedang libur dan memenuhi syarat menerima tawaran shift.</p>
                        <p><strong class="text-brand-text">3. Klaim Mandiri:</strong> Karyawan mengklaim shift; jadwal otomatis berpindah ke kalender pribadinya.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 8: Presensi Digital Mandiri -->
            <div x-show="selectedFeatureModal === 'attendance'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-green-700 text-white flex items-center justify-center">
                        <i data-lucide="fingerprint" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Kehadiran Staf</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Presensi Digital Mandiri</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Pencatatan waktu kehadiran clock-in dan clock-out mandiri secara akurat bagi karyawan yang memiliki jadwal shift aktif hari ini, dilengkapi toleransi keterlambatan (grace period).
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Presensi 3 Langkah:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Validasi Jadwal Shift:</strong> Sistem memeriksa penugasan shift aktif karyawan pada hari ini sebelum mengizinkan proses clock-in.</p>
                        <p><strong class="text-brand-text">2. Toleransi Grace Period:</strong> Jika staf hadir melewati batas toleransi keterlambatan perusahaan (misal: 15 menit), sistem otomatis mencatat status terlambat beserta jumlah menitnya.</p>
                        <p><strong class="text-brand-text">3. Clock-Out &amp; Hitung Jam:</strong> Saat clock-out, total durasi kerja dihitung otomatis dan dipisahkan menjadi jam reguler dan jam lembur.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 9: Timesheet Reports -->
            <div x-show="selectedFeatureModal === 'timesheet-reports'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Pelaporan &amp; Jam Kerja</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Laporan Timesheet Jam Kerja</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Rekapitulasi berkala seluruh aktivitas jam kerja staf per periode bulan dan tahun. Menampilkan agregasi total jam kerja reguler dan jam lembur secara transparan.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Rekap Timesheet:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Agregasi Otomatis:</strong> Data clock-in dan clock-out harian diakumulasikan secara sistematis ke rekap bulanan karyawan.</p>
                        <p><strong class="text-brand-text">2. Pemisahan Jam Reguler &amp; Lembur:</strong> Sistem memisahkan jam reguler dan jam lembur sesuai batasan jam kerja standar.</p>
                        <p><strong class="text-brand-text">3. Arsip Periode Kerja:</strong> Data tersimpan rapi per bulan dan tahun untuk memudahkan peninjauan kinerja dan evaluasi jadwal operasional.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 10: Staff Availability -->
            <div x-show="selectedFeatureModal === 'staff-availability'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center">
                        <i data-lucide="calendar-check-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider">Preferensi Staf</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Ketersediaan Waktu &amp; Libur Staf</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Fitur transparan yang memungkinkan staf menandai hari atau jam di mana mereka tidak dapat hadir (jadwal kuliah, cuti, atau urusan pribadi) agar manajer tidak salah memasang jadwal.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Alur Kerja 3 Tahap:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Input Preferensi:</strong> Karyawan mengisi kalender ketersediaan waktu mereka minimal seminggu sebelumnya.</p>
                        <p><strong class="text-brand-text">2. Visualisasi Roster:</strong> Manajer melihat tanda peringatan jika mencoba menugaskan staf di waktu berhalangan.</p>
                        <p><strong class="text-brand-text">3. Kehadiran Maksimal:</strong> Roster yang sesuai preferensi menekan angka bolos (alpha) hingga 80%.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 11: Multi-Branch -->
            <div x-show="selectedFeatureModal === 'multi-branch'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-600 to-purple-800 text-white flex items-center justify-center">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Tata Kelola Organisasi</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">Multi-Cabang &amp; Departemen (CRUD)</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Struktur hirarki multi-tenant yang mendukung pengelolaan banyak cabang gerai (outlet), departemen operasional, dan pemetaan posisi jabatan staf secara terpusat.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Kapabilitas Utama:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Multi-Outlet:</strong> Tambah, edit, dan atur batasan lisensi per cabang dengan zona waktu berbeda.</p>
                        <p><strong class="text-brand-text">2. CRUD Departemen &amp; Posisi:</strong> Buat struktur jabatan (Barista, Kasir, Head Cook, Supervisor).</p>
                        <p><strong class="text-brand-text">3. Penempatan Staf:</strong> Mutasi atau penugasan staf ke cabang terkait dengan hak akses terisolasi.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Content 12: RBAC Security -->
            <div x-show="selectedFeatureModal === 'rbac-security'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center">
                        <i data-lucide="shield" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Keamanan &amp; Audit</span>
                        <h3 class="font-display font-extrabold text-xl sm:text-2xl text-brand-text">3 Peran RBAC &amp; Digital Audit Trail</h3>
                    </div>
                </div>
                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed">
                    Pengamanan hak akses berbasis peran yang memastikan staf, manajer, dan pemilik bisnis hanya mengakses data sesuai wewenangnya, dilengkapi log audit digital setiap perubahan jadwal.
                </p>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2.5 text-brand-text">
                    <p class="font-bold text-brand-primary">Pilar Keamanan:</p>
                    <div class="space-y-2 text-[#7C7896]">
                        <p><strong class="text-brand-text">1. Role-Based Access:</strong> Antarmuka dan otorisasi terpisah untuk Super Admin, Manager, dan Karyawan.</p>
                        <p><strong class="text-brand-text">2. Audit Trail Terinci:</strong> Setiap persetujuan tukar shift, plotting roster, dan koreksi absen dicatat dalam log.</p>
                        <p><strong class="text-brand-text">3. Perlindungan Privasi:</strong> Enkripsi kredensial dan integritas data tenaga kerja sesuai regulasi.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between gap-3">
                <button 
                    type="button" 
                    @click="selectedFeatureModal = null" 
                    class="px-5 py-2.5 rounded-xl border border-brand-border text-xs font-bold text-brand-text hover:bg-slate-50 transition-colors"
                >
                    Tutup
                </button>
                <a 
                    href="{{ route('login') }}" 
                    class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5"
                >
                    <span>Masuk &amp; Gunakan Fitur Ini</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 5. POPULAR PACKAGES / SEKTOR BISNIS PILIHAN               -->
    <!-- (Specific Business Descriptions & Functional Buttons)     -->
    <!-- ========================================================= -->
    <section id="sektor" class="py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header (Title on left, Functional View All on right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 sm:mb-10 reveal">
                <div>
                    <span class="text-xs font-bold tracking-widest text-brand-primary uppercase">
                        Solusi Industri Spesifik
                    </span>
                    <h2 class="font-display font-bold text-2xl sm:text-3xl text-brand-text mt-1">
                        POPULAR PACKAGES &amp; SEKTOR BISNIS
                    </h2>
                </div>
                
                <!-- Functional View All Modal Trigger Button -->
                <button 
                    type="button" 
                    @click="selectedSectorModal = 'all'"
                    class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-hover group"
                >
                    <span>Lihat Semua Skenario Sektor</span>
                    <div class="w-8 h-8 rounded-full bg-brand-primary/10 flex items-center justify-center group-hover:bg-brand-primary group-hover:text-white transition-colors">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </button>
            </div>

            <!-- 4 Vertical Cards Grid with High-Specific Domain Descriptions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1: Kafe & Resto (F&B) -->
                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden card-elevated card-elevated-hover flex flex-col justify-between reveal delay-100">
                    <div>
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-fnb-ritel.png') }}" 
                                alt="Kafe & Resto F&B" 
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute top-3 left-3 bg-brand-primary text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md">
                                F&amp;B LIFESTYLE
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-display font-bold text-lg text-brand-text leading-snug">
                                Kafe &amp; Resto Dinamis
                            </h3>
                            <!-- Specific Business Description -->
                            <p class="text-xs text-[#7C7896] mt-2.5 leading-relaxed">
                                Mengatur rotasi Barista, Kasir, &amp; Cook saat jam sibuk (rush hours pagi &amp; petang). Mendukung split shift, rotasi closing-to-opening dengan jeda istirahat wajib &ge; 11 jam, serta tukar shift kilat jika ada barista berhalangan hadir mendadak.
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Rush Hour Peak</span>
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Split Shift</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 pt-0 border-t border-brand-border/60 mt-3 flex items-center justify-between">
                        <div>
                            <p class="font-display font-extrabold text-xl text-brand-primary">
                                99.8% <span class="text-xs font-normal text-[#7C7896]">On-Time</span>
                            </p>
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold mt-0.5">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                                <span>4.9</span>
                            </div>
                        </div>
                        <!-- Functional Arrow Button Opening Detail Modal -->
                        <button 
                            type="button" 
                            @click="selectedSectorModal = 'fnb'" 
                            class="w-10 h-10 rounded-full bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white flex items-center justify-center transition-colors shadow-sm" 
                            aria-label="Buka Detail Skenario Kafe"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 2: Supermarket & Gerai Ritel Modern -->
                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden card-elevated card-elevated-hover flex flex-col justify-between reveal delay-200">
                    <div>
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-ritel-minimarket.jpg') }}" 
                                alt="Supermarket & Gerai Ritel" 
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute top-3 left-3 bg-brand-primary text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md">
                                RITEL MODERN
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-display font-bold text-lg text-brand-text leading-snug">
                                Supermarket &amp; Ritel
                            </h3>
                            <!-- Specific Business Description -->
                            <p class="text-xs text-[#7C7896] mt-2.5 leading-relaxed">
                                Pembagian 2 shift operasional kasir, pramusaji, &amp; inventory stocker tanpa celah kekosongan saat lonjakan akhir pekan. Memastikan proses handover laci kasir tercatat rapi tanpa memotong hak jeda istirahat staf.
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Handover Kasir</span>
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Weekend Shift</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 pt-0 border-t border-brand-border/60 mt-3 flex items-center justify-between">
                        <div>
                            <p class="font-display font-extrabold text-xl text-brand-primary">
                                0 Bentrok <span class="text-xs font-normal text-[#7C7896]">Kasir</span>
                            </p>
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold mt-0.5">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                                <span>4.8</span>
                            </div>
                        </div>
                        <!-- Functional Arrow Button Opening Detail Modal -->
                        <button 
                            type="button" 
                            @click="selectedSectorModal = 'ritel'" 
                            class="w-10 h-10 rounded-full bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white flex items-center justify-center transition-colors shadow-sm" 
                            aria-label="Buka Detail Skenario Ritel"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 3: Hotel & Resor 24/7 (Hospitality) -->
                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden card-elevated card-elevated-hover flex flex-col justify-between reveal delay-300">
                    <div>
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-perhotelan-pariwisata.png') }}" 
                                alt="Perhotelan & Pariwisata" 
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute top-3 left-3 bg-brand-primary text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md">
                                HOSPITALITY
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-display font-bold text-lg text-brand-text leading-snug">
                                Hotel &amp; Resor 24/7
                            </h3>
                            <!-- Specific Business Description -->
                            <p class="text-xs text-[#7C7896] mt-2.5 leading-relaxed">
                                Penjadwalan 24 jam nonstop untuk Front Desk, Housekeeping, &amp; Security. Sistem secara otomatis memblokir staf shift malam (night audit) agar tidak langsung terjadwal shift pagi esoknya demi kepatuhan keselamatan kerja.
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">24/7 Night Audit</span>
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Jeda 11 Jam Wajib</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 pt-0 border-t border-brand-border/60 mt-3 flex items-center justify-between">
                        <div>
                            <p class="font-display font-extrabold text-xl text-brand-primary">
                                Jeda 11 Jam <span class="text-xs font-normal text-[#7C7896]">Pasti</span>
                            </p>
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold mt-0.5">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                                <span>4.9</span>
                            </div>
                        </div>
                        <!-- Functional Arrow Button Opening Detail Modal -->
                        <button 
                            type="button" 
                            @click="selectedSectorModal = 'hotel'" 
                            class="w-10 h-10 rounded-full bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white flex items-center justify-center transition-colors shadow-sm" 
                            aria-label="Buka Detail Skenario Hotel"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 4: Fasilitas Kesehatan & Medis -->
                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden card-elevated card-elevated-hover flex flex-col justify-between reveal delay-400">
                    <div>
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-gray-100">
                            <img 
                                src="{{ asset('images/landing/sektor-klinik-medis.jpg') }}" 
                                alt="Klinik & Fasilitas Medis" 
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute top-3 left-3 bg-brand-primary text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md">
                                MEDIS &amp; FASKES
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-display font-bold text-lg text-brand-text leading-snug">
                                Klinik &amp; Tenaga Medis
                            </h3>
                            <!-- Specific Business Description -->
                            <p class="text-xs text-[#7C7896] mt-2.5 leading-relaxed">
                                Rotasi jaga 3 shift tenaga perawat, bidan, &amp; administrasi faskes. Menjamin kuota nakes di unit gawat darurat dan poli rawat jalan selalu terpenuhi di setiap detik tanpa melanggar batas 40 jam kerja per minggu.
                            </p>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Kuota Triase UGD</span>
                                <span class="text-[10px] bg-brand-primary/10 text-brand-primary font-semibold px-2 py-0.5 rounded">Maks 40 Jam/Mgg</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 pt-0 border-t border-brand-border/60 mt-3 flex items-center justify-between">
                        <div>
                            <p class="font-display font-extrabold text-xl text-brand-primary">
                                100% Kuota <span class="text-xs font-normal text-[#7C7896]">Siaga</span>
                            </p>
                            <div class="flex items-center gap-1 text-amber-500 text-xs font-bold mt-0.5">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                                <span>5.0</span>
                            </div>
                        </div>
                        <!-- Functional Arrow Button Opening Detail Modal -->
                        <button 
                            type="button" 
                            @click="selectedSectorModal = 'medis'" 
                            class="w-10 h-10 rounded-full bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white flex items-center justify-center transition-colors shadow-sm" 
                            aria-label="Buka Detail Skenario Medis"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Modal Detail Sektor (Interactive Popup) -->
    <div 
        x-show="selectedSectorModal" 
        x-cloak 
        @keydown.escape.window="selectedSectorModal = null"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-brand-text/50 backdrop-blur-sm"
    >
        <div 
            @click.outside="selectedSectorModal = null"
            class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-brand-border relative"
        >
            <button 
                type="button" 
                @click="selectedSectorModal = null" 
                class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-brand-text flex items-center justify-center"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Content for F&B -->
            <div x-show="selectedSectorModal === 'fnb' || selectedSectorModal === 'all'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold">☕</span>
                    <div>
                        <h3 class="font-display font-extrabold text-xl text-brand-text">Solusi Sektor Kafe &amp; Resto F&amp;B</h3>
                        <p class="text-xs text-[#7C7896]">Tantangan: Lonjakan jam sibuk (rush hour) &amp; kelelahan barista.</p>
                    </div>
                </div>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2 text-brand-text">
                    <p class="font-bold text-brand-primary">Fitur Unggulan untuk Kafe:</p>
                    <ul class="list-disc list-inside space-y-1 text-[#7C7896]">
                        <li>Pola <span class="font-semibold text-brand-text">Split Shift</span> otomatis untuk cover jam 08:00-11:00 dan 17:00-21:00.</li>
                        <li>Proteksi jam istirahat: Barista closing jam 23:00 tidak bisa dipasangi shift opening jam 07:00 esoknya (wajib jeda 11 jam).</li>
                        <li>Tukar shift mandiri real-time antar staf via smartphone dengan persetujuan manajer toko.</li>
                    </ul>
                </div>
            </div>

            <!-- Content for Retail -->
            <div x-show="selectedSectorModal === 'ritel'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold">🛒</span>
                    <div>
                        <h3 class="font-display font-extrabold text-xl text-brand-text">Solusi Sektor Minimarket &amp; Ritel Modern</h3>
                        <p class="text-xs text-[#7C7896]">Tantangan: Selisih pergantian kasir (cash handover) &amp; trafik belanja akhir pekan.</p>
                    </div>
                </div>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2 text-brand-text">
                    <p class="font-bold text-brand-primary">Fitur Unggulan untuk Ritel:</p>
                    <ul class="list-disc list-inside space-y-1 text-[#7C7896]">
                        <li>Penjadwalan 2 shift terstruktur dengan overlapping 30 menit khusus rekonsiliasi kasir.</li>
                        <li>Penguncian batas lembur toko agar tidak melebihi batas regulasi 4 jam/hari.</li>
                        <li>Distribusi adil staf pramusaji saat promosi tanggal kembar / weekend rush.</li>
                    </ul>
                </div>
            </div>

            <!-- Content for Hotel -->
            <div x-show="selectedSectorModal === 'hotel'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold">🏨</span>
                    <div>
                        <h3 class="font-display font-extrabold text-xl text-brand-text">Solusi Sektor Hotel &amp; Resor 24/7</h3>
                        <p class="text-xs text-[#7C7896]">Tantangan: Operasional tanpa henti 24 jam &amp; shift malam (night audit).</p>
                    </div>
                </div>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2 text-brand-text">
                    <p class="font-bold text-brand-primary">Fitur Unggulan untuk Perhotelan:</p>
                    <ul class="list-disc list-inside space-y-1 text-[#7C7896]">
                        <li>Rotasi 3 shift penuh (Morning, Middle, Night Audit) dengan plotting presisi.</li>
                        <li>Sistem otomatis menolak penugasan berturut-turut tanpa hari libur mingguan.</li>
                        <li>Integrasi presensi multi-departemen (Front Office, Housekeeping, F&amp;B Service, Engineering).</li>
                    </ul>
                </div>
            </div>

            <!-- Content for Medical -->
            <div x-show="selectedSectorModal === 'medis'" class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold">🏥</span>
                    <div>
                        <h3 class="font-display font-extrabold text-xl text-brand-text">Solusi Sektor Klinik &amp; Tenaga Medis</h3>
                        <p class="text-xs text-[#7C7896]">Tantangan: Kuota jaga nakes gawat darurat &amp; keselamatan pasien.</p>
                    </div>
                </div>
                <div class="bg-brand-bg/60 p-4 rounded-2xl text-xs space-y-2 text-brand-text">
                    <p class="font-bold text-brand-primary">Fitur Unggulan untuk Klinik &amp; Faskes:</p>
                    <ul class="list-disc list-inside space-y-1 text-[#7C7896]">
                        <li>Validasi kuota minimal dokter jaga dan perawat di setiap ruang periksa.</li>
                        <li>Pencatatan jam kerja terperinci untuk audit akreditasi layanan faskes.</li>
                        <li>Klaim open shift transparan bagi nakes pengganti saat jadwal darurat.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-brand-border flex justify-end gap-3">
                <button type="button" @click="selectedSectorModal = null" class="px-5 py-2.5 rounded-xl border border-brand-border text-xs font-bold text-brand-text hover:bg-slate-50">
                    Tutup
                </button>
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold transition-colors shadow-sm">
                    Mulai Terapkan di Sektor Ini
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 6. PRICING DETAILS SECTION                                -->
    <!-- (Consistent Non-Stuck Button Styling on All 4 Cards)      -->
    <!-- ========================================================= -->
    <section id="pricing" class="py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Rich Brand Gradient Container with Origami Plane Accent -->
            <div class="relative rounded-[2.5rem] bg-gradient-to-r from-brand-primary via-[#5A45E2] to-[#4F46E5] p-6 sm:p-10 lg:p-12 shadow-2xl overflow-hidden reveal">
                
                <!-- Background Geometric Paper Plane Artwork on the Left -->
                <div class="absolute -left-12 bottom-6 w-80 h-80 opacity-20 pointer-events-none transform -rotate-12">
                    <svg viewBox="0 0 320 320" class="w-full h-full" fill="none">
                        <polygon points="160,30 20,240 160,200" fill="#FFFFFF" />
                        <polygon points="160,30 300,240 160,200" fill="#DDD6FE" />
                        <polygon points="160,200 160,270 140,220" fill="#FFFFFF" />
                    </svg>
                </div>

                <!-- Header Inside Container -->
                <div class="relative z-10 mb-10 text-white">
                    <span class="text-xs font-bold tracking-widest uppercase opacity-80">
                        Skema Investasi Platform
                    </span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight mt-1">
                        PRICING DETAILS &amp; PAKET LISENSI
                    </h2>
                    <p class="text-sm sm:text-base text-purple-100 mt-2 max-w-xl">
                        Pilih paket fleksibel yang disesuaikan dengan skala tim, jumlah outlet, dan kebutuhan audit regulasi tenaga kerja Anda.
                    </p>
                </div>

                <!-- 4 Tiers Pricing Cards Grid (All buttons consistently styled and non-stuck) -->
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                    
                    <!-- Tier 1: Economy / Starter -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg flex flex-col justify-between card-elevated">
                        <div>
                            <span class="text-xs font-bold text-[#7C7896] uppercase tracking-wider block">
                                UMKM Pemula
                            </span>
                            <h3 class="font-display font-bold text-2xl text-brand-text mt-1">
                                Economy
                            </h3>
                            <p class="text-xs text-[#7C7896] mt-1 leading-relaxed">
                                Cocok untuk gerai kecil &amp; kafe perintis.
                            </p>

                            <div class="mt-6 pb-6 border-b border-brand-border">
                                <span class="font-display font-extrabold text-3xl sm:text-4xl text-brand-primary">
                                    Rp 0
                                </span>
                                <span class="text-xs font-medium text-[#7C7896]">/ Selamanya</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-brand-text">
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Hingga 10 Anggota Staf</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Penyusun Roster Mingguan</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Deteksi Bentrok Jam Kerja</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Akses Web Karyawan</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <button 
                                type="button" 
                                @click="selectedPlanModal = { name: 'Economy', price: 'Rp 0', desc: 'Cocok untuk gerai kecil & kafe rintisan hingga 10 staf.' }"
                                class="w-full inline-flex items-center justify-center py-3 rounded-2xl bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition-colors border border-brand-border/80 shadow-sm"
                            >
                                Pilih Paket Economy
                            </button>
                        </div>
                    </div>

                    <!-- Tier 2: Premium (Most Popular - Consistent Clean Button) -->
                    <div class="relative bg-white rounded-3xl p-6 sm:p-7 shadow-2xl flex flex-col justify-between border-2 border-brand-primary transform lg:-translate-y-2 card-elevated">
                        <!-- Most Popular Badge -->
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-brand-primary text-white text-[10px] font-extrabold uppercase px-4 py-1 rounded-full shadow-md">
                            Most Popular
                        </div>

                        <div>
                            <span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mt-1">
                                Bisnis Tumbuh
                            </span>
                            <h3 class="font-display font-bold text-2xl text-brand-text mt-1">
                                Premium
                            </h3>
                            <p class="text-xs text-[#7C7896] mt-1 leading-relaxed">
                                Efisiensi tinggi untuk resto &amp; ritel aktif.
                            </p>

                            <div class="mt-6 pb-6 border-b border-brand-border">
                                <span class="font-display font-extrabold text-3xl sm:text-4xl text-brand-primary">
                                    Rp 149K
                                </span>
                                <span class="text-xs font-medium text-[#7C7896]">/ Bulan</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-brand-text">
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span class="font-semibold">Hingga 35 Anggota Staf</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Generator AI Roster Instan</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Audit Kepatuhan PP 35/2021</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Tukar Shift Mandiri (Approval)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Notifikasi Presensi Masuk</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <button 
                                type="button" 
                                @click="selectedPlanModal = { name: 'Premium (Paling Populer)', price: 'Rp 149.000 / Bulan', desc: 'Solusi terlengkap untuk gerai F&B dan toko ritel aktif hingga 35 staf.' }"
                                class="w-full inline-flex items-center justify-center py-3 rounded-2xl bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition-colors border border-brand-primary/40 shadow-sm"
                            >
                                Pilih Paket Premium
                            </button>
                        </div>
                    </div>

                    <!-- Tier 3: Business -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg flex flex-col justify-between card-elevated">
                        <div>
                            <span class="text-xs font-bold text-[#7C7896] uppercase tracking-wider block">
                                Multi-Outlet
                            </span>
                            <h3 class="font-display font-bold text-2xl text-brand-text mt-1">
                                Business
                            </h3>
                            <p class="text-xs text-[#7C7896] mt-1 leading-relaxed">
                                Fleksibilitas skala jaringan cabang.
                            </p>

                            <div class="mt-6 pb-6 border-b border-brand-border">
                                <span class="font-display font-extrabold text-3xl sm:text-4xl text-brand-primary">
                                    Rp 399K
                                </span>
                                <span class="text-xs font-medium text-[#7C7896]">/ Bulan</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-brand-text">
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Hingga 100 Anggota Staf</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Manajemen Multi-Cabang Outlet</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Peran Bertingkat (Admin &amp; Mgr)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Ekspor Timesheet Excel/PDF</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <button 
                                type="button" 
                                @click="selectedPlanModal = { name: 'Business', price: 'Rp 399.000 / Bulan', desc: 'Sempurna untuk multi-outlet dan jaringan waralaba hingga 100 staf.' }"
                                class="w-full inline-flex items-center justify-center py-3 rounded-2xl bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition-colors border border-brand-border/80 shadow-sm"
                            >
                                Pilih Paket Business
                            </button>
                        </div>
                    </div>

                    <!-- Tier 4: Luxury / Enterprise -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg flex flex-col justify-between card-elevated">
                        <div>
                            <span class="text-xs font-bold text-[#7C7896] uppercase tracking-wider block">
                                Korporasi Besar
                            </span>
                            <h3 class="font-display font-bold text-2xl text-brand-text mt-1">
                                Luxury / Corp
                            </h3>
                            <p class="text-xs text-[#7C7896] mt-1 leading-relaxed">
                                Solusi terdedikasi dengan integrasi SLA.
                            </p>

                            <div class="mt-6 pb-6 border-b border-brand-border">
                                <span class="font-display font-extrabold text-3xl sm:text-4xl text-brand-primary">
                                    Rp 899K
                                </span>
                                <span class="text-xs font-medium text-[#7C7896]">/ Bulan</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-brand-text">
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Staf Tak Terbatas (Unlimited)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Kustomisasi Aturan Jam Kerja</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Laporan Komprehensif Multi-Cabang</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                    <span>Prioritas Support 24/7 Dedikasi</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <button 
                                type="button" 
                                @click="selectedPlanModal = { name: 'Luxury / Corp', price: 'Rp 899.000 / Bulan', desc: 'Solusi kustom operasional korporasi, karyawan tanpa batas, dan laporan komprehensif seluruh cabang outlet.' }"
                                class="w-full inline-flex items-center justify-center py-3 rounded-2xl bg-brand-bg hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition-colors border border-brand-border/80 shadow-sm"
                            >
                                Hubungi Lisensi Corp
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Modal Konfirmasi Paket Lisensi -->
    <div 
        x-show="selectedPlanModal" 
        x-cloak 
        @keydown.escape.window="selectedPlanModal = null"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-brand-text/50 backdrop-blur-sm"
    >
        <div 
            @click.outside="selectedPlanModal = null"
            class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-brand-border text-center"
        >
            <div class="w-14 h-14 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check-circle-2" class="w-7 h-7"></i>
            </div>
            <h3 class="font-display font-extrabold text-xl text-brand-text" x-text="'Paket ' + (selectedPlanModal?.name || '')"></h3>
            <p class="font-display font-bold text-2xl text-brand-primary mt-1" x-text="selectedPlanModal?.price || ''"></p>
            <p class="text-xs text-[#7C7896] mt-2 leading-relaxed" x-text="selectedPlanModal?.desc || ''"></p>

            <div class="mt-6 flex flex-col gap-2.5">
                <a href="{{ route('login') }}" class="w-full py-3 rounded-2xl bg-brand-primary hover:bg-brand-hover text-white font-bold text-xs transition-colors shadow-md">
                    Lanjutkan ke Pendaftaran / Workspace
                </a>
                <button type="button" @click="selectedPlanModal = null" class="w-full py-2.5 rounded-2xl border border-brand-border text-xs font-semibold text-[#7C7896] hover:bg-slate-50">
                    Pilih Paket Lain
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 7. HOW TO BOOK / CARA KERJA SISTEM (4 Connected Steps)   -->
    <!-- ========================================================= -->
    <section id="cara-kerja" class="py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header Centered with Accent Bar -->
            <div class="text-center max-w-2xl mx-auto mb-14 reveal">
                <h2 class="font-display font-extrabold text-2xl sm:text-3xl text-brand-text tracking-tight uppercase">
                    HOW TO ROSTER &bull; CARA KERJA JADWALIN
                </h2>
                <div class="w-16 h-1 bg-brand-primary mx-auto mt-3 rounded-full"></div>
                <p class="text-sm text-[#7C7896] mt-3">
                    Empat langkah mudah bertransformasi dari lembar kerja manual menuju ekosistem penjadwalan otomatis berstandar hukum.
                </p>
            </div>

            <!-- 4 Connected Steps Horizontal Bar with 3D Embossed Icons & Dashed Lines -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative items-start">
                
                <!-- Step 1 -->
                <div class="flex flex-col items-center text-center group reveal delay-100">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-brand-border flex items-center justify-center text-brand-primary card-elevated card-elevated-hover mb-5 relative">
                        <i data-lucide="map-pin" class="w-9 h-9"></i>
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-primary text-white text-xs font-bold flex items-center justify-center shadow-md">
                            1
                        </span>
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        1. Atur Tim &amp; Posisi
                    </h3>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed max-w-xs">
                        Masukkan daftar staf, departemen, dan aturan jam kerja per cabang.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col items-center text-center group reveal delay-200">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-brand-border flex items-center justify-center text-brand-primary card-elevated card-elevated-hover mb-5 relative">
                        <i data-lucide="layers" class="w-9 h-9"></i>
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-primary text-white text-xs font-bold flex items-center justify-center shadow-md">
                            2
                        </span>
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        2. Generate Roster
                    </h3>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed max-w-xs">
                        Sistem menyusun jadwal secara otomatis tanpa ada bentrok jam shift.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col items-center text-center group reveal delay-300">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-brand-border flex items-center justify-center text-brand-primary card-elevated card-elevated-hover mb-5 relative">
                        <i data-lucide="send" class="w-9 h-9 transform rotate-45"></i>
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-primary text-white text-xs font-bold flex items-center justify-center shadow-md">
                            3
                        </span>
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        3. Publikasi ke Staf
                    </h3>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed max-w-xs">
                        Jadwal terbit langsung ke smartphone staf beserta opsi tukar shift resmi.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="flex flex-col items-center text-center group reveal delay-400">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-brand-border flex items-center justify-center text-brand-primary card-elevated card-elevated-hover mb-5 relative">
                        <i data-lucide="badge-check" class="w-9 h-9"></i>
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-primary text-white text-xs font-bold flex items-center justify-center shadow-md">
                            4
                        </span>
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        4. Pantau &amp; Patuhi Aturan
                    </h3>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed max-w-xs">
                        Presensi &amp; batas lembur terpantau real-time sesuai PP No. 35/2021.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 8. STATS & TESTIMONIAL RIBBON (Reference Split Banner)   -->
    <!-- ========================================================= -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-r from-brand-primary via-[#5B48E0] to-[#4F46E5] text-white overflow-hidden shadow-2xl reveal">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                    
                    <!-- Left: 4 Stats Counters -->
                    <div class="lg:col-span-6 p-8 sm:p-12 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center border-b lg:border-b-0 lg:border-r border-white/20">
                        <div>
                            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                            <p class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight">150+</p>
                            <p class="text-xs text-purple-200 mt-1 font-medium">Cabang Aktif</p>
                        </div>

                        <div>
                            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <p class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight">10K+</p>
                            <p class="text-xs text-purple-200 mt-1 font-medium">Jam Shift Roster</p>
                        </div>

                        <div>
                            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="calendar-check" class="w-5 h-5"></i>
                            </div>
                            <p class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight">500+</p>
                            <p class="text-xs text-purple-200 mt-1 font-medium">Roster Terbit</p>
                        </div>

                        <div>
                            <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <p class="font-display font-extrabold text-3xl sm:text-4xl tracking-tight">24/7</p>
                            <p class="text-xs text-purple-200 mt-1 font-medium">Sistem Siaga</p>
                        </div>
                    </div>

                    <!-- Right: Testimonial Card with Origami Heart & 5 Stars -->
                    <div class="lg:col-span-6 p-8 sm:p-12 relative flex items-center justify-between gap-6">
                        <div class="relative z-10">
                            <i data-lucide="quote" class="w-8 h-8 text-purple-300 opacity-60 mb-2"></i>
                            <blockquote class="text-sm sm:text-base font-medium leading-relaxed text-purple-50">
                                &ldquo;Sebelumnya kami butuh waktu seharian untuk mencocokkan jadwal kasir dan barista. Sejak pakai Jadwalin, roster mingguan selesai 5 menit tanpa ada bentrok jam lagi!&rdquo;
                            </blockquote>
                            <div class="mt-4 flex items-center gap-3">
                                <div>
                                    <p class="font-bold text-sm text-white">Rani Aditya</p>
                                    <p class="text-xs text-purple-200">Manajer Operasional, Kopi Nusantara Malang</p>
                                </div>
                                <div class="flex items-center gap-0.5 text-amber-300 ml-auto">
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-300"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-300"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-300"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-300"></i>
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-300"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 3D Origami Heart Graphic on the Right Edge -->
                        <div class="hidden sm:block w-24 h-24 shrink-0 opacity-80">
                            <svg viewBox="0 0 100 100" class="w-full h-full drop-shadow-md" fill="none">
                                <polygon points="50,25 20,5 5,30 50,85" fill="#DDD6FE" />
                                <polygon points="50,25 80,5 95,30 50,85" fill="#EDE9FE" />
                                <polygon points="50,25 35,50 50,85" fill="#C4B5FD" />
                                <polygon points="50,25 65,50 50,85" fill="#FFFFFF" />
                            </svg>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 9. DETAIL REGULASI PP NO. 35 TAHUN 2021                   -->
    <!-- ========================================================= -->
    <section id="regulasi" class="py-16 sm:py-20 bg-white border-y border-brand-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 reveal">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-xs font-bold text-brand-primary uppercase tracking-wider mb-3">
                    Landasan Hukum Ketenagakerjaan
                </span>
                <h2 class="font-display font-extrabold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                    Kepatuhan Regulasi PP No. 35 Tahun 2021
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                    Sistem Jadwalin dirancang secara khusus untuk membantu perusahaan mematuhi regulasi ketenagakerjaan Republik Indonesia secara otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Regulasi 1: Waktu Kerja -->
                <div class="bg-brand-bg/50 rounded-3xl border border-brand-border p-8 card-elevated card-elevated-hover flex flex-col justify-between reveal delay-100">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-display font-bold text-xl text-brand-text mb-3">
                            Batas Waktu Kerja Maksimal
                        </h3>
                        <p class="text-sm text-[#7C7896] leading-relaxed">
                            Memonitor batas 7 jam/hari (untuk 6 hari kerja) atau 8 jam/hari (untuk 5 hari kerja) dengan akumulasi maksimal 40 jam dalam satu minggu kalender.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-brand-border text-xs font-bold text-brand-primary flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Otomatis Terkunci di Sistem</span>
                    </div>
                </div>

                <!-- Regulasi 2: Istirahat Antar Shift -->
                <div class="bg-brand-bg/50 rounded-3xl border border-brand-border p-8 card-elevated card-elevated-hover flex flex-col justify-between reveal delay-200">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="coffee" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-display font-bold text-xl text-brand-text mb-3">
                            Jeda Istirahat Minimal 11 Jam
                        </h3>
                        <p class="text-sm text-[#7C7896] leading-relaxed">
                            Mencegah staf yang selesai bertugas shift malam langsung dijadwalkan shift pagi keesokan harinya, melindungi kesehatan dan keselamatan kerja.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-brand-border text-xs font-bold text-brand-primary flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Peringatan Dini Sistem</span>
                    </div>
                </div>

                <!-- Regulasi 3: Pengendalian Lembur -->
                <div class="bg-brand-bg/50 rounded-3xl border border-brand-border p-8 card-elevated card-elevated-hover flex flex-col justify-between reveal delay-300">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center mb-6">
                            <i data-lucide="alert-circle" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-display font-bold text-xl text-brand-text mb-3">
                            Batas Lembur 4 Jam/Hari
                        </h3>
                        <p class="text-sm text-[#7C7896] leading-relaxed">
                            Membatasi kelebihan waktu kerja maksimal 4 jam dalam 1 hari dan 18 jam dalam 1 minggu, lengkap dengan rekaman digital persetujuan lembur.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-brand-border text-xs font-bold text-brand-primary flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Rekapitulasi Otomatis</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 10. TIM PENGEMBANG & AKADEMIK POLINEMA                    -->
    <!-- ========================================================= -->
    <section id="tim" class="py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 reveal">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-xs font-bold text-brand-primary uppercase tracking-wider mb-3">
                    Project Based Learning
                </span>
                <h2 class="font-display font-extrabold text-2xl sm:text-3xl lg:text-4xl text-brand-text tracking-tight">
                    Tim Pengembang Jadwalin
                </h2>
                <p class="mt-3 text-sm sm:text-base text-[#7C7896] leading-relaxed">
                    Dikembangkan dengan dedikasi tinggi oleh mahasiswa Jurusan Teknologi Informasi, Politeknik Negeri Malang.
                </p>
            </div>

            <!-- 4 Developer Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Dev 1: Farras -->
                <div class="bg-white rounded-3xl border border-brand-border p-6 text-center card-elevated card-elevated-hover flex flex-col items-center reveal delay-100">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] text-brand-primary font-display font-extrabold text-xl flex items-center justify-center mb-4 shadow-inner">
                        FA
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        Muhammad Farras A. A.
                    </h3>
                    <p class="text-xs text-brand-primary font-semibold mt-1">
                        Project Manager &amp; Backend Dev
                    </p>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed">
                        Arsitektur sistem, algoritma penjadwalan &amp; integrasi API database.
                    </p>
                </div>

                <!-- Dev 2: Yusuf -->
                <div class="bg-white rounded-3xl border border-brand-border p-6 text-center card-elevated card-elevated-hover flex flex-col items-center reveal delay-200">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] text-brand-primary font-display font-extrabold text-xl flex items-center justify-center mb-4 shadow-inner">
                        MY
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        Muhammad Yusuf
                    </h3>
                    <p class="text-xs text-brand-primary font-semibold mt-1">
                        Database Designer &amp; Analyst
                    </p>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed">
                        Struktur basis data multi-tenant, optimasi query &amp; relasi model.
                    </p>
                </div>

                <!-- Dev 3: Neyza -->
                <div class="bg-white rounded-3xl border border-brand-border p-6 text-center card-elevated card-elevated-hover flex flex-col items-center reveal delay-300">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] text-brand-primary font-display font-extrabold text-xl flex items-center justify-center mb-4 shadow-inner">
                        NA
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        Neyza Ratu Anastasya
                    </h3>
                    <p class="text-xs text-brand-primary font-semibold mt-1">
                        UI/UX Designer &amp; Tech Writer
                    </p>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed">
                        Perancangan antarmuka visual, pengalaman pengguna &amp; dokumen teknis.
                    </p>
                </div>

                <!-- Dev 4: Umi -->
                <div class="bg-white rounded-3xl border border-brand-border p-6 text-center card-elevated card-elevated-hover flex flex-col items-center reveal delay-400">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#EDE9FE] to-[#DDD6FE] text-brand-primary font-display font-extrabold text-xl flex items-center justify-center mb-4 shadow-inner">
                        UM
                    </div>
                    <h3 class="font-display font-bold text-base text-brand-text">
                        Umi Maharani
                    </h3>
                    <p class="text-xs text-brand-primary font-semibold mt-1">
                        Full-Stack &amp; System Analyst
                    </p>
                    <p class="text-xs text-[#7C7896] mt-2 leading-relaxed">
                        Implementasi modul presensi, validasi form &amp; pengujian sistem.
                    </p>
                </div>

            </div>

            <!-- Polinema Badge Banner -->
            <div class="mt-10 rounded-2xl border border-brand-primary/20 bg-brand-primary/10 p-5 text-center reveal">
                <p class="font-display font-bold text-brand-primary text-base">
                    Politeknik Negeri Malang &bull; Jurusan Teknologi Informasi
                </p>
                <p class="text-xs text-brand-primary/80 mt-1">
                    Program Studi D-IV Teknik Informatika / Sistem Informasi Bisnis
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- 11. FOOTER                                                -->
    <!-- ========================================================= -->
    <footer class="bg-white border-t border-brand-border pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-brand-border">
                
                <!-- Kolom 1: Brand Info -->
                <div class="md:col-span-5 flex flex-col items-start">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <x-application-logo class="h-8 w-auto text-brand-primary" />
                        <span class="font-display text-2xl font-bold tracking-tight">
                            <span class="text-brand-text">Jadwal</span><span class="text-brand-primary">in</span>
                        </span>
                    </a>
                    <p class="mt-4 text-sm text-[#7C7896] leading-relaxed max-w-sm">
                        Platform otomasi penyusunan roster shift kerja B2B berstandar PP No. 35 Tahun 2021. Efisien, adil, dan tanpa bentrok jam kerja.
                    </p>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div class="md:col-span-4">
                    <p class="font-display font-bold text-sm text-brand-text uppercase tracking-wider mb-4">
                        Navigasi Cepat
                    </p>
                    <ul class="space-y-2.5 text-sm text-[#7C7896]">
                        <li><a href="#fitur" class="hover:text-brand-primary transition-colors">Semua Fitur Sistem (12 Modul)</a></li>
                        <li><a href="#simulator" class="hover:text-brand-primary transition-colors">Simulasi Shift Otomatis</a></li>
                        <li><a href="#sektor" class="hover:text-brand-primary transition-colors">Sektor Kafe, Ritel, &amp; Medis</a></li>
                        <li><a href="#pricing" class="hover:text-brand-primary transition-colors">Paket Lisensi &amp; Harga</a></li>
                        <li><a href="#regulasi" class="hover:text-brand-primary transition-colors">Kepatuhan PP 35/2021</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Autentikasi & Masuk -->
                <div class="md:col-span-3">
                    <p class="font-display font-bold text-sm text-brand-text uppercase tracking-wider mb-4">
                        Akses Platform
                    </p>
                    <p class="text-xs text-[#7C7896] mb-4">
                        Masuk ke akun manajer, staf, atau super admin perusahaan Anda.
                    </p>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 w-full py-3 rounded-2xl bg-brand-primary hover:bg-brand-hover text-white text-xs font-bold transition-all shadow-md shadow-brand-primary/20">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Workspace</span>
                    </a>
                </div>

            </div>

            <!-- Bottom Copyright & Compliance Badges -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#7C7896]">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary font-semibold">PP No. 35/2021 Ready</span>
                    <span class="px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary font-semibold">Multi-Tenant Architecture</span>
                    <span class="px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary font-semibold">Mobile-First UI</span>
                </div>
                <p>&copy; 2026 Jadwalin - Smart Shift Platform. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Lucide Icons & Scroll Animation Observer Script -->
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Scroll Reveal Observer
            const revealElements = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -40px 0px'
            });

            revealElements.forEach(el => revealObserver.observe(el));

            // Scroll Spy for Nav Highlighting
            const sections = document.querySelectorAll('section[id], div[id="simulator"]');
            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 140;
                    if (window.scrollY >= sectionTop) {
                        current = section.getAttribute('id');
                    }
                });
                if (current && window.Alpine) {
                    const bodyEl = document.querySelector('body');
                    if (bodyEl && bodyEl._x_dataStack && bodyEl._x_dataStack[0]) {
                        bodyEl._x_dataStack[0].activeNav = current;
                    }
                }
            }, { passive: true });
        });
    </script>
</body>
</html>
