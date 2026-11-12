<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyProfileController extends Controller
{
    /**
     * Menampilkan antarmuka profil perusahaan tenant.
     */
    public function index()
    {
        $company = auth()->user()->company;
        
        return view('admin.companies.index', compact('company'));
    }

    /**
     * Memperbarui detail profil perusahaan.
     */
    public function update(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:100', Rule::unique('companies')->ignore($company->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'address'      => ['required', 'string'],
            'timezone'     => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.index')
                         ->with('success', 'Profil legalitas dan zona waktu perusahaan berhasil diperbarui.');
    }
}