<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRegularizationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $employee;

    protected User $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $this->otherEmployee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);
    }

    public function test_employee_can_view_regularization_index_page(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('employee.attendance.regularization.index'));

        $response->assertOk();
        $response->assertViewIs('employee.attendance.regularizations.index');
        $response->assertViewHas('regularizations');
    }

    public function test_employee_can_request_regularization_index_as_json(): void
    {
        $response = $this->actingAs($this->employee)
            ->getJson(route('employee.attendance.regularization.index'));

        $response->assertOk();
        $response->assertJsonStructure(['data', 'current_page']);
    }

    public function test_employee_can_submit_regularization_for_auto_clocked_out_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
        ]);

        $assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'clock_out_time' => Carbon::today()->setTime(17, 0),
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $assignment->id,
                'requested_clock_out_time' => Carbon::today()->setTime(19, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Lembur menyelesaikan laporan closing shift bulanan sampai malam.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_regularizations', [
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_employee_cannot_submit_regularization_for_another_employees_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $otherAssignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->otherEmployee->id,
            'is_auto_clockout' => true,
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $otherAssignment->id,
                'requested_clock_out_time' => Carbon::today()->setTime(19, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Mencoba mengajukan shift rekan kerja.',
            ]);

        $response->assertSessionHasErrors('shift_assignment_id');
    }

    public function test_employee_cannot_submit_regularization_for_normal_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'is_auto_clockout' => false, // Normal shift
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $assignment->id,
                'requested_clock_out_time' => Carbon::today()->setTime(19, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Mencoba mengajukan shift normal.',
            ]);

        $response->assertSessionHasErrors('shift_assignment_id');
    }
}
