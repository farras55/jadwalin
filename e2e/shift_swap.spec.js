// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Tukar & Serah Shift (PBL-22)
 * ============================================================================
 * Target Halaman:
 * - Karyawan: /employee/swaps (Route: employee.swaps)
 * - Manajer: /manager/swaps (Route: manager.swaps.index)
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

test.describe('Modul Shift Swap: Alur Tukar & Serah Shift (PBL-22)', () => {

  test('TC-SWP-01: Karyawan dapat melihat halaman tukar shift dan modal pengajuan', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/swaps');
    await expect(page).toHaveURL(/\/employee\/swaps/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Tukar & Serah Shift/i })).toBeVisible();
    await expect(page.getByText(/Ajukan penyerahan tugas/i)).toBeVisible();

    // Verifikasi Tab Navigasi
    await expect(page.getByRole('button', { name: /Permintaan Masuk|Pengajuan Saya|Riwayat/i }).first()).toBeVisible();

    // Verifikasi Tombol Buka Modal Buat Pengajuan
    const newSwapBtn = page.getByRole('button', { name: /Ajukan Tukar Shift/i });
    if (await newSwapBtn.isVisible()) {
      await newSwapBtn.click();
      await expect(page.getByRole('heading', { name: /Ajukan Tukar \/ Serah Shift/i })).toBeVisible();
      await expect(page.getByLabel(/Alasan/i).or(page.locator('textarea[name="reason"]'))).toBeVisible();
    }
  });

  test('TC-SWP-02: Manajer dapat melihat halaman persetujuan tukar shift', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/swaps');
    await expect(page).toHaveURL(/\/manager\/swaps/);

    // Verifikasi Heading Manajer
    await expect(page.getByRole('heading', { name: /Persetujuan Tukar Shift|Tukar Shift/i })).toBeVisible();
  });

  test('TC-SWP-03: Karyawan biasa ditolak saat mengakses approval manajer (403)', async ({ page }) => {
    await loginAsEmployee(page);
    const response = await page.goto('/manager/swaps');
    expect(response?.status()).toBe(403);
  });
});
