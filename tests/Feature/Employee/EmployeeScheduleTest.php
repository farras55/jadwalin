<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBL-41: Employee Schedule Calendar & Roster
 */
class EmployeeScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Department $department;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'         => 'PT Jadwalin Test',
            'email'        => 'test@jadwalin.test',
            'phone_number' => '081234567890',
            'address'      => 'Jl. Bali No. 1, Denpasar',
            'timezone'     => 'Asia/Jakarta',
            'status'       => 'active',
        ]);

        $this->department = Department::create([
            'company_id' => $this->company->id,
            'name'       => 'Operasional',
        ]);

        $this->employee = User::factory()->create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'role'          => 'employee',
            'status'        => 'active',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Create a ShiftTemplate for the test company/department.
     */
    private function makeTemplate(string $color = '#6C5CE7'): ShiftTemplate
    {
        return ShiftTemplate::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'title'         => 'Shift Pagi',
            'start_time'    => '08:00:00',
            'end_time'      => '16:00:00',
            'default_quota' => 2,
            'color_code'    => $color,
        ]);
    }

    /**
     * Create a Shift with given start/end and status.
     */
    private function makeShift(
        Carbon $start,
        Carbon $end,
        string $status = 'published',
        ?int $templateId = null
    ): Shift {
        return Shift::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'template_id'   => $templateId,
            'title'         => 'Shift Test',
            'start_time'    => $start,
            'end_time'      => $end,
            'quota'         => 2,
            'is_open_shift' => false,
            'requires_approval' => false,
            'status'        => $status,
        ]);
    }

    /**
     * Assign a user to a shift.
     */
    private function makeAssignment(Shift $shift, User $user): ShiftAssignment
    {
        return ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id'   => $shift->id,
            'user_id'    => $user->id,
            'status'     => 'scheduled',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Test 1: Employee can view own published schedule calendar
    // ──────────────────────────────────────────────────────────────────────────

    public function test_employee_can_view_own_published_schedule_calendar(): void
    {
        // Create a published shift this week (Monday 08:00–16:00)
        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(8, 0);
        $shift  = $this->makeShift($monday, $monday->copy()->setTime(16, 0), 'published');
        $this->makeAssignment($shift, $this->employee);

        $response = $this->actingAs($this->employee)->get(route('employee.schedule'));

        $response->assertStatus(200);
        $response->assertViewIs('employee.schedule.index');

        // Should see the shift title in page
        $response->assertSee('Shift Test');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Test 2: Employee cannot see unpublished draft shifts
    // ──────────────────────────────────────────────────────────────────────────

    public function test_employee_cannot_see_unpublished_draft_shifts(): void
    {
        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(8, 0);

        // Draft shift with distinct title — should NOT appear
        $draftShift = Shift::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'title'         => 'Shift Draft Tersembunyi',
            'start_time'    => $monday->copy(),
            'end_time'      => $monday->copy()->setTime(16, 0),
            'quota'         => 1,
            'status'        => 'draft',
        ]);
        $this->makeAssignment($draftShift, $this->employee);

        // Published shift with a distinct title — should appear
        $tuesday = $monday->copy()->setTime(9, 0); // same day, different hour, to confirm it's the status not the day
        $publishedShift = Shift::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'title'         => 'Shift Published Aktif',
            'start_time'    => $monday->copy()->setTime(10, 0),
            'end_time'      => $monday->copy()->setTime(18, 0),
            'quota'         => 1,
            'status'        => 'published',
        ]);
        $this->makeAssignment($publishedShift, $this->employee);

        $response = $this->actingAs($this->employee)->get(route('employee.schedule'));

        $response->assertStatus(200);

        // Draft shift title must NOT appear, published one must appear
        $response->assertDontSee('Shift Draft Tersembunyi');
        $response->assertSee('Shift Published Aktif');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Test 3: Employee cannot access another employee's shift assignment detail
    // ──────────────────────────────────────────────────────────────────────────

    public function test_employee_cannot_access_other_employee_schedule_details(): void
    {
        // Another employee in the same company
        $otherEmployee = User::factory()->create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'role'          => 'employee',
            'status'        => 'active',
        ]);

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(8, 0);
        $shift  = $this->makeShift($monday, $monday->copy()->setTime(16, 0), 'published');

        // Assignment belongs to the OTHER employee
        $otherAssignment = $this->makeAssignment($shift, $otherEmployee);

        // Our employee tries to view that assignment
        $response = $this->actingAs($this->employee)
            ->get(route('employee.schedule.show', $otherAssignment));

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Test 4: Schedule view filters correctly by week range (week_offset)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_schedule_view_filters_correctly_by_week_range(): void
    {
        // Shift in the CURRENT week (Monday)
        $thisMonday = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(8, 0);
        $shiftThisWeek = Shift::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'title'         => 'Shift Minggu Ini',
            'start_time'    => $thisMonday,
            'end_time'      => $thisMonday->copy()->setTime(16, 0),
            'quota'         => 1,
            'status'        => 'published',
        ]);
        $this->makeAssignment($shiftThisWeek, $this->employee);

        // Shift in the NEXT week (Monday + 7 days)
        $nextMonday = $thisMonday->copy()->addWeek();
        $shiftNextWeek = Shift::create([
            'company_id'    => $this->company->id,
            'department_id' => $this->department->id,
            'title'         => 'Shift Minggu Depan',
            'start_time'    => $nextMonday,
            'end_time'      => $nextMonday->copy()->setTime(16, 0),
            'quota'         => 1,
            'status'        => 'published',
        ]);
        $this->makeAssignment($shiftNextWeek, $this->employee);

        // ── View current week (week_offset=0) ────────────────────────────────
        $responseCurrentWeek = $this->actingAs($this->employee)
            ->get(route('employee.schedule', ['week_offset' => 0]));

        $responseCurrentWeek->assertStatus(200);
        $responseCurrentWeek->assertSee('Shift Minggu Ini');
        $responseCurrentWeek->assertDontSee('Shift Minggu Depan');

        // ── View next week (week_offset=1) ───────────────────────────────────
        $responseNextWeek = $this->actingAs($this->employee)
            ->get(route('employee.schedule', ['week_offset' => 1]));

        $responseNextWeek->assertStatus(200);
        $responseNextWeek->assertDontSee('Shift Minggu Ini');
        $responseNextWeek->assertSee('Shift Minggu Depan');
    }
}
