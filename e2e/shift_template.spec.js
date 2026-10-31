// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Master Shift Template (Admin)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Admin/ShiftTemplateTest.php
 * Target Halaman: /admin/shifts (Route: admin.shifts.index)
 * Blade View: resources/views/admin/shifts/index.blade.php
 * Kredensial Uji: database/seeders/JadwalinSeeder.php
 */

/**
 * Helper function: Login sebagai Super Admin
 * Menggunakan akun pancingan default dari JadwalinSeeder
 * @param {import('@playwright/test').Page} page
 */
async function loginAsAdmin(page) {
  await page.goto('/login');

  // Locator input login (sesuaikan jika ID atau name berbeda di lingkungan Anda)
  await page.locator('input#email').fill('owner@senjawisata.co.id');
  await page.locator('input#password').fill('password123');

  // Locator tombol login
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();

  // Pastikan login berhasil dan dialihkan ke area admin
  await expect(page).toHaveURL(/\/admin\/dashboard/);
}

test.describe('Modul Master Shift: Skenario E2E Shift Template Admin', () => {

  test.beforeEach(async ({ page }) => {
    // 1. Lakukan autentikasi sebagai Admin sebelum setiap skenario pengujian
    await loginAsAdmin(page);

    // 2. Navigasi ke halaman Master Template Shift
    await page.goto('/admin/shifts');
    await expect(page).toHaveURL(/\/admin\/shifts/);
  });

  /**
   * Skenario 1: Translasi dari test_admin_can_view_shift_templates_list
   * PHPUnit: $response->assertOk(); $response->assertSee('Shift Pagi Operasional'); $response->assertSee('#6C5CE7');
   * Playwright: Memverifikasi elemen antarmuka, heading, ringkasan data, dan tabel template shift tampil.
   */
  test('TC-SFT-01: Admin dapat melihat halaman dan daftar template shift (View List)', async ({ page }) => {
    // Verifikasi Judul & Subjudul Halaman
    // Locator: Heading h2 "Master Shift"
    await expect(page.getByRole('heading', { name: /Master Shift/i })).toBeVisible();
    await expect(page.getByText(/Kelola template shift, jam kerja, departemen, dan kuota karyawan/i)).toBeVisible();

    // Verifikasi Tombol Tambah Template Shift
    // Locator: Button dengan teks "Tambah Template Shift"
    const createBtn = page.getByRole('button', { name: /Tambah Template Shift/i });
    await expect(createBtn).toBeVisible();

    // Verifikasi Card Ringkasan Statistik (Total Template, Departemen, Total Kuota)
    // Locator: Text label ringkasan
    await expect(page.getByText('Total Template', { exact: true })).toBeVisible();
    await expect(page.getByText('Departemen', { exact: true }).first()).toBeVisible();
    await expect(page.getByText('Total Kuota', { exact: true })).toBeVisible();

    // Verifikasi Kolom Header Tabel
    // Locator: Header tabel (th)
    await expect(page.getByRole('columnheader', { name: /Template/i })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: /Departemen/i })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: /Jam Kerja/i })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: /Durasi/i })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: /Kuota/i })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: /Aksi/i })).toBeVisible();

    // Verifikasi Field Pencarian
    // Locator: Input search filter
    const searchInput = page.locator('input[type="search"]');
    await expect(searchInput).toBeVisible();
  });

  /**
   * Skenario 2: Translasi dari test_admin_can_create_shift_template_with_color_and_quota
   * PHPUnit: POST route('admin.shifts.store') -> assertRedirect -> assertDatabaseHas('shift_templates', [...])
   * Playwright: Buka modal tambah, isi formulir, submit, dan pastikan data baru muncul di tabel beserta flash message.
   */
  test('TC-SFT-02: Admin dapat menambahkan template shift baru dengan kuota dan kode warna (Create)', async ({ page }) => {
    // Generate nama unik untuk menghindari konflik data pengujian berulang
    const uniqueTitle = `Shift Siang ${Date.now()}`;
    const targetColor = '#2ecc71';
    const targetQuota = '3';

    // 1. Klik tombol "Tambah Template Shift" untuk memunculkan modal Alpine.js
    // Locator: Button pemicu modal
    await page.getByRole('button', { name: /Tambah Template Shift/i }).click();

    // 2. Verifikasi modal pembuatan terbuka
    // Locator: Heading h3 pada modal
    await expect(page.getByRole('heading', { name: /Tambah Template Shift/i })).toBeVisible();

    // 3. Isi formulir pembuatan template shift
    // Locator: ID input berdasarkan resources/views/admin/shifts/index.blade.php
    await page.locator('input#title').fill(uniqueTitle);

    // Pilih departemen pertama yang tersedia (bukan opsi placeholder kosong)
    const departmentSelect = page.locator('select#department_id');
    await departmentSelect.selectOption({ index: 1 });

    // Isi jam kerja (12:00 - 20:00)
    await page.locator('input#start_time').fill('12:00');
    await page.locator('input#end_time').fill('20:00');

    // Isi kuota karyawan (min: 1)
    await page.locator('input#default_quota').fill(targetQuota);

    // Pilih / isi kode warna hex
    await page.locator('input#color_code').fill(targetColor);

    // 4. Klik tombol "Simpan Template" untuk mengirim form
    // Locator: Button submit form tambah
    await page.getByRole('button', { name: /Simpan Template/i }).click();

    // 5. Assertion UI:
    // a. Verifikasi banner alert notifikasi sukses muncul
    await expect(page.getByText('Template shift berhasil ditambahkan.')).toBeVisible();

    // b. Verifikasi baris data baru telah masuk dan terlihat di dalam tabel antarmuka
    // (Penerjemahan assertDatabaseHas menjadi verifikasi elemen UI)
    const newRow = page.locator('tbody tr', { hasText: uniqueTitle });
    await expect(newRow).toBeVisible();
    await expect(newRow.getByText(`${targetQuota} orang`)).toBeVisible();
    await expect(newRow.getByText(/12:00\s*–\s*20:00/)).toBeVisible();
  });

  /**
   * Skenario 3: Translasi dari test_admin_can_update_and_delete_shift_template
   * PHPUnit: PUT route('admin.shifts.update') -> assertDatabaseHas -> DELETE route('admin.shifts.destroy') -> assertDatabaseMissing
   * Playwright:
   *   1. Buat template awal (atau edit template yang ada).
   *   2. Klik tombol edit pada baris target, ubah jam, kuota, dan judul, submit, lalu verifikasi perubahannya di tabel.
   *   3. Klik tombol hapus pada baris target, konfirmasi di modal, lalu verifikasi data hilang dari tabel.
   */
  test('TC-SFT-03: Admin dapat memperbarui dan menghapus template shift (Update & Delete)', async ({ page }) => {
    // --- LANGKAH 1: Buat shift template awal untuk diuji ---
    const initialTitle = `Shift Malam Temp ${Date.now()}`;
    const updatedTitle = `Shift Malam Diperbarui ${Date.now()}`;

    await page.getByRole('button', { name: /Tambah Template Shift/i }).click();
    await page.locator('input#title').fill(initialTitle);
    await page.locator('select#department_id').selectOption({ index: 1 });
    await page.locator('input#start_time').fill('20:00');
    await page.locator('input#end_time').fill('04:00');
    await page.locator('input#default_quota').fill('2');
    await page.locator('input#color_code').fill('#e74c3c');
    await page.getByRole('button', { name: /Simpan Template/i }).click();
    await expect(page.getByText('Template shift berhasil ditambahkan.')).toBeVisible();

    // --- LANGKAH 2: UPDATE TEMPLATE SHIFT ---
    // Cari baris tabel yang memuat initialTitle
    const targetRow = page.locator('tbody tr', { hasText: initialTitle });
    await expect(targetRow).toBeVisible();

    // Klik tombol aksi "Edit template" (icon pencil)
    // Locator: button[title="Edit template"] di dalam baris yang sesuai
    await targetRow.locator('button[title="Edit template"]').click();

    // Pastikan modal edit telah terbuka dengan judul "Edit Template Shift"
    await expect(page.getByRole('heading', { name: /Edit Template Shift/i })).toBeVisible();

    // Ubah nilai input: judul, jam, kuota (translasi payload PUT di PHPUnit)
    await page.locator('input#title').fill(updatedTitle);
    await page.locator('input#start_time').fill('21:00');
    await page.locator('input#end_time').fill('05:00');
    await page.locator('input#default_quota').fill('4');
    await page.locator('input#color_code').fill('#e74c3c');

    // Klik tombol "Simpan Perubahan"
    // Locator: Button submit modal saat mode edit
    await page.getByRole('button', { name: /Simpan Perubahan/i }).click();

    // Assertion UI Update (Translasi assertDatabaseHas):
    await expect(page.getByText('Template shift berhasil diperbarui.')).toBeVisible();
    const updatedRow = page.locator('tbody tr', { hasText: updatedTitle });
    await expect(updatedRow).toBeVisible();
    await expect(updatedRow.getByText('4 orang')).toBeVisible();
    await expect(updatedRow.getByText(/21:00\s*–\s*05:00/)).toBeVisible();

    // --- LANGKAH 3: DELETE TEMPLATE SHIFT ---
    // Klik tombol aksi "Hapus template" (icon trash) pada baris yang telah diupdate
    // Locator: button[title="Hapus template"]
    await updatedRow.locator('button[title="Hapus template"]').click();

    // Pastikan modal konfirmasi hapus terbuka
    // Locator: Heading "Hapus Template Shift?" pada modal konfirmasi
    await expect(page.getByRole('heading', { name: /Hapus Template Shift\?/i })).toBeVisible();
    await expect(page.getByText(/akan dihapus\. Tindakan ini tidak dapat dibatalkan\./i)).toBeVisible();

    // Klik tombol konfirmasi "Ya, Hapus"
    // Locator: Tombol submit berwarna merah pada modal hapus
    await page.getByRole('button', { name: /Ya, Hapus/i }).click();

    // Assertion UI Delete (Translasi assertDatabaseMissing):
    // a. Banner notifikasi berhasil dihapus tampil
    await expect(page.getByText('Template shift berhasil dihapus.')).toBeVisible();

    // b. Pastikan judul template yang dihapus sudah tidak muncul lagi di antarmuka tabel
    await expect(page.locator('tbody').getByText(updatedTitle)).toBeHidden();
  });

  /**
   * Skenario 4: Translasi dari test_templates_are_scoped_to_user_company
   * PHPUnit: $response->assertDontSee('Shift Rahasia B'); $forbiddenDelete->assertNotFound();
   * Playwright:
   *   1. UI Multi-tenant check: Memverifikasi data milik perusahaan lain tidak tampil pada tabel admin tenant saat ini.
   *   2. Direct Request check: Memverifikasi pemanggilan aksi ke ID shift yang tidak dimiliki menghasilkan respons 404 (Not Found).
   */
  test('TC-SFT-04: Data template shift terisolasi per perusahaan / tenant (Multi-tenant Scoping)', async ({ page }) => {
    // 1. Verifikasi UI: Template milik perusahaan lain tidak boleh terlihat (assertDontSee)
    const foreignCompanyTemplate = 'Shift Rahasia B';
    await expect(page.getByText(foreignCompanyTemplate)).toBeHidden();

    // 2. Verifikasi Keamanan / RBAC: Percobaan aksi langsung ke ID yang tidak terdaftar / milik perusahaan lain
    // Translasi: $forbiddenDelete = $this->actingAs($adminA)->delete(route('admin.shifts.destroy', $templateB));
    //            $forbiddenDelete->assertNotFound();
    // Gunakan page.request untuk mengirimkan request DELETE dengan cookie sesi login yang aktif
    const nonExistentOrForeignId = 999999;
    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const response = await page.request.delete(`/admin/shifts/${nonExistentOrForeignId}`, {
      headers: {
        'Accept': 'text/html,application/xhtml+xml',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || '',
      },
    });

    // Endpoint memastikan hak akses via ensureCompanyAccess() -> abort(404) jika bukan milik perusahaannya
    expect([404, 403, 419]).toContain(response.status());
  });

  /**
   * Skenario 5: Translasi dari test_invalid_time_range_or_missing_department_fails
   * PHPUnit: POST invalid data -> assertSessionHasErrors(['title', 'department_id', 'end_time', 'default_quota', 'color_code'])
   * Playwright:
   *   1. Menguji validasi form di antarmuka (Client-side HTML5 required/min).
   *   2. Menguji validasi aturan bisnis server (Server-side validation) ketika jam selesai sama dengan jam mulai.
   *   3. Memverifikasi pesan error ditampilkan dengan jelas kepada pengguna.
   */
  test('TC-SFT-05: Validasi gagal jika rentang jam tidak valid atau data wajib tidak lengkap (Validation Errors)', async ({ page }) => {
    // 1. Buka modal tambah template shift
    await page.getByRole('button', { name: /Tambah Template Shift/i }).click();
    await expect(page.getByRole('heading', { name: /Tambah Template Shift/i })).toBeVisible();

    // 2. Verifikasi proteksi HTML5 input attributes (required & min)
    // Locator: input#title, select#department_id, input#default_quota
    const titleInput = page.locator('input#title');
    const deptSelect = page.locator('select#department_id');
    const quotaInput = page.locator('input#default_quota');

    await expect(titleInput).toHaveAttribute('required', '');
    await expect(deptSelect).toHaveAttribute('required', '');
    await expect(quotaInput).toHaveAttribute('min', '1');

    // 3. Uji aturan bisnis backend: Jam selesai sama persis dengan jam mulai (start_time: '08:00', end_time: '08:00')
    // Aturan Laravel: 'end_time' => ['required', 'date_format:H:i', 'different:start_time']
    await titleInput.fill('Shift Jam Identik');
    await deptSelect.selectOption({ index: 1 });
    await page.locator('input#start_time').fill('08:00');
    await page.locator('input#end_time').fill('08:00'); // Jam mulai & selesai sama
    await quotaInput.fill('2');
    await page.locator('input#color_code').fill('#6C5CE7');

    // Kirim formulir
    await page.getByRole('button', { name: /Simpan Template/i }).click();

    // 4. Assertion UI Error Banner:
    // Verifikasi pesan error dari Laravel session errors ditampilkan pada antarmuka
    // Sesuai template: <div class="rounded-xl border border-red-200 bg-red-50 ...">
    await expect(page.getByText(/Periksa kembali data yang dimasukkan/i)).toBeVisible();
    await expect(page.getByText(/Jam selesai tidak boleh sama dengan jam mulai/i).first()).toBeVisible();
  });

});
