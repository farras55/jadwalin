<?php

namespace Tests\Feature\Manager;

use App\Models\Company;
use App\Models\Department;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);
    }

    /**
     * TC-MOP-01: Manager dapat melihat halaman kelola bursa open shift.
     */
    public function test_manager_can_view_open_shifts_index(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('manager.open-shifts.index'));

        $response->assertOk();
    }

    /**
     * TC-MOP-02: Manager dapat mem-broadcast shift menjadi open shift.
     */
    public function test_manager_can_broadcast_shift_to_open_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => false,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0),
            'quota' => 2,
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.broadcast'), [
                'broadcast_source' => 'existing',
                'existing_shift_id' => $shift->id,
                'requires_approval' => 1,
                'open_slots' => 1,
                'note' => 'Dibutuhkan tambahan staf untuk event.',
            ]);

        $response->assertRedirect();
        $shift->refresh();
        $this->assertTrue((bool) $shift->is_open_shift);
    }

    /**
     * TC-MOP-03: Manager dapat menutup penyiaran open shift.
     */
    public function test_manager_can_close_open_shift_broadcast(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0),
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.close', $shift));

        $response->assertRedirect();
        $shift->refresh();
        $this->assertFalse((bool) $shift->is_open_shift);
    }

    /**
     * TC-MOP-04: Manager dapat menyetujui klaim open shift dan staf otomatis ditugaskan.
     */
    public function test_manager_can_approve_open_shift_claim(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'quota' => 2,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0),
        ]);

        $claim = OpenShiftClaim::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.claims.approve', $claim));

        $response->assertRedirect();

        $claim->refresh();
        $this->assertEquals('approved', $claim->status);

        $this->assertDatabaseHas('shift_assignments', [
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
        ]);
    }

    /**
     * TC-MOP-05: Manager dapat menolak klaim open shift disertai alasan.
     */
    public function test_manager_can_reject_open_shift_claim_with_reason(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0),
        ]);

        $claim = OpenShiftClaim::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.claims.reject', $claim), [
                'rejection_reason' => 'Kapasitas shift sudah terpenuhi oleh tim lain.',
            ]);

        $response->assertRedirect();

        $claim->refresh();
        $this->assertEquals('rejected', $claim->status);
        $this->assertEquals('Kapasitas shift sudah terpenuhi oleh tim lain.', $claim->rejection_reason);
    }

    /**
     * TC-MOP-06: Manager gagal menolak klaim jika alasan melebihi 255 karakter.
     */
    public function test_manager_cannot_reject_claim_with_reason_exceeding_max_length(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(17, 0),
        ]);

        $claim = OpenShiftClaim::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.claims.reject', $claim), [
                'rejection_reason' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors(['rejection_reason']);

        $claim->refresh();
        $this->assertEquals('pending_manager', $claim->status);
    }
}
