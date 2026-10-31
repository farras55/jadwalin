<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-[#7C7896] mb-1">
                    <a href="{{ route('admin.departments.index') }}" class="hover:text-[#6C5CE7] transition">Departemen</a>
                    <span>/</span>
                    <span class="text-[#2D2A3E]">Edit</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Edit Department
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Perbarui informasi untuk department <strong class="font-semibold text-[#2D2A3E]">{{ $department->name }}</strong>
                </p>
            </div>
            <div>
                <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-white border border-[#E2E0F7] hover:bg-[#F5F3FF] transition shadow-2xs">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-[#E2E0F7] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7]">
                        <i data-lucide="pencil-line" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#2D2A3E]">Perbarui Data Department</h2>
                        <p class="text-xs text-[#7C7896]">Departemen: {{ $department->name }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                    <i data-lucide="building-2" class="h-3.5 w-3.5"></i>
                    <span>ID #{{ $department->id }}</span>
                </span>
            </div>

            <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Field Name (Wajib) --}}
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                        Nama Department <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $department->name) }}" 
                           placeholder="Contoh: Operasional, IT, HR, Marketing" 
                           required 
                           class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                    
                    @error('name')
                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Field Description (Opsional) --}}
                <div>
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                        Deskripsi <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4" 
                              placeholder="Deskripsi singkat fungsi dan tugas divisi..." 
                              class="w-full px-4 py-2.5 text-sm rounded-xl border {{ $errors->has('description') ? 'border-red-400 bg-red-50/30' : 'border-[#E2E0F7] bg-slate-50 focus:bg-white' }} text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">{{ old('description', $department->description) }}</textarea>
                    
                    @error('description')
                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Titik Koordinat & Geofencing Cabang --}}
                <div class="pt-4 border-t border-[#E2E0F7] space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-[#2D2A3E] flex items-center gap-2">
                            <i data-lucide="map-pin" class="h-4 w-4 text-[#6C5CE7]"></i>
                            <span>Titik Koordinat &amp; Geofencing Presensi Cabang</span>
                        </h3>
                        <p class="text-xs text-[#7C7896] mt-0.5">
                            Koordinat GPS yang digunakan sebagai acuan validasi Clock-In karyawan pada cabang ini.
                        </p>
                    </div>

                    {{-- Alamat Cabang --}}
                    <div>
                        <label for="address" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                            Alamat Kantor Cabang <span class="text-xs font-normal normal-case text-[#7C7896]">(Opsional)</span>
                        </label>
                        <input type="text"
                               name="address"
                               id="address"
                               value="{{ old('address', $department->address) }}"
                               placeholder="Contoh: Jl. Suryo No. 12, Senopati, Kebayoran Baru"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Latitude --}}
                        <div>
                            <label for="latitude" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Latitude Cabang
                            </label>
                            <input type="number"
                                   step="any"
                                   name="latitude"
                                   id="latitude"
                                   value="{{ old('latitude', $department->latitude) }}"
                                   placeholder="-6.2383200"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] font-mono focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                        </div>

                        {{-- Longitude --}}
                        <div>
                            <label for="longitude" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Longitude Cabang
                            </label>
                            <input type="number"
                                   step="any"
                                   name="longitude"
                                   id="longitude"
                                   value="{{ old('longitude', $department->longitude) }}"
                                   placeholder="106.8122800"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] font-mono focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                        </div>

                        {{-- Radius --}}
                        <div>
                            <label for="radius" class="block text-xs font-bold uppercase tracking-wider text-[#2D2A3E] mb-2">
                                Radius Presensi (Meter)
                            </label>
                            <input type="number"
                                   min="5"
                                   max="10000"
                                   step="1"
                                   name="radius"
                                   id="radius"
                                   value="{{ old('radius', $department->radius ?? ($department->radius_meters ?? 100)) }}"
                                   placeholder="100"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] font-mono focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition duration-150">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                    <a href="{{ route('admin.departments.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] border border-[#E2E0F7] transition duration-150">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition duration-150 shadow-sm">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
