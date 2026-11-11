// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Kalender Jadwal Kerja Karyawan (PBL-41)
 * ============================================================================
 * Target Halaman: /employee/schedule (Route: employee.schedule)
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

test.describe('Modul Karyawan: Kalender Jadwal Kerja (PBL-41)', () => {

  test('TC-SCH-01: Karyawan dapat melihat kalender jadwal dan navigasi mingguan', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/employee/schedule');
    await expect(page).toHaveURL(/\/employee\/schedule/);

    // Verifikasi Elemen Utama
    await expect(page.getByRole('heading', { name: /Jadwal Saya/i })).toBeVisible();
    await expect(page.getByText(/Kalender jadwal kerja dan penugasan/i)).toBeVisible();

    // Verifikasi Navigasi Mingguan (Mendukung Tampilan Responsif Desktop & Mobile)
    const prevWeekBtn = page.locator('a[href*="week_offset"]').first();
    const nextWeekBtn = page.locator('a[href*="week_offset"]').last();
    await expect(prevWeekBtn).toBeVisible();
    await expect(nextWeekBtn).toBeVisible();

    // Targetkan teks indikator minggu di bagian tengah navigasi yang terlihat responsif di mobile
    const weekLabel = page.locator('div.flex-col.items-center').getByText(/Minggu Ini|Minggu Lalu|Minggu Mendatang/i);
    await weekLabel.scrollIntoViewIfNeeded();
    await expect(weekLabel).toBeVisible();
  });
});

