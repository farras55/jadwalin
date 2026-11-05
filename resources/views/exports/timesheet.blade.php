<table>
    {{-- Baris 1 & 2: Kop Judul Dokumen (Center & Tebal) --}}
    <tr>
        <td colspan="10" align="center" style="text-align: center; font-weight: bold; font-size: 12pt;">
            <b>{{ strtoupper($companyName ?? $company?->name ?? 'PT CAKRAWALA MEGA PRATAMA') }}</b>
        </td>
    </tr>
    <tr>
        <td colspan="10" align="center" style="text-align: center; font-weight: bold; font-size: 11pt;">
            <b>REKAPITULASI TIMESHEET</b>
        </td>
    </tr>

    {{-- Baris 3: Pemisah Kosong --}}
    <tr>
        <td colspan="10"></td>
    </tr>

    {{-- Baris 4-7: Informasi Metadata (Key-Value Sejajar) --}}
    <tr>
        <td style="font-weight: normal;">Periode Laporan</td>
        <td align="center" style="text-align: center;">:</td>
        <td colspan="8">{{ $periodLabel ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: normal;">Departemen</td>
        <td align="center" style="text-align: center;">:</td>
        <td colspan="8">{{ $departmentName ?? 'Semua Departemen' }}</td>
    </tr>
    <tr>
        <td style="font-weight: normal;">Status</td>
        <td align="center" style="text-align: center;">:</td>
        <td colspan="8">{{ $status ?? 'Final' }}</td>
    </tr>
    <tr>
        <td style="font-weight: normal;">Tanggal Pembuatan</td>
        <td align="center" style="text-align: center;">:</td>
        <td colspan="8">{{ $generatedAt ?? (now()->translatedFormat('d F Y, H:i') . ' WIB') }}</td>
    </tr>

    {{-- Baris 8: Pemisah Kosong --}}
    <tr>
        <td colspan="10"></td>
    </tr>

    {{-- Baris 9: Header Tabel (10 Kolom Berurutan dengan Background Abu-abu #eeeeee dan Border) --}}
    <thead>
        <tr>
            <th align="center" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;"><b>No</b></th>
            <th align="left" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: left; vertical-align: middle;"><b>Nama Karyawan</b></th>
            <th align="left" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: left; vertical-align: middle;"><b>Departemen</b></th>
            <th align="center" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;"><b>Shift Dijadwalkan</b></th>
            <th align="center" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;"><b>Shift Dihadiri</b></th>
            <th align="center" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;"><b>Hari Kehadiran</b></th>
            <th align="right" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: right; vertical-align: middle;"><b>Jam Reguler</b></th>
            <th align="right" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: right; vertical-align: middle;"><b>Jam Lembur</b></th>
            <th align="right" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: right; vertical-align: middle;"><b>Jam Tidak Dihadiri</b></th>
            <th align="right" style="background-color: #eeeeee; font-weight: bold; border: 1px solid #000000; text-align: right; vertical-align: middle;"><b>Keterlambatan</b></th>
        </tr>
    </thead>

    {{-- Baris Data & Total (Footer Tabel) --}}
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
                <td align="center" style="border: 1px solid #000000; text-align: center;">{{ $index + 1 }}</td>
                <td align="left" style="border: 1px solid #000000; text-align: left;">{{ $row->employee_name ?? '-' }}</td>
                <td align="left" style="border: 1px solid #000000; text-align: left;">{{ $row->department_name ?? '-' }}</td>
                <td align="center" style="border: 1px solid #000000; text-align: center;">{{ $scheduled }}</td>
                <td align="center" style="border: 1px solid #000000; text-align: center;">{{ $attended }}</td>
                <td align="center" style="border: 1px solid #000000; text-align: center;">{{ $days }}</td>
                <td align="right" style="border: 1px solid #000000; text-align: right;">{{ $regular > 0 ? round($regular, 2) : 0 }}</td>
                <td align="right" style="border: 1px solid #000000; text-align: right;">{{ $overtime > 0 ? round($overtime, 2) : 0 }}</td>
                <td align="right" style="border: 1px solid #000000; text-align: right;">{{ $unattended > 0 ? (floor($unattended) == $unattended ? (int) $unattended : round($unattended, 2)) : 0 }}</td>
                <td align="right" style="border: 1px solid #000000; text-align: right;">{{ $late }} Menit</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" align="center" style="border: 1px solid #000000; text-align: center;">Tidak ada data rekapitulasi timesheet untuk periode ini.</td>
            </tr>
        @endforelse

        {{-- Baris TOTAL --}}
        @if (!empty($reports) && count($reports) > 0)
            <tr>
                <td colspan="3" align="center" style="border: 1px solid #000000; font-weight: bold; text-align: center;"><b>TOTAL</b></td>
                <td align="center" style="border: 1px solid #000000; font-weight: bold; text-align: center;"><b>{{ (int) ($totalMetrics->total_scheduled_shifts ?? 0) }}</b></td>
                <td align="center" style="border: 1px solid #000000; font-weight: bold; text-align: center;"><b>{{ (int) ($totalMetrics->total_attendance_shifts ?? 0) }}</b></td>
                <td align="center" style="border: 1px solid #000000; font-weight: bold; text-align: center;"><b>{{ (int) ($totalMetrics->total_attendance_days ?? 0) }}</b></td>
                <td align="right" style="border: 1px solid #000000; font-weight: bold; text-align: right;"><b>{{ (float) ($totalMetrics->total_regular_hours ?? 0) > 0 ? round((float) $totalMetrics->total_regular_hours, 2) : 0 }}</b></td>
                <td align="right" style="border: 1px solid #000000; font-weight: bold; text-align: right;"><b>{{ (float) ($totalMetrics->total_overtime_hours ?? 0) > 0 ? round((float) $totalMetrics->total_overtime_hours, 2) : 0 }}</b></td>
                <td align="right" style="border: 1px solid #000000; font-weight: bold; text-align: right;"><b>{{ (float) ($totalMetrics->total_unattended_hours ?? 0) > 0 ? (floor($totalMetrics->total_unattended_hours) == $totalMetrics->total_unattended_hours ? (int) $totalMetrics->total_unattended_hours : round((float) $totalMetrics->total_unattended_hours, 2)) : 0 }}</b></td>
                <td align="right" style="border: 1px solid #000000; font-weight: bold; text-align: right;"><b>{{ (int) ($totalMetrics->total_late_minutes ?? 0) }} Menit</b></td>
            </tr>
        @endif
    </tbody>
</table>
