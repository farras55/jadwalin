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
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('office_latitude', 10, 7)->nullable()->default(-6.2383200)->after('address');
            $table->decimal('office_longitude', 11, 7)->nullable()->default(106.8122800)->after('office_latitude');
            $table->integer('attendance_radius_meters')->default(50)->after('office_longitude');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('office_latitude', 10, 7)->nullable()->default(-6.2383200)->after('grace_period_minutes');
            $table->decimal('office_longitude', 11, 7)->nullable()->default(106.8122800)->after('office_latitude');
            $table->integer('attendance_radius_meters')->default(50)->after('office_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['office_latitude', 'office_longitude', 'attendance_radius_meters']);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['office_latitude', 'office_longitude', 'attendance_radius_meters']);
        });
    }
};
