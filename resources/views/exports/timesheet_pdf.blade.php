<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Timesheet - {{ $companyName ?? $company?->name ?? 'PT Cakrawala Mega Pratama' }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: #222222;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #000000;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 12px;
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
            font-size: 9.5px;
            text-align: left;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .meta-label {
            width: 140px;
            color: #333333;
        }

        .meta-sep {
            width: 10px;
            text-align: center;
        }

        .meta-value {
            color: #000000;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-family: 'DejaVu Sans', sans-serif;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #777777;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: middle;
            font-family: 'DejaVu Sans', sans-serif;
        }

        table.data-table th {
            background-color: #eeeeee;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        table.data-table tfoot td {
            background-color: #f7f7f7;
            font-weight: bold;
            color: #000000;
            border: 1px solid #777777;
        }
    </style>
</head>
<body>

    {{-- Kop & Header Laporan (Center & Rapi) --}}
    <div class="header-box">
        <div class="company-title">{{ strtoupper($companyName ?? $company?->name ?? 'PT Cakrawala Mega Pratama') }}</div>
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
                <td class="meta-value">{{ $departmentName ?? 'Semua Departemen' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Status</td>
                <td class="meta-sep">:</td>
                <td class="meta-value"><strong>{{ $status ?? 'Final' }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Tanggal Pembuatan</td>
                <td class="meta-sep">:</td>
                <td class="meta-value">{{ $generatedAt ?? (now()->translatedFormat('d F Y, H:i') . ' WIB') }}</td>
            </tr>
        </table>
    </div>

    {{-- Tabel 10 Kolom Berurutan dengan Border Penuh --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left" style="width: 155px;">Nama Karyawan</th>
                <th class="text-left" style="width: 100px;">Departemen</th>
                <th style="width: 65px;">Shift Dijadwalkan</th>
                <th style="width: 55px;">Shift Dihadiri</th>
                <th style="width: 55px;">Hari Kehadiran</th>
                <th class="text-right" style="width: 65px;">Jam Reguler</th>
                <th class="text-right" style="width: 60px;">Jam Lembur</th>
                <th class="text-right" style="width: 70px;">Jam Tidak Dihadiri</th>
                <th class="text-right" style="width: 70px;">Keterlambatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $index => $row)
                @php
                    $scheduled = (int) ($row->scheduled_shifts ?? 0);
                    $attended = (int) ($row->attended_shifts ?? 0);
                    $days = (int) ($row->attendance_days ?? 0);
                    $regular = (float) ($row->total_regular_hours ?? 0);
                    $overtime = (float) ($row->total_overtime_hours ?? 0);
                    $unattended = (float) ($row->total_unattended_hours ?? max(0, ($scheduled - $attended) * 8.0));
                    $late = (int) ($row->total_late_minutes ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row->employee_name ?? '-' }}</td>
                    <td class="text-left">{{ $row->department_name ?? '-' }}</td>
                    <td class="text-center">{{ $scheduled }}</td>
                    <td class="text-center">{{ $attended }}</td>
                    <td class="text-center">{{ $days }}</td>
                    <td class="text-right">{{ number_format($regular, 2) }}</td>
                    <td class="text-right">{{ number_format($overtime, 2) }}</td>
                    <td class="text-right">{{ number_format($unattended, 2) }}</td>
                    <td class="text-right">{{ $late }} Menit</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 12px; color: #777777;">
                        Tidak ada data rekapitulasi timesheet untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (!empty($reports) && count($reports) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="text-left"><b>TOTAL</b></td>
                    <td class="text-center">{{ (int) ($totalMetrics->total_scheduled_shifts ?? 0) }}</td>
                    <td class="text-center">{{ (int) ($totalMetrics->total_attendance_shifts ?? 0) }}</td>
                    <td class="text-center">{{ (int) ($totalMetrics->total_attendance_days ?? 0) }}</td>
                    <td class="text-right">{{ number_format((float) ($totalMetrics->total_regular_hours ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($totalMetrics->total_overtime_hours ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($totalMetrics->total_unattended_hours ?? 0), 2) }}</td>
                    <td class="text-right">{{ (int) ($totalMetrics->total_late_minutes ?? 0) }} Menit</td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
