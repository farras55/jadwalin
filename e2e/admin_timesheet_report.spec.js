// @ts-check
import { test, expect } from '@playwright/test';

/**
 * ============================================================================
 * E2E Test: Rekapitulasi Timesheet & Ekspor (Admin)
 * ============================================================================
 * Penerjemahan skenario pengujian dari: tests/Feature/Admin/TimesheetReportTest.php
 * Target Halaman: /admin/reports/timesheet (Route: admin.reports.timesheet)
 * Blade View: resources/views/admin/reports/timesheet.blade.php
 * Akun Pengujian: JadwalinSeeder (Super Admin & Karyawan)
 */

async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('owner@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/admin\/dashboard/);
}

async function loginAsEmployee(page) {
  await page.goto('/login');
  await page.locator('input#email').fill('karyawan@senjawisata.co.id');
  await page.locator('input#password').fill('password123');
  await page.getByRole('button', { name: /Masuk ke Workspace/i }).click();
  await expect(page).toHaveURL(/\/employee\/dashboard/);
}

test.describe('Modul Admin: Rekapitulasi Timesheet & Ekspor Laporan (PBL-21)', () => {

  /**
   * Skenario 1: Translasi test_admin_can_view_timesheet_report_page
   * PHPUnit: $response->assertOk(); assertSee('Rekapitulasi Timesheet'); assertSee('Ekspor Excel'); assertSee('Ekspor PDF');
   */
  test('TC-TMR-01: Admin dapat membuka dan melihat halaman laporan rekapitulasi timesheet', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('/admin/reports/timesheet');
    await expect(page).toHaveURL(/\/admin\/reports\/timesheet/);

    // Verifikasi Judul Halaman
    await expect(page.getByRole('heading', { name: /Rekapitulasi Timesheet & Ekspor/i })).toBeVisible();
    await expect(page.getByText(/Monitoring agregasi jam kerja reguler, jam lembur/i)).toBeVisible();

    // Verifikasi Tombol Ekspor Excel & PDF
    const exportExcelBtn = page.getByRole('link', { name: /Ekspor Excel/i });
    const exportPdfBtn = page.getByRole('link', { name: /Ekspor PDF/i });
    await expect(exportExcelBtn).toBeVisible();
    await expect(exportPdfBtn).toBeVisible();

    // Verifikasi KPI Cards (Karyawan, Jam Reguler, Jam Lembur, Keterlambatan)
    await expect(page.getByText('Karyawan & Kehadiran', { exact: true })).toBeVisible();
    await expect(page.getByText('Jam Kerja Reguler', { exact: true })).toBeVisible();
    await expect(page.getByText('Jam Lembur (Overtime)', { exact: true })).toBeVisible();
    await expect(page.getByText('Keterlambatan Presensi', { exact: true })).toBeVisible();

    // Verifikasi Komponen Filter Periode
    await expect(page.locator('select#filter-month')).toBeVisible();
    await expect(page.locator('select#filter-year')).toBeVisible();
    await expect(page.locator('select#filter-department')).toBeVisible();
    await expect(page.getByRole('button', { name: /Terapkan Filter/i })).toBeVisible();
  });

  /**
   * Skenario 2: Translasi test_timesheet_correctly_aggregates_regular_and_overtime_hours
   * PHPUnit: Memverifikasi penghitungan jam reguler, jam lembur, dan filter per departemen.
   */
  test('TC-TMR-02: Filter laporan timesheet berdasarkan bulan, tahun, dan departemen', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('/admin/reports/timesheet');

    // Terapkan filter bulan Oktober (10) dan tahun 2026
    await page.locator('select#filter-month').selectOption('10');
    await page.locator('select#filter-year').selectOption('2026');

    // Submit filter
    await page.getByRole('button', { name: /Terapkan Filter/i }).click();

    // URL memuat parameter query yang sesuai
    await expect(page).toHaveURL(/month=10/);
    await expect(page).toHaveURL(/year=2026/);

    // Verifikasi tabel rekapitulasi data tampil
    await expect(page.getByRole('heading', { name: /Rekapitulasi Jam Kerja Karyawan/i })).toBeVisible();
  });

  /**
   * Skenario 3: Translasi test_admin_can_export_timesheet_to_csv_excel
   * PHPUnit: Stream download file .xlsx dengan header content-disposition attachment.
   */
  test('TC-TMR-03: Admin dapat mengunduh rekapitulasi timesheet ke format Excel (.xlsx)', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('/admin/reports/timesheet');

    // Tangkap event download saat tombol Ekspor Excel diklik
    const [download] = await Promise.all([
      page.waitForEvent('download'),
      page.getByRole('link', { name: /Ekspor Excel/i }).click(),
    ]);

    // Verifikasi nama berkas berakhiran .xlsx
    const suggestedFilename = download.suggestedFilename();
    expect(suggestedFilename).toContain('.xlsx');
  });

  /**
   * Skenario 4: Translasi test_admin_can_export_timesheet_to_pdf
   * PHPUnit: Stream download file .pdf dengan Content-Type: application/pdf.
   */
  test('TC-TMR-04: Admin dapat mengunduh rekapitulasi timesheet ke format PDF (.pdf)', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('/admin/reports/timesheet');

    // Tangkap event download saat tombol Ekspor PDF diklik
    const [download] = await Promise.all([
      page.waitForEvent('download'),
      page.getByRole('link', { name: /Ekspor PDF/i }).click(),
    ]);

    // Verifikasi nama berkas berakhiran .pdf
    const suggestedFilename = download.suggestedFilename();
    expect(suggestedFilename).toContain('.pdf');
  });

  /**
   * Skenario 5: Translasi test_employee_cannot_access_timesheet_admin_report
   * PHPUnit: actingAs($employee)->get(route('admin.reports.timesheet'))->assertStatus(403);
   */
  test('TC-TMR-05: Karyawan biasa (role employee) ditolak saat mengakses laporan timesheet admin (403 Forbidden)', async ({ page }) => {
    await loginAsEmployee(page);

    // 1. Percobaan akses halaman laporan utama admin
    const pageResponse = await page.goto('/admin/reports/timesheet');
    expect(pageResponse?.status()).toBe(403);

    // 2. Percobaan akses langsung ke endpoint export Excel
    const excelResponse = await page.request.get('/admin/reports/timesheet/export-excel');
    expect(excelResponse.status()).toBe(403);

    // 3. Percobaan akses langsung ke endpoint export PDF
    const pdfResponse = await page.request.get('/admin/reports/timesheet/export-pdf');
    expect(pdfResponse.status()).toBe(403);
  });

});
