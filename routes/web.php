<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- 1. Tambahkan Facade Auth di sini

Route::get('/', function () {
    return view('welcome');
});

// Role redirection helper route
Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user(); // <-- 2. Gunakan Auth::user() yang lebih mudah dibaca Intelephense

    return match ($user->role) {
        'superadmin' => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default => redirect()->route('employee.dashboard'),
    };
})->middleware('auth')->name('dashboard');

// SuperAdmin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Super Admin']);
    })->name('dashboard');
});

// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Manager']);
    })->name('dashboard');
});

// Employee Routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['roleTitle' => 'Karyawan']);
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/test-dashboard', function () {
    return  view('dashboard', ['roleTitle' => 'Test Dashboard']);
});

require __DIR__.'/auth.php';