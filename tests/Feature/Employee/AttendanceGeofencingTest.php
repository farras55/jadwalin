<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceGeofencingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $employee;

    protected Shift $shift;

    protected ShiftAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
        ]);

        // Kantor Monas: -6.175392, 106.827153, Radius 100 Meter
        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius' => 100,
            'radius_meters' => 100,
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(8, 0));

        $this->shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $today->copy()->setTime(8, 0),
            'end_time' => $today->copy()->setTime(17, 0),
        ]);

        $this->assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $this->shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => null,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Test normal clock-in when inside office radius.
     */
    public function test_clock_in_allowed_when_within_office_radius(): void
    {
        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => -6.175392,
                'longitude' => 106.827153,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assignment->refresh();
        $this->assertNotNull($this->assignment->clock_in_time);
    }

    /**
     * Test defect discovery: clock-in should be rejected when coordinates are outside office radius.
     * Lokasi Bundaran HI (-6.195048, 106.823023) berjarak ~2.2 km dari kantor Monas (radius 100m).
     */
    public function test_clock_in_rejected_when_outside_office_radius(): void
    {
        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => -6.195048,
                'longitude' => 106.823023,
            ]);

        // Harapan sistem yang aman: Clock-In harus ditolak dengan error pesan geofencing
        $response->assertSessionHas('error');

        $this->assignment->refresh();
        $this->assertNull($this->assignment->clock_in_time);
    }
}
