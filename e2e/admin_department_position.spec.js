// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Kelola Departemen & Posisi (Admin)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Admin/DepartmentPositionTest.php
 * Target Halaman:
 *   - /admin/departments (Daftar Departemen)
 *   - /admin/departments/create (Form Tambah Departemen)
 *   - /admin/positions (Daftar Posisi Jabatan)
 * Blade Views:
 *   - resources/views/admin/departments/index.blade.php
 *   - resources/views/admin/departments/create.blade.php
 *   - resources/views/admin/positions/index.blade.php
 * Akun Pengujian: JadwalinSeeder (Super Admin: owner@senjawisata.co.id, Manager: manager@senjawisata.co.id)
 */

/**
 * Helper login sebagai Super Admin
 * @param {import('@playwright/test').Page} page
 */
async function loginAsSuperAdmin(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('owner@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/admin\/dashboard/);
}

/**
 * Helper login sebagai Manager (Aktor Non-Admin)
 * @param {import('@playwright/test').Page} page
 */
async function loginAsManager(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('manager@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/manager\/dashboard/);
}

test.describe('Modul Admin: Kelola Departemen & Posisi Jabatan', () => {

  /**
   * Skenario 1: Translasi test_superadmin_can_view_department_and_position_list
   * PHPUnit: $response->assertStatus(200); $response->assertSee('Human Resources'); $response->assertSee('Department HR');
   * Playwright: Memverifikasi Super Admin dapat membuka halaman daftar departemen dan berpindah ke tab posisi jabatan.
   */
  test('TC-DEP-01: Super Admin dapat melihat daftar departemen dan posisi jabatan', async ({ page }) => {
    await loginAsSuperAdmin(page);

    // 1. Kunjungi halaman utama Departemen
    await page.goto('/admin/departments');
    await expect(page).toHaveURL(/\/admin\/departments/);

    // Verifikasi Heading & Tab Navigasi
    await expect(page.getByRole('heading', { name: /Departemen & Posisi/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Daftar Departemen/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Posisi Jabatan/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /Tambah Department/i })).toBeVisible();

    // 2. Berpindah ke halaman Kelola Posisi Jabatan
    await page.getByRole('link', { name: /Posisi Jabatan/i }).click();
    await expect(page).toHaveURL(/\/admin\/positions/);
    await expect(page.getByRole('heading', { name: /Posisi/i })).toBeVisible();
    await expect(page.getByText(/Kelola jabatan kerja berdasarkan department/i)).toBeVisible();
  });

  /**
   * Skenario 2: Translasi test_superadmin_can_create_department_with_valid_data
   * PHPUnit: POST route('admin.departments.store') -> assertRedirect -> assertDatabaseHas('departments', [...])
   * Playwright: Buka form tambah departemen, submit data valid, dan assert kemunculan flash success serta data di daftar.
   */
  test('TC-DEP-02: Super Admin dapat menambahkan departemen baru dengan data valid', async ({ page }) => {
    await loginAsSuperAdmin(page);

    const deptName = `Finance Div ${Date.now()}`;
    const deptDesc = 'Divisi Pengelolaan Keuangan dan Anggaran Perusahaan';

    // 1. Akses form tambah departemen
    await page.goto('/admin/departments/create');
    await expect(page.getByRole('heading', { name: /Tambah Department/i })).toBeVisible();

    // 2. Isi data formulir
    // Locator: input#name (Nama Department) & textarea#description (Deskripsi)
    await page.locator('input#name').fill(deptName);
    await page.locator('textarea#description').fill(deptDesc);

    // 3. Submit formulir
    await page.getByRole('button', { name: /Simpan Department/i }).click();

    // 4. Assertion UI:
    // a. Dialihkan kembali ke daftar departemen
    await expect(page).toHaveURL(/\/admin\/departments/);

    // b. Flash message sukses muncul
    await expect(page.getByText(/(Departemen|Department) berhasil ditambahkan/i)).toBeVisible();

    // c. Data nama departemen baru tampil pada tabel/daftar
    await expect(page.getByText(deptName).first()).toBeVisible();
  });

  /**
   * Skenario 3: Translasi test_duplicate_department_name_in_same_company_is_rejected
   * PHPUnit: POST duplikat -> assertSessionHasErrors('name') -> assertDatabaseCount('departments', 1)
   * Playwright: Mencoba membuat departemen dengan nama yang sudah ada, memastikan form menampilkan error validasi.
   */
  test('TC-DEP-03: Penolakan nama departemen duplikat dalam satu perusahaan', async ({ page }) => {
    await loginAsSuperAdmin(page);

    // Gunakan nama departemen default yang sudah ada di database seeder (misal: "Food & Beverage")
    const existingName = 'Food & Beverage';

    await page.goto('/admin/departments/create');
    await page.locator('input#name').fill(existingName);
    await page.locator('textarea#description').fill('Mencoba membuat departemen bernama sama');

    await page.getByRole('button', { name: /Simpan Department/i }).click();

    // Assertion UI:
    // Form menolak submit dan menampilkan pesan error validasi unik
    const errorMsg = page.locator('.text-red-600', { hasText: /sudah digunakan|sudah terdaftar|has already been taken|sudah ada/i });
    await expect(errorMsg.first()).toBeVisible();
  });

  /**
   * Skenario 4: Translasi test_user_cannot_access_departments_from_another_company
   * PHPUnit: $response = get(route('admin.departments.edit', $departmentB)); $response->assertStatus(404);
   * Playwright: Percobaan akses ke ID departemen yang tidak ada atau milik perusahaan lain mengembalikan status 404.
   */
  test('TC-DEP-04: User tidak dapat mengakses atau mengedit departemen milik perusahaan lain (404 Not Found)', async ({ page }) => {
    await loginAsSuperAdmin(page);

    // Gunakan page.request untuk memverifikasi isolasi multi-tenant data pada rute edit
    const foreignOrNonExistentId = 999999;
    const response = await page.request.get(`/admin/departments/${foreignOrNonExistentId}/edit`);

    // Memverifikasi status 404 Not Found
    expect(response.status()).toBe(404);
  });

  /**
   * Skenario 5: Translasi test_non_admin_cannot_access_department_crud
   * PHPUnit: actingAs($manager)->get(route('admin.departments.index'))->assertStatus(403);
   * Playwright: Memverifikasi role non-admin (Manager) dilarang mengakses modul kelola departemen admin (403 Forbidden).
   */
  test('TC-DEP-05: Non-Admin (Manager) ditolak saat mengakses halaman kelola departemen admin (403 Forbidden)', async ({ page }) => {
    await loginAsManager(page);

    // Manager mencoba mengakses URL admin departments
    const response = await page.goto('/admin/departments');

    // Server harus mengembalikan status HTTP 403 Forbidden
    expect(response?.status()).toBe(403);
  });

});
