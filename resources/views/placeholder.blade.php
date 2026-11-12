<x-app-layout>
    @if (($title ?? null) === 'Perusahaan & Cabang')
        <x-slot name="header">
            <div class="space-y-3">
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs font-medium text-[#7C7896]">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#6C5CE7]">
                                Admin
                            </a>
                        </li>
                        <li aria-hidden="true">
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                        </li>
                        <li aria-current="page" class="font-semibold text-[#6C5CE7]">
                            Perusahaan &amp; Cabang
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[#2D2A3E] md:text-2xl">
                            Perusahaan &amp; Cabang
                        </h1>
                        <p class="mt-1 text-xs text-[#7C7896] md:text-sm">
                            {{ $subtitle ?? 'Kelola tenant perusahaan, outlet cabang, dan batasan lisensi.' }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-[#E2E0F7] bg-white px-3 py-1.5 text-xs font-semibold text-[#6C5CE7] shadow-sm">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                        {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>
        </x-slot>

        <section class="space-y-5">
            <div class="overflow-hidden rounded-2xl border border-[#E2E0F7] bg-white shadow-sm">
                <div class="border-b border-[#E2E0F7] bg-gradient-to-r from-[#F5F3FF] via-white to-white px-5 py-5 sm:px-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#E2E0F7] bg-white text-[#6C5CE7] shadow-sm">
                            <i data-lucide="building-2" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#6C5CE7]">
                                Administrasi perusahaan
                            </p>
                            <p class="mt-1 text-sm leading-6 text-[#7C7896]">
                                Daftar perusahaan dan cabang akan ditampilkan di area ini setelah integrasi backend tersedia.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-12 text-center sm:px-8 sm:py-16">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[#E2E0F7] bg-[#F5F3FF] text-[#6C5CE7]">
                        <i data-lucide="building-2" class="h-8 w-8"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-bold text-[#2D2A3E]">
                        Data perusahaan belum tersedia
                    </h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[#7C7896]">
                        Halaman ini belum terhubung ke sumber data perusahaan atau cabang. Tidak ada data contoh yang ditampilkan.
                    </p>
                    <span class="mt-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Backend belum terintegrasi
                    </span>
                </div>
            </div>
        </section>
    @elseif (($title ?? null) === 'Kelola Pengguna')
        <x-slot name="header">
            <div class="space-y-3">
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs font-medium text-[#7C7896]">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#6C5CE7]">
                                Admin
                            </a>
                        </li>
                        <li aria-hidden="true">
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                        </li>
                        <li aria-current="page" class="font-semibold text-[#6C5CE7]">
                            Kelola Pengguna
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[#2D2A3E] md:text-2xl">
                            Kelola Pengguna
                        </h1>
                        <p class="mt-1 text-xs text-[#7C7896] md:text-sm">
                            {{ $subtitle ?? 'Manajemen akun pengguna, penetapan peran (role), dan reset kata sandi.' }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-[#E2E0F7] bg-white px-3 py-1.5 text-xs font-semibold text-[#6C5CE7] shadow-sm">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                        {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>
        </x-slot>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(260px,0.8fr)]">
            <div class="overflow-hidden rounded-2xl border border-[#E2E0F7] bg-white shadow-sm">
                <div class="border-b border-[#E2E0F7] bg-gradient-to-r from-[#F5F3FF] via-white to-white px-5 py-5 sm:px-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#E2E0F7] bg-white text-[#6C5CE7] shadow-sm">
                            <i data-lucide="users-round" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#6C5CE7]">
                                Administrasi akun
                            </p>
                            <p class="mt-1 text-sm leading-6 text-[#7C7896]">
                                Kelola informasi pengguna dan akses akun dari satu tempat setelah fitur ini terhubung ke backend.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-12 text-center sm:px-8 sm:py-16">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[#E2E0F7] bg-[#F5F3FF] text-[#6C5CE7]">
                        <i data-lucide="users-round" class="h-8 w-8"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-bold text-[#2D2A3E]">
                        Pengelolaan pengguna belum tersedia
                    </h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[#7C7896]">
                        Route ini belum terhubung ke daftar pengguna atau aksi pengelolaan. Data dan kontrol akun tidak ditampilkan sebelum integrasi tersedia.
                    </p>
                </div>
            </div>

            <aside class="rounded-2xl border border-[#E2E0F7] bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F5F3FF] text-[#6C5CE7]">
                        <i data-lucide="lock-keyhole" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-[#2D2A3E]">Akses tetap terlindungi</h2>
                        <p class="mt-0.5 text-xs text-[#7C7896]">Khusus Admin / Super Admin</p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-[#E2E0F7] bg-[#F5F3FF]/60 p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-[#6C5CE7]"></i>
                        <p class="text-xs leading-5 text-[#7C7896]">
                            Halaman ini belum menyediakan daftar akun, pencarian, atau aksi tambah dan ubah pengguna.
                        </p>
                    </div>
                </div>
            </aside>
        </section>
    @else
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    {{ $title ?? 'Modul Fitur' }}
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    {{ $subtitle ?? 'Halaman operasional fitur Jadwalin.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Konten Placeholder Terstandarisasi --}}
    <div class="bg-white rounded-2xl border border-[#E2E0F7] p-8 md:p-12 text-center shadow-2xs">
        <div class="max-w-md mx-auto flex flex-col items-center">
            <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4 shadow-2xs">
                <i data-lucide="{{ $icon ?? 'layers' }}" class="h-8 w-8"></i>
            </div>
            <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">{{ $title ?? 'Modul Dalam Pengembangan' }}</h3>
            <p class="text-xs md:text-sm text-[#7C7896] leading-relaxed mb-6">
                {{ $description ?? 'Fitur ini telah siap dari segi routing, otorisasi hak akses, dan tata letak UI. Controller logika bisnis sedang dalam tahap integrasi.' }}
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
    @endif
</x-app-layout>
