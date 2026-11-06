<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ShiftTemplateController;
use App\Http\Controllers\Admin\TimesheetReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\AttendanceRegularizationController as EmployeeAttendanceRegularizationController;
use App\Http\Controllers\Employee\AvailabilityController;
use App\Http\Controllers\Employee\PersonalTimesheetController;
use App\Http\Controllers\Employee\ScheduleController;
use App\Http\Controllers\Manager\AttendanceMonitorController;
use App\Http\Controllers\Manager\AttendanceRegularizationController as ManagerAttendanceRegularizationController;
use App\Http\Controllers\Manager\AvailabilityApprovalController;
use App\Http\Controllers\Manager\DepartmentTimesheetController;
use App\Http\Controllers\Manager\ShiftController;
use App\Http\Controllers\Manager\TeamEmployeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpenShiftController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftSwapController;
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
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard Super Admin - PBL-38
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

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

        // ==========================================
        // Department & Position CRUD - PBL-9
        // ==========================================
        Route::resource('departments', DepartmentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('positions', PositionController::class)
            ->only(['index', 'store', 'destroy']);

        // ==========================================
        // Shift Template CRUD - PBL-18
        // ==========================================
        Route::get('/shifts', [ShiftTemplateController::class, 'index'])
            ->name('shifts.index');

        Route::post('/shifts', [ShiftTemplateController::class, 'store'])
            ->name('shifts.store');

        Route::put('/shifts/{shift}', [ShiftTemplateController::class, 'update'])
            ->name('shifts.update');

        Route::delete('/shifts/{shift}', [ShiftTemplateController::class, 'destroy'])
            ->name('shifts.destroy');

        // ==========================================
        // Company Settings - PBL-24
        // ==========================================
        Route::get('/settings', [CompanySettingController::class, 'edit'])
            ->name('settings.index');

        Route::put('/settings', [CompanySettingController::class, 'update'])
            ->name('settings.update');

        Route::put('/settings/branch-location', [CompanySettingController::class, 'updateBranchLocation'])
            ->name('settings.update-branch-location');

        // ==========================================
        // Timesheet Report & Export - PBL-21
        // ==========================================
        Route::get(
            '/reports/timesheet',
            [TimesheetReportController::class, 'index']
        )->name('reports.timesheet');

        Route::get(
            '/reports/timesheet/export-excel',
            [TimesheetReportController::class, 'exportExcel']
        )->name('reports.timesheet.export-excel');

        Route::get(
            '/reports/timesheet/export-pdf',
            [TimesheetReportController::class, 'exportPdf']
        )->name('reports.timesheet.export-pdf');

        // Audit Trail Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');
    });

// ==========================================
// 2. Manager Routes (role: manager)
// ==========================================
Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        // Dashboard Manager - PBL-38
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // PBL-17: Smart Scheduling Builder
        Route::get('/shifts', [ShiftController::class, 'index'])
            ->name('shifts.index');

        Route::post('/shifts/generate', [ShiftController::class, 'generate'])
            ->name('shifts.generate');

        Route::post('/shifts/publish', [ShiftController::class, 'publish'])
            ->name('shifts.publish');

        Route::delete('/shifts/drafts', [ShiftController::class, 'clearDrafts'])
            ->name('shifts.clear-drafts');

        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])
            ->name('shifts.update');

        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])
            ->name('shifts.destroy');

        Route::put(
            '/shift-templates/{template}',
            [ShiftController::class, 'updateTemplate']
        )->name('shifts.templates.update');

        // PBL-22: Shift Swap Approvals (Manager)
        Route::get('/swaps', [ShiftSwapController::class, 'indexManager'])
            ->name('swaps.index');

        Route::post('/swaps/{swap}/approve', [ShiftSwapController::class, 'approveManager'])
            ->name('swaps.approve');

        Route::post('/swaps/{swap}/reject', [ShiftSwapController::class, 'rejectManager'])
            ->name('swaps.reject');

        // PBL-23: Open Shift Broadcast & Concurrency (Manager)
        Route::get('/open-shifts', [OpenShiftController::class, 'indexManager'])
            ->name('open-shifts.index');

        Route::post('/open-shifts', [OpenShiftController::class, 'broadcast'])
            ->name('open-shifts.broadcast');

        Route::post('/open-shifts/{shift}/close', [OpenShiftController::class, 'closeBroadcast'])
            ->name('open-shifts.close');

        Route::post('/open-shifts/claims/{claim}/approve', [OpenShiftController::class, 'approveClaim'])
            ->name('open-shifts.claims.approve');

        Route::post('/open-shifts/claims/{claim}/reject', [OpenShiftController::class, 'rejectClaim'])
            ->name('open-shifts.claims.reject');

        // PBL-27: Realtime Attendance Monitoring
        Route::get('/attendance', [AttendanceMonitorController::class, 'index'])
            ->name('attendance.index');

        Route::get('/attendance/feed', [AttendanceMonitorController::class, 'realtimeFeed'])
            ->name('attendance.feed');

        // PBL-44: Department Timesheets (Manager)
        Route::get('/timesheets', [DepartmentTimesheetController::class, 'index'])
            ->name('timesheets.index');
        Route::get('/timesheets/export/excel', [DepartmentTimesheetController::class, 'exportExcel'])
            ->name('timesheets.export.excel');
        Route::get('/timesheets/export/pdf', [DepartmentTimesheetController::class, 'exportPdf'])
            ->name('timesheets.export.pdf');

        // ==========================================
        // PBL-25: Team Employee Management
        // ==========================================

        // Daftar karyawan dalam perusahaan/departemen manager
        Route::get(
            '/employees',
            [TeamEmployeeController::class, 'index']
        )->name('employees.index');

        // Detail profil dan aktivitas shift karyawan
        Route::get(
            '/employees/{user}',
            [TeamEmployeeController::class, 'show']
        )->name('employees.show');

        // ==========================================
        // Availability Approval
        // ==========================================
        Route::get('/availability', [
            AvailabilityApprovalController::class,
            'index',
        ])->name('availability.index');

        Route::post('/availability/{availability}/approve', [
            AvailabilityApprovalController::class,
            'approve',
        ])->name('availability.approve');

        Route::post('/availability/{availability}/reject', [
            AvailabilityApprovalController::class,
            'reject',
        ])->name('availability.reject');

        // ==========================================
        // PBL-28: Attendance Regularization (Manager)
        // ==========================================
        Route::get('/attendance/regularizations', [ManagerAttendanceRegularizationController::class, 'index'])
            ->name('attendance.regularizations.index');

        Route::post('/attendance/regularizations/{regularization}/approve', [ManagerAttendanceRegularizationController::class, 'approve'])
            ->name('attendance.regularizations.approve');

        Route::post('/attendance/regularizations/{regularization}/reject', [ManagerAttendanceRegularizationController::class, 'reject'])
            ->name('attendance.regularizations.reject');
    });

