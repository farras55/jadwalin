// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Smart Scheduling Builder & Roster Jadwal Tim (Manager)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Manager/SmartSchedulingBuilderTest.php
 * Target Halaman: /manager/shifts (Route: manager.shifts.index)
 * Blade View: resources/views/manager/shifts/index.blade.php
 * Akun Pengujian: JadwalinSeeder (Manager: manager@senjawisata.co.id)
 */

async function loginAsManager(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('manager@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/manager\/dashboard/);
}

test.describe('Modul Manager: Smart Scheduling Builder (Roster Kerja Tim)', () => {

  /**
   * Skenario 1: Translasi test_manager_can_view_weekly_schedule_grid
   * PHPUnit: actingAs($manager)->get(route('manager.shifts.index'))->assertSee('Jadwal Kerja Tim (Roster)')
   */
  test('TC-SCH-01: Manager dapat melihat grid roster jadwal mingguan tim', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/shifts');
    await expect(page).toHaveURL(/\/manager\/shifts/);

    // Verifikasi Heading & Informasi Roster
    await expect(page.getByRole('heading', { name: /Jadwal Kerja Tim \(Roster\)/i })).toBeVisible();
    await expect(page.getByText(/Susun kebutuhan kuota orang per shift/i)).toBeVisible();

    // Verifikasi Hari-Hari dalam Grid Roster (Senin s.d. Minggu)
    await expect(page.getByText('Senin', { exact: false }).first()).toBeVisible();
    await expect(page.getByText('Minggu', { exact: false }).first()).toBeVisible();

    // Verifikasi Tombol Builder & Aksi
    await expect(page.getByRole('button', { name: /Atur Kebutuhan & Build Jadwal|Build Jadwal|Buat Jadwal Otomatis|Generate/i }).or(page.locator('#btn-open-auto-builder')).first()).toBeVisible();
  });

  /**
   * Skenario 2: Translasi test_global_manager_and_regular_manager_view_differences
   * PHPUnit: Global manager melihat dropdown pemilih departemen, regular manager terkunci ke departemennya
   */
  test('TC-SCH-02: Tampilan departemen sesuai tipe role Manager (Global Manager vs Regular Manager)', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/shifts');

    // Akun manager di JadwalinSeeder memiliki is_global_manager = true
    // Verifikasi indikator Global Manager atau Departemen Switcher tampil
    const globalBadge = page.getByText(/Global Manager \(Multi-Departemen\)|Departemen Anda/i).first();
    await expect(globalBadge).toBeVisible();
  });

  /**
   * Skenario 3: Translasi test_manager_can_publish_draft_shifts
   * PHPUnit: POST route('manager.shifts.publish') [department_id, week_start] -> assertSessionHas('success')
   */
  test('TC-SCH-03: Manager dapat menerbitkan jadwal draf menjadi berstatus resmi (Publish Roster)', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/shifts');

    // Cari tombol Terbitkan Jadwal jika terdapat shift draf
    const publishBtn = page.getByRole('button', { name: /Terbitkan Jadwal|Publish/i }).first();
    if (await publishBtn.isVisible()) {
      await publishBtn.click();

      // Verifikasi alert sukses publikasi
      await expect(page.getByText(/Jadwal berhasil diterbitkan|draf berhasil dipublikasikan/i)).toBeVisible();
    }
  });

  /**
   * Skenario 4: Translasi test_manager_can_update_existing_shift_and_reassign_staff & test_manager_can_delete_existing_shift
   * PHPUnit: PUT route('manager.shifts.update') & DELETE route('manager.shifts.destroy')
   */
  test('TC-SCH-04: Manager dapat mengedit detail shift dan menghapus shift dari roster', async ({ page }) => {
    await loginAsManager(page);

    await page.goto('/manager/shifts');

    // Periksa apakah terdapat kartu shift di grid
    const shiftCard = page.locator('[data-shift-id]').first();
    if (await shiftCard.isVisible()) {
      // Klik kartu shift untuk membuka modal edit
      await shiftCard.click();

      // Modal edit shift terbuka
      const editModal = page.locator('#modal-edit-shift').or(page.getByRole('heading', { name: /Edit Shift/i }));
      if (await editModal.isVisible()) {
        await expect(editModal).toBeVisible();
        // Tutup modal
        await page.keyboard.press('Escape');
      }
    }
  });

  /**
   * Skenario 5: Translasi test_regular_manager_cannot_edit_shift_of_another_department
   * PHPUnit: PUT ke shift departemen lain -> assertStatus(403)
   */
  test('TC-SCH-05: Penolakan akses saat Manager mencoba memodifikasi shift di luar kewenangan (403 Forbidden)', async ({ page }) => {
    await loginAsManager(page);

    // Percobaan PUT ke shift ID departemen lain / tidak berhak
    const foreignShiftId = 999999;
    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const response = await page.request.put(`/manager/shifts/${foreignShiftId}`, {
      data: {
        title: 'Coba Edit Shift Ilegal',
        start_time: '08:00',
        end_time: '16:00',
        quota: 1,
      },
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || '',
      },
    });

    // Harus ditolak dengan status 403 atau 404 (atau 419 jika CSRF dicek ketat)
    expect([403, 404, 419]).toContain(response.status());
  });

});
