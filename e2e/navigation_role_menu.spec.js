// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Navigasi, Menu Sidebar, & Kontrol Akses Peran (RBAC)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/NavigationAndRoleMenuTest.php
 * Target Halaman:
 *   - /admin/* (Super Admin)
 *   - /manager/* (Manager)
 *   - /employee/* (Karyawan)
 * Kredensial Uji: database/seeders/JadwalinSeeder.php
 */

test.describe('Modul Navigasi & Hak Akses Berbasis Peran (RBAC)', () => {

  /**
   * Skenario 1: Translasi test_superadmin_can_access_admin_routes_and_not_manager_routes
   * PHPUnit: Superadmin dapat membuka /admin/dashboard & /admin/companies, namun ditolak di /manager/dashboard (403)
   */
  test('TC-NAV-01: Hak akses dan menu navigasi Super Admin', async ({ page }) => {
    // 1. Login sebagai Super Admin
    await page.goto('/login');
    await page.locator('input#email').fill('owner@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // 2. Akses Dashboard Super Admin
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    await expect(page.getByText('Super Admin', { exact: false }).first()).toBeVisible();

    // 3. Verifikasi Menu Navigasi Sidebar Super Admin
    await expect(page.getByRole('link', { name: /Perusahaan & Cabang/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Kelola Pengguna/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Departemen & Posisi/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Template Shift/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Rekap Timesheet/i })).toBeVisible();

    // 4. Kunjungi halaman Perusahaan & Cabang
    await page.goto('/admin/companies');
    await expect(page).toHaveURL(/\/admin\/companies/);

    // 5. Verifikasi Proteksi: Super Admin ditolak mengakses rute Manager (403 Forbidden)
    const forbiddenResponse = await page.goto('/manager/dashboard');
    expect(forbiddenResponse?.status()).toBe(403);
  });

  /**
   * Skenario 2: Translasi test_manager_can_access_manager_routes_and_not_admin_routes
   * PHPUnit: Manager dapat membuka /manager/dashboard & /manager/shifts, namun ditolak di /admin/dashboard (403)
   */
  test('TC-NAV-02: Hak akses dan menu navigasi Manager', async ({ page }) => {
    // 1. Login sebagai Manager
    await page.goto('/login');
    await page.locator('input#email').fill('manager@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // 2. Akses Dashboard Manager
    await expect(page).toHaveURL(/\/manager\/dashboard/);
    await expect(page.getByText('Manager', { exact: false }).first()).toBeVisible();

    // 3. Verifikasi Menu Navigasi Sidebar Manager
    await expect(page.getByRole('link', { name: /Jadwal Kerja Tim/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Persetujuan Ketersediaan/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Persetujuan Tukar Shift/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Presensi Tim/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Timesheet & Laporan/i })).toBeVisible();

    // 4. Kunjungi halaman Jadwal Kerja Tim
    await page.goto('/manager/shifts');
    await expect(page).toHaveURL(/\/manager\/shifts/);

    // 5. Verifikasi Proteksi: Manager ditolak mengakses rute Admin (403 Forbidden)
    const forbiddenResponse = await page.goto('/admin/dashboard');
    expect(forbiddenResponse?.status()).toBe(403);
  });

  /**
   * Skenario 3: Translasi test_employee_can_access_employee_routes_and_not_admin_routes
   * PHPUnit: Employee dapat membuka /employee/dashboard & /employee/schedule, namun ditolak di /admin/dashboard (403)
   */
  test('TC-NAV-03: Hak akses dan menu navigasi Karyawan (Employee)', async ({ page }) => {
    // 1. Login sebagai Karyawan
    await page.goto('/login');
    await page.locator('input#email').fill('karyawan@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // 2. Akses Dashboard Karyawan
    await expect(page).toHaveURL(/\/employee\/dashboard/);
    await expect(page.getByText('Karyawan', { exact: false }).first()).toBeVisible();

    // 3. Verifikasi Menu Navigasi Sidebar Karyawan
    await expect(page.getByRole('link', { name: /Jadwal Saya/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Tukar Shift/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Presensi \/ Absensi/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Timesheet/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Ketersediaan Waktu/i })).toBeVisible();

    // 4. Kunjungi halaman Jadwal Saya
    await page.goto('/employee/schedule');
    await expect(page).toHaveURL(/\/employee\/schedule/);

    // 5. Verifikasi Proteksi: Karyawan ditolak mengakses rute Admin (403 Forbidden)
    const forbiddenResponse = await page.goto('/admin/dashboard');
    expect(forbiddenResponse?.status()).toBe(403);
  });

});
