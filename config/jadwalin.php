<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pemetaan Zona Waktu Operasional
    |--------------------------------------------------------------------------
    |
    | Pemetaan identifier timezone standar IANA ke singkatan zona waktu resmi
    | Indonesia. Fallback dinamis mendukung WIB, WITA (+08), dan WIT (+09).
    |
    */
    'timezones' => [
        'Asia/Jakarta' => 'WIB',
        'Asia/Pontianak' => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Ujung_Pandang' => 'WITA',
        'Asia/Jayapura' => 'WIT',
        'default' => 'WIB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Default Lokasi & Geofencing Presensi
    |--------------------------------------------------------------------------
    |
    | Konfigurasi fallback terpusat untuk lokasi presensi jika database
    | belum dikonfigurasi oleh admin.
    | allow_mock_gps default true untuk kelancaran demo PBL / pengujian laptop.
    |
    */
    'attendance' => [
        'allow_mock_gps' => (bool) env('ALLOW_MOCK_GPS', true),
        'is_ci_or_testing' => (bool) env('CI', false) || (bool) env('PLAYWRIGHT_TEST', false),
        'default_radius_meters' => (int) env('DEFAULT_ATTENDANCE_RADIUS', 100),
        'min_radius_meters' => 5,
        'max_radius_meters' => 10000,
        'default_latitude' => env('DEFAULT_OFFICE_LATITUDE', null),
        'default_longitude' => env('DEFAULT_OFFICE_LONGITUDE', null),
        'default_address' => env('DEFAULT_OFFICE_ADDRESS', null),
        'default_name' => env('DEFAULT_OFFICE_NAME', 'Kantor Utama'),
        'auto_clock_out_buffer_minutes' => (int) env('AUTO_CLOCK_OUT_BUFFER_MINUTES', 30),
        'auto_clock_out_buffer_hours' => (int) env('AUTO_CLOCK_OUT_BUFFER_HOURS', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Batasan Kepatuhan Ketenagakerjaan (PP No. 35 / 2021)
    |--------------------------------------------------------------------------
    */
    'compliance' => [
        'max_daily_hours' => (int) env('COMPLIANCE_MAX_DAILY_HOURS', 8),
        'max_weekly_hours' => (int) env('COMPLIANCE_MAX_WEEKLY_HOURS', 40),
        'min_rest_hours_between_shifts' => (int) env('COMPLIANCE_MIN_REST_HOURS', 11),
        'consecutive_work_days_limit' => (int) env('COMPLIANCE_CONSECUTIVE_DAYS_LIMIT', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Pagination Terpusat
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default' => (int) env('PAGINATION_DEFAULT', 15),
        'audit_logs' => (int) env('PAGINATION_AUDIT_LOGS', 20),
        'timesheets' => (int) env('PAGINATION_TIMESHEETS', 15),
        'users' => (int) env('PAGINATION_USERS', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand Palette
    |--------------------------------------------------------------------------
    */
    'branding' => [
        'primary_color' => '#4F46E5', // Indigo 600
        'primary_hover' => '#4338CA', // Indigo 700
    ],

    /*
    |--------------------------------------------------------------------------
    | Aset Eksternal & Peta Geografis (Dapat dialihkan ke lokal/offline via .env)
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'leaflet_css' => env('LEAFLET_CSS_URL', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
        'leaflet_js' => env('LEAFLET_JS_URL', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
    ],
];
