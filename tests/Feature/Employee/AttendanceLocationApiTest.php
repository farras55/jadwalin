<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_api_requires_authentication(): void
    {
        $response = $this->getJson(route('api.employee.attendance-location'));
        $response->assertStatus(401);
    }

    public function test_location_api_returns_json_with_outlet_details_and_no_cache(): void
    {
        $company = Company::factory()->create([
            'office_latitude' => -6.2088,
            'office_longitude' => 106.8456,
            'attendance_radius_meters' => 100,
        ]);

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
            'radius_meters' => 100,
        ]);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)
            ->get(route('api.employee.attendance-location'));

        $response->assertOk();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $response->headers->get('Cache-Control'));
        $response->assertJsonStructure([
            'id',
            'name',
            'address',
            'latitude',
            'longitude',
            'radius_meters',
            'is_configured',
        ]);
        $this->assertTrue($response->json('is_configured'));
    }
}
