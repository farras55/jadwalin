<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\ShiftTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian Keamanan Isolasi Multi-Tenant (Pencegahan Kebocoran Data Antar-Perusahaan)
 */
class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;

    protected Company $companyB;

    protected User $superadminA;

    protected User $superadminB;

    protected User $managerA;

    protected User $managerB;

    protected User $employeeA;

    protected User $employeeB;

    protected Department $departmentA;

    protected Department $departmentB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant Perusahaan A
        $this->companyA = Company::factory()->create(['name' => 'PT Senja Wisata Nusantara']);
        $this->departmentA = Department::factory()->create(['company_id' => $this->companyA->id, 'name' => 'Operasional A']);
        $this->superadminA = User::factory()->create(['company_id' => $this->companyA->id, 'role' => 'superadmin']);
        $this->managerA = User::factory()->create(['company_id' => $this->companyA->id, 'department_id' => $this->departmentA->id, 'role' => 'manager']);
        $this->employeeA = User::factory()->create(['company_id' => $this->companyA->id, 'department_id' => $this->departmentA->id, 'role' => 'employee']);

        // 2. Setup Tenant Perusahaan B (Kompetitor / Tenant Lain)
        $this->companyB = Company::factory()->create(['name' => 'PT Cakrawala Mega']);
        $this->departmentB = Department::factory()->create(['company_id' => $this->companyB->id, 'name' => 'Operasional B']);
        $this->superadminB = User::factory()->create(['company_id' => $this->companyB->id, 'role' => 'superadmin']);
        $this->managerB = User::factory()->create(['company_id' => $this->companyB->id, 'department_id' => $this->departmentB->id, 'role' => 'manager']);
        $this->employeeB = User::factory()->create(['company_id' => $this->companyB->id, 'department_id' => $this->departmentB->id, 'role' => 'employee']);
    }

    /**
     * TC-MT-01: Superadmin Perusahaan A dilarang mengakses atau mengedit departemen Perusahaan B.
     */
    public function test_superadmin_cannot_access_or_edit_departments_from_another_company(): void
    {
        $response = $this->actingAs($this->superadminA)
            ->get(route('admin.departments.edit', $this->departmentB));

        // Harus ditolak dengan 404 Not Found atau 403 Forbidden
        $this->assertTrue(in_array($response->status(), [403, 404]), "Status was {$response->status()}");
    }

    /**
     * TC-MT-02: Superadmin Perusahaan A dilarang menghapus template shift milik Perusahaan B.
     */
    public function test_superadmin_cannot_delete_shift_template_from_another_company(): void
    {
        $templateB = ShiftTemplate::factory()->create([
            'company_id' => $this->companyB->id,
            'department_id' => $this->departmentB->id,
        ]);

        $response = $this->actingAs($this->superadminA)
            ->delete(route('admin.shifts.destroy', $templateB));

        $this->assertTrue(in_array($response->status(), [403, 404]), "Status was {$response->status()}");
        $this->assertDatabaseHas('shift_templates', ['id' => $templateB->id]);
    }

    /**
     * TC-MT-03: Manager Perusahaan A dilarang melihat profil detail staf Perusahaan B.
     */
    public function test_manager_cannot_view_employee_details_from_another_company(): void
    {
        $response = $this->actingAs($this->managerA)
            ->get(route('manager.employees.show', $this->employeeB));

        $this->assertTrue(in_array($response->status(), [403, 404]), "Status was {$response->status()}");
    }

    /**
     * TC-MT-04: Manager Perusahaan A dilarang menyetujui pertukaran shift milik Perusahaan B.
     */
    public function test_manager_cannot_approve_shift_swap_from_another_company(): void
    {
        $shiftB = Shift::factory()->create([
            'company_id' => $this->companyB->id,
            'department_id' => $this->departmentB->id,
        ]);

        $assignmentB = ShiftAssignment::factory()->create([
            'company_id' => $this->companyB->id,
            'shift_id' => $shiftB->id,
            'user_id' => $this->employeeB->id,
        ]);

        $swapB = ShiftSwap::factory()->create([
            'company_id' => $this->companyB->id,
            'shift_assignment_id' => $assignmentB->id,
            'requester_id' => $this->employeeB->id,
            'target_user_id' => $this->employeeB->id,
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->managerA)
            ->post(route('manager.swaps.approve', $swapB));

        $this->assertTrue(in_array($response->status(), [403, 404]), "Status was {$response->status()}");
        $swapB->refresh();
        $this->assertEquals('pending_manager', $swapB->status);
    }

    /**
     * TC-MT-05: Karyawan Perusahaan A dilarang melihat detail jadwal staf Perusahaan B.
     */
    public function test_employee_cannot_view_schedule_details_from_another_company(): void
    {
        $shiftB = Shift::factory()->create([
            'company_id' => $this->companyB->id,
            'department_id' => $this->departmentB->id,
        ]);

        $assignmentB = ShiftAssignment::factory()->create([
            'company_id' => $this->companyB->id,
            'shift_id' => $shiftB->id,
            'user_id' => $this->employeeB->id,
        ]);

        $response = $this->actingAs($this->employeeA)
            ->get(route('employee.schedule.show', $assignmentB));

        $this->assertTrue(in_array($response->status(), [403, 404]), "Status was {$response->status()}");
    }
}
