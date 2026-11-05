<?php

namespace Tests\Feature;

use App\Exports\TimesheetExport;
use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Services\TimesheetExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetExportUnifiedFormatTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $departmentA;

    protected Department $departmentB;

    protected User $superadmin;

    protected User $managerA;

    protected User $staffA;

    protected User $staffB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'PT Cakrawala Mega Pratama',
            'email' => 'corp@cakrawala.test',
            'phone_number' => '081122334455',
            'address' => 'Gedung Cakrawala Lt 5',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->departmentA = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Operasional',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'radius_meters' => 100,
            'address' => 'Kantor Operasional Lt 1',
        ]);

        $this->departmentB = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Pemasaran',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'radius_meters' => 100,
            'address' => 'Kantor Pemasaran Lt 2',
        ]);

        $this->superadmin = User::factory()->create([
            'name' => 'Super Admin Pratama',
            'role' => 'superadmin',
            'company_id' => $this->company->id,
            'department_id' => null,
        ]);

        $this->managerA = User::factory()->create([
            'name' => 'Hendra Saputra (Manager Operasional)',
            'role' => 'manager',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
        ]);

        $this->staffA = User::factory()->create([
            'name' => 'Dewi Manan',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
        ]);

        $this->staffB = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentB->id,
        ]);
    }

    /**
     * Memverifikasi struktur tabel Excel sesuai layout acuan pengguna:
     * - Sheet title 'Worksheet'
     * - Kop Judul: PT CAKRAWALA MEGA PRATAMA & REKAPITULASI TIMESHEET
     * - Metadata: Periode Laporan, Departemen, Status, Tanggal Pembuatan dengan ':'
     * - Kolom: No, Nama Karyawan, Departemen, Shift Dijadwalkan, Shift Dihadiri, Hari Kehadiran,
     *   Jam Reguler, Jam Lembur, Jam Tidak Dihadiri, Keterlambatan
     * - Kolom 'Jam Tidak Dihadiri' tersedia
     * - Kolom 'Keterlambatan' berakhiran 'Menit' (misal '379 Menit')
     * - Baris TOTAL di bawah
     */
    public function test_excel_export_structure_and_headers_match_specification(): void
    {
        $baseDate = Carbon::create(2026, 10, 15, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        // 1 shift dihadiri (7.63 jam, lembur 0, terlambat 379 menit)
        $shiftAttended = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Dihadiri',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(16, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shiftAttended->id,
            'user_id' => $this->staffA->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 30, 0),
            'clock_out_time' => $baseDate->copy()->setTime(16, 0, 0),
            'regular_hours' => 7.63,
            'overtime_hours' => 0.0,
            'late_minutes' => 379,
            'status' => 'completed',
        ]);

        // 1 shift tidak dihadiri (durasi 8 jam)
        $shiftUnattended = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Absen',
            'start_time' => $baseDate->copy()->addDay()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->addDay()->setTime(16, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shiftUnattended->id,
            'user_id' => $this->staffA->id,
            'clock_in_time' => null,
            'clock_out_time' => null,
            'regular_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'absent',
        ]);

        $service = app(TimesheetExportService::class);
        $request = request()->merge(['month' => 10, 'year' => 2026, 'department_id' => $this->departmentA->id]);
        $data = $service->getTimesheetData($this->superadmin, $request);

        $export = new TimesheetExport($data);

        // 1. Verifikasi sheet title
        $this->assertEquals('Worksheet', $export->title());

        // 2. Render view Excel
        $html = $export->view()->render();

        // 3. Kop judul dokumen
        $this->assertStringContainsString('PT CAKRAWALA MEGA PRATAMA', $html);
        $this->assertStringContainsString('REKAPITULASI TIMESHEET', $html);

        // 4. Metadata key-value sejajar dengan ':'
        $this->assertStringContainsString('Periode Laporan', $html);
        $this->assertStringContainsString('Departemen', $html);
        $this->assertStringContainsString('Status', $html);
        $this->assertStringContainsString('Tanggal Pembuatan', $html);
        $this->assertStringContainsString(':', $html);

        // 5. 10 Kolom Header (dengan Background #eeeeee dan Border)
        $this->assertStringContainsString('background-color: #eeeeee;', $html);
        $this->assertStringContainsString('<b>No</b>', $html);
        $this->assertStringContainsString('<b>Nama Karyawan</b>', $html);
        $this->assertStringContainsString('<b>Departemen</b>', $html);
        $this->assertStringContainsString('<b>Shift Dijadwalkan</b>', $html);
        $this->assertStringContainsString('<b>Shift Dihadiri</b>', $html);
        $this->assertStringContainsString('<b>Hari Kehadiran</b>', $html);
        $this->assertStringContainsString('<b>Jam Reguler</b>', $html);
        $this->assertStringContainsString('<b>Jam Lembur</b>', $html);
        $this->assertStringContainsString('<b>Jam Tidak Dihadiri</b>', $html);
        $this->assertStringContainsString('<b>Keterlambatan</b>', $html);

        // 6. Data karyawan
        $this->assertStringContainsString('Dewi Manan', $html);
        $this->assertStringContainsString('379 Menit', $html);
        $this->assertStringContainsString('7.63', $html);
        $this->assertStringContainsString('8', $html); // Jam Tidak Dihadiri

        // 7. Baris TOTAL tersedia di footer
        $this->assertStringContainsString('<b>TOTAL</b>', $html);
    }

    /**
     * Memverifikasi nama file ekspor unik per role pada respons HTTP Content-Disposition.
     */
    public function test_export_filename_is_unique_per_role(): void
    {
        $now = Carbon::create(2026, 10, 15, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Superadmin Excel
        $resAdminExcel = $this->actingAs($this->superadmin)
            ->get(route('admin.reports.timesheet.export-excel', ['month' => 10, 'year' => 2026]));
        $resAdminExcel->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_superadmin_2026_10.xlsx', (string) $resAdminExcel->headers->get('content-disposition'));

        // Superadmin PDF
        $resAdminPdf = $this->actingAs($this->superadmin)
            ->get(route('admin.reports.timesheet.export-pdf', ['month' => 10, 'year' => 2026]));
        $resAdminPdf->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_superadmin_2026_10.pdf', (string) $resAdminPdf->headers->get('content-disposition'));

        // Manager Excel
        $resManagerExcel = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.excel', ['month' => 10, 'year' => 2026]));
        $resManagerExcel->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_manager_2026_10.xlsx', (string) $resManagerExcel->headers->get('content-disposition'));

        // Manager PDF
        $resManagerPdf = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.pdf', ['month' => 10, 'year' => 2026]));
        $resManagerPdf->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_manager_2026_10.pdf', (string) $resManagerPdf->headers->get('content-disposition'));

        // Employee Excel
        $resEmployeeExcel = $this->actingAs($this->staffA)
            ->get(route('employee.timesheet.export.excel', ['month' => 10, 'year' => 2026]));
        $resEmployeeExcel->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_employee_2026_10.xlsx', (string) $resEmployeeExcel->headers->get('content-disposition'));

        // Employee PDF
        $resEmployeePdf = $this->actingAs($this->staffA)
            ->get(route('employee.timesheet.export.pdf', ['month' => 10, 'year' => 2026]));
        $resEmployeePdf->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_employee_2026_10.pdf', (string) $resEmployeePdf->headers->get('content-disposition'));
    }

    /**
     * Memverifikasi hak akses data:
     * - Manager dibatasi hanya melihat departemennya
     * - Employee dibatasi hanya melihat dirinya sendiri
     */
    public function test_data_access_isolation_per_role(): void
    {
        // Manager A tidak boleh mengekspor departemen B (403)
        $resManagerBlocked = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.excel', ['department_id' => $this->departmentB->id]));
        $resManagerBlocked->assertStatus(403);

        // Employee A tidak boleh mengekspor user B (403)
        $resEmployeeBlocked = $this->actingAs($this->staffA)
            ->get(route('employee.timesheet.export.excel', ['user_id' => $this->staffB->id]));
        $resEmployeeBlocked->assertStatus(403);
    }
}
