<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendance_regularizations DROP CONSTRAINT IF EXISTS attendance_regularizations_overtime_status_check');
            DB::statement("ALTER TABLE attendance_regularizations ADD CONSTRAINT attendance_regularizations_overtime_status_check CHECK (overtime_status::text = ANY (ARRAY['none'::character varying, 'pending_approval'::character varying, 'approved'::character varying, 'rejected'::character varying, 'regular'::character varying]::text[]))");

            DB::statement('ALTER TABLE shift_assignments DROP CONSTRAINT IF EXISTS shift_assignments_overtime_status_check');
            DB::statement("ALTER TABLE shift_assignments ADD CONSTRAINT shift_assignments_overtime_status_check CHECK (overtime_status::text = ANY (ARRAY['none'::character varying, 'pending_approval'::character varying, 'approved'::character varying, 'rejected'::character varying, 'regular'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendance_regularizations DROP CONSTRAINT IF EXISTS attendance_regularizations_overtime_status_check');
            DB::statement("ALTER TABLE attendance_regularizations ADD CONSTRAINT attendance_regularizations_overtime_status_check CHECK (overtime_status::text = ANY (ARRAY['none'::character varying, 'pending_approval'::character varying, 'approved'::character varying, 'rejected'::character varying]::text[]))");

            DB::statement('ALTER TABLE shift_assignments DROP CONSTRAINT IF EXISTS shift_assignments_overtime_status_check');
            DB::statement("ALTER TABLE shift_assignments ADD CONSTRAINT shift_assignments_overtime_status_check CHECK (overtime_status::text = ANY (ARRAY['none'::character varying, 'pending_approval'::character varying, 'approved'::character varying, 'rejected'::character varying]::text[]))");
        }
    }
};
