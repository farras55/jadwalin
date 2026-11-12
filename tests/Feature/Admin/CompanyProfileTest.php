<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_own_company_profile()
    {
        $company = Company::factory()->create();
        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/companies');

        $response->assertStatus(200);
        $response->assertViewIs('admin.companies.index');
        $response->assertSee($company->name);
    }

    public function test_superadmin_can_update_company_details_with_valid_data()
    {
        $company = Company::factory()->create();
        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $updateData = [
            'name' => 'PT Jadwalin Sukses',
            'email' => 'admin@jadwalin.co.id',
            'phone_number' => '0211234567',
            'address' => 'Jl. Soekarno Hatta No. 9, Malang',
            'timezone' => 'Asia/Jakarta',
        ];

        $response = $this->actingAs($superadmin)->put('/admin/companies', $updateData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'PT Jadwalin Sukses',
            'email' => 'admin@jadwalin.co.id',
            'timezone' => 'Asia/Jakarta'
        ]);
    }

    public function test_invalid_timezone_or_email_format_is_rejected()
    {
        $company = Company::factory()->create();
        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $invalidData = [
            'name' => 'PT Jadwalin',
            'email' => 'format-email-salah',
            'phone_number' => '021123',
            'address' => 'Malang',
            'timezone' => 'Asia/Tokyo',
        ];

        $response = $this->actingAs($superadmin)->put('/admin/companies', $invalidData);

        $response->assertSessionHasErrors(['email', 'timezone']);
    }

    public function test_user_cannot_update_other_company_profile()
    {
        $companyA = Company::factory()->create(['name' => 'Tenant A']);
        $companyB = Company::factory()->create(['name' => 'Tenant B']);

        $superadminA = User::factory()->create([
            'company_id' => $companyA->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($superadminA)->put('/admin/companies', [
            'name' => 'Tenant A Berubah',
            'email' => 'new@tenanta.com',
            'phone_number' => '081234',
            'address' => 'Alamat A',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $companyA->id,
            'name' => 'Tenant A Berubah',
        ]);

        $this->assertDatabaseMissing('companies', [
            'id' => $companyB->id,
            'name' => 'Tenant A Berubah',
        ]);
    }
}