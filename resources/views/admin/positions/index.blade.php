<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-indigo-600">Admin</a>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li>
                        <a href="{{ route('admin.departments.index') }}" class="transition hover:text-indigo-600">Departemen &amp; Posisi</a>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li aria-current="page" class="font-semibold text-indigo-600">Posisi Jabatan</li>
                </ol>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Posisi Jabatan
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">
                    Kelola jabatan kerja berdasarkan department.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.departments.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition shadow-sm">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Tambah Department</span>
                </a>
            </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Navigation Tabs --}}
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('admin.departments.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-900 hover:bg-indigo-50 transition">
                <i data-lucide="building-2" class="h-4 w-4"></i>
                <span>Daftar Departemen</span>
            </a>
            <a href="{{ route('admin.positions.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-indigo-600 text-white shadow-sm transition">
                <i data-lucide="briefcase" class="h-4 w-4"></i>
                <span>Posisi Jabatan</span>
            </a>
        </div>
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-card">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-card">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Department Cards List --}}
        @if ($departments->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 md:p-12 text-center shadow-sm">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 bg-indigo-50 border border-slate-200 rounded-xl flex items-center justify-center text-indigo-600 mb-4 shadow-card">
                        <i data-lucide="building-2" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Belum Ada Department</h3>
                    <p class="text-xs md:text-sm text-slate-500 leading-relaxed mb-6">
                        Tambahkan data department terlebih dahulu sebelum menambahkan jabatan atau posisi kerja.
                    </p>
                    <a href="{{ route('admin.departments.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition shadow-sm">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        <span>Tambah Department</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach ($departments as $department)
                    @php
                        $hasErrorOnThisDepartment = old('department_id') == $department->id && $errors->any();
                    @endphp
                    <div x-data="{ showForm: {{ ($hasErrorOnThisDepartment || $department->positions->isEmpty()) ? 'true' : 'false' }} }" 
                         class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                        
                        {{-- Card Header --}}
                        <div>
                            <div class="p-5 border-b border-slate-200 flex items-center justify-between gap-3 bg-indigo-50/60">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 shrink-0 shadow-card">
                                        <i data-lucide="building-2" class="h-4 w-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-sm md:text-base font-bold text-slate-900 truncate">
                                            {{ $department->name }}
                                        </h2>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500">
                                            <i data-lucide="briefcase" class="h-3 w-3 text-indigo-600"></i>
                                            <span>{{ $department->positions->count() }} Posisi</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Tombol Toggle Form Tambah Posisi --}}
                                <button type="button" 
                                        @click="showForm = !showForm" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition duration-150 shadow-card shrink-0">
                                    <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                                    <span>Tambah Posisi</span>
                                </button>
                            </div>

                            {{-- Form Tambah Position --}}
                            <div x-show="showForm" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-4 bg-indigo-50/60 border-b border-slate-200">
                                <form action="{{ route('admin.positions.store') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="department_id" value="{{ $department->id }}">

                                    <div>
                                        <label for="name-{{ $department->id }}" class="block text-xs font-bold text-slate-900 mb-1.5">
                                            Nama Posisi
                                        </label>
                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                            <input type="text" 
                                                   id="name-{{ $department->id }}" 
                                                   name="name" 
                                                   value="{{ old('department_id') == $department->id ? old('name') : '' }}"
                                                   placeholder="Contoh: Staff HR, Frontend Dev, Supervisor" 
                                                   required 
                                                   class="flex-1 px-3.5 py-2 text-xs md:text-sm rounded-xl border {{ ($hasErrorOnThisDepartment && $errors->has('name')) ? 'border-red-400 bg-red-50/50' : 'border-slate-200 bg-white' }} text-slate-900 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 outline-none transition duration-150">
                                            
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition duration-150 shadow-sm shrink-0">
                                                <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                                                <span>Tambah</span>
                                            </button>
                                        </div>

                                        {{-- Validation Error Messages --}}
                                        @error('name')
                                            @if (old('department_id') == $department->id || !old('department_id'))
                                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @endif
                                        @enderror

                                        @error('department_id')
                                            @if (old('department_id') == $department->id || !old('department_id'))
                                                <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                                                    <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                                                    <span>{{ $message }}</span>
                                                </p>
                                            @endif
                                        @enderror
                                    </div>
                                </form>
                            </div>

                            {{-- Daftar Position --}}
                            <div class="p-4 sm:p-5">
                                @if ($department->positions->isEmpty())
                                    <div class="py-6 text-center text-xs md:text-sm text-slate-500">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-indigo-50 flex items-center justify-center text-slate-400 mb-2">
                                            <i data-lucide="briefcase" class="h-5 w-5"></i>
                                        </div>
                                        <p class="font-medium text-slate-900">Belum ada posisi.</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Tambahkan posisi pertama untuk department {{ $department->name }}.</p>
                                    </div>
                                @else
                                    <ul class="divide-y divide-slate-100/60">
                                        @foreach ($department->positions as $position)
                                            <li class="py-3 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-600 shrink-0"></span>
                                                    <span class="text-xs md:text-sm font-semibold text-slate-900 truncate">
                                                        {{ $position->name }}
                                                    </span>
                                                </div>

                                                {{-- Tombol Hapus Posisi --}}
                                                <div class="shrink-0 flex items-center">
                                                    <button type="button" 
                                                            x-data="" 
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-position-deletion-{{ $position->id }}')" 
                                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition duration-150" 
                                                            title="Hapus Posisi">
                                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                        <span class="hidden sm:inline">Hapus</span>
                                                    </button>

                                                    {{-- Modal Konfirmasi Hapus Posisi --}}
                                                    <x-modal name="confirm-position-deletion-{{ $position->id }}" :show="false">
                                                        <form method="POST" action="{{ route('admin.positions.destroy', $position) }}" class="p-6 text-left">
                                                            @csrf
                                                            @method('DELETE')

                                                            <div class="flex items-start gap-4 mb-4">
                                                                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                                                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                                                                </div>
                                                                <div>
                                                                    <h2 class="text-base font-bold text-slate-900">
                                                                        Hapus Posisi
                                                                    </h2>
                                                                    <p class="text-xs text-slate-500 mt-0.5">
                                                                        Konfirmasi penghapusan jabatan kerja.
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-6">
                                                                Apakah Anda yakin ingin menghapus posisi <strong class="text-slate-900">{{ $position->name }}</strong> dari department <strong class="text-slate-900">{{ $department->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                                            </p>

                                                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                                                                <button type="button" 
                                                                        x-on:click="$dispatch('close')" 
                                                                        class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 bg-indigo-50 hover:bg-[#E2E8F0] rounded-xl transition duration-150">
                                                                    Batal
                                                                </button>
                                                                <button type="submit" 
                                                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition duration-150 shadow-sm">
                                                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                                    <span>Hapus Posisi</span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
