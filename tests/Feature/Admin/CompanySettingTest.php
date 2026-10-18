<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\CompanySetting;
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
}