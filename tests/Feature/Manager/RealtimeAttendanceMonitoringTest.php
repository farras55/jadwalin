<?php

namespace Tests\Feature\Manager;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RealtimeAttendanceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'PT Senja Wisata Nusantara',
            'email' => 'contact@senjawisata.co.id',
            'phone_number' => '081234567890',
            'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createDepartment(Company $company, string $name = 'Food & Beverage'): Department
    {
        return Department::create([
            'company_id' => $company->id,
            'name' => $name,
            'description' => 'Divisi Operasional '.$name,
        ]);
    }

    private function createPosition(Company $company, Department $department, string $name = 'Barista'): Position
    {
        return Position::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'name' => $name,
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
        ?Position $position = null,
        string $name = 'Staf Operasional'
    ): User {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'position_id' => $position?->id,
            'name' => $name,
            'role' => 'employee',
            'status' => 'active',
        ]);
    }

    private function createShift(
        Company $company,
        Department $department,
        array $attributes = []
    ): Shift {
        return Shift::create(array_merge([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Pagi Reguler',
            'start_time' => Carbon::today()->setTime(8, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
            'quota' => 5,
            'is_open_shift' => false,
            'status' => 'published',
        ], $attributes));
    }

    private function createShiftAssignment(
        Company $company,
        Shift $shift,
        User $user,
        array $attributes = []
    ): ShiftAssignment {
        return ShiftAssignment::create(array_merge([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $user->id,
            'status' => 'scheduled',
            'late_minutes' => 0,
            'clock_in_time' => null,
            'clock_out_time' => null,
        ], $attributes));
    }

    /**
     * 1. Test manager dapat mengakses dashboard realtime monitoring kehadiran departemennya.
     */
    public function test_manager_can_view_realtime_attendance_monitoring_dashboard(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company, 'Operasional');
        $position = $this->createPosition($company, $department, 'Supervisor Toko');
        $manager = $this->createManager($company, $department);
        $employee = $this->createEmployee($company, $department, $position, 'Budi Santoso');

        $shift = $this->createShift($company, $department, [
            'title' => 'Shift Siang Mantap',
            'start_time' => Carbon::today()->setTime(9, 0, 0),
            'end_time' => Carbon::today()->setTime(18, 0, 0),
        ]);

        $this->createShiftAssignment($company, $shift, $employee);

        $response = $this->actingAs($manager)->get(route('manager.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('manager.attendance.index');
        $response->assertViewHasAll([
            'serverTime',
            'serverDate',
            'metrics',
            'rows',
            'gracePeriod',
            'manager',
        ]);

        // Verifikasi nama staf departemen dan detail shift tampil pada view
        $response->assertSee('Budi Santoso');
        $response->assertSee('Shift Siang Mantap');
    }

    /**
     * 2. Test feed JSON realtime mengembalikan metrik akurat untuk departemen manager
     *    dengan skenario campuran: tepat waktu, terlambat, belum hadir, belum mulai.
     */
    public function test_realtime_feed_returns_accurate_counts_for_manager_department(): void
    {
        $company = $this->createCompany();

        // Atur toleransi keterlambatan 15 menit di company_settings
        CompanySetting::create([
            'company_id' => $company->id,
            'grace_period_minutes' => 15,
            'max_regular_hours' => 8,
        ]);

        $department = $this->createDepartment($company, 'Food & Beverage');
        $manager = $this->createManager($company, $department);

        // Kunci waktu saat ini ke 2026-10-26 10:00:00 (deterministik)
        $simulatedNow = Carbon::create(2026, 10, 26, 10, 0, 0);
        Carbon::setTestNow($simulatedNow);

        // Shift 1: 08:00 s.d 17:00 (Batas toleransi: 08:15)
        $shiftPagi = $this->createShift($company, $department, [
            'title' => 'Shift Pagi',
            'start_time' => Carbon::today()->setTime(8, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);

        // Shift 2: 13:00 s.d 21:00 (Batas toleransi: 13:15, jam saat ini 10:00 belum mulai)
        $shiftSiang = $this->createShift($company, $department, [
            'title' => 'Shift Siang',
            'start_time' => Carbon::today()->setTime(13, 0, 0),
            'end_time' => Carbon::today()->setTime(21, 0, 0),
        ]);

        // Staf 1: Tepat Waktu (Clock-in 08:10 <= 08:15)
        $empOnTime = $this->createEmployee($company, $department, null, 'Andi Tepat Waktu');
        $this->createShiftAssignment($company, $shiftPagi, $empOnTime, [
            'clock_in_time' => Carbon::today()->setTime(8, 10, 0),
            'status' => 'present',
        ]);

        // Staf 2: Terlambat (Clock-in 08:25 > 08:15)
        $empLate = $this->createEmployee($company, $department, null, 'Bambang Terlambat');
        $this->createShiftAssignment($company, $shiftPagi, $empLate, [
            'clock_in_time' => Carbon::today()->setTime(8, 25, 0),
            'late_minutes' => 25,
            'status' => 'late',
        ]);

        // Staf 3: Belum Hadir / Absent (Shift mulai 08:00, jam sekarang 10:00 > 08:15, belum clock-in)
        $empAbsent = $this->createEmployee($company, $department, null, 'Citra Belum Hadir');
        $this->createShiftAssignment($company, $shiftPagi, $empAbsent, [
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);

        // Staf 4: Belum Mulai / Not Started (Shift mulai 13:00, jam sekarang 10:00 <= 13:15, belum clock-in)
        $empNotStarted = $this->createEmployee($company, $department, null, 'Dewi Belum Mulai');
        $this->createShiftAssignment($company, $shiftSiang, $empNotStarted, [
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($manager)->getJson(route('manager.attendance.feed'));

        $response->assertStatus(200);

        // Verifikasi struktur JSON sesuai spesifikasi
        $response->assertJsonStructure([
            'server_time',
            'server_date',
            'metrics' => [
                'scheduled',
                'on_time',
                'late',
                'absent',
                'not_started',
            ],
            'rows' => [
                '*' => [
                    'user_id',
                    'name',
                    'shift',
                    'shift_start',
                    'shift_end',
                    'clock_in',
                    'status',
                    'status_label',
                ],
            ],
        ]);

        // Assert keakuratan angka metrik
        $response->assertJsonPath('metrics.scheduled', 4);
        $response->assertJsonPath('metrics.on_time', 1);
        $response->assertJsonPath('metrics.late', 1);
        $response->assertJsonPath('metrics.absent', 1);
        $response->assertJsonPath('metrics.not_started', 1);

        // Assert status baris staf secara presisi
        $rows = collect($response->json('rows'));
        $this->assertEquals('on_time', $rows->firstWhere('user_id', $empOnTime->id)['status']);
        $this->assertEquals('late', $rows->firstWhere('user_id', $empLate->id)['status']);
        $this->assertEquals('absent', $rows->firstWhere('user_id', $empAbsent->id)['status']);
        $this->assertEquals('not_started', $rows->firstWhere('user_id', $empNotStarted->id)['status']);
    }

    /**
     * 3. Test manager tidak dapat melihat data presensi staf departemen lain.
     */
    public function test_manager_cannot_see_attendance_of_other_departments(): void
    {
        $company = $this->createCompany();

        $deptIT = $this->createDepartment($company, 'Teknologi Informasi');
        $deptHR = $this->createDepartment($company, 'Human Resources');

        $managerIT = $this->createManager($company, $deptIT);

        $employeeIT = $this->createEmployee($company, $deptIT, null, 'Programmer IT');
        $employeeHR = $this->createEmployee($company, $deptHR, null, 'Recruiter HR');

        $shiftIT = $this->createShift($company, $deptIT, [
            'title' => 'Shift IT Harian',
            'start_time' => Carbon::today()->setTime(9, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $shiftHR = $this->createShift($company, $deptHR, [
            'title' => 'Shift HR Harian',
            'start_time' => Carbon::today()->setTime(9, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);

        $this->createShiftAssignment($company, $shiftIT, $employeeIT);
        $this->createShiftAssignment($company, $shiftHR, $employeeHR);

        // 1. Verifikasi di halaman web HTML
        $responseView = $this->actingAs($managerIT)->get(route('manager.attendance.index'));
        $responseView->assertStatus(200);
        $responseView->assertSee('Programmer IT');
        $responseView->assertDontSee('Recruiter HR');

        // 2. Verifikasi di feed JSON
        $responseFeed = $this->actingAs($managerIT)->getJson(route('manager.attendance.feed'));
        $responseFeed->assertStatus(200);
        $responseFeed->assertJsonPath('metrics.scheduled', 1);

        $rows = collect($responseFeed->json('rows'));
        $this->assertTrue($rows->contains('user_id', $employeeIT->id));
        $this->assertFalse($rows->contains('user_id', $employeeHR->id));
    }

    /**
     * 4. Test karyawan (role: employee) dilarang mengakses endpoint monitoring realtime (403 Forbidden).
     */
    public function test_employee_cannot_access_realtime_monitoring(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company, 'Operasional');
        $employee = $this->createEmployee($company, $department);

        // Akses dashboard web
        $responseView = $this->actingAs($employee)->get(route('manager.attendance.index'));
        $responseView->assertStatus(403);

        // Akses feed endpoint JSON
        $responseFeed = $this->actingAs($employee)->getJson(route('manager.attendance.feed'));
        $responseFeed->assertStatus(403);
    }

    /**
     * Test tambahan: Pengguna guest (belum login) diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $responseView = $this->get(route('manager.attendance.index'));
        $responseView->assertRedirect(route('login'));

        $responseFeed = $this->get(route('manager.attendance.feed'));
        $responseFeed->assertRedirect(route('login'));
    }

    /**
     * Test tambahan: Feed realtime hanya mencakup penugasan shift untuk hari ini,
     * mengabaikan shift hari kemarin dan shift hari esok.
     */
    public function test_feed_only_contains_today_shift_assignments(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company, 'Operasional');
        $manager = $this->createManager($company, $department);
        $employee = $this->createEmployee($company, $department, null, 'Joko Widodo');

        $now = Carbon::create(2026, 10, 26, 11, 0, 0);
        Carbon::setTestNow($now);

        // Shift Kemarin (2026-10-25)
        $shiftYesterday = $this->createShift($company, $department, [
            'title' => 'Shift Kemarin',
            'start_time' => Carbon::yesterday()->setTime(8, 0, 0),
            'end_time' => Carbon::yesterday()->setTime(17, 0, 0),
        ]);
        $this->createShiftAssignment($company, $shiftYesterday, $employee);

        // Shift Hari Ini (2026-10-26)
        $shiftToday = $this->createShift($company, $department, [
            'title' => 'Shift Hari Ini',
            'start_time' => Carbon::today()->setTime(8, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);
        $this->createShiftAssignment($company, $shiftToday, $employee);

        // Shift Besok (2026-10-27)
        $shiftTomorrow = $this->createShift($company, $department, [
            'title' => 'Shift Besok',
            'start_time' => Carbon::tomorrow()->setTime(8, 0, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0, 0),
        ]);
        $this->createShiftAssignment($company, $shiftTomorrow, $employee);

        $response = $this->actingAs($manager)->getJson(route('manager.attendance.feed'));
        $response->assertStatus(200);

        $response->assertJsonPath('metrics.scheduled', 1);
        $rows = $response->json('rows');
        $this->assertCount(1, $rows);
        $this->assertEquals('Shift Hari Ini', $rows[0]['shift']);
    }

    /**
     * Test tambahan: Staf yang memiliki izin/cuti yang disetujui (on_leave)
     * diklasifikasikan sebagai 'on_leave' dan tidak dihitung sebagai 'absent'.
     */
    public function test_employee_on_leave_is_classified_as_on_leave_and_not_counted_as_absent(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company, 'Operasional');
        $manager = $this->createManager($company, $department);

        $employeeOnLeave = $this->createEmployee($company, $department, null, 'Siti Cuti');

        $now = Carbon::create(2026, 10, 26, 14, 0, 0);
        Carbon::setTestNow($now);

        $shift = $this->createShift($company, $department, [
            'start_time' => Carbon::today()->setTime(8, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);
        $this->createShiftAssignment($company, $shift, $employeeOnLeave);

        // Berikan izin cuti yang disetujui untuk hari ini
        Availability::create([
            'company_id' => $company->id,
            'user_id' => $employeeOnLeave->id,
            'is_recurring' => false,
            'start_time' => Carbon::today()->setTime(0, 0, 0),
            'end_time' => Carbon::today()->setTime(23, 59, 59),
            'reason' => 'Izin keperluan keluarga mendesak',
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->getJson(route('manager.attendance.feed'));
        $response->assertStatus(200);

        $response->assertJsonPath('metrics.scheduled', 1);
        $response->assertJsonPath('metrics.absent', 0);
        $response->assertJsonPath('metrics.on_leave', 1);

        $rows = collect($response->json('rows'));
        $this->assertEquals('on_leave', $rows->firstWhere('user_id', $employeeOnLeave->id)['status']);
        $this->assertEquals('Izin / Cuti', $rows->firstWhere('user_id', $employeeOnLeave->id)['status_label']);
    }

    /**
     * Test verifikasi tidak adanya masalah query N+1 pada endpoint feed.
     * Jumlah query harus tetap konstan (O(1)) meskipun jumlah staf bertambah banyak.
     */
    public function test_feed_has_no_n_plus_one_queries(): void
    {
        $company = $this->createCompany();
        $department = $this->createDepartment($company, 'Operasional');
        $position = $this->createPosition($company, $department, 'Staff');
        $manager = $this->createManager($company, $department);

        $shift = $this->createShift($company, $department, [
            'start_time' => Carbon::today()->setTime(8, 0, 0),
            'end_time' => Carbon::today()->setTime(17, 0, 0),
        ]);

        // Buat 10 staf dengan 10 penugasan shift hari ini
        for ($i = 1; $i <= 10; $i++) {
            $employee = $this->createEmployee($company, $department, $position, "Staf Ke-{$i}");
            $this->createShiftAssignment($company, $shift, $employee, [
                'clock_in_time' => ($i % 2 === 0) ? Carbon::today()->setTime(8, 5, 0) : null,
                'status' => ($i % 2 === 0) ? 'present' : 'scheduled',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($manager)->getJson(route('manager.attendance.feed'));
        $response->assertStatus(200);

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 10 staf hanya membutuhkan 8 query konstan (Auth user, Company, CompanySetting, ShiftAssignment, User, Position, Department, Availability)
        // Bukan 30+ query (N+1 dicegah sepenuhnya)
        $this->assertLessThanOrEqual(10, $queryCount);
    }
}
