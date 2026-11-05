<?php

namespace Tests\Feature\Console;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoClockOutAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
        ]);
    }

    /**
     * TC-ACO-01: Auto clock-out berhasil memproses shift dan mencatat AuditLog secara transparan.
     */
    public function test_auto_clock_out_creates_audit_log_entry(): void
    {
        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'is_auto_clockout' => true,
            'auto_clockout_buffer_hours' => 1,
            'max_regular_hours' => 8,
        ]);

        // Shift kemarin jam 08:00 - 16:00
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => Carbon::yesterday()->setTime(8, 0),
            'end_time' => Carbon::yesterday()->setTime(16, 0),
        ]);

        $assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => Carbon::yesterday()->setTime(8, 0),
            'clock_out_time' => null,
            'is_auto_clockout' => false,
            'status' => 'present',
        ]);

        // Jalankan perintah auto-clockout
        $this->artisan('jadwalin:auto-clockout')
            ->assertSuccessful();

        $assignment->refresh();
        $this->assertTrue((bool) $assignment->is_auto_clockout);
        $this->assertNotNull($assignment->clock_out_time);
        $this->assertEquals('completed', $assignment->status);

        // Verifikasi entri AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'action' => 'auto_clockout',
            'model_type' => ShiftAssignment::class,
        ]);
    }

    /**
     * TC-ACO-02: Auto clock-out mengabaikan shift jika perusahaan menonaktifkan is_auto_clockout.
     */
    public function test_auto_clock_out_skips_when_disabled_in_settings(): void
    {
        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'is_auto_clockout' => false, // Dinonaktifkan
        ]);

        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => Carbon::yesterday()->setTime(8, 0),
            'end_time' => Carbon::yesterday()->setTime(16, 0),
        ]);

        $assignment = ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => Carbon::yesterday()->setTime(8, 0),
            'clock_out_time' => null,
            'is_auto_clockout' => false,
        ]);

        $this->artisan('jadwalin:auto-clockout')
            ->assertSuccessful();

        $assignment->refresh();
        $this->assertFalse((bool) $assignment->is_auto_clockout);
        $this->assertNull($assignment->clock_out_time);
    }
}
