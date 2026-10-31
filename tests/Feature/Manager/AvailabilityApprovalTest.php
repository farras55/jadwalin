<?php

namespace Tests\Feature\Manager;

use App\Models\Availability;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityApprovalTest extends TestCase
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
        string $name = 'Employee Test'
    ): User {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'is_global_manager' => false,
            'role' => 'employee',
            'status' => 'active',
            'name' => $name,
        ]);
    }

    private function createAvailability(
        Company $company,
        User $employee,
        array $overrides = []
    ): Availability {
        return Availability::create(array_merge([
            'company_id' => $company->id,
            'user_id' => $employee->id,
            'is_recurring' => false,
            'day_of_week' => null,
            'start_time' => now()->addDays(2)->setTime(9, 0),
            'end_time' => now()->addDays(2)->setTime(17, 0),
            'reason' => 'Ada keperluan pribadi',
            'status' => 'pending_manager',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ], $overrides));
    }

    public function test_manager_can_view_pending_availabilities_in_own_department(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment(
            $company,
            'IT'
        );

        $otherDepartment = $this->createDepartment(
            $company,
            'HR'
        );

        $position = $this->createPosition(
            $company,
            $department
        );

        $otherPosition = $this->createPosition(
            $company,
            $otherDepartment,
            'HR Staff'
        );

        $manager = $this->createManager(
            $company,
            $department
        );

        $employee = $this->createEmployee(
            $company,
            $department,
            $position,
            'Employee IT'
        );

        $otherEmployee = $this->createEmployee(
            $company,
            $otherDepartment,
            $otherPosition,
            'Employee HR'
        );

        $ownAvailability = $this->createAvailability(
            $company,
            $employee
        );

        $otherAvailability = $this->createAvailability(
            $company,
            $otherEmployee,
            [
                'reason' => 'Keperluan pribadi HR',
            ]
        );

        $response = $this->actingAs($manager)
            ->get(route('manager.availability.index'));

        $response->assertOk();

        $response->assertSee($employee->name);
        $response->assertSee($ownAvailability->reason);

        $response->assertDontSee($otherEmployee->name);
        $response->assertDontSee($otherAvailability->reason);
    }

    public function test_manager_can_approve_availability_request(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment(
            $company
        );

        $position = $this->createPosition(
            $company,
            $department
        );

        $manager = $this->createManager(
            $company,
            $department
        );

        $employee = $this->createEmployee(
            $company,
            $department,
            $position
        );

        $availability = $this->createAvailability(
            $company,
            $employee
        );

        $response = $this->actingAs($manager)
            ->post(
                route(
                    'manager.availability.approve',
                    $availability
                )
            );

        $response
            ->assertRedirect(
                route('manager.availability.index')
            )
            ->assertSessionHas('success');

        $availability->refresh();

        $this->assertSame(
            'approved',
            $availability->status
        );

        $this->assertSame(
            $manager->id,
            $availability->approved_by
        );

        $this->assertNotNull(
            $availability->approved_at
        );

        $this->assertNull(
            $availability->rejection_reason
        );
    }

    public function test_manager_can_reject_availability_request_with_reason(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment(
            $company
        );

        $position = $this->createPosition(
            $company,
            $department
        );

        $manager = $this->createManager(
            $company,
            $department
        );

        $employee = $this->createEmployee(
            $company,
            $department,
            $position
        );

        $availability = $this->createAvailability(
            $company,
            $employee
        );

        $reason = 'Jadwal kerja tidak dapat disesuaikan.';

        $response = $this->actingAs($manager)
            ->post(
                route(
                    'manager.availability.reject',
                    $availability
                ),
                [
                    'rejection_reason' => $reason,
                ]
            );

        $response
            ->assertRedirect(
                route('manager.availability.index')
            )
            ->assertSessionHas('success');

        $availability->refresh();

        $this->assertSame(
            'rejected',
            $availability->status
        );

        $this->assertSame(
            $manager->id,
            $availability->approved_by
        );

        $this->assertNotNull(
            $availability->approved_at
        );

        $this->assertSame(
            $reason,
            $availability->rejection_reason
        );
    }

    public function test_manager_cannot_approve_request_from_different_department(): void
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

        $position = $this->createPosition(
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
            $position
        );

        $availability = $this->createAvailability(
            $company,
            $employee
        );

        $response = $this->actingAs($manager)
            ->post(
                route(
                    'manager.availability.approve',
                    $availability
                )
            );

        $response->assertForbidden();

        $availability->refresh();

        $this->assertSame(
            'pending_manager',
            $availability->status
        );

        $this->assertNull(
            $availability->approved_by
        );

        $this->assertNull(
            $availability->approved_at
        );
    }

    public function test_employee_cannot_access_manager_approval_panel(): void
    {
        $company = $this->createCompany();

        $department = $this->createDepartment(
            $company
        );

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
            ->get(
                route('manager.availability.index')
            );

        $response->assertForbidden();
    }
}
