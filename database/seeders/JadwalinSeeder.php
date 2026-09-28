<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JadwalinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // 1. Perusahaan dummy
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'PT Senja Wisata Nusantara',
            'email' => 'kontak@senjawisata.co.id',
            'phone_number' => '021-5550199',
            'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
            'timezone' => 'Asia/Makassar',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Pengaturan Perusahaan
        DB::table('company_settings')->insert([
            'company_id' => $companyId,
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'auto_buffer_minutes' => 30,
            'grace_period_minutes' => 15,
            'is_auto_clockout' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Departemen
        $deptId = DB::table('departments')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Food & Beverage',
            'description' => 'Divisi operasional restoran dan kafe',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 4. Posisi / Jabatan
        $posId = DB::table('positions')->insertGetId([
            'company_id' => $companyId,
            'department_id' => $deptId,
            'name' => 'Barista',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 5. Tiga Akun Pengguna Pancingan (Superadmin, Manager, Karyawan)
        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'company_id' => $companyId,
                'department_id' => null,
                'position_id' => null,
                'is_global_manager' => false,
                'name' => 'Budi Hartono',
                'email' => 'owner@senjawisata.co.id',
                'email_verified_at' => $now,
                'password' => $defaultPassword,
                'role' => 'superadmin',
                'phone_number' => '081234567890',
                'join_date' => '2024-01-01',
                'status' => 'active',
                'profile_photo' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_id' => $companyId,
                'department_id' => $deptId,
                'position_id' => null,
                'is_global_manager' => false,
                'name' => 'Siti Nurhaliza',
                'email' => 'manager@senjawisata.co.id',
                'email_verified_at' => $now,
                'password' => $defaultPassword,
                'role' => 'manager',
                'phone_number' => '081234567891',
                'join_date' => '2024-01-15',
                'status' => 'active',
                'profile_photo' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_id' => $companyId,
                'department_id' => $deptId,
                'position_id' => $posId,
                'is_global_manager' => false,
                'name' => 'Dewi Lestari',
                'email' => 'karyawan@senjawisata.co.id',
                'email_verified_at' => $now,
                'password' => $defaultPassword,
                'role' => 'employee',
                'phone_number' => '081234567892',
                'join_date' => '2024-02-01',
                'status' => 'active',
                'profile_photo' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('users')->insert($users);
    }
}
