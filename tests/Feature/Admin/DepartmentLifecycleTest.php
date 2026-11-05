<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $superadmin;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->superadmin = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'F&B Kitchen',
        ]);
    }

    /**
     * TC-DEP-01: Superadmin dapat memperbarui data departemen termasuk titik koordinat GPS cabang.
     */
    public function test_superadmin_can_update_department_details_and_geofence_coordinates(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->put(route('admin.departments.update', $this->department), [
                'name' => 'F&B Kitchen & Bar',
                'description' => 'Divisi Kitchen, Pastry, dan Bar',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'radius' => 150,
                'address' => 'Gedung Wisata Nusantara Lt 1',
            ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->department->refresh();

        $this->assertEquals('F&B Kitchen & Bar', $this->department->name);
        $this->assertEquals(-6.175392, $this->department->latitude);
        $this->assertEquals(106.827153, $this->department->longitude);
        $this->assertEquals(150, $this->department->radius);
    }

    /**
     * TC-DEP-02: Departemen dilarang dihapus jika masih memiliki karyawan aktif yang terdaftar.
     */
    public function test_department_cannot_be_deleted_if_has_active_employees(): void
    {
        // Daftarkan 1 staf aktif di departemen ini
        User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->delete(route('admin.departments.destroy', $this->department));

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('error');

        // Pastikan departemen tidak terhapus
        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
        ]);
    }

    /**
     * TC-DEP-03: Departemen dapat dihapus jika sudah tidak ada karyawan aktif.
     */
    public function test_department_can_be_deleted_if_no_active_employees(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->delete(route('admin.departments.destroy', $this->department));

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', [
            'id' => $this->department->id,
        ]);
    }
}
