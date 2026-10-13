<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Timesheet - {{ $periodLabel }}</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D2A3E;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header Perusahaan */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #6C5CE7;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #2D2A3E;
            margin: 0 0 4px 0;
        }

        .company-title span {
            color: #6C5CE7;
        }

        .company-meta {
            font-size: 10px;
            color: #7C7896;
            margin: 0;
            line-height: 1.3;
        }

        .doc-badge {
            text-align: right;
            vertical-align: top;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #6C5CE7;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }

        .period-pill {
            display: inline-block;
            background-color: #F5F3FF;
            border: 1px solid #E2E0F7;
            color: #4F46E5;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
        }

        /* Summary Cards / Box */
        .summary-box {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .summary-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748B;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1E293B;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #6C5CE7;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #5A4BD1;
            text-align: left;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 6px;
            border: 1px solid #E2E8F0;
            font-size: 10.5px;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #F8F9FE;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: bold; }

        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-finalized {
            background-color: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .badge-draft {
            background-color: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        /* Footer & Signatures */
        .footer-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #2D2A3E;
            display: inline-block;
            min-width: 150px;
            padding-bottom: 2px;
        }

        .signature-role {
            font-size: 9.5px;
            color: #7C7896;
            margin-top: 3px;
        }

        .print-meta {
            margin-top: 20px;
            font-size: 8.5px;
            color: #94A3B8;
            text-align: right;
            border-top: 1px dashed #CBD5E1;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    {{-- Header Perusahaan & Dokumen --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                <div class="company-title">
                    {{ $company?->name ?? 'Jadwalin Business System' }}
                </div>
                <p class="company-meta">
                    {{ $company?->address ?? 'Sistem Manajemen Roster & Presensi Karyawan B2B' }}<br>
                    Email: {{ $company?->email ?? 'admin@jadwalin.id' }} | Telp: {{ $company?->phone_number ?? '-' }}
                </p>
            </td>
            <td class="doc-badge" style="width: 40%;">
                <div class="doc-title">Rekapitulasi Timesheet</div>
                <div class="period-pill">
                    Periode: {{ $periodLabel }}
                </div>
                <div style="font-size: 9.5px; color: #7C7896; margin-top: 4px;">
                    Departemen: <strong>{{ $selectedDepartment?->name ?? 'Semua Departemen' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan Metrik --}}
    <table class="summary-box" cellpadding="0" cellspacing="4">
        <tr>
            <td style="width: 20%;">
                <div class="summary-card">
                    <div class="summary-label">Total Karyawan</div>
                    <div class="summary-value">{{ $totalMetrics->total_employees }} Orang</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <div class="summary-label">Shift Dihadiri</div>
                    <div class="summary-value" style="color: #059669;">{{ $totalMetrics->total_attendance_shifts }} Shift</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <div class="summary-label">Total Jam Reguler</div>
                    <div class="summary-value" style="color: #4F46E5;">{{ number_format($totalMetrics->total_regular_hours, 2) }} Jam</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <div class="summary-label">Total Jam Lembur</div>
                    <div class="summary-value" style="color: #D97706;">{{ number_format($totalMetrics->total_overtime_hours, 2) }} Jam</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-card">
                    <div class="summary-label">Keterlambatan</div>
                    <div class="summary-value" style="color: #E11D48;">{{ $totalMetrics->total_late_minutes }} Menit</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Utama --}}
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Nama Karyawan</th>
                <th>Departemen</th>
                <th style="width: 70px;" class="text-center">Shift Dijadwalkan</th>
                <th style="width: 65px;" class="text-center">Shift Dihadiri</th>
                <th style="width: 65px;" class="text-center">Hari Hadir</th>
                <th style="width: 75px;" class="text-right">Jam Reguler</th>
                <th style="width: 75px;" class="text-right">Jam Lembur</th>
                <th style="width: 75px;" class="text-right">Keterlambatan</th>
                <th style="width: 55px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $index => $row)
                <tr>
                    <td class="text-center font-semibold">{{ $index + 1 }}</td>
                    <td>
                        <span class="font-bold">{{ $row->employee_name }}</span>
                        @if(!empty($row->employee_email))
                            <br><span style="font-size: 8.5px; color: #7C7896;">{{ $row->employee_email }}</span>
                        @endif
                    </td>
                    <td>{{ $row->department_name }}</td>
                    <td class="text-center">{{ $row->scheduled_shifts }}</td>
                    <td class="text-center font-semibold" style="color: #059669;">{{ $row->attended_shifts }}</td>
                    <td class="text-center">{{ $row->attendance_days }} hari</td>
                    <td class="text-right font-semibold">{{ number_format($row->total_regular_hours, 2) }} Jam</td>
                    <td class="text-right font-semibold" style="{{ $row->total_overtime_hours > 0 ? 'color: #D97706;' : '' }}">
                        {{ number_format($row->total_overtime_hours, 2) }} Jam
                    </td>
                    <td class="text-right" style="{{ $row->total_late_minutes > 0 ? 'color: #E11D48; font-weight: bold;' : '' }}">
                        {{ $row->total_late_minutes }} mnt
                    </td>
                    <td class="text-center">
                        <span class="badge-status {{ $row->status === 'finalized' ? 'badge-finalized' : 'badge-draft' }}">
                            {{ $row->status === 'finalized' ? 'Final' : 'Draft' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 16px; color: #7C7896;">
                        Tidak ada data timesheet karyawan yang sesuai dengan kriteria filter pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($reports->isNotEmpty())
            <tfoot>
                <tr style="background-color: #EEF2FF; font-weight: bold;">
                    <td colspan="3" class="text-right" style="padding: 7px;">TOTAL KESELURUHAN:</td>
                    <td class="text-center">{{ $totalMetrics->total_scheduled_shifts }}</td>
                    <td class="text-center">{{ $totalMetrics->total_attendance_shifts }}</td>
                    <td class="text-center">-</td>
                    <td class="text-right">{{ number_format($totalMetrics->total_regular_hours, 2) }} Jam</td>
                    <td class="text-right">{{ number_format($totalMetrics->total_overtime_hours, 2) }} Jam</td>
                    <td class="text-right">{{ $totalMetrics->total_late_minutes }} mnt</td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Tanda Tangan / Persetujuan --}}
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="signature-box">
                <p class="company-meta">Dibuat Oleh:</p>
                <div class="signature-space"></div>
                <div class="signature-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
                <div class="signature-role">Admin HR / Timesheet Officer</div>
            </td>
            <td style="width: 40%;"></td>
            <td class="signature-box">
                <p class="company-meta">Disetujui Oleh:</p>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
                <div class="signature-role">Operations / HR Manager</div>
            </td>
        </tr>
    </table>

    <div class="print-meta">
        Dicetak otomatis melalui Sistem Jadwalin pada {{ now()->translatedFormat('d F Y H:i:s') }} WIB | Halaman ini sah sebagai dokumen verifikasi presensi internal.
    </div>

</body>
</html>
