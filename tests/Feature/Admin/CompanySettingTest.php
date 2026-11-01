<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_and_edit_company_settings(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertOk();

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
        ]);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 40,
                'max_overtime_hours' => 18,
                'work_hour_limit_type' => 'weekly',
                'min_rest_hours' => 11,
                'max_working_days_per_week' => 6,
                'auto_buffer_minutes' => 30,
                'grace_period_minutes' => 15,
                'is_auto_clockout' => 1,
                'auto_clockout_buffer_hours' => 2,
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'auto_buffer_minutes' => 30,
            'auto_clockout_buffer_hours' => 2,
        ]);
    }

    public function test_settings_update_persists_properly_with_validation_rules(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 45,
                'max_overtime_hours' => 12,
                'work_hour_limit_type' => 'daily',
                'min_rest_hours' => 10,
                'max_working_days_per_week' => 5,
                'auto_buffer_minutes' => 45,
                'grace_period_minutes' => 10,
                'is_auto_clockout' => 0,
                'auto_clockout_buffer_hours' => 3,
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'max_regular_hours' => 45,
            'max_overtime_hours' => 12,
            'work_hour_limit_type' => 'daily',
            'min_rest_hours' => 10,
            'max_working_days_per_week' => 5,
            'auto_buffer_minutes' => 45,
            'grace_period_minutes' => 10,
            'auto_clockout_buffer_hours' => 3,
        ]);
    }

    public function test_non_admin_cannot_access_or_modify_settings(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'employee',
        ]);

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 40,
                'max_overtime_hours' => 18,
                'work_hour_limit_type' => 'weekly',
                'min_rest_hours' => 11,
                'max_working_days_per_week' => 6,
                'auto_buffer_minutes' => 30,
                'grace_period_minutes' => 15,
                'is_auto_clockout' => 1,
                'auto_clockout_buffer_hours' => 2,
            ])
            ->assertForbidden();
    }

    public function test_invalid_hours_or_negative_buffer_is_rejected(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->from(route('admin.settings.index'))
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 61,
                'max_overtime_hours' => 25,
                'work_hour_limit_type' => 'weekly',
                'min_rest_hours' => 11,
                'max_working_days_per_week' => 6,
                'auto_buffer_minutes' => -1,
                'grace_period_minutes' => 15,
                'is_auto_clockout' => 1,
                'auto_clockout_buffer_hours' => 2,
            ])
            ->assertSessionHasErrors([
                'max_regular_hours',
                'max_overtime_hours',
                'auto_buffer_minutes',
            ]);
    }

    public function test_superadmin_can_update_office_location_and_geofencing_settings(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 40,
                'max_overtime_hours' => 18,
                'work_hour_limit_type' => 'weekly',
                'min_rest_hours' => 11,
                'max_working_days_per_week' => 6,
                'auto_buffer_minutes' => 30,
                'grace_period_minutes' => 15,
                'is_auto_clockout' => 1,
                'auto_clockout_buffer_hours' => 2,
                'office_latitude' => -6.1753924,
                'office_longitude' => 106.8271528,
                'attendance_radius_meters' => 75,
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'office_latitude' => -6.1753924,
            'office_longitude' => 106.8271528,
            'attendance_radius_meters' => 75,
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'office_latitude' => -6.1753924,
            'office_longitude' => 106.8271528,
            'attendance_radius_meters' => 75,
        ]);
    }

    public function test_settings_update_handles_null_or_empty_attendance_radius_meters_safely(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        // Submit form dengan attendance_radius_meters null / empty
        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'max_regular_hours' => 40,
                'max_overtime_hours' => 18,
                'work_hour_limit_type' => 'weekly',
                'min_rest_hours' => 11,
                'max_working_days_per_week' => 6,
                'auto_buffer_minutes' => 30,
                'grace_period_minutes' => 15,
                'is_auto_clockout' => 1,
                'auto_clockout_buffer_hours' => 2,
                'attendance_radius_meters' => null,
            ])
            ->assertRedirect(route('admin.settings.index'));

        // Radius tidak boleh null di database
        $this->assertDatabaseMissing('company_settings', [
            'company_id' => $company->id,
            'attendance_radius_meters' => null,
        ]);
    }

    public function test_superadmin_can_update_specific_branch_location_and_radius(): void
    {
        $company = Company::factory()->create();

        $branch = Department::forceCreate([
            'company_id' => $company->id,
            'name' => 'Cabang Bali Sunset',
            'latitude' => -8.7185,
            'longitude' => 115.1686,
            'radius' => 80,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->put(route('admin.settings.update-branch-location'), [
                'branch_id' => $branch->id,
                'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
                'latitude' => -8.6500,
                'longitude' => 115.2166,
                'radius' => 120,
            ])
            ->assertRedirect(route('admin.settings.index', ['branch_id' => $branch->id]));

        $this->assertDatabaseHas('departments', [
            'id' => $branch->id,
            'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
            'latitude' => -8.6500,
            'longitude' => 115.2166,
            'radius' => 120,
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'address' => 'Jl. Sunset Road No. 88, Kuta, Bali',
            'office_latitude' => -8.6500,
            'office_longitude' => 115.2166,
            'attendance_radius_meters' => 120,
        ]);
    }

    public function test_employee_attendance_page_synchronizes_with_updated_branch_location_from_superadmin(): void
    {
        $company = Company::factory()->create([
            'address' => 'Jl. Kantor Lama No. 1',
        ]);

        $branch = Department::forceCreate([
            'company_id' => $company->id,
            'name' => 'Cabang Surabaya Gubeng',
            'address' => 'Jl. Gubeng Pojok Lama',
            'latitude' => -7.2654,
            'longitude' => 112.7518,
            'radius' => 50,
        ]);

        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $employee = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $branch->id,
            'role' => 'employee',
        ]);

        // Superadmin mengupdate lokasi cabang ke lokasi baru
        $newAddress = 'Jl. Pemuda No. 45, Surabaya';
        $newLat = -7.2635000;
        $newLng = 112.7482000;
        $newRadius = 150;

        $this->actingAs($superadmin)
            ->put(route('admin.settings.update-branch-location'), [
                'branch_id' => $branch->id,
                'address' => $newAddress,
                'latitude' => $newLat,
                'longitude' => $newLng,
                'radius' => $newRadius,
            ])
            ->assertRedirect(route('admin.settings.index', ['branch_id' => $branch->id]))
            ->assertSessionHas('selected_branch_id', $branch->id);

        // Halaman presensi karyawan harus langsung mencerminkan data cabang yang baru disimpan
        $response = $this->actingAs($employee)->get(route('employee.attendance'));

        $response->assertOk();
        $response->assertViewHas('officeLat', $newLat);
        $response->assertViewHas('officeLng', $newLng);
        $response->assertViewHas('radius', $newRadius);
        $response->assertViewHas('outlet', function ($outlet) use ($newAddress, $newLat, $newLng, $newRadius) {
            return $outlet['address'] === $newAddress
                && (float) $outlet['latitude'] === $newLat
                && (float) $outlet['longitude'] === $newLng
                && (int) $outlet['radius'] === $newRadius;
        });

        $response->assertSee($newAddress);
        $response->assertSee('Cabang Surabaya Gubeng');
    }

    public function test_branch_location_supports_decimal_comma_normalization(): void
    {
        $company = Company::factory()->create();

        $branch = Department::forceCreate([
            'company_id' => $company->id,
            'name' => 'Cabang Malang Polinema',
            'address' => 'Jl. Soekarno Hatta',
            'latitude' => null,
            'longitude' => null,
            'radius' => 100,
        ]);

        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
        ]);

        $this->actingAs($superadmin)
            ->put(route('admin.settings.update-branch-location'), [
                'branch_id' => $branch->id,
                'address' => 'Politeknik Negeri Malang',
                'latitude' => '-7,9439111',
                'longitude' => '112,6148093',
                'radius' => '100',
            ])
            ->assertRedirect(route('admin.settings.index', ['branch_id' => $branch->id]))
            ->assertSessionHas('selected_branch_id', $branch->id);

        $branch->refresh();
        $this->assertEquals(-7.9439111, (float) $branch->latitude);
        $this->assertEquals(112.6148093, (float) $branch->longitude);
        $this->assertEquals('Politeknik Negeri Malang', $branch->address);
    }
}
