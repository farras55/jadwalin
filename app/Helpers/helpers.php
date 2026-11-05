<?php

use App\Services\TimesheetExportService;

if (! function_exists('buildExportFilename')) {
    /**
     * Global helper function untuk buildExportFilename.
     * Pola: rekap_timesheet_{role}_{tahun}_{bulan}.{ext}
     */
    function buildExportFilename(mixed $roleOrUser, int|string $year, int|string $month, string $ext): string
    {
        return TimesheetExportService::buildExportFilename($roleOrUser, $year, $month, $ext);
    }
}
