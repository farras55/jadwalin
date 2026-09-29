<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'JadwalIn Dashboard') }}</title>
    
    {{-- Tailwind CSS dari Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

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

@php
    // ====== DATA DINAMIS USER ======
    // Jika user sudah login, gunakan data asli dari database.
    // Jika belum (guest/test), gunakan data fallback.
    $authUser = auth()->user();
    $userName   = optional($authUser)->name ?? 'Guest';
    $userRole   = optional($authUser)->role ?? '-';
    $userBranch = optional($authUser)->branch ?? null;

    // Generate Inisial dari nama user (contoh: "Budi Santoso" -> "BS")
    $initials = mb_strtoupper(
        \Illuminate\Support\Str::of($userName)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->join('')
    );

    // Tanggal server saat ini (WIB) — hanya untuk nilai awal sebelum JS mengambil alih
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulanPendek = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sept','Okt','Nov','Des'];
    $tanggalStr = $hari[$now->dayOfWeek] . ', ' . $now->day . ' ' . $bulanPendek[$now->month - 1] . ' ' . $now->year;

    // ====== DAFTAR CABANG (Outlet) ======
    // Developer: Ganti dengan query database jika cabang bersifat dinamis dari DB.
    $branchList = [
        'Senopati & Kemang (2 Cabang)',
        'Outlet Senopati',
        'Outlet Kemang',
        'Outlet Sudirman',
        'Outlet Kelapa Gading',
    ];
    // Branch default: dari data user jika ada, jika tidak pakai pilihan pertama
    $defaultBranch = $userBranch ?? $branchList[0];

    // ====== DATA MENU SIDEBAR ======
    $sidebarMenus = [
        ['label' => 'Dashboard',          'icon' => 'layout-grid',    'url' => route('dashboard'),       'active_pattern' => 'dashboard*'],
        ['label' => 'Jadwal Saya',        'icon' => 'calendar',       'url' => url('/jadwal'),            'active_pattern' => 'jadwal*'],
        ['label' => 'Tukar Shift',        'icon' => 'arrow-left-right','url' => url('/tukar-shift'),      'active_pattern' => 'tukar-shift*'],
        ['label' => 'Presensi / Absensi', 'icon' => 'fingerprint',    'url' => url('/presensi'),          'active_pattern' => 'presensi*'],
        ['label' => 'Timesheet',          'icon' => 'clock',          'url' => url('/timesheet'),         'active_pattern' => 'timesheet*'],
        ['label' => 'Ketersediaan Waktu', 'icon' => 'calendar-check', 'url' => url('/ketersediaan'),     'active_pattern' => 'ketersediaan*'],
        ['label' => 'Pengaturan',         'icon' => 'settings',       'url' => url('/pengaturan'),        'active_pattern' => 'pengaturan*'],
    ];

    // ====== MENU BOTTOM NAV (Mobile) ======
    $bottomMenus = [
        ['label' => 'Beranda',   'icon' => 'layout-grid',    'url' => route('dashboard'),  'active_pattern' => 'dashboard*'],
        ['label' => 'Jadwal',    'icon' => 'calendar',       'url' => url('/jadwal'),       'active_pattern' => 'jadwal*'],
        ['label' => 'Tukar',     'icon' => 'arrow-left-right','url' => url('/tukar-shift'), 'active_pattern' => 'tukar-shift*'],
        ['label' => 'Presensi',  'icon' => 'fingerprint',    'url' => url('/presensi'),     'active_pattern' => 'presensi*'],
        ['label' => 'Menu',      'icon' => 'menu',           'url' => '#',                  'active_pattern' => 'menu*'],
    ];
@endphp

<body class="flex h-screen overflow-hidden text-sm">

    {{-- ===== SIDEBAR (Hidden on Mobile) ===== --}}
    <aside class="hidden md:flex w-[260px] flex-shrink-0 flex-col justify-between border-r border-slate-200 bg-[#F8FAFC]">
        <div>
            {{-- Logo --}}
            <div class="px-6 py-6 flex items-center gap-2">
                <div class="bg-[#0B6B5A] text-white rounded-lg p-1.5 flex items-center justify-center">
                    <i data-lucide="check" class="h-4 w-4 stroke-[3]"></i>
                </div>
                <span class="text-[19px] font-bold text-slate-800 tracking-tight">Jadwal<span style="color: var(--c-primary);">In</span></span>
            </div>

            {{-- Role Dropdown --}}
            <div class="px-5 mb-6">
                <button class="w-full h-8 flex items-center justify-between bg-[#EEF0FB] border border-[#DDE1F5] text-slate-700 rounded-lg px-2.5 py-1 text-[12px] font-semibold transition hover:brightness-95">
                    <span class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="h-3.5 w-3.5" style="color: var(--c-primary);"></i>
                        {{ $userRole }}
                    </span>
                    <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 text-slate-400"></i>
                </button>
            </div>

            {{-- Menu Navigasi --}}
            <nav class="px-4 flex flex-col gap-1">
                @foreach ($sidebarMenus as $menu)
                    @php
                        $isActive = request()->is($menu['active_pattern']);
                    @endphp
                    <a href="{{ $menu['url'] }}" 
                       class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[12.5px] transition duration-200 
                              {{ $isActive 
                                  ? 'bg-[#0B6B5A] text-white font-semibold shadow-sm' 
                                  : 'text-slate-500 font-medium hover:bg-[#0B6B5A] hover:text-white' }}">
                        <i data-lucide="{{ $menu['icon'] }}" class="h-4 w-4"></i> {{ $menu['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Profil Bawah Sidebar (Dinamis dari auth user) --}}
        <div class="p-4">
            <div class="bg-white rounded-lg border border-slate-200 p-2 h-14 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="h-9 w-9 bg-[#0B6B5A] text-white rounded-full flex items-center justify-center font-bold text-[11px] shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="truncate">
                        <p class="text-[12.5px] font-bold text-slate-800 truncate">{{ $userName }}</p>
                        <p class="text-[10px] text-slate-500 truncate capitalize">{{ $userRole }}</p>
                    </div>
                </div>
                @auth
                <form method="POST" action="{{ route('logout') }}" class="shrink-0 flex">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-slate-700 transition p-1.5 rounded-lg hover:bg-slate-50" title="Keluar">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </aside>

    {{-- ===== MAIN WRAPPER ===== --}}
    {{-- Tambahan pb-16 di mobile agar konten tidak tertutup bottom nav --}}
    <main class="flex-1 flex flex-col min-w-0 bg-[#F9FAFB] pb-[60px] md:pb-0">
        
        {{-- ===== TOP NAVBAR ===== --}}
        <header class="h-[64px] md:h-[72px] bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 shrink-0 z-10">
            
            {{-- Bagian Kiri Navbar --}}
            <div class="flex items-center gap-3 md:gap-5">
                {{-- Tampil di mobile saja: Logo Singkat --}}
                <div class="md:hidden flex items-center gap-2">
                    <div class="bg-[#0B6B5A] text-white rounded-lg p-1.5 flex items-center justify-center">
                        <i data-lucide="check" class="h-4 w-4 stroke-[3]"></i>
                    </div>
                </div>

                {{-- Dropdown Outlet INTERAKTIF (Alpine.js) --}}
                <div class="relative"
                     x-data="{ open: false, selected: '{{ addslashes($defaultBranch) }}', branches: {{ json_encode($branchList) }} }"
                     @click.outside="open = false">

                    {{-- Trigger Button --}}
                    <button @click="open = !open"
                            class="flex h-7 items-center gap-1.5 text-[11px] md:text-[12px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg transition hover:bg-slate-100 max-w-[160px] md:max-w-[260px] truncate">
                        <i data-lucide="store" class="h-3.5 w-3.5 shrink-0" style="color: var(--c-primary);"></i>
                        <span class="truncate" x-text="selected"></span>
                        <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    {{-- Dropdown List --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute top-full left-0 mt-1.5 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 py-1 overflow-hidden"
                         style="display: none;">
                        <template x-for="branch in branches" :key="branch">
                            <button type="button"
                                    @click="selected = branch; open = false"
                                    class="w-full text-left flex items-center gap-2 px-3 py-2 text-[12px] font-medium transition-colors"
                                    :class="selected === branch ? 'bg-[#0B6B5A]/10 text-[#0B6B5A] font-semibold' : 'text-slate-600 hover:bg-slate-50'">
                                <i data-lucide="map-pin" class="h-3.5 w-3.5 shrink-0"></i>
                                <span x-text="branch"></span>
                                <i x-show="selected === branch" data-lucide="check" class="h-3.5 w-3.5 ml-auto shrink-0" style="color: var(--c-primary);"></i>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Jam & Tanggal Realtime WIB (Sembunyi di mobile kecil) --}}
                <div class="hidden sm:flex items-center gap-2 text-[12px] text-slate-500 font-medium border-l border-slate-200 pl-4 md:pl-5">
                    <i data-lucide="calendar-days" class="h-4 w-4"></i>
                    <span id="navbar-date">{{ $tanggalStr }}</span>
                    <span class="hidden md:inline">|</span>
                    <span id="navbar-clock" class="hidden md:inline">{{ $now->format('H:i') }}</span>
                    <span class="hidden md:inline">WIB</span>
                </div>
            </div>

            {{-- Bagian Kanan Navbar --}}
            <div class="flex items-center gap-2 md:gap-4">
                <button class="relative text-slate-400 hover:text-slate-700 transition p-1.5">
                    <i data-lucide="bell" class="h-5 w-5"></i>
                    <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
                </button>
                <button class="hidden md:block text-slate-400 hover:text-slate-700 transition p-1.5">
                    <i data-lucide="help-circle" class="h-5 w-5"></i>
                </button>
                {{-- Avatar Inisial User --}}
                <div class="h-8 w-8 ml-1 md:ml-2 bg-[#0B6B5A] text-white rounded-full flex items-center justify-center font-bold text-[12px] cursor-pointer ring-2 ring-offset-1 ring-slate-100" title="{{ $userName }}">
                    {{ $initials }}
                </div>
            </div>
        </header>

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
                $isActive = request()->is($menu['active_pattern']);
            @endphp
            <a href="{{ $menu['url'] }}" class="flex flex-col items-center justify-center w-full h-full gap-1 transition-colors {{ $isActive ? 'text-[#0B6B5A]' : 'text-slate-400 hover:text-slate-600' }}">
                <i data-lucide="{{ $menu['icon'] }}" class="h-5 w-5 {{ $isActive ? 'fill-[#0B6B5A]/10' : '' }}"></i>
                <span class="text-[10px] font-medium">{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Initialize Icons & Scripts --}}
    <script>
        // Inisialisasi Lucide Icons — dijalankan sekali saat DOM siap
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());

        // Re-inisialisasi ikon saat dropdown Alpine terbuka (tanpa MutationObserver)
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                setTimeout(() => lucide.createIcons(), 50);
            });
        });

        // ====== Jam & Tanggal Realtime WIB ======
        (function() {
            const clockEl = document.getElementById('navbar-clock');
            const dateEl  = document.getElementById('navbar-date');

            const hariList   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const bulanList  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sept','Okt','Nov','Des'];

            function updateDateTime() {
                const d = new Date();
                // Konversi ke WIB (UTC+7)
                const wib = new Date(d.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));

                if (clockEl) {
                    const hh = String(wib.getHours()).padStart(2, '0');
                    const mm = String(wib.getMinutes()).padStart(2, '0');
                    clockEl.textContent = hh + ':' + mm;
                }

                if (dateEl) {
                    const hari  = hariList[wib.getDay()];
                    const tgl   = wib.getDate();
                    const bulan = bulanList[wib.getMonth()];
                    const tahun = wib.getFullYear();
                    dateEl.textContent = hari + ', ' + tgl + ' ' + bulan + ' ' + tahun;
                }
            }

            updateDateTime();           // Jalankan langsung
            setInterval(updateDateTime, 1000); // Update setiap detik
        })();
    </script>
    
    @stack('scripts')
</body>
</html>