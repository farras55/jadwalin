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
        $response->assertViewHas([
            'officeLatitude',
            'officeLongitude',
            'attendanceRadius',
            'officeLat',
            'officeLng',
            'radius',
            'branch',
        ]);
        $response->assertDontSee('Ekspor CSV');
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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in')
        );

        $response->assertRedirect();
        $response->assertSessionHas(
            'error',
            'Anda sudah melakukan clock-in hari ini.'
        );
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

        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-out')
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();

        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
        $this->assertEquals('none', $assignment->overtime_status);
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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-out')
        );

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda belum melakukan clock-in.');
    }

    public function test_employee_location_api_endpoint_returns_json()
    {
        $response = $this->actingAs($this->user)->get(
            route('api.employee.attendance-location')
        );

        $response->assertStatus(200);

        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control')
        );

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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in')
        );

        $response->assertRedirect();

        $response->assertSessionHas(
            'error',
            'Lokasi cabang belum diatur oleh admin. Presensi tidak dapat dilakukan.'
        );
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

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

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

        Carbon::setTestNow(Carbon::today()->setTime(17, 5));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHas(
            'error',
            'Shift telah berakhir. Presensi tidak dapat dilakukan.'
        );
    }

    public function test_part_time_shift_calculates_overtime_when_exceeding_scheduled_hours()
    {
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

        Carbon::setTestNow(Carbon::today()->setTime(17, 0));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-out')
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();

        $this->assertEquals(4.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
        $this->assertEquals('pending_approval', $assignment->overtime_status);
        $this->assertDatabaseHas('attendance_regularizations', [
            'shift_assignment_id' => $assignment->id,
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);
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

        Carbon::setTestNow(Carbon::today()->setTime(11, 0));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHas(
            'error',
            'Clock In hanya dapat dilakukan paling cepat 60 menit sebelum shift dimulai.'
        );
    }

    public function test_employee_can_clock_out_of_overnight_shift_the_next_morning()
    {
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

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

        Carbon::setTestNow($today->copy()->setTime(6, 0));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-out')
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();

        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
        $this->assertEquals('completed', $assignment->status);
    }

    public function test_employee_can_clock_in_past_midnight_for_overnight_shift()
    {
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Overnight Shift',
            'start_time' => $yesterday->copy()->setTime(22, 0),
            'end_time' => $today->copy()->setTime(6, 0),
            'quota' => 1,
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'status' => 'scheduled',
            'clock_in_time' => null,
            'clock_out_time' => null,
        ]);

        // Clock in pukul 00:30 (melewati tengah malam)
        Carbon::setTestNow($today->copy()->setTime(0, 30));

        $response = $this->actingAs($this->user)->post(
            route('employee.attendance.clock-in'),
            [
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertNotNull($assignment->clock_in_time);
        $this->assertEquals('late', $assignment->status);
        $this->assertEquals(150, $assignment->late_minutes);
    }

    public function test_clock_out_within_late_tolerance_buffer_does_not_create_regularization()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Day Shift',
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

        // Clock-out pada 17:10 (10 menit setelah shift, masih dalam toleransi 15 menit)
        Carbon::setTestNow(Carbon::today()->setTime(17, 10));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('completed', $assignment->status);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
        $this->assertEquals('none', $assignment->overtime_status);

        // Tidak membuat tiket AttendanceRegularization
        $this->assertDatabaseMissing('attendance_regularizations', [
            'shift_assignment_id' => $assignment->id,
        ]);
    }

    public function test_clock_out_exceeding_late_tolerance_buffer_creates_regularization()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Day Shift',
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

        // Clock-out pada 17:25 (25 menit setelah shift, melebihi toleransi 15 menit)
        Carbon::setTestNow(Carbon::today()->setTime(17, 25));

        $response = $this->actingAs($this->user)->post(route('employee.attendance.clock-out'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('completed', $assignment->status);
        $this->assertEquals(8.0, $assignment->regular_hours);
        $this->assertEquals(0.0, $assignment->overtime_hours);
        $this->assertEquals('pending_approval', $assignment->overtime_status);

        // Membuat tiket AttendanceRegularization
        $this->assertDatabaseHas('attendance_regularizations', [
            'shift_assignment_id' => $assignment->id,
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);
    }

    public function test_history_shows_not_attended_status_when_employee_has_not_clocked_in()
    {
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(16, 0),
            'quota' => 1,
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'shift_id' => $shift->id,
            'status' => 'scheduled',
            'clock_in_time' => null,
            'clock_out_time' => null,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(7, 30));

        $response = $this->actingAs($this->user)->get(route('employee.attendance'));

        $response->assertStatus(200);
        $response->assertSee('Belum Hadir');
        $response->assertSee('bg-amber-50');
        $this->assertCount(1, $response->viewData('recentHistory'));
        $this->assertNull($response->viewData('recentHistory')->first()->clock_in_time);
    }
}
