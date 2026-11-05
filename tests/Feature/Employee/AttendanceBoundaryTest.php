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

class AttendanceBoundaryTest extends TestCase
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

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius' => 100,
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
        ]);

        // Shift dimulai jam 08:00 sampai 16:00 hari ini
        $this->shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => Carbon::today()->setTime(8, 0),
            'end_time' => Carbon::today()->setTime(16, 0),
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
     * TC-BND-01: Clock-in tepat 60 menit sebelum shift (jam 07:00) -> DIIZINKAN.
     */
    public function test_clock_in_at_exact_60_minutes_before_shift_is_allowed(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'));

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
        Carbon::setTestNow(Carbon::today()->setTime(6, 59));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'));

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
        Carbon::setTestNow(Carbon::today()->setTime(8, 15));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'));

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
        Carbon::setTestNow(Carbon::today()->setTime(8, 16));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assignment->refresh();
        $this->assertEquals('late', $this->assignment->status);
        $this->assertEquals(16, $this->assignment->late_minutes);
    }

    /**
     * TC-BND-05: Clock-in setelah shift berakhir (jam 16:01) -> DITOLAK.
     */
    public function test_clock_in_after_shift_ended_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(16, 1));

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.clock-in'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Shift telah berakhir. Presensi tidak dapat dilakukan.');

        $this->assignment->refresh();
        $this->assertNull($this->assignment->clock_in_time);
    }
}
