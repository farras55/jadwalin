<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftTemplate;
use App\Models\User;
use App\Services\ScheduleBuilderService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Tampilkan matriks kalender penyusunan jadwal kerja tim.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isGlobalManager = (bool) $user->is_global_manager;

        // 1. Identifikasi Departemen Berdasarkan Hak Akses Manajer
        $allDepartments = Department::where('company_id', $user->company_id)
            ->with(['shiftTemplates' => fn ($q) => $q->orderBy('start_time')])
            ->withCount(['users as active_staff_count' => fn ($q) => $q->where('status', 'active')->where('role', 'employee')])
            ->orderBy('name')
            ->get();

        if ($isGlobalManager) {
            // Global Manager bebas memilih departemen manapun
            $departmentId = (int) $request->input('department_id', $user->department_id ?? $allDepartments->first()?->id);
            $currentDepartment = $allDepartments->firstWhere('id', $departmentId) ?? $allDepartments->first();
        } else {
            // Regular Manager terkunci hanya pada departemennya sendiri
            $departmentId = (int) $user->department_id;
            $currentDepartment = $allDepartments->firstWhere('id', $departmentId);

            // Fallback pengaman jika department_id user belum ter-set
            if (! $currentDepartment) {
                $currentDepartment = $allDepartments->first();
                $departmentId = (int) ($currentDepartment?->id ?? 0);
            }
        }

        // 2. Kalkulasi Rentang Waktu Dinamis
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($request->filled('week_start')) {
            $startDate = Carbon::parse($request->input('week_start'))->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        } else {
            // Default: Pekan Ini (Senin s.d. Minggu)
            $startDate = now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        }

        // Validasi agar endDate tidak lebih kecil dari startDate
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
        }

        // Batasi rentang maksimal 31 hari agar tampilan kalender tetap optimal
        if ($startDate->diffInDays($endDate) > 31) {
            $endDate = $startDate->copy()->addDays(30)->endOfDay();
        }

        $daysCount = $startDate->diffInDays($endDate) + 1;
        $prevStart = $startDate->copy()->subDays($daysCount)->format('Y-m-d');
        $prevEnd = $startDate->copy()->subDay()->format('Y-m-d');
        $nextStart = $endDate->copy()->addDay()->format('Y-m-d');
        $nextEnd = $endDate->copy()->addDays($daysCount)->format('Y-m-d');

        $thisWeekStart = now()->startOfWeek(CarbonInterface::MONDAY)->format('Y-m-d');
        $thisWeekEnd = now()->endOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');

        // 3. Bangun Struktur Kolom Tanggal Dinamis
        $days = [];
        $dayCursor = $startDate->copy();
        $indonesianDays = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        while ($dayCursor->lte($endDate)) {
            $days[] = [
                'date_str' => $dayCursor->format('Y-m-d'),
                'day_name' => $indonesianDays[$dayCursor->dayOfWeekIso],
                'formatted' => $dayCursor->translatedFormat('d M'),
                'full_date' => $dayCursor->translatedFormat('l, d F Y'),
                'day_of_week' => $dayCursor->dayOfWeekIso,
                'is_today' => $dayCursor->isToday(),
            ];
            $dayCursor->addDay();
        }

        // 4. Hitung Shift Statistik Per Departemen (untuk Tabs & Navigasi Multi-Departemen)
        $deptShiftStats = Shift::where('company_id', $user->company_id)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->selectRaw('department_id, 
                count(*) as total_shifts, 
                count(case when status = \'draft\' then 1 end) as draft_count, 
                count(case when status = \'published\' then 1 end) as published_count')
            ->groupBy('department_id')
            ->get()
            ->keyBy('department_id');

        $totalCompanyShifts = 0;
        $totalCompanyDraftShifts = 0;
        $totalCompanyPublishedShifts = 0;

        foreach ($allDepartments as $dept) {
            $stats = $deptShiftStats->get($dept->id);
            $dept->total_shifts = $stats ? (int) $stats->total_shifts : 0;
            $dept->draft_count = $stats ? (int) $stats->draft_count : 0;
            $dept->published_count = $stats ? (int) $stats->published_count : 0;

            $totalCompanyShifts += $dept->total_shifts;
            $totalCompanyDraftShifts += $dept->draft_count;
            $totalCompanyPublishedShifts += $dept->published_count;
        }

        // 5. Template Shift & Staf Departemen yang Sedang Ditampilkan
        $templates = ShiftTemplate::where('company_id', $user->company_id)
            ->where('department_id', $departmentId)
            ->orderBy('start_time')
            ->get();

        $activeStaff = User::where('company_id', $user->company_id)
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->where('role', 'employee')
            ->get();

        // 6. Muat Data Shift & Penugasan pada Periode Ini
        $shifts = Shift::where('company_id', $user->company_id)
            ->where('department_id', $departmentId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with(['template', 'shiftAssignments.user'])
            ->orderBy('start_time')
            ->get();

        // 7. Kelompokkan Shift Berdasarkan Tanggal (Y-m-d)
        $shiftsByDate = [];
        foreach ($days as $day) {
            $shiftsByDate[$day['date_str']] = collect();
        }

        foreach ($shifts as $shift) {
            $dateKey = $shift->start_time->format('Y-m-d');
            if (isset($shiftsByDate[$dateKey])) {
                $shiftsByDate[$dateKey]->push($shift);
            }
        }

        // 8. Hitung Metrik Ringkasan Departemen Aktif
        $totalShifts = $shifts->count();
        $draftShiftsCount = $shifts->where('status', 'draft')->count();
        $publishedShiftsCount = $shifts->where('status', 'published')->count();

        $totalAssignments = 0;
        $totalScheduledHours = 0.0;
        $uniqueStaffIds = [];
        $unfilledSlotsCount = 0;

        foreach ($shifts as $shift) {
            $assignedCount = $shift->shiftAssignments->count();
            $totalAssignments += $assignedCount;
            $duration = $shift->start_time->diffInMinutes($shift->end_time) / 60.0;
            $totalScheduledHours += ($duration * $assignedCount);

            if ($assignedCount < $shift->quota) {
                $unfilledSlotsCount += ($shift->quota - $assignedCount);
            }

            foreach ($shift->shiftAssignments as $assignment) {
                $uniqueStaffIds[$assignment->user_id] = true;
            }
        }

        return view('manager.shifts.index', [
            'isGlobalManager' => $isGlobalManager,
            'departments' => $allDepartments,
            'currentDepartment' => $currentDepartment,
            'departmentId' => $departmentId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysCount' => $daysCount,
            'prevStart' => $prevStart,
            'prevEnd' => $prevEnd,
            'nextStart' => $nextStart,
            'nextEnd' => $nextEnd,
            'thisWeekStart' => $thisWeekStart,
            'thisWeekEnd' => $thisWeekEnd,
            'days' => $days,
            'templates' => $templates,
            'activeStaff' => $activeStaff,
            'shifts' => $shifts,
            'shiftsByDate' => $shiftsByDate,
            'totalShifts' => $totalShifts,
            'draftShiftsCount' => $draftShiftsCount,
            'publishedShiftsCount' => $publishedShiftsCount,
            'totalCompanyShifts' => $totalCompanyShifts,
            'totalCompanyDraftShifts' => $totalCompanyDraftShifts,
            'totalCompanyPublishedShifts' => $totalCompanyPublishedShifts,
            'totalAssignments' => $totalAssignments,
            'totalScheduledHours' => round($totalScheduledHours, 1),
            'uniqueStaffCount' => count($uniqueStaffIds),
            'unfilledSlotsCount' => $unfilledSlotsCount,
        ]);
    }

    /**
     * Eksekusi algoritma Smart Scheduling Builder dengan kebutuhan dinamis.
     */
    public function generate(Request $request, ScheduleBuilderService $builder): RedirectResponse
    {
        $user = Auth::user();
        $isGlobalManager = (bool) $user->is_global_manager;

        // 1. Tentukan Departemen Sasaran
        if ($isGlobalManager) {
            // Bisa menerima single department_id atau array department_ids
            if ($request->has('department_ids') && is_array($request->input('department_ids'))) {
                $targetDeptIds = array_map('intval', $request->input('department_ids'));
            } else {
                $targetDeptIds = [(int) $request->input('department_id', $user->department_id)];
            }
        } else {
            // Regular manager strictly locked to own department
            $targetDeptIds = [(int) $user->department_id];
        }

        // 2. Parse Rentang Tanggal Dinamis
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($request->filled('week_start')) {
            $startDate = Carbon::parse($request->input('week_start'))->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        } else {
            $startDate = now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        }

        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
        }

        // 3. Ambil Konfigurasi Kebutuhan Staf Per Shift (Requirements)
        $requirements = $request->input('requirements');

        // 4. Eksekusi Engine Scheduling
        $result = $builder->generateWeeklyRoster(
            $user->company_id,
            $targetDeptIds,
            $startDate,
            $endDate,
            $requirements,
            true // Overwrite drafts
        );

        $primaryDeptId = (int) ($request->input('view_department_id') ?: ($targetDeptIds[0] ?? $user->department_id));

        return redirect()->route('manager.shifts.index', [
            'department_id' => $primaryDeptId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ])->with('success', $result['message'])
            ->with('soft_warnings', $result['soft_warnings'] ?? [])
            ->with('unfilled_slots', $result['unfilled_slots'] ?? []);
    }

    /**
     * Publikasikan draf jadwal menjadi resmi (status: published).
     */
    public function publish(Request $request, ScheduleBuilderService $builder): RedirectResponse
    {
        $user = Auth::user();
        $isGlobalManager = (bool) $user->is_global_manager;

        if ($isGlobalManager) {
            if ($request->input('publish_scope') === 'all') {
                $targetDeptIds = Department::where('company_id', $user->company_id)->pluck('id')->all();
            } else {
                $targetDeptIds = $request->has('department_ids')
                    ? array_map('intval', (array) $request->input('department_ids'))
                    : [(int) $request->input('department_id', $user->department_id)];
            }
        } else {
            $targetDeptIds = [(int) $user->department_id];
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($request->filled('week_start')) {
            $startDate = Carbon::parse($request->input('week_start'))->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        } else {
            $startDate = now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        }

        $overwritePublished = $request->boolean('overwrite_published');

        $publishedCount = $builder->publishWeeklyRoster(
            $user->company_id,
            $targetDeptIds,
            $startDate,
            $endDate,
            $overwritePublished
        );

        $primaryDeptId = $targetDeptIds[0] ?? $user->department_id;

        if ($publishedCount === 0) {
            return redirect()->route('manager.shifts.index', [
                'department_id' => $primaryDeptId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ])->with('warning', 'Tidak ada draf jadwal yang perlu diterbitkan pada periode ini.');
        }

        $successMessage = $overwritePublished
            ? sprintf('Berhasil menerbitkan %d shift kerja baru dan menimpa jadwal terbit sebelumnya. Roster kini resmi aktif untuk staf!', $publishedCount)
            : sprintf('Berhasil menerbitkan %d shift kerja. Roster kini resmi aktif untuk staf!', $publishedCount);

        return redirect()->route('manager.shifts.index', [
            'department_id' => $primaryDeptId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ])->with('success', $successMessage);
    }

    /**
     * Hapus seluruh draf jadwal yang belum terbit.
     */
    public function clearDrafts(Request $request, ScheduleBuilderService $builder): RedirectResponse
    {
        $user = Auth::user();
        $isGlobalManager = (bool) $user->is_global_manager;

        if ($isGlobalManager) {
            if ($request->input('clear_scope') === 'all') {
                $targetDeptIds = Department::where('company_id', $user->company_id)->pluck('id')->all();
            } else {
                $targetDeptIds = $request->has('department_ids')
                    ? array_map('intval', (array) $request->input('department_ids'))
                    : [(int) $request->input('department_id', $user->department_id)];
            }
        } else {
            $targetDeptIds = [(int) $user->department_id];
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } elseif ($request->filled('week_start')) {
            $startDate = Carbon::parse($request->input('week_start'))->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        } else {
            $startDate = now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
            $endDate = $startDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        }

        $deletedCount = $builder->clearWeeklyDrafts(
            $user->company_id,
            $targetDeptIds,
            $startDate,
            $endDate
        );

        $primaryDeptId = $targetDeptIds[0] ?? $user->department_id;

        return redirect()->route('manager.shifts.index', [
            'department_id' => $primaryDeptId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ])->with('info', sprintf('%d draf shift berhasil dibersihkan.', $deletedCount));
    }

    /**
     * Perbarui detail shift yang ada (judul, jam kerja, kuota, dan staf penugasan).
     */
    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $user = Auth::user();

        // 1. Verifikasi kepemilikan tenant
        if ($shift->company_id !== $user->company_id) {
            abort(403, 'Akses ditolak ke tenant ini.');
        }

        // 2. Verifikasi hak departemen jika bukan global manager
        if (! $user->is_global_manager && (int) $shift->department_id !== (int) $user->department_id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah shift departemen ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'quota' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'in:draft,published,completed'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // 3. Kalkulasi jam shift (mempertahankan tanggal shift)
        $shiftDate = $shift->start_time->copy()->startOfDay();
        $newStart = $shiftDate->copy()->setTimeFromTimeString($validated['start_time']);
        $newEnd = $shiftDate->copy()->setTimeFromTimeString($validated['end_time']);

        if ($newEnd->lte($newStart)) {
            $newEnd->addDay();
        }

        $shiftDuration = $newEnd->diffInMinutes($newStart) / 60.0;

        // 4. Filter staf yang valid (hanya staf departemen & perusahaan yang sama)
        $assignedUserIds = collect($request->input('assigned_user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validUsers = User::where('company_id', $user->company_id)
            ->where('department_id', $shift->department_id)
            ->where('role', 'employee')
            ->whereIn('id', $assignedUserIds)
            ->get()
            ->keyBy('id');

        $finalQuota = max((int) $validated['quota'], $validUsers->count());

        $updateData = [
            'title' => $validated['title'],
            'start_time' => $newStart,
            'end_time' => $newEnd,
            'quota' => $finalQuota,
        ];

        if (! empty($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        $shift->update($updateData);

        // 5. Sinkronisasi Penugasan Staf (Shift Assignments)
        // Hapus penugasan user yang tidak dicentang lagi
        $shift->shiftAssignments()
            ->whereNotIn('user_id', $validUsers->keys())
            ->delete();

        // Tambah atau perbarui penugasan yang dicentang
        $existingAssignedIds = $shift->shiftAssignments()->pluck('user_id')->toArray();

        foreach ($validUsers as $uId => $employee) {
            if (! in_array($uId, $existingAssignedIds)) {
                $shift->shiftAssignments()->create([
                    'company_id' => $shift->company_id,
                    'user_id' => $uId,
                    'is_overtime' => false,
                    'overtime_status' => 'none',
                    'clock_in_time' => null,
                    'clock_out_time' => null,
                    'late_minutes' => 0,
                    'regular_hours' => round($shiftDuration, 2),
                    'overtime_hours' => 0.0,
                    'status' => 'scheduled',
                    'is_auto_clockout' => false,
                    'manager_notes' => null,
                ]);
            } else {
                $shift->shiftAssignments()
                    ->where('user_id', $uId)
                    ->update([
                        'regular_hours' => round($shiftDuration, 2),
                    ]);
            }
        }

        return redirect()->back()->with('success', sprintf('Shift "%s" berhasil diperbarui.', $shift->title));
    }

    /**
     * Hapus shift tertentu beserta penugasan terkait.
     */
    public function destroy(Shift $shift): RedirectResponse
    {
        $user = Auth::user();

        if ($shift->company_id !== $user->company_id) {
            abort(403, 'Akses ditolak ke tenant ini.');
        }

        if (! $user->is_global_manager && (int) $shift->department_id !== (int) $user->department_id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus shift departemen ini.');
        }

        $title = $shift->title;
        $shift->openShiftClaims()->delete();
        $shift->shiftAssignments()->delete();
        $shift->delete();

        return redirect()->back()->with('success', sprintf('Shift "%s" berhasil dihapus.', $title));
    }

    /**
     * Perbarui master template shift (ShiftTemplate).
     */
    public function updateTemplate(Request $request, ShiftTemplate $template): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($template->company_id !== $user->company_id) {
            abort(403, 'Akses ditolak ke tenant ini.');
        }

        if (! $user->is_global_manager && (int) $template->department_id !== (int) $user->department_id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah template shift departemen ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'default_quota' => ['required', 'integer', 'min:1', 'max:50'],
            'color_code' => ['nullable', 'string', 'max:20'],
        ]);

        $template->update([
            'title' => $validated['title'],
            'start_time' => strlen($validated['start_time']) === 5 ? $validated['start_time'].':00' : $validated['start_time'],
            'end_time' => strlen($validated['end_time']) === 5 ? $validated['end_time'].':00' : $validated['end_time'],
            'default_quota' => (int) $validated['default_quota'],
            'color_code' => $validated['color_code'] ?? $template->color_code ?? '#6C5CE7',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => sprintf('Template shift "%s" berhasil diperbarui.', $template->title),
                'template' => $template,
            ]);
        }

        return redirect()->back()->with('success', sprintf('Template shift "%s" berhasil diperbarui.', $template->title));
    }
}
