<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_settings', 'auto_clockout_buffer_hours')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->unsignedTinyInteger('auto_clockout_buffer_hours')
                    ->default(1);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_settings', 'auto_clockout_buffer_hours')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('auto_clockout_buffer_hours');
            });
        }
    }
};
