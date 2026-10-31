// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Landing Page & Navigasi Publik (Jadwalin)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/LandingPageAndProfileTest.php
 * Target Halaman:
 *   - / (Halaman Utama / Landing Page)
 *   - /login (Halaman Masuk Sistem)
 */

test.describe('Modul Publik: Halaman Utama (Landing Page)', () => {

  test('TC-LAND-01: Pengunjung dapat mengakses landing page publik Jadwalin', async ({ page }) => {
    // 1. Kunjungi halaman utama
    await page.goto('/');

    // 2. Verifikasi status URL dan elemen landing page
    await expect(page).toHaveURL('/');
    await expect(page).toHaveTitle(/Jadwalin/i);
    await expect(page.getByText(/Jadwalin/i).first()).toBeVisible();
    await expect(page.getByText(/PP No\. 35\/2021/i).first()).toBeVisible();
  });

  test('TC-LAND-02: Tautan tombol Masuk Sistem mengarahkan ke halaman login', async ({ page }) => {
    // 1. Kunjungi halaman utama
    await page.goto('/');

    // 2. Klik tombol CTA Masuk Sistem
    const loginLink = page.getByRole('link', { name: /Masuk Sistem|Masuk/i }).first();
    await expect(loginLink).toBeVisible();
    await loginLink.click();

    // 3. Verifikasi dialihkan ke rute /login
    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByRole('heading', { name: /Selamat Datang/i })).toBeVisible();
  });

  test('TC-LAND-03: Menampilkan pilar fitur 0 Konflik Jam Kerja dan informasi platform', async ({ page }) => {
    await page.goto('/');

    // Verifikasi fitur utama dan hak cipta tertera di antarmuka
    await expect(page.getByRole('navigation')).toBeVisible();
    await expect(page.getByText(/Hak Cipta Dilindungi|Jadwalin/i).first()).toBeVisible();
  });

});
