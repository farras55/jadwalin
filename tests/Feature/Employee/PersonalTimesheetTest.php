<?php

namespace Tests\Feature\Employee;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalTimesheetTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $department;

    protected User $employee1;

    protected User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'PT Jadwalin Utama',
            'email' => 'corporate@jadwalin.test',
            'phone_number' => '081234567890',
            'address' => 'Jl. Sudirman No. 45',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->department = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Divisi Pelayanan',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 100,
            'address' => 'Gedung Pelayanan Lt 1',
        ]);

        $this->employee1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);

        $this->employee2 = User::factory()->create([
            'name' => 'Siti Rahma',
            'role' => 'employee',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
        ]);
    }

    /**
     * Skenario 1: Karyawan dapat melihat riwayat timesheet miliknya sendiri beserta agregasi jam.
     */
    public function test_employee_can_view_own_timesheet_history(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Reguler Pagi Budi',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.0,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->employee1)->get(route('employee.timesheet'));

        $response->assertStatus(200);
        $response->assertViewIs('employee.timesheet.index');
        $response->assertViewHas(['totalRegularHours', 'totalOvertimeHours', 'attendanceRate', 'assignments']);

        // Pastikan angka agregasi level database sesuai
        $this->assertEquals(8.0, $response->viewData('totalRegularHours'));
        $this->assertEquals(1.0, $response->viewData('totalOvertimeHours'));
        $this->assertEquals(100.0, $response->viewData('attendanceRate'));

        $response->assertSee('Shift Reguler Pagi Budi');
        $response->assertSee('8.00');
        $response->assertSee('1.00');
    }

    /**
     * Skenario 2: Karyawan tidak dapat melihat riwayat timesheet karyawan lain (isolasi data).
     */
    public function test_employee_cannot_see_other_employee_timesheet(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        // Shift khusus milik Employee 2
        $shiftSiti = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Rahasia Siti Rahma',
            'start_time' => $baseDate->copy()->setTime(13, 0, 0),
            'end_time' => $baseDate->copy()->setTime(21, 0, 0),
            'quota' => 1,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shiftSiti->id,
            'user_id' => $this->employee2->id,
            'clock_in_time' => $baseDate->copy()->setTime(13, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(21, 0, 0),
            'regular_hours' => 7.0,
            'overtime_hours' => 2.0,
            'status' => 'completed',
        ]);

        // Employee 1 membuka timesheet miliknya
        $response = $this->actingAs($this->employee1)->get(route('employee.timesheet'));

        $response->assertStatus(200);
        // Data milik Employee 2 tidak boleh muncul
        $response->assertDontSee('Shift Rahasia Siti Rahma');

        // Jika Employee 1 mencoba mengakses timesheet Employee 2 dengan parameter ID lain
        $blockedResponse1 = $this->actingAs($this->employee1)->get(route('employee.timesheet', ['employee_id' => $this->employee2->id]));
        $blockedResponse1->assertStatus(403);

        $blockedResponse2 = $this->actingAs($this->employee1)->get(route('employee.timesheet', ['user_id' => $this->employee2->id]));
        $blockedResponse2->assertStatus(403);
    }

    /**
     * Skenario 3: Karyawan dapat mengekspor timesheet miliknya ke format Excel dan PDF.
     */
    public function test_employee_can_export_own_timesheet_to_excel_and_pdf(): void
    {
        $baseDate = Carbon::create(2026, 11, 3, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($baseDate);

        $shift = Shift::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Shift Ekspor Karyawan',
            'start_time' => $baseDate->copy()->setTime(8, 0, 0),
            'end_time' => $baseDate->copy()->setTime(17, 0, 0),
            'quota' => 2,
            'status' => 'published',
        ]);

        ShiftAssignment::forceCreate([
            'company_id' => $this->company->id,
            'shift_id' => $shift->id,
            'user_id' => $this->employee1->id,
            'clock_in_time' => $baseDate->copy()->setTime(8, 0, 0),
            'clock_out_time' => $baseDate->copy()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.0,
            'status' => 'completed',
        ]);

        // Ekspor Excel
        $excelResponse = $this->actingAs($this->employee1)->get(route('employee.timesheet.export.excel'));
        $excelResponse->assertSuccessful();
        $this->assertStringContainsString('rekap_timesheet_employee_2026_11.xlsx', (string) $excelResponse->headers->get('content-disposition'));

        // Ekspor PDF
        $pdfResponse = $this->actingAs($this->employee1)->get(route('employee.timesheet.export.pdf'));
        $pdfResponse->assertSuccessful();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
        $this->assertStringContainsString('rekap_timesheet_employee_2026_11.pdf', (string) $pdfResponse->headers->get('content-disposition'));
    }

    /**
     * Skenario 4: Karyawan dilarang mengekspor timesheet milik orang lain (403 Forbidden).
     */
    public function test_employee_cannot_export_other_employee_timesheet(): void
    {
        $responseExcel = $this->actingAs($this->employee1)->get(route('employee.timesheet.export.excel', ['employee_id' => $this->employee2->id]));
        $responseExcel->assertStatus(403);

        $responsePdf = $this->actingAs($this->employee1)->get(route('employee.timesheet.export.pdf', ['user_id' => $this->employee2->id]));
        $responsePdf->assertStatus(403);
    }
}
