<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Departemen & Posisi
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Kelola struktur divisi dan jabatan kerja perusahaan.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Tambah Department</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Table / Empty State Card --}}
        @if ($departments->isEmpty())
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-8 md:p-12 text-center shadow-2xs">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4 shadow-2xs">
                        <i data-lucide="layers" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">Belum Ada Department</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] leading-relaxed mb-6">
                        Mulai atur struktur organisasi perusahaan dengan menambahkan data department pertama Anda.
                    </p>
                    <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        <span>Tambah Department</span>
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-[#F5F3FF] border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7]">
                            <i data-lucide="building-2" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm md:text-base font-bold text-[#2D2A3E]">Daftar Department</h2>
                            <p class="text-xs text-[#7C7896]">Total {{ $departments->count() }} department terdaftar</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm text-[#2D2A3E]">
                        <thead class="bg-[#F5F3FF] text-[#2D2A3E] font-semibold border-b border-[#E2E0F7]">
                            <tr>
                                <th class="px-5 py-3.5 text-center w-16">No</th>
                                <th class="px-5 py-3.5">Nama Department</th>
                                <th class="px-5 py-3.5">Deskripsi</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Posisi</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Karyawan</th>
                                <th class="px-5 py-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E0F7]">
                            @foreach ($departments as $department)
                                <tr class="hover:bg-[#F5F3FF]/40 transition duration-150">
                                    <td class="px-5 py-4 text-center font-medium text-[#7C7896]">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-[#2D2A3E] flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#6C5CE7]"></span>
                                            <span>{{ $department->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-[#7C7896] max-w-xs truncate">
                                        {{ $department->description ?: '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                                            <i data-lucide="briefcase" class="h-3 w-3"></i>
                                            <span>{{ $department->positions_count }} Posisi</span>
                                        </span>
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
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#6C5CE7] bg-[#6C5CE7]/10 hover:bg-[#6C5CE7] hover:text-white transition duration-150"
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
                                                        <h2 class="text-base font-bold text-[#2D2A3E]">
                                                            Hapus Department
                                                        </h2>
                                                        <p class="text-xs text-[#7C7896] mt-0.5">
                                                            Konfirmasi penghapusan data department dari sistem.
                                                        </p>
                                                    </div>
                                                </div>

                                                <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed mb-4">
                                                    Apakah Anda yakin ingin menghapus department <strong class="text-[#2D2A3E]">{{ $department->name }}</strong>? Data yang dihapus tidak dapat dikembalikan.
                                                </p>

                                                @if ($department->employees_count > 0)
                                                    <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
                                                        <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600"></i>
                                                        <span>Department ini memiliki <strong>{{ $department->employees_count }}</strong> karyawan aktif. Department tidak dapat dihapus selama masih ada karyawan yang terkait.</span>
                                                    </div>
                                                @endif

                                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                                                    <button type="button" 
                                                            x-on:click="$dispatch('close')" 
                                                            class="px-4 py-2 text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] rounded-xl transition duration-150">
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
