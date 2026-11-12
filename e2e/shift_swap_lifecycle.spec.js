// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Alur Lengkap Transaksi Tukar & Serah Shift (PBL-22)
 * ============================================================================
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

test.describe('E2E Lifecycle: Pertukaran Shift Antar-Peran (PBL-22)', () => {

  test('TC-SWL-01: Karyawan dapat mengakses halaman tukar shift', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/swaps');
    await expect(page).toHaveURL(/\/employee\/swaps/);
    await expect(page.getByRole('heading', { name: /Tukar & Serah Shift/i }).first()).toBeVisible();
  });

  test('TC-SWL-02: Manager dapat mengakses persetujuan tukar shift', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/swaps');
    await expect(page).toHaveURL(/\/manager\/swaps/);
    await expect(page.getByRole('heading', { name: /Persetujuan Tukar Shift|Tukar Shift/i }).first()).toBeVisible();
  });
});

