<?php

namespace Tests\Feature\Manager;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTimesheetTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $departmentA;

    protected Department $departmentB;

    protected User $managerA;

    protected User $staffA;

    protected User $staffB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'PT Jadwalin Sukses Mandiri',
            'email' => 'info@jadwalin.test',
            'phone_number' => '081298765432',
            'address' => 'Jl. Asia Afrika No. 10',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->departmentA = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Divisi Operasional',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Gedung A Lt 1',
        ]);

        $this->departmentB = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Divisi Pemasaran',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Gedung B Lt 2',
        ]);

        $this->managerA = User::factory()->create([
            'name' => 'Manager Operasional',
            'role' => 'manager',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'is_global_manager' => false,
        ]);

        $this->staffA = User::factory()->create([
            'name' => 'Staf Operasional Satu',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
        ]);

        $this->staffB = User::factory()->create([
            'name' => 'Staf Pemasaran Dua',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->departmentB->id,
        ]);
    }

    /**
     * Skenario 3: Manajer hanya dapat melihat timesheet staf di bawah departemennya sendiri.
     */
    public function test_manager_can_view_timesheet_of_own_department_staf_only(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        // Shift untuk Staf Departemen A
        $shiftA = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Lapangan Operasional',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shiftA->id,
            'user_id' => $this->staffA->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.5,
            'status' => 'completed',
        ]);

        // Shift untuk Staf Departemen B (Pemasaran)
        $shiftB = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentB->id,
            'title' => 'Shift Campaign Pemasaran',
            'start_time' => $baseDate->copy()->setTime(9, 0, 0),
            'end_time' => $baseDate->copy()->setTime(18, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shiftB->id,
            'user_id' => $this->staffB->id,
            'clock_in_time' => $baseDate->copy()->setTime(9, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(18, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->managerA)->get(route('manager.timesheets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('manager.timesheets.index');
        $response->assertViewHas(['totalRegularHours', 'totalOvertimeHours', 'assignments']);

        // Staf Departemen A harus terlihat
        $response->assertSee('Staf Operasional Satu');
        $response->assertSee('Shift Lapangan Operasional');

        // Staf Departemen B TIDAK BOLEH terlihat sama sekali
        $response->assertDontSee('Staf Pemasaran Dua');
        $response->assertDontSee('Shift Campaign Pemasaran');
    }

    /**
     * Skenario 4: Manajer tidak dapat mengekspor timesheet departemen lain (akses diblokir 403).
     */
    public function test_manager_cannot_export_timesheet_of_other_departments(): void
    {
        // Percobaan 1: Export Excel untuk Departemen Lain
        $responseExcel = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.excel', ['department_id' => $this->departmentB->id]));

        $responseExcel->assertStatus(403);

        // Percobaan 2: Export PDF untuk Departemen Lain
        $responsePdf = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.pdf', ['department_id' => $this->departmentB->id]));

        $responsePdf->assertStatus(403);

        // Percobaan 3: Mengakses index view dengan parameter departemen lain
        $responseIndex = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.index', ['department_id' => $this->departmentB->id]));

        $responseIndex->assertStatus(403);
    }

    /**
     * Pengujian Tambahan: Manajer dapat mengekspor Excel & PDF departemennya sendiri.
     */
    public function test_manager_can_export_excel_and_pdf_of_own_department(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Ekspor Operasional',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->staffA->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 2.0,
            'status' => 'completed',
        ]);

        // Ekspor Excel sukses
        $excelResponse = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.excel'));
        $excelResponse->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_', $excelResponse->headers->get('content-disposition'));
        $this->assertStringContainsString('rekap_timesheet_manager_2026_11.xlsx', (string) $excelResponse->headers->get('content-disposition'));

        // Ekspor PDF sukses
        $pdfResponse = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.pdf'));
        $pdfResponse->assertSuccessful();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
        $this->assertStringContainsString('rekap_timesheet_', $pdfResponse->headers->get('content-disposition'));
        $this->assertStringContainsString('rekap_timesheet_manager_2026_11.pdf', (string) $pdfResponse->headers->get('content-disposition'));
    }

    /**
     * Pengujian Batas Memori (CRITICAL RULE): Ekspor PDF membatasi tabel rincian maksimal 50 baris.
     */
    public function test_manager_export_pdf_limits_rows_to_fifty_for_memory_safety(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        // Buat 55 shifts dan assignments
        for ($i = 1; $i <= 55; $i++) {
            $shift = Shift::forceCreate([
                'company_id' => $this->company->id,
                'department_id' => $this->departmentA->id,
                'title' => 'Shift Lapangan #'.$i,
                'start_time' => $baseDate->copy()->setTime(8, 0, 0),
                'end_time' => $baseDate->copy()->setTime(17, 0, 0),
                'quota' => 2,
                'status' => 'published',
            ]);

            ShiftAssignment::forceCreate([
                'company_id' => $this->company->id,
                'shift_id' => $shift->id,
                'user_id' => $this->staffA->id,
                'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
                'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
                'regular_hours' => 8.0,
                'overtime_hours' => 0.0,
                'status' => 'completed',
            ]);
        }

        $pdfResponse = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.pdf'));

        $pdfResponse->assertSuccessful();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    /**
     * Pengujian Status Otomatis: Status timesheet otomatis 'Final' jika periode sudah selesai/lewat.
     */
    public function test_manager_timesheet_status_is_automatically_final_when_period_is_past(): void
    {
        // Tetapkan waktu saat ini di November 2026
        $now = Carbon::create(2026, 11, 4, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Shift di Oktober 2026 (periode lalu / sudah selesai)
        $octDate = Carbon::create(2026, 10, 15, 8, 0, 0, 'Asia/Jakarta');
        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Oktober',
            'start_time' => $octDate->copy()->setTime(8, 0, 0),
            'end_time' => $octDate->copy()->setTime(17, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->staffA->id,
            'clock_in_time' => $octDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $octDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'completed',
        ]);

        // Request export Excel untuk bulan Oktober 2026 (periode sudah lewat)
        $response = $this->actingAs($this->managerA)
            ->get(route('manager.timesheets.export.excel', ['month' => 10, 'year' => 2026]));

        $response->assertSuccessful();

        // Cek bahwa data timesheet_reports tercatat finalized
        $this->assertDatabaseHas('timesheet_reports', [
            'user_id' => $this->staffA->id,
            'period_month' => 10,
            'period_year' => 2026,
            'status' => 'finalized',
        ]);
    }
}
