<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndRoleMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_admin_routes_and_not_manager_routes(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Super Admin');
        $response->assertSee('Perusahaan & Cabang');

        $responseCompany = $this->actingAs($superadmin)
            ->get(route('admin.companies.index'));

        $responseCompany->assertStatus(200);

        $forbiddenResponse = $this->actingAs($superadmin)
            ->get(route('manager.dashboard'));

        $forbiddenResponse->assertStatus(403);
    }

    public function test_manager_can_access_manager_routes_and_not_admin_routes(): void
    {
        $company = Company::factory()->create();

        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Departemen Pengujian',
            'description' => 'Departemen untuk pengujian dashboard.',
        ]);

        $manager = User::factory()->create([
            'role' => 'manager',
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manager');
        $response->assertSee('Jadwal Kerja Tim');

        $responseShifts = $this->actingAs($manager)
            ->get(route('manager.shifts.index'));

        $responseShifts->assertStatus(200);

        $forbiddenResponse = $this->actingAs($manager)
            ->get(route('admin.dashboard'));

        $forbiddenResponse->assertStatus(403);
    }

    public function test_employee_can_access_employee_routes_and_not_admin_routes(): void
    {
        $company = Company::factory()->create();

        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Departemen Pengujian',
            'description' => 'Departemen untuk pengujian dashboard.',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        $response = $this->actingAs($employee)
            ->get(route('employee.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Karyawan');
        $response->assertSee('Jadwal Saya');

        $responseSchedule = $this->actingAs($employee)
            ->get(route('employee.schedule'));

        $responseSchedule->assertStatus(200);

        $forbiddenResponse = $this->actingAs($employee)
            ->get(route('admin.dashboard'));

        $forbiddenResponse->assertStatus(403);
    }
}