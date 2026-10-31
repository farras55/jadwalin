<x-app-layout>
    {{-- Leaflet Map CSS & JS for Real-Time Geofencing Map --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        .custom-branch-pin {
            background: transparent !important;
            border: none !important;
        }
        .leaflet-container {
            font-family: inherit;
        }
    </style>

    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Pengaturan Perusahaan
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Atur batas jam kerja dan ketentuan kepatuhan karyawan.
            </p>
        </div>
    </x-slot>

    @php
        $branchesData = $branchesData ?? ($branches ?? collect())->map(function($b) {
            $companyName = $b->company?->name;
            $displayName = $b->name . ($companyName ? " ({$companyName})" : '');
            return [
                'id' => $b->id,
                'name' => $displayName,
                'raw_name' => $b->name,
                'company_name' => $companyName,
                'address' => $b->address,
                'latitude' => $b->latitude !== null ? (float) $b->latitude : null,
                'longitude' => $b->longitude !== null ? (float) $b->longitude : null,
                'radius' => (int) ($b->radius_meters ?? $b->radius ?? 100),
            ];
        })->values();

        $companySettingsData = $companySettingsData ?? [
            'latitude' => $setting->office_latitude !== null ? (float) $setting->office_latitude : ($company?->office_latitude !== null ? (float) $company->office_latitude : null),
            'longitude' => $setting->office_longitude !== null ? (float) $setting->office_longitude : ($company?->office_longitude !== null ? (float) $company->office_longitude : null),
            'radius' => (int) ($setting->attendance_radius_meters ?? ($company?->attendance_radius_meters ?? 100)),
        ];
    @endphp

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-5xl px-4 pb-10 sm:px-6 lg:px-8"
             x-data="branchLocationManager(@js($branchesData), @js($companySettingsData), {{ $selectedBranchId ? (int) $selectedBranchId : 'null' }})">

            {{-- Pesan berhasil --}}
            @if (session('success'))
                <div
                    role="status"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Pesan validasi --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                >
                    <p class="font-semibold">
                        Periksa kembali input kamu:
                    </p>

                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM PENGATURAN LOKASI CABANG DINAMIS (OWNER MULTI-BRANCH GEOFENCING) --}}
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Pengaturan Titik Koordinat &amp; Geofencing Cabang
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Pilih cabang perusahaan dan tentukan titik koordinat GPS (latitude, longitude) serta radius batas absensi secara dinamis.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 self-start sm:self-auto">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                        <span>Multi-Branch Dinamis</span>
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.settings.update-branch-location') }}" @submit="prepareSubmit()" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Dropdown Pemilihan Cabang --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="branch_id_select" class="block text-sm font-medium text-gray-700">
                                Pilih Cabang / Branch Perusahaan <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs text-indigo-600 font-medium" x-show="branchName" x-text="'Aktif: ' + branchName"></span>
                        </div>
                        <select id="branch_id_select"
                                name="branch_id"
                                x-model="selectedBranchId"
                                @change="onBranchChange()"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($branches as $b)
                                @php
                                    $cName = $b->company?->name;
                                    $dName = $b->name . ($cName ? " ({$cName})" : '');
                                @endphp
                                <option value="{{ $b->id }}">{{ $dName }}{{ $b->address ? ' — ' . $b->address : '' }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Pilih cabang tempat kerja yang ingin diatur titik GPS dan batas radius presensinya.
                        </p>
                    </div>

                    {{-- Input Nama Lokasi / Alamat Cabang dengan Autocomplete Real-Time & Matikan Browser Autofill --}}
                    <div class="relative" @click.outside="showDropdown = false">
                        <label for="branch_address_input" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Lokasi / Alamat Cabang <span class="text-xs font-normal text-gray-500">(Ketik nama lokasi atau geser pin di peta)</span>
                        </label>
                        <div class="relative mt-1">
                            <input id="branch_address_input"
                                   name="address"
                                   type="text"
                                   autocomplete="off"
                                   x-model="locationName"
                                   @input.debounce.400ms="searchLocations($event.target.value)"
                                   @keydown.enter.prevent="searchLocations(locationName, true)"
                                   @focus="if (searchResults.length > 0) showDropdown = true"
                                   @keydown.escape="showDropdown = false"
                                   placeholder="Ketik nama lokasi / alamat cabang (contoh: Politeknik Negeri Malang, Menara Cakrawala, dsb.)"
                                   class="block w-full rounded-lg border-gray-300 pl-10 pr-28 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <button type="button"
                                    @click="searchLocations(locationName, true)"
                                    class="absolute inset-y-1.5 right-1.5 px-3 rounded-md bg-indigo-50 border border-indigo-200 text-xs font-medium text-indigo-700 hover:bg-indigo-600 hover:text-white transition flex items-center gap-1.5 shadow-xs">
                                <span x-show="!isSearching">Cari GPS</span>
                                <span x-show="isSearching" class="flex items-center gap-1">
                                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Mencari...</span>
                                </span>
                            </button>
                        </div>

                        {{-- Custom Dropdown List Rekomendasi Hasil Pencarian OpenStreetMap Nominatim --}}
                        <div x-show="showDropdown && (searchResults.length > 0 || isSearching || searchErrorMessage)"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-lg border border-gray-200 shadow-xl z-[1050] max-h-64 overflow-y-auto divide-y divide-gray-100">

                            <div x-show="isSearching" class="p-3 text-center text-xs text-gray-500 flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-indigo-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Mencari rekomendasi lokasi di OpenStreetMap...</span>
                            </div>

                            <div x-show="!isSearching && searchErrorMessage" class="p-3 text-center text-xs text-amber-700 bg-amber-50" x-text="searchErrorMessage"></div>

                            <template x-for="(item, idx) in searchResults" :key="idx">
                                <button type="button"
                                        @click="selectLocation(item)"
                                        class="w-full text-left px-3.5 py-2.5 hover:bg-indigo-50/60 transition flex items-start gap-2.5 group cursor-pointer">
                                    <div class="mt-0.5 text-indigo-600 shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-900 group-hover:text-indigo-600 truncate" x-text="item.title || item.name || item.address"></p>
                                        <p class="text-[11px] text-gray-500 line-clamp-1" x-text="item.subtitle || item.display_name || item.address"></p>
                                        <div class="mt-0.5 flex items-center gap-2 text-[10px] text-gray-400 font-mono">
                                            <span>Lat: <strong class="text-gray-700" x-text="Number(item.lat).toFixed(6)"></strong></span>
                                            <span>Lng: <strong class="text-gray-700" x-text="Number(item.lon || item.lng).toFixed(6)"></strong></span>
                                            <span x-show="item.source" class="px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-sans font-medium" x-text="item.source"></span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <p class="mt-1 text-xs text-gray-500">
                            <span x-show="autoFilledNotice" class="text-emerald-600 font-semibold flex items-center gap-1" x-text="autoFilledNotice"></span>
                            <span x-show="!autoFilledNotice">Ketik nama lokasi atau paste koordinat "lat, lng", sistem akan mengisi latitude &amp; longitude secara otomatis.</span>
                        </p>
                    </div>

                    {{-- PETA INTERAKTIF GEOFENCING CABANG (LEAFLET.JS) --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <span>Peta Interaktif &amp; Geofencing Presensi</span>
                            </label>
                            <span class="text-xs text-gray-500 hidden sm:inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Geser pin (marker) atau klik peta untuk koordinat presisi</span>
                            </span>
                        </div>

                        <div class="mt-1 relative w-full rounded-xl border border-gray-200 overflow-hidden shadow-xs bg-gray-100">
                            <div id="branch-geofence-map" class="w-full h-72 sm:h-80 relative z-0"></div>

                            {{-- Overlay Badge Koordinat & Radius --}}
                            <div class="absolute bottom-3 left-3 z-[400] bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm text-xs text-gray-700 pointer-events-none flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-ping"></span>
                                <span class="font-mono font-medium" x-text="hasValidCoordinates() ? (branchLatitude + ', ' + branchLongitude) : 'Belum diatur'"></span>
                                <span class="text-gray-300">&bull;</span>
                                <span class="font-semibold text-emerald-600" x-text="'Radius: ' + branchRadius + 'm'"></span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 sm:hidden">
                            💡 Geser pin (marker) atau klik langsung di peta untuk menyesuaikan titik koordinat secara presisi.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        {{-- Latitude Cabang --}}
                        <div>
                            <label for="branch_latitude_input" class="block text-sm font-medium text-gray-700">
                                Latitude Cabang <span class="text-red-500">*</span>
                            </label>
                            <input id="branch_latitude_input"
                                   name="latitude"
                                   type="text"
                                   inputmode="decimal"
                                   required
                                   x-model="branchLatitude"
                                   @input="onLatitudeInput($event.target.value)"
                                   placeholder="Contoh: -7.9439111"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            <p class="mt-1 text-xs text-gray-500">Garis lintang (contoh: -7.9439111)</p>
                        </div>

                        {{-- Longitude Cabang --}}
                        <div>
                            <label for="branch_longitude_input" class="block text-sm font-medium text-gray-700">
                                Longitude Cabang <span class="text-red-500">*</span>
                            </label>
                            <input id="branch_longitude_input"
                                   name="longitude"
                                   type="text"
                                   inputmode="decimal"
                                   required
                                   x-model="branchLongitude"
                                   @input="onLongitudeInput($event.target.value)"
                                   placeholder="Contoh: 112.6148093"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            <p class="mt-1 text-xs text-gray-500">Garis bujur (contoh: 112.6148093)</p>
                        </div>

                        {{-- Radius Cabang --}}
                        <div>
                            <label for="branch_radius_input" class="block text-sm font-medium text-gray-700">
                                Radius Absensi (Meter) <span class="text-red-500">*</span>
                            </label>
                            <input id="branch_radius_input"
                                   name="radius"
                                   type="number"
                                   min="5"
                                   max="10000"
                                   step="1"
                                   required
                                   x-model="branchRadius"
                                   @input="onRadiusInput($event.target.value)"
                                   placeholder="100"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            <p class="mt-1 text-xs text-gray-500">Batas toleransi jarak Clock-In (default: 100m)</p>
                        </div>
                    </div>

                    {{-- Status preview & tombol simpan --}}
                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="text-xs text-gray-700 space-y-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                <span>Cabang: <strong class="text-indigo-600 font-semibold" x-text="branchName">Cabang</strong></span>
                                <span class="text-gray-300">&bull;</span>
                                <span class="text-gray-600">Alamat: <strong class="text-gray-900 font-medium" x-text="locationName || 'Belum diatur'">Alamat Cabang</strong></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-gray-600">
                                <span>Titik: <strong class="font-mono text-gray-900" x-text="hasValidCoordinates() ? (branchLatitude + ', ' + branchLongitude) : 'Belum diatur'">Belum diatur</strong></span>
                                <span class="text-gray-300">&bull;</span>
                                <span>Radius Aman: <strong class="text-emerald-600 font-semibold" x-text="branchRadius + ' Meter'">100 Meter</strong></span>
                                <span class="ml-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold" x-show="autoFilledNotice" x-text="autoFilledNotice"></span>
                            </div>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Lokasi Cabang</span>
                        </button>
                    </div>
                </form>
            </section>

            <form
                id="form-global-settings"
                method="POST"
                action="{{ route('admin.settings.update') }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- Sinkronisasi Cabang yang Sedang Dipilih ke Form Global --}}
                <input type="hidden" name="branch_id" :value="selectedBranchId">
                <input type="hidden" name="branch_address" :value="locationName">
                <input type="hidden" name="branch_latitude" :value="branchLatitude">
                <input type="hidden" name="branch_longitude" :value="branchLongitude">
                <input type="hidden" name="branch_radius" :value="branchRadius">

                {{-- KETENTUAN JAM KERJA --}}
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Ketentuan Jam Kerja
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan batas jam kerja reguler, lembur,
                        dan waktu istirahat.
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Jam kerja reguler --}}
                        <div>
                            <label
                                for="max_regular_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Jam Kerja Reguler
                            </label>

                            <input
                                id="max_regular_hours"
                                name="max_regular_hours"
                                type="number"
                                min="1"
                                max="60"
                                step="1"
                                required
                                value="{{ old('max_regular_hours', $setting->max_regular_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 1–60 jam.
                            </p>
                        </div>

                        {{-- Jam lembur --}}
                        <div>
                            <label
                                for="max_overtime_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Jam Lembur
                            </label>

                            <input
                                id="max_overtime_hours"
                                name="max_overtime_hours"
                                type="number"
                                min="0"
                                max="24"
                                step="1"
                                required
                                value="{{ old('max_overtime_hours', $setting->max_overtime_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 0–24 jam.
                            </p>
                        </div>

                        {{-- Periode batas jam kerja --}}
                        <div>
                            <label
                                for="work_hour_limit_type"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Periode Batas Jam Kerja
                            </label>

                            <select
                                id="work_hour_limit_type"
                                name="work_hour_limit_type"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option
                                    value="weekly"
                                    @selected(old('work_hour_limit_type', $setting->work_hour_limit_type) === 'weekly')
                                >
                                    Mingguan
                                </option>

                                <option
                                    value="daily"
                                    @selected(old('work_hour_limit_type', $setting->work_hour_limit_type) === 'daily')
                                >
                                    Harian
                                </option>
                            </select>
                        </div>

                        {{-- Waktu istirahat --}}
                        <div>
                            <label
                                for="min_rest_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Minimal Waktu Istirahat
                            </label>

                            <input
                                id="min_rest_hours"
                                name="min_rest_hours"
                                type="number"
                                min="1"
                                max="24"
                                step="1"
                                required
                                value="{{ old('min_rest_hours', $setting->min_rest_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam jam, rentang 1–24.
                            </p>
                        </div>

                        {{-- Hari kerja --}}
                        <div>
                            <label
                                for="max_working_days_per_week"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Hari Kerja per Minggu
                            </label>

                            <input
                                id="max_working_days_per_week"
                                name="max_working_days_per_week"
                                type="number"
                                min="1"
                                max="7"
                                step="1"
                                required
                                value="{{ old('max_working_days_per_week', $setting->max_working_days_per_week) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 1–7 hari per minggu.
                            </p>
                        </div>

                    </div>
                </section>

                {{-- PENGATURAN PRESENSI OTOMATIS --}}
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Pengaturan Presensi Otomatis
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Atur buffer otomatis, toleransi keterlambatan, mekanisme clock-out, dan batas radius validasi presensi.
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Buffer otomatis --}}
                        <div>
                            <label
                                for="auto_buffer_minutes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Buffer Otomatis
                            </label>

                            <input
                                id="auto_buffer_minutes"
                                name="auto_buffer_minutes"
                                type="number"
                                min="0"
                                max="120"
                                step="1"
                                required
                                value="{{ old('auto_buffer_minutes', $setting->auto_buffer_minutes) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam menit, rentang 0–120.
                            </p>
                        </div>

                        {{-- Toleransi keterlambatan --}}
                        <div>
                            <label
                                for="grace_period_minutes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Toleransi Keterlambatan
                            </label>

                            <input
                                id="grace_period_minutes"
                                name="grace_period_minutes"
                                type="number"
                                min="0"
                                max="60"
                                step="1"
                                required
                                value="{{ old('grace_period_minutes', $setting->grace_period_minutes) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam menit, rentang 0–60.
                            </p>
                        </div>

                        {{-- Batas waktu auto clock-out --}}
                        <div>
                            <label
                                for="auto_clockout_buffer_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Batas Waktu Auto Clock-out
                            </label>

                            <input
                                id="auto_clockout_buffer_hours"
                                name="auto_clockout_buffer_hours"
                                type="number"
                                min="1"
                                max="12"
                                step="1"
                                required
                                value="{{ old('auto_clockout_buffer_hours', $setting->auto_clockout_buffer_hours ?? 1) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam jam, rentang 1–12.
                            </p>
                        </div>

                        {{-- Radius Validasi (dipindah dari container Geofencing) --}}
                        <div>
                            <label
                                for="attendance_radius_meters"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Radius Validasi (Meter) <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="attendance_radius_meters"
                                name="attendance_radius_meters"
                                type="number"
                                min="5"
                                max="5000"
                                step="1"
                                required
                                x-model="branchRadius"
                                @input="onRadiusInput($event.target.value)"
                                value="{{ old('attendance_radius_meters', $setting->attendance_radius_meters ?? ($company->attendance_radius_meters ?? 100)) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"
                                placeholder="100"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Jarak maksimal karyawan dari kantor (contoh: 100m).
                            </p>
                        </div>

                        {{-- Aktifkan auto clock-out --}}
                        <div class="flex items-center gap-3 sm:col-span-2">
                            <input
                                type="hidden"
                                name="is_auto_clockout"
                                value="0"
                            >

                            <input
                                id="is_auto_clockout"
                                name="is_auto_clockout"
                                type="checkbox"
                                value="1"
                                @checked((bool) old('is_auto_clockout', $setting->is_auto_clockout))
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >

                            <label
                                for="is_auto_clockout"
                                class="text-sm font-medium text-gray-700"
                            >
                                Aktifkan Auto Clock-out
                            </label>
                        </div>

                    </div>
                </section>

                {{-- TOMBOL SIMPAN GLOBAL --}}
                <div
                    class="flex justify-end border-t border-gray-200 pt-6"
                    style="position: relative; z-index: 10;"
                >
                    <button
                        type="button"
                        id="btn-global-save"
                        onclick="handleGlobalSave()"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-bold shadow-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        style="background-color: #4f46e5 !important; color: #ffffff !important; opacity: 1 !important; border: 1px solid #4f46e5;"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                d="M16.707 5.293a1 1 0 0 1 0 1.414l-7.5 7.5a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L8.5 11.086l6.793-6.793a1 1 0 0 1 1.414 0Z"
                            />
                        </svg>

                        Simpan Pengaturan
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI AUTO CLOCK-OUT (di luar form, fixed overlay) --}}
    <div id="modal-auto-clockout"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
         style="display: none;"
         aria-modal="true" role="dialog">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-md w-full mx-4 overflow-hidden">
            {{-- Header Modal --}}
            <div class="flex items-center gap-3 p-6 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Fitur Auto Clock-out Belum Aktif</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Peringatan sebelum menyimpan pengaturan</p>
                </div>
            </div>
            {{-- Body Modal --}}
            <div class="p-6 pt-4">
                <p class="text-sm text-gray-600 leading-relaxed">
                    Anda belum mengaktifkan fitur <strong class="font-semibold text-gray-900">Auto Clock-out</strong>. Tanpa fitur ini, sistem <strong class="text-rose-700">tidak akan otomatis mencatat waktu pulang</strong> karyawan yang lupa melakukan Clock-Out, yang dapat memengaruhi akurasi laporan jam kerja.
                </p>
                <p class="text-sm text-gray-500 mt-3">
                    Apakah Anda tetap ingin menyimpan tanpa mengaktifkan Auto Clock-out?
                </p>
            </div>
            {{-- Footer Modal --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 p-6 pt-0">
                <button
                    type="button"
                    onclick="closeAutoClockoutModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal (Centang Kembali)
                </button>
                <button
                    type="button"
                    onclick="confirmSaveWithoutAutoClockout()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tetap Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
        /**
         * Global Save Handler — validasi checkbox Auto Clock-out sebelum submit.
         * Jika checkbox tidak dicentang, tampilkan modal konfirmasi.
         * Jika dicentang, langsung submit form pengaturan utama.
         */
        function handleGlobalSave() {
            const radiusInput = document.getElementById('attendance_radius_meters');
            if (radiusInput && (!radiusInput.value || parseInt(radiusInput.value) < 5)) {
                radiusInput.value = 100;
            }

            const autoClockoutCheckbox = document.getElementById('is_auto_clockout');
            const isAutoClockoutEnabled = autoClockoutCheckbox && autoClockoutCheckbox.checked;

            if (!isAutoClockoutEnabled) {
                // Tampilkan modal peringatan
                const modal = document.getElementById('modal-auto-clockout');
                if (modal) {
                    modal.style.removeProperty('display');
                    modal.style.display = 'flex';
                }
                return;
            }

            // Langsung submit form jika auto clock-out aktif
            const form = document.getElementById('form-global-settings');
            if (form) {
                form.submit();
            }
        }

        /**
         * Tutup modal konfirmasi auto clock-out (tombol "Batal").
         * User akan kembali ke form untuk mencentang checkbox.
         */
        function closeAutoClockoutModal() {
            const modal = document.getElementById('modal-auto-clockout');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        /**
         * Konfirmasi simpan tanpa auto clock-out aktif (tombol "Tetap Simpan").
         * Tutup modal dan langsung submit form.
         */
        function confirmSaveWithoutAutoClockout() {
            closeAutoClockoutModal();
            const radiusInput = document.getElementById('attendance_radius_meters');
            if (radiusInput && (!radiusInput.value || parseInt(radiusInput.value) < 5)) {
                radiusInput.value = 100;
            }
            const form = document.getElementById('form-global-settings');
            if (form) {
                form.submit();
            }
        }

        // Tutup modal saat klik overlay latar belakang
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modal-auto-clockout');
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        closeAutoClockoutModal();
                    }
                });
            }
        });

        function branchLocationManager(branches, initialSettings, initialBranchId) {
            const list = Array.isArray(branches) ? branches : [];
            const init = initialSettings || {};

            // Prioritaskan cabang yang dipilih dari URL / flash session (setelah simpan)
            let targetBranch = null;
            if (initialBranchId) {
                targetBranch = list.find(b => String(b.id) === String(initialBranchId));
            }
            if (!targetBranch && list.length > 0) {
                targetBranch = list[0];
            }

            const initialLat = (targetBranch && targetBranch.latitude !== null && targetBranch.latitude !== undefined && targetBranch.latitude !== '')
                ? parseFloat(targetBranch.latitude)
                : ((init.latitude !== null && init.latitude !== undefined && init.latitude !== '') ? parseFloat(init.latitude) : '');

            const initialLng = (targetBranch && targetBranch.longitude !== null && targetBranch.longitude !== undefined && targetBranch.longitude !== '')
                ? parseFloat(targetBranch.longitude)
                : ((init.longitude !== null && init.longitude !== undefined && init.longitude !== '') ? parseFloat(init.longitude) : '');

            const initialRadius = targetBranch && targetBranch.radius
                ? parseInt(targetBranch.radius)
                : (init.radius ? parseInt(init.radius) : 100);

            const initialAddress = targetBranch ? (targetBranch.address || '') : '';
            const initialName = targetBranch ? targetBranch.name : 'Kantor Utama';

            return {
                branches: list,
                selectedBranchId: targetBranch ? targetBranch.id : '',
                branchLatitude: initialLat,
                branchLongitude: initialLng,
                branchRadius: initialRadius,
                branchName: initialName,
                locationName: initialAddress,
                isSearching: false,
                autoFilledNotice: '',
                searchResults: [],
                showDropdown: false,
                searchErrorMessage: '',
                map: null,
                marker: null,
                circle: null,
                searchAbortController: null,

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                    window.addEventListener('resize', () => {
                        if (this.map) {
                            setTimeout(() => this.map.invalidateSize(), 200);
                        }
                    });
                },

                hasValidCoordinates() {
                    if (this.branchLatitude === '' || this.branchLongitude === '' || this.branchLatitude === null || this.branchLongitude === null) {
                        return false;
                    }
                    const lat = parseFloat(this.branchLatitude);
                    const lng = parseFloat(this.branchLongitude);
                    return !isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
                },

                initMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 150);
                        return;
                    }
                    if (this.map) return;
                    const container = document.getElementById('branch-geofence-map');
                    if (!container) return;

                    const valid = this.hasValidCoordinates();
                    const initialLat = valid ? parseFloat(this.branchLatitude) : -2.5489;
                    const initialLng = valid ? parseFloat(this.branchLongitude) : 118.0149;
                    const initialZoom = valid ? 16 : 5;
                    const radius = parseInt(this.branchRadius) || 100;

                    this.map = L.map('branch-geofence-map', {
                        center: [initialLat, initialLng],
                        zoom: initialZoom,
                        zoomControl: false,
                        attributionControl: false
                    });

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);

                    L.control.zoom({ position: 'topright' }).addTo(this.map);

                    if (valid) {
                        this.renderMarkerAndCircle(initialLat, initialLng, radius);
                    }

                    // Click map listener
                    this.map.on('click', (e) => {
                        const { lat, lng } = e.latlng;
                        this.setLocation({
                            lat: lat,
                            lng: lng,
                            reverseGeocode: true,
                            source: 'Klik Peta'
                        });
                    });

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 300);
                },

                renderMarkerAndCircle(lat, lng, radius) {
                    if (!this.map || typeof L === 'undefined') return;

                    const branchPinIcon = L.divIcon({
                        className: 'custom-branch-pin',
                        html: `
                            <div style="width: 36px; height: 46px; display: flex; align-items: center; justify-content: center; cursor: grab;">
                                <svg width="36" height="46" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 3px 6px rgba(79, 70, 229, 0.45));">
                                    <path d="M12 0C5.37258 0 0 5.37258 0 12C0 21 12 32 12 32C12 32 24 21 24 12C24 5.37258 18.6274 0 12 0Z" fill="#4F46E5"/>
                                    <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                                    <circle cx="12" cy="11" r="2.8" fill="#4F46E5"/>
                                </svg>
                            </div>
                        `,
                        iconSize: [36, 46],
                        iconAnchor: [18, 46],
                        popupAnchor: [0, -46]
                    });

                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], {
                            icon: branchPinIcon,
                            draggable: true
                        }).addTo(this.map);

                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.setLocation({
                                lat: pos.lat,
                                lng: pos.lng,
                                reverseGeocode: true,
                                source: 'Geser Pin'
                            });
                        });
                    }

                    const r = parseInt(radius || this.branchRadius) || 100;
                    if (this.circle) {
                        this.circle.setLatLng([lat, lng]);
                        this.circle.setRadius(r);
                    } else {
                        this.circle = L.circle([lat, lng], {
                            radius: r,
                            color: '#4F46E5',
                            fillColor: '#4F46E5',
                            fillOpacity: 0.15,
                            weight: 2,
                            dashArray: '6, 6'
                        }).addTo(this.map);
                    }

                    this.updatePopup();
                },

                updatePopup() {
                    if (!this.marker) return;
                    const name = this.branchName || 'Cabang';
                    const addr = this.locationName || 'Titik koordinat presensi';
                    this.marker.bindPopup(`<div style="font-size: 12px; line-height: 1.4;"><strong>${name}</strong><br><span style="color: #64748b;">${addr}</span></div>`);
                },

                /**
                 * Centralized single source of truth method to update location.
                 */
                setLocation({ lat, lng, address = null, radius = null, fly = true, reverseGeocode = false, source = '' }) {
                    const cleanLat = parseFloat(Number(lat).toFixed(7));
                    const cleanLng = parseFloat(Number(lng).toFixed(7));
                    if (isNaN(cleanLat) || isNaN(cleanLng)) return;

                    this.branchLatitude = cleanLat;
                    this.branchLongitude = cleanLng;

                    if (address !== null) {
                        this.locationName = address;
                    }
                    if (radius !== null) {
                        this.branchRadius = parseInt(radius);
                    }

                    if (this.map) {
                        this.renderMarkerAndCircle(cleanLat, cleanLng, this.branchRadius);
                        if (fly) {
                            this.map.flyTo([cleanLat, cleanLng], 16, { duration: 1.0 });
                        } else {
                            this.map.setView([cleanLat, cleanLng], 16);
                        }
                    }

                    this.syncBranchObject();

                    if (source) {
                        this.autoFilledNotice = `Lokasi disinkronkan (${source}): ${cleanLat}, ${cleanLng}`;
                        setTimeout(() => { this.autoFilledNotice = ''; }, 3500);
                    }

                    if (reverseGeocode) {
                        this.fetchReverseGeocode(cleanLat, cleanLng);
                    }
                },

                syncBranchObject() {
                    const idx = this.branches.findIndex(item => String(item.id) === String(this.selectedBranchId));
                    if (idx !== -1) {
                        this.branches[idx].address = this.locationName;
                        this.branches[idx].latitude = this.branchLatitude !== '' ? parseFloat(this.branchLatitude) : null;
                        this.branches[idx].longitude = this.branchLongitude !== '' ? parseFloat(this.branchLongitude) : null;
                        this.branches[idx].radius = parseInt(this.branchRadius) || 100;
                        this.branches = [...this.branches];
                    }
                },

                onBranchChange() {
                    const found = this.branches.find(item => String(item.id) === String(this.selectedBranchId));
                    if (!found) return;

                    this.branchName = found.name;
                    this.locationName = found.address || '';
                    this.branchRadius = found.radius ? parseInt(found.radius) : 100;

                    const hasCoords = found.latitude !== null && found.longitude !== null && found.latitude !== '' && found.longitude !== '' && !isNaN(parseFloat(found.latitude)) && !isNaN(parseFloat(found.longitude));

                    if (hasCoords) {
                        this.branchLatitude = parseFloat(Number(found.latitude).toFixed(7));
                        this.branchLongitude = parseFloat(Number(found.longitude).toFixed(7));
                        if (this.map) {
                            this.renderMarkerAndCircle(this.branchLatitude, this.branchLongitude, this.branchRadius);
                            this.map.flyTo([this.branchLatitude, this.branchLongitude], 16, { duration: 1.0 });
                        }
                    } else {
                        this.branchLatitude = '';
                        this.branchLongitude = '';
                        if (this.marker && this.map) {
                            this.map.removeLayer(this.marker);
                            this.marker = null;
                        }
                        if (this.circle && this.map) {
                            this.map.removeLayer(this.circle);
                            this.circle = null;
                        }
                    }

                    this.autoFilledNotice = `Cabang ${found.name} dipilih.`;
                    setTimeout(() => { this.autoFilledNotice = ''; }, 3500);
                },

                tryParseCoordPair(str) {
                    if (!str || typeof str !== 'string') return false;
                    const cleaned = str.replace(/[()]/g, '').trim();
                    const parts = cleaned.split(/[,;\s]+/).map(p => p.replace(',', '.').trim()).filter(Boolean);
                    if (parts.length >= 2) {
                        const lat = parseFloat(parts[0]);
                        const lng = parseFloat(parts[1]);
                        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                            this.setLocation({
                                lat: lat,
                                lng: lng,
                                reverseGeocode: true,
                                source: 'Deteksi Koordinat'
                            });
                            return true;
                        }
                    }
                    return false;
                },

                async searchLocations(query, forceSearch = false) {
                    const q = String(query || '').trim();
                    if (!q) {
                        this.searchResults = [];
                        this.showDropdown = false;
                        return;
                    }

                    if (this.tryParseCoordPair(q)) {
                        this.showDropdown = false;
                        return;
                    }

                    if (q.length < 3 && !forceSearch) {
                        return;
                    }

                    if (this.searchAbortController) {
                        this.searchAbortController.abort();
                    }
                    this.searchAbortController = new AbortController();

                    this.isSearching = true;
                    this.searchErrorMessage = '';
                    this.showDropdown = true;
                    const results = [];

                    try {
                        const endpoint = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=6&addressdetails=1&countrycodes=id`;
                        let res = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json' },
                            signal: this.searchAbortController.signal
                        });
                        let data = [];
                        if (res.ok) {
                            data = await res.json();
                        }
                        if (!data || data.length === 0) {
                            const fallbackEndpoint = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=6&addressdetails=1`;
                            const fbRes = await fetch(fallbackEndpoint, {
                                headers: { 'Accept': 'application/json' },
                                signal: this.searchAbortController.signal
                            });
                            if (fbRes.ok) {
                                data = await fbRes.json();
                            }
                        }

                        if (Array.isArray(data)) {
                            data.forEach(item => {
                                const parts = (item.display_name || '').split(',');
                                const title = parts[0] ? parts[0].trim() : item.display_name;
                                const subtitle = parts.slice(1).join(',').trim();
                                results.push({
                                    title: title,
                                    subtitle: subtitle || item.display_name,
                                    display_name: item.display_name,
                                    lat: parseFloat(item.lat),
                                    lon: parseFloat(item.lon),
                                    source: 'OpenStreetMap'
                                });
                            });
                        }
                    } catch (e) {
                        if (e.name !== 'AbortError') {
                            console.warn('Geocoding search failed:', e);
                        }
                    } finally {
                        this.isSearching = false;
                    }

                    // Deduplikasi hasil
                    const unique = [];
                    const seen = new Set();
                    for (const r of results) {
                        const key = `${r.lat.toFixed(4)},${r.lon.toFixed(4)}`;
                        if (!seen.has(key)) {
                            seen.add(key);
                            unique.push(r);
                        }
                    }

                    this.searchResults = unique.slice(0, 6);
                    if (this.searchResults.length === 0 && !this.isSearching) {
                        this.searchErrorMessage = `Tidak ada hasil rekomendasi untuk "${q}". Coba kata kunci yang lebih spesifik atau sesuaikan pin di peta.`;
                    }
                },

                selectLocation(item) {
                    let addr = item.display_name || item.title || item.address || '';
                    if (addr.length > 250) addr = addr.substring(0, 247) + '...';

                    this.setLocation({
                        lat: item.lat,
                        lng: item.lon || item.lng,
                        address: addr,
                        source: item.title || addr.split(',')[0],
                        reverseGeocode: false
                    });

                    this.showDropdown = false;
                    this.searchResults = [];
                },

                async fetchReverseGeocode(lat, lng) {
                    this.isSearching = true;
                    try {
                        const endpoint = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1`;
                        const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.display_name) {
                                let addr = data.display_name;
                                if (addr.length > 250) addr = addr.substring(0, 247) + '...';
                                this.locationName = addr;
                                this.syncBranchObject();
                                this.updatePopup();
                                this.autoFilledNotice = `Alamat sinkron: ${addr.split(',')[0]}`;
                                setTimeout(() => { this.autoFilledNotice = ''; }, 3500);
                            }
                        }
                    } catch (e) {
                        console.warn('Reverse geocode failed:', e);
                    } finally {
                        this.isSearching = false;
                    }
                },

                onLatitudeInput(val) {
                    if (val === null || val === undefined || val === '') {
                        this.branchLatitude = '';
                        return;
                    }
                    if (this.tryParseCoordPair(String(val))) return;

                    const cleanStr = String(val).replace(',', '.').trim();
                    const num = parseFloat(cleanStr);
                    this.branchLatitude = cleanStr;
                    if (!isNaN(num) && num >= -90 && num <= 90) {
                        if (this.hasValidCoordinates()) {
                            this.renderMarkerAndCircle(num, parseFloat(this.branchLongitude), this.branchRadius);
                        }
                        this.syncBranchObject();
                    }
                },

                onLongitudeInput(val) {
                    if (val === null || val === undefined || val === '') {
                        this.branchLongitude = '';
                        return;
                    }
                    if (this.tryParseCoordPair(String(val))) return;

                    const cleanStr = String(val).replace(',', '.').trim();
                    const num = parseFloat(cleanStr);
                    this.branchLongitude = cleanStr;
                    if (!isNaN(num) && num >= -180 && num <= 180) {
                        if (this.hasValidCoordinates()) {
                            this.renderMarkerAndCircle(parseFloat(this.branchLatitude), num, this.branchRadius);
                        }
                        this.syncBranchObject();
                    }
                },

                onRadiusInput(val) {
                    const r = parseInt(val);
                    if (!isNaN(r)) {
                        this.branchRadius = r;
                        if (this.circle) {
                            this.circle.setRadius(r);
                        }
                        this.syncBranchObject();
                    }
                },

                prepareSubmit() {
                    if (this.branchLatitude !== null && this.branchLatitude !== '') {
                        this.branchLatitude = String(this.branchLatitude).replace(',', '.').trim();
                    }
                    if (this.branchLongitude !== null && this.branchLongitude !== '') {
                        this.branchLongitude = String(this.branchLongitude).replace(',', '.').trim();
                    }
                    const r = parseInt(this.branchRadius);
                    this.branchRadius = (!isNaN(r) && r >= 5) ? r : 100;
                }
            };
        }
    </script>
</x-app-layout>