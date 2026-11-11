<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs for Super Admin review.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user?->company;
        $companyId = $user?->company_id;
        $companyTimezone = $company?->timezone ?: config('app.timezone', 'Asia/Jakarta');
        $timezoneAbbr = AuditLog::getTimezoneAbbreviation($companyTimezone);

        $query = AuditLog::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with(['user', 'company'])
            ->latest();

        // Optional filter: action (create, update, delete)
        if ($request->filled('action') && $request->input('action') !== 'all') {
            $query->where('action', strtolower($request->input('action')));
        }

        // Optional filter: model_type
        if ($request->filled('model_type') && $request->input('model_type') !== 'all') {
            $modelType = $request->input('model_type');
            $query->where(function ($q) use ($modelType) {
                $q->where('model_type', $modelType)
                    ->orWhere('model_type', 'like', '%\\'.$modelType);
            });
        }

        // Optional search: keyword for actor name, IP, or model
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        $statsBase = AuditLog::query()->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        $today = now()->timezone($companyTimezone)->startOfDay();

        $stats = [
            'total_today' => (clone $statsBase)->where('created_at', '>=', $today)->count(),
            'total_updates' => (clone $statsBase)->whereIn('action', ['update', 'UPDATE'])->count(),
            'total_deletes' => (clone $statsBase)->whereIn('action', ['delete', 'DELETE'])->count(),
            'total_system' => (clone $statsBase)->whereNotIn('action', ['create', 'update', 'delete', 'CREATE', 'UPDATE', 'DELETE'])->count(),
        ];

        $availableActions = [
            'create' => 'Penambahan Data (Buat Baru)',
            'update' => 'Pengeditan Data',
            'delete' => 'Penghapusan Data',
            'auto_clockout' => 'Absen Keluar Otomatis',
            'broadcast_existing_shift' => 'Pengiriman Jadwal Shift',
            'approve_shift_swap' => 'Persetujuan Tukar Shift',
            'reject_shift_swap' => 'Penolakan Tukar Shift',
        ];

        $availableModels = [
            'Shift' => 'Jadwal Shift',
            'ShiftAssignment' => 'Penugasan Karyawan',
            'ShiftSwap' => 'Tukar Shift',
            'OpenShiftClaim' => 'Klaim Shift Terbuka',
            'Availability' => 'Ketersediaan Waktu',
            'CompanySetting' => 'Pengaturan Perusahaan',
            'Department' => 'Departemen & Cabang',
        ];

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'selectedAction' => $request->input('action', 'all'),
            'selectedModel' => $request->input('model_type', 'all'),
            'search' => $request->input('search', ''),
            'availableActions' => $availableActions,
            'availableModels' => $availableModels,
            'fieldLabels' => AuditLog::FIELD_LABELS,
            'companyTimezone' => $companyTimezone,
            'timezoneAbbr' => $timezoneAbbr,
        ]);
    }
}
