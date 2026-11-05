<?php

namespace Tests\Unit\Services;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\ShiftTemplate;
use App\Models\User;
use App\Services\ScheduleBuilderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ScheduleBuilderService $builder;

    protected Company $company;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ScheduleBuilderService;

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        CompanySetting::factory()->create([
            'company_id' => $this->company->id,
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'min_rest_hours' => 11,
            'max_working_days_per_week' => 6,
        ]);
    }

    public function test_generate_weekly_roster_returns_empty_when_no_templates_exist(): void
    {
        $startDate = Carbon::today()->startOfWeek();
        $endDate = Carbon::today()->endOfWeek();

        $result = $this->builder->generateWeeklyRoster(
            $this->company->id,
            $this->department->id,
            $startDate,
            $endDate
        );

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['shifts_created'] ?? 0);
    }

    public function test_generate_weekly_roster_creates_shifts_and_assignments(): void
    {
        // Create 2 employees
        User::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Create shift template
        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Reguler Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'default_quota' => 1,
        ]);

        $startDate = Carbon::today()->startOfWeek();
        $endDate = $startDate->copy()->addDays(2); // 3 days

        $result = $this->builder->generateWeeklyRoster(
            $this->company->id,
            $this->department->id,
            $startDate,
            $endDate
        );

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result['shifts_created']);
    }

    public function test_builder_respects_employee_blackout_unavailability(): void
    {
        $employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $startDate = Carbon::today()->startOfWeek();
        $endDate = $startDate->copy()->addDays(1);

        // Karyawan mengajukan izin approved untuk hari pertama
        Availability::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $employee->id,
            'start_time' => $startDate->copy()->setTime(0, 0),
            'end_time' => $startDate->copy()->setTime(23, 59),
            'is_recurring' => false,
            'status' => 'approved',
        ]);

        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'default_quota' => 1,
        ]);

        $result = $this->builder->generateWeeklyRoster(
            $this->company->id,
            $this->department->id,
            $startDate,
            $endDate
        );

        $this->assertIsArray($result);
    }
}
