<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $admin;

    protected User $manager;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'PT Nusantara Sejahtera',
            'email' => 'corp@nusantara.test',
            'phone_number' => '081234567890',
            'address' => 'Gedung Nusantara Lt 8',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->department = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Operasional Pusat',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Jl. Jenderal Sudirman No. 10',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'role' => 'superadmin',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Operasional',
            'role' => 'manager',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $this->employee = User::factory()->create([
            'name' => 'Staff Biasa',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);
    }

    /**
     * Skenario 1: Super Admin dapat melihat tabel dan data audit logs.
     */
    public function test_admin_can_view_audit_logs_table(): void
    {
        AuditLog::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'action' => 'update',
            'model_type' => Shift::class,
            'model_id' => 99,
            'old_values' => ['title' => 'Shift Pagi Awal'],
            'new_values' => ['title' => 'Shift Pagi Direvisi'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 AuditBrowser/1.0',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertViewIs('admin.audit-logs.index');
        $response->assertViewHas('logs');
        $response->assertViewHas('stats');
        $response->assertSee('Audit Trail Logging');
        $response->assertSee('Aktivitas Hari Ini');
        $response->assertSee('Perubahan Data');
        $response->assertSee('Data Dihapus');
        $response->assertSee('Aktivitas Sistem');
        $response->assertSee('Semua Jenis Aktivitas');
        $response->assertSee('Semua Fitur Aplikasi');
        $response->assertSee('Cari nama staf, fitur, atau lokasi IP...');
        $response->assertSee('UPDATE');
        $response->assertSee('Pengeditan Data');
        $response->assertSee('Jadwal Shift');
        $response->assertSee('127.0.0.1');
        $response->assertSee('Lihat Perubahan');
        $response->assertSee('Rincian Perubahan Data');
        $response->assertSee('DATA SEBELUM DIUBAH');
        $response->assertSee('DATA SESUDAH DIUBAH');
    }

    /**
     * Skenario Tambahan: AuditLog merender label ramah pengguna untuk aksi non-CRUD dan model terkait.
     */
    public function test_audit_log_renders_friendly_labels_for_non_crud_actions_and_models(): void
    {
        AuditLog::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'action' => 'auto_clockout',
            'model_type' => 'App\Models\ShiftAssignment',
            'model_id' => 10,
            'old_values' => ['status' => 'checked_in'],
            'new_values' => ['status' => 'auto_closed'],
            'ip_address' => '10.0.0.1',
        ]);

        AuditLog::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'action' => 'broadcast_existing_shift',
            'model_type' => 'App\Models\ShiftSwap',
            'model_id' => 12,
            'old_values' => null,
            'new_values' => ['broadcasted' => true],
            'ip_address' => '10.0.0.2',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Absen Keluar Otomatis');
        $response->assertSee('Pengiriman Jadwal Shift');
        $response->assertSee('Penugasan Karyawan');
        $response->assertSee('Tukar Shift');
    }

    /**
     * Skenario 2: Create, Update, dan Delete pada model auditable otomatis mencatat AuditLog.
     */
    public function test_creating_or_updating_model_automatically_writes_audit_log(): void
    {
        $this->actingAs($this->admin);

        // 1. Create Shift
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Pagi Baru',
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(16, 0),
            'quota' => 4,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'model_type' => Shift::class,
            'model_id' => $shift->id,
            'user_id' => $this->admin->id,
        ]);

        // 2. Update Shift
        $shift->update([
            'title' => 'Shift Pagi Diperbarui',
            'quota' => 6,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update',
            'model_type' => Shift::class,
            'model_id' => $shift->id,
            'user_id' => $this->admin->id,
        ]);

        // 3. Delete Shift
        $shiftId = $shift->id;
        $shift->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'delete',
            'model_type' => Shift::class,
            'model_id' => $shiftId,
            'user_id' => $this->admin->id,
        ]);

        // 4. Create CompanySetting
        $setting = CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'max_regular_hours' => 8,
            'max_overtime_hours' => 4,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'model_type' => CompanySetting::class,
            'model_id' => $setting->id,
            'user_id' => $this->admin->id,
        ]);
    }

    /**
     * Skenario 3: AuditLog menangkap JSON diff (old_values vs new_values) dan IP address secara akurat.
     */
    public function test_audit_log_captures_accurate_json_diff_and_ip_address(): void
    {
        $this->actingAs($this->admin);

        $fakeIp = '203.0.113.77';
        $fakeUserAgent = 'JadwalinAuditClient/3.0';

        $simulatedRequest = Request::create('/test-url', 'PUT', [], [], [], [
            'REMOTE_ADDR' => $fakeIp,
            'HTTP_USER_AGENT' => $fakeUserAgent,
        ]);
        $this->app->instance('request', $simulatedRequest);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Judul Lama',
            'start_time' => now()->setTime(9, 0),
            'end_time' => now()->setTime(17, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        $shift->update([
            'title' => 'Shift Judul Baru',
            'quota' => 5,
        ]);

        $log = AuditLog::where('model_type', Shift::class)
            ->where('model_id', $shift->id)
            ->where('action', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($fakeIp, $log->ip_address);
        $this->assertEquals($fakeUserAgent, $log->user_agent);

        $this->assertIsArray($log->old_values);
        $this->assertIsArray($log->new_values);

        $this->assertEquals('Shift Judul Lama', $log->old_values['title']);
        $this->assertEquals(2, $log->old_values['quota']);
        $this->assertEquals('Shift Judul Baru', $log->new_values['title']);
        $this->assertEquals(5, $log->new_values['quota']);

        // Field sensitif tidak boleh tercatat
        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertArrayNotHasKey('remember_token', $log->old_values);
        $this->assertArrayNotHasKey('remember_token', $log->new_values);
    }

    /**
     * Skenario 4: Pengguna non-admin (manager & employee) dilarang mengakses halaman audit logs (403/Redirect).
     */
    public function test_non_admin_cannot_access_audit_logs(): void
    {
        // Karyawan (employee) dilarang
        $employeeResponse = $this->actingAs($this->employee)->get(route('admin.audit-logs.index'));
        $employeeResponse->assertForbidden();

        // Manager dilarang
        $managerResponse = $this->actingAs($this->manager)->get(route('admin.audit-logs.index'));
        $managerResponse->assertForbidden();

        // Guest diarahkan ke halaman login
        auth()->logout();
        $guestResponse = $this->get(route('admin.audit-logs.index'));
        $guestResponse->assertRedirect(route('login'));
    }

    /**
     * Skenario 5: Audit log terisolasi secara ketat per perusahaan (Multi-Tenant).
     */
    public function test_audit_logs_are_strictly_isolated_by_company(): void
    {
        // 1. Log untuk Perusahaan A ($this->company)
        AuditLog::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'action' => 'update',
            'model_type' => Shift::class,
            'model_id' => 101,
            'old_values' => ['title' => 'Shift Rahasia Perusahaan A'],
            'new_values' => ['title' => 'Shift Publik Perusahaan A'],
            'ip_address' => '192.168.1.10',
        ]);

        // 2. Buat Perusahaan B dan Admin Perusahaan B
        $companyB = Company::forceCreate([
            'name' => 'PT Makmur Jaya Abadi',
            'email' => 'corp@makmur.test',
            'phone_number' => '081234567891',
            'address' => 'Jl. Hasanuddin No. 45',
            'timezone' => 'Asia/Makassar',
        ]);

        $adminB = User::factory()->create([
            'name' => 'Admin Perusahaan B',
            'role' => 'superadmin',
            'company_id' => $companyB->id,
        ]);

        AuditLog::create([
            'company_id' => $companyB->id,
            'user_id' => $adminB->id,
            'action' => 'delete',
            'model_type' => Shift::class,
            'model_id' => 202,
            'old_values' => ['title' => 'Data Rahasia Perusahaan B'],
            'new_values' => null,
            'ip_address' => '10.0.0.99',
        ]);

        // Login sebagai Admin Perusahaan A -> Hanya melihat log Perusahaan A
        $responseA = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));
        $responseA->assertOk();
        $responseA->assertSee('192.168.1.10');
        $responseA->assertDontSee('10.0.0.99');
        $responseA->assertDontSee('Admin Perusahaan B');

        // Login sebagai Admin Perusahaan B -> Hanya melihat log Perusahaan B
        $responseB = $this->actingAs($adminB)->get(route('admin.audit-logs.index'));
        $responseB->assertOk();
        $responseB->assertSee('10.0.0.99');
        $responseB->assertSee('Admin Perusahaan B');
        $responseB->assertDontSee('192.168.1.10');
    }

    /**
     * Skenario 6: Trait Auditable secara otomatis meng-assign company_id dan menolak log jika null.
     */
    public function test_audit_log_creation_automatically_assigns_correct_company_id(): void
    {
        $this->actingAs($this->admin);

        // 1. Model yang memiliki relasi / atribut company_id otomatis ter-assign
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Verifikasi Tenant',
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(16, 0),
            'quota' => 3,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'model_type' => Shift::class,
            'model_id' => $shift->id,
            'company_id' => $this->company->id,
        ]);

        // 2. Model tanpa company_id saat tidak ada user login (company_id null) -> TIDAK boleh membuat log
        auth()->logout();

        $beforeCount = AuditLog::withoutGlobalScopes()->count();

        // Model dummy tanpa company_id
        $orphanModel = new Shift;
        $reflection = new \ReflectionClass(Auditable::class);
        $method = $reflection->getMethod('recordAuditLog');
        $method->setAccessible(true);
        $method->invoke(null, $orphanModel, 'create');

        $this->assertEquals($beforeCount, AuditLog::withoutGlobalScopes()->count(), 'Audit log tidak boleh ter-create jika company_id bernilai null.');
    }

    /**
     * Skenario 7: Format timestamp pada audit log mengikuti konfigurasi timezone perusahaan.
     */
    public function test_audit_log_timestamp_follows_company_timezone_settings(): void
    {
        $fixedUtcTime = Carbon::create(2026, 11, 8, 10, 0, 0, 'UTC'); // 17:00 WIB, 18:00 WITA
        Carbon::setTestNow($fixedUtcTime);

        AuditLog::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'action' => 'update',
            'model_type' => Shift::class,
            'model_id' => 888,
            'old_values' => ['title' => 'Shift Pagi'],
            'new_values' => ['title' => 'Shift Sore'],
            'ip_address' => '127.0.0.1',
            'created_at' => $fixedUtcTime,
        ]);

        // Perusahaan A menggunakan Asia/Jakarta -> Menampilkan WIB
        $responseA = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));
        $responseA->assertOk();
        $responseA->assertSee('WIB');
        $responseA->assertSee('17:00 WIB');

        // Perusahaan Bali menggunakan Asia/Makassar -> Menampilkan WITA
        $companyBali = Company::forceCreate([
            'name' => 'PT Bali Mandiri',
            'email' => 'corp@bali.test',
            'phone_number' => '081234567892',
            'address' => 'Jl. Bypass Ngurah Rai No. 10',
            'timezone' => 'Asia/Makassar',
        ]);

        $adminBali = User::factory()->create([
            'name' => 'Admin Bali',
            'role' => 'superadmin',
            'company_id' => $companyBali->id,
        ]);

        AuditLog::create([
            'company_id' => $companyBali->id,
            'user_id' => $adminBali->id,
            'action' => 'update',
            'model_type' => Shift::class,
            'model_id' => 889,
            'old_values' => ['title' => 'Shift Pagi'],
            'new_values' => ['title' => 'Shift Malam'],
            'ip_address' => '127.0.0.2',
            'created_at' => $fixedUtcTime,
        ]);

        $responseBali = $this->actingAs($adminBali)->get(route('admin.audit-logs.index'));
        $responseBali->assertOk();
        $responseBali->assertSee('WITA');
        $responseBali->assertSee('18:00 WITA');
        $responseBali->assertDontSee('17:00 WIB');

        Carbon::setTestNow(null);
    }
}
