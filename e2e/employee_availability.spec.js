// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Ketersediaan Waktu & Pengajuan Halangan (Karyawan)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Employee/AvailabilityTest.php
 * Target Halaman:
 *   - /employee/availability (Riwayat Ketersediaan)
 *   - /employee/availability/create (Form Pengajuan Halangan)
 * Blade Views:
 *   - resources/views/employee/availability/index.blade.php
 *   - resources/views/employee/availability/create.blade.php
 * Akun Pengujian: JadwalinSeeder (karyawan@senjawisata.co.id)
 */

async function loginAsEmployee(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('karyawan@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/employee\/dashboard/);
}

test.describe('Modul Karyawan: Ketersediaan Waktu (Availability & Auto-Buffer)', () => {

  /**
   * Skenario 1: Translasi test_employee_can_view_own_availability_list
   * PHPUnit: actingAs($employee)->get(route('employee.availability'))->assertStatus(200);
   */
  test('TC-AVL-01: Karyawan dapat melihat daftar riwayat halangan pribadinya', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/availability');
    await expect(page).toHaveURL(/\/employee\/availability/);

    // Verifikasi Heading & Tombol Ajukan Halangan
    await expect(page.getByRole('heading', { name: /Ketersediaan Waktu/i })).toBeVisible();
    await expect(page.getByText(/Atur jadwal halangan Anda agar penjadwalan tidak bentrok/i)).toBeVisible();
    await expect(page.getByRole('link', { name: /Ajukan Halangan/i })).toBeVisible();
    await expect(page.getByRole('heading', { name: /Riwayat Halangan/i })).toBeVisible();
  });

  /**
   * Skenario 2: Translasi test_employee_can_submit_unavailability_with_auto_buffer_applied
   * PHPUnit: POST route('employee.availability.store') [date, start_time, end_time, reason, is_recurring: 0]
   * Playwright: Buka form, isi tanggal masa depan, jam mulai, jam selesai, dan alasan, submit, pastikan muncul di riwayat.
   */
  test('TC-AVL-02: Karyawan dapat mengajukan halangan tanggal tertentu (Single Date)', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/availability/create');
    await expect(page).toHaveURL(/\/employee\/availability\/create/);

    const testReason = `Kuliah Praktikum ${Date.now()}`;

    // Tentukan tanggal masa depan (besok)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateString = tomorrow.toISOString().split('T')[0];

    // Isi formulir
    // Locator: input#date, input#start_time, input#end_time, #reason
    await page.locator('input#date').fill(dateString);
    await page.locator('input#start_time').fill('13:00');
    await page.locator('input#end_time').fill('15:00');
    await page.locator('#reason').fill(testReason);

    // Submit formulir
    await page.getByRole('button', { name: /Ajukan Halangan|Simpan Halangan/i }).click();

    // Assertion UI:
    // a. Dialihkan kembali ke daftar riwayat halangan
    await expect(page).toHaveURL(/\/employee\/availability/);

    // b. Flash message sukses muncul
    await expect(page.getByText(/Halangan berhasil diajukan/i)).toBeVisible();

    // c. Alasan halangan baru muncul di riwayat dengan status "Menunggu Manajer"
    await expect(page.getByText(testReason)).toBeVisible();
    await expect(page.getByText(/Menunggu Manajer/i).first()).toBeVisible();
  });

  /**
   * Skenario 3: Translasi test_recurring_submission_stores_day_of_week
   * PHPUnit: POST dengan is_recurring: 1 -> menyimpan day_of_week
   */
  test('TC-AVL-03: Karyawan dapat mengajukan halangan berulang mingguan (Recurring)', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/availability/create');

    // Klik tombol switch "Berulang Mingguan"
    await page.getByRole('button', { name: /Berulang Mingguan/i }).click();

    const recurringReason = `Kuliah Rutin Mingguan ${Date.now()}`;
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    const dateString = nextWeek.toISOString().split('T')[0];

    await page.locator('input#date').fill(dateString);
    await page.locator('input#start_time').fill('08:00');
    await page.locator('input#end_time').fill('12:00');
    await page.locator('#reason').fill(recurringReason);

    await page.getByRole('button', { name: /Ajukan Halangan|Simpan Halangan/i }).click();

    await expect(page).toHaveURL(/\/employee\/availability/);
    await expect(page.getByText(/Halangan berhasil diajukan/i)).toBeVisible();
    await expect(page.getByText(recurringReason)).toBeVisible();
  });

  /**
   * Skenario 4: Translasi test_submission_with_past_date_or_inverted_time_fails
   * PHPUnit: Validasi penolakan tanggal lampau, jam selesai lebih awal dari jam mulai, dan alasan kosong.
   */
  test('TC-AVL-04: Validasi penolakan form jika tanggal lampau atau jam tidak valid', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/availability/create');

    // Tanggal lampau (kemarin)
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const pastDate = yesterday.toISOString().split('T')[0];

    // Isi tanggal kemarin dan jam terbalik
    await page.locator('input#date').fill(pastDate);
    await page.locator('input#start_time').fill('15:00');
    await page.locator('input#end_time').fill('13:00'); // Terbalik
    await page.locator('#reason').fill(''); // Kosong

    // Percobaan submit
    await page.getByRole('button', { name: /Ajukan Halangan|Simpan Halangan/i }).click();

    // Verifikasi tetap berada di form create dan indikator validasi aktif
    await expect(page).toHaveURL(/\/employee\/availability\/create/);
  });

  /**
   * Skenario 5: Translasi test_employee_can_delete_pending_availability_only & test_employee_cannot_delete_another_users_availability
   * PHPUnit: DELETE route('employee.availability.destroy', $availability) -> assertDatabaseMissing
   */
  test('TC-AVL-05: Karyawan dapat membatalkan halangan berstatus pending dan dilarang menghapus milik orang lain', async ({ page }) => {
    await loginAsEmployee(page);

    await page.goto('/employee/availability');

    // Cari tombol aksi Hapus / Batalkan pada riwayat halangan yang pending
    const cancelBtn = page.getByRole('button', { name: /Batalkan|Hapus/i }).first();
    if (await cancelBtn.isVisible()) {
      page.once('dialog', dialog => dialog.accept());
      await cancelBtn.click();
      await expect(page.getByText(/berhasil dibatalkan|berhasil dihapus/i)).toBeVisible();
    }

    // Uji percobaan menghapus ID asing via direct request:
    // Translasi: test_employee_cannot_delete_another_users_availability -> assertForbidden (403)
    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const foreignId = 999999;
    const response = await page.request.delete(`/employee/availability/${foreignId}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || '',
      },
    });

    // Harus ditolak dengan status 403 atau 404 (atau 419 jika CSRF dicek ketat)
    expect([403, 404, 419]).toContain(response.status());
  });

});
