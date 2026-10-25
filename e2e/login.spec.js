// @ts-check
import { test, expect } from '@playwright/test';

/**
 * Pengujian Modul Autentikasi - Login & Logout (F-01, F-02)
 * Rencana Pengujian: docs/TEST_PLAN.md
 * Data Uji: database/seeders/JadwalinSeeder.php
 */

const TEST_ACCOUNTS = {
  superAdmin: {
    email: 'owner@senjawisata.co.id',
    password: 'password123',
    dashboard: /\/admin\/dashboard(?:\?.*)?$/,
    role: /Super Admin/i,
  },
  manager: {
    email: 'manager@senjawisata.co.id',
    password: 'password123',
    dashboard: /\/manager\/dashboard(?:\?.*)?$/,
    role: /Manager/i,
  },
  employee: {
    email: 'karyawan@senjawisata.co.id',
    password: 'password123',
    dashboard: /\/employee\/dashboard(?:\?.*)?$/,
    role: /Karyawan/i,
  },
};

test.describe('Modul Autentikasi: Skenario Login & Logout (F-01, F-02)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');

    await expect(
      page.getByRole('heading', {
        name: /Selamat Datang di Jadwalin/i,
      }),
    ).toBeVisible();
  });

  /**
   * Helper untuk login dan memverifikasi dashboard tujuan.
   * @param {import('@playwright/test').Page} page
   * @param {{ email: string, password: string, dashboard: RegExp|string, role: RegExp|string }} account
   */
  async function loginAs(page, account) {
    await page.locator('#email').fill(account.email);
    await page.locator('#password').fill(account.password);

    await page
      .getByRole('button', { name: /Masuk ke Workspace/i })
      .click();

    // Tunggu sampai navigasi selesai atau URL berubah.
    await expect(page).toHaveURL(account.dashboard, {
      timeout: 15000,
    });

    await expect(
      page.getByText(account.role).first(),
    ).toBeVisible();
  }

  test('TC-LOG-01: Elemen antarmuka halaman login tampil lengkap', async ({
    page,
  }) => {
    await expect(
      page.getByRole('heading', {
        name: /Selamat Datang di Jadwalin/i,
      }),
    ).toBeVisible();

    await expect(page.locator('#email')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();

    await expect(
      page.getByRole('button', {
        name: /Masuk ke Workspace/i,
      }),
    ).toBeVisible();
  });

  test('TC-LOG-02: Toggle tampil/sembunyikan kata sandi berfungsi', async ({
    page,
  }) => {
    const passwordInput = page.locator('#password');
    const toggleButton = passwordInput
      .locator('..')
      .locator('button[type="button"]');

    await expect(passwordInput).toHaveAttribute('type', 'password');

    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');

    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('TC-LOG-03: Login Super Admin menuju dashboard admin', async ({
    page,
  }) => {
    await loginAs(page, TEST_ACCOUNTS.superAdmin);
  });

  test('TC-LOG-04: Login Manager menuju dashboard manager', async ({
    page,
  }) => {
    await loginAs(page, TEST_ACCOUNTS.manager);
  });

  test('TC-LOG-05: Login Karyawan menuju dashboard karyawan', async ({
    page,
  }) => {
    await loginAs(page, TEST_ACCOUNTS.employee);
  });

  test('TC-LOG-06: Login gagal jika kata sandi salah', async ({ page }) => {
    await page.locator('#email').fill(TEST_ACCOUNTS.superAdmin.email);
    await page.locator('#password').fill('passwordSalahTotal123');

    await page
      .getByRole('button', { name: /Masuk ke Workspace/i })
      .click();

    await expect(page).toHaveURL(/\/login(?:\?.*)?$/);

    await expect(
      page.locator('.text-red-600').filter({ hasText: /.+/ }).first(),
    ).toBeVisible();
  });

  test('TC-LOG-07: Login gagal jika email tidak terdaftar', async ({
    page,
  }) => {
    await page
      .locator('#email')
      .fill('tidak_terdaftar@senjawisata.co.id');

    await page.locator('#password').fill('password123');

    await page
      .getByRole('button', { name: /Masuk ke Workspace/i })
      .click();

    await expect(page).toHaveURL(/\/login(?:\?.*)?$/);

    await expect(
      page.locator('.text-red-600').filter({ hasText: /.+/ }).first(),
    ).toBeVisible();
  });

  test('TC-LOG-08: Field email dan password wajib diisi', async ({
    page,
  }) => {
    const emailInput = page.locator('#email');
    const passwordInput = page.locator('#password');

    await expect(emailInput).toHaveAttribute('required', '');
    await expect(passwordInput).toHaveAttribute('required', '');

    await page
      .getByRole('button', { name: /Masuk ke Workspace/i })
      .click();

    // Validasi HTML browser mencegah form kosong dikirim.
    await expect(page).toHaveURL(/\/login(?:\?.*)?$/);
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
  });

  test('TC-LOG-09: Pengguna dapat logout dan kembali ke halaman login', async ({
    page,
  }) => {
    await loginAs(page, TEST_ACCOUNTS.employee);

    // Cari tombol profil pada header.
    const profileButton = page.locator(
      'header [x-data*="open"] > button',
    );

    await expect(profileButton).toBeVisible();
    await profileButton.click();

    const logoutButton = page
      .locator('header')
      .getByRole('button', { name: /Keluar/i });

    await expect(logoutButton).toBeVisible();
    await logoutButton.click();

    await expect(page).toHaveURL(/\/login(?:\?.*)?$/, {
      timeout: 15000,
    });

    // Pastikan pengguna benar-benar kembali ke halaman login.
    await expect(
      page.getByRole('heading', {
        name: /Selamat Datang di Jadwalin/i,
      }),
    ).toBeVisible();
  });
});
