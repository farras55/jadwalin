<?php

namespace Tests\Feature\Manager;

use App\Models\AttendanceRegularization;
use App\Models\Company;
use App\Models\CompanySetting;
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

    protected Department $departmentA;

    protected Department $departmentB;

    protected User $managerA;

    protected User $employeeA;

    protected User $employeeB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'max_regular_hours' => 8,
        ]);

        $this->departmentA = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Departemen Operasional',
        ]);

        $this->departmentB = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Departemen Dapur',
        ]);

        $this->managerA = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'role' => 'manager',
            'is_global_manager' => false,
            'status' => 'active',
        ]);

        $this->employeeA = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $this->employeeB = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentB->id,
            'role' => 'employee',
            'status' => 'active',
        ]);
    }

    public function test_manager_can_view_department_regularization_list(): void
    {
        $response = $this->actingAs($this->managerA)
            ->get(route('manager.attendance.regularizations.index'));

        $response->assertOk();
        $response->assertViewIs('manager.attendance.regularizations.index');
        $response->assertViewHas('regularizations');
    }

    public function test_manager_can_approve_regularization_and_update_hours(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
        ]);

        $assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employeeA->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'clock_out_time' => Carbon::today()->setTime(17, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 0.0,
            'is_auto_clockout' => true,
        ]);

        $requestedOut = Carbon::today()->setTime(19, 0);

        $regularization = AttendanceRegularization::factory()->create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employeeA->id,
            'requested_clock_out_time' => $requestedOut,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->managerA)
            ->post(route('manager.attendance.regularizations.approve', $regularization), [
                'overtime_status' => 'approved',
                'review_note' => 'Disetujui, lembur terkonfirmasi.',
            ]);

        $response->assertRedirect();
        $regularization->refresh();
        $this->assertEquals('approved', $regularization->status);
        $this->assertEquals($this->managerA->id, $regularization->reviewed_by);

        $assignment->refresh();
        $this->assertEquals($requestedOut->format('Y-m-d H:i:s'), $assignment->clock_out_time->format('Y-m-d H:i:s'));
        $this->assertGreaterThan(0, $assignment->overtime_hours);
    }

    public function test_manager_can_reject_regularization(): void
    {
        $regularization = AttendanceRegularization::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeA->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->managerA)
            ->post(route('manager.attendance.regularizations.reject', $regularization), [
                'review_note' => 'Ditolak: tidak ada konfirmasi lembur.',
            ]);

        $response->assertRedirect();
        $regularization->refresh();
        $this->assertEquals('rejected', $regularization->status);
        $this->assertEquals($this->managerA->id, $regularization->reviewed_by);
    }

    public function test_manager_cannot_approve_out_of_department_regularization(): void
    {
        // Regularization for employee in Department B
        $regularization = AttendanceRegularization::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employeeB->id,
            'status' => 'pending',
        ]);

        // Manager A from Department A attempts to approve
        $response = $this->actingAs($this->managerA)
            ->post(route('manager.attendance.regularizations.approve', $regularization), [
                'review_note' => 'Mencoba approve lintas departemen.',
            ]);

        $response->assertStatus(403);
    }
}
