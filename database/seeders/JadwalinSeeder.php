<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JadwalinSeeder extends Seeder
{
    /**
     * Membuat data awal perusahaan, pengaturan, departemen,
     * posisi, akun pengujian, template shift, jadwal shift,
     * absensi timesheet, pengajuan ketersediaan, tukar shift,
     * dan open shift untuk pengujian SUS (System Usability Scale).
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $defaultPassword = Hash::make('password123');

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
                    'office_latitude' => -8.7231000,
                    'office_longitude' => 115.1723000,
                    'attendance_radius_meters' => 100,
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
                    'work_hour_limit_type' => 'weekly',
                    'min_rest_hours' => 11,
                    'max_working_days_per_week' => 6,
                    'auto_buffer_minutes' => 30,
                    'grace_period_minutes' => 15,
                    'is_auto_clockout' => true,
                    'auto_clockout_buffer_hours' => 2,
                    'office_latitude' => -8.7231000,
                    'office_longitude' => 115.1723000,
                    'attendance_radius_meters' => 100,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            /*
             * 3. Departemen
             */
            // Dept 1: Food & Beverage
            DB::table('departments')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'name' => 'Food & Beverage',
                ],
                [
                    'description' => 'Divisi operasional restoran, bar, dan kafe.',
                    'latitude' => -8.7231000,
                    'longitude' => 115.1723000,
                    'radius_meters' => 100,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            // Dept 2: Operasional & Layanan
            DB::table('departments')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'name' => 'Operasional & Layanan',
                ],
                [
                    'description' => 'Divisi pelayanan garis depan, ticketing, dan front office.',
                    'latitude' => -8.7231000,
                    'longitude' => 115.1723000,
                    'radius_meters' => 100,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $deptFB = DB::table('departments')->where('company_id', $companyId)->where('name', 'Food & Beverage')->first();
            $deptOp = DB::table('departments')->where('company_id', $companyId)->where('name', 'Operasional & Layanan')->first();

            /*
             * 4. Posisi / Jabatan
             */
            $positionsData = [
                ['dept_id' => $deptFB->id, 'name' => 'Barista'],
                ['dept_id' => $deptFB->id, 'name' => 'Kasir Resto'],
                ['dept_id' => $deptFB->id, 'name' => 'Waiter / Server'],
                ['dept_id' => $deptOp->id, 'name' => 'Front Desk Officer'],
                ['dept_id' => $deptOp->id, 'name' => 'Staf Operasional Tiket'],
            ];

            foreach ($positionsData as $pos) {
                DB::table('positions')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'department_id' => $pos['dept_id'],
                        'name' => $pos['name'],
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $posBarista = DB::table('positions')->where('company_id', $companyId)->where('name', 'Barista')->first();
            $posKasir = DB::table('positions')->where('company_id', $companyId)->where('name', 'Kasir Resto')->first();
            $posServer = DB::table('positions')->where('company_id', $companyId)->where('name', 'Waiter / Server')->first();
            $posFrontDesk = DB::table('positions')->where('company_id', $companyId)->where('name', 'Front Desk Officer')->first();

            /*
             * 5. Akun Pengujian Demo (Minimal 3 akun per role)
             * Password untuk semua akun: password123
             */
            $users = [
                // SUPERADMIN (3 Akun)
                [
                    'name' => 'Budi Hartono (Owner 1)',
                    'email' => 'owner@senjawisata.co.id',
                    'role' => 'superadmin',
                    'department_id' => null,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567890',
                    'join_date' => '2024-01-01',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Agus Pratama (Superadmin 2)',
                    'email' => 'admin2@senjawisata.co.id',
                    'role' => 'superadmin',
                    'department_id' => null,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567891',
                    'join_date' => '2024-01-02',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Rina Permata (Superadmin 3)',
                    'email' => 'admin3@senjawisata.co.id',
                    'role' => 'superadmin',
                    'department_id' => null,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567892',
                    'join_date' => '2024-01-03',
                    'employment_type' => 'full_time',
                ],

                // MANAGER (3 Akun)
                [
                    'name' => 'Siti Nurhaliza (Global Manager)',
                    'email' => 'manager@senjawisata.co.id',
                    'role' => 'manager',
                    'department_id' => $deptFB->id,
                    'position_id' => null,
                    'is_global_manager' => true,
                    'phone_number' => '081234567801',
                    'join_date' => '2024-01-15',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Fajar Nugraha (Manager F&B)',
                    'email' => 'manager2@senjawisata.co.id',
                    'role' => 'manager',
                    'department_id' => $deptFB->id,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567802',
                    'join_date' => '2024-01-20',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Dimas Setiawan (Manager Operasional)',
                    'email' => 'manager3@senjawisata.co.id',
                    'role' => 'manager',
                    'department_id' => $deptOp->id,
                    'position_id' => null,
                    'is_global_manager' => false,
                    'phone_number' => '081234567803',
                    'join_date' => '2024-01-25',
                    'employment_type' => 'full_time',
                ],

                // EMPLOYEE / KARYAWAN (4 Akun)
                [
                    'name' => 'Dewi Lestari (Karyawan 1)',
                    'email' => 'karyawan@senjawisata.co.id',
                    'role' => 'employee',
                    'department_id' => $deptFB->id,
                    'position_id' => $posBarista?->id,
                    'is_global_manager' => false,
                    'phone_number' => '081234567701',
                    'join_date' => '2024-02-01',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Bayu Wicaksono (Karyawan 2)',
                    'email' => 'karyawan2@senjawisata.co.id',
                    'role' => 'employee',
                    'department_id' => $deptFB->id,
                    'position_id' => $posKasir?->id,
                    'is_global_manager' => false,
                    'phone_number' => '081234567702',
                    'join_date' => '2024-02-05',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Rizky Ramadhan (Karyawan 3)',
                    'email' => 'karyawan3@senjawisata.co.id',
                    'role' => 'employee',
                    'department_id' => $deptFB->id,
                    'position_id' => $posServer?->id,
                    'is_global_manager' => false,
                    'phone_number' => '081234567703',
                    'join_date' => '2024-02-10',
                    'employment_type' => 'full_time',
                ],
                [
                    'name' => 'Siti Aisyah (Karyawan 4)',
                    'email' => 'karyawan4@senjawisata.co.id',
                    'role' => 'employee',
                    'department_id' => $deptOp->id,
                    'position_id' => $posFrontDesk?->id,
                    'is_global_manager' => false,
                    'phone_number' => '081234567704',
                    'join_date' => '2024-02-15',
                    'employment_type' => 'part_time',
                ],
            ];

            foreach ($users as $user) {
                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    [
                        'company_id' => $companyId,
                        'department_id' => $user['department_id'],
                        'position_id' => $user['position_id'],
                        'is_global_manager' => $user['is_global_manager'],
                        'name' => $user['name'],
                        'email_verified_at' => $now,
                        'password' => $defaultPassword,
                        'role' => $user['role'],
                        'employment_type' => $user['employment_type'] ?? 'full_time',
                        'phone_number' => $user['phone_number'],
                        'join_date' => $user['join_date'],
                        'status' => 'active',
                        'profile_photo' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $uDewi = DB::table('users')->where('email', 'karyawan@senjawisata.co.id')->first();
            $uBayu = DB::table('users')->where('email', 'karyawan2@senjawisata.co.id')->first();
            $uRizky = DB::table('users')->where('email', 'karyawan3@senjawisata.co.id')->first();
            $uAisyah = DB::table('users')->where('email', 'karyawan4@senjawisata.co.id')->first();
            $uManager = DB::table('users')->where('email', 'manager@senjawisata.co.id')->first();

            /*
             * 6. Shift Templates
             */
            $templatesData = [
                ['dept_id' => $deptFB->id, 'title' => 'Shift Pagi F&B', 'start' => '07:00:00', 'end' => '15:00:00', 'quota' => 2, 'color' => '#6C5CE7'],
                ['dept_id' => $deptFB->id, 'title' => 'Shift Siang F&B', 'start' => '15:00:00', 'end' => '23:00:00', 'quota' => 2, 'color' => '#00B894'],
                ['dept_id' => $deptFB->id, 'title' => 'Shift Middle F&B', 'start' => '11:00:00', 'end' => '19:00:00', 'quota' => 2, 'color' => '#FFA502'],
                ['dept_id' => $deptOp->id, 'title' => 'Shift Pagi Operasional', 'start' => '08:00:00', 'end' => '16:00:00', 'quota' => 2, 'color' => '#0984E3'],
                ['dept_id' => $deptOp->id, 'title' => 'Shift Sore Operasional', 'start' => '14:00:00', 'end' => '22:00:00', 'quota' => 2, 'color' => '#E84393'],
            ];

            foreach ($templatesData as $t) {
                DB::table('shift_templates')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'department_id' => $t['dept_id'],
                        'title' => $t['title'],
                    ],
                    [
                        'start_time' => $t['start'],
                        'end_time' => $t['end'],
                        'default_quota' => $t['quota'],
                        'color_code' => $t['color'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $tmplPagiFB = DB::table('shift_templates')->where('company_id', $companyId)->where('title', 'Shift Pagi F&B')->first();
            $tmplSiangFB = DB::table('shift_templates')->where('company_id', $companyId)->where('title', 'Shift Siang F&B')->first();

            /*
             * 7. Availabilities (Ketersediaan / Jadwal Halangan)
             */
            $monday = now()->startOfWeek(CarbonInterface::MONDAY);

            // A. Halangan Approved untuk Dewi: Rutin Setiap Senin Pagi (Kuliah) -> memicu anti-bentrok smart scheduling!
            DB::table('availabilities')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'user_id' => $uDewi->id,
                    'is_recurring' => true,
                    'day_of_week' => 1,
                ],
                [
                    'start_time' => $monday->copy()->setTime(7, 0, 0),
                    'end_time' => $monday->copy()->setTime(11, 0, 0),
                    'reason' => 'Kuliah Pagi Semester Akhir',
                    'status' => 'approved',
                    'approved_by' => $uManager->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            // B. Halangan Pending untuk Bayu: Menunggu Persetujuan Manajer
            $tomorrow = now()->addDay();
            DB::table('availabilities')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'user_id' => $uBayu->id,
                    'is_recurring' => false,
                    'reason' => 'Pemeriksaan Kesehatan di Rumah Sakit',
                ],
                [
                    'day_of_week' => null,
                    'start_time' => $tomorrow->copy()->setTime(8, 0, 0),
                    'end_time' => $tomorrow->copy()->setTime(12, 0, 0),
                    'status' => 'pending_manager',
                    'approved_by' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            /*
             * 8. Shifts & Shift Assignments
             */
            // A. Shift Hari Ini untuk Dewi (Aktif, sedang berlangsung agar bisa Clock In / Out)
            $todayShiftStart = now()->copy()->subHour();
            $todayShiftEnd = now()->copy()->addHours(7);

            $todayShiftId = DB::table('shifts')->insertGetId([
                'company_id' => $companyId,
                'department_id' => $deptFB->id,
                'template_id' => $tmplPagiFB?->id,
                'title' => 'Shift Pagi F&B (Hari Ini)',
                'start_time' => $todayShiftStart,
                'end_time' => $todayShiftEnd,
                'quota' => 2,
                'is_open_shift' => false,
                'requires_approval' => true,
                'is_holiday' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('shift_assignments')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'shift_id' => $todayShiftId,
                    'user_id' => $uDewi->id,
                ],
                [
                    'is_overtime' => false,
                    'overtime_status' => 'none',
                    'clock_in_time' => null, // Belum clock-in agar responden bisa mencoba fitur Clock In
                    'clock_out_time' => null,
                    'late_minutes' => 0,
                    'regular_hours' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'scheduled',
                    'is_auto_clockout' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // B. Shift Riwayat Masa Lalu (7 Hari Terakhir & Bulan Berjalan) untuk Laporan Timesheet
            for ($i = 1; $i <= 5; $i++) {
                $pastDate = now()->copy()->subDays($i);
                $pastStart = $pastDate->copy()->setTime(7, 0, 0);
                $pastEnd = $pastDate->copy()->setTime(15, 0, 0);

                $pastShiftId = DB::table('shifts')->insertGetId([
                    'company_id' => $companyId,
                    'department_id' => $deptFB->id,
                    'template_id' => $tmplPagiFB?->id,
                    'title' => 'Shift Pagi F&B',
                    'start_time' => $pastStart,
                    'end_time' => $pastEnd,
                    'quota' => 2,
                    'is_open_shift' => false,
                    'requires_approval' => true,
                    'is_holiday' => false,
                    'status' => 'completed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('shift_assignments')->insert([
                    'company_id' => $companyId,
                    'shift_id' => $pastShiftId,
                    'user_id' => $uDewi->id,
                    'is_overtime' => false,
                    'overtime_status' => 'none',
                    'clock_in_time' => $pastStart->copy()->addMinutes(5),
                    'clock_out_time' => $pastEnd->copy(),
                    'late_minutes' => 0,
                    'regular_hours' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'present',
                    'is_auto_clockout' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('shift_assignments')->insert([
                    'company_id' => $companyId,
                    'shift_id' => $pastShiftId,
                    'user_id' => $uBayu->id,
                    'is_overtime' => false,
                    'overtime_status' => 'none',
                    'clock_in_time' => $pastStart->copy()->addMinutes(25),
                    'clock_out_time' => $pastEnd->copy(),
                    'late_minutes' => 25,
                    'regular_hours' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'late',
                    'is_auto_clockout' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // C. Shift Mendatang untuk Uji Tukar Shift (2 Hari ke depan)
            $futureDate1 = now()->copy()->addDays(2);
            $futureShift1Id = DB::table('shifts')->insertGetId([
                'company_id' => $companyId,
                'department_id' => $deptFB->id,
                'template_id' => $tmplPagiFB?->id,
                'title' => 'Shift Pagi F&B (Mendatang)',
                'start_time' => $futureDate1->copy()->setTime(7, 0, 0),
                'end_time' => $futureDate1->copy()->setTime(15, 0, 0),
                'quota' => 2,
                'is_open_shift' => false,
                'requires_approval' => true,
                'is_holiday' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $dewiFutureAssignmentId = DB::table('shift_assignments')->insertGetId([
                'company_id' => $companyId,
                'shift_id' => $futureShift1Id,
                'user_id' => $uDewi->id,
                'is_overtime' => false,
                'overtime_status' => 'none',
                'regular_hours' => 8.0,
                'overtime_hours' => 0.0,
                'status' => 'scheduled',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $futureDate2 = now()->copy()->addDays(3);
            $futureShift2Id = DB::table('shifts')->insertGetId([
                'company_id' => $companyId,
                'department_id' => $deptFB->id,
                'template_id' => $tmplSiangFB?->id,
                'title' => 'Shift Siang F&B (Mendatang)',
                'start_time' => $futureDate2->copy()->setTime(15, 0, 0),
                'end_time' => $futureDate2->copy()->setTime(23, 0, 0),
                'quota' => 2,
                'is_open_shift' => false,
                'requires_approval' => true,
                'is_holiday' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $bayuFutureAssignmentId = DB::table('shift_assignments')->insertGetId([
                'company_id' => $companyId,
                'shift_id' => $futureShift2Id,
                'user_id' => $uBayu->id,
                'is_overtime' => false,
                'overtime_status' => 'none',
                'regular_hours' => 8.0,
                'overtime_hours' => 0.0,
                'status' => 'scheduled',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rizkyFutureAssignmentId = DB::table('shift_assignments')->insertGetId([
                'company_id' => $companyId,
                'shift_id' => $futureShift2Id,
                'user_id' => $uRizky->id,
                'is_overtime' => false,
                'overtime_status' => 'none',
                'regular_hours' => 8.0,
                'overtime_hours' => 0.0,
                'status' => 'scheduled',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
             * 9. Shift Swap (Permintaan Tukar Shift)
             */
            // A. Permintaan Masuk untuk Dewi (pending_target): Bayu minta tukar ke Dewi
            DB::table('shift_swaps')->insert([
                'company_id' => $companyId,
                'shift_assignment_id' => $bayuFutureAssignmentId,
                'target_shift_assignment_id' => $dewiFutureAssignmentId,
                'swap_type' => 'exchange',
                'requester_id' => $uBayu->id,
                'target_user_id' => $uDewi->id,
                'reason' => 'Ada acara keluarga di kampung halaman.',
                'status' => 'pending_target',
                'approved_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // B. Permintaan Menunggu Persetujuan Manajer (pending_manager): Rizky serahkan shift ke Bayu
            DB::table('shift_swaps')->insert([
                'company_id' => $companyId,
                'shift_assignment_id' => $rizkyFutureAssignmentId,
                'target_shift_assignment_id' => null,
                'swap_type' => 'transfer',
                'requester_id' => $uRizky->id,
                'target_user_id' => $uBayu->id,
                'reason' => 'Keperluan mendesak mengurus berkas studi.',
                'status' => 'pending_manager',
                'approved_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
             * 10. Open Shifts (Shift Terbuka)
             */
            // A. Open Shift Aktif siap diklaim oleh karyawan
            $openShiftDate1 = now()->copy()->addDays(4);
            $openShift1Id = DB::table('shifts')->insertGetId([
                'company_id' => $companyId,
                'department_id' => $deptFB->id,
                'template_id' => $tmplPagiFB?->id,
                'title' => 'Shift Ekstra Event Akhir Pekan',
                'start_time' => $openShiftDate1->copy()->setTime(7, 0, 0),
                'end_time' => $openShiftDate1->copy()->setTime(15, 0, 0),
                'quota' => 2,
                'is_open_shift' => true,
                'requires_approval' => true,
                'is_holiday' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // B. Open Shift dengan Klaim Pending untuk disetujui Manajer
            $openShiftDate2 = now()->copy()->addDays(5);
            $openShift2Id = DB::table('shifts')->insertGetId([
                'company_id' => $companyId,
                'department_id' => $deptFB->id,
                'template_id' => $tmplSiangFB?->id,
                'title' => 'Shift Tambahan Liburan Nasional',
                'start_time' => $openShiftDate2->copy()->setTime(15, 0, 0),
                'end_time' => $openShiftDate2->copy()->setTime(23, 0, 0),
                'quota' => 1,
                'is_open_shift' => true,
                'requires_approval' => true,
                'is_holiday' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('open_shift_claims')->insert([
                'company_id' => $companyId,
                'shift_id' => $openShift2Id,
                'user_id' => $uRizky->id,
                'status' => 'pending_manager',
                'applied_at' => $now->copy()->subHours(2),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
}
