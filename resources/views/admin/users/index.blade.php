<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Kelola Pengguna
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Manajemen akun pengguna, penetapan peran, departemen, dan status staf.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Tambah Pengguna</span>
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

        {{-- Filter & Search Toolbar --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 md:p-5 shadow-2xs">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                {{-- Search Box --}}
                <div class="lg:col-span-5">
                    <label for="search" class="block text-xs font-bold uppercase tracking-wider text-[#7C7896] mb-1.5">
                        Pencarian
                    </label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#7C7896]"></i>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari nama, email, atau nomor HP..." 
                               class="w-full pl-10 pr-4 py-2 text-xs md:text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition">
                    </div>
                </div>

                {{-- Filter Role --}}
                <div class="lg:col-span-3">
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-[#7C7896] mb-1.5">
                        Peran (Role)
                    </label>
                    <select name="role" 
                            id="role" 
                            class="w-full px-3.5 py-2 text-xs md:text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition">
                        <option value="">Semua Peran</option>
                        <option value="superadmin" @selected(request('role') === 'superadmin')>Super Admin</option>
                        <option value="manager" @selected(request('role') === 'manager')>Manager</option>
                        <option value="employee" @selected(request('role') === 'employee')>Employee</option>
                    </select>
                </div>

                {{-- Filter Department --}}
                <div class="lg:col-span-3">
                    <label for="department_id" class="block text-xs font-bold uppercase tracking-wider text-[#7C7896] mb-1.5">
                        Departemen
                    </label>
                    <select name="department_id" 
                            id="department_id" 
                            class="w-full px-3.5 py-2 text-xs md:text-sm rounded-xl border border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-2 focus:ring-[#6C5CE7]/20 outline-none transition">
                        <option value="">Semua Departemen</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="lg:col-span-1 flex items-center gap-2">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center p-2 rounded-xl text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-xs" 
                            title="Terapkan Filter">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                    </button>
                    @if (request('search') || request('role') || request('department_id'))
                        <a href="{{ route('admin.users.index') }}" 
                           class="inline-flex items-center justify-center p-2 rounded-xl text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] border border-[#E2E0F7] hover:bg-[#E2E0F7] transition shadow-2xs" 
                           title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Main Table / Empty State Card --}}
        @if ($users->isEmpty())
            <div class="bg-white rounded-2xl border border-[#E2E0F7] p-8 md:p-12 text-center shadow-2xs">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4 shadow-2xs">
                        <i data-lucide="users" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">Pengguna Tidak Ditemukan</h3>
                    <p class="text-xs md:text-sm text-[#7C7896] leading-relaxed mb-6">
                        @if (request('search') || request('role') || request('department_id'))
                            Tidak ada pengguna yang cocok dengan kriteria pencarian atau filter yang Anda terapkan.
                        @else
                            Mulai tambahkan pengguna baru untuk staf atau manajemen di perusahaan Anda.
                        @endif
                    </p>
                    @if (request('search') || request('role') || request('department_id'))
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#6C5CE7] bg-[#F5F3FF] border border-[#E2E0F7] hover:bg-[#E2E0F7] transition shadow-2xs">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            <span>Reset Filter</span>
                        </a>
                    @else
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#6C5CE7] hover:bg-[#4F46E5] transition shadow-sm">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            <span>Tambah Pengguna</span>
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
                <div class="p-5 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#F5F3FF] border border-[#E2E0F7] flex items-center justify-center text-[#6C5CE7]">
                            <i data-lucide="users" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm md:text-base font-bold text-[#2D2A3E]">Daftar Pengguna</h2>
                            <p class="text-xs text-[#7C7896]">Total {{ $users->total() }} pengguna terdaftar</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm text-[#2D2A3E]">
                        <thead class="bg-[#F5F3FF] text-[#2D2A3E] font-semibold border-b border-[#E2E0F7]">
                            <tr>
                                <th class="px-5 py-3.5 text-center w-14">No</th>
                                <th class="px-5 py-3.5">Pengguna</th>
                                <th class="px-5 py-3.5">Kontak</th>
                                <th class="px-5 py-3.5">Peran</th>
                                <th class="px-5 py-3.5">Departemen &amp; Posisi</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E0F7]">
                            @foreach ($users as $user)
                                @php
                                    $initials = collect(explode(' ', $user->name))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn($part) => mb_substr($part, 0, 1))
                                        ->join('');
                                @endphp
                                <tr class="hover:bg-[#F5F3FF]/40 transition duration-150">
                                    {{-- Kolom No --}}
                                    <td class="px-5 py-4 text-center font-medium text-[#7C7896]">
                                        {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                                    </td>

                                    {{-- Kolom Pengguna (Avatar, Nama, Email) --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @if ($user->profile_photo)
                                                <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="w-10 h-10 rounded-full object-cover shrink-0 border border-[#E2E0F7]">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-[#6C5CE7]/10 text-[#6C5CE7] font-bold text-xs flex items-center justify-center border border-[#6C5CE7]/20 shrink-0">
                                                    {{ strtoupper($initials ?: 'U') }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-[#2D2A3E] flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if ($user->id === auth()->id())
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#6C5CE7] text-white">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-[#7C7896]">
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Nomor HP --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-[#7C7896]">
                                        {{ $user->phone_number ?: '-' }}
                                    </td>

                                    {{-- Kolom Peran (Role Badge: Super Admin purple, Manager indigo, Employee emerald) --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if ($user->role === 'superadmin')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                <i data-lucide="shield" class="h-3 w-3 text-purple-600"></i>
                                                <span>Super Admin</span>
                                            </span>
                                        @elseif ($user->role === 'manager')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <i data-lucide="user-check" class="h-3 w-3 text-indigo-600"></i>
                                                <span>Manager</span>
                                                @if ($user->is_global_manager)
                                                    <span class="text-[10px] px-1 rounded bg-indigo-100 text-indigo-800 font-bold ml-0.5" title="Manajer Global">Global</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="user" class="h-3 w-3 text-emerald-600"></i>
                                                <span>Employee</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Departemen & Posisi --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-[#2D2A3E]">
                                            {{ $user->department?->name ?? 'Belum ada departemen' }}
                                        </div>
                                        <div class="text-[11px] text-[#7C7896]">
                                            {{ $user->position?->name ?? 'Belum ada posisi' }}
                                        </div>
                                    </td>

                                    {{-- Kolom Status (Active emerald, Inactive gray) --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        @if ($user->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @elseif ($user->status === 'on_leave')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                <span>Cuti / Izin</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                <span>Non-aktif</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#6C5CE7] bg-[#6C5CE7]/10 hover:bg-[#6C5CE7] hover:text-white transition duration-150"
                                               title="Edit Pengguna">
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                                <span>Edit</span>
                                            </a>

                                            {{-- Tombol Hapus --}}
                                            @if ($user->id === auth()->id())
                                                <span class="px-2 py-1 text-xs text-[#7C7896] cursor-not-allowed opacity-60 font-medium" title="Anda tidak dapat menghapus akun Anda sendiri">
                                                    Terkunci
                                                </span>
                                            @else
                                                <button type="button" 
                                                        x-data="" 
                                                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition duration-150"
                                                        title="Hapus Pengguna">
                                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>

                                                {{-- Modal Konfirmasi Hapus --}}
                                                <x-modal name="confirm-user-deletion-{{ $user->id }}" :show="false">
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6 text-left">
                                                        @csrf
                                                        @method('DELETE')

                                                        <div class="flex items-start gap-4 mb-4">
                                                            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                                                <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                                                            </div>
                                                            <div>
                                                                <h2 class="text-base font-bold text-[#2D2A3E]">
                                                                    Hapus Akun Pengguna
                                                                </h2>
                                                                <p class="text-xs text-[#7C7896] mt-0.5">
                                                                    Konfirmasi penonaktifan akun pengguna dari sistem.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <p class="text-xs sm:text-sm text-[#7C7896] leading-relaxed mb-4">
                                                            Apakah Anda yakin ingin menghapus akun <strong class="text-[#2D2A3E]">{{ $user->name }}</strong> ({{ $user->email }})? Data pengguna akan dinonaktifkan (Soft Delete).
                                                        </p>

                                                        <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
                                                            <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600"></i>
                                                            <span>Pengguna tidak dapat dihapus jika masih memiliki penugasan shift yang aktif.</span>
                                                        </div>

                                                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E0F7]">
                                                            <button type="button" 
                                                                    x-on:click="$dispatch('close')" 
                                                                    class="px-4 py-2 text-xs font-semibold text-[#7C7896] hover:text-[#2D2A3E] bg-[#F5F3FF] hover:bg-[#E2E0F7] rounded-xl transition duration-150">
                                                                Batal
                                                            </button>
                                                            <button type="submit" 
                                                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition duration-150 shadow-sm">
                                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                                <span>Hapus Pengguna</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </x-modal>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination (Mempertahankan Query String Filter) --}}
                @if ($users->hasPages())
                    <div class="p-4 border-t border-[#E2E0F7] bg-white">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>