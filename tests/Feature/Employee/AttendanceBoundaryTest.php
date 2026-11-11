<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private ShiftAssignment $assignment;
    private Shift $shift;
    private string $timezone = 'Asia/Jakarta';

    // Koordinat dummy valid agar lolos validasi geofencing
    private float $validLatitude = -6.2088;
    private float $validLongitude = 106.8456;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create(['timezone' => $this->timezone]);
        
        CompanySetting::factory()->create([
            'company_id' => $company->id,
            'grace_period_minutes' => 15,
            'office_latitude' => $this->validLatitude,
            'office_longitude' => $this->validLongitude,
            'attendance_radius_meters' => 100,
        ]);

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'latitude' => $this->validLatitude,
            'longitude' => $this->validLongitude,
            'radius_meters' => 100,
        ]);

        // PERBAIKAN: Mendefinisikan role langsung tanpa menggunakan state ->employee()
        $this->employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'employee',
        ]);

        $template = ShiftTemplate::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        // Shift untuk hari ini pukul 08:00 - 17:00
        $this->shift = Shift::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'template_id' => $template->id,
            'start_time' => Carbon::today($this->timezone)->setHour(8)->setMinute(0),
            'end_time' => Carbon::today($this->timezone)->setHour(17)->setMinute(0),
            'status' => 'published'
        ]);

        $this->assignment = ShiftAssignment::factory()->create([
            'company_id' => $company->id,
            'shift_id' => $this->shift->id,
            'user_id' => $this->employee->id,
            'status' => 'scheduled',
            'clock_in_time' => null,
            'clock_out_time' => null,
        ]);
    }

    /**
     * TC-BND-01: Clock-in tepat 60 menit sebelum shift (jam 07:00) -> DIIZINKAN.
     */
    public function test_clock_in_at_exact_60_minutes_before_shift_is_allowed(): void
    {
        $shiftStart = Carbon::parse($this->shift->getRawOriginal('start_time'), $this->timezone);
        Carbon::setTestNow($shiftStart->copy()->subMinutes(60));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => $this->validLatitude,
                'longitude' => $this->validLongitude,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assignment->refresh();
        $this->assertNotNull($this->assignment->clock_in_time);
    }

    /**
     * TC-BND-02: Clock-in 61 menit sebelum shift (jam 06:59) -> DITOLAK.
     */
    public function test_clock_in_at_61_minutes_before_shift_is_rejected(): void
    {
        $shiftStart = Carbon::parse($this->shift->getRawOriginal('start_time'), $this->timezone);
        Carbon::setTestNow($shiftStart->copy()->subMinutes(61));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => $this->validLatitude,
                'longitude' => $this->validLongitude,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Clock In hanya dapat dilakukan paling cepat 60 menit sebelum shift dimulai.');

        $this->assignment->refresh();
        $this->assertNull($this->assignment->clock_in_time);
    }

    /**
     * TC-BND-03: Clock-in tepat di menit ke-15 toleransi (jam 08:15) -> Berstatus present (tepat waktu).
     */
    public function test_clock_in_at_exact_15_minutes_grace_period_is_marked_present(): void
    {
        $shiftStart = Carbon::parse($this->shift->getRawOriginal('start_time'), $this->timezone);
        Carbon::setTestNow($shiftStart->copy()->addMinutes(15));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => $this->validLatitude,
                'longitude' => $this->validLongitude,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assignment->refresh();
        $this->assertEquals('present', $this->assignment->status);
        $this->assertEquals(0, $this->assignment->late_minutes);
    }

    /**
     * TC-BND-04: Clock-in di menit ke-16 (jam 08:16) -> Berstatus late dengan late_minutes = 16.
     */
    public function test_clock_in_at_16_minutes_is_marked_late(): void
    {
        $shiftStart = Carbon::parse($this->shift->getRawOriginal('start_time'), $this->timezone);
        Carbon::setTestNow($shiftStart->copy()->addMinutes(16));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => $this->validLatitude,
                'longitude' => $this->validLongitude,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assignment->refresh();
        $this->assertEquals('late', $this->assignment->status);
        $this->assertEquals(16, $this->assignment->late_minutes);
    }

    /**
     * TC-BND-05: Clock-in setelah shift berakhir (jam 17:01) -> DITOLAK.
     */
    public function test_clock_in_after_shift_ended_is_rejected(): void
    {
        $shiftEnd = Carbon::parse($this->shift->getRawOriginal('end_time'), $this->timezone);
        Carbon::setTestNow($shiftEnd->copy()->addMinute());

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'), [
                'latitude' => $this->validLatitude,
                'longitude' => $this->validLongitude,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Shift telah berakhir. Presensi tidak dapat dilakukan.');

        $this->assignment->refresh();
        $this->assertNull($this->assignment->clock_in_time);
    }
}