<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <span>Admin</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-900 font-medium">Manajemen Pengguna</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Kelola Pengguna
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Manajemen akun pengguna, penetapan peran, departemen, dan status staf.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition shadow-xs">
                    <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                    <span>Tambah Pengguna</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="check-circle" class="h-4 w-4 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm shadow-2xs">
                <i data-lucide="alert-circle" class="h-4 w-4 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Filter & Search Toolbar --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-2xs">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-end">
                {{-- Search Box --}}
                <div class="lg:col-span-5">
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">
                        Pencarian
                    </label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"></i>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari nama, email, NIK, atau nomor HP..." 
                               class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white text-slate-800 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] outline-none transition">
                    </div>
                </div>

                {{-- Filter Role --}}
                <div class="lg:col-span-3">
                    <label for="role" class="block text-xs font-semibold text-slate-700 mb-1">
                        Peran (Role)
                    </label>
                    <select name="role" 
                            id="role" 
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white text-slate-800 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] outline-none transition">
                        <option value="">Semua Peran</option>
                        <option value="superadmin" @selected(request('role') === 'superadmin')>Super Admin</option>
                        <option value="manager" @selected(request('role') === 'manager')>Manager</option>
                        <option value="employee" @selected(request('role') === 'employee')>Employee</option>
                    </select>
                </div>

                {{-- Filter Department --}}
                <div class="lg:col-span-3">
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">
                        Departemen
                    </label>
                    <select name="department_id" 
                            id="department_id" 
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white text-slate-800 focus:border-[#6C5CE7] focus:ring-1 focus:ring-[#6C5CE7] outline-none transition">
                        <option value="">Semua Departemen</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="lg:col-span-1 flex items-center gap-1.5">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center p-2 rounded-lg text-white bg-slate-900 hover:bg-slate-800 transition shadow-xs" 
                            title="Terapkan Filter">
                        <i data-lucide="filter" class="h-3.5 w-3.5"></i>
                    </button>
                    @if (request('search') || request('role') || request('department_id'))
                        <a href="{{ route('admin.users.index') }}" 
                           class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-800 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition shadow-2xs" 
                           title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Main Table / Empty State Card --}}
        @if ($users->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200/80 p-10 text-center shadow-2xs">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 mb-3">
                        <i data-lucide="users" class="h-6 w-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Pengguna Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        @if (request('search') || request('role') || request('department_id'))
                            Tidak ada pengguna yang cocok dengan kriteria pencarian atau filter yang Anda terapkan.
                        @else
                            Mulai tambahkan pengguna baru untuk staf atau manajemen di perusahaan Anda.
                        @endif
                    </p>
                    @if (request('search') || request('role') || request('department_id'))
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition">
                            <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i>
                            <span>Reset Filter</span>
                        </a>
                    @else
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition shadow-xs">
                            <i data-lucide="plus" class="h-3.5 w-3.5"></i>
                            <span>Tambah Pengguna</span>
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Daftar Pengguna</h2>
                        <p class="text-xs text-slate-500">Total {{ $users->total() }} pengguna terdaftar</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-800 min-w-[680px]">
                        <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-200/80 uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-2.5 text-center w-12">No</th>
                                <th class="px-4 py-2.5">Pengguna</th>
                                <th class="px-4 py-2.5">Kontak</th>
                                <th class="px-4 py-2.5">Peran</th>
                                <th class="px-4 py-2.5">Departemen &amp; Posisi</th>
                                <th class="px-4 py-2.5 text-center">Status</th>
                                <th class="px-4 py-2.5 text-right w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                @php
                                    $initials = collect(explode(' ', $user->name))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn($part) => mb_substr($part, 0, 1))
                                        ->join('');
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    {{-- Kolom No --}}
                                    <td class="px-4 py-3 text-center font-medium text-slate-400">
                                        {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                                    </td>

                                    {{-- Kolom Pengguna (Avatar, Nama, Email) --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            @if ($user->profile_photo)
                                                <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200">
                                            @else
                                                <div class="w-8 h-8 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ strtoupper($initials ?: 'U') }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-semibold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if ($user->id === auth()->id())
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#6C5CE7] text-white">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500">
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Nomor HP --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                        {{ $user->phone_number ?: '-' }}
                                    </td>

                                    {{-- Kolom Peran --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($user->role === 'superadmin')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                <i data-lucide="shield" class="h-3 w-3 text-purple-600"></i>
                                                <span>Super Admin</span>
                                            </span>
                                        @elseif ($user->role === 'manager')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i data-lucide="user-check" class="h-3 w-3 text-blue-600"></i>
                                                <span>Manager</span>
                                                @if ($user->is_global_manager)
                                                    <span class="text-[9px] px-1 rounded bg-blue-100 text-blue-800 font-bold ml-0.5" title="Manajer Global">Global</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="user" class="h-3 w-3 text-emerald-600"></i>
                                                <span>Employee</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Departemen & Posisi --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-800">
                                            {{ $user->department?->name ?? 'Belum ada departemen' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $user->position?->name ?? 'Belum ada posisi' }}
                                        </div>
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if ($user->status === 'active')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @elseif ($user->status === 'on_leave')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Cuti</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                <span>Non-aktif</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition"
                                               title="Edit Pengguna">
                                                <i data-lucide="pencil" class="h-3 w-3 text-slate-400"></i>
                                                <span>Edit</span>
                                            </a>

                                            {{-- Tombol Hapus --}}
                                            @if ($user->id === auth()->id())
                                                <span class="px-2 py-1 text-[11px] text-slate-400 cursor-not-allowed font-medium" title="Anda tidak dapat menghapus akun Anda sendiri">
                                                    Terkunci
                                                </span>
                                            @else
                                                <button type="button" 
                                                        x-data="" 
                                                        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')" 
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition"
                                                        title="Hapus Pengguna">
                                                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                                                    <span>Hapus</span>
                                                </button>

                                                {{-- Modal Konfirmasi Hapus --}}
                                                <x-modal name="confirm-user-deletion-{{ $user->id }}" :show="false">
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-5 text-left">
                                                        @csrf
                                                        @method('DELETE')

                                                        <div class="flex items-start gap-3 mb-3">
                                                            <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                                                <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                                                            </div>
                                                            <div>
                                                                <h2 class="text-sm font-bold text-slate-900">
                                                                    Hapus Akun Pengguna
                                                                </h2>
                                                                <p class="text-xs text-slate-500">
                                                                    Konfirmasi penonaktifan akun pengguna dari sistem.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                                                            Apakah Anda yakin ingin menghapus akun <strong class="text-slate-900">{{ $user->name }}</strong> ({{ $user->email }})? Data pengguna akan dinonaktifkan (Soft Delete).
                                                        </p>

                                                        <div class="p-2.5 mb-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
                                                            <i data-lucide="alert-circle" class="h-3.5 w-3.5 shrink-0 mt-0.5 text-amber-600"></i>
                                                            <span>Pengguna tidak dapat dihapus jika masih memiliki penugasan shift yang aktif.</span>
                                                        </div>

                                                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                                            <button type="button" 
                                                                    x-on:click="$dispatch('close')" 
                                                                    class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-lg transition">
                                                                Batal
                                                            </button>
                                                            <button type="submit" 
                                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition shadow-xs">
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

                {{-- Pagination --}}
                @if ($users->hasPages())
                    <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-white">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
