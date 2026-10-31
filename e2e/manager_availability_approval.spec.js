// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Persetujuan Halangan Staf oleh Manajer (Availability Approval)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Manager/AvailabilityApprovalTest.php
 * Target Halaman:
 *   - /manager/availability (Daftar Pengajuan Halangan Tim)
 * Akun Pengujian: JadwalinSeeder (Manager: manager@senjawisata.co.id, Employee: karyawan@senjawisata.co.id)
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

test.describe('Modul Manager: Persetujuan Ketersediaan & Halangan Karyawan', () => {

  test('TC-APP-01: Manager dapat melihat halaman dan daftar persetujuan halangan tim', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/availability');
    await expect(page).toHaveURL(/\/manager\/availability/);

    // Verifikasi Heading dan filter tab
    await expect(page.getByRole('heading', { name: /Persetujuan Ketersediaan/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Menunggu/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Disetujui/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Ditolak/i })).toBeVisible();
  });

  test('TC-APP-02: Filter status persetujuan menampilkan daftar halangan yang relevan', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/availability');

    // Klik tab 'Disetujui'
    await page.getByRole('link', { name: /Disetujui/i }).click();
    await expect(page).toHaveURL(/status=approved/);

    // Klik tab 'Ditolak'
    await page.getByRole('link', { name: /Ditolak/i }).click();
    await expect(page).toHaveURL(/status=rejected/);
  });

  test('TC-APP-03: Karyawan biasa (role employee) ditolak saat mengakses approval manajer (403 Forbidden)', async ({ page }) => {
    await loginAsEmployee(page);

    // Employee mencoba mengakses rute manajer
    const response = await page.goto('/manager/availability');

    // Harus ditolak dengan status HTTP 403 Forbidden
    expect(response?.status()).toBe(403);
  });

});
