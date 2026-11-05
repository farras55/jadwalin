// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Pusat Notifikasi & Pemberitahuan Akun (PBL-20)
 * ============================================================================
 * Target Halaman: /notifications (Route: notifications.index)
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

test.describe('Modul Notifikasi: Pusat Pemberitahuan Akun (PBL-20)', () => {

  test('TC-NOT-01: Pengguna dapat mengakses halaman pusat notifikasi', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/notifications');
    await expect(page).toHaveURL(/\/notifications/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Pusat Notifikasi/i })).toBeVisible();
    await expect(page.getByText(/Lihat informasi dan pemberitahuan terbaru/i)).toBeVisible();
  });

  test('TC-NOT-02: Ikon lonceng notifikasi di navbar dapat diakses', async ({ page }) => {
    await loginAsEmployee(page);

    // Temukan tombol lonceng notifikasi di topbar
    const bellBtn = page.locator('button[title="Notifikasi"]');
    await expect(bellBtn).toBeVisible();
    await bellBtn.click();

    // Temukan dan klik link "Lihat Semua" di dalam dropdown notifikasi navbar
    const viewAllLink = page.locator('header a[href*="/notifications"]').first();
    await expect(viewAllLink).toBeVisible();
    await viewAllLink.click();

    await expect(page).toHaveURL(/\/notifications/);
  });
});