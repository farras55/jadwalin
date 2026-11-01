<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetReportTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(string $name = 'PT Jadwalin Prima'): Company
    {
        return Company::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)).'@example.com',
            'phone_number' => '081298765432',
            'address' => 'Jl. Sudirman No. 45, Jakarta',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }

    private function createSuperadmin(Company $company): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    private function createEmployee(Company $company, ?Department $department = null, string $name = 'Karyawan Test'): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department?->id,
            'name' => $name,
            'role' => 'employee',
            'status' => 'active',
        ]);
    }

    /**
     * Skenario 1: Admin dapat membuka dan melihat halaman laporan rekapitulasi timesheet.
     */
    public function test_admin_can_view_timesheet_report_page(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperadmin($company);
        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Divisi Operasional',
            'description' => 'Operasional Lapangan',
        ]);

        $employee = $this->createEmployee($company, $department, 'Ahmad Fajar');

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.reports.timesheet'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Timesheet');
        $response->assertSee('Ekspor Excel');
        $response->assertSee('Ekspor PDF');
        $response->assertSee('Divisi Operasional');
        $response->assertSee('Ahmad Fajar');
    }

    /**
     * Skenario 2: Sistem menghitung dan mengagregasi total jam reguler dan jam lembur secara akurat.
     */
    public function test_timesheet_correctly_aggregates_regular_and_overtime_hours(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperadmin($company);
        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Departemen Logistik',
        ]);

        $employee = $this->createEmployee($company, $department, 'Budi Santoso');

        $shift = Shift::create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'title' => 'Shift Pagi Reguler',
            'start_time' => Carbon::create(2026, 10, 5, 8, 0, 0),
            'end_time' => Carbon::create(2026, 10, 5, 17, 0, 0),
            'quota' => 5,
            'status' => 'published',
        ]);

        // Shift 1: Dihadiri (Clock in & out terisi) -> 8.0 jam reguler, 2.0 jam lembur, 15 menit telat
        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => Carbon::create(2026, 10, 5, 8, 15, 0),
            'clock_out_time' => Carbon::create(2026, 10, 5, 18, 15, 0),
            'regular_hours' => 8.00,
            'overtime_hours' => 2.00,
            'late_minutes' => 15,
            'status' => 'late',
        ]);

        // Shift 2: Dihadiri (Clock in & out terisi) -> 7.5 jam reguler, 1.5 jam lembur, 0 menit telat
        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => Carbon::create(2026, 10, 6, 8, 0, 0),
            'clock_out_time' => Carbon::create(2026, 10, 6, 17, 0, 0),
            'regular_hours' => 7.50,
            'overtime_hours' => 1.50,
            'late_minutes' => 0,
            'status' => 'present',
        ]);

        // Shift 3: Dijadwalkan tetapi TIDAK hadir (clock in & out null) -> Tidak boleh dihitung ke jam reguler/lembur
        ShiftAssignment::create([
            'company_id' => $company->id,
            'shift_id' => $shift->id,
            'user_id' => $employee->id,
            'clock_in_time' => null,
            'clock_out_time' => null,
            'regular_hours' => 0.00,
            'overtime_hours' => 0.00,
            'late_minutes' => 0,
            'status' => 'scheduled',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.reports.timesheet', [
                'month' => 10,
                'year' => 2026,
                'department_id' => $department->id,
            ]));

        $response->assertStatus(200);

        // Verifikasi hasil agregasi pada view data
        $reports = $response->viewData('reports');
        $this->assertNotNull($reports);

        $budiData = $reports->firstWhere('user_id', $employee->id);
        $this->assertNotNull($budiData);

        // Agregasi: Jam Reguler = 8.0 + 7.5 = 15.5
        $this->assertEquals(15.50, $budiData->total_regular_hours);

        // Agregasi: Jam Lembur = 2.0 + 1.5 = 3.5
        $this->assertEquals(3.50, $budiData->total_overtime_hours);

        // Agregasi: Menit Keterlambatan = 15
        $this->assertEquals(15, $budiData->total_late_minutes);

        // Shift Dihadiri = 2, Total Dijadwalkan = 3
        $this->assertEquals(2, $budiData->attended_shifts);
        $this->assertEquals(3, $budiData->scheduled_shifts);

        // Verifikasi tampilan pada HTML output
        $response->assertSee('Budi Santoso');
        $response->assertSee('15.50');
        $response->assertSee('3.50');
    }

    /**
     * Skenario 3: Admin dapat mengekspor rekapitulasi timesheet ke format CSV / Excel (.xlsx).
     */
    public function test_admin_can_export_timesheet_to_csv_excel(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperadmin($company);
        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Departemen Keuangan',
        ]);

        $employee = $this->createEmployee($company, $department, 'Siti Nurhaliza');

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.reports.timesheet.export-excel', [
                'month' => 10,
                'year' => 2026,
                'department_id' => $department->id,
            ]));

        $response->assertStatus(200);

        // Verifikasi header stream download attachment
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('.xlsx', $contentDisposition);

        // Verifikasi bahwa file binary Excel valid (format OpenXML Zip dimulai dengan PK\x03\x04)
        $content = $response->streamedContent();
        $this->assertTrue(str_starts_with($content, "PK\x03\x04"), 'Isi file Excel harus berupa file OpenXML XLSX biner yang valid.');
    }

    /**
     * Skenario 4: Admin dapat mengekspor rekapitulasi timesheet ke format PDF (.pdf).
     */
    public function test_admin_can_export_timesheet_to_pdf(): void
    {
        $company = $this->createCompany();
        $admin = $this->createSuperadmin($company);
        $department = Department::create([
            'company_id' => $company->id,
            'name' => 'Departemen Keuangan',
        ]);

        $employee = $this->createEmployee($company, $department, 'Siti Nurhaliza');

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.reports.timesheet.export-pdf', [
                'month' => 10,
                'year' => 2026,
                'department_id' => $department->id,
            ]));

        $response->assertStatus(200);

        // Verifikasi tipe konten PDF dan stream attachment
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('.pdf', $contentDisposition);

        // Verifikasi bahwa file binary PDF valid (dimulai dengan magic bytes %PDF)
        $content = $response->getContent();
        $this->assertTrue(str_starts_with($content, '%PDF'), 'Isi file PDF harus berupa dokumen biner PDF yang valid.');
    }

    /**
     * Skenario 5: Karyawan biasa (role employee) ditolak aksesnya ke laporan admin timesheet (403 Forbidden).
     */
    public function test_employee_cannot_access_timesheet_admin_report(): void
    {
        $company = $this->createCompany();
        $employee = $this->createEmployee($company, null, 'Karyawan Biasa');

        // Percobaan akses halaman utama report admin
        $response = $this
            ->actingAs($employee)
            ->get(route('admin.reports.timesheet'));

        $response->assertStatus(403);

        // Percobaan akses endpoint ekspor Excel
        $responseExcel = $this
            ->actingAs($employee)
            ->get(route('admin.reports.timesheet.export-excel'));

        $responseExcel->assertStatus(403);

        // Percobaan akses endpoint ekspor PDF
        $responsePdf = $this
            ->actingAs($employee)
            ->get(route('admin.reports.timesheet.export-pdf'));

        $responsePdf->assertStatus(403);
    }
}
