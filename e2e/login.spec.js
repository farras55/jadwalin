// @ts-check
import { test, expect } from '@playwright/test';

/**
 * Pengujian Modul Autentikasi - Kasus Uji Login (MOD-01, F-01 & F-02)
 * Rencana Pengujian: docs/TEST_PLAN.md
 * Data Uji: database/seeders/JadwalinSeeder.php
 */
test.describe('Modul Autentikasi: Skenario Login & Logout (F-01, F-02)', () => {

  test.beforeEach(async ({ page }) => {
    // Navigasi ke halaman form login sebelum setiap test case
    await page.goto('/login');
  });

  test('TC-LOG-01: Verifikasi elemen antarmuka halaman login tampil lengkap', async ({ page }) => {
    // 1. Verifikasi judul halaman / heading
    await expect(page.getByRole('heading', { name: /Selamat Datang di Jadwalin/i })).toBeVisible();

    // 2. Verifikasi field input email dan password
    const emailInput = page.locator('input#email');
    const passwordInput = page.locator('input#password');
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();

    // 3. Verifikasi tombol submit 'Masuk ke Workspace'
    const submitBtn = page.getByRole('button', { name: /Masuk ke Workspace/i });
    await expect(submitBtn).toBeVisible();
  });

  test('TC-LOG-02: Fitur interaktif toggle tampil/sembunyikan kata sandi (Show/Hide Password)', async ({ page }) => {
    const passwordInput = page.locator('input#password');
    // Tombol toggle mata berada di sebelah input password
    const toggleButton = passwordInput.locator('..').locator('button[type="button"]');

    // Kondisi awal bertipe password
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Klik tombol toggle pertama kali -> tipe berubah ke text (kata sandi terlihat)
    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');

    // Klik tombol toggle kedua kali -> tipe kembali ke password (kata sandi tersembunyi)
    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('TC-LOG-03: Login Berhasil sebagai Super Admin dan dialihkan ke Dashboard Super Admin', async ({ page }) => {
    await page.locator('input#email').fill('owner@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi URL dialihkan ke /admin/dashboard
    await expect(page).toHaveURL(/\/admin\/dashboard/);

    // Verifikasi indikator peran Super Admin tampil di antarmuka
    await expect(page.getByText('Super Admin', { exact: false }).first()).toBeVisible();
  });

  test('TC-LOG-04: Login Berhasil sebagai Manager dan dialihkan ke Dashboard Manager', async ({ page }) => {
    await page.locator('input#email').fill('manager@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi URL dialihkan ke /manager/dashboard
    await expect(page).toHaveURL(/\/manager\/dashboard/);

    // Verifikasi indikator peran Manager tampil di antarmuka
    await expect(page.getByText('Manager', { exact: false }).first()).toBeVisible();
  });

  test('TC-LOG-05: Login Berhasil sebagai Karyawan dan dialihkan ke Dashboard Karyawan', async ({ page }) => {
    await page.locator('input#email').fill('karyawan@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi URL dialihkan ke /employee/dashboard
    await expect(page).toHaveURL(/\/employee\/dashboard/);

    // Verifikasi indikator peran Karyawan tampil di antarmuka
    await expect(page.getByText('Karyawan', { exact: false }).first()).toBeVisible();
  });

  test('TC-LOG-06: Login Gagal dengan kata sandi salah (Negative Test)', async ({ page }) => {
    await page.locator('input#email').fill('owner@senjawisata.co.id');
    await page.locator('input#password').fill('passwordSalahTotal123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi pengguna tetap berada di URL /login
    await expect(page).toHaveURL(/\/login/);

    // Verifikasi pesan error kredensial tidak cocok tampil
    const errorMessage = page.locator('.text-red-600');
    await expect(errorMessage).toBeVisible();
  });

  test('TC-LOG-07: Login Gagal dengan email tidak terdaftar (Negative Test)', async ({ page }) => {
    await page.locator('input#email').fill('tidak_terdaftar@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi pengguna tetap berada di URL /login
    await expect(page).toHaveURL(/\/login/);

    // Verifikasi pesan error kredensial tidak cocok tampil
    const errorMessage = page.locator('.text-red-600');
    await expect(errorMessage).toBeVisible();
  });

  test('TC-LOG-08: Validasi field formulir tidak boleh kosong (Required Attributes)', async ({ page }) => {
    const emailInput = page.locator('input#email');
    const passwordInput = page.locator('input#password');

    // Verifikasi atribut required aktif pada field
    await expect(emailInput).toHaveAttribute('required', '');
    await expect(passwordInput).toHaveAttribute('required', '');

    // Form tidak dapat disubmit saat kosong dan tetap berada di /login
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('TC-LOG-09: Skenario Logout Pengguna mengakhiri sesi autentikasi', async ({ page }) => {
    // 1. Login terlebih dahulu sebagai Karyawan
    await page.locator('input#email').fill('karyawan@senjawisata.co.id');
    await page.locator('input#password').fill('password123');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
    await expect(page).toHaveURL(/\/employee\/dashboard/);

    // 2. Buka dropdown menu profil di topbar
    const profileDropdown = page.locator('header [x-data*="open"] > button');
    await profileDropdown.click();

    // 3. Klik tombol 'Keluar' pada dropdown navbar header
    const logoutBtn = page.locator('header').getByRole('button', { name: /Keluar/i });
    await expect(logoutBtn).toBeVisible();
    await logoutBtn.click();

    // 4. Verifikasi sesi berakhir dan pengguna dialihkan ke halaman login /login
    await expect(page).toHaveURL(/\/login/);
  });

});
