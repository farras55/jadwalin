<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-[#7C7896] mb-1">
                    <a href="{{ route('admin.departments.index') }}" class="hover:text-[#6C5CE7] transition">Departemen</a>
                    <span>/</span>
                    <span class="text-[#2D2A3E]">Kelola Posisi</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Posisi
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Kelola jabatan kerja berdasarkan department.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.departments.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-white border border-[#E2E0F7] hover:bg-[#F5F3FF] transition shadow-2xs">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Kembali ke Department</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Department Cards List --}}
        @if ($departments->isEmpty())
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-8 md:p-12 text-center shadow-2xs">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4 shadow-2xs">
                        <i data-lucide="building-2" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">Belum Ada Department</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] leading-relaxed mb-6">
                        Tambahkan data department terlebih dahulu sebelum menambahkan jabatan atau posisi kerja.
                    </p>
                    <a href="{{ route('admin.departments.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
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
                         class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden flex flex-col justify-between">
                        
                        {{-- Card Header --}}
                        <div>
                            <div class="p-5 border-b border-[#E2E0F7] flex items-center justify-between gap-3 bg-[#F5F3FF]/40">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7] shrink-0 shadow-2xs">
                                        <i data-lucide="building-2" class="h-4 w-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-sm md:text-base font-bold text-[#2D2A3E] truncate">
                                            {{ $department->name }}
                                        </h2>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#7C7896]">
                                            <i data-lucide="briefcase" class="h-3 w-3 text-[#6C5CE7]"></i>
                                            <span>{{ $department->positions->count() }} Posisi</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Tombol Toggle Form Tambah Posisi --}}
                                <button type="button" 
                                        @click="showForm = !showForm" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition duration-150 shadow-2xs shrink-0">
                                    <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                                    <span>Tambah Posisi</span>
                                </button>
                            </div>

                            {{-- Form Tambah Position --}}
                            <div x-show="showForm" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-4 bg-[#F5F3FF]/70 border-b border-[#E2E0F7]">
                                <form action="{{ route('admin.positions.store') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="department_id" value="{{ $department->id }}">

                                    <div>
                                        <label for="name-{{ $department->id }}" class="block text-xs font-bold text-[#2D2A3E] mb-1.5">
                                            Nama Posisi
                                        </label>
                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                            <input type="text" 
                                                   id="name-{{ $department->id }}" 
                                                   name="name" 
                                                   value="{{ old('department_id') == $department->id ? old('name') : '' }}"
                                                   placeholder="Contoh: Staff HR, Frontend Dev, Supervisor" 
                                                   required 
                                                   class="flex-1 px-3.5 py-2 text-xs md:text-sm rounded-xl border {{ ($hasErrorOnThisDepartment && $errors->has('name')) ? 'border-red-400 bg-red-50/50' : 'border-[#E2E0F7] bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                                            
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition duration-150 shadow-sm shrink-0">
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
                                    <div class="py-6 text-center text-xs md:text-sm text-[#7C7896]">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-[#F5F3FF] flex items-center justify-center text-slate-400 mb-2">
                                            <i data-lucide="briefcase" class="h-5 w-5"></i>
                                        </div>
                                        <p class="font-medium text-[#2D2A3E]">Belum ada posisi.</p>
                                        <p class="text-xs text-[#7C7896] mt-0.5">Tambahkan posisi pertama untuk department {{ $department->name }}.</p>
                                    </div>
                                @else
                                    <ul class="divide-y divide-[#E2E0F7]/60">
                                        @foreach ($department->positions as $position)
                                            <li class="py-3 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-2 h-2 rounded-full bg-[#6C5CE7] shrink-0"></span>
                                                    <span class="text-xs md:text-sm font-semibold text-[#2D2A3E] truncate">
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
                                                                    <h2 class="text-base font-bold text-[#2D2A3E]">
                                                                        Hapus Posisi
                                                                    </h2>
                                                                    <p class="text-xs text-[#7C7896] mt-0.5">
                                                                        Konfirmasi penghapusan jabatan kerja.
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed mb-6">
                                                                Apakah Anda yakin ingin menghapus posisi <strong class="text-[#2D2A3E]">{{ $position->name }}</strong> dari department <strong class="text-[#2D2A3E]">{{ $department->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                                            </p>

                                                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                                                                <button type="button" 
                                                                        x-on:click="$dispatch('close')" 
                                                                        class="px-4 py-2 text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] rounded-xl transition duration-150">
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
