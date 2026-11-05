// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Monitoring Kehadiran Realtime Tim (Manager - PBL-27)
 * ============================================================================
 * Target Halaman: /manager/attendance (Route: manager.attendance.index)
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

test.describe('Modul Manager: Realtime Attendance Monitoring (PBL-27)', () => {

  test('TC-MON-01: Manager dapat melihat dashboard monitoring kehadiran tim hari ini', async ({ page }) => {
    await loginAsManager(page);
    await page.goto('/manager/attendance');
    await expect(page).toHaveURL(/\/manager\/attendance/);

    // Verifikasi Heading & Subtitle
    await expect(page.getByRole('heading', { name: /Monitoring Kehadiran Tim/i })).toBeVisible();
    await expect(page.getByText(/Pantau kehadiran staf secara realtime/i)).toBeVisible();

    // Verifikasi Kartu Metrik Kehadiran
    await expect(page.getByText(/Dijadwalkan Hari Ini|Total Dijadwalkan|Tepat Waktu|Terlambat/i).first()).toBeVisible();

    // Verifikasi Indikator Live Clock & Polling Status
    await expect(page.getByText(/WIB/i)).toBeVisible();
    await expect(page.getByText(/Live|Terputus/i)).toBeVisible();
  });

  test('TC-MON-02: Endpoint feed realtime mengembalikan payload status kehadiran tim', async ({ page }) => {
    await loginAsManager(page);
    const feedResponse = await page.request.get('/manager/attendance/feed');
    expect(feedResponse.ok()).toBeTruthy();

    const json = await feedResponse.json();
    expect(json).toHaveProperty('metrics');
    expect(json).toHaveProperty('rows');
    expect(json.metrics).toHaveProperty('on_time');
    expect(json.metrics).toHaveProperty('late');
  });
});

