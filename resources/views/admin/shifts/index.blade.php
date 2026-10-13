<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Master Shift
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola template shift, jam kerja, departemen, dan kuota karyawan.
                </p>
            </div>

            <button
                type="button"
                @click="window.dispatchEvent(new CustomEvent('open-shift-modal'))"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#6C5CE7] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5848d6]"
            >
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Template Shift
            </button>
        </div>
    </x-slot>

    <div
        class="py-8"
        x-data="shiftTemplatePage()"
        @open-shift-modal.window="openCreate()"
        @open-edit-shift.window="openEdit($event.detail)"
    >
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Notifikasi berhasil --}}
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    <i data-lucide="check-circle" class="mt-0.5 h-5 w-5 shrink-0"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Notifikasi error validasi --}}
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <div class="mb-2 flex items-center gap-2 font-semibold">
                        <i data-lucide="alert-circle" class="h-5 w-5"></i>
                        Periksa kembali data yang dimasukkan.
                    </div>

                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Template</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">
                                {{ $templates->count() }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-violet-50 p-3 text-[#6C5CE7]">
                            <i data-lucide="layers" class="h-6 w-6"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Departemen</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">
                                {{ $departments->count() }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-3 text-blue-600">
                            <i data-lucide="building-2" class="h-6 w-6"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Kuota</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">
                                {{ $templates->sum('default_quota') }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600">
                            <i data-lucide="users" class="h-6 w-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel template shift --}}
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            Daftar Template Shift
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Template shift untuk departemen perusahaan kamu.
                        </p>
                    </div>

                    <div class="relative w-full sm:max-w-xs">
                        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="search"
                            x-model="search"
                            placeholder="Cari nama atau departemen..."
                            class="w-full rounded-xl border-gray-200 py-2.5 pl-9 pr-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                        >
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Template
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Departemen
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Jam Kerja
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Durasi
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Kuota
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($templates as $template)
                                @php
                                    $startMinutes = ((int) substr($template->start_time, 0, 2) * 60)
                                        + (int) substr($template->start_time, 3, 2);

                                    $endMinutes = ((int) substr($template->end_time, 0, 2) * 60)
                                        + (int) substr($template->end_time, 3, 2);

                                    $duration = $endMinutes - $startMinutes;

                                    if ($duration <= 0) {
                                        $duration += 1440;
                                    }

                                    $durationHours = intdiv($duration, 60);
                                    $durationMinutes = $duration % 60;

                                    $durationLabel = $durationHours . ' jam';

                                    if ($durationMinutes > 0) {
                                        $durationLabel .= ' ' . $durationMinutes . ' menit';
                                    }
                                @endphp

                                <tr
                                    class="transition hover:bg-violet-50/30"
                                    x-show="matches(
                                        @js($template->title),
                                        @js($template->department?->name ?? '')
                                    )"
                                >
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="h-10 w-1.5 rounded-full"
                                                style="background-color: {{ $template->color_code }}"
                                            ></span>

                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    {{ $template->title }}
                                                </p>
                                                <div class="mt-1 flex items-center gap-2">
                                                    <span
                                                        class="h-3 w-3 rounded-full border border-black/10"
                                                        style="background-color: {{ $template->color_code }}"
                                                    ></span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $template->color_code }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ $template->department?->name ?? 'Departemen tidak tersedia' }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <i data-lucide="clock" class="h-4 w-4 text-gray-400"></i>
                                            {{ substr($template->start_time, 0, 5) }}
                                            <span class="text-gray-400">–</span>
                                            {{ substr($template->end_time, 0, 5) }}
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ $durationLabel }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-violet-50 px-2.5 py-1.5 text-sm font-semibold text-[#6C5CE7]">
                                            <i data-lucide="users" class="h-3.5 w-3.5"></i>
                                            {{ $template->default_quota }} orang
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                title="Edit template"
                                                @click="openEdit({
                                                    id: {{ $template->id }},
                                                    title: @js($template->title),
                                                    department_id: {{ $template->department_id }},
                                                    start_time: @js(substr($template->start_time, 0, 5)),
                                                    end_time: @js(substr($template->end_time, 0, 5)),
                                                    default_quota: {{ $template->default_quota }},
                                                    color_code: @js($template->color_code)
                                                })"
                                                class="rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-[#6C5CE7]"
                                            >
                                                <i data-lucide="pencil" class="h-4 w-4"></i>
                                            </button>

                                            <button
                                                type="button"
                                                title="Hapus template"
                                                @click="deleteId = {{ $template->id }}; deleteTitle = @js($template->title); deleteOpen = true"
                                                class="rounded-lg border border-gray-200 p-2 text-gray-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            >
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-14 text-center">
                                        <div class="mx-auto flex max-w-sm flex-col items-center">
                                            <div class="rounded-2xl bg-violet-50 p-4 text-[#6C5CE7]">
                                                <i data-lucide="calendar-days" class="h-8 w-8"></i>
                                            </div>
                                            <h4 class="mt-4 font-semibold text-gray-800">
                                                Belum ada template shift
                                            </h4>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Tambahkan template shift pertama untuk mulai mengatur jadwal kerja.
                                            </p>
                                            <button
                                                type="button"
                                                @click="openCreate()"
                                                class="mt-4 rounded-xl bg-[#6C5CE7] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#5848d6]"
                                            >
                                                Tambah Template
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                            <tr x-cloak x-show="!hasResults()" x-transition>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">
                                    Tidak ada template yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50/70 px-5 py-3 text-sm text-gray-500">
                    Menampilkan {{ $templates->count() }} template shift.
                </div>
            </div>
        </div>

        {{-- Modal tambah/edit --}}
        <div
            x-cloak
            x-show="formOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
            @keydown.escape.window="formOpen = false"
        >
            <div
                x-show="formOpen"
                x-transition
                @click.outside="formOpen = false"
                class="my-auto w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div class="flex items-start justify-between border-b border-gray-100 p-5 sm:p-6">
                    <div>
                        <h3
                            class="text-xl font-bold text-gray-800"
                            x-text="isEditing ? 'Edit Template Shift' : 'Tambah Template Shift'"
                        ></h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Isi detail jam kerja dan kuota shift.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="formOpen = false"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                    >
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form
                    :action="isEditing ? updateUrl : '{{ route('admin.shifts.store') }}'"
                    method="POST"
                    class="space-y-5 p-5 sm:p-6"
                >
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label for="title" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Nama Template Shift <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            x-model="form.title"
                            maxlength="100"
                            required
                            placeholder="Contoh: Shift Pagi"
                            class="w-full rounded-xl border-gray-200 px-3.5 py-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department_id" class="mb-1.5 block text-sm font-semibold text-gray-700">
                            Departemen <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="department_id"
                            name="department_id"
                            x-model="form.department_id"
                            required
                            class="w-full rounded-xl border-gray-200 px-3.5 py-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                        >
                            <option value="">Pilih departemen</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($departments->isEmpty())
                            <p class="mt-1.5 text-sm text-amber-600">
                                Belum ada departemen. Buat departemen terlebih dahulu sebelum menambahkan template.
                            </p>
                        @endif

                        @error('department_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="start_time" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                Jam Mulai <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="start_time"
                                type="time"
                                name="start_time"
                                x-model="form.start_time"
                                required
                                class="w-full rounded-xl border-gray-200 px-3.5 py-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                            >
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                Jam Selesai <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="end_time"
                                type="time"
                                name="end_time"
                                x-model="form.end_time"
                                required
                                class="w-full rounded-xl border-gray-200 px-3.5 py-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                            >
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="default_quota" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                Kuota Karyawan <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="default_quota"
                                type="number"
                                name="default_quota"
                                x-model="form.default_quota"
                                min="1"
                                step="1"
                                required
                                placeholder="Contoh: 10"
                                class="w-full rounded-xl border-gray-200 px-3.5 py-3 text-sm focus:border-[#6C5CE7] focus:ring-[#6C5CE7]"
                            >
                            @error('default_quota')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="color_code" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                Warna Shift <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-2">
                                <input
                                    id="color_code"
                                    type="color"
                                    name="color_code"
                                    x-model="form.color_code"
                                    required
                                    class="h-10 w-12 cursor-pointer rounded-lg border-0 bg-transparent p-0"
                                >

                                <span
                                    class="text-sm font-medium text-gray-600"
                                    x-text="form.color_code"
                                ></span>
                            </div>

                            @error('color_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="formOpen = false"
                            class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            :disabled="{{ $departments->isEmpty() ? 'true' : 'false' }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#6C5CE7] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#5848d6] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i data-lucide="save" class="h-4 w-4"></i>
                            <span x-text="isEditing ? 'Simpan Perubahan' : 'Simpan Template'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal konfirmasi hapus --}}
        <div
            x-cloak
            x-show="deleteOpen"
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4"
            @keydown.escape.window="deleteOpen = false"
        >
            <div
                x-show="deleteOpen"
                x-transition
                @click.outside="deleteOpen = false"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <i data-lucide="trash-2" class="h-6 w-6"></i>
                </div>

                <h3 class="mt-4 text-center text-lg font-bold text-gray-800">
                    Hapus Template Shift?
                </h3>

                <p class="mt-2 text-center text-sm leading-6 text-gray-500">
                    Template
                    <span class="font-semibold text-gray-700" x-text="deleteTitle"></span>
                    akan dihapus. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                    <button
                        type="button"
                        @click="deleteOpen = false"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </button>

                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 sm:w-auto"
                        >
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function shiftTemplatePage() {
                return {
                    search: '',
                    formOpen: false,
                    deleteOpen: false,
                    isEditing: false,
                    updateUrl: '',
                    deleteUrl: '',
                    deleteId: null,
                    deleteTitle: '',

                    form: {
                        id: null,
                        title: '',
                        department_id: '',
                        start_time: '08:00',
                        end_time: '16:00',
                        default_quota: 1,
                        color_code: '#6C5CE7',
                    },

                    init() {
                        this.$watch('deleteId', (id) => {
                            this.deleteUrl = id
                                ? `/admin/shifts/${id}`
                                : '';
                        });
                    },

                    matches(title, department) {
                        const query = this.search.trim().toLowerCase();

                        if (!query) {
                            return true;
                        }

                        return String(title).toLowerCase().includes(query)
                            || String(department).toLowerCase().includes(query);
                    },

                    hasResults() {
                        const query = this.search.trim().toLowerCase();

                        if (!query) {
                            return true;
                        }

                        return Array.from(
                            this.$el.querySelectorAll('tbody tr[x-show]')
                        ).some((row) => {
                            const title = row.querySelector('td:first-child')?.innerText ?? '';
                            const department = row.querySelector('td:nth-child(2)')?.innerText ?? '';

                            return (title + ' ' + department).toLowerCase().includes(query);
                        });
                    },

                    openCreate() {
                        this.isEditing = false;
                        this.updateUrl = '';
                        this.form = {
                            id: null,
                            title: '',
                            department_id: '',
                            start_time: '08:00',
                            end_time: '16:00',
                            default_quota: 1,
                            color_code: '#6C5CE7',
                        };
                        this.formOpen = true;
                    },

                    openEdit(data) {
                        this.isEditing = true;
                        this.form = {
                            id: data.id,
                            title: data.title,
                            department_id: String(data.department_id),
                            start_time: data.start_time,
                            end_time: data.end_time,
                            default_quota: data.default_quota,
                            color_code: data.color_code || '#6C5CE7',
                        };
                        this.updateUrl = `/admin/shifts/${data.id}`;
                        this.formOpen = true;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>