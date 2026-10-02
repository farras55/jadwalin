<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Penyesuaian shift_swaps (Mendukung Dual-Mode: Transfer vs Barter)
        Schema::table('shift_swaps', function (Blueprint $table) {
            $table->foreignId('target_shift_assignment_id')
                ->nullable()
                ->after('shift_assignment_id')
                ->constrained('shift_assignments')
                ->nullOnDelete();
            $table->enum('swap_type', ['transfer', 'exchange'])
                ->default('transfer')
                ->after('target_shift_assignment_id');
        });

        // 2. Penyesuaian shifts (Mendukung Open Shift Hybrid & Tanggal Merah)
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('requires_approval')
                ->default(true)
                ->after('is_open_shift');
            $table->boolean('is_holiday')
                ->default(false)
                ->after('requires_approval');
        });

        // 3. Penyesuaian open_shift_claims (Anti-Spam Duplicate Claim)
        Schema::table('open_shift_claims', function (Blueprint $table) {
            $table->unique(['shift_id', 'user_id'], 'unique_open_shift_user_claim');
        });

        // 4. Penyesuaian company_settings (Opsi Batas PP 35, Jeda Istirahat & Hari Libur)
        Schema::table('company_settings', function (Blueprint $table) {
            $table->enum('work_hour_limit_type', ['weekly', 'daily'])
                ->default('weekly')
                ->after('max_overtime_hours');
            $table->integer('min_rest_hours')
                ->default(11)
                ->after('work_hour_limit_type');
            $table->integer('max_working_days_per_week')
                ->default(6)
                ->after('min_rest_hours');
        });

        // 5. Penyesuaian shift_assignments (Flag Auto Clock-Out & Status Lembur)
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->boolean('is_auto_clockout')
                ->default(false)
                ->after('status');
            $table->enum('overtime_status', ['none', 'pending_approval', 'approved', 'rejected'])
                ->default('none')
                ->after('is_overtime');
        });

        // 6. Penyesuaian users (Pembeda Tipe Karyawan & Batas Jam Part-Time)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('employment_type', ['full_time', 'part_time', 'freelance'])
                ->default('full_time')
                ->after('role');
            $table->integer('max_weekly_hours')
                ->nullable()
                ->after('employment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_type', 'max_weekly_hours']);
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn(['is_auto_clockout', 'overtime_status']);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['work_hour_limit_type', 'min_rest_hours', 'max_working_days_per_week']);
        });

        Schema::table('open_shift_claims', function (Blueprint $table) {
            $table->dropUnique('unique_open_shift_user_claim');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'is_holiday']);
        });

        Schema::table('shift_swaps', function (Blueprint $table) {
            $table->dropForeign(['target_shift_assignment_id']);
            $table->dropColumn(['target_shift_assignment_id', 'swap_type']);
        });
    }
};
