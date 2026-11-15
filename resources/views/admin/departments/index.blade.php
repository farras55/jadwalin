<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-indigo-600">Admin</a>
                    </li>
                    <li aria-hidden="true">
                        <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                    </li>
                    <li aria-current="page" class="font-semibold text-indigo-600">Departemen &amp; Posisi</li>
                </ol>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Departemen & Posisi
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">
                    Kelola struktur divisi dan jabatan kerja perusahaan.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.positions.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-indigo-600 bg-white border border-slate-200 hover:bg-indigo-50 transition shadow-card">
                    <i data-lucide="briefcase" class="h-4 w-4"></i>
                    <span>Kelola Posisi</span>
                </a>
                <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition shadow-sm">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Tambah Department</span>
                </a>
            </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Navigation Tabs --}}
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('admin.departments.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-indigo-600 text-white shadow-sm transition">
                <i data-lucide="building-2" class="h-4 w-4"></i>
                <span>Daftar Departemen</span>
                <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] bg-white/20 text-white">{{ $departments->count() }}</span>
            </a>
            <a href="{{ route('admin.positions.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-900 hover:bg-indigo-50 transition">
                <i data-lucide="briefcase" class="h-4 w-4"></i>
                <span>Posisi Jabatan</span>
            </a>
        </div>
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-card">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-card">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Table / Empty State Card --}}
        @if ($departments->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 md:p-12 text-center shadow-sm">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 bg-indigo-50 border border-slate-200 rounded-xl flex items-center justify-center text-indigo-600 mb-4 shadow-card">
                        <i data-lucide="layers" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Belum Ada Department</h3>
                    <p class="text-xs md:text-sm text-slate-500 leading-relaxed mb-6">
                        Mulai atur struktur organisasi perusahaan dengan menambahkan data department pertama Anda.
                    </p>
                    <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-600 transition shadow-sm">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        <span>Tambah Department</span>
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-slate-200 flex items-center justify-center text-indigo-600">
                            <i data-lucide="building-2" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm md:text-base font-bold text-slate-900">Daftar Department</h2>
                            <p class="text-xs text-slate-500">Total {{ $departments->count() }} department terdaftar</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm text-slate-900 min-w-[650px]">
                        <thead class="bg-indigo-50/80 text-slate-900 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 text-center w-16">No</th>
                                <th class="px-5 py-3.5">Nama Department</th>
                                <th class="px-5 py-3.5">Deskripsi</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Posisi</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Karyawan</th>
                                <th class="px-5 py-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($departments as $department)
                                <tr class="hover:bg-indigo-50/40 transition duration-150">
                                    <td class="px-5 py-4 text-center font-medium text-slate-500">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                            <span>{{ $department->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 max-w-xs truncate">
                                        {{ $department->description ?: '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.positions.index') }}" 
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-600 hover:text-white transition duration-150"
                                           title="Lihat dan kelola posisi departemen ini">
                                            <i data-lucide="briefcase" class="h-3 w-3"></i>
                                            <span>{{ $department->positions_count }} Posisi</span>
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="users" class="h-3 w-3"></i>
                                            <span>{{ $department->employees_count }} Karyawan</span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('admin.departments.edit', $department) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition duration-150"
                                               title="Edit Department">
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                                <span>Edit</span>
                                            </a>

                                            {{-- Tombol Hapus (Trigger Modal) --}}
                                            <button type="button" 
                                                    x-data="" 
                                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-department-deletion-{{ $department->id }}')" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition duration-150"
                                                    title="Hapus Department">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>

                                        {{-- Modal Konfirmasi Hapus --}}
                                        <x-modal name="confirm-department-deletion-{{ $department->id }}" :show="false">
                                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="p-6 text-left">
                                                @csrf
                                                @method('DELETE')

                                                <div class="flex items-start gap-4 mb-4">
                                                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                                        <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                                                    </div>
                                                    <div>
                                                        <h2 class="text-base font-bold text-slate-900">
                                                            Hapus Department
                                                        </h2>
                                                        <p class="text-xs text-slate-500 mt-0.5">
                                                            Konfirmasi penghapusan data department dari sistem.
                                                        </p>
                                                    </div>
                                                </div>

                                                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                                    Apakah Anda yakin ingin menghapus department <strong class="text-slate-900">{{ $department->name }}</strong>? Data yang dihapus tidak dapat dikembalikan.
                                                </p>

                                                @if ($department->employees_count > 0)
                                                    <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
                                                        <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600"></i>
                                                        <span>Department ini memiliki <strong>{{ $department->employees_count }}</strong> karyawan aktif. Department tidak dapat dihapus selama masih ada karyawan yang terkait.</span>
                                                    </div>
                                                @endif

                                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                                                    <button type="button" 
                                                            x-on:click="$dispatch('close')" 
                                                            class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 bg-indigo-50 hover:bg-[#E2E8F0] rounded-xl transition duration-150">
                                                        Batal
                                                    </button>
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition duration-150 shadow-sm">
                                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                        <span>Hapus Department</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
