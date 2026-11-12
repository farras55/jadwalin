<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $query = User::where('company_id', $companyId)
            ->with(['department', 'position']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::where('company_id', $companyId)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'departments'));
    }

    public function create(): View
    {
        $companyId = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->orderBy('name')->get();
        $positions = Position::where('company_id', $companyId)->orderBy('name')->get();

        return view('admin.users.create', compact('departments', 'positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        if ($request->has('phone') && ! $request->has('phone_number')) {
            $request->merge(['phone_number' => $request->input('phone')]);
        }

        if (! $request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('password')]);
        }

        if (! $request->has('employment_type')) {
            $request->merge(['employment_type' => 'full_time']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:manager,employee'],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId);
                }),
            ],
            'position_id' => [
                'nullable',
                'integer',
                Rule::exists('positions', 'id')->where(function ($query) use ($companyId, $request): void {
                    $query->where('company_id', $companyId);
                    if ($request->filled('department_id')) {
                        $query->where('department_id', $request->department_id);
                    }
                }),
            ],
            'status' => ['required', 'in:active,inactive'],
            'employment_type' => ['required', 'in:full_time,part_time,freelance'],
            'is_global_manager' => ['nullable', 'boolean'],
            'max_weekly_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'join_date' => ['nullable', 'date'],
        ]);

        User::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'position_id' => $validated['position_id'] ?? null,
            'status' => $validated['status'],
            'employment_type' => $validated['employment_type'],
            'is_global_manager' => $validated['role'] === 'manager' ? $request->boolean('is_global_manager') : false,
            'max_weekly_hours' => $validated['max_weekly_hours'] ?? null,
            'join_date' => $validated['join_date'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        abort(404);
    }

    public function edit(User $user): View
    {
        $this->authorizeTenant($user);

        $companyId = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->orderBy('name')->get();
        $positions = Position::where('company_id', $companyId)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'departments', 'positions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTenant($user);
        $companyId = auth()->user()->company_id;

        if ($request->has('phone') && ! $request->has('phone_number')) {
            $request->merge(['phone_number' => $request->input('phone')]);
        }

        if ($request->filled('password') && ! $request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('password')]);
        }

        if (! $request->has('employment_type')) {
            $request->merge(['employment_type' => $user->employment_type ?? 'full_time']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                $user->role === 'superadmin' ? 'in:superadmin' : 'in:manager,employee',
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId);
                }),
            ],
            'position_id' => [
                'nullable',
                'integer',
                Rule::exists('positions', 'id')->where(function ($query) use ($companyId, $request): void {
                    $query->where('company_id', $companyId);
                    if ($request->filled('department_id')) {
                        $query->where('department_id', $request->department_id);
                    }
                }),
            ],
            'status' => ['required', 'in:active,inactive'],
            'employment_type' => ['required', 'in:full_time,part_time,freelance'],
            'is_global_manager' => ['nullable', 'boolean'],
            'max_weekly_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'join_date' => ['nullable', 'date'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'position_id' => $validated['position_id'] ?? null,
            'status' => $validated['status'],
            'employment_type' => $validated['employment_type'],
            'is_global_manager' => $validated['role'] === 'manager' ? $request->boolean('is_global_manager') : false,
            'max_weekly_hours' => $validated['max_weekly_hours'] ?? null,
            'join_date' => $validated['join_date'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeTenant($user);

        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->shiftAssignments()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena masih memiliki penugasan shift yang aktif.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function authorizeTenant(User $user): void
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404);
        }
    }
}
