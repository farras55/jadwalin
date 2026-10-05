<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Notification;
use App\Models\OpenShiftClaim;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\ShiftTemplate;
use App\Models\TimesheetReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    /**
     * Test all 14 models can be instantiated.
     */
    public function test_all_14_models_can_be_instantiated(): void
    {
        $models = [
            new Company(),
            new CompanySetting(),
            new Department(),
            new Position(),
            new User(),
            new Availability(),
            new ShiftTemplate(),
            new Shift(),
            new ShiftAssignment(),
            new ShiftSwap(),
            new OpenShiftClaim(),
            new TimesheetReport(),
            new Notification(),
            new AuditLog(),
        ];

        $this->assertCount(14, $models);
        foreach ($models as $model) {
            $this->assertNotNull($model);
        }
    }

    /**
     * Test User employment type and role helpers.
     */
    public function test_user_employment_type_and_role_helpers(): void
    {
        $user = new User([
            'role' => 'employee',
            'employment_type' => 'full_time',
        ]);

        $this->assertTrue($user->isEmployee());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isSuperadmin());

        $this->assertTrue($user->isFullTime());
        $this->assertFalse($user->isPartTime());
        $this->assertFalse($user->isFreelance());

        $user->employment_type = 'part_time';
        $this->assertTrue($user->isPartTime());

        $user->employment_type = 'freelance';
        $this->assertTrue($user->isFreelance());

        $user->role = 'manager';
        $this->assertTrue($user->isManager());

        $user->role = 'superadmin';
        $this->assertTrue($user->isSuperadmin());
    }

    /**
     * Test ShiftSwap dual-mode helpers.
     */
    public function test_shift_swap_dual_mode_helpers(): void
    {
        $swap = new ShiftSwap(['swap_type' => 'transfer']);
        $this->assertTrue($swap->isTransfer());
        $this->assertFalse($swap->isExchange());

        $swap->swap_type = 'exchange';
        $this->assertFalse($swap->isTransfer());
        $this->assertTrue($swap->isExchange());
    }

    /**
     * Test Shift model supports gap adjustment flags.
     */
    public function test_shift_supports_gap_flags(): void
    {
        $shift = new Shift([
            'requires_approval' => true,
            'is_holiday' => false,
        ]);

        $this->assertContains('requires_approval', $shift->getFillable());
        $this->assertContains('is_holiday', $shift->getFillable());
        $this->assertEquals('boolean', $shift->getCasts()['requires_approval']);
        $this->assertEquals('boolean', $shift->getCasts()['is_holiday']);
    }

    /**
     * Test Company relationships.
     */
    public function test_company_relationships(): void
    {
        $company = new Company();

        $this->assertInstanceOf(HasOne::class, $company->companySetting());
        $this->assertInstanceOf(HasMany::class, $company->departments());
        $this->assertInstanceOf(HasMany::class, $company->positions());
        $this->assertInstanceOf(HasMany::class, $company->users());
        $this->assertInstanceOf(HasMany::class, $company->shiftTemplates());
        $this->assertInstanceOf(HasMany::class, $company->shifts());
        $this->assertInstanceOf(HasMany::class, $company->shiftAssignments());
        $this->assertInstanceOf(HasMany::class, $company->availabilities());
        $this->assertInstanceOf(HasMany::class, $company->shiftSwaps());
        $this->assertInstanceOf(HasMany::class, $company->openShiftClaims());
        $this->assertInstanceOf(HasMany::class, $company->timesheetReports());
        $this->assertInstanceOf(HasMany::class, $company->notifications());
        $this->assertInstanceOf(HasMany::class, $company->auditLogs());
    }

    /**
     * Test CompanySetting relationships.
     */
    public function test_company_setting_relationships(): void
    {
        $setting = new CompanySetting();

        $this->assertInstanceOf(BelongsTo::class, $setting->company());
    }

    /**
     * Test Department relationships.
     */
    public function test_department_relationships(): void
    {
        $department = new Department();

        $this->assertInstanceOf(BelongsTo::class, $department->company());
        $this->assertInstanceOf(HasMany::class, $department->positions());
        $this->assertInstanceOf(HasMany::class, $department->users());
        $this->assertInstanceOf(HasMany::class, $department->shiftTemplates());
        $this->assertInstanceOf(HasMany::class, $department->shifts());
    }

    /**
     * Test Position relationships.
     */
    public function test_position_relationships(): void
    {
        $position = new Position();

        $this->assertInstanceOf(BelongsTo::class, $position->company());
        $this->assertInstanceOf(BelongsTo::class, $position->department());
        $this->assertInstanceOf(HasMany::class, $position->users());
    }

    /**
     * Test User relationships.
     */
    public function test_user_relationships(): void
    {
        $user = new User();

        $this->assertInstanceOf(BelongsTo::class, $user->company());
        $this->assertInstanceOf(BelongsTo::class, $user->department());
        $this->assertInstanceOf(BelongsTo::class, $user->position());
        $this->assertInstanceOf(HasMany::class, $user->availabilities());
        $this->assertInstanceOf(HasMany::class, $user->shiftAssignments());
        $this->assertInstanceOf(HasMany::class, $user->initiatedSwaps());
        $this->assertInstanceOf(HasMany::class, $user->receivedSwaps());
        $this->assertInstanceOf(HasMany::class, $user->openShiftClaims());
        $this->assertInstanceOf(HasMany::class, $user->timesheetReports());
        $this->assertInstanceOf(HasMany::class, $user->notifications());
        $this->assertInstanceOf(HasMany::class, $user->auditLogs());
    }

    /**
     * Test Availability relationships.
     */
    public function test_availability_relationships(): void
    {
        $availability = new Availability();

        $this->assertInstanceOf(BelongsTo::class, $availability->company());
        $this->assertInstanceOf(BelongsTo::class, $availability->user());
        $this->assertInstanceOf(BelongsTo::class, $availability->approvedBy());
    }

    /**
     * Test ShiftTemplate relationships.
     */
    public function test_shift_template_relationships(): void
    {
        $template = new ShiftTemplate();

        $this->assertInstanceOf(BelongsTo::class, $template->company());
        $this->assertInstanceOf(BelongsTo::class, $template->department());
        $this->assertInstanceOf(HasMany::class, $template->shifts());
    }

    /**
     * Test Shift relationships.
     */
    public function test_shift_relationships(): void
    {
        $shift = new Shift();

        $this->assertInstanceOf(BelongsTo::class, $shift->company());
        $this->assertInstanceOf(BelongsTo::class, $shift->department());
        $this->assertInstanceOf(BelongsTo::class, $shift->template());
        $this->assertInstanceOf(HasMany::class, $shift->shiftAssignments());
        $this->assertInstanceOf(HasMany::class, $shift->openShiftClaims());
    }

    /**
     * Test ShiftAssignment relationships.
     */
    public function test_shift_assignment_relationships(): void
    {
        $assignment = new ShiftAssignment();

        $this->assertInstanceOf(BelongsTo::class, $assignment->company());
        $this->assertInstanceOf(BelongsTo::class, $assignment->shift());
        $this->assertInstanceOf(BelongsTo::class, $assignment->user());
        $this->assertInstanceOf(HasMany::class, $assignment->shiftSwaps());
    }

    /**
     * Test ShiftSwap dual-assignment and multi-user relationships.
     */
    public function test_shift_swap_relationships(): void
    {
        $swap = new ShiftSwap();

        $this->assertInstanceOf(BelongsTo::class, $swap->company());
        $this->assertInstanceOf(BelongsTo::class, $swap->shiftAssignment());
        $this->assertInstanceOf(BelongsTo::class, $swap->targetShiftAssignment());
        $this->assertInstanceOf(BelongsTo::class, $swap->requester());
        $this->assertInstanceOf(BelongsTo::class, $swap->targetUser());
        $this->assertInstanceOf(BelongsTo::class, $swap->approvedBy());
    }

    /**
     * Test OpenShiftClaim relationships.
     */
    public function test_open_shift_claim_relationships(): void
    {
        $claim = new OpenShiftClaim();

        $this->assertInstanceOf(BelongsTo::class, $claim->company());
        $this->assertInstanceOf(BelongsTo::class, $claim->shift());
        $this->assertInstanceOf(BelongsTo::class, $claim->user());
        $this->assertInstanceOf(BelongsTo::class, $claim->approvedBy());
    }

    /**
     * Test TimesheetReport relationships.
     */
    public function test_timesheet_report_relationships(): void
    {
        $report = new TimesheetReport();

        $this->assertInstanceOf(BelongsTo::class, $report->company());
        $this->assertInstanceOf(BelongsTo::class, $report->user());
        $this->assertInstanceOf(BelongsTo::class, $report->generatedBy());
    }

    /**
     * Test Notification relationships.
     */
    public function test_notification_relationships(): void
    {
        $notification = new Notification();

        $this->assertInstanceOf(BelongsTo::class, $notification->company());
        $this->assertInstanceOf(BelongsTo::class, $notification->user());
    }

    /**
     * Test AuditLog relationships.
     */
    public function test_audit_log_relationships(): void
    {
        $log = new AuditLog();

        $this->assertInstanceOf(BelongsTo::class, $log->company());
        $this->assertInstanceOf(BelongsTo::class, $log->user());
    }
}
