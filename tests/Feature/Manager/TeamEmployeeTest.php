<?php

namespace Tests\Feature\Manager;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Company Test',
            'email' => 'company@test.com',
            'phone_number' => '081234567890',
            'address' => 'Alamat Test',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createDepartment(
        Company $company,
        string $name = 'IT'
    ): Department {
        return Department::create([
            'company_id' => $company->id,
            'name' => $name,
            'description' => 'Department test',
        ]);
    }

    private function createPosition(
        Company $company,
        Department $department,
        string $name = 'Staff'
    ): Position {
        return Position::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'name' => $name,
        ]);
    }

    private function createManager(
        Company $company,
        Department $department
    ): User {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'position_id' => null,
            'is_global_manager' => false,
            'role' => 'manager',
            'status' => 'active',
        ]);
    }

    private function createEmployee(
        Company $company,
        Department $department,
        Position $position,
        string $name = 'Employee Test',
        array $overrides = []
    ): User {
        return User::factory()->create(array_merge([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'is_global_manager' => false,
            'role' => 'employee',
            'status' => 'active',
            'employment_type' => 'full_time',
            'max_weekly_hours' => 40,
            'phone_number' => '081234567890',
            'name' => $name,
        ], $overrides));
    }

    public function test_manager_can_view_employee_list_in_own_department(): void
    {
        $company = $this->createCompany();

        $itDepartment = $this->createDepartment($company, 'IT');
        $hrDepartment = $this->createDepartment($company, 'HR');

        $itPosition = $this->createPosition(
            $company,
            $itDepartment,
            'IT Staff'
        );

        $hrPosition = $this->createPosition(
            $company,
            $hrDepartment,
            'HR Staff'
        );

        $manager = $this->createManager($company, $itDepartment);

        $employee = $this->createEmployee(
            $company,
            $itDepartment,
            $itPosition,
            'Employee IT'
        );

        $otherEmployee = $this->createEmployee(
            $company,
            $hrDepartment,
            $hrPosition,
            'Employee HR'
        );

        $response = $this->actingAs($manager)
            ->get(route('manager.employees.index'));

        $response->assertOk();
        $response->assertViewIs('manager.employees.index');
        $response->assertSee($employee->name);
        $response->assertSee($employee->phone_number);
        $response->assertDontSee($otherEmployee->name);
    }

    public function test_manager_cannot_view_employee_details_from_different_department(): void
    {
        $company = $this->createCompany();

        $managerDepartment = $this->createDepartment(
            $company,
            'IT'
        );

        $employeeDepartment = $this->createDepartment(
            $company,
            'HR'
        );

        $employeePosition = $this->createPosition(
            $company,
            $employeeDepartment,
            'HR Staff'
        );

        $manager = $this->createManager(
            $company,
            $managerDepartment
        );

        $employee = $this->createEmployee(
            $company,
            $employeeDepartment,
            $employeePosition,
            'Employee HR'
        );

        $response = $this->actingAs($manager)
            ->get(route('manager.employees.show', $employee));

        $response->assertForbidden();
    }

    public function test_search_and_filter_by_employment_type_and_position(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment($company);

        $staffPosition = $this->createPosition(
            $company,
            $department,
            'Staff'
        );

        $supervisorPosition = $this->createPosition(
            $company,
            $department,
            'Supervisor'
        );

        $manager = $this->createManager($company, $department);

        $partTimeStaff = $this->createEmployee(
            $company,
            $department,
            $staffPosition,
            'Rina Part Time',
            [
                'employment_type' => 'part_time',
                'phone_number' => '081111111111',
            ]
        );

        $this->createEmployee(
            $company,
            $department,
            $staffPosition,
            'Budi Full Time',
            [
                'employment_type' => 'full_time',
                'phone_number' => '082222222222',
            ]
        );

        $this->createEmployee(
            $company,
            $department,
            $supervisorPosition,
            'Siti Freelance',
            [
                'employment_type' => 'freelance',
                'phone_number' => '083333333333',
            ]
        );

        $searchResponse = $this->actingAs($manager)
            ->get(route('manager.employees.index', [
                'search' => 'Rina',
            ]));

        $searchResponse->assertOk();
        $searchResponse->assertSee($partTimeStaff->name);
        $searchResponse->assertDontSee('Budi Full Time');
        $searchResponse->assertDontSee('Siti Freelance');

        $filterResponse = $this->actingAs($manager)
            ->get(route('manager.employees.index', [
                'employment_type' => 'part_time',
                'position_id' => $staffPosition->id,
            ]));

        $filterResponse->assertOk();
        $filterResponse->assertSee($partTimeStaff->name);
        $filterResponse->assertDontSee('Budi Full Time');
        $filterResponse->assertDontSee('Siti Freelance');
    }

    public function test_employee_cannot_access_manager_employee_directory(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment($company);

        $position = $this->createPosition(
            $company,
            $department
        );

        $employee = $this->createEmployee(
            $company,
            $department,
            $position
        );

        $response = $this->actingAs($employee)
            ->get(route('manager.employees.index'));

        $response->assertForbidden();
    }
}
