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
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->index(['user_id', 'clock_in_time'], 'shift_assignments_user_clock_in_idx');
            $table->index(['shift_id', 'status'], 'shift_assignments_shift_status_idx');
            $table->index(['company_id', 'status'], 'shift_assignments_company_status_idx');
            $table->index('clock_in_time', 'shift_assignments_clock_in_idx');
            $table->index('clock_out_time', 'shift_assignments_clock_out_idx');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->index(['company_id', 'start_time'], 'shifts_company_start_time_idx');
            $table->index(['department_id', 'start_time'], 'shifts_dept_start_time_idx');
            $table->index('start_time', 'shifts_start_time_idx');
            $table->index('end_time', 'shifts_end_time_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'notifications_user_is_read_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'audit_logs_company_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_company_created_at_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_is_read_idx');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex('shifts_company_start_time_idx');
            $table->dropIndex('shifts_dept_start_time_idx');
            $table->dropIndex('shifts_start_time_idx');
            $table->dropIndex('shifts_end_time_idx');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropIndex('shift_assignments_user_clock_in_idx');
            $table->dropIndex('shift_assignments_shift_status_idx');
            $table->dropIndex('shift_assignments_company_status_idx');
            $table->dropIndex('shift_assignments_clock_in_idx');
            $table->dropIndex('shift_assignments_clock_out_idx');
        });
    }
};
