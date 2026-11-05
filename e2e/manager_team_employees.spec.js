// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Manajemen Data Karyawan Tim (Manager - PBL-25)
 * ============================================================================
 * Target Halaman: /manager/employees (Route: manager.employees.index)
 */

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

test.describe('Modul Manager: Data Karyawan Tim (PBL-25)', () => {

  test('TC-TEM-01: Manager dapat melihat direktori dan daftar staf departemen', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/employees');
    await expect(page).toHaveURL(/\/manager\/employees/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Data Karyawan Tim/i })).toBeVisible();
    await expect(page.getByText(/Total Karyawan/i)).toBeVisible();
    await expect(page.getByText(/Full-Time/i).first()).toBeVisible();

    // Verifikasi Form Pencarian & Filter
    await expect(page.locator('input[name="search"]').or(page.getByPlaceholder(/Cari/i))).toBeVisible();
  });
});

