<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * No-op: Kolom kepatuhan ketenagakerjaan (work_hour_limit_type, min_rest_hours,
     * dan max_working_days_per_week) pada tabel company_settings sudah dibuat oleh
     * migration 2026_10_02_000001_add_gap_adjustments_to_jadwalin_tables.php.
     */
    public function up(): void
    {
        // No-op: Kolom kepatuhan sudah dibuat oleh migration 2 Oktober 2026
        // (2026_10_02_000001_add_gap_adjustments_to_jadwalin_tables.php).
    }

    /**
     * Reverse the migrations.
     *
     * No-op: Migration ini tidak boleh menghapus kolom kepatuhan karena kolom
     * tersebut merupakan milik resmi migration 2 Oktober 2026 dan digunakan
     * aktif oleh aplikasi.
     */
    public function down(): void
    {
        // No-op: Tidak menghapus kolom milik migration 2 Oktober 2026
        // untuk mencegah kehilangan data pengaturan kepatuhan perusahaan.
    }
};
