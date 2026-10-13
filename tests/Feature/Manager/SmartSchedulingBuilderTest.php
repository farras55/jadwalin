<?php

namespace Tests\Feature\Manager;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartSchedulingBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'PT Senja Wisata Nusantara',
            'email' => 'senja@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Sunset Road No. 88, Bali',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createDepartment(Company $company): Department
    {
        return Department::create([
            'company_id' => $company->id,
            'name' => 'Food & Beverage',
            'description' => 'Divisi Kafe & Restoran',
        ]);
    }

    private function createManager(Company $company, Department $department): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'manager',
            'is_global_manager' => false,
            'status' => 'active',
        ]);
    }

    private function createEmployee(
        Company $company,
        Department $department,
        string $name = 'Staf Operasional',
        string $employmentType = 'full_time',
        ?int $maxWeeklyHours = null
    ): User {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'name' => $name,
            'role' => 'employee',
            'employment_type' => $employmentType,
            'max_weekly_hours' => $maxWeeklyHours,
            'status' => 'active',
        ]);
    }

    private function createTemplate(
        Company $company,
        Department $department,
        string $title = 'Shift Pagi',
        string $startTime = '08:00:00',
        string $endTime = '16:00:00',
        int $defaultQuota = 1
    ): ShiftTemplate {
        return ShiftTemplate::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => $title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'default_quota' => $defaultQuota,
            'color_code' => '#6C5CE7',
        ]);
    }

    /**
     * Skenario 1: Manager can view weekly schedule grid
     */
    public function test_manager_can_view_weekly_schedule_grid(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        $response = $this
            ->actingAs($manager)
            ->get(route('manager.shifts.index'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Kerja Tim (Roster)');
        $response->assertSee('Matriks Roster Mingguan');
        $response->assertSee('Senin');
        $response->assertSee('Minggu');
    }

    /**
     * Skenario 2: Auto scheduling excludes employees with approved unavailability
     */
    public function test_auto_scheduling_excludes_employees_with_approved_unavailability(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // Karyawan A & B
        $empA = $this->createEmployee($company, $department, 'Karyawan A (Berhalangan)');
        $empB = $this->createEmployee($company, $department, 'Karyawan B (Siap Kerja)');

        $monday = now()->startOfWeek(CarbonInterface::MONDAY);

        // Shift template kuota 1
        $this->createTemplate($company, $department, 'Shift Pagi', '08:00:00', '16:00:00', 1);

        // Buat unavailability approved untuk Karyawan A pada hari Senin
        Availability::create([
            'company_id' => $company->id,
            'user_id' => $empA->id,
            'is_recurring' => false,
            'start_time' => $monday->copy()->setTime(8, 0, 0),
            'end_time' => $monday->copy()->setTime(16, 0, 0),
            'reason' => 'Kuliah Praktikum',
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);

        // Eksekusi generate jadwal
        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'week_start' => $monday->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        // Verifikasi pada shift hari Senin
        $mondayShift = Shift::where('department_id', $department->id)
            ->whereDate('start_time', $monday->format('Y-m-d'))
            ->first();

        $this->assertNotNull($mondayShift);
        $this->assertEquals(1, $mondayShift->shiftAssignments()->count());

        $assignedUser = $mondayShift->shiftAssignments()->first()->user;
        // Karyawan B harus terpilih, Karyawan A harus dikecualikan karena halangan
        $this->assertEquals($empB->id, $assignedUser->id);
    }

    /**
     * Skenario 3: Auto scheduling enforces regular hour cap from settings
     */
    public function test_auto_scheduling_enforces_regular_hour_cap_from_settings(): void
    {
        $company = $this->createCompany();
        CompanySetting::create([
            'company_id' => $company->id,
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'min_rest_hours' => 11,
            'max_working_days_per_week' => 6,
        ]);

        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // Hanya 1 karyawan Full-Time agar selalu ditugaskan setiap hari
        $emp = $this->createEmployee($company, $department, 'Karyawan Full Time', 'full_time');

        // Shift 8 jam per hari, 1 slot per hari
        $this->createTemplate($company, $department, 'Shift Harian', '08:00:00', '16:00:00', 1);

        $monday = now()->startOfWeek(CarbonInterface::MONDAY);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'week_start' => $monday->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        // Karyawan akan ditugaskan di 5 hari pertama (5 x 8 = 40 jam reguler).
        // Hari ke-6 (hari Sabtu) = 8 jam tambahan (total 48 jam > 40 reguler).
        // Assignment pada hari ke-6 harus bernilai is_overtime = true dan overtime_status = 'pending_approval'.
        $assignments = ShiftAssignment::where('user_id', $emp->id)
            ->join('shifts', 'shifts.id', '=', 'shift_assignments.shift_id')
            ->orderBy('shifts.start_time')
            ->get();

        $this->assertGreaterThanOrEqual(5, $assignments->count());

        // Cek assignment ke-6 jika ada
        if ($assignments->count() >= 6) {
            $sixthAssignment = $assignments->get(5);
            $this->assertTrue((bool) $sixthAssignment->is_overtime);
            $this->assertEquals('pending_approval', $sixthAssignment->overtime_status);
        }
    }

    /**
     * Skenario 4: Auto scheduling respects freelance and part-time max weekly hours
     */
    public function test_auto_scheduling_respects_freelance_and_part_time_max_weekly_hours(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // Karyawan Part Time dengan batas 16 jam per minggu (tepat 2 shift x 8 jam)
        $partTimeEmp = $this->createEmployee(
            $company,
            $department,
            'Karyawan Part Time',
            'part_time',
            16
        );

        // Shift 8 jam per hari
        $this->createTemplate($company, $department, 'Shift Harian', '08:00:00', '16:00:00', 1);

        $monday = now()->startOfWeek(CarbonInterface::MONDAY);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'week_start' => $monday->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        // Part-time karyawan tidak boleh ditugaskan lebih dari 2 shift (16 jam)
        $assignedCount = ShiftAssignment::where('user_id', $partTimeEmp->id)->count();
        $this->assertLessThanOrEqual(2, $assignedCount);
    }

    /**
     * Skenario 5: Auto scheduling enforces weekly rostered day off
     */
    public function test_auto_scheduling_enforces_weekly_rostered_day_off(): void
    {
        $company = $this->createCompany();
        CompanySetting::create([
            'company_id' => $company->id,
            'max_regular_hours' => 48,
            'max_overtime_hours' => 18,
            'min_rest_hours' => 11,
            'max_working_days_per_week' => 6, // Wajib libur 1 hari jika sudah 6 hari kerja
        ]);

        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // 1 Karyawan saja
        $emp = $this->createEmployee($company, $department, 'Karyawan Tunggal', 'full_time');

        // Template shift setiap hari (7 hari dalam seminggu)
        $this->createTemplate($company, $department, 'Shift Harian', '08:00:00', '14:00:00', 1);

        $monday = now()->startOfWeek(CarbonInterface::MONDAY);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'week_start' => $monday->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        // Karyawan maksimal hanya boleh dijadwalkan 6 hari kerja (hari ke-7 / Minggu wajib libur RDO)
        $distinctDaysWorked = ShiftAssignment::where('user_id', $emp->id)
            ->join('shifts', 'shifts.id', '=', 'shift_assignments.shift_id')
            ->selectRaw('DATE(shifts.start_time) as work_date')
            ->groupBy('work_date')
            ->get();

        $this->assertLessThanOrEqual(6, $distinctDaysWorked->count());
    }

    /**
     * Skenario 6: Manager can publish draft shifts
     */
    public function test_manager_can_publish_draft_shifts(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        $monday = now()->startOfWeek(CarbonInterface::MONDAY);

        // Buat 3 shift berstatus draft
        Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Pagi Draf',
            'start_time' => $monday->copy()->setTime(8, 0, 0),
            'end_time' => $monday->copy()->setTime(16, 0, 0),
            'quota' => 2,
            'status' => 'draft',
        ]);

        Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Siang Draf',
            'start_time' => $monday->copy()->setTime(16, 0, 0),
            'end_time' => $monday->copy()->setTime(22, 0, 0),
            'quota' => 2,
            'status' => 'draft',
        ]);

        // Verifikasi ada 2 draf
        $this->assertEquals(2, Shift::where('department_id', $department->id)->where('status', 'draft')->count());

        // Manager mengeksekusi publish
        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.publish'), [
                'department_id' => $department->id,
                'week_start' => $monday->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status seluruh shift harus berubah menjadi 'published'
        $this->assertEquals(0, Shift::where('department_id', $department->id)->where('status', 'draft')->count());
        $this->assertEquals(2, Shift::where('department_id', $department->id)->where('status', 'published')->count());
    }

    /**
     * Skenario 7 (Revisi): Global Manager dapat melihat selector multi-departemen, Regular Manager terkunci pada departemennya
     */
    public function test_global_manager_and_regular_manager_view_differences(): void
    {
        $company = $this->createCompany();
        $dept1 = $this->createDepartment($company);
        $dept2 = Department::create([
            'company_id' => $company->id,
            'name' => 'Front Office',
            'description' => 'Divisi Resepsionis',
        ]);

        // Regular Manager
        $regularManager = $this->createManager($company, $dept1);
        $responseRegular = $this->actingAs($regularManager)->get(route('manager.shifts.index'));
        $responseRegular->assertStatus(200);
        $responseRegular->assertSee('Departemen Anda');
        $responseRegular->assertSee($dept1->name);
        $responseRegular->assertDontSee('Global Manager (Multi-Departemen)');

        // Global Manager
        $globalManager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept1->id,
            'role' => 'manager',
            'is_global_manager' => true,
            'status' => 'active',
        ]);
        $responseGlobal = $this->actingAs($globalManager)->get(route('manager.shifts.index'));
        $responseGlobal->assertStatus(200);
        $responseGlobal->assertSee('Global Manager (Multi-Departemen)');
        $responseGlobal->assertSee($dept2->name);
    }

    /**
     * Skenario 8 (Revisi): Auto scheduling dengan konfigurasi kebutuhan kustom kuota dan rentang tanggal dinamis
     */
    public function test_auto_scheduling_with_custom_quota_requirements_and_dynamic_dates(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // Buat 3 karyawan
        $emp1 = $this->createEmployee($company, $department, 'Staf 1');
        $emp2 = $this->createEmployee($company, $department, 'Staf 2');
        $emp3 = $this->createEmployee($company, $department, 'Staf 3');

        // Template default_quota = 1
        $template = $this->createTemplate($company, $department, 'Shift Pagi', '08:00:00', '16:00:00', 1);

        // Rentang dinamis: 3 hari saja
        $startDate = now()->startOfWeek(CarbonInterface::MONDAY);
        $endDate = $startDate->copy()->addDays(2); // Senin s.d. Rabu (3 hari)

        // Manager meng-override kebutuhan kuota menjadi 2 orang per shift
        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'requirements' => [
                    $department->id => [
                        $template->id => [
                            'enabled' => 1,
                            'quota' => 2, // Kebutuhan dinaikkan menjadi 2 orang
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect();

        // Harus ada tepat 3 shift (3 hari x 1 template)
        $shifts = Shift::where('department_id', $department->id)
            ->whereBetween('start_time', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->get();

        $this->assertEquals(3, $shifts->count());

        // Setiap shift harus memiliki kuota 2 dan 2 penugasan
        foreach ($shifts as $shift) {
            $this->assertEquals(2, $shift->quota);
            $this->assertEquals(2, $shift->shiftAssignments()->count());
        }
    }

    /**
     * Skenario 9: Manager dapat menambah jumlah shift secara dinamis (shift kustom baru) pada build jadwal
     */
    public function test_auto_scheduling_with_dynamic_number_of_shifts_and_custom_shifts(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        // Buat 4 karyawan
        $emp1 = $this->createEmployee($company, $department, 'Staf Alpha');
        $emp2 = $this->createEmployee($company, $department, 'Staf Beta');
        $emp3 = $this->createEmployee($company, $department, 'Staf Gamma');
        $emp4 = $this->createEmployee($company, $department, 'Staf Delta');

        // Departemen hanya punya 1 template default di database
        $template = $this->createTemplate($company, $department, 'Shift Pagi', '08:00:00', '16:00:00', 1);

        $startDate = now()->startOfWeek(CarbonInterface::MONDAY);
        $endDate = $startDate->copy()->addDays(1); // 2 hari: Senin & Selasa

        // Manager mengirimkan 2 shift dinamis:
        // 1) Shift Pagi (template) kuota 1
        // 2) Shift Malam (custom baru yang ditambahkan di form modal) kuota 1
        $response = $this
            ->actingAs($manager)
            ->post(route('manager.shifts.generate'), [
                'department_id' => $department->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'requirements' => [
                    $department->id => [
                        0 => [
                            'template_id' => $template->id,
                            'title' => 'Shift Pagi',
                            'start_time' => '08:00:00',
                            'end_time' => '16:00:00',
                            'quota' => 1,
                            'days' => [1, 2],
                            'enabled' => 1,
                        ],
                        1 => [
                            'template_id' => null, // Shift kustom baru!
                            'title' => 'Shift Malam Fleksibel',
                            'start_time' => '23:00:00',
                            'end_time' => '07:00:00',
                            'quota' => 1,
                            'days' => [1, 2],
                            'enabled' => 1,
                            'color_code' => '#00B894',
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect();

        // Harus ada 4 shift yang terbuat (2 hari x 2 shift)
        $shifts = Shift::where('department_id', $department->id)
            ->whereBetween('start_time', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->get();

        $this->assertEquals(4, $shifts->count());

        // Verifikasi bahwa shift kustom 'Shift Malam Fleksibel' berhasil terbuat dan terisi staf
        $customShifts = $shifts->where('title', 'Shift Malam Fleksibel');
        $this->assertEquals(2, $customShifts->count());
        foreach ($customShifts as $cs) {
            $this->assertEquals(1, $cs->quota);
            $this->assertEquals(1, $cs->shiftAssignments()->count());
        }
    }

    /**
     * Skenario 10: Manager can edit existing shift details and reassign staff
     */
    public function test_manager_can_update_existing_shift_and_reassign_staff(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        $emp1 = $this->createEmployee($company, $department, 'Staf Pertama');
        $emp2 = $this->createEmployee($company, $department, 'Staf Kedua');
        $emp3 = $this->createEmployee($company, $department, 'Staf Ketiga');

        $shiftDate = now()->startOfWeek(CarbonInterface::MONDAY);
        $shift = Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Pagi Asli',
            'start_time' => $shiftDate->copy()->setTime(8, 0),
            'end_time' => $shiftDate->copy()->setTime(16, 0),
            'quota' => 2,
            'status' => 'draft',
        ]);

        // Awalnya emp1 dan emp2 ditugaskan
        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $emp1->id,
            'status' => 'scheduled',
            'regular_hours' => 8,
            'overtime_hours' => 0,
        ]);
        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $emp2->id,
            'status' => 'scheduled',
            'regular_hours' => 8,
            'overtime_hours' => 0,
        ]);

        $this->assertEquals(2, $shift->shiftAssignments()->count());

        // Manager mengedit: ganti judul, jam, kuota jadi 2, staf diubah: lepas emp2, tambah emp3
        $response = $this
            ->actingAs($manager)
            ->put(route('manager.shifts.update', $shift->id), [
                'title' => 'Shift Pagi Direvisi',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'quota' => 2,
                'status' => 'published',
                'assigned_user_ids' => [$emp1->id, $emp3->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shift->refresh();
        $this->assertEquals('Shift Pagi Direvisi', $shift->title);
        $this->assertEquals('09:00', $shift->start_time->format('H:i'));
        $this->assertEquals('17:00', $shift->end_time->format('H:i'));
        $this->assertEquals(2, $shift->quota);
        $this->assertEquals('published', $shift->status);

        $assignedUserIds = $shift->shiftAssignments()->pluck('user_id')->toArray();
        $this->assertContains($emp1->id, $assignedUserIds);
        $this->assertNotContains($emp2->id, $assignedUserIds);
        $this->assertContains($emp3->id, $assignedUserIds);
        $this->assertCount(2, $assignedUserIds);
    }

    /**
     * Skenario 11: Manager can delete an existing shift
     */
    public function test_manager_can_delete_existing_shift(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        $emp1 = $this->createEmployee($company, $department, 'Staf Alpha');

        $shiftDate = now()->startOfWeek(CarbonInterface::MONDAY);
        $shift = Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Dihapus',
            'start_time' => $shiftDate->copy()->setTime(8, 0),
            'end_time' => $shiftDate->copy()->setTime(16, 0),
            'quota' => 1,
            'status' => 'draft',
        ]);

        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $emp1->id,
            'status' => 'scheduled',
            'regular_hours' => 8,
            'overtime_hours' => 0,
        ]);

        $response = $this
            ->actingAs($manager)
            ->delete(route('manager.shifts.destroy', $shift->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
        $this->assertDatabaseMissing('shift_assignments', ['shift_id' => $shift->id]);
    }

    /**
     * Skenario 12: Regular Manager cannot edit shifts of another department
     */
    public function test_regular_manager_cannot_edit_shift_of_another_department(): void
    {
        $company = $this->createCompany();
        $department1 = $this->createDepartment($company);
        $department2 = Department::create([
            'company_id' => $company->id,
            'name' => 'Housekeeping',
        ]);

        $manager1 = $this->createManager($company, $department1);

        $shiftDate = now()->startOfWeek(CarbonInterface::MONDAY);
        $shiftDept2 = Shift::create([
            'company_id' => $company->id,
            'department_id' => $department2->id,
            'title' => 'Shift Departemen Lain',
            'start_time' => $shiftDate->copy()->setTime(8, 0),
            'end_time' => $shiftDate->copy()->setTime(16, 0),
            'quota' => 1,
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($manager1)
            ->put(route('manager.shifts.update', $shiftDept2->id), [
                'title' => 'Coba Retas',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'quota' => 1,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Skenario 13: Manager can update master shift template
     */
    public function test_manager_can_update_master_shift_template(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company);
        $manager = $this->createManager($company, $department);

        $template = $this->createTemplate($company, $department, 'Shift Pagi Asli', '08:00:00', '16:00:00', 2);

        $response = $this
            ->actingAs($manager)
            ->put(route('manager.shifts.templates.update', $template->id), [
                'title' => 'Shift Pagi Direvisi',
                'start_time' => '07:00',
                'end_time' => '15:00',
                'default_quota' => 4,
                'color_code' => '#00B894',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $template->refresh();
        $this->assertEquals('Shift Pagi Direvisi', $template->title);
        $this->assertEquals('07:00:00', $template->start_time);
        $this->assertEquals('15:00:00', $template->end_time);
        $this->assertEquals(4, $template->default_quota);
        $this->assertEquals('#00B894', $template->color_code);
    }
}

