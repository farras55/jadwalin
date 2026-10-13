@php
    $statusMap = [
        'pending_manager' => ['Menunggu Manajer', 'bg-amber-100 text-amber-800 border-amber-200'],
        'approved' => ['Disetujui', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
        'rejected' => ['Ditolak', 'bg-red-100 text-red-800 border-red-200'],
    ];
    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Ketersediaan Waktu
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

    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Riwayat halangan --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-[#2D2A3E]">Riwayat Halangan</h2>
                    <a href="{{ route('employee.availability.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 min-h-[44px] rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-hover transition shadow-sm">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        <span>Ajukan Halangan</span>
                    </a>
                </div>

                @forelse ($availabilities as $item)
                    @php
                        [$statusLabel, $statusClass] = $statusMap[$item->status] ?? [$item->status, 'bg-gray-100 text-gray-700 border-gray-200'];
                        // Waktu tersimpan sudah termasuk buffer; waktu input dihitung mundur dari buffer saat ini.
                        $inputStart = $item->start_time->copy()->addMinutes($bufferMinutes);
                        $inputEnd = $item->end_time->copy()->subMinutes($bufferMinutes);
                    @endphp
                    <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-[#2D2A3E]">
                                    {{ $inputStart->copy()->locale('id')->translatedFormat('l, j M Y') }}
                                    <span class="text-[#7C7896] font-semibold">&bull; {{ $inputStart->format('H:i') }}&ndash;{{ $inputEnd->format('H:i') }}</span>
                                </p>
                                <p class="text-sm text-[#7C7896] mt-1 break-words">
                                    {{ $item->reason }}
                                    <span class="mx-1">&bull;</span>
                                    Tercatat {{ $item->start_time->format('H:i') }}&ndash;{{ $item->end_time->format('H:i') }}
                                </p>
                                @if ($item->is_recurring)
                                    <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary">
                                        <i data-lucide="repeat" class="h-3.5 w-3.5"></i>
                                        Berulang setiap {{ $dayNames[$item->day_of_week] ?? '-' }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                @if ($item->status === 'pending_manager')
                                    <form action="{{ route('employee.availability.destroy', $item) }}" method="POST"
                                          onsubmit="return confirm('Batalkan pengajuan halangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 min-h-[44px] px-2 text-sm font-semibold text-brand-primary hover:text-brand-hover">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-brand-border p-8 md:p-12 text-center shadow-2xs">
                        <div class="max-w-md mx-auto flex flex-col items-center">
                            <div class="w-16 h-16 bg-[#F5F3FF] border border-[#E2E0F7] rounded-2xl flex items-center justify-center text-[#6C5CE7] mb-4">
                                <i data-lucide="calendar-off" class="h-8 w-8"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#2D2A3E] mb-2">Belum ada halangan</h3>
                            <p class="text-sm text-[#7C7896] leading-relaxed">
                                Anda belum mengajukan halangan. Ajukan jika ada kuliah, kegiatan keluarga, atau keperluan lain agar tidak dijadwalkan bentrok.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Alur persetujuan --}}
            <div>
                <div class="bg-white rounded-2xl border border-brand-border p-5 shadow-2xs">
                    <h2 class="text-lg font-bold text-[#2D2A3E] mb-4">Alur Persetujuan</h2>
                    <ul class="space-y-4">
                        @foreach ([
                            'Ajukan halangan beserta alasan',
                            'Manajer menyetujui atau menolak',
                            'Hanya halangan yang disetujui dipakai saat penyusunan jadwal',
                        ] as $step)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F5F3FF] text-brand-primary">
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                </span>
                                <span class="text-sm text-[#2D2A3E]">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-5 flex items-start gap-2 rounded-xl bg-[#F5F3FF] p-3 text-xs text-brand-primary">
                        <i data-lucide="info" class="h-4 w-4 shrink-0 mt-0.5"></i>
                        <span>Sistem menambah buffer {{ $bufferMinutes }} menit sebelum dan sesudah halangan.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>