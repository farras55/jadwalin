<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $departments = Department::where('company_id', $user->company_id)
            ->with([
                'positions' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return view('admin.positions.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where('company_id', $user->company_id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')
                    ->where('company_id', $user->company_id)
                    ->where(function ($query) use ($request) {
                        $query->where('department_id', $request->department_id);
                    }),
            ],
        ], [
            'department_id.required' => 'Department wajib dipilih.',
            'department_id.exists' => 'Department tidak ditemukan atau bukan milik perusahaan Anda.',
            'name.required' => 'Nama posisi wajib diisi.',
            'name.unique' => 'Nama posisi tersebut sudah digunakan pada department ini.',
        ]);

        Position::create([
            'company_id' => $user->company_id,
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Posisi berhasil ditambahkan.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $user = Auth::user();

        if ($position->company_id !== $user->company_id) {
            abort(404);
        }

        $position->delete();

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Posisi berhasil dihapus.');
    }
}