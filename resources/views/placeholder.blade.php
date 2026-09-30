<x-app-layout>
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
</x-app-layout>
