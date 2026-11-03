<?php

namespace Tests\Feature\Console;

use App\Models\AttendanceRegularization;
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

class AutoClockOutCommandTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $employee;

    protected User $manager;

    protected CompanySetting $companySetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'Jadwalin Corp',
            'email' => 'contact@jadwalin.test',
            'phone_number' => '081234567890',
            'address' => 'Jl. Gatot Subroto No. 12',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->department = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Divisi Operasional',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Gedung Operasional Lt 2',
        ]);

        $this->companySetting = CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'grace_period_minutes' => 15,
            'max_regular_hours' => 8,
            'auto_clockout_buffer_hours' => 2,
            'is_auto_clockout' => true,
        ]);

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'is_global_manager' => false,
        ]);
    }

    /**
     * 1. Test auto clockout command closes overdue shifts at shift end_time.
     */
    public function test_auto_clockout_command_closes_overdue_shifts_at_shift_end_time(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate->copy()->setTime(8, 0, 0));

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => null,
            'status' => 'present',
            'is_auto_clockout' => false,
        ]);

        // Waktu saat ini: jam 20:00 (lewat end_time 17:00 + buffer 2 jam = 19:00)
        Carbon::setTestNow($baseDate->copy()->setTime(20, 0, 0));

        $this->artisan('jadwalin:auto-clockout')
            ->expectsOutputToContain('Auto clock-out completed')
            ->assertSuccessful();

        $assignment->refresh();

        // Verifikasi clock_out_time tepat pada end_time (17:00), BUKAN 20:00 atau 19:00
        $this->assertEquals(
            $baseDate->copy()->setTime(17, 0, 0)->toDateTimeString(),
            $assignment->clock_out_time->toDateTimeString()
        );
        $this->assertTrue((bool) $assignment->is_auto_clockout);
        $this->assertEquals('completed', $assignment->status);

        // Durasi kerja 08:00 - 17:00 = 9 jam. Max regular = 8 jam -> 8.0 regular, 1.0 overtime.
        // Buffer 17:00 - 20:00 tidak dibayar (0 jam).
        $this->assertEquals(8.0, (float) $assignment->regular_hours);
        $this->assertEquals(1.0, (float) $assignment->overtime_hours);

        // Verifikasi tercatat di audit_logs
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'action' => 'auto_clockout',
            'model_type' => ShiftAssignment::class,
        ]);
    }

    /**
     * 2. Test auto clockout does not affect active shifts within buffer period.
     */
    public function test_auto_clockout_does_not_affect_active_shifts_within_buffer(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate->copy()->setTime(8, 0, 0));

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => null,
            'status' => 'present',
            'is_auto_clockout' => false,
        ]);

        // Waktu saat ini: jam 18:30 (masih dalam buffer 2 jam hingga 19:00)
        Carbon::setTestNow($baseDate->copy()->setTime(18, 30, 0));

        $this->artisan('jadwalin:auto-clockout')
            ->assertSuccessful();

        $assignment->refresh();

        // Assignment harus tetap aktif dan belum ditutup
        $this->assertNull($assignment->clock_out_time);
        $this->assertFalse((bool) $assignment->is_auto_clockout);
        $this->assertEquals('present', $assignment->status);
    }

    /**
     * 3. Test auto clockout handles overnight shifts correctly.
     */
    public function test_auto_clockout_handles_overnight_shifts_correctly(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');
        $this->companySetting->update(['auto_clockout_buffer_hours' => 1]);

        // Shift malam: 22:00 (hari ini) s.d. 06:00 (keesokan harinya)
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Malam',
            'start_time' => $baseDate->copy()->setTime(22, 0, 0),
            'end_time' => $baseDate->copy()->addDay()->setTime(6, 0, 0),
            'quota' => 3,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(22, 0, 0),
            'clock_out_time' => null,
            'status' => 'present',
            'is_auto_clockout' => false,
        ]);

        // Waktu saat ini: keesokan harinya pukul 07:30 (melewati 06:00 + 1 jam buffer = 07:00)
        Carbon::setTestNow($baseDate->copy()->addDay()->setTime(7, 30, 0));

        $this->artisan('jadwalin:auto-clockout')
            ->assertSuccessful();

        $assignment->refresh();

        // Clock out harus tepat pada 06:00 hari berikutnya
        $expectedOut = $baseDate->copy()->addDay()->setTime(6, 0, 0);
        $this->assertEquals($expectedOut->toDateTimeString(), $assignment->clock_out_time->toDateTimeString());
        $this->assertTrue((bool) $assignment->is_auto_clockout);
        $this->assertEquals('completed', $assignment->status);
    }

    /**
     * 4. Test auto clockout command is idempotent.
     */
    public function test_auto_clockout_is_idempotent(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => null,
            'status' => 'present',
            'is_auto_clockout' => false,
        ]);

        Carbon::setTestNow($baseDate->copy()->setTime(21, 0, 0));

        // Eksekusi pertama
        $this->artisan('jadwalin:auto-clockout')->assertSuccessful();
        $this->assertEquals(1, AuditLog::where('action', 'auto_clockout')->count());

        $assignment->refresh();
        $firstClockOut = $assignment->clock_out_time;

        // Eksekusi kedua (idempotency check)
        $this->artisan('jadwalin:auto-clockout')->assertSuccessful();

        // Jumlah audit log tidak bertambah
        $this->assertEquals(1, AuditLog::where('action', 'auto_clockout')->count());

        $assignment->refresh();
        $this->assertEquals($firstClockOut->toDateTimeString(), $assignment->clock_out_time->toDateTimeString());
    }

    /**
     * 5. Test employee can submit attendance regularization for auto clocked-out shift.
     */
    public function test_employee_can_submit_attendance_regularization_for_auto_clocked_out_shift(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        $requestedOut = $baseDate->copy()->setTime(19, 0, 0)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $assignment->id,
                'requested_clock_out_time' => $requestedOut,
                'reason' => 'Lembur menyelesaikan laporan closing shift bulanan sampai malam.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_regularizations', [
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employee->id,
            'company_id' => $this->company->id,
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'action' => 'regularization_submitted',
        ]);
    }

    /**
     * 6. Test employee cannot submit regularization for normal shift or duplicate pending.
     */
    public function test_employee_cannot_submit_regularization_for_normal_shift_or_duplicate(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        // Shift manual / normal clock out (is_auto_clockout = false)
        $normalAssignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'status' => 'completed',
            'is_auto_clockout' => false,
        ]);

        // Percobaan 1: Mengajukan untuk shift normal
        $response = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $normalAssignment->id,
                'requested_clock_out_time' => $baseDate->copy()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Mencoba mengajukan shift yang bukan auto clock out.',
            ]);

        $response->assertSessionHasErrors('shift_assignment_id');

        // Shift auto clock out
        $autoAssignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        // Pengajuan pertama berhasil
        AttendanceRegularization::forceCreate([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $autoAssignment->id,
            'employee_id' => $this->employee->id,
            'requested_clock_out_time' => $baseDate->copy()->setTime(19, 0, 0),
            'reason' => 'Pengajuan pertama yang masih berstatus pending.',
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        // Percobaan 2: Mengajukan duplikat saat pengajuan masih pending
        $dupResponse = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $autoAssignment->id,
                'requested_clock_out_time' => $baseDate->copy()->setTime(20, 0, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Mencoba mengajukan duplikat permohonan koreksi.',
            ]);

        $dupResponse->assertSessionHasErrors('shift_assignment_id');

        // Percobaan 3: Jam pulang yang diminta sebelum atau sama dengan end_time resmi
        $invalidTimeResponse = $this->actingAs($this->employee)
            ->post(route('employee.attendance.regularization.store'), [
                'shift_assignment_id' => $autoAssignment->id,
                'requested_clock_out_time' => $baseDate->copy()->setTime(16, 30, 0)->format('Y-m-d H:i:s'),
                'reason' => 'Jam pulang lebih awal dari shift end time.',
            ]);

        $invalidTimeResponse->assertSessionHasErrors();
    }

    /**
     * 7. Test manager can approve attendance regularization and update hours.
     */
    public function test_manager_can_approve_attendance_regularization_and_update_hours(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.0,
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        $regularization = AttendanceRegularization::forceCreate([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employee->id,
            'requested_clock_out_time' => $baseDate->copy()->setTime(19, 0, 0),
            'reason' => 'Lembur 2 jam tambahan mengerjakan inventory audit.',
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.attendance.regularizations.approve', $regularization), [
                'overtime_status' => 'approved',
                'review_note' => 'Lembur disetujui setelah diverifikasi dengan supervisor.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $regularization->refresh();
        $assignment->refresh();

        $this->assertEquals('approved', $regularization->status);
        $this->assertEquals($this->manager->id, $regularization->reviewed_by);
        $this->assertNotNull($regularization->reviewed_at);
        $this->assertEquals('approved', $regularization->overtime_status);

        // Jam pulang riil diperbarui ke 19:00
        $this->assertEquals(
            $baseDate->copy()->setTime(19, 0, 0)->toDateTimeString(),
            $assignment->clock_out_time->toDateTimeString()
        );

        // Total durasi 08:00 - 19:00 = 11 jam. Max reg = 8 -> 8.0 reg, 3.0 ovt.
        $this->assertEquals(8.0, (float) $assignment->regular_hours);
        $this->assertEquals(3.0, (float) $assignment->overtime_hours);
        $this->assertTrue((bool) $assignment->is_overtime);
        $this->assertEquals('approved', $assignment->overtime_status);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->manager->id,
            'action' => 'regularization_approved',
        ]);
    }

    /**
     * 8. Test manager can reject attendance regularization.
     */
    public function test_manager_can_reject_attendance_regularization(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.0,
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        $regularization = AttendanceRegularization::forceCreate([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employee->id,
            'requested_clock_out_time' => $baseDate->copy()->setTime(19, 0, 0),
            'reason' => 'Pengajuan lembur tanpa bukti valid.',
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.attendance.regularizations.reject', $regularization), [
                'review_note' => 'Tidak ada rekaman lembur atau bukti pekerjaan tambahan.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $regularization->refresh();
        $assignment->refresh();

        $this->assertEquals('rejected', $regularization->status);
        $this->assertEquals($this->manager->id, $regularization->reviewed_by);
        $this->assertNotNull($regularization->reviewed_at);

        // Jam pulang assignment tetap pada jam auto-clockout awal (17:00)
        $this->assertEquals(
            $baseDate->copy()->setTime(17, 0, 0)->toDateTimeString(),
            $assignment->clock_out_time->toDateTimeString()
        );
        $this->assertEquals(8.0, (float) $assignment->regular_hours);
        $this->assertEquals(1.0, (float) $assignment->overtime_hours);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->manager->id,
            'action' => 'regularization_rejected',
        ]);
    }

    /**
     * 9. Test manager cannot approve out-of-department or self regularization.
     */
    public function test_manager_cannot_approve_out_of_department_or_self_regularization(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 0, 0, 0, 'Asia/Jakarta');

        $otherDept = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Divisi Marketing',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Gedung Marketing Lt 1',
        ]);

        $otherManager = User::factory()->create([
            'role' => 'manager',
            'company_id' => $this->company->id,
            'department_id' => $otherDept->id,
            'is_global_manager' => false,
        ]);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        $assignment = ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'status' => 'completed',
            'is_auto_clockout' => true,
        ]);

        $regularization = AttendanceRegularization::forceCreate([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->employee->id,
            'requested_clock_out_time' => $baseDate->copy()->setTime(19, 0, 0),
            'reason' => 'Pengajuan reguler antar departemen.',
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        // Manajer departemen lain mencoba menyetujui -> 403 Forbidden
        $response = $this->actingAs($otherManager)
            ->post(route('manager.attendance.regularizations.approve', $regularization), [
                'overtime_status' => 'approved',
            ]);
        $response->assertStatus(403);

        // Manajer mencoba menyetujui pengajuannya sendiri jika dia yang mengajukan -> 403 Forbidden
        $selfRegularization = AttendanceRegularization::forceCreate([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignment->id,
            'employee_id' => $this->manager->id,
            'requested_clock_out_time' => $baseDate->copy()->setTime(19, 0, 0),
            'reason' => 'Pengajuan diri sendiri oleh manajer.',
            'status' => 'pending',
            'overtime_status' => 'pending_approval',
        ]);

        $selfResponse = $this->actingAs($this->manager)
            ->post(route('manager.attendance.regularizations.approve', $selfRegularization), [
                'overtime_status' => 'approved',
            ]);
        $selfResponse->assertStatus(403);
    }
}
