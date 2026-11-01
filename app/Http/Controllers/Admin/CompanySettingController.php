<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    /**
     * Nilai default pengaturan perusahaan.
     */
    private function defaultSettings(): array
    {
        return [
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'work_hour_limit_type' => 'weekly',
            'min_rest_hours' => 11,
            'max_working_days_per_week' => 6,
            'auto_buffer_minutes' => 30,
            'grace_period_minutes' => 15,
            'is_auto_clockout' => true,
            'auto_clockout_buffer_hours' => 2,
            'office_latitude' => config('jadwalin.attendance.default_latitude'),
            'office_longitude' => config('jadwalin.attendance.default_longitude'),
            'attendance_radius_meters' => (int) config('jadwalin.attendance.default_radius_meters', 100),
        ];
    }

    /**
     * Memastikan pengguna sudah login dan terhubung
     * dengan perusahaan.
     */
    private function ensureCompany(Request $request): int
    {
        $user = $request->user();

        abort_unless($user, 403);

        abort_if(
            $user->company_id === null,
            403,
            'Akun ini belum terhubung dengan perusahaan.'
        );

        return (int) $user->company_id;
    }

    /**
     * Menampilkan halaman pengaturan perusahaan.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->ensureCompany($request);
        $company = Company::find($companyId);

        $setting = CompanySetting::firstOrCreate(
            ['company_id' => $companyId],
            $this->defaultSettings()
        );

        if ($company) {
            if ($setting->office_latitude === null && $company->office_latitude !== null) {
                $setting->office_latitude = $company->office_latitude;
            }
            if ($setting->office_longitude === null && $company->office_longitude !== null) {
                $setting->office_longitude = $company->office_longitude;
            }
            if ($setting->attendance_radius_meters === null && $company->attendance_radius_meters !== null) {
                $setting->attendance_radius_meters = $company->attendance_radius_meters;
            }
        }

        // Jika superadmin, tampilkan cabang dari seluruh perusahaan (dengan label perusahaan)
        // agar superadmin dapat mengatur koordinat cabang manapun yang ditugaskan ke karyawan.
        if ($user && $user->role === 'superadmin') {
            $branches = Department::with('company')->orderBy('name')->get();
        } else {
            $branches = Department::where('company_id', $companyId)->orderBy('name')->get();
        }

        $branchesData = $branches->map(function ($b) {
            $companyName = $b->company?->name;
            $displayName = $b->name.($companyName ? " ({$companyName})" : '');

            return [
                'id' => $b->id,
                'name' => $displayName,
                'raw_name' => $b->name,
                'company_name' => $companyName,
                'address' => $b->address,
                'latitude' => $b->latitude !== null ? (float) $b->latitude : null,
                'longitude' => $b->longitude !== null ? (float) $b->longitude : null,
                'radius' => (int) ($b->radius_meters ?? $b->radius ?? 100),
            ];
        })->values();

        $companySettingsData = [
            'latitude' => $setting->office_latitude !== null ? (float) $setting->office_latitude : ($company?->office_latitude !== null ? (float) $company->office_latitude : null),
            'longitude' => $setting->office_longitude !== null ? (float) $setting->office_longitude : ($company?->office_longitude !== null ? (float) $company->office_longitude : null),
            'radius' => (int) ($setting->attendance_radius_meters ?? ($company?->attendance_radius_meters ?? 100)),
        ];

        $selectedBranchId = $request->query('branch_id', session('selected_branch_id'));

        return view('admin.settings.edit', [
            'setting' => $setting,
            'company' => $company,
            'branches' => $branches,
            'branchesData' => $branchesData,
            'companySettingsData' => $companySettingsData,
            'selectedBranchId' => $selectedBranchId ? (int) $selectedBranchId : null,
        ]);
    }

    /**
     * Memvalidasi dan menyimpan pengaturan perusahaan.
     */
    public function update(Request $request): RedirectResponse
    {
        $companyId = $this->ensureCompany($request);

        // Normalisasi format desimal jika input koordinat menggunakan koma
        if ($request->has('office_latitude') && $request->input('office_latitude') !== null) {
            $request->merge(['office_latitude' => str_replace(',', '.', (string) $request->input('office_latitude'))]);
        }
        if ($request->has('office_longitude') && $request->input('office_longitude') !== null) {
            $request->merge(['office_longitude' => str_replace(',', '.', (string) $request->input('office_longitude'))]);
        }
        if ($request->has('branch_latitude') && $request->input('branch_latitude') !== null) {
            $request->merge(['branch_latitude' => str_replace(',', '.', (string) $request->input('branch_latitude'))]);
        }
        if ($request->has('branch_longitude') && $request->input('branch_longitude') !== null) {
            $request->merge(['branch_longitude' => str_replace(',', '.', (string) $request->input('branch_longitude'))]);
        }

        $validated = $request->validate([
            'max_regular_hours' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'max_overtime_hours' => [
                'required',
                'integer',
                'min:0',
                'max:24',
            ],

            'work_hour_limit_type' => [
                'required',
                'in:weekly,daily',
            ],

            'min_rest_hours' => [
                'required',
                'integer',
                'min:1',
                'max:24',
            ],

            'max_working_days_per_week' => [
                'required',
                'integer',
                'min:1',
                'max:7',
            ],

            'auto_buffer_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],

            'grace_period_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:60',
            ],

            'is_auto_clockout' => [
                'required',
                'boolean',
            ],

            'auto_clockout_buffer_hours' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],

            'office_latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'office_longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'attendance_radius_meters' => [
                'nullable',
                'integer',
                'min:5',
                'max:5000',
            ],
        ], [
            'max_regular_hours.required' => 'Maksimal jam kerja reguler wajib diisi.',
            'max_regular_hours.integer' => 'Jam kerja reguler harus berupa bilangan bulat.',
            'max_regular_hours.min' => 'Jam kerja reguler minimal 1 jam.',
            'max_regular_hours.max' => 'Jam kerja reguler maksimal 60 jam.',

            'max_overtime_hours.required' => 'Maksimal jam lembur wajib diisi.',
            'max_overtime_hours.integer' => 'Jam lembur harus berupa bilangan bulat.',
            'max_overtime_hours.min' => 'Jam lembur tidak boleh negatif.',
            'max_overtime_hours.max' => 'Jam lembur maksimal 24 jam.',

            'work_hour_limit_type.required' => 'Periode batas jam kerja wajib dipilih.',
            'work_hour_limit_type.in' => 'Periode batas jam kerja harus mingguan atau harian.',

            'min_rest_hours.required' => 'Minimal waktu istirahat wajib diisi.',
            'min_rest_hours.integer' => 'Waktu istirahat harus berupa bilangan bulat.',
            'min_rest_hours.min' => 'Waktu istirahat minimal 1 jam.',
            'min_rest_hours.max' => 'Waktu istirahat maksimal 24 jam.',

            'max_working_days_per_week.required' => 'Maksimal hari kerja per minggu wajib diisi.',
            'max_working_days_per_week.integer' => 'Maksimal hari kerja harus berupa bilangan bulat.',
            'max_working_days_per_week.min' => 'Minimal hari kerja adalah 1 hari.',
            'max_working_days_per_week.max' => 'Maksimal hari kerja adalah 7 hari per minggu.',

            'auto_buffer_minutes.required' => 'Buffer otomatis wajib diisi.',
            'auto_buffer_minutes.integer' => 'Buffer otomatis harus berupa bilangan bulat.',
            'auto_buffer_minutes.min' => 'Buffer otomatis tidak boleh negatif.',
            'auto_buffer_minutes.max' => 'Buffer otomatis maksimal 120 menit.',

            'grace_period_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
            'grace_period_minutes.integer' => 'Toleransi keterlambatan harus berupa bilangan bulat.',
            'grace_period_minutes.min' => 'Toleransi keterlambatan tidak boleh negatif.',
            'grace_period_minutes.max' => 'Toleransi keterlambatan maksimal 60 menit.',

            'is_auto_clockout.required' => 'Pengaturan auto clock-out wajib dikirim.',
            'is_auto_clockout.boolean' => 'Pengaturan auto clock-out tidak valid.',

            'auto_clockout_buffer_hours.required' => 'Batas waktu auto clock-out wajib diisi.',
            'auto_clockout_buffer_hours.integer' => 'Batas waktu auto clock-out harus berupa bilangan bulat.',
            'auto_clockout_buffer_hours.min' => 'Batas waktu auto clock-out minimal 1 jam.',
            'auto_clockout_buffer_hours.max' => 'Batas waktu auto clock-out maksimal 12 jam.',

            'office_latitude.numeric' => 'Latitude kantor harus berupa angka desimal valid.',
            'office_latitude.between' => 'Latitude kantor harus berada di antara -90 dan 90.',

            'office_longitude.numeric' => 'Longitude kantor harus berupa angka desimal valid.',
            'office_longitude.between' => 'Longitude kantor harus berada di antara -180 dan 180.',

            'attendance_radius_meters.integer' => 'Radius absensi harus berupa bilangan bulat.',
            'attendance_radius_meters.min' => 'Radius absensi minimal 5 meter.',
            'attendance_radius_meters.max' => 'Radius absensi maksimal 5.000 meter.',
        ]);

        // Ambil atau buat pengaturan hanya untuk perusahaan pengguna.
        $setting = CompanySetting::firstOrCreate(
            ['company_id' => $companyId],
            $this->defaultSettings()
        );

        // Pastikan attendance_radius_meters tidak pernah bernilai null
        $radius = $validated['attendance_radius_meters']
            ?? $request->input('branch_radius')
            ?? $setting->attendance_radius_meters
            ?? config('jadwalin.attendance.default_radius_meters', 100);

        $setting->attendance_radius_meters = (int) $radius;
        unset($validated['attendance_radius_meters']);

        // Koordinat kantor
        if ($request->filled('branch_latitude')) {
            $setting->office_latitude = (float) str_replace(',', '.', (string) $request->input('branch_latitude'));
        } elseif ($request->filled('office_latitude')) {
            $setting->office_latitude = (float) str_replace(',', '.', (string) $request->input('office_latitude'));
        }

        if ($request->filled('branch_longitude')) {
            $setting->office_longitude = (float) str_replace(',', '.', (string) $request->input('branch_longitude'));
        } elseif ($request->filled('office_longitude')) {
            $setting->office_longitude = (float) str_replace(',', '.', (string) $request->input('office_longitude'));
        }

        unset($validated['office_latitude'], $validated['office_longitude']);

        $setting->fill($validated);
        $setting->save();

        // Sinkronisasi data ke tabel Company
        $company = Company::find($companyId);
        if ($company) {
            $companyUpdateData = [];
            if ($setting->office_latitude !== null) {
                $companyUpdateData['office_latitude'] = $setting->office_latitude;
            }
            if ($setting->office_longitude !== null) {
                $companyUpdateData['office_longitude'] = $setting->office_longitude;
            }
            if ($setting->attendance_radius_meters !== null) {
                $companyUpdateData['attendance_radius_meters'] = $setting->attendance_radius_meters;
            }
            if (! empty($companyUpdateData)) {
                $company->update($companyUpdateData);
            }
        }

        // Jika tombol global save mengirimkan juga perubahan lokasi cabang
        if ($request->filled('branch_id')) {
            $branch = Department::find($request->input('branch_id'));
            if ($branch) {
                $bLat = $request->input('branch_latitude');
                $bLng = $request->input('branch_longitude');
                if ($bLat !== null && $bLat !== '') {
                    $bLat = (float) str_replace(',', '.', (string) $bLat);
                } else {
                    $bLat = null;
                }
                if ($bLng !== null && $bLng !== '') {
                    $bLng = (float) str_replace(',', '.', (string) $bLng);
                } else {
                    $bLng = null;
                }
                $bRadius = (int) $setting->attendance_radius_meters;

                $bData = [
                    'radius' => $bRadius,
                    'radius_meters' => $bRadius,
                ];
                if ($bLat !== null) {
                    $bData['latitude'] = $bLat;
                }
                if ($bLng !== null) {
                    $bData['longitude'] = $bLng;
                }
                if ($request->filled('branch_address')) {
                    $bData['address'] = $request->input('branch_address');
                }
                $branch->update($bData);
            }
        }

        $redirectParams = [];
        if ($request->filled('branch_id')) {
            $redirectParams['branch_id'] = $request->input('branch_id');
        }

        return redirect()
            ->route('admin.settings.index', $redirectParams)
            ->with('selected_branch_id', $request->input('branch_id'))
            ->with(
                'success',
                'Pengaturan perusahaan berhasil diperbarui.'
            );
    }

    /**
     * Memperbarui titik koordinat dan radius cabang tertentu (multi-branch geofencing).
     */
    public function updateBranchLocation(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        $companyId = $this->ensureCompany($request);

        // Normalisasi format desimal jika input koordinat menggunakan koma
        if ($request->has('latitude')) {
            $request->merge(['latitude' => str_replace(',', '.', (string) $request->input('latitude'))]);
        }
        if ($request->has('longitude')) {
            $request->merge(['longitude' => str_replace(',', '.', (string) $request->input('longitude'))]);
        }
        if ($request->has('radius')) {
            $request->merge(['radius' => (int) $request->input('radius')]);
        }

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                $user->role === 'superadmin'
                    ? Rule::exists('departments', 'id')
                    : Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'radius' => [
                'required',
                'integer',
                'min:5',
                'max:10000',
            ],
        ], [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang tidak ditemukan atau bukan milik perusahaan Anda.',
            'latitude.required' => 'Latitude cabang wajib diisi.',
            'latitude.numeric' => 'Latitude cabang harus berupa angka valid.',
            'latitude.between' => 'Nilai latitude harus di antara -90 dan 90.',
            'longitude.required' => 'Longitude cabang wajib diisi.',
            'longitude.numeric' => 'Longitude cabang harus berupa angka valid.',
            'longitude.between' => 'Nilai longitude harus di antara -180 dan 180.',
            'radius.required' => 'Radius presensi wajib diisi.',
            'radius.min' => 'Radius presensi minimal 5 meter.',
            'radius.max' => 'Radius presensi maksimal 10.000 meter.',
        ]);

        $branch = Department::findOrFail($validated['branch_id']);
        $updateBranchData = [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'radius' => (int) $validated['radius'],
            'radius_meters' => (int) $validated['radius'],
        ];
        if (array_key_exists('address', $validated) && $validated['address'] !== null) {
            $updateBranchData['address'] = $validated['address'];
        }
        $branch->update($updateBranchData);

        // Sinkronisasi koordinat acuan ke company_settings & companies milik cabang tersebut
        $targetCompanyId = $branch->company_id ?? $companyId;
        if ($targetCompanyId) {
            $setting = CompanySetting::firstOrCreate(['company_id' => $targetCompanyId], $this->defaultSettings());
            $setting->update([
                'office_latitude' => (float) $validated['latitude'],
                'office_longitude' => (float) $validated['longitude'],
                'attendance_radius_meters' => (int) $validated['radius'],
            ]);

            $targetCompany = Company::find($targetCompanyId);
            if ($targetCompany) {
                $companyData = [
                    'office_latitude' => (float) $validated['latitude'],
                    'office_longitude' => (float) $validated['longitude'],
                    'attendance_radius_meters' => (int) $validated['radius'],
                ];
                if (array_key_exists('address', $validated) && $validated['address'] !== null) {
                    $companyData['address'] = $validated['address'];
                }
                $targetCompany->update($companyData);
            }
        }

        return redirect()
            ->route('admin.settings.index', ['branch_id' => $branch->id])
            ->with('selected_branch_id', $branch->id)
            ->with('success', "Pengaturan lokasi & radius presensi cabang {$branch->name} berhasil disimpan.");
    }
}
