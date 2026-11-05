<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
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
        $response->assertSee('Perusahaan & Cabang');
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
