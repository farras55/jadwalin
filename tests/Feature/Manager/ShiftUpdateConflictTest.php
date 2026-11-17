<?php

namespace Tests\Feature\Manager;

use App\Models\Availability;
use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftUpdateConflictTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected User $employee;

    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->department = Department::factory()->create(['company_id' => $this->company->id]);

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

        $this->shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Sore',
            'start_time' => Carbon::tomorrow()->setTime(14, 0),
            'end_time' => Carbon::tomorrow()->setTime(22, 0),
            'quota' => 2,
        ]);
    }

    public function test_manager_cannot_update_shift_if_assigned_employee_has_overlapping_shift(): void
    {
        // Karyawan sudah memiliki shift lain di waktu yang bertabrakan (18:00 - 23:00)
        $conflictingShift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Malam Lain',
            'start_time' => Carbon::tomorrow()->setTime(18, 0),
            'end_time' => Carbon::tomorrow()->setTime(23, 0),
        ]);

        ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $conflictingShift->id,
            'user_id' => $this->employee->id,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->manager)
            ->put(route('manager.shifts.update', $this->shift), [
                'title' => 'Shift Sore Updated',
                'start_time' => '14:00',
                'end_time' => '22:00',
                'quota' => 2,
                'assigned_user_ids' => [$this->employee->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Pastikan penugasan tidak ditambahkan
        $this->assertDatabaseMissing('shift_assignments', [
            'shift_id' => $this->shift->id,
            'user_id' => $this->employee->id,
        ]);
    }

    public function test_manager_cannot_update_shift_if_assigned_employee_has_approved_availability_conflict(): void
    {
        // Karyawan memiliki pengajuan cuti/halangan yang telah disetujui (12:00 - 16:00)
        Availability::create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'start_time' => Carbon::tomorrow()->setTime(12, 0),
            'end_time' => Carbon::tomorrow()->setTime(16, 0),
            'reason' => 'Izin periksa ke dokter',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->manager)
            ->put(route('manager.shifts.update', $this->shift), [
                'title' => 'Shift Sore Updated',
                'start_time' => '14:00',
                'end_time' => '22:00',
                'quota' => 2,
                'assigned_user_ids' => [$this->employee->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('shift_assignments', [
            'shift_id' => $this->shift->id,
            'user_id' => $this->employee->id,
        ]);
    }

    public function test_manager_can_update_shift_when_no_conflicts_exist(): void
    {
        $response = $this->actingAs($this->manager)
            ->put(route('manager.shifts.update', $this->shift), [
                'title' => 'Shift Sore Aman',
                'start_time' => '14:00',
                'end_time' => '22:00',
                'quota' => 3,
                'assigned_user_ids' => [$this->employee->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'shift_id' => $this->shift->id,
            'user_id' => $this->employee->id,
        ]);

        $this->shift->refresh();
        $this->assertEquals('Shift Sore Aman', $this->shift->title);
    }
}
