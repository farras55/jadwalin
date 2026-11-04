// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Koreksi Presensi / Attendance Regularization (PBL-28)
 * ============================================================================
 * Target Halaman:
 * - Karyawan: /employee/attendance/regularization
 * - Manajer: /manager/attendance/regularizations
 */

/**
 * @param {import('@playwright/test').Page} page
 */
async function loginAsEmployee(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('karyawan@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/employee\/dashboard/);
}

/**
 * @param {import('@playwright/test').Page} page
 */
async function loginAsManager(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('manager@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/manager\/dashboard/);
}

test.describe('Modul Koreksi Presensi: Attendance Regularization (PBL-28)', () => {

  test('TC-REG-01: Karyawan dapat melihat riwayat pengajuan koreksi kehadiran', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/attendance/regularization');
    await expect(page).toHaveURL(/\/employee\/attendance\/regularization/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Riwayat Koreksi Kehadiran/i })).toBeVisible();
    await expect(page.getByText(/Daftar seluruh permohonan koreksi/i)).toBeVisible();
    await expect(page.getByRole('heading', { name: /Daftar Pengajuan Koreksi/i })).toBeVisible();
  });

  test('TC-REG-02: Manajer dapat melihat antrean persetujuan koreksi absensi', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/attendance/regularizations');
    await expect(page).toHaveURL(/\/manager\/attendance\/regularizations/);

    // Verifikasi Antarmuka Persetujuan Manajer
    await expect(page.getByRole('heading', { name: /Koreksi|Regularization|Persetujuan/i }).first()).toBeVisible();
  });

  test('TC-REG-03: Karyawan biasa ditolak saat mengakses approval koreksi manajer (403)', async ({ page }) => {
    await loginAsEmployee(page);
    const response = await page.goto('/manager/attendance/regularizations');
    expect(response?.status()).toBe(403);
  });
});

