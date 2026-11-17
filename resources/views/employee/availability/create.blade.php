@php
    // Contoh di kartu Auto-Buffer: 13:00-15:00 -> tercatat dengan buffer perusahaan.
    $exampleStart = \Carbon\Carbon::createFromTime(13, 0)->subMinutes($bufferMinutes)->format('H:i');
    $exampleEnd = \Carbon\Carbon::createFromTime(15, 0)->addMinutes($bufferMinutes)->format('H:i');
    $inputClass = 'block w-full rounded-xl border-brand-border text-sm text-slate-900 shadow-none focus:border-brand-primary focus:ring-brand-primary min-h-[44px]';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    Ajukan Halangan
                </h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">
                    Atur jadwal halangan Anda agar penjadwalan tidak bentrok.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                    Karyawan
                </span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
         x-data="availabilityForm(@js([
             'buffer' => $bufferMinutes,
             'recurring' => (bool) old('is_recurring'),
             'date' => old('date', ''),
             'start' => old('start_time', ''),
             'end' => old('end_time', ''),
         ]))">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('employee.availability.store') }}" method="POST"
                  class="bg-white rounded-xl border border-brand-border p-6 shadow-card space-y-5">
                @csrf
                <input type="hidden" name="is_recurring" :value="recurring ? 1 : 0">

                <h2 class="text-lg font-bold text-slate-900">Ajukan Halangan</h2>

                {{-- Toggle jenis halangan --}}
                <div class="inline-flex rounded-xl border border-brand-border bg-indigo-50 p-1" role="group" aria-label="Jenis halangan">
                    <button type="button" @click="recurring = false"
                            :class="!recurring ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-500'"
                            class="min-h-[44px] px-4 rounded-lg text-sm font-semibold transition">
                        Tanggal Tertentu
                    </button>
                    <button type="button" @click="recurring = true"
                            :class="recurring ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-500'"
                            class="min-h-[44px] px-4 rounded-lg text-sm font-semibold transition">
                        Berulang Mingguan
                    </button>
                </div>

                {{-- Tanggal --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-slate-900 mb-1.5">Tanggal</label>
                    <input type="date" id="date" name="date" x-model="date"
                           min="{{ now()->format('Y-m-d') }}" value="{{ old('date') }}"
                           class="{{ $inputClass }}">
                    <p x-show="recurring && weekdayName" x-cloak class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary">
                        <i data-lucide="repeat" class="h-3.5 w-3.5"></i>
                        <span>Berulang setiap <span x-text="weekdayName"></span></span>
                    </p>
                    @error('date')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jam --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-semibold text-slate-900 mb-1.5">Jam Mulai</label>
                        <input type="time" id="start_time" name="start_time" x-model="start"
                               value="{{ old('start_time') }}" class="{{ $inputClass }}">
                        @error('start_time')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-slate-900 mb-1.5">Jam Selesai</label>
                        <input type="time" id="end_time" name="end_time" x-model="end"
                               value="{{ old('end_time') }}" class="{{ $inputClass }}">
                        @error('end_time')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Alasan --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="reason" class="block text-sm font-semibold text-slate-900">Alasan</label>
                        <span class="text-xs text-slate-500">Wajib diisi</span>
                    </div>
                    <input type="text" id="reason" name="reason" maxlength="255"
                           value="{{ old('reason') }}" placeholder="Contoh: Kuliah Praktikum"
                           class="{{ $inputClass }}">
                    @error('reason')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Preview buffer real-time --}}
                <div class="flex items-start gap-3 rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                    <i data-lucide="info" class="h-4 w-4 text-indigo-600 shrink-0 mt-0.5"></i>
                    <p>
                        <template x-if="!valid">
                            <span>Buffer {{ $bufferMinutes }} menit akan ditambahkan otomatis sebelum dan sesudah halangan.</span>
                        </template>
                        <template x-if="valid">
                            <span>
                                Tercatat otomatis: <strong class="font-mono text-slate-900" x-text="from + ' - ' + to"></strong> {{ $timezoneAbbr ?? 'WIB' }}
                                (termasuk buffer {{ $bufferMinutes }} menit).
                            </span>
                        </template>
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-6 rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm">
                        Ajukan Halangan
                    </button>
                    <a href="{{ route('employee.availability') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-4 text-sm font-semibold text-slate-500 hover:text-slate-900 text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Kartu Penjelasan Auto-Buffer --}}
        <div>
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-card space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-alert" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">
                            Aturan Auto-Buffer {{ $bufferMinutes }} Menit
                        </h3>
                        <p class="text-[11px] text-slate-500">Kebijakan penjadwalan perusahaan</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Sistem secara otomatis menambahkan jeda waktu <strong>{{ $bufferMinutes }} menit</strong> sebelum jam mulai dan setelah jam selesai agar penugasan shift tidak beririsan dengan halangan Anda.
                </p>

                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Simulasi Pencatatan:</p>
                    <p class="text-xs font-mono font-bold text-slate-900">
                        Input: 13:00 – 15:00
                    </p>
                    <p class="text-xs font-mono text-indigo-600 font-semibold">
                        Sistem: {{ $exampleStart }} – {{ $exampleEnd }} {{ $timezoneAbbr ?? 'WIB' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('availabilityForm', (initial) => ({
                buffer: initial.buffer,
                recurring: initial.recurring,
                date: initial.date,
                start: initial.start,
                end: initial.end,

                toMinutes(time) {
                    const parts = time.split(':');
                    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                },
                format(minutes) {
                    const m = ((minutes % 1440) + 1440) % 1440;
                    return String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
                },
                get valid() {
                    return this.date && this.start && this.end
                        && this.toMinutes(this.end) > this.toMinutes(this.start);
                },
                get from() {
                    return this.format(this.toMinutes(this.start) - this.buffer);
                },
                get to() {
                    return this.format(this.toMinutes(this.end) + this.buffer);
                },
                get weekdayName() {
                    if (!this.date) return '';
                    const names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    return names[new Date(this.date + 'T00:00:00').getDay()];
                },
            }));
        });
    </script>
</x-app-layout>