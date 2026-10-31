// @ts-check
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',

  // Jalankan tes secara berurutan.
  fullyParallel: false,

  // Cegah test.only tertinggal di CI.
  forbidOnly: !!process.env.CI,

  // Ulangi tes yang gagal saat berjalan di CI.
  retries: process.env.CI ? 2 : 0,

  // Gunakan satu worker untuk mengurangi konflik.
  workers: 1,

  // Reporter hasil pengujian.
  reporter: 'html',

  // Konfigurasi browser dan URL aplikasi.
  use: {
    baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
  },

  // Browser untuk pengujian.
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // Playwright menjadi satu-satunya pengelola server.
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8000',
    url: 'http://127.0.0.1:8000/login',

    // CI harus menjalankan server sendiri.
    // Lokal boleh menggunakan server yang sudah aktif.
    reuseExistingServer: !process.env.CI,

    timeout: 120000,
    stdout: 'pipe',
    stderr: 'pipe',
  },
});