@php
    // Contoh di kartu Auto-Buffer: 13:00-15:00 -> tercatat dengan buffer perusahaan.
    $exampleStart = \Carbon\Carbon::createFromTime(13, 0)->subMinutes($bufferMinutes)->format('H:i');
    $exampleEnd = \Carbon\Carbon::createFromTime(15, 0)->addMinutes($bufferMinutes)->format('H:i');
    $inputClass = 'block w-full rounded-xl border-brand-border text-sm text-[#2D2A3E] shadow-none focus:border-brand-primary focus:ring-brand-primary min-h-[44px]';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Ajukan Halangan
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-1">
                    Atur jadwal halangan Anda agar penjadwalan tidak bentrok.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
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
                  class="bg-white rounded-2xl border border-brand-border p-6 shadow-2xs space-y-5">
                @csrf
                <input type="hidden" name="is_recurring" :value="recurring ? 1 : 0">

                <h2 class="text-lg font-bold text-[#2D2A3E]">Ajukan Halangan</h2>

                {{-- Toggle jenis halangan --}}
                <div class="inline-flex rounded-xl border border-brand-border bg-[#F5F3FF] p-1" role="group" aria-label="Jenis halangan">
                    <button type="button" @click="recurring = false"
                            :class="!recurring ? 'bg-brand-primary text-white shadow-sm' : 'text-[#7C7896]'"
                            class="min-h-[44px] px-4 rounded-lg text-sm font-semibold transition">
                        Tanggal Tertentu
                    </button>
                    <button type="button" @click="recurring = true"
                            :class="recurring ? 'bg-brand-primary text-white shadow-sm' : 'text-[#7C7896]'"
                            class="min-h-[44px] px-4 rounded-lg text-sm font-semibold transition">
                        Berulang Mingguan
                    </button>
                </div>

                {{-- Tanggal --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-[#2D2A3E] mb-1.5">Tanggal</label>
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
                        <label for="start_time" class="block text-sm font-semibold text-[#2D2A3E] mb-1.5">Jam Mulai</label>
                        <input type="time" id="start_time" name="start_time" x-model="start"
                               value="{{ old('start_time') }}" class="{{ $inputClass }}">
                        @error('start_time')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-[#2D2A3E] mb-1.5">Jam Selesai</label>
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
                        <label for="reason" class="block text-sm font-semibold text-[#2D2A3E]">Alasan</label>
                        <span class="text-xs text-[#7C7896]">Wajib diisi</span>
                    </div>
                    <input type="text" id="reason" name="reason" maxlength="255"
                           value="{{ old('reason') }}" placeholder="Contoh: Kuliah Praktikum"
                           class="{{ $inputClass }}">
                    @error('reason')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Preview buffer real-time --}}
                <div class="flex items-start gap-3 rounded-xl bg-[#EDE9FE] p-4 text-sm text-brand-primary">
                    <i data-lucide="info" class="h-5 w-5 shrink-0 mt-0.5"></i>
                    <p>
                        <template x-if="!valid">
                            <span>Buffer {{ $bufferMinutes }} menit akan ditambahkan otomatis sebelum dan sesudah halangan.</span>
                        </template>
                        <template x-if="valid">
                            <span>
                                Tercatat: <strong x-text="from + ' - ' + to"></strong>
                                (buffer {{ $bufferMinutes }} menit sebelum dan sesudah).
                            </span>
                        </template>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center justify-center min-h-[44px] px-6 rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm">
                        Ajukan Halangan
                    </button>
                    <a href="{{ route('employee.availability') }}"
                       class="inline-flex items-center justify-center min-h-[44px] px-4 text-sm font-semibold text-[#7C7896] hover:text-[#2D2A3E]">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Kartu Auto-Buffer --}}
        <div>
            <div class="relative overflow-hidden rounded-2xl bg-brand-primary p-6 text-white shadow-2xs">
                <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-12 -left-8 h-36 w-36 rounded-full bg-white/[0.08]"></div>
                </div>
                <div class="relative">
                    <h2 class="flex items-center gap-2 text-lg font-bold">
                        <i data-lucide="info" class="h-5 w-5"></i>
                        Auto-Buffer {{ $bufferMinutes }} Menit
                    </h2>
                    <p class="mt-2 text-sm text-white/85">
                        Sistem menambah jeda {{ $bufferMinutes }} menit sebelum dan sesudah halangan agar penugasan tidak mepet.
                    </p>
                    <div class="mt-4 rounded-xl border border-white/25 bg-white/15 px-4 py-3 text-sm font-semibold">
                        13:00&ndash;15:00 &rarr; tercatat {{ $exampleStart }}&ndash;{{ $exampleEnd }}
                    </div>
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