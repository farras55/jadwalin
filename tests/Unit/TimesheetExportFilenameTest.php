<?php

namespace Tests\Unit;

use App\Services\TimesheetExportService;
use Tests\TestCase;

class TimesheetExportFilenameTest extends TestCase
{
    /**
     * Memastikan buildExportFilename menghasilkan nama file yang benar untuk tiap role.
     */
    public function test_build_export_filename_for_various_roles(): void
    {
        // 1. Role superadmin
        $this->assertEquals(
            'rekap_timesheet_superadmin_2026_10.xlsx',
            TimesheetExportService::buildExportFilename('superadmin', 2026, 10, 'xlsx')
        );

        // 2. Role owner
        $this->assertEquals(
            'rekap_timesheet_owner_2026_10.xlsx',
            TimesheetExportService::buildExportFilename('owner', 2026, 10, 'xlsx')
        );

        // 3. Role manager
        $this->assertEquals(
            'rekap_timesheet_manager_2026_10.xlsx',
            TimesheetExportService::buildExportFilename('manager', 2026, 10, 'xlsx')
        );

        // 4. Role employee
        $this->assertEquals(
            'rekap_timesheet_employee_2026_10.xlsx',
            TimesheetExportService::buildExportFilename('employee', 2026, 10, 'xlsx')
        );

        // 5. Ekstensi PDF
        $this->assertEquals(
            'rekap_timesheet_superadmin_2026_10.pdf',
            TimesheetExportService::buildExportFilename('superadmin', 2026, 10, 'pdf')
        );

        // 6. Zero padding untuk bulan (1 -> 01)
        $this->assertEquals(
            'rekap_timesheet_manager_2026_01.xlsx',
            TimesheetExportService::buildExportFilename('manager', 2026, 1, 'xlsx')
        );

        // 7. Sanitasi role (spasi atau huruf besar)
        $this->assertEquals(
            'rekap_timesheet_super_admin_2026_05.xlsx',
            TimesheetExportService::buildExportFilename('Super Admin', 2026, 5, '.xlsx')
        );

        // 8. Global helper function buildExportFilename
        $this->assertEquals(
            'rekap_timesheet_employee_2026_12.pdf',
            buildExportFilename('employee', 2026, 12, '.pdf')
        );
    }
}
