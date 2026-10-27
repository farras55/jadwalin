<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSwapTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected User $employeeA;

    protected User $employeeB;

    protected Shift $shift1;

    protected Shift $shift2;

    protected ShiftAssignment $assignmentA;

    protected ShiftAssignment $assignmentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'PT Senja Wisata Nusantara',
            'email' => 'senja@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Sunset Road No. 88, Bali',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $this->department = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Food & Beverage',
            'description' => 'Divisi Kafe & Restoran',
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'manager',
            'is_global_manager' => false,
            'status' => 'active',
        ]);

        $this->employeeA = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'name' => 'Budi Santoso',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $this->employeeB = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'name' => 'Siti Rahma',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $templateMorning = ShiftTemplate::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'default_quota' => 2,
            'color_code' => '#6C5CE7',
        ]);

        $templateEvening = ShiftTemplate::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Malam',
            'start_time' => '16:00:00',
            'end_time' => '00:00:00',
            'default_quota' => 2,
            'color_code' => '#A29BFE',
        ]);

        $now = Carbon::create(2026, 11, 4, 8, 0, 0);
        Carbon::setTestNow($now);

        $this->shift1 = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $templateMorning->id,
            'title' => 'Shift Pagi - Rabu',
            'start_time' => Carbon::create(2026, 11, 4, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 4, 16, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        $this->shift2 = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $templateEvening->id,
            'title' => 'Shift Malam - Kamis',
            'start_time' => Carbon::create(2026, 11, 5, 16, 0, 0),
            'end_time' => Carbon::create(2026, 11, 6, 0, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        $this->assignmentA = ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $this->shift1->id,
            'user_id' => $this->employeeA->id,
            'status' => 'scheduled',
        ]);

        $this->assignmentB = ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $this->shift2->id,
            'user_id' => $this->employeeB->id,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Skenario Uji 1:
     * Karyawan dapat mengajukan penyerahan shift 1-arah (One-Way Transfer / Handover)
     */
    public function test_employee_can_request_one_way_shift_transfer(): void
    {
        $response = $this->actingAs($this->employeeA)->post(route('employee.swaps.request'), [
            'swap_type' => 'transfer',
            'shift_assignment_id' => $this->assignmentA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Ada acara keluarga mendesak di luar kota.',
        ]);

        $response->assertRedirect(route('employee.swaps'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_swaps', [
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'status' => 'pending_target',
            'reason' => 'Ada acara keluarga mendesak di luar kota.',
        ]);

        // Verifikasi notifikasi in-app terkirim ke karyawan target
        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeB->id,
            'type' => 'shift_swap_request',
        ]);
    }

    /**
     * Skenario Uji 2:
     * Karyawan dapat mengajukan pertukaran jadwal 2-arah (Two-Way Mutual Exchange / Barter)
     */
    public function test_employee_can_request_two_way_mutual_exchange(): void
    {
        $response = $this->actingAs($this->employeeA)->post(route('employee.swaps.request'), [
            'swap_type' => 'exchange',
            'shift_assignment_id' => $this->assignmentA->id,
            'target_user_id' => $this->employeeB->id,
            'target_shift_assignment_id' => $this->assignmentB->id,
            'reason' => 'Ingin menukar jadwal karena jadwal kuliah berubah ke pagi.',
        ]);

        $response->assertRedirect(route('employee.swaps'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_swaps', [
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => $this->assignmentB->id,
            'swap_type' => 'exchange',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'status' => 'pending_target',
            'reason' => 'Ingin menukar jadwal karena jadwal kuliah berubah ke pagi.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeB->id,
            'type' => 'shift_swap_request',
        ]);
    }

    /**
     * Skenario Uji 3:
     * Karyawan target dapat menyetujui swap dan status berubah menjadi pending_manager
     */
    public function test_target_employee_can_accept_and_transition_to_pending_manager(): void
    {
        $swap = ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => $this->assignmentB->id,
            'swap_type' => 'exchange',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Perlu ganti hari.',
            'status' => 'pending_target',
        ]);

        $response = $this->actingAs($this->employeeB)->post(route('employee.swaps.respond', $swap), [
            'action' => 'accept',
        ]);

        $response->assertRedirect(route('employee.swaps'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_swaps', [
            'id' => $swap->id,
            'status' => 'pending_manager',
        ]);

        // Notifikasi ke pemohon bahwa rekan telah setuju
        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeA->id,
            'type' => 'shift_swap_accepted',
        ]);

        // Notifikasi ke manajer bahwa ada permohonan menunggu persetujuan
        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->manager->id,
            'type' => 'shift_swap_pending_manager',
        ]);
    }

    /**
     * Skenario Uji 4:
     * Persetujuan Manajer secara atomik menukar atau memindahkan kepemilikan shift (Transfer & Exchange)
     */
    public function test_manager_approval_atomically_swaps_or_reassigns_shift_ownership(): void
    {
        // Bagian A: Pengujian Mode Exchange (Pertukaran 2-Arah)
        $exchangeSwap = ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => $this->assignmentB->id,
            'swap_type' => 'exchange',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Tukar shift 2 arah.',
            'status' => 'pending_manager',
        ]);

        $responseExchange = $this->actingAs($this->manager)
            ->post(route('manager.swaps.approve', $exchangeSwap));

        $responseExchange->assertRedirect(route('manager.swaps.index'));
        $responseExchange->assertSessionHas('success');

        // Status swap menjadi approved
        $this->assertDatabaseHas('shift_swaps', [
            'id' => $exchangeSwap->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id,
        ]);

        // Kepemilikan penugasan shift bertukar secara atomik
        $this->assertEquals($this->employeeB->id, $this->assignmentA->fresh()->user_id);
        $this->assertEquals($this->employeeA->id, $this->assignmentB->fresh()->user_id);

        // Audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->manager->id,
            'action' => 'approve_shift_swap',
        ]);

        // Notifikasi ke kedua belah pihak
        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeA->id,
            'type' => 'shift_swap_approved',
        ]);
        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeB->id,
            'type' => 'shift_swap_approved',
        ]);

        // Bagian B: Pengujian Mode Transfer (Penyerahan 1-Arah)
        $assignmentC = ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $this->shift1->id,
            'user_id' => $this->employeeA->id,
            'status' => 'scheduled',
        ]);

        $transferSwap = ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $assignmentC->id,
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Penyerahan shift 1 arah.',
            'status' => 'pending_manager',
        ]);

        $responseTransfer = $this->actingAs($this->manager)
            ->post(route('manager.swaps.approve', $transferSwap));

        $responseTransfer->assertRedirect(route('manager.swaps.index'));
        $this->assertEquals($this->employeeB->id, $assignmentC->fresh()->user_id);
    }

    /**
     * Skenario Uji 5:
     * Pencegahan pengajuan swap ganda/konkuren pada shift assignment yang sama (Anti-Spam / Race Condition)
     */
    public function test_prevent_concurrent_duplicate_swap_requests_on_same_assignment(): void
    {
        // Buat swap aktif pertama
        ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Pengajuan swap pertama yang sedang diproses.',
            'status' => 'pending_target',
        ]);

        // Coba ajukan swap kedua untuk shift assignment A yang sama
        $response = $this->actingAs($this->employeeA)->post(route('employee.swaps.request'), [
            'swap_type' => 'transfer',
            'shift_assignment_id' => $this->assignmentA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Pengajuan kedua yang harus ditolak.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(
            'Shift Anda sedang dalam proses pertukaran yang belum selesai.',
            session('error')
        );

        // Pastikan hanya ada 1 record di database
        $this->assertEquals(1, ShiftSwap::where('shift_assignment_id', $this->assignmentA->id)->count());
    }

    /**
     * Skenario Tambahan: Karyawan target menolak permohonan swap
     */
    public function test_target_employee_can_reject_swap_request(): void
    {
        $swap = ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Tolong ambil shift saya.',
            'status' => 'pending_target',
        ]);

        $response = $this->actingAs($this->employeeB)->post(route('employee.swaps.respond', $swap), [
            'action' => 'reject',
        ]);

        $response->assertRedirect(route('employee.swaps'));
        $this->assertDatabaseHas('shift_swaps', [
            'id' => $swap->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('notifications', [
            'company_id' => $this->company->id,
            'user_id' => $this->employeeA->id,
            'type' => 'shift_swap_rejected',
        ]);
    }

    /**
     * Skenario Tambahan: Manajer menolak permohonan swap dengan catatan
     */
    public function test_manager_can_reject_swap_request_with_notes(): void
    {
        $swap = ShiftSwap::create([
            'company_id' => $this->company->id,
            'shift_assignment_id' => $this->assignmentA->id,
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => $this->employeeA->id,
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Izin tidak masuk.',
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->manager)->post(route('manager.swaps.reject', $swap), [
            'rejection_reason' => 'Tidak memenuhi kuota staf minimal.',
        ]);

        $response->assertRedirect(route('manager.swaps.index'));

        $this->assertDatabaseHas('shift_swaps', [
            'id' => $swap->id,
            'status' => 'rejected',
            'approved_by' => $this->manager->id,
        ]);

        // Verifikasi kepemilikan shift TIDAK berubah
        $this->assertEquals($this->employeeA->id, $this->assignmentA->fresh()->user_id);
    }

    /**
     * Skenario Validasi: Karyawan tidak bisa menukar shift milik orang lain
     */
    public function test_employee_cannot_swap_shift_they_do_not_own(): void
    {
        $response = $this->actingAs($this->employeeA)->post(route('employee.swaps.request'), [
            'swap_type' => 'transfer',
            'shift_assignment_id' => $this->assignmentB->id, // Milik Employee B
            'target_user_id' => $this->employeeB->id,
            'reason' => 'Mencoba menukar shift bukan milik saya.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, ShiftSwap::count());
    }

    /**
     * Skenario Validasi: Karyawan tidak bisa menukar shift dengan dirinya sendiri
     */
    public function test_employee_cannot_swap_with_self(): void
    {
        $response = $this->actingAs($this->employeeA)->post(route('employee.swaps.request'), [
            'swap_type' => 'transfer',
            'shift_assignment_id' => $this->assignmentA->id,
            'target_user_id' => $this->employeeA->id, // Diri sendiri
            'reason' => 'Tukar dengan diri sendiri.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, ShiftSwap::count());
    }

    /**
     * Skenario Akses: Karyawan dan Manajer dapat membuka halaman tukar shift
     */
    public function test_employee_and_manager_can_access_swap_views(): void
    {
        $employeeRes = $this->actingAs($this->employeeA)->get(route('employee.swaps'));
        $employeeRes->assertOk();
        $employeeRes->assertSee('Permintaan Masuk');

        $managerRes = $this->actingAs($this->manager)->get(route('manager.swaps.index'));
        $managerRes->assertOk();
        $managerRes->assertSee('Persetujuan Tukar Shift');
    }
}
