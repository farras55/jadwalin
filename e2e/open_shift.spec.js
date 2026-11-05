// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Bursa Shift Terbuka / Open Shift (PBL-23)
 * ============================================================================
 * Target Halaman:
 * - Karyawan: /employee/open-shifts (Route: employee.open-shifts)
 * - Manajer: /manager/open-shifts (Route: manager.open-shifts.index)
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

test.describe('Modul Open Shift: Bursa & Klaim Shift Terbuka (PBL-23)', () => {

  test('TC-OPS-01: Karyawan dapat melihat daftar open shift yang tersedia', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/open-shifts');
    await expect(page).toHaveURL(/\/employee\/open-shifts/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Shift Terbuka|Open Shift/i })).toBeVisible();
    await expect(page.getByText(/Klaim jadwal shift tambahan/i)).toBeVisible();
  });

  test('TC-OPS-02: Manajer dapat melihat halaman kelola bursa open shift', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/open-shifts');
    await expect(page).toHaveURL(/\/manager\/open-shifts/);

    // Verifikasi Antarmuka Manajer
    await expect(page.getByRole('heading', { name: /Open Shift|Bursa Shift/i }).first()).toBeVisible();
  });

  test('TC-OPS-03: Karyawan biasa ditolak saat mengakses kelola open shift manajer (403)', async ({ page }) => {
    await loginAsEmployee(page);
    const response = await page.goto('/manager/open-shifts');
    expect(response?.status()).toBe(403);
  });
});

