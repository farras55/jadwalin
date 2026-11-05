<?php

namespace Tests\Feature\Routing;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $superadmin;

    protected User $manager;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->superadmin = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'superadmin',
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'manager',
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);
    }

    public function test_guest_is_redirected_to_login_for_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_employee_is_forbidden_from_admin_dashboard_and_settings(): void
    {
        $this->actingAs($this->employee)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->employee)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);
    }

    public function test_employee_is_forbidden_from_admin_placeholder_routes(): void
    {
        $this->actingAs($this->employee)
            ->get(route('admin.companies.index'))
            ->assertStatus(403);

        $this->actingAs($this->employee)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    public function test_manager_is_forbidden_from_admin_routes(): void
    {
        $this->actingAs($this->manager)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->manager)
            ->get(route('admin.departments.index'))
            ->assertStatus(403);
    }

    public function test_superadmin_can_access_admin_dashboard_and_placeholders(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($this->superadmin)
            ->get(route('admin.companies.index'))
            ->assertOk();

        $this->actingAs($this->superadmin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }
}
