<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Pengaturan Perusahaan
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Atur batas jam kerja dan ketentuan kepatuhan karyawan.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-5xl px-4 pb-10 sm:px-6 lg:px-8">

            {{-- Pesan berhasil --}}
            @if (session('success'))
                <div
                    role="status"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Pesan validasi --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                >
                    <p class="font-semibold">
                        Periksa kembali input kamu:
                    </p>

                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('admin.settings.update') }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- KETENTUAN JAM KERJA --}}
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Ketentuan Jam Kerja
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan batas jam kerja reguler, lembur,
                        dan waktu istirahat.
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Jam kerja reguler --}}
                        <div>
                            <label
                                for="max_regular_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Jam Kerja Reguler
                            </label>

                            <input
                                id="max_regular_hours"
                                name="max_regular_hours"
                                type="number"
                                min="1"
                                max="60"
                                step="1"
                                required
                                value="{{ old('max_regular_hours', $setting->max_regular_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 1–60 jam.
                            </p>
                        </div>

                        {{-- Jam lembur --}}
                        <div>
                            <label
                                for="max_overtime_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Jam Lembur
                            </label>

                            <input
                                id="max_overtime_hours"
                                name="max_overtime_hours"
                                type="number"
                                min="0"
                                max="24"
                                step="1"
                                required
                                value="{{ old('max_overtime_hours', $setting->max_overtime_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 0–24 jam.
                            </p>
                        </div>

                        {{-- Periode batas jam kerja --}}
                        <div>
                            <label
                                for="work_hour_limit_type"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Periode Batas Jam Kerja
                            </label>

                            <select
                                id="work_hour_limit_type"
                                name="work_hour_limit_type"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option
                                    value="weekly"
                                    @selected(old('work_hour_limit_type', $setting->work_hour_limit_type) === 'weekly')
                                >
                                    Mingguan
                                </option>

                                <option
                                    value="daily"
                                    @selected(old('work_hour_limit_type', $setting->work_hour_limit_type) === 'daily')
                                >
                                    Harian
                                </option>
                            </select>
                        </div>

                        {{-- Waktu istirahat --}}
                        <div>
                            <label
                                for="min_rest_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Minimal Waktu Istirahat
                            </label>

                            <input
                                id="min_rest_hours"
                                name="min_rest_hours"
                                type="number"
                                min="1"
                                max="24"
                                step="1"
                                required
                                value="{{ old('min_rest_hours', $setting->min_rest_hours) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam jam, rentang 1–24.
                            </p>
                        </div>

                        {{-- Hari kerja --}}
                        <div>
                            <label
                                for="max_working_days_per_week"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Maksimal Hari Kerja per Minggu
                            </label>

                            <input
                                id="max_working_days_per_week"
                                name="max_working_days_per_week"
                                type="number"
                                min="1"
                                max="7"
                                step="1"
                                required
                                value="{{ old('max_working_days_per_week', $setting->max_working_days_per_week) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Rentang 1–7 hari per minggu.
                            </p>
                        </div>

                    </div>
                </section>

                {{-- PENGATURAN PRESENSI OTOMATIS --}}
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Pengaturan Presensi Otomatis
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Atur buffer otomatis, toleransi keterlambatan,
                        dan mekanisme clock-out.
                    </p>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Buffer otomatis --}}
                        <div>
                            <label
                                for="auto_buffer_minutes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Buffer Otomatis
                            </label>

                            <input
                                id="auto_buffer_minutes"
                                name="auto_buffer_minutes"
                                type="number"
                                min="0"
                                max="120"
                                step="1"
                                required
                                value="{{ old('auto_buffer_minutes', $setting->auto_buffer_minutes) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam menit, rentang 0–120.
                            </p>
                        </div>

                        {{-- Toleransi keterlambatan --}}
                        <div>
                            <label
                                for="grace_period_minutes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Toleransi Keterlambatan
                            </label>

                            <input
                                id="grace_period_minutes"
                                name="grace_period_minutes"
                                type="number"
                                min="0"
                                max="60"
                                step="1"
                                required
                                value="{{ old('grace_period_minutes', $setting->grace_period_minutes) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam menit, rentang 0–60.
                            </p>
                        </div>

                        {{-- Batas waktu auto clock-out --}}
                        <div>
                            <label
                                for="auto_clockout_buffer_hours"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Batas Waktu Auto Clock-out
                            </label>

                            <input
                                id="auto_clockout_buffer_hours"
                                name="auto_clockout_buffer_hours"
                                type="number"
                                min="1"
                                max="12"
                                step="1"
                                required
                                value="{{ old('auto_clockout_buffer_hours', $setting->auto_clockout_buffer_hours ?? 1) }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Dalam jam, rentang 1–12.
                            </p>
                        </div>

                        {{-- Aktifkan auto clock-out --}}
                        <div class="flex items-center gap-3 sm:col-span-2">
                            <input
                                type="hidden"
                                name="is_auto_clockout"
                                value="0"
                            >

                            <input
                                id="is_auto_clockout"
                                name="is_auto_clockout"
                                type="checkbox"
                                value="1"
                                @checked((bool) old('is_auto_clockout', $setting->is_auto_clockout))
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >

                            <label
                                for="is_auto_clockout"
                                class="text-sm font-medium text-gray-700"
                            >
                                Aktifkan Auto Clock-out
                            </label>
                        </div>

                    </div>
                </section>

                {{-- TOMBOL SIMPAN --}}
                <div
                    class="flex justify-end border-t border-gray-200 pt-6"
                    style="position: relative; z-index: 10;"
                >
                    <button
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-bold shadow-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        style="background-color: #4f46e5 !important; color: #ffffff !important; opacity: 1 !important; border: 1px solid #4f46e5;"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                d="M16.707 5.293a1 1 0 0 1 0 1.414l-7.5 7.5a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L8.5 11.086l6.793-6.793a1 1 0 0 1 1.414 0Z"
                            />
                        </svg>

                        Simpan Pengaturan
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>