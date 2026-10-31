<?php

namespace Tests\Feature\Concurrency;

use App\Models\Company;
use App\Models\Department;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenShiftClaimConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $manager;

    protected User $employee1;

    protected User $employee2;

    protected ShiftTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'PT Cakrawala Mega',
            'email' => 'contact@cakrawalamega.co.id',
            'phone_number' => '081234567890',
            'address' => 'Jl. Sudirman Kav 20, Jakarta',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $this->department = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Operasional',
            'description' => 'Divisi Operasional & Lapangan',
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'manager',
            'is_global_manager' => false,
            'status' => 'active',
        ]);

        $this->employee1 = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'name' => 'Budi Santoso',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $this->employee2 = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'name' => 'Siti Rahma',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $this->template = ShiftTemplate::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi Reguler',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'default_quota' => 2,
            'color_code' => '#6C5CE7',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 11, 10, 7, 0, 0));
    }

    /**
     * Skenario Uji 1:
     * Karyawan dapat mengklaim open shift yang tersedia dengan sukses.
     */
    public function test_employee_can_claim_available_open_shift_successfully(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Tambahan Weekend',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 2,
            'is_open_shift' => true,
            'requires_approval' => false,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->employee1)
            ->post(route('employee.open-shifts.claim', $shift));

        $response->assertRedirect(route('employee.open-shifts'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('open_shift_claims', [
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Skenario Uji 2:
     * Open shift Instant FCFS (requires_approval = false) langsung membuat penugasan shift terkonfirmasi.
     */
    public function test_open_shift_instant_fcfs_creates_assignment_immediately(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Lembur Darurat FCFS',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 1,
            'is_open_shift' => true,
            'requires_approval' => false,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->employee1)
            ->post(route('employee.open-shifts.claim', $shift));

        $response->assertRedirect(route('employee.open-shifts'));

        // Verifikasi ShiftAssignment langsung tercipta (instant confirm)
        $this->assertDatabaseHas('shift_assignments', [
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'scheduled',
        ]);

        // Verifikasi klaim berstatus approved
        $this->assertDatabaseHas('open_shift_claims', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'approved',
        ]);

        // Verifikasi notifikasi terkirim ke karyawan
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee1->id,
            'type' => 'open_shift_claimed',
        ]);

        // Kuota 1 telah terpenuhi, shift terbuka harus otomatis ditutup
        $this->assertFalse($shift->fresh()->is_open_shift);
    }

    /**
     * Skenario Uji 3:
     * Open shift yang memerlukan approval manajer (requires_approval = true) membuat klaim pending_manager.
     */
    public function test_open_shift_requires_approval_creates_pending_claim(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Butuh Kualifikasi Khusus',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 2,
            'is_open_shift' => true,
            'requires_approval' => true,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->employee1)
            ->post(route('employee.open-shifts.claim', $shift));

        $response->assertRedirect(route('employee.open-shifts'));
        $response->assertSessionHas('success');

        // Belum membuat ShiftAssignment
        $this->assertDatabaseMissing('shift_assignments', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
        ]);

        // Tersimpan di antrean klaim dengan status pending_manager
        $this->assertDatabaseHas('open_shift_claims', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending_manager',
        ]);

        // Verifikasi notifikasi terkirim ke manajer
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->manager->id,
            'type' => 'open_shift_pending_approval',
        ]);
    }

    /**
     * Skenario Uji 4:
     * Pencegahan klaim ganda oleh karyawan yang sama (Anti-Spam / Duplicate Claim).
     */
    public function test_prevent_duplicate_claims_by_same_employee(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Anti-Duplicate Test',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 3,
            'is_open_shift' => true,
            'requires_approval' => true,
            'status' => 'published',
        ]);

        // Klaim pertama berhasil
        $firstResponse = $this->actingAs($this->employee1)
            ->post(route('employee.open-shifts.claim', $shift));
        $firstResponse->assertRedirect();
        $this->assertEquals(1, OpenShiftClaim::where('shift_id', $shift->id)->where('user_id', $this->employee1->id)->count());

        // Klaim kedua oleh karyawan yang sama harus ditolak
        $secondResponse = $this->actingAs($this->employee1)
            ->post(route('employee.open-shifts.claim', $shift));

        $secondResponse->assertSessionHas('error');
        $this->assertEquals(1, OpenShiftClaim::where('shift_id', $shift->id)->where('user_id', $this->employee1->id)->count());
    }

    /**
     * Skenario Uji 5 (Paling Krusial):
     * Persaingan klaim simultan (Race Condition) di mana kuota 1 slot berhasil diamankan
     * oleh request pertama, dan request kedua ditolak dengan HTTP 409 Conflict tanpa over-allocation.
     */
    public function test_simultaneous_claims_prevent_race_condition_under_pessimistic_lock(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Rebutan Kuota 1 Slot',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 1, // HANYA 1 SLOT
            'is_open_shift' => true,
            'requires_approval' => false, // FCFS Instant
            'status' => 'published',
        ]);

        // Pemenang Pertama (Employee 1) mengklaim slot
        $winnerResponse = $this->actingAs($this->employee1)
            ->postJson(route('employee.open-shifts.claim', $shift));

        $winnerResponse->assertStatus(200);

        // Peserta Kedua (Employee 2) mencoba mengklaim saat kuota sudah habis
        $loserResponse = $this->actingAs($this->employee2)
            ->postJson(route('employee.open-shifts.claim', $shift));

        // Harus ditolak dengan status HTTP 409 Conflict
        $loserResponse->assertStatus(409);

        // Verifikasi tidak terjadi over-allocation: Tepat 1 ShiftAssignment di database
        $this->assertEquals(1, ShiftAssignment::where('shift_id', $shift->id)->count());
        $this->assertEquals(1, OpenShiftClaim::where('shift_id', $shift->id)->count());
        $this->assertEquals($this->employee1->id, ShiftAssignment::where('shift_id', $shift->id)->first()->user_id);
    }

    /**
     * Skenario Uji 6:
     * Manajer dapat mempublikasikan (broadcast) open shift baru dengan kuota dan mode alokasi.
     */
    public function test_manager_can_broadcast_open_shift(): void
    {
        $response = $this->actingAs($this->manager)->post(route('manager.open-shifts.broadcast'), [
            'title' => 'Shift Tambahan Proyek Cepat',
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'start_time' => '2026-11-10T08:00',
            'end_time' => '2026-11-10T16:00',
            'quota' => 3,
            'requires_approval' => 1,
            'is_holiday' => 0,
        ]);

        $response->assertRedirect(route('manager.open-shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', [
            'title' => 'Shift Tambahan Proyek Cepat',
            'department_id' => $this->department->id,
            'quota' => 3,
            'is_open_shift' => true,
            'requires_approval' => true,
        ]);

        // Notifikasi broadcast terkirim ke staf departemen
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee1->id,
            'type' => 'open_shift_broadcast',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee2->id,
            'type' => 'open_shift_broadcast',
        ]);
    }

    /**
     * Skenario Uji 7:
     * Manajer dapat menyetujui klaim open shift yang pending dan menerbitkan penugasan jadwal.
     */
    public function test_manager_can_approve_pending_claim(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Butuh Review Manajer',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 1,
            'is_open_shift' => true,
            'requires_approval' => true,
            'status' => 'published',
        ]);

        $claim = OpenShiftClaim::create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending_manager',
            'applied_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.claims.approve', $claim));

        $response->assertRedirect(route('manager.open-shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('open_shift_claims', [
            'id' => $claim->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id,
        ]);

        $this->assertDatabaseHas('shift_assignments', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'scheduled',
        ]);

        // Notifikasi approval terkirim ke employee
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee1->id,
            'type' => 'open_shift_approved',
        ]);
    }

    /**
     * Skenario Uji 8:
     * Manajer dapat menolak klaim open shift yang pending.
     */
    public function test_manager_can_reject_pending_claim(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Penolakan Klaim',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 1,
            'is_open_shift' => true,
            'requires_approval' => true,
            'status' => 'published',
        ]);

        $claim = OpenShiftClaim::create([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending_manager',
            'applied_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.claims.reject', $claim));

        $response->assertRedirect(route('manager.open-shifts.index'));

        $this->assertDatabaseHas('open_shift_claims', [
            'id' => $claim->id,
            'status' => 'rejected',
            'approved_by' => $this->manager->id,
        ]);

        $this->assertDatabaseMissing('shift_assignments', [
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
        ]);
    }

    /**
     * Skenario Uji 9:
     * Manajer dapat menutup broadcast open shift secara manual.
     */
    public function test_manager_can_close_open_shift_broadcast(): void
    {
        $shift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Ditutup Manual',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 3,
            'is_open_shift' => true,
            'requires_approval' => false,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('manager.open-shifts.close', $shift));

        $response->assertRedirect();
        $this->assertFalse($shift->fresh()->is_open_shift);
    }

    /**
     * Skenario Uji 10:
     * Manajer dapat membuka open shift dari shift terjadwal yang sudah ada yang kekurangan staf.
     */
    public function test_manager_can_broadcast_existing_understaffed_shift(): void
    {
        // Buat shift terjadwal yang belum open shift, kuota 2, baru terisi 1
        $existingShift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Operasional Terjadwal',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 2,
            'is_open_shift' => false,
            'status' => 'published',
        ]);

        // Staf 1 sudah terjadwal
        ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $existingShift->id,
            'user_id' => $this->employee1->id,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->manager)->post(route('manager.open-shifts.broadcast'), [
            'broadcast_source' => 'existing',
            'existing_shift_id' => $existingShift->id,
            'requires_approval' => 0,
        ]);

        $response->assertRedirect(route('manager.open-shifts.index'));
        $response->assertSessionHas('success');

        $this->assertTrue($existingShift->fresh()->is_open_shift);
        $this->assertFalse($existingShift->fresh()->requires_approval);

        // Notifikasi broadcast terkirim ke karyawan
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee2->id,
            'type' => 'open_shift_broadcast',
        ]);
    }

    /**
     * Skenario Uji 10B:
     * Ketika manajer mengisi butuh 2 slot terbuka pada shift yang asalnya kurang 1 slot,
     * kuota total shift otomatis disesuaikan menjadi staf terisi + slot terbuka (1 + 2 = 3),
     * sehingga sisa slot yang terbuka dan dapat diklaim benar-benar menjadi 2 slot.
     */
    public function test_broadcasting_existing_shift_with_custom_open_slots_adjusts_quota_correctly(): void
    {
        // Shift awal kuota 2, terisi 1 staf (kurang 1)
        $existingShift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Kurang Staf Butuh 2 Slot',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 2,
            'is_open_shift' => false,
            'status' => 'published',
        ]);

        ShiftAssignment::create([
            'company_id' => $this->company->id,
            'shift_id' => $existingShift->id,
            'user_id' => $this->employee1->id,
            'status' => 'scheduled',
        ]);

        // Manajer menentukan butuh 2 slot terbuka untuk open broadcast
        $response = $this->actingAs($this->manager)->post(route('manager.open-shifts.broadcast'), [
            'broadcast_source' => 'existing',
            'existing_shift_id' => $existingShift->id,
            'open_slots' => 2,
            'requires_approval' => 0,
        ]);

        $response->assertRedirect(route('manager.open-shifts.index'));
        $response->assertSessionHas('success');

        $fresh = $existingShift->fresh();
        // Kuota total otomatis disesuaikan menjadi: 1 (staf terisi) + 2 (slot terbuka) = 3
        $this->assertEquals(3, $fresh->quota);
        $this->assertTrue($fresh->is_open_shift);

        // Sisa slot yang dapat diklaim benar-benar 2
        $remaining = $fresh->quota - $fresh->shiftAssignments()->count();
        $this->assertEquals(2, $remaining);
    }

    /**
     * Skenario Uji 11:
     * Manajer Global dapat membuat Open Shift Global (Lintas Departemen),
     * dan karyawan dari departemen berbeda dapat melihat serta mengklaim shift tersebut.
     */
    public function test_global_manager_can_broadcast_global_shift_and_cross_department_employee_can_claim(): void
    {
        $globalManager = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'manager',
            'is_global_manager' => true,
            'status' => 'active',
        ]);

        $otherDepartment = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Customer Service',
            'description' => 'Divisi Layanan Pelanggan',
        ]);

        $csEmployee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $otherDepartment->id,
            'name' => 'Dewi Lestari',
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Manajer Global mempublikasikan shift secara global
        $response = $this->actingAs($globalManager)->post(route('manager.open-shifts.broadcast'), [
            'broadcast_source' => 'new',
            'title' => 'Shift Global Bantuan Operasional',
            'department_id' => 'global',
            'is_global_broadcast' => 1,
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0)->format('Y-m-d\TH:i'),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0)->format('Y-m-d\TH:i'),
            'quota' => 2,
            'requires_approval' => 0,
        ]);

        $response->assertRedirect(route('manager.open-shifts.index'));
        $response->assertSessionHas('success');

        $globalShift = Shift::where('title', 'Shift Global Bantuan Operasional')->first();
        $this->assertNotNull($globalShift);
        $this->assertTrue($globalShift->is_global_broadcast);

        // Karyawan dari CS (departemen berbeda) dapat melihat shift di availableShifts
        $viewResponse = $this->actingAs($csEmployee)->get(route('employee.open-shifts'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Shift Global Bantuan Operasional');

        // Karyawan CS mengklaim shift global
        $claimResponse = $this->actingAs($csEmployee)->post(route('employee.open-shifts.claim', $globalShift));
        $claimResponse->assertRedirect(route('employee.open-shifts'));
        $claimResponse->assertSessionHas('success');

        $this->assertDatabaseHas('shift_assignments', [
            'shift_id' => $globalShift->id,
            'user_id' => $csEmployee->id,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Skenario Uji 12:
     * Manajer Global dapat membuka shift kurang staf sebagai Global Broadcast,
     * sehingga karyawan departemen lain dapat mengisi slot kosong tersebut.
     */
    public function test_global_manager_can_broadcast_existing_shift_as_global(): void
    {
        $globalManager = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'manager',
            'is_global_manager' => true,
            'status' => 'active',
        ]);

        $otherDepartment = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Gudang',
        ]);

        $gudangEmployee = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $otherDepartment->id,
            'name' => 'Ahmad Gudang',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $existingShift = Shift::create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'template_id' => $this->template->id,
            'title' => 'Shift Butuh Bantuan Lintas Divisi',
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0),
            'quota' => 2,
            'is_open_shift' => false,
            'status' => 'published',
        ]);

        $response = $this->actingAs($globalManager)->post(route('manager.open-shifts.broadcast'), [
            'broadcast_source' => 'existing',
            'existing_shift_id' => $existingShift->id,
            'is_global_broadcast' => 1,
            'requires_approval' => 0,
        ]);

        $response->assertRedirect(route('manager.open-shifts.index'));
        $this->assertTrue($existingShift->fresh()->is_global_broadcast);

        // Karyawan Gudang mengklaim slot shift Operasional ini
        $claimResponse = $this->actingAs($gudangEmployee)->post(route('employee.open-shifts.claim', $existingShift));
        $claimResponse->assertRedirect(route('employee.open-shifts'));

        $this->assertDatabaseHas('shift_assignments', [
            'shift_id' => $existingShift->id,
            'user_id' => $gudangEmployee->id,
        ]);
    }

    /**
     * Skenario Uji 13:
     * Manajer departemen reguler tidak dapat mempublikasikan shift untuk departemen lain.
     */
    public function test_department_manager_cannot_broadcast_for_other_departments(): void
    {
        $otherDepartment = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Finance',
        ]);

        $response = $this->actingAs($this->manager)->post(route('manager.open-shifts.broadcast'), [
            'broadcast_source' => 'new',
            'title' => 'Shift Ilegal Departemen Lain',
            'department_id' => $otherDepartment->id,
            'start_time' => Carbon::create(2026, 11, 10, 8, 0, 0)->format('Y-m-d\TH:i'),
            'end_time' => Carbon::create(2026, 11, 10, 16, 0, 0)->format('Y-m-d\TH:i'),
            'quota' => 1,
            'requires_approval' => 0,
        ]);

        // Karena manager bukan global manager, sistem memaksakan department_id ke department_id milik manager
        $shift = Shift::where('title', 'Shift Ilegal Departemen Lain')->first();
        $this->assertNotNull($shift);
        $this->assertEquals($this->department->id, $shift->department_id);
        $this->assertFalse($shift->is_global_broadcast);
    }
}
