<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

if (interface_exists(FromCollection::class)) {
    class TimesheetExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
    {
        protected Collection $reports;

        protected mixed $company;

        protected int $month;

        protected int $year;

        public function __construct(Collection $reports, mixed $company, int $month, int $year)
        {
            $this->reports = $reports;
            $this->company = $company;
            $this->month = $month;
            $this->year = $year;
        }

        public function collection(): Collection
        {
            return $this->reports->map(function ($item, $index) {
                return [
                    'no' => $index + 1,
                    'name' => $item->employee_name,
                    'department' => $item->department_name,
                    'scheduled' => $item->scheduled_shifts,
                    'attended' => $item->attended_shifts,
                    'attendance_days' => $item->attendance_days,
                    'regular_hours' => number_format($item->total_regular_hours, 2),
                    'overtime_hours' => number_format($item->total_overtime_hours, 2),
                    'late_minutes' => $item->total_late_minutes,
                    'status' => ucfirst($item->status),
                ];
            });
        }

        public function headings(): array
        {
            return [
                'No',
                'Nama Karyawan',
                'Departemen',
                'Shift Dijadwalkan',
                'Shift Dihadiri',
                'Hari Kehadiran',
                'Jam Reguler',
                'Jam Lembur',
                'Keterlambatan (Menit)',
                'Status',
            ];
        }

        public function title(): string
        {
            return 'Timesheet '.$this->month.'-'.$this->year;
        }
    }
} else {
    class TimesheetExport
    {
        public Collection $reports;

        public mixed $company;

        public int $month;

        public int $year;

        public function __construct(Collection $reports, mixed $company, int $month, int $year)
        {
            $this->reports = $reports;
            $this->company = $company;
            $this->month = $month;
            $this->year = $year;
        }
    }
}
