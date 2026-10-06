<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">
            Pencatatan Kehadiran
        </h1>
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

        <!-- Card Status Hari Ini -->
        <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-2xs text-center flex flex-col items-center">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Shift Hari Ini</h2>
            
            @if ($todayShift)
                <p class="text-gray-600 mb-4 font-medium">
                    {{ \Carbon\Carbon::parse($todayShift->shift->start_time)->format('H:i') }} - 
                    {{ \Carbon\Carbon::parse($todayShift->shift->end_time)->format('H:i') }}
                </p>

                <!-- Live Clock WIB -->
                <div class="text-4xl md:text-5xl font-mono font-bold text-gray-900 mb-8 tracking-wider" id="live-clock">
                    --:--:--
                </div>

                <div class="flex gap-4 w-full justify-center">
                    @if (!$todayShift->clock_in_time)
                        <form action="{{ route('employee.attendance.clock-in') }}" method="POST" id="form-clock-in" class="w-full max-w-xs">
                            @csrf
                            <button type="submit" id="btn-clock-in" class="w-full flex items-center justify-center gap-2 bg-brand-primary text-white rounded-xl px-8 py-4 text-lg font-bold shadow-md hover:bg-brand-hover transition-colors min-h-[48px] min-w-[48px]">
                                <i data-lucide="log-in" class="w-6 h-6"></i>
                                CLOCK IN
                            </button>
                        </form>
                    @elseif (!$todayShift->clock_out_time)
                        <div class="flex flex-col items-center w-full max-w-xs space-y-4">
                            <div class="text-sm text-green-700 font-semibold bg-green-50 px-6 py-3 rounded-xl w-full border border-green-200 shadow-sm flex items-center justify-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Clock In: {{ \Carbon\Carbon::parse($todayShift->clock_in_time)->format('H:i:s') }}
                            </div>
                            <form action="{{ route('employee.attendance.clock-out') }}" method="POST" id="form-clock-out" class="w-full">
                                @csrf
                                <button type="submit" id="btn-clock-out" class="w-full flex items-center justify-center gap-2 bg-red-500 text-white rounded-xl px-8 py-4 text-lg font-bold hover:bg-red-600 transition-colors shadow-md min-h-[48px] min-w-[48px]">
                                    <i data-lucide="log-out" class="w-6 h-6"></i>
                                    CLOCK OUT
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex flex-col gap-3 w-full max-w-xs">
                            <div class="text-sm text-gray-700 bg-gray-50 px-6 py-3 rounded-xl border border-gray-200 flex justify-between">
                                <span>Clock In:</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($todayShift->clock_in_time)->format('H:i:s') }}</span>
                            </div>
                            <div class="text-sm text-gray-700 bg-gray-50 px-6 py-3 rounded-xl border border-gray-200 flex justify-between">
                                <span>Clock Out:</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($todayShift->clock_out_time)->format('H:i:s') }}</span>
                            </div>
                            <div class="text-md text-brand-primary font-bold mt-3 bg-brand-primary/10 py-3 rounded-xl">
                                Shift Selesai
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-gray-500 mb-6 py-8 flex flex-col items-center">
                    <i data-lucide="calendar-x" class="w-12 h-12 text-gray-300 mb-3"></i>
                    <p>Tidak ada shift yang ditugaskan untuk Anda hari ini.</p>
                </div>
            @endif
        </div>

        <!-- Tabel Riwayat Kehadiran 7 Hari Terakhir -->
        <div class="bg-white rounded-2xl border border-brand-border shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Kehadiran (7 Hari Terakhir)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Shift</th>
                            <th class="px-6 py-4">Clock In</th>
                            <th class="px-6 py-4">Clock Out</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentHistory as $history)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($history->shift->start_time)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($history->shift->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($history->shift->end_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $history->clock_in_time ? \Carbon\Carbon::parse($history->clock_in_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $history->clock_out_time ? \Carbon\Carbon::parse($history->clock_out_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badgeClass = 'bg-gray-100 text-gray-800 border-gray-200'; // scheduled
                                        $statusText = ucfirst($history->status ?? 'Scheduled');
                                        
                                        if ($history->status === 'present') {
                                            $badgeClass = 'bg-green-100 text-green-800 border-green-200';
                                        } elseif ($history->status === 'late') {
                                            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                        } elseif ($history->status === 'absent') {
                                            $badgeClass = 'bg-red-100 text-red-800 border-red-200';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada riwayat kehadiran dalam 7 hari terakhir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script Live Clock & Anti Double Submit -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live Clock WIB
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                // Initialize clock immediately
                updateClock();
                setInterval(updateClock, 1000);
                
                function updateClock() {
                    const now = new Date();
                    const options = { timeZone: 'Asia/Jakarta', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' };
                    // ID locale uses '.' instead of ':' for time, so it's better to manually format or replace
                    const timeString = now.toLocaleTimeString('en-GB', options); 
                    clockElement.textContent = timeString;
                }
            }

            // Anti-double submit for Clock In
            const formClockIn = document.getElementById('form-clock-in');
            if (formClockIn) {
                formClockIn.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-clock-in');
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    btn.innerHTML = '<i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i> MEMPROSES...';
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            }

            // Anti-double submit for Clock Out
            const formClockOut = document.getElementById('form-clock-out');
            if (formClockOut) {
                formClockOut.addEventListener('submit', function() {
                    const btn = document.getElementById('btn-clock-out');
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    btn.innerHTML = '<i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i> MEMPROSES...';
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            }
        });
    </script>
</x-app-layout>
