<?php

namespace App\View\Components;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $user->loadMissing(['company', 'department', 'position']);
        }

        $userName = $user?->name ?? 'Tamu';
        $userRole = $user?->role ?? 'tamu';

        $roleLabel = match ($userRole) {
            'superadmin' => 'Super Admin',
            'manager' => 'Manager',
            'employee' => 'Karyawan',
            default => ucfirst($userRole),
        };

        // Posisi user atau role sebagai fallback
        $positionName = $user?->position?->name ?? $roleLabel;

        // Inisial user (maksimal 2 huruf pertama kata)
        $initials = mb_strtoupper(
            Str::of($userName)
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn ($w) => mb_substr($w, 0, 1))
                ->join('')
        ) ?: 'U';

        // Foto profil (jika ada file di storage, atau null)
        $profilePhotoUrl = $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null;

        // Nama perusahaan / outlet
        $companyName = $user?->company?->name ?? config('app.name', 'Jadwalin');
        $timezone = $user?->company?->timezone ?? 'Asia/Jakarta';

        // Tanggal dan waktu realtime server
        $now = Carbon::now($timezone);
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sept', 'Okt', 'Nov', 'Des'];
        $tanggalStr = $hari[$now->dayOfWeek] . ', ' . $now->day . ' ' . $bulanPendek[$now->month - 1] . ' ' . $now->year;

        // Daftar menu dinamis sesuai role
        $rawMenus = $this->getMenuDefinitions($userRole);
        $sidebarMenus = array_map(function ($menu) {
            $url = '#';
            if (!empty($menu['route']) && Route::has($menu['route'])) {
                $url = route($menu['route']);
            } elseif (!empty($menu['fallback'])) {
                $url = url($menu['fallback']);
            }

            return [
                'label' => $menu['label'],
                'icon' => $menu['icon'],
                'url' => $url,
                'active_pattern' => $menu['active_pattern'],
            ];
        }, $rawMenus);

        return view('layouts.app', compact(
            'user',
            'userName',
            'userRole',
            'roleLabel',
            'positionName',
            'initials',
            'profilePhotoUrl',
            'companyName',
            'timezone',
            'now',
            'tanggalStr',
            'sidebarMenus'
        ));
    }

    /**
     * Definisi menu berdasarkan role pengguna.
     */
    protected function getMenuDefinitions(string $role): array
    {
        return match ($role) {
            'superadmin' => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'admin.dashboard', 'fallback' => '/admin/dashboard', 'active_pattern' => 'admin/dashboard*'],
                ['label' => 'Perusahaan & Cabang', 'icon' => 'building-2', 'route' => 'admin.companies.index', 'fallback' => '/admin/companies', 'active_pattern' => 'admin/companies*'],
                ['label' => 'Kelola Pengguna', 'icon' => 'users', 'route' => 'admin.users.index', 'fallback' => '/admin/users', 'active_pattern' => 'admin/users*'],
                ['label' => 'Departemen & Posisi', 'icon' => 'briefcase', 'route' => 'admin.departments.index', 'fallback' => '/admin/departments', 'active_pattern' => 'admin/departments*'],
                ['label' => 'Master Shift', 'icon' => 'calendar-days', 'route' => 'admin.shifts.index', 'fallback' => '/admin/shifts', 'active_pattern' => 'admin/shifts*'],
                ['label' => 'Pengaturan Platform', 'icon' => 'settings', 'route' => 'admin.settings.index', 'fallback' => '/admin/settings', 'active_pattern' => 'admin/settings*'],
            ],
            'manager' => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'manager.dashboard', 'fallback' => '/manager/dashboard', 'active_pattern' => 'manager/dashboard*'],
                ['label' => 'Jadwal Kerja Tim', 'icon' => 'calendar', 'route' => 'manager.shifts.index', 'fallback' => '/manager/shifts', 'active_pattern' => 'manager/shifts*'],
                ['label' => 'Persetujuan Tukar Shift', 'icon' => 'arrow-left-right', 'route' => 'manager.swaps.index', 'fallback' => '/manager/swaps', 'active_pattern' => 'manager/swaps*'],
                ['label' => 'Presensi Tim', 'icon' => 'fingerprint', 'route' => 'manager.attendance.index', 'fallback' => '/manager/attendance', 'active_pattern' => 'manager/attendance*'],
                ['label' => 'Timesheet & Laporan', 'icon' => 'clock', 'route' => 'manager.timesheets.index', 'fallback' => '/manager/timesheets', 'active_pattern' => 'manager/timesheets*'],
                ['label' => 'Data Karyawan Tim', 'icon' => 'users', 'route' => 'manager.employees.index', 'fallback' => '/manager/employees', 'active_pattern' => 'manager/employees*'],
            ],
            default => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'employee.dashboard', 'fallback' => '/employee/dashboard', 'active_pattern' => 'employee/dashboard*'],
                ['label' => 'Jadwal Saya', 'icon' => 'calendar', 'route' => 'employee.schedule', 'fallback' => '/employee/schedule', 'active_pattern' => 'employee/schedule*'],
                ['label' => 'Tukar Shift', 'icon' => 'arrow-left-right', 'route' => 'employee.swaps', 'fallback' => '/employee/swaps', 'active_pattern' => 'employee/swaps*'],
                ['label' => 'Presensi / Absensi', 'icon' => 'fingerprint', 'route' => 'employee.attendance', 'fallback' => '/employee/attendance', 'active_pattern' => 'employee/attendance*'],
                ['label' => 'Timesheet', 'icon' => 'clock', 'route' => 'employee.timesheet', 'fallback' => '/employee/timesheet', 'active_pattern' => 'employee/timesheet*'],
                ['label' => 'Ketersediaan Waktu', 'icon' => 'calendar-check', 'route' => 'employee.availability', 'fallback' => '/employee/availability', 'active_pattern' => 'employee/availability*'],
                ['label' => 'Pengaturan Profil', 'icon' => 'settings', 'route' => 'profile.edit', 'fallback' => '/profile', 'active_pattern' => 'profile*'],
            ],
        };
    }
}
