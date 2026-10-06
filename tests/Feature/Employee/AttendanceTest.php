<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1'
        ]);
        
        $this->department = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'IT Department',
        ]);
        
        $this->user = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
            'max_regular_hours' => 8,
        ]);
    }

    public function test_employee_can_view_attendance_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('employee.attendance'));
        $response->assertStatus(200);
        $response->assertViewIs('employee.attendance.index');
    }

    public function test_employee_can_clock_in_on_time_marked_as_present()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(8, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'id' => $assignment->id,
            'status' => 'present',
            'late_minutes' => 0,
        ]);
    }

    public function test_employee_clocking_in_after_grace_period_marked_as_late()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(8, 16));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'id' => $assignment->id,
            'status' => 'late',
            'late_minutes' => 16,
        ]);
    }

    public function test_employee_cannot_double_clock_in()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda sudah melakukan clock-in hari ini.');
    }

    public function test_employee_can_clock_out_and_work_hours_calculated()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'status' => 'present',
        ]);

        // Clock out 9 hours later. Max regular is 8. Should be 8 regular, 1 overtime.
        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        
        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8, $assignment->regular_hours);
        $this->assertEquals(1, $assignment->overtime_hours);
    }

    public function test_employee_cannot_clock_out_without_clocking_in()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            // no clock_in_time
        ]);

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda belum melakukan clock-in.');
    }
}
