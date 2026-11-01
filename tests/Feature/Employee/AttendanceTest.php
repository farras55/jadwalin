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

        ShiftAssignment::forceCreate([
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

        // Clock out 9 jam kemudian (jam 17:00). Max regular hours 8 jam -> 8 reg, 1 ovt.
        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(1.0, $assignment->overtime_hours);
        $this->assertEquals('completed', $assignment->status);
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

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
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

    public function test_part_time_shift_calculates_overtime_when_exceeding_scheduled_hours()
    {
        // Shift part-time 4 jam (10:00 - 14:00)
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Part-Time Shift',
            'start_time' => Carbon::today()->setTime(10, 0),
            'end_time' => Carbon::today()->setTime(14, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => Carbon::today()->setTime(10, 0),
            'status' => 'present',
        ]);

        // Pulang jam 17:00 (bekerja 7 jam: 4 jam reguler, 3 jam lembur)
        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals(4.0, $assignment->regular_hours);
        $this->assertEquals(3.0, $assignment->overtime_hours);
    }

    public function test_employee_cannot_clock_in_too_early_before_shift()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Afternoon Shift',
            'start_time' => Carbon::today()->setTime(14, 0),
            'end_time' => Carbon::today()->setTime(22, 0),
            'quota' => 1,
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
        ]);

        // Mencoba clock in 3 jam sebelum shift (jam 11:00)
        Carbon::setTestNow(Carbon::today()->setTime(11, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Clock In hanya dapat dilakukan paling cepat 60 menit sebelum shift dimulai.');
    }

    public function test_employee_can_clock_out_of_overnight_shift_the_next_morning()
    {
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        // Shift overnight dari jam 22:00 kemarin s.d. 06:00 hari ini
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Night Shift',
            'start_time' => $yesterday->copy()->setTime(22, 0),
            'end_time' => $today->copy()->setTime(6, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'clock_in_time' => $yesterday->copy()->setTime(22, 0),
            'status' => 'present',
        ]);

        // Clock out pagi ini jam 06:00
        Carbon::setTestNow($today->copy()->setTime(6, 0));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
    }
}
