<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Timesheet - {{ $company->name ?? 'PT Cakrawala Mega Pratama' }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Bagian Header Dokumen */
        .header-box {
            text-align: center;
            margin-bottom: 16px;
        }

        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #000000;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #222222;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-table {
            width: auto;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .meta-table td {
            border: none;
            padding: 2px 6px;
            font-size: 10px;
            text-align: left;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .meta-label {
            width: 140px;
            color: #444444;
        }

        .meta-sep {
            width: 10px;
            text-align: center;
        }

        .meta-value {
            color: #000000;
        }

        /* Struktur Tabel Klasik */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #999999;
            padding: 6px 5px;
            font-size: 9.5px;
            vertical-align: middle;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .data-table th {
            background-color: #eeeeee;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }

        .data-table td {
            color: #333333;
            font-weight: normal;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Footer Baris Total */
        .data-table tfoot td {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #000000;
        }
    </style>
</head>
<body>

    {{-- Bagian Header Dokumen (Informasi Laporan) --}}
    <div class="header-box">
        <div class="company-title">{{ $company->name ?? 'PT Cakrawala Mega Pratama' }}</div>
        <div class="report-title">REKAPITULASI TIMESHEET</div>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Periode Laporan</td>
                <td class="meta-sep">:</td>
                <td class="meta-value">{{ $periodLabel ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Departemen</td>
                <td class="meta-sep">:</td>
                <td class="meta-value">{{ $department->name ?? 'Semua Departemen' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Status</td>
                <td class="meta-sep">:</td>
                <td class="meta-value"><strong>{{ ($isPeriodPast ?? false) || (($reports->first()->status ?? '') === 'finalized') ? 'Final' : 'Draft' }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Tanggal Pembuatan</td>
                <td class="meta-sep">:</td>
                <td class="meta-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    {{-- Struktur Tabel (Header & Kolom) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th class="text-left" style="width: 140px;">Nama Karyawan</th>
                <th class="text-left" style="width: 90px;">Departemen</th>
                <th style="width: 65px;">Shift Dijadwalkan</th>
                <th style="width: 55px;">Shift Dihadiri</th>
                <th style="width: 55px;">Hari Kehadiran</th>
                <th class="text-right" style="width: 65px;">Jam Reguler</th>
                <th class="text-right" style="width: 65px;">Jam Lembur</th>
                <th class="text-right" style="width: 70px;">Jam Tidak Dihadiri</th>
                <th class="text-right" style="width: 65px;">Keterlambatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $index => $row)
                @php
                    $unattendedHours = (float) ($row->total_unattended_hours ?? max(0, ($row->scheduled_shifts - $row->attended_shifts) * 8.0));
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row->employee_name }}</td>
                    <td class="text-left">{{ $row->department_name }}</td>
                    <td class="text-center">{{ $row->scheduled_shifts }}</td>
                    <td class="text-center">{{ $row->attended_shifts }}</td>
                    <td class="text-center">{{ $row->attendance_days }}</td>
                    <td class="text-right">{{ number_format($row->total_regular_hours, 2) }}</td>
                    <td class="text-right">{{ number_format($row->total_overtime_hours, 2) }}</td>
                    <td class="text-right">{{ number_format($unattendedHours, 2) }}</td>
                    <td class="text-right">{{ $row->total_late_minutes }} Menit</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 12px; color: #777777;">
                        Tidak ada data rekapitulasi timesheet untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($reports->isNotEmpty())
            <tfoot>
                @php
                    $totalUnattended = (float) ($totalMetrics->total_unattended_hours ?? $reports->sum(fn ($r) => $r->total_unattended_hours ?? max(0, ($r->scheduled_shifts - $r->attended_shifts) * 8.0)));
                @endphp
                <tr>
                    <td colspan="3" class="text-right">TOTAL:</td>
                    <td class="text-center">{{ $totalMetrics->total_scheduled_shifts }}</td>
                    <td class="text-center">{{ $totalMetrics->total_attendance_shifts }}</td>
                    <td class="text-center">-</td>
                    <td class="text-right">{{ number_format($totalMetrics->total_regular_hours, 2) }}</td>
                    <td class="text-right">{{ number_format($totalMetrics->total_overtime_hours, 2) }}</td>
                    <td class="text-right">{{ number_format($totalUnattended, 2) }}</td>
                    <td class="text-right">{{ $totalMetrics->total_late_minutes }} Menit</td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
