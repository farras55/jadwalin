<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_role_helper_methods(): void
    {
        $superadmin = new User(['role' => 'superadmin']);
        $this->assertTrue($superadmin->isSuperadmin());
        $this->assertFalse($superadmin->isManager());
        $this->assertFalse($superadmin->isEmployee());

        $manager = new User(['role' => 'manager']);
        $this->assertFalse($manager->isSuperadmin());
        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isEmployee());

        $employee = new User(['role' => 'employee']);
        $this->assertFalse($employee->isSuperadmin());
        $this->assertFalse($employee->isManager());
        $this->assertTrue($employee->isEmployee());
    }

    public function test_employment_type_helper_methods(): void
    {
        $fullTime = new User(['employment_type' => 'full_time']);
        $this->assertTrue($fullTime->isFullTime());
        $this->assertFalse($fullTime->isPartTime());
        $this->assertFalse($fullTime->isFreelance());

        $partTime = new User(['employment_type' => 'part_time']);
        $this->assertFalse($partTime->isFullTime());
        $this->assertTrue($partTime->isPartTime());
        $this->assertFalse($partTime->isFreelance());

        $freelance = new User(['employment_type' => 'freelance']);
        $this->assertFalse($freelance->isFullTime());
        $this->assertFalse($freelance->isPartTime());
        $this->assertTrue($freelance->isFreelance());
    }

    public function test_branch_id_accessor_and_mutator_maps_to_department_id(): void
    {
        $user = new User;
        $user->branch_id = 42;

        $this->assertEquals(42, $user->department_id);
        $this->assertEquals(42, $user->branch_id);
    }
}
