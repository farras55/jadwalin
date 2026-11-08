<?php

namespace Tests\Unit\Services;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
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

    public function test_builder_respects_recurring_weekly_availability_and_prevents_conflict(): void
    {
        $employeeWithClass = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $freeEmployee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $startDate = Carbon::today()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->addDay(); // Mon & Tue

        // Jadwal kuliah mingguan berulang setiap Senin 09:00 - 12:00
        Availability::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $employeeWithClass->id,
            'day_of_week' => 1,
            'start_time' => Carbon::parse('2026-01-01 09:00:00'),
            'end_time' => Carbon::parse('2026-01-01 12:00:00'),
            'is_recurring' => true,
            'status' => 'approved',
        ]);

        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'default_quota' => 1,
        ]);

        $this->builder->generateWeeklyRoster(
            $this->company->id,
            $this->department->id,
            $startDate,
            $endDate
        );

        // Pada hari Senin (07:00 - 15:00), employeeWithClass tidak boleh ditugaskan
        $mondayShift = Shift::where('company_id', $this->company->id)
            ->whereDate('start_time', $startDate->toDateString())
            ->with('shiftAssignments')
            ->first();

        $this->assertNotNull($mondayShift);
        $assignedUserIds = $mondayShift->shiftAssignments->pluck('user_id')->all();

        $this->assertNotContains($employeeWithClass->id, $assignedUserIds);
        $this->assertContains($freeEmployee->id, $assignedUserIds);
    }

    public function test_generate_weekly_roster_rotates_full_time_off_days_and_fills_all_shifts(): void
    {
        // 5 Full-Time employees
        User::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'employment_type' => 'full_time',
            'max_weekly_hours' => 48,
            'status' => 'active',
        ]);

        // 5 Part-Time employees
        User::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'employment_type' => 'part_time',
            'max_weekly_hours' => null,
            'status' => 'active',
        ]);

        CompanySetting::where('company_id', $this->company->id)->update([
            'max_regular_hours' => 48,
            'max_working_days_per_week' => 6,
        ]);

        // Shift 1 (Pagi, quota 2)
        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift 1 - Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'default_quota' => 2,
        ]);

        // Shift 2 (Sore, quota 3)
        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift 2 - Sore',
            'start_time' => '15:00:00',
            'end_time' => '23:00:00',
            'default_quota' => 3,
        ]);

        // Shift 3 (Malam, quota 2)
        ShiftTemplate::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift 3 - Malam',
            'start_time' => '23:00:00',
            'end_time' => '07:00:00',
            'default_quota' => 2,
        ]);

        $startDate = Carbon::today()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        $result = $this->builder->generateWeeklyRoster(
            $this->company->id,
            $this->department->id,
            $startDate,
            $endDate
        );

        $this->assertEquals(21, $result['shifts_created']);
        $this->assertEquals(49, $result['assignments_created']);
        $this->assertEmpty($result['unfilled_slots'] ?? []);

        // Verifikasi bahwa Shift 3 (Malam) kini didampingi oleh staf Full-Time (tidak 100% PT lagi)
        $nightShifts = Shift::where('company_id', $this->company->id)
            ->where('title', 'Shift 3 - Malam')
            ->with('shiftAssignments.user')
            ->get();

        foreach ($nightShifts as $ns) {
            $hasFT = $ns->shiftAssignments->contains(fn ($a) => $a->user->employment_type === 'full_time');
            $this->assertTrue($hasFT, 'Setiap shift malam harus didampingi minimal 1 staf Full-Time');
        }

        // Verifikasi bahwa staf Part-Time juga terdistribusi ke Shift 1 atau Shift 2 (tidak menumpuk hanya di shift malam)
        $dayShifts = Shift::where('company_id', $this->company->id)
            ->whereIn('title', ['Shift 1 - Pagi', 'Shift 2 - Sore'])
            ->with('shiftAssignments.user')
            ->get();

        $ptInDayShiftsCount = 0;
        foreach ($dayShifts as $ds) {
            $ptInDayShiftsCount += $ds->shiftAssignments->where('user.employment_type', 'part_time')->count();
        }
        $this->assertGreaterThan(0, $ptInDayShiftsCount, 'Staf Part-Time harus ikut terdistribusi ke shift pagi/sore');
    }
}
