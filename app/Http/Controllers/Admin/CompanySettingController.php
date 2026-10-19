<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $companyId = $this->ensureCompany($request);

        $setting = CompanySetting::firstOrCreate(
            ['company_id' => $companyId],
            $this->defaultSettings()
        );

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Memvalidasi dan menyimpan pengaturan perusahaan.
     */
    public function update(Request $request): RedirectResponse
    {
        $companyId = $this->ensureCompany($request);

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
        ], [
            'max_regular_hours.required' =>
                'Maksimal jam kerja reguler wajib diisi.',
            'max_regular_hours.integer' =>
                'Jam kerja reguler harus berupa bilangan bulat.',
            'max_regular_hours.min' =>
                'Jam kerja reguler minimal 1 jam.',
            'max_regular_hours.max' =>
                'Jam kerja reguler maksimal 60 jam.',

            'max_overtime_hours.required' =>
                'Maksimal jam lembur wajib diisi.',
            'max_overtime_hours.integer' =>
                'Jam lembur harus berupa bilangan bulat.',
            'max_overtime_hours.min' =>
                'Jam lembur tidak boleh negatif.',
            'max_overtime_hours.max' =>
                'Jam lembur maksimal 24 jam.',

            'work_hour_limit_type.required' =>
                'Periode batas jam kerja wajib dipilih.',
            'work_hour_limit_type.in' =>
                'Periode batas jam kerja harus mingguan atau harian.',

            'min_rest_hours.required' =>
                'Minimal waktu istirahat wajib diisi.',
            'min_rest_hours.integer' =>
                'Waktu istirahat harus berupa bilangan bulat.',
            'min_rest_hours.min' =>
                'Waktu istirahat minimal 1 jam.',
            'min_rest_hours.max' =>
                'Waktu istirahat maksimal 24 jam.',

            'max_working_days_per_week.required' =>
                'Maksimal hari kerja per minggu wajib diisi.',
            'max_working_days_per_week.integer' =>
                'Maksimal hari kerja harus berupa bilangan bulat.',
            'max_working_days_per_week.min' =>
                'Minimal hari kerja adalah 1 hari.',
            'max_working_days_per_week.max' =>
                'Maksimal hari kerja adalah 7 hari per minggu.',

            'auto_buffer_minutes.required' =>
                'Buffer otomatis wajib diisi.',
            'auto_buffer_minutes.integer' =>
                'Buffer otomatis harus berupa bilangan bulat.',
            'auto_buffer_minutes.min' =>
                'Buffer otomatis tidak boleh negatif.',
            'auto_buffer_minutes.max' =>
                'Buffer otomatis maksimal 120 menit.',

            'grace_period_minutes.required' =>
                'Toleransi keterlambatan wajib diisi.',
            'grace_period_minutes.integer' =>
                'Toleransi keterlambatan harus berupa bilangan bulat.',
            'grace_period_minutes.min' =>
                'Toleransi keterlambatan tidak boleh negatif.',
            'grace_period_minutes.max' =>
                'Toleransi keterlambatan maksimal 60 menit.',

            'is_auto_clockout.required' =>
                'Pengaturan auto clock-out wajib dikirim.',
            'is_auto_clockout.boolean' =>
                'Pengaturan auto clock-out tidak valid.',

            'auto_clockout_buffer_hours.required' =>
                'Batas waktu auto clock-out wajib diisi.',
            'auto_clockout_buffer_hours.integer' =>
                'Batas waktu auto clock-out harus berupa bilangan bulat.',
            'auto_clockout_buffer_hours.min' =>
                'Batas waktu auto clock-out minimal 1 jam.',
            'auto_clockout_buffer_hours.max' =>
                'Batas waktu auto clock-out maksimal 12 jam.',
        ]);

        // Ambil atau buat pengaturan hanya untuk perusahaan pengguna.
        $setting = CompanySetting::firstOrCreate(
            ['company_id' => $companyId],
            $this->defaultSettings()
        );

        $setting->fill($validated);
        $setting->save();

        return redirect()
            ->route('admin.settings.index')
            ->with(
                'success',
                'Pengaturan perusahaan berhasil diperbarui.'
            );
    }
}