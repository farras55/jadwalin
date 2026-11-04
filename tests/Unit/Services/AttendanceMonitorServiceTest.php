<?php

namespace Tests\Unit\Services;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Services\AttendanceMonitorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceMonitorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceMonitorService $service;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AttendanceMonitorService;

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'manager',
            'is_global_manager' => false,
            'status' => 'active',
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
        ]);
    }

    /**
     * TC-AMS-01: Menguji kalkulasi staf yang clock-in tepat waktu (on_time).
     */
    public function test_classifies_on_time_attendance_correctly(): void
    {
        $today = Carbon::today();
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(16, 0),
        ]);

        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $today->copy()->setTime(8, 5), // 5 menit setelah shift, dalam grace period 15 menit
            'status' => 'present',
        ]);

        $result = $this->service->getTodayDepartmentAttendance($this->manager, $today->copy()->setTime(10, 0));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertEquals(1, $result['metrics']['on_time'] ?? 0);
        $this->assertEquals(0, $result['metrics']['late'] ?? 0);
    }

    /**
     * TC-AMS-02: Menguji staf dengan izin disetujui terklasifikasi sebagai on_leave (bukan mangkir/absent).
     */
    public function test_classifies_approved_leave_as_on_leave_instead_of_absent(): void
    {
        $today = Carbon::today();
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(16, 0),
        ]);

        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);

        // Catat cuti/izin yang telah disetujui manajer
        Availability::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'start_time' => $today->copy()->setTime(0, 0),
            'end_time' => $today->copy()->setTime(23, 59),
            'is_recurring' => false,
            'status' => 'approved',
        ]);

        // Waktu sekarang jam 12:00 (lewat jam mulai shift)
        $result = $this->service->getTodayDepartmentAttendance($this->manager, $today->copy()->setTime(12, 0));

        $this->assertEquals(1, $result['metrics']['on_leave'] ?? 0);
        $this->assertEquals(0, $result['metrics']['absent'] ?? 0);
    }
}
