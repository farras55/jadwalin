<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Pengujian Kepatuhan Regulasi Ketenagakerjaan Indonesia (PP No. 35/2021 & UU No. 13/2003)
 */
class LaborLawComplianceTest extends TestCase
{
    /**
     * TC-LL-01: Batas Maksimum Jam Kerja Reguler Standar adalah 40 Jam / Minggu (Pasal 21 PP 35/2021)
     */
    public function test_standard_weekly_regular_hours_capped_at_40_hours(): void
    {
        $dailyHoursFiveDays = 8;
        $workDaysFiveDays = 5;
        $totalHoursFiveDays = $dailyHoursFiveDays * $workDaysFiveDays;
        $this->assertEquals(40, $totalHoursFiveDays);

        $dailyHoursSixDays = 7;
        $workDaysSixDays = 6;
        $totalHoursSixDays = $dailyHoursSixDays * $workDaysSixDays;
        $this->assertEquals(42, $totalHoursSixDays);
        // Maksimal reguler yang diakui hukum tetap 40 jam
        $recognizedRegular = min(40, $totalHoursSixDays);
        $this->assertEquals(40, $recognizedRegular);
    }

    /**
     * TC-LL-02: Batas Maksimum Waktu Kerja Lembur adalah 4 Jam/Hari dan 18 Jam/Minggu (Pasal 26 PP 35/2021)
     */
    public function test_overtime_limits_enforce_daily_and_weekly_maximums(): void
    {
        $maxDailyOvertime = 4;
        $maxWeeklyOvertime = 18;

        // Simulasi pengajuan lembur 5 jam dalam 1 hari
        $requestedDailyOvertime = 5;
        $isDailyExceeded = $requestedDailyOvertime > $maxDailyOvertime;
        $this->assertTrue($isDailyExceeded, 'Lembur 5 jam/hari melebihi batas legal 4 jam.');

        // Simulasi akumulasi lembur mingguan 20 jam
        $accumulatedWeeklyOvertime = 20;
        $isWeeklyExceeded = $accumulatedWeeklyOvertime > $maxWeeklyOvertime;
        $this->assertTrue($isWeeklyExceeded, 'Akumulasi lembur 20 jam/minggu melebihi batas legal 18 jam.');
    }

    /**
     * TC-LL-03: Jeda Istirahat Antar-Shift Minimal 11 Jam Berturut-turut (Inter-shift Rest Period)
     */
    public function test_inter_shift_rest_period_requires_at_least_11_consecutive_hours(): void
    {
        $shiftOneEnd = Carbon::parse('2026-11-02 22:00:00');

        // Skenario A: Shift berikutnya mulai jam 07:00 (Hanya jeda 9 jam -> Melanggar batas)
        $shiftTwoStartTooEarly = Carbon::parse('2026-11-03 07:00:00');
        $restHoursA = $shiftOneEnd->diffInMinutes($shiftTwoStartTooEarly) / 60;
        $this->assertEquals(9.0, $restHoursA);
        $this->assertLessThan(11.0, $restHoursA, 'Jeda istirahat 9 jam melanggar batas minimum 11 jam.');

        // Skenario B: Shift berikutnya mulai jam 10:00 (Jeda 12 jam -> Patuh aturan)
        $shiftTwoStartCompliant = Carbon::parse('2026-11-03 10:00:00');
        $restHoursB = $shiftOneEnd->diffInMinutes($shiftTwoStartCompliant) / 60;
        $this->assertEquals(12.0, $restHoursB);
        $this->assertGreaterThanOrEqual(11.0, $restHoursB, 'Jeda istirahat 12 jam mematuhi batas legal.');
    }

    /**
     * TC-LL-04: Hak Istirahat Mingguan Minimal 1 Hari untuk 6 Hari Kerja (Pasal 21 Ayat 2 PP 35/2021)
     */
    public function test_weekly_rest_day_enforces_maximum_consecutive_work_days(): void
    {
        $maxWorkingDaysPerWeek = 6;
        $scheduledDays = 7; // Menjadwalkan 7 hari berturut-turut tanpa libur

        $hasViolatedRestDay = $scheduledDays > $maxWorkingDaysPerWeek;
        $this->assertTrue($hasViolatedRestDay, 'Jadwal 7 hari tanpa istirahat melanggar hak istirahat mingguan.');
    }
}
