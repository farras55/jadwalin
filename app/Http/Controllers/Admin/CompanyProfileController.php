<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    /**
     * Menampilkan antarmuka profil perusahaan tenant.
     */
    public function index(): View
    {
        $user = auth()->user();
        $company = $user?->company ?? new Company(['timezone' => 'Asia/Jakarta']);

        if ($company->exists) {
            $company->loadCount(['departments', 'users']);
        }

        return view('admin.companies.index', compact('company'));
    }

    /**
     * Memperbarui detail profil perusahaan.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $company = $user?->company;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('companies')->ignore($company?->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
        ], [
            'name.required' => 'Nama legal perusahaan wajib diisi.',
            'name.max' => 'Nama legal perusahaan maksimal 100 karakter.',
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.email' => 'Format email perusahaan tidak valid.',
            'email.unique' => 'Email perusahaan sudah digunakan oleh tenant lain.',
            'phone_number.required' => 'Nomor telepon kantor wajib diisi.',
            'address.required' => 'Alamat fisik kantor/outlet wajib diisi.',
            'timezone.required' => 'Zona waktu operasional wajib dipilih.',
            'timezone.in' => 'Pilihan zona waktu harus salah satu dari WIB, WITA, atau WIT.',
        ]);

        if (! $company) {
            $company = Company::create($validated);
            $user?->update(['company_id' => $company->id]);
        } else {
            $company->update($validated);
        }

        return redirect()->route('admin.companies.index')
            ->with('success', 'Profil legalitas dan zona waktu perusahaan berhasil diperbarui.');
    }
}
