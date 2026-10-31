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

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $company;

    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1',
        ]);

        $this->department = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'IT Department',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
            'radius_meters' => 100,
            'address' => 'Jl. Jenderal Sudirman No. 1',
        ]);

        $this->user = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
            'max_regular_hours' => 8,
        ]);
    }

    public function test_employee_can_view_attendance_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('employee.attendance'));
        $response->assertStatus(200);
        $response->assertViewIs('employee.attendance.index');
        $response->assertViewHas(['officeLatitude', 'officeLongitude', 'attendanceRadius', 'officeLat', 'officeLng', 'radius', 'branch']);
    }

    public function test_attendance_page_uses_coordinates_from_user_branch()
    {
        $this->department->update([
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'radius' => 150,
        ]);

        $this->assertEquals(-6.917464, $this->user->branch->latitude);
        $this->assertEquals(107.619123, $this->user->branch->longitude);
        $this->assertEquals(150, $this->user->branch->radius);

        $response = $this->actingAs($this->user)->get(route('employee.attendance'));
        $response->assertStatus(200);
        $response->assertViewHas('officeLat', -6.917464);
        $response->assertViewHas('officeLng', 107.619123);
        $response->assertViewHas('radius', 150);
    }

    public function test_employee_can_clock_in_on_time_marked_as_present()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(8, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'id' => $assignment->id,
            'status' => 'present',
            'late_minutes' => 0,
        ]);
    }

    public function test_employee_clocking_in_after_grace_period_marked_as_late()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(8, 16));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'id' => $assignment->id,
            'status' => 'late',
            'late_minutes' => 16,
        ]);
    }

    public function test_employee_cannot_double_clock_in()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda sudah melakukan clock-in hari ini.');
    }

    public function test_employee_can_clock_out_and_work_hours_calculated()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => Carbon::today()->setTime(8, 0),
            'status' => 'present',
        ]);

        // Clock out 9 hours later. Max regular is 8. Should be 8 regular, 1 overtime.
        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();

        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8, $assignment->regular_hours);
        $this->assertEquals(1, $assignment->overtime_hours);
    }

    public function test_employee_cannot_clock_out_without_clocking_in()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            // no clock_in_time
        ]);

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda belum melakukan clock-in.');
    }

    public function test_employee_location_api_endpoint_returns_json()
    {
        $response = $this->actingAs($this->user)->get(route('api.employee.attendance-location'));

        $response->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $response->assertJson([
            'id' => $this->department->id,
            'name' => 'IT Department',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'is_configured' => true,
        ]);
    }

    public function test_clock_in_rejected_when_location_is_unconfigured()
    {
        $this->department->update([
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->company->update([
            'office_latitude' => null,
            'office_longitude' => null,
        ]);

        CompanySetting::where('company_id', $this->company->id)->update([
            'office_latitude' => null,
            'office_longitude' => null,
        ]);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Lokasi cabang belum diatur oleh admin. Presensi tidak dapat dilakukan.');
    }

    public function test_clock_in_allowed_when_branch_location_is_configured()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(8, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('shift_assignments', [
            'id' => $assignment->id,
            'status' => 'present',
        ]);
    }

    public function test_clock_in_rejected_when_shift_has_ended()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Morning Shift',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(17, 0),
            'quota' => 1,
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        // Shift berakhir jam 17:00, user mencoba clock-in jam 17:05
        Carbon::setTestNow(Carbon::today()->setTime(17, 5));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Shift telah berakhir. Presensi tidak dapat dilakukan.');
    }
}
