<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE shift_assignments DROP CONSTRAINT IF EXISTS shift_assignments_status_check');
            DB::statement("ALTER TABLE shift_assignments ADD CONSTRAINT shift_assignments_status_check CHECK (status::text IN ('scheduled', 'present', 'absent', 'late', 'completed'))");
        } else {
            Schema::table('shift_assignments', function (Blueprint $table) {
                $table->string('status', 30)->default('scheduled')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE shift_assignments DROP CONSTRAINT IF EXISTS shift_assignments_status_check');
            DB::statement("ALTER TABLE shift_assignments ADD CONSTRAINT shift_assignments_status_check CHECK (status::text IN ('scheduled', 'present', 'absent', 'late'))");
        }
    }
};
