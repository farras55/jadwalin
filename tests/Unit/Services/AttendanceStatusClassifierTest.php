<?php

namespace Tests\Unit\Services;

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

class AttendanceStatusClassifierTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceMonitorService $service;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AttendanceMonitorService;

        $this->company = Company::factory()->create([
            'timezone' => 'Asia/Jakarta',
        ]);

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

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
        ]);
    }

    public function test_classifies_on_time_when_clock_in_is_within_grace_period(): void
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(10, 0));

        $employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(17, 0),
        ]);

        // Clock in at 08:10 (within 15 min grace period)
        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => $today->copy()->setTime(8, 10),
            'status' => 'present',
        ]);

        $data = $this->service->getTodayDepartmentAttendance($this->manager);

        $this->assertEquals(1, $data['metrics']['on_time']);
        $this->assertEquals('on_time', $data['rows'][0]['status']);
    }

    public function test_classifies_late_when_clock_in_exceeds_grace_period(): void
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(10, 0));

        $employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(17, 0),
        ]);

        // Clock in at 08:20 (exceeds 15 min grace period)
        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => $today->copy()->setTime(8, 20),
            'status' => 'late',
        ]);

        $data = $this->service->getTodayDepartmentAttendance($this->manager);

        $this->assertEquals(1, $data['metrics']['late']);
        $this->assertEquals('late', $data['rows'][0]['status']);
    }

    public function test_classifies_not_started_when_shift_has_not_started(): void
    {
        $today = Carbon::today();
        // Time is 07:30 (shift starts at 08:00)
        Carbon::setTestNow($today->copy()->setTime(7, 30));

        $employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(17, 0),
        ]);

        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);

        $data = $this->service->getTodayDepartmentAttendance($this->manager);

        $this->assertEquals(1, $data['metrics']['not_started']);
        $this->assertEquals('not_started', $data['rows'][0]['status']);
    }

    public function test_classifies_absent_when_time_passes_threshold_without_clock_in(): void
    {
        $today = Carbon::today();
        // Time is 09:00 (shift started at 08:00, grace was until 08:15)
        Carbon::setTestNow($today->copy()->setTime(9, 0));

        $employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(17, 0),
        ]);

        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);

        $data = $this->service->getTodayDepartmentAttendance($this->manager);

        $this->assertEquals(1, $data['metrics']['absent']);
        $this->assertEquals('absent', $data['rows'][0]['status']);
    }
}
