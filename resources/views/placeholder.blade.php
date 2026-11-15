<x-app-layout>
    @if (($title ?? null) === 'Perusahaan & Cabang')
        <x-slot name="header">
            <div class="space-y-3">
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs font-medium text-slate-500">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-indigo-600">
                                Admin
                            </a>
                        </li>
                        <li aria-hidden="true">
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-slate-400"></i>
                        </li>
                        <li aria-current="page" class="font-semibold text-indigo-600">
                            Perusahaan &amp; Cabang
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 md:text-2xl">
                            Perusahaan &amp; Cabang
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 md:text-sm">
                            {{ $subtitle ?? 'Kelola tenant perusahaan, outlet cabang, dan batasan lisensi.' }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-card">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5 text-indigo-600"></i>
                        {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>
        </x-slot>

        <section class="space-y-5">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card">
                <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-5 sm:px-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-indigo-600 shadow-xs">
                            <i data-lucide="building-2" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                                Administrasi perusahaan
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Daftar perusahaan dan cabang akan ditampilkan di area ini setelah integrasi backend tersedia.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-12 text-center sm:px-8 sm:py-16">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                        <i data-lucide="building-2" class="h-7 w-7"></i>
                    </div>
                    <h2 class="mt-4 text-base font-bold text-slate-900">
                        Data perusahaan belum tersedia
                    </h2>
                    <p class="mx-auto mt-1.5 max-w-lg text-xs leading-relaxed text-slate-500">
                        Halaman ini belum terhubung ke sumber data perusahaan atau cabang. Tidak ada data contoh yang ditampilkan.
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
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
                    <ol class="flex items-center gap-2 text-xs font-medium text-slate-500">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-indigo-600">
                                Admin
                            </a>
                        </li>
                        <li aria-hidden="true">
                            <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-slate-400"></i>
                        </li>
                        <li aria-current="page" class="font-semibold text-indigo-600">
                            Kelola Pengguna
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 md:text-2xl">
                            Kelola Pengguna
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 md:text-sm">
                            {{ $subtitle ?? 'Manajemen akun pengguna, penetapan peran (role), dan reset kata sandi.' }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-card">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5 text-indigo-600"></i>
                        {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>
        </x-slot>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(260px,0.8fr)]">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card">
                <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-5 sm:px-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-indigo-600 shadow-xs">
                            <i data-lucide="users" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                                Administrasi akun
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Kelola informasi pengguna dan akses akun dari satu tempat setelah fitur ini terhubung ke backend.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-12 text-center sm:px-8 sm:py-16">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                        <i data-lucide="users" class="h-7 w-7"></i>
                    </div>
                    <h2 class="mt-4 text-base font-bold text-slate-900">
                        Pengelolaan pengguna belum tersedia
                    </h2>
                    <p class="mx-auto mt-1.5 max-w-lg text-xs leading-relaxed text-slate-500">
                        Route ini belum terhubung ke daftar pengguna atau aksi pengelolaan. Data dan kontrol akun tidak ditampilkan sebelum integrasi tersedia.
                    </p>
                </div>
            </div>

            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i data-lucide="lock" class="h-4.5 w-4.5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Akses tetap terlindungi</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Khusus Admin / Super Admin</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3.5">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"></i>
                        <p class="text-xs leading-relaxed text-slate-600">
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
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    {{ $title ?? 'Modul Fitur' }}
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">
                    {{ $subtitle ?? 'Halaman operasional fitur Jadwalin.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    {{ $roleTitle ?? ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Konten Placeholder Terstandarisasi --}}
    <div class="bg-white rounded-xl border border-slate-200 p-8 md:p-12 text-center shadow-card">
        <div class="max-w-md mx-auto flex flex-col items-center">
            <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-4 shadow-xs">
                <i data-lucide="{{ $icon ?? 'layers' }}" class="h-7 w-7"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1.5">{{ $title ?? 'Modul Dalam Pengembangan' }}</h3>
            <p class="text-xs md:text-sm text-slate-500 leading-relaxed mb-6">
                {{ $description ?? 'Fitur ini telah siap dari segi routing, otorisasi hak akses, dan tata letak UI. Controller logika bisnis sedang dalam tahap integrasi.' }}
            </p>
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
    @endif
</x-app-layout>