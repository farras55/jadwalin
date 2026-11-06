<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TimesheetExport implements FromView, ShouldAutoSize, WithTitle
{
    public array $data;

    public function __construct(
        mixed $reports,
        mixed $company = null,
        mixed $department = null,
        ?string $periodLabel = null,
        mixed $totalMetrics = null,
        ?string $status = 'Final',
        ?string $generatedAt = null
    ) {
        if (is_array($reports) && isset($reports['reports'])) {
            $this->data = $reports;
        } else {
            $this->data = [
                'reports' => $reports,
                'company' => $company,
                'companyName' => is_object($company) ? ($company->name ?? 'PT Cakrawala Mega Pratama') : ($company ?? 'PT Cakrawala Mega Pratama'),
                'department' => $department,
                'departmentName' => is_object($department) ? ($department->name ?? 'Semua Departemen') : ($department ?? 'Semua Departemen'),
                'periodLabel' => $periodLabel,
                'totalMetrics' => $totalMetrics,
                'status' => $status ?? 'Final',
                'generatedAt' => $generatedAt ?? (now()->translatedFormat('d F Y, H:i').' WIB'),
            ];
        }
    }

    /**
     * Nama tab sheet (Worksheet).
     */
    public function title(): string
    {
        return 'Worksheet';
    }

    /**
     * Render view template Blade untuk format Excel.
     */
    public function view(): View
    {
        return view('exports.timesheet', $this->data);
    }
}
