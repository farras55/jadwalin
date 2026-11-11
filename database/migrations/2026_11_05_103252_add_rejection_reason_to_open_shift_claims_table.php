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
        Schema::table('open_shift_claims', function (Blueprint $table) {
            if (! Schema::hasColumn('open_shift_claims', 'rejection_reason')) {
                $table->string('rejection_reason', 255)->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('open_shift_claims', function (Blueprint $table) {
            if (Schema::hasColumn('open_shift_claims', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};

