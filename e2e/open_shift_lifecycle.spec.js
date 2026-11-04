// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Alur Lengkap Transaksi Bursa Open Shift (PBL-23)
 * ============================================================================
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

test.describe('E2E Lifecycle: Bursa Shift Terbuka (Open Shift - PBL-23)', () => {

  test('TC-OSL-01: Manager dapat mengakses bursa open shift', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/open-shifts');
    await expect(page).toHaveURL(/\/manager\/open-shifts/);
    await expect(page.getByRole('heading', { name: /Bursa Shift Terbuka|Open Shift/i })).toBeVisible();
  });

  test('TC-OSL-02: Karyawan dapat mengakses shift terbuka', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/open-shifts');
    await expect(page).toHaveURL(/\/employee\/open-shifts/);
    await expect(page.getByRole('heading', { name: /Shift Terbuka|Bursa Shift/i })).toBeVisible();
  });
});

