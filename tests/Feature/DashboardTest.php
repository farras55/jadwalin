<?php

namespace Tests\Feature;

use App\Models\AttendanceRegularization;
use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_sees_admin_specific_dashboard_metrics(): void
    {
        $company = Company::factory()->create();
        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Super Admin');
        $response->assertSee('Identitas Perusahaan');
    }

    public function test_manager_without_company_is_forbidden(): void
    {
        $manager = User::factory()->create([
            'company_id' => null,
            'role' => 'manager',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'));

        $response->assertForbidden();
    }

    public function test_manager_with_valid_company_sees_department_schedule_and_pending_approvals(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'manager',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'));

        $response->assertOk();
        $response->assertSee('Manager');
        $response->assertSee('Jadwal Kerja Tim');
        $response->assertSee('Koreksi Presensi');
        $response->assertSee('Koreksi Kehadiran');
    }

    public function test_manager_sees_pending_regularization_count_and_spotlight(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $manager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'manager',
        ]);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'employee',
            'name' => 'Budi Santoso',
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Pagi Khusus',
        ]);
        $shiftAssignment = ShiftAssignment::factory()->create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
        ]);

        AttendanceRegularization::factory()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_assignment_id' => $shiftAssignment->id,
            'status' => 'pending',
            'reason' => 'Lupa clock out karena server restart.',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Shift Pagi Khusus');
        $response->assertSee('Lupa clock out karena server restart.');
        $response->assertSee('Koreksi Kehadiran');
        $response->assertSee(route('manager.attendance.regularizations.index'));
    }

    public function test_manager_does_not_see_pending_regularization_from_other_departments_unless_global(): void
    {
        $company = Company::factory()->create();
        $dept1 = Department::factory()->create(['company_id' => $company->id]);
        $dept2 = Department::factory()->create(['company_id' => $company->id]);

        $deptManager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept1->id,
            'role' => 'manager',
            'is_global_manager' => false,
        ]);
        $globalManager = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept1->id,
            'role' => 'manager',
            'is_global_manager' => true,
        ]);

        $otherEmployee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept2->id,
            'role' => 'employee',
            'name' => 'Siti Rahma',
        ]);
        $otherShift = Shift::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept2->id,
            'title' => 'Shift Logistik Malam',
        ]);
        $otherAssignment = ShiftAssignment::factory()->create([
            'company_id' => $company->id,
            'shift_id' => $otherShift->id,
            'user_id' => $otherEmployee->id,
        ]);

        AttendanceRegularization::factory()->create([
            'company_id' => $company->id,
            'employee_id' => $otherEmployee->id,
            'shift_assignment_id' => $otherAssignment->id,
            'status' => 'pending',
            'reason' => 'Koreksi staf departemen lain',
        ]);

        // Regular dept manager should NOT see other department's pending regularization
        $resp1 = $this->actingAs($deptManager)->get(route('manager.dashboard'));
        $resp1->assertOk();
        $resp1->assertDontSee('Siti Rahma');
        $resp1->assertDontSee('Shift Logistik Malam');

        // Global manager should see other department's pending regularization
        $resp2 = $this->actingAs($globalManager)->get(route('manager.dashboard'));
        $resp2->assertOk();
        $resp2->assertSee('Siti Rahma');
        $resp2->assertSee('Shift Logistik Malam');
    }

    public function test_employee_without_company_is_forbidden(): void
    {
        $employee = User::factory()->create([
            'company_id' => null,
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)
            ->get(route('employee.dashboard'));

        $response->assertForbidden();
    }

    public function test_employee_with_valid_company_sees_own_assigned_shifts_and_hours(): void
    {
        $company = Company::factory()->create();
        $department = Department::factory()->create(['company_id' => $company->id]);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)
            ->get(route('employee.dashboard'));

        $response->assertOk();
        $response->assertSee('Karyawan');
        $response->assertSee('Jadwal Saya');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
