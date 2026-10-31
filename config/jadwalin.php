<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pengaturan Default Lokasi & Geofencing Presensi
    |--------------------------------------------------------------------------
    |
    | Konfigurasi fallback terpusat untuk lokasi presensi jika database
    | belum dikonfigurasi oleh admin. Tidak boleh ada angka koordinat
    | hardcode di controller atau layer view.
    |
    */
    'attendance' => [
        'default_radius_meters' => (int) env('DEFAULT_ATTENDANCE_RADIUS', 100),
        'min_radius_meters' => 5,
        'max_radius_meters' => 10000,
        'default_latitude' => env('DEFAULT_OFFICE_LATITUDE', null),
        'default_longitude' => env('DEFAULT_OFFICE_LONGITUDE', null),
        'default_address' => env('DEFAULT_OFFICE_ADDRESS', null),
        'default_name' => env('DEFAULT_OFFICE_NAME', 'Kantor Utama'),
    ],
];
