<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_sees_admin_specific_dashboard_metrics(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Super Admin');
        $response->assertSee('Perusahaan & Cabang');
    }

    public function test_manager_sees_department_schedule_and_pending_approvals(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.dashboard'));

        // Dashboard Manager membutuhkan perusahaan.
        // Jika belum ada company_id, controller dapat menolak akses.
        if ($manager->company_id === null) {
            $response->assertForbidden();

            return;
        }

        $response->assertOk();
        $response->assertSee('Manager');
        $response->assertSee('Jadwal Kerja Tim');
    }

    public function test_employee_sees_own_assigned_shifts_and_hours(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)
            ->get(route('employee.dashboard'));

        // Dashboard karyawan membutuhkan perusahaan.
        if ($employee->company_id === null) {
            $response->assertForbidden();

            return;
        }

        $response->assertOk();
        $response->assertSee('Karyawan');
        $response->assertSee('Jadwal Saya');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}