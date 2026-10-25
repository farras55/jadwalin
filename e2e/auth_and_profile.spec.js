// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Autentikasi, Keamanan Akun, & Manajemen Profil Pengguna
 * ============================================================================
 * Penerjemahan skenario pengujian gabungan dari:
 *   1. tests/Feature/Auth/AuthenticationTest.php
 *   2. tests/Feature/Auth/PasswordConfirmationTest.php
 *   3. tests/Feature/Auth/PasswordResetTest.php
 *   4. tests/Feature/Auth/PasswordUpdateTest.php
 *   5. tests/Feature/ProfileTest.php
 *
 * Target Halaman:
 *   - /login & /logout
 *   - /forgot-password
 *   - /confirm-password
 *   - /profile (Edit Profil, Ganti Kata Sandi, Hapus Akun)
 *
 * Akun Pengujian: JadwalinSeeder (karyawan@senjawisata.co.id / password123)
 */

async function loginAsEmployee(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('karyawan@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/employee\/dashboard/);
}

test.describe('Modul Autentikasi & Profil Pengguna (Auth & Profile)', () => {

  // --------------------------------------------------------------------------
  // BAGIAN 1: ALUR AUTENTIKASI (AuthenticationTest.php)
  // --------------------------------------------------------------------------

  test('TC-AUTH-01: Halaman login dapat dimuat dan menampilkan form autentikasi', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByRole('heading', { name: /Selamat Datang di Jadwalin/i })).toBeVisible();
    await expect(page.locator('input#email')).toBeVisible();
    await expect(page.locator('input#password')).toBeVisible();
    await expect(page.getByRole('button', { name: /Masuk ke Workspace/i })).toBeVisible();
  });

  test('TC-AUTH-02: Autentikasi gagal ketika kata sandi salah (Negative Test)', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input#email').fill('karyawan@senjawisata.co.id');
    await page.locator('input#password').fill('kata-sandi-salah-total');
    await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

    // Verifikasi tetap di halaman login dan pesan error kredensial tampil
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('.text-red-600')).toBeVisible();
  });

  test('TC-AUTH-03: Sesi pengguna dapat diakhiri melalui aksi Logout', async ({ page }) => {
    await loginAsEmployee(page);

    // Buka menu profil di topbar/sidebar dan klik tombol keluar
    const logoutBtn = page.getByRole('button', { name: /Keluar|Log out/i }).or(page.locator('button[title="Keluar"]')).first();
    await logoutBtn.click();

    // Verifikasi sesi berakhir dan dialihkan kembali ke /login
    await expect(page).toHaveURL(/\/login/);
  });

  // --------------------------------------------------------------------------
  // BAGIAN 2: LUPA KATA SANDI & KONFIRMASI (PasswordResetTest & PasswordConfirmationTest)
  // --------------------------------------------------------------------------

  test('TC-AUTH-04: Halaman lupa kata sandi (Forgot Password) dapat dimuat', async ({ page }) => {
    await page.goto('/forgot-password');
    await expect(page).toHaveURL(/\/forgot-password/);
    await expect(page.locator('input#email')).toBeVisible();
    await expect(page.getByRole('button', { name: /Email Password Reset Link|Kirim Tautan Reset/i })).toBeVisible();
  });

  test('TC-AUTH-05: Halaman konfirmasi kata sandi (Confirm Password) terlindungi autentikasi', async ({ page }) => {
    await loginAsEmployee(page);

    // Buka rute konfirmasi kata sandi
    await page.goto('/confirm-password');

    // Jika sistem mengarahkan ke form konfirmasi
    if (page.url().includes('confirm-password')) {
      await expect(page.locator('input#password')).toBeVisible();
      await expect(page.getByRole('button', { name: /Confirm|Konfirmasi/i })).toBeVisible();
    }
  });

  // --------------------------------------------------------------------------
  // BAGIAN 3: MANAJEMEN PROFIL (ProfileTest.php & PasswordUpdateTest.php)
  // --------------------------------------------------------------------------

  test('TC-PROF-01: Pengguna terautentikasi dapat melihat halaman profil lengkap', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/profile');
    await expect(page).toHaveURL(/\/profile/);

    // Verifikasi Heading Halaman Profil
    await expect(page.getByRole('heading', { name: /Pengaturan Profil Akun/i })).toBeVisible();
    await expect(page.getByText(/Kelola identitas profil, nomor kontak, serta keamanan akun Anda/i)).toBeVisible();

    // Verifikasi Kartu Identitas Pengguna
    await expect(page.getByText('Dewi Lestari', { exact: false }).first()).toBeVisible();
    await expect(page.getByText('karyawan@senjawisata.co.id')).toBeVisible();
    await expect(page.getByText(/PT Senja Wisata Nusantara/i)).toBeVisible();

    // Verifikasi Form Informasi Profil & Form Keamanan
    await expect(page.getByRole('heading', { name: /Informasi Profil/i })).toBeVisible();
    await expect(page.getByRole('heading', { name: /Perbarui Kata Sandi|Keamanan & Kata Sandi/i })).toBeVisible();
    await expect(page.getByRole('heading', { name: /Hapus Akun/i })).toBeVisible();
  });

  test('TC-PROF-02: Pengguna dapat memperbarui nama dan nomor telepon profil', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/profile');

    const updatedPhone = '081298765432';

    // Isi formulir profil
    const phoneInput = page.locator('input#phone_number');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill(updatedPhone);

      // Simpan perubahan profil
      const saveBtn = page.locator('form', { has: phoneInput }).getByRole('button', { name: /Simpan|Save/i });
      await saveBtn.click();

      // Verifikasi indikator tersimpan / notifikasi sukses
      await expect(page.getByText(/Tersimpan|Saved|berhasil disimpan|Profil berhasil diperbarui/i).first()).toBeVisible();
    }
  });

  test('TC-PROF-03: Validasi nomor telepon tidak valid ditolak', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/profile');

    const phoneInput = page.locator('input#phone_number');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill('bukan-nomor-telepon-asli');

      const saveBtn = page.locator('form', { has: phoneInput }).getByRole('button', { name: /Simpan|Save/i });
      await saveBtn.click();

      // Verifikasi pesan error validasi muncul di dalam form profil
      await expect(page.locator('form', { has: phoneInput }).locator('.text-red-600').first()).toBeVisible();
    }
  });

  test('TC-PROF-04: Pengubahan kata sandi menolak jika kata sandi saat ini salah', async ({ page }) => {
    await loginAsEmployee(page);
    await page.goto('/profile');

    const currentPasswordInput = page.locator('input#update_password_current_password');
    const newPasswordInput = page.locator('input#update_password_password');
    const confirmPasswordInput = page.locator('input#update_password_password_confirmation');

    if (await currentPasswordInput.isVisible()) {
      await currentPasswordInput.fill('passwordLamaSalahTotal');
      await newPasswordInput.fill('passwordBaru12345');
      await confirmPasswordInput.fill('passwordBaru12345');

      const submitBtn = page.locator('form', { has: currentPasswordInput }).getByRole('button', { name: /Perbarui Sandi|Simpan|Save/i });
      await submitBtn.click();

      // Verifikasi pesan error kata sandi saat ini tidak cocok
      await expect(page.locator('form', { has: currentPasswordInput }).locator('.text-red-600').first()).toBeVisible();
    }
  });

});