// ==========================================
// 3. Employee Routes (role: employee)
// ==========================================
Route::middleware(['auth', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        // Dashboard Karyawan - PBL-38
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // PBL-41: Employee Schedule Calendar
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
        Route::get('/schedule/{assignment}', [ScheduleController::class, 'show'])->name('schedule.show');

        // PBL-22: Shift Swap & Transfer (Employee)
        Route::get('/swaps', [ShiftSwapController::class, 'indexEmployee'])
            ->name('swaps');

        Route::post('/swaps', [ShiftSwapController::class, 'requestSwap'])
            ->name('swaps.request');

        Route::post('/swaps/{swap}/respond', [ShiftSwapController::class, 'respondSwap'])
            ->name('swaps.respond');

        // PBL-23: Open Shift Claim (Employee)
        Route::get('/open-shifts', [OpenShiftController::class, 'availableShifts'])
            ->name('open-shifts');

        Route::post('/open-shifts/{shift}/claim', [OpenShiftController::class, 'claim'])
            ->name('open-shifts.claim');

        // Employee Attendance
        Route::get(
            '/attendance',
            [AttendanceController::class, 'index']
        )->name('attendance');

        Route::get(
            '/attendance/location',
            [AttendanceController::class, 'location']
        )->name('attendance.location');

        Route::post(
            '/attendance/clock-in',
            [AttendanceController::class, 'clockIn']
        )->name('attendance.clock-in');

        Route::post(
            '/attendance/clock-out',
            [AttendanceController::class, 'clockOut']
        )->name('attendance.clock-out');

        // PBL-28: Attendance Regularization (Employee)
        Route::get(
            '/attendance/regularization',
            [EmployeeAttendanceRegularizationController::class, 'index']
        )->name('attendance.regularization.index');

        Route::post(
            '/attendance/regularization',
            [EmployeeAttendanceRegularizationController::class, 'store']
        )->name('attendance.regularization.store');

        // PBL-44: Personal Timesheet (Employee)
        Route::get('/timesheet', [PersonalTimesheetController::class, 'index'])
            ->name('timesheet');
        Route::get('/timesheets', [PersonalTimesheetController::class, 'index'])
            ->name('timesheet.index');
        Route::get('/timesheets/export/excel', [PersonalTimesheetController::class, 'exportExcel'])
            ->name('timesheet.export.excel');
        Route::get('/timesheets/export/pdf', [PersonalTimesheetController::class, 'exportPdf'])
            ->name('timesheet.export.pdf');

        // Employee Availability
        Route::get(
            '/availability',
            [AvailabilityController::class, 'index']
        )->name('availability');

        Route::get(
            '/availability/create',
            [AvailabilityController::class, 'create']
        )->name('availability.create');

        Route::post(
            '/availability',
            [AvailabilityController::class, 'store']
        )->name('availability.store');

        Route::delete(
            '/availability/{availability}',
            [AvailabilityController::class, 'destroy']
        )->name('availability.destroy');
    });

// Dedicated API endpoint for employee attendance location polling
Route::middleware('auth')
    ->get('/api/employee/attendance-location', [AttendanceController::class, 'location'])
    ->name('api.employee.attendance-location');

// ==========================================
// 4. Profile Routes
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// ==========================================
// 5. Notification Routes - PBL-20
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
});

// Authentication routes
require __DIR__.'/auth.php';
