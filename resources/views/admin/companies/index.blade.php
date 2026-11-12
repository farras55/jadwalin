<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6 p-4 sm:p-8">
        
        <!-- Statistik & Ringkasan Cabang -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-2xs flex items-center space-x-4">
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Identitas Organisasi</h3>
                    <p class="text-sm text-gray-500">Pengaturan profil tenant utama</p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-2xs flex items-center space-x-4">
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Lisensi & Outlet</h3>
                    <p class="text-sm text-gray-500">Status Aktif &bull; 1 Kantor Pusat</p>
                </div>
            </div>
        </div>

        <!-- Form Profil Perusahaan -->
        <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-2xs">
            <form action="{{ route('admin.companies.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Legal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="building-2" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i> Nama Legal Perusahaan
                        </label>
                        <input type="text" name="name" value="{{ old('name', $company?->name) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="mail" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i> Email Perusahaan
                        </label>
                        <input type="email" name="email" value="{{ old('email', $company?->email) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="phone" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i> No. Telepon Kantor
                        </label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $company?->phone_number) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('phone_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="clock" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i> Zona Waktu Operasional
                        </label>
                        <select name="timezone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Asia/Jakarta" {{ old('timezone', $company?->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $company?->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $company?->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                        </select>
                        @error('timezone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="map-pin" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i> Alamat Fisik Outlet / Pusat
                        </label>
                        <textarea name="address" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address', $company?->address) }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 font-medium transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Render Lucide Icons -->
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</x-app-layout>