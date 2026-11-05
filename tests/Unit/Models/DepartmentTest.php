<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_location_configured_returns_true_when_both_coordinates_present(): void
    {
        $department = new Department([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $this->assertTrue($department->isLocationConfigured());
    }

    public function test_is_location_configured_returns_false_when_coordinates_are_null(): void
    {
        $department = new Department;
        $department->latitude = null;
        $department->longitude = null;

        $this->assertFalse($department->isLocationConfigured());
    }

    public function test_radius_and_radius_meters_are_synchronized(): void
    {
        $department = new Department;
        $department->radius = 150;

        $this->assertEquals(150, $department->radius);
        $this->assertEquals(150, $department->radius_meters);

        $department->radius_meters = 200;
        $this->assertEquals(200, $department->radius);
        $this->assertEquals(200, $department->radius_meters);
    }

    public function test_department_falls_back_to_company_coordinates(): void
    {
        $company = Company::factory()->create([
            'office_latitude' => -7.250445,
            'office_longitude' => 112.768845,
            'attendance_radius_meters' => 120,
        ]);

        $department = new Department;
        $department->company_id = $company->id;
        $department->setRelation('company', $company);

        $this->assertEquals(-7.250445, $department->latitude);
        $this->assertEquals(112.768845, $department->longitude);
        $this->assertEquals(120, $department->radius);
    }
}
