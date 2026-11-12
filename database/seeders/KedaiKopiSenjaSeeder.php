<?php

namespace Database\Seeders;

use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KedaiKopiSenjaSeeder extends Seeder
{
    /**
     * Seed master data untuk Kedai Kopi Senja (Kafe Operasional 24 Jam):
     * - Perusahaan & Regulasi (PP 35/2021)
     * - 5 Departemen (Bar, Kitchen, Kasir, Waiters, Media)
     * - Posisi Jabatan (Head & Staff per divisi)
     * - Template Shift 24 Jam dengan kuota presisi (Bar: 2-3-2, Kitchen: 2-3-2, Waiters: 1-2-1, Kasir: 1-1-1, Media: 1)
     * - 38 Akun Pengguna (1 Owner, 1 GM, 5 Head Divisi, 31 Staf)
     * - Availabilities Lengkap Mingguan Tetap (Jadwal Kuliah Part-Time & Variasi Non-Kuliah)
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $defaultPassword = Hash::make('password123');

            // =========================================================================
            // 1. Perusahaan (Kedai Kopi Senja)
            // =========================================================================
            $companyData = [
                'name' => 'Kedai Kopi Senja',
                'phone_number' => '0341-555024',
                'address' => 'Jl. Soekarno Hatta No.9, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'timezone' => 'Asia/Jakarta',
                'status' => 'active',
                'office_latitude' => -7.9467780,
                'office_longitude' => 112.6159870,
                'attendance_radius_meters' => 100,
                'updated_at' => $now,
                'created_at' => $now,
            ];

            DB::table('companies')->updateOrInsert(
                ['email' => 'kontak@kedaikopisenja.id'],
                $companyData
            );

            $company = DB::table('companies')
                ->where('email', 'kontak@kedaikopisenja.id')
                ->first();

            if (! $company) {
                throw new \RuntimeException('Gagal membuat atau mengambil tenant Kedai Kopi Senja.');
            }

            $companyId = $company->id;

            // =========================================================================
            // 2. Pengaturan Perusahaan & Regulasi Ketenagakerjaan (PP No. 35/2021)
            // =========================================================================
            DB::table('company_settings')->updateOrInsert(
                ['company_id' => $companyId],
                [
                    'max_regular_hours' => 48,
                    'max_overtime_hours' => 18,
                    'work_hour_limit_type' => 'weekly',
                    'min_rest_hours' => 11,
                    'max_working_days_per_week' => 6,
                    'auto_buffer_minutes' => 30,
                    'grace_period_minutes' => 15,
                    'is_auto_clockout' => true,
                    'auto_clockout_buffer_hours' => 2,
                    'office_latitude' => -7.9467780,
                    'office_longitude' => 112.6159870,
                    'attendance_radius_meters' => 100,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            // =========================================================================
            // 3. Departemen
            // =========================================================================
            $departmentDefinitions = [
                'Bar' => [
                    'description' => 'Divisi operasional bar, racikan kopi, espresso bar, dan beverages (24 Jam).',
                ],
                'Kitchen' => [
                    'description' => 'Divisi operasional dapur, makanan berat, snack, dan persiapan bahan (24 Jam).',
                ],
                'Kasir' => [
                    'description' => 'Divisi kasir, transaksi pembayaran, billing, dan laporan shift kasir (24 Jam).',
                ],
                'Waiters' => [
                    'description' => 'Divisi pelayanan meja, seating pelanggan, kebersihan area dine-in, dan runner (24 Jam).',
                ],
                'Media' => [
                    'description' => 'Divisi kreatif, konten media sosial, promosi digital, dan dokumentasi.',
                ],
            ];

            $departments = [];
            foreach ($departmentDefinitions as $deptName => $deptInfo) {
                DB::table('departments')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'name' => $deptName,
                    ],
                    [
                        'description' => $deptInfo['description'],
                        'latitude' => -7.9467780,
                        'longitude' => 112.6159870,
                        'radius_meters' => 100,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $departments[$deptName] = DB::table('departments')
                    ->where('company_id', $companyId)
                    ->where('name', $deptName)
                    ->first();
            }

            // =========================================================================
            // 4. Posisi Jabatan (Head & Staff per Divisi)
            // =========================================================================
            $positions = [];

            foreach ($departments as $deptName => $deptModel) {
                $headTitle = "Head {$deptName}";
                $staffTitle = "Staff {$deptName}";

                // Head Position
                DB::table('positions')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'department_id' => $deptModel->id,
                        'name' => $headTitle,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
                $positions[$headTitle] = DB::table('positions')
                    ->where('company_id', $companyId)
                    ->where('department_id', $deptModel->id)
                    ->where('name', $headTitle)
                    ->value('id');

                // Staff Position
                DB::table('positions')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'department_id' => $deptModel->id,
                        'name' => $staffTitle,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
                $positions[$staffTitle] = DB::table('positions')
                    ->where('company_id', $companyId)
                    ->where('department_id', $deptModel->id)
                    ->where('name', $staffTitle)
                    ->value('id');
            }

            // =========================================================================
            // 5. Template Shift 24 Jam (Kuota Presisi Sesuai Susunan Pengguna)
            // =========================================================================
            $departmentTemplateConfigs = [
                'Bar' => [
                    ['title' => 'Shift 1 - Pagi', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 2, 'color' => '#3B82F6'],
                    ['title' => 'Shift 2 - Sore', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 3, 'color' => '#F59E0B'],
                    ['title' => 'Shift 3 - Malam', 'start' => '23:00:00', 'end' => '07:00:00', 'quota' => 2, 'color' => '#6C5CE7'],
                ],
                'Kitchen' => [
                    ['title' => 'Shift 1 - Pagi', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 2, 'color' => '#3B82F6'],
                    ['title' => 'Shift 2 - Sore', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 3, 'color' => '#F59E0B'],
                    ['title' => 'Shift 3 - Malam', 'start' => '23:00:00', 'end' => '07:00:00', 'quota' => 2, 'color' => '#6C5CE7'],
                ],
                'Waiters' => [
                    ['title' => 'Shift 1 - Pagi', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 1, 'color' => '#3B82F6'],
                    ['title' => 'Shift 2 - Sore', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 2, 'color' => '#F59E0B'],
                    ['title' => 'Shift 3 - Malam', 'start' => '23:00:00', 'end' => '07:00:00', 'quota' => 1, 'color' => '#6C5CE7'],
                ],
                'Kasir' => [
                    ['title' => 'Shift 1 - Pagi', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 1, 'color' => '#3B82F6'],
                    ['title' => 'Shift 2 - Sore', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 1, 'color' => '#F59E0B'],
                    ['title' => 'Shift 3 - Malam', 'start' => '23:00:00', 'end' => '07:00:00', 'quota' => 1, 'color' => '#6C5CE7'],
                ],
                'Media' => [
                    ['title' => 'Shift Reguler', 'start' => '09:00:00', 'end' => '17:00:00', 'quota' => 1, 'color' => '#10B981'],
                ],
            ];

            foreach ($departmentTemplateConfigs as $deptName => $templatesList) {
                $deptId = $departments[$deptName]->id;
                foreach ($templatesList as $tmpl) {
                    DB::table('shift_templates')->updateOrInsert(
                        [
                            'company_id' => $companyId,
                            'department_id' => $deptId,
                            'title' => $tmpl['title'],
                        ],
                        [
                            'start_time' => $tmpl['start'],
                            'end_time' => $tmpl['end'],
                            'default_quota' => $tmpl['quota'],
                            'color_code' => $tmpl['color'],
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }

            // =========================================================================
            // 6. Akun Pengguna (38 Akun: 7 Manajemen + 31 Staf Ramping)
            // =========================================================================
            $usersData = [
                // 1. Superadmin / Owner
                [
                    'name' => 'Bagas Pratama',
                    'email' => 'owner@kedaikopisenja.id',
                    'role' => 'superadmin',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => null,
                    'position_id' => null,
                    'phone_number' => '081234567801',
                    'join_date' => '2025-01-01',
                ],
                // 2. General Manager (Manager Global)
                [
                    'name' => 'Dimas Anggara',
                    'email' => 'manager.global@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => true,
                    'department_id' => null,
                    'position_id' => null,
                    'phone_number' => '081234567802',
                    'join_date' => '2025-01-15',
                ],
                // 3. Head Bar
                [
                    'name' => 'Rian Hidayat',
                    'email' => 'rian.bar@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Head Bar'] ?? null,
                    'phone_number' => '081234567803',
                    'join_date' => '2025-02-01',
                ],
                // 4. Head Kitchen
                [
                    'name' => 'Fajar Nugraha',
                    'email' => 'fajar.kitchen@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Head Kitchen'] ?? null,
                    'phone_number' => '081234567804',
                    'join_date' => '2025-02-01',
                ],
                // 5. Head Kasir
                [
                    'name' => 'Dinda Lestari',
                    'email' => 'dinda.kasir@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kasir']->id,
                    'position_id' => $positions['Head Kasir'] ?? null,
                    'phone_number' => '081234567805',
                    'join_date' => '2025-02-01',
                ],
                // 6. Head Waiters
                [
                    'name' => 'Rizky Ramadhan',
                    'email' => 'rizky.waiters@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Head Waiters'] ?? null,
                    'phone_number' => '081234567806',
                    'join_date' => '2025-02-01',
                ],
                // 7. Head Media
                [
                    'name' => 'Nabila Putri',
                    'email' => 'nabila.media@kedaikopisenja.id',
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Media']->id,
                    'position_id' => $positions['Head Media'] ?? null,
                    'phone_number' => '081234567807',
                    'join_date' => '2025-02-01',
                ],

                // -------------------------------------------------------------
                // Staf Departemen Bar (10 Staf: 5 Full-Time, 5 Part-Time)
                // -------------------------------------------------------------
                [
                    'name' => 'Adit Pratama',
                    'email' => 'adit.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567808',
                    'join_date' => '2025-03-01',
                ],
                [
                    'name' => 'Rendy Saputra',
                    'email' => 'rendy.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567821',
                    'join_date' => '2025-03-02',
                ],
                [
                    'name' => 'Bima Perkasa',
                    'email' => 'bima.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567822',
                    'join_date' => '2025-03-03',
                ],
                [
                    'name' => 'Faris Mahendra',
                    'email' => 'faris.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567829',
                    'join_date' => '2025-03-15',
                ],
                [
                    'name' => 'Arif Gunawan',
                    'email' => 'arif.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567830',
                    'join_date' => '2025-03-16',
                ],
                [
                    'name' => 'Bayu Saputra',
                    'email' => 'bayu.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567809',
                    'join_date' => '2025-03-05',
                ],
                [
                    'name' => 'Yoga Permana',
                    'email' => 'yoga.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567810',
                    'join_date' => '2025-03-10',
                ],
                [
                    'name' => 'Diki Wahyudi',
                    'email' => 'diki.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567823',
                    'join_date' => '2025-03-12',
                ],
                [
                    'name' => 'Naufal Zaki',
                    'email' => 'naufal.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567831',
                    'join_date' => '2025-03-18',
                ],
                [
                    'name' => 'Hafiz Alamsyah',
                    'email' => 'hafiz.bar@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Bar']->id,
                    'position_id' => $positions['Staff Bar'] ?? null,
                    'phone_number' => '081234567832',
                    'join_date' => '2025-03-20',
                ],

                // -------------------------------------------------------------
                // Staf Departemen Kitchen (10 Staf: 6 Full-Time, 4 Part-Time)
                // -------------------------------------------------------------
                [
                    'name' => 'Agung Prasetyo',
                    'email' => 'agung.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567811',
                    'join_date' => '2025-03-01',
                ],
                [
                    'name' => 'Ilham Wahyudi',
                    'email' => 'ilham.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567812',
                    'join_date' => '2025-03-05',
                ],
                [
                    'name' => 'Surya Pratama',
                    'email' => 'surya.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567824',
                    'join_date' => '2025-03-06',
                ],
                [
                    'name' => 'Dimas Permana',
                    'email' => 'dimas.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567825',
                    'join_date' => '2025-03-07',
                ],
                [
                    'name' => 'Wahyu Setiawan',
                    'email' => 'wahyu.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567833',
                    'join_date' => '2025-03-15',
                ],
                [
                    'name' => 'Bagus Saputro',
                    'email' => 'bagus.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567834',
                    'join_date' => '2025-03-16',
                ],
                [
                    'name' => 'Reza Kurniawan',
                    'email' => 'reza.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567813',
                    'join_date' => '2025-03-12',
                ],
                [
                    'name' => 'Panji Hidayat',
                    'email' => 'panji.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567826',
                    'join_date' => '2025-03-14',
                ],
                [
                    'name' => 'Rizky Fauzi',
                    'email' => 'rizky.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567835',
                    'join_date' => '2025-03-18',
                ],
                [
                    'name' => 'Fahmi Irawan',
                    'email' => 'fahmi.kitchen@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kitchen']->id,
                    'position_id' => $positions['Staff Kitchen'] ?? null,
                    'phone_number' => '081234567836',
                    'join_date' => '2025-03-20',
                ],

                // -------------------------------------------------------------
                // Staf Departemen Kasir (4 Staf: 2 Full-Time, 2 Part-Time)
                // -------------------------------------------------------------
                [
                    'name' => 'Maya Anggraini',
                    'email' => 'maya.kasir@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kasir']->id,
                    'position_id' => $positions['Staff Kasir'] ?? null,
                    'phone_number' => '081234567814',
                    'join_date' => '2025-03-01',
                ],
                [
                    'name' => 'Rina Anggraini',
                    'email' => 'rina.kasir@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kasir']->id,
                    'position_id' => $positions['Staff Kasir'] ?? null,
                    'phone_number' => '081234567827',
                    'join_date' => '2025-03-04',
                ],
                [
                    'name' => 'Siti Rahma',
                    'email' => 'siti.kasir@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kasir']->id,
                    'position_id' => $positions['Staff Kasir'] ?? null,
                    'phone_number' => '081234567815',
                    'join_date' => '2025-03-08',
                ],
                [
                    'name' => 'Zahra Amelia',
                    'email' => 'zahra.kasir@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Kasir']->id,
                    'position_id' => $positions['Staff Kasir'] ?? null,
                    'phone_number' => '081234567838',
                    'join_date' => '2025-03-18',
                ],

                // -------------------------------------------------------------
                // Staf Departemen Waiters (5 Staf: 2 Full-Time, 2 Part-Time, 1 Freelance)
                // -------------------------------------------------------------
                [
                    'name' => 'Doni Irawan',
                    'email' => 'doni.waiter@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Staff Waiters'] ?? null,
                    'phone_number' => '081234567816',
                    'join_date' => '2025-03-01',
                ],
                [
                    'name' => 'Rian Kurniawan',
                    'email' => 'rian.waiter@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'full_time',
                    'max_weekly_hours' => 40,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Staff Waiters'] ?? null,
                    'phone_number' => '081234567828',
                    'join_date' => '2025-03-05',
                ],
                [
                    'name' => 'Tari Wulandari',
                    'email' => 'tari.waiter@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Staff Waiters'] ?? null,
                    'phone_number' => '081234567817',
                    'join_date' => '2025-03-06',
                ],
                [
                    'name' => 'Salsabila Putri',
                    'email' => 'salsabila.waiter@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Staff Waiters'] ?? null,
                    'phone_number' => '081234567837',
                    'join_date' => '2025-03-18',
                ],
                [
                    'name' => 'Eko Prasetya',
                    'email' => 'eko.waiter@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'freelance',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Waiters']->id,
                    'position_id' => $positions['Staff Waiters'] ?? null,
                    'phone_number' => '081234567818',
                    'join_date' => '2025-03-15',
                ],

                // -------------------------------------------------------------
                // Staf Departemen Media (2 Staf: 1 Part-Time, 1 Freelance)
                // -------------------------------------------------------------
                [
                    'name' => 'Kevin Sanjaya',
                    'email' => 'kevin.media@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'part_time',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Media']->id,
                    'position_id' => $positions['Staff Media'] ?? null,
                    'phone_number' => '081234567819',
                    'join_date' => '2025-03-10',
                ],
                [
                    'name' => 'Putri Maharani',
                    'email' => 'putri.media@kedaikopisenja.id',
                    'role' => 'employee',
                    'employment_type' => 'freelance',
                    'max_weekly_hours' => null,
                    'is_global_manager' => false,
                    'department_id' => $departments['Media']->id,
                    'position_id' => $positions['Staff Media'] ?? null,
                    'phone_number' => '081234567820',
                    'join_date' => '2025-03-15',
                ],
            ];

            foreach ($usersData as $userRow) {
                DB::table('users')->updateOrInsert(
                    ['email' => $userRow['email']],
                    [
                        'name' => $userRow['name'],
                        'password' => $defaultPassword,
                        'role' => $userRow['role'],
                        'employment_type' => $userRow['employment_type'],
                        'max_weekly_hours' => $userRow['max_weekly_hours'],
                        'is_global_manager' => $userRow['is_global_manager'],
                        'company_id' => $companyId,
                        'department_id' => $userRow['department_id'],
                        'position_id' => $userRow['position_id'],
                        'phone_number' => $userRow['phone_number'],
                        'join_date' => $userRow['join_date'],
                        'status' => 'active',
                        'email_verified_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            // Ambil mapping user terpilih untuk pengaitan halangan
            $usersByEmail = DB::table('users')
                ->where('company_id', $companyId)
                ->get()
                ->keyBy('email');

            // =========================================================================
            // 7. Availabilities (Ketersediaan / Jadwal Halangan Mingguan Tetap)
            // =========================================================================
            // Bersihkan data lama agar hanya skenario mingguan tetap yang tersimpan
            DB::table('availabilities')->where('company_id', $companyId)->delete();

            $monday = now()->startOfWeek(CarbonInterface::MONDAY);
            $headBar = $usersByEmail->get('rian.bar@kedaikopisenja.id');
            $headKitchen = $usersByEmail->get('fajar.kitchen@kedaikopisenja.id');
            $headKasir = $usersByEmail->get('dinda.kasir@kedaikopisenja.id');
            $headWaiters = $usersByEmail->get('rizky.waiters@kedaikopisenja.id');
            $headMedia = $usersByEmail->get('nabila.media@kedaikopisenja.id');

            $availabilitiesData = [
                // 1. Bayu Saputra (Bar - PT)
                [
                    'user_email' => 'bayu.bar@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Kuliah Teori Rekayasa Web',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'bayu.bar@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '11:30:00',
                    'reason' => 'Kuliah Basis Data Lanjut',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'bayu.bar@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '13:00:00',
                    'end_time_str' => '16:30:00',
                    'reason' => 'Praktikum Jaringan Komputer',
                    'approved_by' => $headBar?->id,
                ],

                // 2. Yoga Permana (Bar - PT)
                [
                    'user_email' => 'yoga.bar@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Kuliah Manajemen Pemasaran',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'yoga.bar@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '13:00:00',
                    'end_time_str' => '16:00:00',
                    'reason' => 'Kuliah Manajemen Keuangan',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'yoga.bar@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '11:00:00',
                    'reason' => 'Kuliah Etika Bisnis & Regulasi',
                    'approved_by' => $headBar?->id,
                ],

                // 3. Diki Wahyudi (Bar - PT Baru)
                [
                    'user_email' => 'diki.bar@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Kuliah Pemrograman Berorientasi Objek',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'diki.bar@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Praktikum Sistem Basis Data',
                    'approved_by' => $headBar?->id,
                ],

                // 4. Reza Kurniawan (Kitchen - PT)
                [
                    'user_email' => 'reza.kitchen@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '10:00:00',
                    'end_time_str' => '14:00:00',
                    'reason' => 'Praktikum Pengolahan Kuliner Kontinental',
                    'approved_by' => $headKitchen?->id,
                ],
                [
                    'user_email' => 'reza.kitchen@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Higiene, Sanitasi & Keamanan Pangan',
                    'approved_by' => $headKitchen?->id,
                ],

                // 5. Panji Hidayat (Kitchen - PT Baru)
                [
                    'user_email' => 'panji.kitchen@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Kuliah Pengetahuan Bahan Makanan',
                    'approved_by' => $headKitchen?->id,
                ],
                [
                    'user_email' => 'panji.kitchen@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Praktikum Pengolahan Roti & Pastry',
                    'approved_by' => $headKitchen?->id,
                ],

                // 6. Siti Rahma (Kasir - PT)
                [
                    'user_email' => 'siti.kasir@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '13:00:00',
                    'end_time_str' => '16:30:00',
                    'reason' => 'Perpajakan & Sistem Akuntansi',
                    'approved_by' => $headKasir?->id,
                ],
                [
                    'user_email' => 'siti.kasir@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Akuntansi Keuangan Menengah',
                    'approved_by' => $headKasir?->id,
                ],
                [
                    'user_email' => 'siti.kasir@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Sistem Informasi Akuntansi Kasir',
                    'approved_by' => $headKasir?->id,
                ],

                // 7. Tari Wulandari (Waiters - PT)
                [
                    'user_email' => 'tari.waiter@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '11:30:00',
                    'reason' => 'Tata Hidang & Service Excellence',
                    'approved_by' => $headWaiters?->id,
                ],
                [
                    'user_email' => 'tari.waiter@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '10:00:00',
                    'end_time_str' => '13:30:00',
                    'reason' => 'Komunikasi Interpersonal Tamu',
                    'approved_by' => $headWaiters?->id,
                ],
                [
                    'user_email' => 'tari.waiter@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '13:00:00',
                    'end_time_str' => '16:00:00',
                    'reason' => 'Operasional & Manajemen Restoran',
                    'approved_by' => $headWaiters?->id,
                ],

                // 8. Kevin Sanjaya (Media - PT)
                [
                    'user_email' => 'kevin.media@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '13:00:00',
                    'end_time_str' => '17:00:00',
                    'reason' => 'Studio Desain Grafis & Tipografi',
                    'approved_by' => $headMedia?->id,
                ],
                [
                    'user_email' => 'kevin.media@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Produksi Audio Visual Digital',
                    'approved_by' => $headMedia?->id,
                ],

                // 9. Ilham Wahyudi (Kitchen - FT)
                [
                    'user_email' => 'ilham.kitchen@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '11:30:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Ibadah Sholat Jumat Mingguan',
                    'approved_by' => $headKitchen?->id,
                ],

                // 10. Doni Irawan (Waiters - FT)
                [
                    'user_email' => 'doni.waiter@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '07:30:00',
                    'end_time_str' => '10:30:00',
                    'reason' => 'Mengantar Orang Tua Terapi Medis Rutin',
                    'approved_by' => $headWaiters?->id,
                ],

                // 11. Eko Prasetya (Waiters - Freelance)
                [
                    'user_email' => 'eko.waiter@kedaikopisenja.id',
                    'day_of_week' => 6,
                    'day_offset' => 5,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Terikat Kontrak Asisten Event Eksternal',
                    'approved_by' => $headWaiters?->id,
                ],

                // 12. Putri Maharani (Media - Freelance)
                [
                    'user_email' => 'putri.media@kedaikopisenja.id',
                    'day_of_week' => 6,
                    'day_offset' => 5,
                    'start_time_str' => '13:30:00',
                    'end_time_str' => '17:00:00',
                    'reason' => 'Kursus Profesional Desain UI/UX & Branding',
                    'approved_by' => $headMedia?->id,
                ],

                // 13. Naufal Zaki (Bar - PT)
                [
                    'user_email' => 'naufal.bar@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Kuliah Sistem Informasi Manajemen',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'naufal.bar@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '12:00:00',
                    'reason' => 'Praktikum Komputasi Awan',
                    'approved_by' => $headBar?->id,
                ],

                // 14. Hafiz Alamsyah (Bar - PT)
                [
                    'user_email' => 'hafiz.bar@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Kuliah Pemrograman Web Lanjut',
                    'approved_by' => $headBar?->id,
                ],
                [
                    'user_email' => 'hafiz.bar@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Kuliah Keamanan Siber & Jaringan',
                    'approved_by' => $headBar?->id,
                ],

                // 15. Rizky Fauzi (Kitchen - PT)
                [
                    'user_email' => 'rizky.kitchen@kedaikopisenja.id',
                    'day_of_week' => 2,
                    'day_offset' => 1,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Praktikum Gastronomi Modern',
                    'approved_by' => $headKitchen?->id,
                ],
                [
                    'user_email' => 'rizky.kitchen@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '08:30:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Kuliah Pengendalian Mutu Pangan',
                    'approved_by' => $headKitchen?->id,
                ],

                // 16. Fahmi Irawan (Kitchen - PT)
                [
                    'user_email' => 'fahmi.kitchen@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Praktikum Manajemen Dapur Utama',
                    'approved_by' => $headKitchen?->id,
                ],
                [
                    'user_email' => 'fahmi.kitchen@kedaikopisenja.id',
                    'day_of_week' => 5,
                    'day_offset' => 4,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '13:00:00',
                    'reason' => 'Kuliah Inovasi Kuliner Nusantara',
                    'approved_by' => $headKitchen?->id,
                ],

                // 17. Salsabila Putri (Waiters - PT)
                [
                    'user_email' => 'salsabila.waiter@kedaikopisenja.id',
                    'day_of_week' => 1,
                    'day_offset' => 0,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Kuliah Komunikasi & Pelayanan Tamu',
                    'approved_by' => $headWaiters?->id,
                ],
                [
                    'user_email' => 'salsabila.waiter@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '09:00:00',
                    'end_time_str' => '12:30:00',
                    'reason' => 'Praktikum Tata Meja & Etika Jamuan',
                    'approved_by' => $headWaiters?->id,
                ],

                // 18. Zahra Amelia (Kasir - PT)
                [
                    'user_email' => 'zahra.kasir@kedaikopisenja.id',
                    'day_of_week' => 3,
                    'day_offset' => 2,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '11:30:00',
                    'reason' => 'Kuliah Aplikasi Perbankan & POS',
                    'approved_by' => $headKasir?->id,
                ],
                [
                    'user_email' => 'zahra.kasir@kedaikopisenja.id',
                    'day_of_week' => 4,
                    'day_offset' => 3,
                    'start_time_str' => '08:00:00',
                    'end_time_str' => '11:30:00',
                    'reason' => 'Praktikum Audit Keuangan Kafe',
                    'approved_by' => $headKasir?->id,
                ],
            ];

            foreach ($availabilitiesData as $avail) {
                $targetUser = $usersByEmail->get($avail['user_email']);
                if (! $targetUser) {
                    continue;
                }

                $sTime = explode(':', $avail['start_time_str']);
                $eTime = explode(':', $avail['end_time_str']);

                $startDateTime = $monday->copy()
                    ->addDays($avail['day_offset'])
                    ->setTime((int) $sTime[0], (int) $sTime[1], (int) $sTime[2]);

                $endDateTime = $monday->copy()
                    ->addDays($avail['day_offset'])
                    ->setTime((int) $eTime[0], (int) $eTime[1], (int) $eTime[2]);

                DB::table('availabilities')->insert([
                    'company_id' => $companyId,
                    'user_id' => $targetUser->id,
                    'is_recurring' => true,
                    'day_of_week' => $avail['day_of_week'],
                    'start_time' => $startDateTime,
                    'end_time' => $endDateTime,
                    'reason' => $avail['reason'],
                    'status' => 'approved',
                    'approved_by' => $avail['approved_by'],
                    'approved_at' => $now->copy()->subDays(2),
                    'rejection_reason' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
            }

            // Bersihkan draf shift lama agar kalender bersih dan siap digenerate dengan aturan baru
            $draftIds = DB::table('shifts')
                ->where('company_id', $companyId)
                ->where('status', 'draft')
                ->pluck('id');

            if ($draftIds->isNotEmpty()) {
                DB::table('shift_assignments')->whereIn('shift_id', $draftIds)->delete();
                DB::table('shifts')->whereIn('id', $draftIds)->delete();
            }
        });
    }
}
