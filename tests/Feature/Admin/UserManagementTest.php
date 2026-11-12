<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(string $name = 'Test Company'): Company
    {
        return Company::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)).'@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createSuperadmin(Company $company): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Superadmin '.$company->name,
            'email' => 'admin.'.strtolower(str_replace(' ', '', $company->name)).'@example.com',
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    private function createDepartment(Company $company, string $name = 'Operasional'): Department
    {
        return Department::create([
            'company_id' => $company->id,
            'name' => $name,
            'description' => 'Department '.$name,
        ]);
    }

    private function createPosition(Company $company, Department $department, string $name = 'Staff'): Position
    {
        return Position::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'name' => $name,
        ]);
    }

    /**
     * Skenario 1 (Wajib): Superadmin dapat melihat daftar user milik perusahaannya sendiri.
     */
    public function test_superadmin_can_view_user_list_in_own_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $superadminA = $this->createSuperadmin($companyA);
        $employeeA = User::factory()->create([
            'company_id' => $companyA->id,
            'name' => 'Budi Santoso Company A',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $companyB = $this->createCompany('Company B');
        $employeeB = User::factory()->create([
            'company_id' => $companyB->id,
            'name' => 'Siti Rahma Company B',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($superadminA)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso Company A');
        $response->assertSee($superadminA->name);
        $response->assertDontSee('Siti Rahma Company B');
    }

    /**
     * Skenario 2 (Wajib): Superadmin dapat mendaftarkan manager dan employee baru.
     */
    public function test_superadmin_can_register_new_manager_and_employee(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);
        $department = $this->createDepartment($company, 'Teknologi');
        $position = $this->createPosition($company, $department, 'Engineering Lead');

        // 1. Daftarkan Manager
        $managerData = [
            'name' => 'Ahmad Manajer',
            'email' => 'ahmad.manager@example.com',
            'password' => 'password123',
            'role' => 'manager',
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_type' => 'full_time',
            'phone_number' => '081298765432',
            'join_date' => '2026-02-01',
            'is_global_manager' => true,
        ];

        $responseManager = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), $managerData);

        $responseManager->assertRedirect(route('admin.users.index'));
        $responseManager->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'name' => 'Ahmad Manajer',
            'email' => 'ahmad.manager@example.com',
            'role' => 'manager',
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'is_global_manager' => 1,
        ]);

        // 2. Daftarkan Employee
        $employeeData = [
            'name' => 'Dewi Karyawan',
            'email' => 'dewi.employee@example.com',
            'password' => 'password123',
            'role' => 'employee',
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_type' => 'part_time',
            'max_weekly_hours' => 20,
        ];

        $responseEmployee = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), $employeeData);

        $responseEmployee->assertRedirect(route('admin.users.index'));
        $responseEmployee->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'name' => 'Dewi Karyawan',
            'email' => 'dewi.employee@example.com',
            'role' => 'employee',
            'status' => 'active',
            'employment_type' => 'part_time',
            'max_weekly_hours' => 20,
            'is_global_manager' => 0,
        ]);
    }

    /**
     * Skenario 3 (Wajib): Keunikan email ditegakkan saat pembuatan pengguna.
     */
    public function test_email_uniqueness_is_enforced_upon_user_creation(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'duplicate@example.com',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), [
                'name' => 'Pengguna Duplikat',
                'email' => 'duplicate@example.com',
                'password' => 'password123',
                'role' => 'employee',
                'status' => 'active',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', [
            'name' => 'Pengguna Duplikat',
        ]);
    }

    /**
     * Skenario 4 (Wajib): Superadmin tidak dapat melihat, mengakses, atau mengedit pengguna tenant lain.
     */
    public function test_superadmin_cannot_access_or_edit_users_from_another_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $superadminA = $this->createSuperadmin($companyA);

        $companyB = $this->createCompany('Company B');
        $userB = User::factory()->create([
            'company_id' => $companyB->id,
            'name' => 'User Rahasia Company B',
            'email' => 'rahasia.b@example.com',
            'role' => 'employee',
            'status' => 'active',
        ]);

        // 1. Akses Edit -> 404 Not Found
        $editResponse = $this
            ->actingAs($superadminA)
            ->get(route('admin.users.edit', $userB));
        $editResponse->assertStatus(404);

        // 2. Akses Show -> 404 Not Found
        $showResponse = $this
            ->actingAs($superadminA)
            ->get(route('admin.users.show', $userB));
        $showResponse->assertStatus(404);

        // 3. Update data lintas tenant -> 404 Not Found & DB tidak berubah
        $updateResponse = $this
            ->actingAs($superadminA)
            ->put(route('admin.users.update', $userB), [
                'name' => 'Hacked Name B',
                'email' => 'hacked.b@example.com',
                'role' => 'employee',
                'status' => 'active',
            ]);
        $updateResponse->assertStatus(404);
        $this->assertDatabaseMissing('users', ['name' => 'Hacked Name B']);

        // 4. Delete lintas tenant -> 404 Not Found & DB tidak terhapus
        $deleteResponse = $this
            ->actingAs($superadminA)
            ->delete(route('admin.users.destroy', $userB));
        $deleteResponse->assertStatus(404);
        $this->assertDatabaseHas('users', [
            'id' => $userB->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Skenario 5 (Wajib): Pengguna non-admin (Manager/Employee/Tamu) dilarang mengakses modul kelola pengguna.
     */
    public function test_non_admin_cannot_access_user_management_module(): void
    {
        $company = $this->createCompany();

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        // 1. Tamu (Unauthenticated) -> Redirect ke login
        $responseGuest = $this->get(route('admin.users.index'));
        $responseGuest->assertRedirect(route('login'));

        // 2. Manager -> 403 Forbidden
        $responseManager = $this
            ->actingAs($manager)
            ->get(route('admin.users.index'));
        $responseManager->assertStatus(403);

        // 3. Employee -> 403 Forbidden
        $responseEmployee = $this
            ->actingAs($employee)
            ->get(route('admin.users.index'));
        $responseEmployee->assertStatus(403);
    }

    /**
     * Pengujian Tambahan: Validasi departemen dan posisi lintas tenant ditolak.
     */
    public function test_cannot_assign_department_or_position_from_another_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $superadminA = $this->createSuperadmin($companyA);

        $companyB = $this->createCompany('Company B');
        $departmentB = $this->createDepartment($companyB, 'Dept B');
        $positionB = $this->createPosition($companyB, $departmentB, 'Pos B');

        $response = $this
            ->actingAs($superadminA)
            ->post(route('admin.users.store'), [
                'name' => 'Penyusup Tenant',
                'email' => 'penyusup@example.com',
                'password' => 'password123',
                'role' => 'employee',
                'status' => 'active',
                'department_id' => $departmentB->id,
                'position_id' => $positionB->id,
            ]);

        $response->assertSessionHasErrors(['department_id', 'position_id']);
        $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
    }

    /**
     * Pengujian Tambahan: Validasi posisi harus sesuai dengan departemen yang dipilih.
     */
    public function test_cannot_assign_position_not_belonging_to_selected_department(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $deptKitchen = $this->createDepartment($company, 'Kitchen');
        $deptBar = $this->createDepartment($company, 'Bar');

        $posBarista = $this->createPosition($company, $deptBar, 'Barista');

        // Mencoba memilih departemen Kitchen tapi posisinya Barista (milik Bar)
        $response = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), [
                'name' => 'Staf Salah Divisi',
                'email' => 'salahdivisi@example.com',
                'password' => 'password123',
                'role' => 'employee',
                'status' => 'active',
                'department_id' => $deptKitchen->id,
                'position_id' => $posBarista->id,
            ]);

        $response->assertSessionHasErrors('position_id');
        $this->assertDatabaseMissing('users', ['email' => 'salahdivisi@example.com']);
    }

    /**
     * Pengujian Tambahan: Superadmin tidak dapat menghapus akunnya sendiri.
     */
    public function test_superadmin_cannot_delete_own_account(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $response = $this
            ->actingAs($superadmin)
            ->delete(route('admin.users.destroy', $superadmin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Pengujian Tambahan: Tidak dapat menghapus user yang memiliki shift assignment aktif.
     */
    public function test_cannot_delete_user_with_active_shift_assignment(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $department = $this->createDepartment($company);
        $shift = Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Kerja Pagi',
            'start_time' => now()->startOfWeek(),
            'end_time' => now()->startOfWeek()->addHours(8),
            'quota' => 3,
            'status' => 'published',
        ]);

        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'status' => 'scheduled',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->delete(route('admin.users.destroy', $employee));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Pengguna tidak dapat dihapus karena masih memiliki penugasan shift yang aktif.');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Pengujian Tambahan: Password tidak berubah jika field password dikosongkan saat update.
     */
    public function test_password_is_not_changed_when_empty_during_update(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
            'status' => 'active',
            'password' => Hash::make('original-password-123'),
        ]);

        $originalPasswordHash = $employee->password;

        $response = $this
            ->actingAs($superadmin)
            ->put(route('admin.users.update', $employee), [
                'name' => 'Nama Baru Karyawan',
                'email' => $employee->email,
                'role' => 'employee',
                'status' => 'active',
                'password' => '', // Dikosongkan
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertEquals('Nama Baru Karyawan', $employee->name);
        $this->assertEquals($originalPasswordHash, $employee->password);
    }

    /**
     * Pengujian Tambahan: Soft delete berjalan dengan benar pada penghapusan akun.
     */
    public function test_user_is_soft_deleted_upon_deletion(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->delete(route('admin.users.destroy', $employee));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $employee->id,
        ]);
    }

    /**
     * Pengujian Tambahan: Superadmin dilarang membuat atau menaikkan role pengguna menjadi superadmin.
     */
    public function test_superadmin_cannot_create_or_promote_user_to_superadmin(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        // 1. Ditolak saat store
        $responseStore = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), [
                'name' => 'Superadmin Baru',
                'email' => 'new.superadmin@example.com',
                'password' => 'password123',
                'role' => 'superadmin',
                'status' => 'active',
            ]);

        $responseStore->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'new.superadmin@example.com']);

        // 2. Ditolak saat update (menaikkan role employee ke superadmin)
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $responseUpdate = $this
            ->actingAs($superadmin)
            ->put(route('admin.users.update', $employee), [
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => 'superadmin',
                'status' => 'active',
            ]);

        $responseUpdate->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'role' => 'employee',
        ]);
    }

    /**
     * Skenario 13: Super Admin dapat melakukan pencarian user berdasarkan nomor telepon tanpa query crash.
     */
    public function test_superadmin_can_search_users_by_phone_number_without_query_error(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Budi Setiawan',
            'email' => 'budi.setiawan@example.com',
            'phone_number' => '087712345678',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->get(route('admin.users.index', ['search' => '08771234']));

        $response->assertOk();
        $response->assertSee('Budi Setiawan');
        $response->assertSee('087712345678');
    }

    /**
     * Skenario 14: Nomor telepon tersimpan dan ter-update dengan benar pada kolom phone_number.
     */
    public function test_phone_number_is_properly_saved_and_displayed(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $postData = [
            'name' => 'Rina Sasmita',
            'email' => 'rina.sasmita@example.com',
            'phone_number' => '089988776655',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => 'employee',
            'status' => 'active',
            'employment_type' => 'full_time',
        ];

        $response = $this
            ->actingAs($superadmin)
            ->post(route('admin.users.store'), $postData);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'rina.sasmita@example.com',
            'phone_number' => '089988776655',
        ]);

        $user = User::where('email', 'rina.sasmita@example.com')->first();

        // Verifikasi pada form edit menampilkan nomor telepon
        $editResponse = $this
            ->actingAs($superadmin)
            ->get(route('admin.users.edit', $user));

        $editResponse->assertOk();
        $editResponse->assertSee('089988776655');

        // Update nomor telepon
        $updateResponse = $this
            ->actingAs($superadmin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Rina Sasmita Updated',
                'email' => 'rina.sasmita@example.com',
                'phone_number' => '081122334455',
                'role' => 'employee',
                'status' => 'active',
                'employment_type' => 'full_time',
            ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone_number' => '081122334455',
        ]);
    }

    /**
     * Skenario 15: Pengguna dengan status inactive diblokir saat mencoba login.
     */
    public function test_user_with_inactive_status_is_blocked_from_logging_in(): void
    {
        $company = $this->createCompany();

        $inactiveUser = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Karyawan Non-Aktif',
            'email' => 'inactive.user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'status' => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'inactive.user@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Skenario 16: Super Admin dapat memperbarui profil akunnya sendiri tanpa error validasi role.
     */
    public function test_superadmin_can_update_own_account_without_role_validation_error(): void
    {
        $company = $this->createCompany();
        $superadmin = $this->createSuperadmin($company);

        $response = $this
            ->actingAs($superadmin)
            ->put(route('admin.users.update', $superadmin), [
                'name' => 'Superadmin Nama Baru',
                'email' => $superadmin->email,
                'phone_number' => '081299998888',
                'role' => 'superadmin',
                'status' => 'active',
                'employment_type' => 'full_time',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'name' => 'Superadmin Nama Baru',
            'phone_number' => '081299998888',
            'role' => 'superadmin',
        ]);
    }
}
