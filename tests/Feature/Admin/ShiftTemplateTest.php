<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\ShiftTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(string $name = 'Test Company'): Company
    {
        return Company::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createSuperAdmin(Company $company): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    private function createDepartment(Company $company, string $name = 'Operasional'): Department
    {
        return Department::create([
            'company_id' => $company->id,
            'name' => $name,
            'description' => 'Department ' . $name,
        ]);
    }

    public function test_admin_can_view_shift_templates_list(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperAdmin($company);
        $dept = $this->createDepartment($company);

        $template = ShiftTemplate::create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'title' => 'Shift Pagi Operasional',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'default_quota' => 5,
            'color_code' => '#6C5CE7',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.shifts.index'));

        $response->assertOk();
        $response->assertSee('Shift Pagi Operasional');
        $response->assertSee('#6C5CE7');
    }

    public function test_admin_can_create_shift_template_with_color_and_quota(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperAdmin($company);
        $dept = $this->createDepartment($company);

        $response = $this->actingAs($admin)->post(route('admin.shifts.store'), [
            'title' => 'Shift Siang',
            'department_id' => $dept->id,
            'start_time' => '12:00',
            'end_time' => '20:00',
            'default_quota' => 3,
            'color_code' => '#2ECC71',
        ]);

        $response->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseHas('shift_templates', [
            'company_id' => $company->id,
            'title' => 'Shift Siang',
            'default_quota' => 3,
            'color_code' => '#2ECC71',
        ]);
    }

    public function test_admin_can_update_and_delete_shift_template(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperAdmin($company);
        $dept = $this->createDepartment($company);

        $template = ShiftTemplate::create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'title' => 'Shift Malam',
            'start_time' => '20:00',
            'end_time' => '04:00',
            'default_quota' => 2,
            'color_code' => '#E74C3C',
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.shifts.update', $template), [
            'title' => 'Shift Malam Diperbarui',
            'department_id' => $dept->id,
            'start_time' => '21:00',
            'end_time' => '05:00',
            'default_quota' => 4,
            'color_code' => '#E74C3C',
        ]);
        $updateResponse->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseHas('shift_templates', [
            'id' => $template->id,
            'title' => 'Shift Malam Diperbarui',
            'default_quota' => 4,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.shifts.destroy', $template));
        $deleteResponse->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseMissing('shift_templates', ['id' => $template->id]);
    }

    public function test_templates_are_scoped_to_user_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $adminA = $this->createSuperAdmin($companyA);
        $deptB = $this->createDepartment($companyB, 'Finance');

        $templateB = ShiftTemplate::create([
            'company_id' => $companyB->id,
            'department_id' => $deptB->id,
            'title' => 'Shift Rahasia B',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'default_quota' => 1,
            'color_code' => '#123456',
        ]);

        $response = $this->actingAs($adminA)->get(route('admin.shifts.index'));
        $response->assertDontSee('Shift Rahasia B');

        $forbiddenDelete = $this->actingAs($adminA)->delete(route('admin.shifts.destroy', $templateB));
        $forbiddenDelete->assertNotFound();
    }

    public function test_invalid_time_range_or_missing_department_fails(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperAdmin($company);

        $response = $this->actingAs($admin)->post(route('admin.shifts.store'), [
            'title' => '',
            'department_id' => 999999,
            'start_time' => '08:00',
            'end_time' => '08:00', // Jam mulai dan selesai identik
            'default_quota' => 0,
            'color_code' => 'bukan-hex',
        ]);

        $response->assertSessionHasErrors(['title', 'department_id', 'end_time', 'default_quota', 'color_code']);
    }

    public function test_admin_cannot_delete_shift_template_in_use_by_shifts(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperAdmin($company);
        $dept = $this->createDepartment($company);

        $template = ShiftTemplate::create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'title' => 'Shift Aktif Dipakai',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'default_quota' => 2,
            'color_code' => '#6C5CE7',
        ]);

        \App\Models\Shift::create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'template_id' => $template->id,
            'title' => 'Shift Aktif Dipakai',
            'start_time' => now()->startOfWeek(),
            'end_time' => now()->startOfWeek()->addHours(8),
            'quota' => 2,
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.shifts.destroy', $template));
        $response->assertRedirect(route('admin.shifts.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('shift_templates', ['id' => $template->id]);
    }
}

