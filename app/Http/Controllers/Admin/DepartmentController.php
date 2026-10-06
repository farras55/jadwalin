<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Menampilkan daftar department milik company user yang login.
     */
    public function index(): View
    {
        $user = Auth::user();

        $departments = Department::where('company_id', $user->company_id)
            ->withCount([
                'positions',
                'users as employees_count' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->orderBy('name')
            ->get();

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Menampilkan form tambah department.
     */
    public function create(): View
    {
        return view('admin.departments.create');
    }

    /**
     * Menyimpan department baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where('company_id', $user->company_id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'name.required' => 'Nama department wajib diisi.',
            'name.unique' => 'Nama department tersebut sudah digunakan.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
        ]);

        Department::create([
            'company_id' => $user->company_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit department.
     */
    public function edit(Department $department): View
    {
        $this->ensureCompanyAccess($department);

        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Memperbarui department.
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->ensureCompanyAccess($department);

        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where('company_id', $user->company_id)
                    ->ignore($department->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'name.required' => 'Nama department wajib diisi.',
            'name.unique' => 'Nama department tersebut sudah digunakan.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
        ]);

        $department->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department berhasil diperbarui.');
    }

    /**
     * Menghapus department.
     *
     * Department tidak boleh dihapus jika masih memiliki
     * karyawan aktif.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->ensureCompanyAccess($department);

        $activeEmployees = $department->users()
            ->where('status', 'active')
            ->count();

        if ($activeEmployees > 0) {
            return redirect()
                ->route('admin.departments.index')
                ->with(
                    'error',
                    'Department tidak dapat dihapus karena masih memiliki karyawan aktif.'
                );
        }

        $department->delete();

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department berhasil dihapus.');
    }

    /**
     * Memastikan department berasal dari company user yang login.
     */
    private function ensureCompanyAccess(Department $department): void
    {
        $user = Auth::user();

        if ($department->company_id !== $user->company_id) {
            abort(404);
        }
    }
}