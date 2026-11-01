<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JadwalinSeeder extends Seeder
{
    /**
     * Membuat data awal perusahaan, pengaturan, departemen,
     * posisi, dan akun pengujian.
     *
     * Seeder dapat dijalankan berulang kali tanpa membuat
     * duplikasi untuk data yang menggunakan kunci pencarian sama.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            /*
             * 1. Perusahaan
             */
            DB::table('companies')->updateOrInsert(
                ['email' => 'kontak@senjawisata.co.id'],
                [
                    'name' => 'PT Senja Wisata Nusantara',
                    'phone_number' => '021-5550199',
                    'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
                    'timezone' => 'Asia/Makassar',
                    'status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $company = DB::table('companies')
                ->where('email', 'kontak@senjawisata.co.id')
                ->first();

            if (! $company) {
                throw new \RuntimeException(
                    'Gagal membuat atau mengambil perusahaan pengujian.'
                );
            }

            $companyId = $company->id;

            /*
             * 2. Pengaturan perusahaan
             */
            DB::table('company_settings')->updateOrInsert(
                ['company_id' => $companyId],
                [
                    'max_regular_hours' => 40,
                    'max_overtime_hours' => 18,
                    'auto_buffer_minutes' => 30,
                    'grace_period_minutes' => 15,
                    'is_auto_clockout' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            /*
             * 3. Departemen
             */
            DB::table('departments')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'name' => 'Food & Beverage',
                ],
                [
                    'description' => 'Divisi operasional restoran dan kafe',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $department = DB::table('departments')
                ->where('company_id', $companyId)
                ->where('name', 'Food & Beverage')
                ->first();

            if (! $department) {
                throw new \RuntimeException(
                    'Gagal membuat atau mengambil departemen pengujian.'
                );
            }

            $departmentId = $department->id;

            /*
             * 4. Posisi / jabatan
             */
            DB::table('positions')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'department_id' => $departmentId,
                    'name' => 'Barista',
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $position = DB::table('positions')
                ->where('company_id', $companyId)
                ->where('department_id', $departmentId)
                ->where('name', 'Barista')
                ->first();

            if (! $position) {
                throw new \RuntimeException(
                    'Gagal membuat atau mengambil posisi pengujian.'
                );
            }

            $positionId = $position->id;

            /*
             * 5. Akun pengujian
             *
             * Password untuk ketiga akun: password123
             */
            $defaultPassword = Hash::make('password123');

            $users = [
                [
                    'name' => 'Budi Hartono',
                    'email' => 'owner@senjawisata.co.id',
                    'role' => 'superadmin',
                    'company_id' => $companyId,
                    'department_id' => null,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567890',
                    'join_date' => '2024-01-01',
                ],
                [
                    'name' => 'Siti Nurhaliza',
                    'email' => 'manager@senjawisata.co.id',
                    'role' => 'manager',
                    'company_id' => $companyId,
                    'department_id' => $departmentId,
                    'position_id' => null,
                    'is_global_manager' => true,
                    'phone_number' => '081234567891',
                    'join_date' => '2024-01-15',
                ],
                [
                    'name' => 'Dewi Lestari',
                    'email' => 'karyawan@senjawisata.co.id',
                    'role' => 'employee',
                    'company_id' => $companyId,
                    'department_id' => $departmentId,
                    'position_id' => $positionId,
                    'is_global_manager' => false,
                    'phone_number' => '081234567892',
                    'join_date' => '2024-02-01',
                ],
            ];

            foreach ($users as $user) {
                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    [
                        'company_id' => $user['company_id'],
                        'department_id' => $user['department_id'],
                        'position_id' => $user['position_id'],
                        'is_global_manager' => $user['is_global_manager'],
                        'name' => $user['name'],
                        'email_verified_at' => $now,
                        'password' => $defaultPassword,
                        'role' => $user['role'],
                        'phone_number' => $user['phone_number'],
                        'join_date' => $user['join_date'],
                        'status' => 'active',
                        'profile_photo' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }
}
