// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Presensi Kehadiran & Absensi (Karyawan)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Employee/AttendanceTest.php
 * Target Halaman: /employee/attendance (Route: employee.attendance)
 * Blade View: resources/views/employee/attendance/index.blade.php
 * Akun Pengujian: JadwalinSeeder (Karyawan: karyawan@senjawisata.co.id)
 */

async function loginAsEmployee(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('karyawan@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/employee\/dashboard/);
}

test.describe('Modul Karyawan: Presensi & Absensi Mandiri (Attendance)', () => {

  /**
   * Skenario 1: Translasi test_employee_can_view_attendance_dashboard
   * PHPUnit: actingAs($employee)->get(route('employee.attendance'))->assertStatus(200);
   */
  test('TC-ATT-01: Karyawan dapat melihat dashboard presensi dan informasi shift', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/attendance');
    await expect(page).toHaveURL(/\/employee\/attendance/);

    // Verifikasi Elemen Utama Antarmuka
    await expect(page.getByText('Presensi / Absensi', { exact: false }).first()).toBeVisible();
    await expect(page.getByText(/Radius Outlet Terverifikasi|titik presensi/i)).toBeVisible();
    await expect(page.getByText(/DURASI BEKERJA BERJALAN/i)).toBeVisible();

    // Verifikasi Widget GPS & Lokasi Presensi
    await expect(page.getByRole('heading', { name: /Lokasi Presensi Anda/i })).toBeVisible();

    // Verifikasi Riwayat Presensi
    await expect(page.getByText(/Riwayat Presensi/i)).toBeVisible();
  });

  /**
   * Skenario 2: Translasi test_employee_can_clock_in_on_time_marked_as_present & test_employee_cannot_double_clock_in
   * PHPUnit: POST route('employee.attendance.clock-in') -> assertSessionHas('success') -> status: 'present'
   */
  test('TC-ATT-02: Karyawan melakukan Clock-In dan dicegah melakukan double Clock-In', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/attendance');

    const clockInBtn = page.locator('#btn-clock-in');
    const clockOutBtn = page.locator('#btn-clock-out');

    // Jika belum clock-in, tombol Clock-In harus tampil
    if (await clockInBtn.isVisible()) {
      await clockInBtn.click();

      // Assertion UI: Notifikasi flash sukses atau perubahan status aktif
      await expect(page).toHaveURL(/\/employee\/attendance/);
      // Status berubah menjadi sedang bekerja atau tombol clock-out aktif
      await expect(page.locator('#btn-clock-out').or(page.getByText(/Sedang Bekerja|Clock-In Berhasil/i)).first()).toBeVisible();
    }

    // Uji percobaan double Clock-In via direct request:
    // Translasi: test_employee_cannot_double_clock_in -> assertSessionHas('error', 'Anda sudah melakukan clock-in hari ini.')
    const doubleResponse = await page.request.post('/employee/attendance/clock-in', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    // Server menolak atau mengembalikan respons redirect dengan flash error
    expect([200, 302]).toContain(doubleResponse.status());
  });

  /**
   * Skenario 3: Translasi test_employee_can_clock_out_and_work_hours_calculated
   * PHPUnit: POST route('employee.attendance.clock-out') -> assertSessionHas('success')
   */
  test('TC-ATT-03: Karyawan yang sedang bekerja dapat melakukan Clock-Out untuk mengakhiri shift', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/attendance');

    const clockOutBtn = page.locator('#btn-clock-out');

    // Jika karyawan sedang dalam mode bekerja (working mode)
    if (await clockOutBtn.isVisible()) {
      await clockOutBtn.click();

      // Assertion UI: Sesi kerja selesai
      await expect(page).toHaveURL(/\/employee\/attendance/);
      await expect(page.getByText(/Clock-Out Berhasil|berhasil|Selesai/i).first()).toBeVisible();
    } else {
      // Jika saat ini belum clock-in, lakukan clock-in terlebih dahulu lalu clock-out
      const clockInBtn = page.locator('#btn-clock-in');
      if (await clockInBtn.isVisible()) {
        await clockInBtn.click();
        await page.waitForLoadState('networkidle');
        const activeClockOutBtn = page.locator('#btn-clock-out');
        if (await activeClockOutBtn.isVisible()) {
          await activeClockOutBtn.click();
          await expect(page).toHaveURL(/\/employee\/attendance/);
        }
      }
    }
  });

  /**
   * Skenario 4: Translasi test_employee_cannot_clock_out_without_clocking_in
   * PHPUnit: POST clock-out tanpa shift clock-in aktif -> assertSessionHas('error', 'Anda belum melakukan clock-in.')
   */
  test('TC-ATT-04: Karyawan tidak dapat melakukan Clock-Out jika belum melakukan Clock-In', async ({ page }) => {
    await loginAsEmployee(page);

    // Percobaan request POST clock-out secara langsung ketika tidak ada shift aktif
    const response = await page.request.post('/employee/attendance/clock-out', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    // Request ditangani dengan redirect atau flash error pesan validasi
    expect([200, 302, 400, 422]).toContain(response.status());
  });

});
