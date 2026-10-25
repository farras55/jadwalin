// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Pengaturan Regulasi Perusahaan (Admin Settings)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Admin/CompanySettingTest.php
 * Target Halaman:
 *   - /admin/settings (Pengaturan Jam Kerja & Kepatuhan Regulasi PP No. 35/2021)
 * Akun Pengujian: JadwalinSeeder (Super Admin: owner@senjawisata.co.id, Manager: manager@senjawisata.co.id)
 */

/**
 * @param {import('@playwright/test').Page} page
 */
async function loginAsSuperAdmin(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('owner@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/admin\/dashboard/);
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

test.describe('Modul Admin: Pengaturan Regulasi Perusahaan (Company Settings)', () => {

  test('TC-REG-01: Super Admin dapat melihat halaman pengaturan regulasi dan jam kerja', async ({ page }) => {
    await loginAsSuperAdmin(page);

    await page.goto('/admin/settings');
    await expect(page).toHaveURL(/\/admin\/settings/);

    // Verifikasi heading dan form pengaturan
    await expect(page.getByRole('heading', { name: /Pengaturan Perusahaan/i })).toBeVisible();
    await expect(page.getByText(/Atur batas jam kerja dan ketentuan kepatuhan/i)).toBeVisible();
    await expect(page.locator('input#max_regular_hours')).toBeVisible();
    await expect(page.locator('input#max_overtime_hours')).toBeVisible();
  });

  test('TC-REG-02: Super Admin dapat memperbarui parameter regulasi kerja perusahaan', async ({ page }) => {
    await loginAsSuperAdmin(page);

    await page.goto('/admin/settings');
    await expect(page).toHaveURL(/\/admin\/settings/);

    // Ubah parameter jam kerja
    await page.locator('input#max_regular_hours').fill('40');
    await page.locator('input#max_overtime_hours').fill('14');

    // Submit form
    await page.getByRole('button', { name: /Simpan Pengaturan/i }).click();

    // Verifikasi notifikasi berhasil
    await expect(page.locator('[role="status"]', { hasText: /Pengaturan perusahaan berhasil diperbarui|berhasil disimpan/i })).toBeVisible();
  });

  test('TC-REG-03: Penolakan nilai batas jam kerja tidak valid atau melebihi batas regulasi', async ({ page }) => {
    await loginAsSuperAdmin(page);

    await page.goto('/admin/settings');
    await expect(page).toHaveURL(/\/admin\/settings/);

    // Isi nilai melebihi batas validasi (misal regular hours 65 > 60)
    // Gunakan evaluate untuk bypass HTML5 min/max jika diperlukan
    await page.locator('input#max_regular_hours').evaluate((el) => {
      // @ts-ignore
      el.removeAttribute('max');
    });
    await page.locator('input#max_regular_hours').fill('65');

    await page.getByRole('button', { name: /Simpan Pengaturan/i }).click();

    // Verifikasi pesan validasi error
    await expect(page.locator('[role="alert"]')).toBeVisible();
  });

  test('TC-REG-04: Non-Admin (Manager) ditolak saat mengakses pengaturan perusahaan (403 Forbidden)', async ({ page }) => {
    await loginAsManager(page);

    // Manager mencoba mengakses URL pengaturan admin
    const response = await page.goto('/admin/settings');

    // Server menolak dengan kode HTTP 403 Forbidden
    expect(response?.status()).toBe(403);
  });

});
