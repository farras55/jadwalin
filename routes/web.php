<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Role redirection helper route
Route::get('/dashboard', function () {
    return match (auth()->user()?->role) {
        'superadmin' => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default => redirect()->route('employee.dashboard'),
    };
})->middleware('auth')->name('dashboard');

// ==========================================
// 1. Super Admin Routes (role: superadmin)
// ==========================================
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Super Admin']);
    })->name('dashboard');

    Route::get('/companies', function () {
        return view('placeholder', [
            'title' => 'Perusahaan & Cabang',
            'subtitle' => 'Kelola tenant perusahaan, outlet cabang, dan batasan lisensi.',
            'icon' => 'building-2',
            'roleTitle' => 'Super Admin',
        ]);
    })->name('companies.index');

    Route::get('/users', function () {
        return view('placeholder', [
            'title' => 'Kelola Pengguna',
            'subtitle' => 'Manajemen akun pengguna, penetapan peran (role), dan reset kata sandi.',
            'icon' => 'users',
            'roleTitle' => 'Super Admin',
        ]);
    })->name('users.index');

    Route::get('/departments', function () {
        return view('placeholder', [
            'title' => 'Departemen & Posisi',
            'subtitle' => 'Struktur divisi organisasi dan jabatan kerja operasional.',
            'icon' => 'briefcase',
            'roleTitle' => 'Super Admin',
        ]);
    })->name('departments.index');

    Route::get('/shifts', function () {
        return view('placeholder', [
            'title' => 'Master Shift',
            'subtitle' => 'Pengaturan template shift, durasi kerja, dan toleransi keterlambatan.',
            'icon' => 'calendar-days',
            'roleTitle' => 'Super Admin',
        ]);
    })->name('shifts.index');

    Route::get('/settings', function () {
        return view('placeholder', [
            'title' => 'Pengaturan Platform',
            'subtitle' => 'Konfigurasi sistem, zona waktu server, dan preferensi aplikasi.',
            'icon' => 'settings',
            'roleTitle' => 'Super Admin',
        ]);
    })->name('settings.index');
});

// ==========================================
// 2. Manager Routes (role: manager)
// ==========================================
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Manager']);
    })->name('dashboard');

    Route::get('/shifts', function () {
        return view('placeholder', [
            'title' => 'Jadwal Kerja Tim',
            'subtitle' => 'Penyusunan jadwal roster tim, plotting shift mingguan, dan auto-scheduling.',
            'icon' => 'calendar',
            'roleTitle' => 'Manager',
        ]);
    })->name('shifts.index');

    Route::get('/swaps', function () {
        return view('placeholder', [
            'title' => 'Persetujuan Tukar Shift',
            'subtitle' => 'Verifikasi dan setujui permohonan pergantian shift antar karyawan.',
            'icon' => 'arrow-left-right',
            'roleTitle' => 'Manager',
        ]);
    })->name('swaps.index');

    Route::get('/attendance', function () {
        return view('placeholder', [
            'title' => 'Presensi Tim',
            'subtitle' => 'Monitoring kehadiran realtime staf, absensi lokasi GPS, dan bukti swafoto.',
            'icon' => 'fingerprint',
            'roleTitle' => 'Manager',
        ]);
    })->name('attendance.index');

    Route::get('/timesheets', function () {
        return view('placeholder', [
            'title' => 'Timesheet & Laporan',
            'subtitle' => 'Rekapitulasi jam kerja reguler, lembur, dan ekspor laporan berkala.',
            'icon' => 'clock',
            'roleTitle' => 'Manager',
        ]);
    })->name('timesheets.index');

    Route::get('/employees', function () {
        return view('placeholder', [
            'title' => 'Data Karyawan Tim',
            'subtitle' => 'Daftar staf, posisi, informasi kontak, dan ketersediaan bawahan.',
            'icon' => 'users',
            'roleTitle' => 'Manager',
        ]);
    })->name('employees.index');
});

// ==========================================
// 3. Employee Routes (role: employee)
// ==========================================
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Karyawan']);
    })->name('dashboard');

    Route::get('/schedule', function () {
        return view('placeholder', [
            'title' => 'Jadwal Saya',
            'subtitle' => 'Kalender jadwal kerja dan penugasan shift harian Anda.',
            'icon' => 'calendar',
            'roleTitle' => 'Karyawan',
        ]);
    })->name('schedule');

    Route::get('/swaps', function () {
        return view('placeholder', [
            'title' => 'Tukar Shift',
            'subtitle' => 'Ajukan permintaan tukar shift atau klaim shift rekan kerja.',
            'icon' => 'arrow-left-right',
            'roleTitle' => 'Karyawan',
        ]);
    })->name('swaps');

    Route::get('/attendance', function () {
        return view('placeholder', [
            'title' => 'Presensi / Absensi',
            'subtitle' => 'Catat kehadiran Clock-In dan Clock-Out kerja mandiri.',
            'icon' => 'fingerprint',
            'roleTitle' => 'Karyawan',
        ]);
    })->name('attendance');

    Route::get('/timesheet', function () {
        return view('placeholder', [
            'title' => 'Timesheet Saya',
            'subtitle' => 'Riwayat total jam kerja produktif dan status presensi personal.',
            'icon' => 'clock',
            'roleTitle' => 'Karyawan',
        ]);
    })->name('timesheet');

    Route::get('/availability', function () {
        return view('placeholder', [
            'title' => 'Ketersediaan Waktu',
            'subtitle' => 'Atur jadwal preferensi waktu luang dan hari libur Anda.',
            'icon' => 'calendar-check',
            'roleTitle' => 'Karyawan',
        ]);
    })->name('availability');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';