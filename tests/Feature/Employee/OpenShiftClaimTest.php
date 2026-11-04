<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenShiftClaimTest extends TestCase
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
            'status' => 'active',
        ]);
    }

    /**
     * TC-EOP-01: Karyawan dapat melihat daftar shift terbuka untuk departemennya.
     */
    public function test_employee_can_view_available_open_shifts(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'start_time' => Carbon::tomorrow()->setTime(8, 0),
            'end_time' => Carbon::tomorrow()->setTime(16, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('employee.open-shifts'));

        $response->assertOk();
    }

    /**
     * TC-EOP-02: Karyawan dapat mengklaim open shift yang tersedia.
     */
    public function test_employee_can_claim_available_open_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'requires_approval' => true,
            'quota' => 2,
            'start_time' => Carbon::tomorrow()->setTime(8, 0),
            'end_time' => Carbon::tomorrow()->setTime(16, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.open-shifts.claim', $shift), [
                'note' => 'Siap masuk kerja lembur tambahan.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('open_shift_claims', [
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => 'pending_manager',
        ]);
    }

    /**
     * TC-EOP-03 (Celah Bentrok Jadwal): Karyawan dilarang mengklaim open shift jika jadwalnya
     * bertabrakan dengan shift lain yang sudah ditugaskan pada jam yang sama (Double-booking conflict).
     */
    public function test_employee_cannot_claim_open_shift_if_schedule_conflicts(): void
    {
        $tomorrow = Carbon::tomorrow();

        // 1. Karyawan sudah memiliki shift penugasan jam 08:00 - 16:00
        $existingShift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'start_time' => $tomorrow->copy()->setTime(8, 0),
            'end_time' => $tomorrow->copy()->setTime(16, 0),
        ]);

        ShiftAssignment::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $existingShift->id,
            'user_id' => $this->employee->id,
            'status' => 'scheduled',
        ]);

        // 2. Open shift bertabrakan: jam 12:00 - 20:00 di hari yang sama
        $conflictingOpenShift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'requires_approval' => true,
            'quota' => 2,
            'start_time' => $tomorrow->copy()->setTime(12, 0),
            'end_time' => $tomorrow->copy()->setTime(20, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.open-shifts.claim', $conflictingOpenShift));

        // Ekspektasi: Sistem yang aman menolak klaim karena konflik jadwal (HTTP 422 atau redirect with error)
        $isBlocked = $response->isClientError() || session('error') !== null;
        $this->assertTrue($isBlocked, 'Klaim open shift yang bertabrakan dengan jadwal aktif harus ditolak.');

        $this->assertDatabaseMissing('open_shift_claims', [
            'shift_id' => $conflictingOpenShift->id,
            'user_id' => $this->employee->id,
        ]);
    }

    /**
     * TC-EOP-04: Karyawan dilarang mengklaim dua kali pada shift yang sama.
     */
    public function test_employee_cannot_claim_same_open_shift_twice(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_open_shift' => true,
            'requires_approval' => true,
            'quota' => 2,
            'start_time' => Carbon::tomorrow()->setTime(8, 0),
            'end_time' => Carbon::tomorrow()->setTime(16, 0),
        ]);

        OpenShiftClaim::factory()->create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.open-shifts.claim', $shift));

        // Menolak klaim kedua
        $this->assertTrue($response->status() >= 400 || session('error') !== null);
    }
}
