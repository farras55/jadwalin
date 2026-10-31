<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Position;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanDatasetSeeder extends Seeder
{
    /**
     * Run the database seeds for testing scheduling with real 300 employee dataset.
     */
    public function run(): void
    {
        $now = now();
        $defaultPassword = Hash::make('password123');

        $this->command->info('Memulai seeding dataset 300 karyawan untuk pengujian Smart Scheduling...');

        DB::transaction(function () use ($now, $defaultPassword) {
            // =========================================================================
            // 1. PERUSAHAAN BARU (Berbeda dengan PT Senja Wisata Nusantara)
            // =========================================================================
            $company = Company::firstOrCreate(
                ['email' => 'kontak@cakrawalamega.co.id'],
                [
                    'name' => 'PT Cakrawala Mega Pratama',
                    'phone_number' => '021-88899911',
                    'address' => 'Gedung Menara Cakrawala Lt. 18, Jl. Jend. Sudirman Kav. 45, Jakarta Selatan',
                    'timezone' => 'Asia/Jakarta',
                    'status' => 'active',
                ]
            );

            $companyId = $company->id;

            // =========================================================================
            // 2. PENGATURAN KETENAGAKERJAAN (PP 35/2021)
            // =========================================================================
            CompanySetting::updateOrCreate(
                ['company_id' => $companyId],
                [
                    'max_regular_hours' => 40,
                    'max_overtime_hours' => 18,
                    'work_hour_limit_type' => 'weekly',
                    'min_rest_hours' => 11,
                    'max_working_days_per_week' => 6,
                    'auto_buffer_minutes' => 30,
                    'grace_period_minutes' => 15,
                    'is_auto_clockout' => true,
                ]
            );

            // =========================================================================
            // 3. BACA DATASET DARI JSON (Hasil konversi karyawan_bersih_300.xlsx)
            // =========================================================================
            $jsonPath = database_path('seeders/data/karyawan_300.json');
            if (! file_exists($jsonPath)) {
                $this->command->error("File dataset tidak ditemukan di: {$jsonPath}");

                return;
            }

            $rawDataset = json_decode(file_get_contents($jsonPath), true);
            $this->command->info('Membaca '.count($rawDataset).' record karyawan dari file dataset...');

            // =========================================================================
            // 4. GENERATE DEPARTEMEN DARI DATASET
            // =========================================================================
            $uniqueDepts = collect($rawDataset)->pluck('departemen')->unique()->sort()->values();
            $deptMap = []; // name => Department model

            $deptDescriptions = [
                'Administrasi' => 'Divisi tata kelola arsip, dokumen, dan korespondensi kantor.',
                'Finance' => 'Divisi manajemen kas, perpajakan, anggaran, dan penggajian.',
                'HR' => 'Divisi pengembangan talenta, rekrutmen, dan kepatuhan karyawan.',
                'IT' => 'Divisi infrastruktur server, sistem informasi, dan dukungan teknis.',
                'Marketing' => 'Divisi strategi merek, kampanye promosi, dan relasi media.',
                'Operasional' => 'Divisi pelayanan garis depan, logistik harian, dan kepuasan pelanggan.',
                'Produksi' => 'Divisi perakitan produk, kendali mutu (QC), dan manajemen pabrik.',
                'R&D' => 'Divisi riset inovasi, formulasi, dan pengembangan teknologi baru.',
                'Sales' => 'Divisi penjualan langsung, kemitraan retail, dan akuisisi pelanggan.',
            ];

            foreach ($uniqueDepts as $deptName) {
                $dept = Department::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $deptName,
                    ],
                    [
                        'description' => $deptDescriptions[$deptName] ?? "Divisi {$deptName}",
                    ]
                );
                $deptMap[$deptName] = $dept;
            }

            // =========================================================================
            // 5. GENERATE JABATAN / POSISI PER DEPARTEMEN DARI DATASET
            // =========================================================================
            $positionMap = []; // "deptId_positionName" => Position model

            foreach ($rawDataset as $row) {
                $deptName = $row['departemen'];
                $jabatanName = $row['jabatan'];
                $dept = $deptMap[$deptName];

                $key = "{$dept->id}_{$jabatanName}";
                if (! isset($positionMap[$key])) {
                    $pos = Position::firstOrCreate([
                        'company_id' => $companyId,
                        'department_id' => $dept->id,
                        'name' => $jabatanName,
                    ]);
                    $positionMap[$key] = $pos;
                }
            }

            // =========================================================================
            // 6. GENERATE TEMPLATE SHIFT LENGKAP UNTUK SELURUH DEPARTEMEN
            // =========================================================================
            $shiftTemplatesConfig = [
                'Operasional' => [
                    ['title' => 'Shift Pagi', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 3, 'color' => '#6C5CE7'],
                    ['title' => 'Shift Siang', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 3, 'color' => '#00B894'],
                    ['title' => 'Shift Middle', 'start' => '11:00:00', 'end' => '19:00:00', 'quota' => 2, 'color' => '#FFA502'],
                ],
                'Produksi' => [
                    ['title' => 'Shift Pagi Pabrik', 'start' => '06:00:00', 'end' => '14:00:00', 'quota' => 4, 'color' => '#4834D4'],
                    ['title' => 'Shift Siang Pabrik', 'start' => '14:00:00', 'end' => '22:00:00', 'quota' => 4, 'color' => '#0984E3'],
                    ['title' => 'Shift Malam (Overnight)', 'start' => '22:00:00', 'end' => '06:00:00', 'quota' => 2, 'color' => '#2D3436'],
                ],
                'Sales' => [
                    ['title' => 'Shift Sales Regular', 'start' => '09:00:00', 'end' => '17:00:00', 'quota' => 3, 'color' => '#E84393'],
                    ['title' => 'Shift Booth/Outlet Mall', 'start' => '13:00:00', 'end' => '21:00:00', 'quota' => 2, 'color' => '#FD79A8'],
                ],
                'IT' => [
                    ['title' => 'Shift IT Support Pagi', 'start' => '08:00:00', 'end' => '16:30:00', 'quota' => 2, 'color' => '#00CEC9'],
                    ['title' => 'Shift Standby Server', 'start' => '16:30:00', 'end' => '01:00:00', 'quota' => 1, 'color' => '#6C5CE7'],
                ],
                'Administrasi' => [
                    ['title' => 'Shift Office Reguler', 'start' => '08:00:00', 'end' => '17:00:00', 'quota' => 2, 'color' => '#6C5CE7'],
                ],
                'Finance' => [
                    ['title' => 'Shift Office Reguler', 'start' => '08:30:00', 'end' => '17:30:00', 'quota' => 2, 'color' => '#2ECC71'],
                ],
                'HR' => [
                    ['title' => 'Shift Office Reguler', 'start' => '08:00:00', 'end' => '17:00:00', 'quota' => 2, 'color' => '#9B59B6'],
                ],
                'Marketing' => [
                    ['title' => 'Shift Campaign Pagi', 'start' => '08:30:00', 'end' => '17:00:00', 'quota' => 2, 'color' => '#E67E22'],
                    ['title' => 'Shift Event Luar', 'start' => '12:00:00', 'end' => '20:30:00', 'quota' => 2, 'color' => '#F39C12'],
                ],
                'R&D' => [
                    ['title' => 'Shift Laboratorium Pagi', 'start' => '08:00:00', 'end' => '16:30:00', 'quota' => 2, 'color' => '#34495E'],
                ],
            ];

            foreach ($shiftTemplatesConfig as $deptName => $tmplList) {
                if (isset($deptMap[$deptName])) {
                    $dept = $deptMap[$deptName];
                    foreach ($tmplList as $t) {
                        ShiftTemplate::firstOrCreate(
                            [
                                'company_id' => $companyId,
                                'department_id' => $dept->id,
                                'title' => $t['title'],
                            ],
                            [
                                'start_time' => $t['start'],
                                'end_time' => $t['end'],
                                'default_quota' => $t['quota'],
                                'color_code' => $t['color'],
                            ]
                        );
                    }
                }
            }

            // =========================================================================
            // 7. AKUN UTAMA UNTUK LOGIN & PENGUJIAN MANAJER
            // =========================================================================
            $operasionalDept = $deptMap['Operasional'];
            $produksiDept = $deptMap['Produksi'];

            // A. Super Admin Perusahaan
            User::firstOrCreate(
                ['email' => 'owner@cakrawalamega.co.id'],
                [
                    'company_id' => $companyId,
                    'department_id' => null,
                    'position_id' => null,
                    'name' => 'Bambang Wijaya (Owner & Superadmin)',
                    'password' => $defaultPassword,
                    'role' => 'superadmin',
                    'employment_type' => 'full_time',
                    'is_global_manager' => false,
                    'phone_number' => '081122334400',
                    'join_date' => '2023-01-01',
                    'status' => 'active',
                ]
            );

            // B. Global Manager (Bisa atur multi-departemen)
            $globalManager = User::firstOrCreate(
                ['email' => 'global.manager@cakrawalamega.co.id'],
                [
                    'company_id' => $companyId,
                    'department_id' => $operasionalDept->id,
                    'position_id' => $positionMap["{$operasionalDept->id}_Manager"]->id ?? null,
                    'name' => 'Rina Agustina (Global Manager)',
                    'password' => $defaultPassword,
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'is_global_manager' => true,
                    'phone_number' => '081122334401',
                    'join_date' => '2023-02-01',
                    'status' => 'active',
                ]
            );

            // C. Regular Manager (Terkunci khusus Departemen Operasional)
            $regManagerOp = User::firstOrCreate(
                ['email' => 'manager.operasional@cakrawalamega.co.id'],
                [
                    'company_id' => $companyId,
                    'department_id' => $operasionalDept->id,
                    'position_id' => $positionMap["{$operasionalDept->id}_Manager"]->id ?? null,
                    'name' => 'Hendra Saputra (Manager Operasional)',
                    'password' => $defaultPassword,
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'is_global_manager' => false,
                    'phone_number' => '081122334402',
                    'join_date' => '2023-03-01',
                    'status' => 'active',
                ]
            );

            // D. Regular Manager (Terkunci khusus Departemen Produksi)
            User::firstOrCreate(
                ['email' => 'manager.produksi@cakrawalamega.co.id'],
                [
                    'company_id' => $companyId,
                    'department_id' => $produksiDept->id,
                    'position_id' => $positionMap["{$produksiDept->id}_Manager"]->id ?? null,
                    'name' => 'Farhan Santoso (Manager Produksi)',
                    'password' => $defaultPassword,
                    'role' => 'manager',
                    'employment_type' => 'full_time',
                    'is_global_manager' => false,
                    'phone_number' => '081122334403',
                    'join_date' => '2023-03-15',
                    'status' => 'active',
                ]
            );

            // =========================================================================
            // 8. INSERT SELURUH 300 KARYAWAN DARI DATASET (BULK UPSERT CEPAT)
            // =========================================================================
            $usersToInsert = [];

            foreach ($rawDataset as $idx => $row) {
                $idKaryawan = $row['id_karyawan'];
                $nama = $row['nama'];
                $deptName = $row['departemen'];
                $jabatan = $row['jabatan'];
                $dept = $deptMap[$deptName];
                $pos = $positionMap["{$dept->id}_{$jabatan}"] ?? null;

                $seqId = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                $cleanSlug = Str::slug($nama, '.');
                $email = "{$cleanSlug}.{$seqId}@cakrawalamega.co.id";

                $employmentType = 'full_time';
                $maxWeeklyHours = null;

                if ($idx % 7 === 0) {
                    $employmentType = 'part_time';
                    $maxWeeklyHours = 24;
                } elseif ($idx % 19 === 0) {
                    $employmentType = 'freelance';
                    $maxWeeklyHours = 20;
                }

                $usersToInsert[] = [
                    'company_id' => $companyId,
                    'department_id' => $dept->id,
                    'position_id' => $pos?->id,
                    'name' => $nama,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'role' => $jabatan === 'Manager' ? 'manager' : 'employee',
                    'employment_type' => $employmentType,
                    'max_weekly_hours' => $maxWeeklyHours,
                    'is_global_manager' => false,
                    'phone_number' => '0812'.str_pad($idx + 1, 8, '0', STR_PAD_LEFT),
                    'join_date' => '2023-06-'.str_pad(($idx % 28) + 1, 2, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($usersToInsert, 100) as $chunk) {
                User::upsert(
                    $chunk,
                    ['email'],
                    ['company_id', 'department_id', 'position_id', 'name', 'password', 'role', 'employment_type', 'max_weekly_hours', 'phone_number', 'join_date', 'status', 'updated_at']
                );
            }

            $operasionalStaffList = User::where('company_id', $companyId)
                ->where('department_id', $operasionalDept->id)
                ->where('role', 'employee')
                ->take(2)
                ->get();

            // =========================================================================
            // 9. SAMPEL DATA HALANGAN (AVAILABILITY APPROVED) UNTUK PENGUJIAN
            // =========================================================================
            // Ambil 2 staf operasional pertama untuk diberi jadwal halangan
            $monday = now()->startOfWeek(CarbonInterface::MONDAY);

            if (count($operasionalStaffList) >= 2) {
                $stafHalangan1 = $operasionalStaffList[0];
                $stafHalangan2 = $operasionalStaffList[1];

                // Halangan 1: Rutin setiap Senin pagi (Kuliah Kelas Karyawan)
                Availability::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'user_id' => $stafHalangan1->id,
                        'is_recurring' => true,
                        'day_of_week' => 1, // Senin
                    ],
                    [
                        'start_time' => $monday->copy()->setTime(7, 0, 0),
                        'end_time' => $monday->copy()->setTime(15, 0, 0),
                        'reason' => 'Kuliah Kelas Karyawan (Senin Pagi)',
                        'status' => 'approved',
                        'approved_by' => $globalManager->id,
                    ]
                );

                // Halangan 2: Tanggal spesifik Rabu sore (Jadwal Terapi/Klinik)
                $wednesday = $monday->copy()->addDays(2);
                Availability::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'user_id' => $stafHalangan2->id,
                        'is_recurring' => false,
                        'start_time' => $wednesday->copy()->setTime(15, 0, 0),
                    ],
                    [
                        'end_time' => $wednesday->copy()->setTime(23, 0, 0),
                        'reason' => 'Pemeriksaan Kesehatan Berkala',
                        'status' => 'approved',
                        'approved_by' => $globalManager->id,
                    ]
                );
            }

            $this->command->info('Dataset seeder berhasil ditanam!');
            $this->command->line('');
            $this->command->info('================ AKUN PENGUJIAN BARU (PT CAKRAWALA MEGA PRATAMA) ================');
            $this->command->line('Password untuk semua akun: password123');
            $this->command->line('');
            $this->command->line('1. GLOBAL MANAGER (Akses Seluruh Departemen):');
            $this->command->line('   Email: global.manager@cakrawalamega.co.id');
            $this->command->line('');
            $this->command->line('2. REGULAR MANAGER (Terkunci Khusus Dept Operasional):');
            $this->command->line('   Email: manager.operasional@cakrawalamega.co.id');
            $this->command->line('');
            $this->command->line('3. REGULAR MANAGER (Terkunci Khusus Dept Produksi):');
            $this->command->line('   Email: manager.produksi@cakrawalamega.co.id');
            $this->command->line('==================================================================================');
        });
    }
}
