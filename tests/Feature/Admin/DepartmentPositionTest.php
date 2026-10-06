<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentPositionTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(string $name = 'Test Company'): Company
    {
        return Company::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createSuperadmin(Company $company): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    public function test_superadmin_can_view_department_and_position_list(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Human Resources',
            'description' => 'Department HR',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->get(route('admin.departments.index'));

        $response->assertStatus(200);
        $response->assertSee('Human Resources');
        $response->assertSee('Department HR');

        $positionResponse = $this
            ->actingAs($superadmin)
            ->get(route('admin.positions.index'));

        $positionResponse->assertStatus(200);
        $positionResponse->assertSee('Human Resources');
    }

    public function test_superadmin_can_create_department_with_valid_data(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('admin.departments.store'), [
                'name' => 'Finance',
                'description' => 'Department Finance',
            ]);

        $response->assertRedirect(route('admin.departments.index'));

        $this->assertDatabaseHas('departments', [
            'company_id' => $company->id,
            'name' => 'Finance',
            'description' => 'Department Finance',
        ]);
    }

    public function test_duplicate_department_name_in_same_company_is_rejected(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        Department::create([
            'company_id' => $company->id,
            'name' => 'Marketing',
            'description' => 'Existing department',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('admin.departments.store'), [
                'name' => 'Marketing',
                'description' => 'Duplicate department',
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('departments', 1);
    }

    public function test_user_cannot_access_departments_from_another_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');

        $superadmin = $this->createSuperadmin($companyA);

        $departmentB = Department::create([
            'company_id' => $companyB->id,
            'name' => 'Department Company B',
            'description' => 'Private department',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->get(route('admin.departments.edit', $departmentB));

        $response->assertStatus(404);
        $response->assertDontSee('Department Company B');
    }

    public function test_non_admin_cannot_access_department_crud(): void
    {
        $company = $this->createCompany();

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($manager)
            ->get(route('admin.departments.index'));

        $response->assertStatus(403);
    }
}