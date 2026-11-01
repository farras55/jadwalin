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
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('departments', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('departments', 'address')) {
                $table->string('address')->nullable();
            }
            if (! Schema::hasColumn('departments', 'radius')) {
                $table->integer('radius')->default(100);
            }
        });

        // Pastikan baris data yang sudah ada memiliki default radius
        if (Schema::hasColumn('departments', 'radius')) {
            DB::table('departments')
                ->whereNull('radius')
                ->update(['radius' => 100]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'radius')) {
                $table->dropColumn('radius');
            }
        });
    }
};
