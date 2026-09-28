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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->after('company_id')->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
            $table->boolean('is_global_manager')->default(false)->after('position_id');
            $table->enum('role', ['superadmin', 'manager', 'employee'])->default('employee')->after('password');
            $table->string('phone_number', 20)->nullable()->after('role');
            $table->date('join_date')->nullable()->after('phone_number');
            $table->enum('status', ['active', 'on_leave', 'resigned'])->default('active')->after('join_date');
            $table->string('profile_photo')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn([
                'company_id', 'department_id', 'position_id', 'is_global_manager', 
                'role', 'phone_number', 'join_date', 'status', 'profile_photo', 'deleted_at'
            ]);
        });
    }
};
