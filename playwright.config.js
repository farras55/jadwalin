// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',

  // Jalankan tes secara berurutan agar mengurangi konflik antar tes.
  fullyParallel: false,

  // Gagal di CI jika terdapat test.only yang tertinggal.
  forbidOnly: !!process.env.CI,

  // Ulangi tes yang gagal saat berjalan di CI.
  retries: process.env.CI ? 2 : 0,

  // Gunakan satu worker untuk menghindari konflik antar tes.
  workers: 1,

  // Reporter hasil pengujian.
  reporter: 'html',

  // Konfigurasi browser dan URL aplikasi.
  use: {
    baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
  },

  // Browser yang digunakan untuk pengujian.
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // Jalankan server Laravel sebelum tes dimulai.
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8000',

    // Gunakan endpoint login untuk memeriksa kesiapan server.
    url: 'http://127.0.0.1:8000/login',

    // Di CI, jalankan server baru; secara lokal, boleh gunakan server yang aktif.
    reuseExistingServer: !process.env.CI,

    // Batas waktu startup server.
    timeout: 120000,

    // Tampilkan output server jika terjadi masalah.
    stdout: 'pipe',
    stderr: 'pipe',
  },
});
