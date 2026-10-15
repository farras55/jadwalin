<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ShiftTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftTemplateController extends Controller
{
    /**
     * Menampilkan template shift milik perusahaan pengguna.
     */
    public function index(): View
    {
        $user = Auth::user();

        $templates = ShiftTemplate::where('company_id', $user->company_id)
            ->with('department')
            ->orderBy('title')
            ->get();

        $departments = Department::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('admin.shifts.index', compact(
            'templates',
            'departments'
        ));
    }

    /**
     * Menyimpan template shift baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where('company_id', $user->company_id),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'default_quota' => ['required', 'integer', 'min:1'],
            'color_code' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ], [
            'title.required' => 'Nama template shift wajib diisi.',
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen tidak valid.',
            'start_time.date_format' => 'Format jam mulai harus HH:MM.',
            'end_time.date_format' => 'Format jam selesai harus HH:MM.',
            'end_time.different' => 'Jam selesai tidak boleh sama dengan jam mulai.',
            'default_quota.min' => 'Kuota minimal 1 orang.',
            'color_code.regex' => 'Kode warna harus berupa hex, misalnya #6C5CE7.',
        ]);

        ShiftTemplate::create([
            'company_id' => $user->company_id,
            'department_id' => $validated['department_id'],
            'title' => $validated['title'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'default_quota' => $validated['default_quota'],
            'color_code' => $validated['color_code'],
        ]);

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Template shift berhasil ditambahkan.');
    }

    /**
     * Memperbarui template shift milik perusahaan pengguna.
     */
    public function update(
        Request $request,
        ShiftTemplate $shift
    ): RedirectResponse {
        $user = Auth::user();

        $this->ensureCompanyAccess($shift);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where('company_id', $user->company_id),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'default_quota' => ['required', 'integer', 'min:1'],
            'color_code' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ], [
            'title.required' => 'Nama template shift wajib diisi.',
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen tidak valid.',
            'start_time.date_format' => 'Format jam mulai harus HH:MM.',
            'end_time.date_format' => 'Format jam selesai harus HH:MM.',
            'end_time.different' => 'Jam selesai tidak boleh sama dengan jam mulai.',
            'default_quota.min' => 'Kuota minimal 1 orang.',
            'color_code.regex' => 'Kode warna harus berupa hex, misalnya #6C5CE7.',
        ]);

        $shift->update([
            'department_id' => $validated['department_id'],
            'title' => $validated['title'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'default_quota' => $validated['default_quota'],
            'color_code' => $validated['color_code'],
        ]);

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Template shift berhasil diperbarui.');
    }

    /**
     * Menghapus template shift milik perusahaan pengguna.
     */
    public function destroy(ShiftTemplate $shift): RedirectResponse
    {
        $this->ensureCompanyAccess($shift);

        $shift->delete();

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Template shift berhasil dihapus.');
    }

    /**
     * Memastikan template shift berasal dari perusahaan pengguna.
     */
    private function ensureCompanyAccess(ShiftTemplate $shift): void
    {
        $user = Auth::user();

        if ($shift->company_id !== $user->company_id) {
            abort(404);
        }
    }
}
