<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Dashboard
                </h1>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-medium">
                    {{ $roleTitle ?? ucfirst(auth()->user()?->role ?? 'Staf') }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>{{ auth()->user()?->company?->name ?? 'Jadwalin Workspace' }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Sub-views modular berdasarkan peran (Super Admin / Manager / Karyawan) --}}
    @if (($roleTitle ?? '') === 'Super Admin')
        @include('dashboard.superadmin')
    @elseif (($roleTitle ?? '') === 'Manager')
        @include('dashboard.manager')
    @else
        @include('dashboard.employee')
    @endif
</x-app-layout>
