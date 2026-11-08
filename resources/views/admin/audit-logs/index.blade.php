<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#6C5CE7]/10 text-[#6C5CE7] border border-[#6C5CE7]/20">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                        <span>Keamanan &amp; Audit Platform</span>
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-[#7C7896]">Super Admin Executive View</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-[#2D2A3E] tracking-tight">
                    Audit Trail Logging
                </h1>
                <p class="text-xs md:text-sm text-[#7C7896] mt-0.5">
                    Rekam jejak mutasi data dan aktivitas operasional seluruh sistem secara transparan untuk pengawasan bisnis.
                </p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        modalOpen: false,
        activeLog: null,
        diffRows: [],
        fieldDictionary: {
            'clock_out_time': 'Clock Out Time',
            'clock_in_time': 'Clock In Time',
            'is_auto_clockout': 'Is Auto Clockout',
            'regular_hours': 'Jam Kerja Reguler',
            'overtime_hours': 'Jam Kerja Lembur',
            'title': 'Judul Shift',
            'start_time': 'Jam Mulai',
            'end_time': 'Jam Selesai',
            'date': 'Tanggal',
            'quota': 'Kuota Karyawan',
            'status': 'Status',
            'department_id': 'Departemen',
            'position_id': 'Posisi / Jabatan',
            'user_id': 'Karyawan',
            'reason': 'Alasan / Keterangan',
            'note': 'Catatan',
            'review_note': 'Catatan Peninjauan',
            'latitude': 'Titik Lintang',
            'longitude': 'Titik Bujur',
            'radius_meters': 'Radius Presensi (Meter)',
            'address': 'Alamat Kantor/Cabang',
            'max_regular_hours': 'Batas Jam Reguler',
            'max_overtime_hours': 'Batas Jam Lembur',
            'break_minutes': 'Durasi Istirahat',
            'is_holiday': 'Hari Libur Nasional',
            'color': 'Warna Label',
            'type': 'Jenis / Tipe'
        },
        hiddenFields: [
            'id', 
            'company_id', 
            'created_at', 
            'updated_at', 
            'deleted_at', 
            'remember_token', 
            'password', 
            'two_factor_secret', 
            'two_factor_recovery_codes', 
            'email_verified_at'
        ],

        formatVal(key, val) {
            if (val === null || val === undefined || val === '') return '';
            if (typeof val === 'boolean') {
                return val ? 'Ya (Aktif)' : 'Tidak (Nonaktif)';
            }
            if (key && (key.startsWith('is_') || key.startsWith('has_'))) {
                if (val === 1 || val === '1' || val === true) return 'Ya (Aktif)';
                if (val === 0 || val === '0' || val === false) return 'Tidak (Nonaktif)';
            }
            if (typeof val === 'object') {
                return JSON.stringify(val);
            }
            return String(val);
        },

        buildDiffRows(oldVals, newVals) {
            const oldObj = (oldVals && typeof oldVals === 'object') ? oldVals : {};
            const newObj = (newVals && typeof newVals === 'object') ? newVals : {};

            const allKeys = Array.from(new Set([...Object.keys(oldObj), ...Object.keys(newObj)]))
                .filter(k => !this.hiddenFields.includes(k));

            if (allKeys.length === 0) {
                return [];
            }

            return allKeys.map(key => {
                const hasOld = Object.prototype.hasOwnProperty.call(oldObj, key) && oldObj[key] !== null && oldObj[key] !== undefined && oldObj[key] !== '';
                const hasNew = Object.prototype.hasOwnProperty.call(newObj, key) && newObj[key] !== null && newObj[key] !== undefined && newObj[key] !== '';

                const rawOld = oldObj[key];
                const rawNew = newObj[key];

                const oldStr = this.formatVal(key, rawOld);
                const newStr = this.formatVal(key, rawNew);

                const isChanged = hasOld && hasNew && (String(oldStr) !== String(newStr));
                const isAdded = !hasOld && hasNew;
                const isRemoved = hasOld && !hasNew;

                const label = this.fieldDictionary[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                return {
                    key: key,
                    label: label,
                    oldValue: oldStr,
                    newValue: newStr,
                    isChanged: isChanged,
                    isAdded: isAdded,
                    isRemoved: isRemoved,
                };
            });
        },

        openModal(log) {
            this.activeLog = log;
            this.diffRows = this.buildDiffRows(log.old_values, log.new_values);
            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        closeModal() {
            this.modalOpen = false;
            this.activeLog = null;
            this.diffRows = [];
        }
    }" @keydown.escape.window="closeModal()" class="space-y-6">

        {{-- ========================================================= --}}
        {{-- 1. KARTU RINGKASAN EKSEKUTIF (EXECUTIVE STAT CARDS)       --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Kartu 1: Total Aktivitas Hari Ini --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Aktivitas Hari Ini</p>
                    <h3 class="text-2xl font-bold text-[#2D2A3E] mt-1">{{ number_format($stats['total_today'] ?? 0) }}</h3>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Tercatat sejak 00:00 {{ $timezoneAbbr ?? 'WIB' }}</p>
                </div>
                <div class="p-3 bg-[#6C5CE7]/10 text-[#6C5CE7] rounded-xl shrink-0">
                    <i data-lucide="activity" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 2: Perubahan Data / Edit --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Perubahan Data</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['total_updates'] ?? 0) }}</h3>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Revisi &amp; pengeditan jadwal</p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl shrink-0">
                    <i data-lucide="edit-3" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 3: Data Dihapus (Aksen Merah Soft) --}}
            <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Data Dihapus</p>
                    <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['total_deletes'] ?? 0) }}</h3>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Penghapusan data penting</p>
                </div>
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                    <i data-lucide="trash-2" class="h-6 w-6"></i>
                </div>
            </div>

            {{-- Kartu 4: Aktivitas Sistem Otomatis --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E0F7] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-[#7C7896] uppercase tracking-wider">Aktivitas Sistem</p>
                    <h3 class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['total_system'] ?? 0) }}</h3>
                    <p class="text-[11px] text-[#7C7896] mt-0.5">Auto Clockout &amp; Broadcast</p>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl shrink-0">
                    <i data-lucide="cpu" class="h-6 w-6"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 2. BAR FILTER & PENCARIAN YANG RAMAH AWAM                 --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] p-4 sm:p-5 shadow-2xs">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                    {{-- Dropdown 1: Semua Jenis Aktivitas --}}
                    <div>
                        <label for="filter-action" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Semua Jenis Aktivitas
                        </label>
                        <select id="filter-action" name="action" 
                                class="w-full bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition font-medium">
                            <option value="all" {{ $selectedAction === 'all' ? 'selected' : '' }}>Semua Jenis Aktivitas</option>
                            @foreach ($availableActions as $actionKey => $actionLabel)
                                <option value="{{ $actionKey }}" {{ $selectedAction === $actionKey ? 'selected' : '' }}>
                                    {{ $actionLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown 2: Semua Fitur Aplikasi --}}
                    <div>
                        <label for="filter-model" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Semua Fitur Aplikasi
                        </label>
                        <select id="filter-model" name="model_type" 
                                class="w-full bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition font-medium">
                            <option value="all" {{ $selectedModel === 'all' ? 'selected' : '' }}>Semua Fitur Aplikasi</option>
                            @foreach ($availableModels as $modelKey => $modelLabel)
                                <option value="{{ $modelKey }}" {{ $selectedModel === $modelKey ? 'selected' : '' }}>
                                    {{ $modelLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Field Pencarian --}}
                    <div>
                        <label for="search" class="block text-xs font-semibold text-[#2D2A3E] mb-1.5">
                            Pencarian Kata Kunci
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="search" class="h-4 w-4"></i>
                            </div>
                            <input type="text" id="search" name="search" value="{{ $search }}"
                                   placeholder="Cari nama staf, fitur, atau lokasi IP..."
                                   class="w-full pl-10 bg-[#F5F3FF]/40 border border-[#E2E0F7] text-[#2D2A3E] text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-[#6C5CE7] focus:border-[#6C5CE7] transition placeholder:text-gray-400">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold shadow-2xs transition-all w-full sm:w-auto">
                        <i data-lucide="search" class="h-4 w-4"></i>
                        <span>Filter Log</span>
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" 
                       title="Reset Filter"
                       class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-600 font-semibold transition-all">
                        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. TABEL UTAMA AUDIT LOG (KETERBACAAN MAKSIMAL)           --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-[#E2E0F7] shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E2E0F7] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7]">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-[#2D2A3E]">Catatan Audit Aktivitas</h2>
                        <p class="text-[11px] sm:text-xs text-[#7C7896]">Total {{ $logs->total() }} aktivitas mutasi data tercatat di sistem</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                    <thead class="bg-slate-50/80 text-gray-600 font-bold border-b border-gray-200 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Timestamp ({{ $timezoneAbbr ?? 'WIB' }})</th>
                            <th class="px-5 py-3.5">Aktor</th>
                            <th class="px-5 py-3.5 text-center">Tipe Aksi</th>
                            <th class="px-5 py-3.5">Model Terkait</th>
                            <th class="px-5 py-3.5">IP Address</th>
                            <th class="px-5 py-3.5 text-center">Rincian Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            @php
                                $actionUpper = strtoupper($log->action ?? '');
                                $formattedTime = $log->formattedCreatedAt($companyTimezone ?? null);
                                $modelBasename = class_basename($log->model_type ?? '');
                                $payload = [
                                    'id' => $log->id,
                                    'action' => $actionUpper,
                                    'action_label' => $log->action_label,
                                    'action_badge_class' => $log->action_badge_class,
                                    'model_type' => $modelBasename,
                                    'model_label' => $log->model_label,
                                    'model_id' => $log->model_id,
                                    'actor' => $log->user ? $log->user->name : 'Sistem / Anonim',
                                    'role' => $log->user ? ($log->user->role_title ?? ucfirst(str_replace('_', ' ', $log->user->role ?? 'Staf'))) : 'System',
                                    'timestamp' => $formattedTime,
                                    'ip_address' => $log->ip_address ?? '-',
                                    'user_agent' => $log->user_agent ?? '-',
                                    'old_values' => $log->old_values,
                                    'new_values' => $log->new_values,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                {{-- 1. Timestamp --}}
                                <td class="px-5 py-4 whitespace-nowrap text-gray-700 font-medium text-xs">
                                    {{ $formattedTime }}
                                </td>

                                {{-- 2. Aktor --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($log->user)
                                        <div class="font-bold text-[#2D2A3E]">
                                            {{ $log->user->name }}
                                        </div>
                                        <div class="text-xs text-[#7C7896] mt-0.5">
                                            {{ $log->user->role_title ?? ucfirst(str_replace('_', ' ', $log->user->role ?? 'Staf')) }}
                                        </div>
                                    @else
                                        <div class="font-bold text-[#2D2A3E] flex items-center gap-1.5">
                                            <i data-lucide="bot" class="w-3.5 h-3.5 text-[#6C5CE7]"></i>
                                            <span>Sistem / Anonim</span>
                                        </div>
                                        <div class="text-xs text-[#7C7896] mt-0.5">
                                            Role: System
                                        </div>
                                    @endif
                                </td>

                                {{-- 3. Tipe Aksi (Badge Colors) --}}
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $log->action_badge_class }}" title="Tipe Sistem: {{ $actionUpper }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $log->action_dot_class }}"></span>
                                        <span>{{ $log->action_label }}</span>
                                        <span class="sr-only">({{ $actionUpper }})</span>
                                    </span>
                                </td>

                                {{-- 4. Model Terkait --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">
                                        {{ $log->model_label }}
                                    </div>
                                    @if ($log->model_id)
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">
                                            <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[11px]">#{{ $log->model_id }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- 5. IP Address (Redup) --}}
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-xs text-gray-400">
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                {{-- 6. Tombol Detail Perubahan (Lihat Perubahan) --}}
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <button type="button" 
                                            @click="openModal({{ json_encode($payload) }})"
                                            title="Lihat Perubahan"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-gray-700 text-xs font-semibold border border-slate-200 shadow-2xs transition cursor-pointer">
                                        <i data-lucide="file-text" class="h-3.5 w-3.5 text-[#6C5CE7]"></i>
                                        <span>Lihat Perubahan</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                            <i data-lucide="shield" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-600">Belum ada catatan aktivitas</p>
                                        <p class="text-xs text-gray-400">Mutasi data dari fitur yang diawasi akan tercatat secara otomatis di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-slate-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================= --}}
        {{-- 4. MODAL RINCIAN PERUBAHAN DATA (LIGHT MODE TABLE)        --}}
        {{-- ========================================================= --}}
        <div x-show="modalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            
            {{-- Backdrop Overlay --}}
            <div x-show="modalOpen" 
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-4xl border border-[#E2E0F7]">
                    
                    {{-- 1. Header Modal Pop-Up --}}
                    <div class="px-6 py-4 border-b border-[#E2E0F7] flex items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7]">
                                <i data-lucide="file-text" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 id="modal-title" class="text-base font-bold text-[#2D2A3E] flex items-center gap-2">
                                    <span>Rincian Perubahan Data</span>
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold border"
                                          :class="activeLog?.action_badge_class || 'bg-amber-50 text-amber-700 border-amber-200'"
                                          x-text="activeLog?.action_label || activeLog?.action"></span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Fitur: <span class="font-semibold text-gray-800" x-text="activeLog?.model_label"></span>
                                    <span x-show="activeLog?.model_id" x-text="' #' + activeLog?.model_id"></span>
                                    • Pelaku: <span class="font-semibold text-gray-800" x-text="activeLog?.actor"></span>
                                    • <span x-text="activeLog?.timestamp"></span>
                                </p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="closeModal()" 
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        {{-- 2. Box Ringkasan Metadata (Container Abu-abu Terang 3 Kolom) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold tracking-wider">ALAMAT IP</span>
                                <span class="font-mono text-gray-800 font-semibold text-xs mt-0.5 block" x-text="activeLog?.ip_address || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold tracking-wider">JABATAN / PERAN</span>
                                <span class="text-gray-800 font-semibold capitalize text-xs mt-0.5 block" x-text="activeLog?.role || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold tracking-wider">PERANGKAT / BROWSER</span>
                                <span class="text-gray-600 font-mono text-[11px] truncate mt-0.5 block" :title="activeLog?.user_agent" x-text="activeLog?.user_agent || '-'"></span>
                            </div>
                        </div>

                        {{-- 3. Tabel Komparasi Rincian Perubahan (Light Mode Table) --}}
                        <template x-if="diffRows.length > 0">
                            <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs">
                                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                                    <thead class="bg-slate-50/80 text-gray-500 text-[10px] sm:text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                                        <tr>
                                            <th class="px-5 py-3 w-1/3">INFORMASI YANG DIUBAH</th>
                                            <th class="px-5 py-3 w-1/3">DATA SEBELUM DIUBAH</th>
                                            <th class="px-5 py-3 w-1/3">DATA SESUDAH DIUBAH</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(row, idx) in diffRows" :key="idx">
                                            <tr class="hover:bg-slate-50/40 transition-colors">
                                                {{-- Kolom 1: INFORMASI YANG DIUBAH --}}
                                                <td class="px-5 py-3.5 align-middle">
                                                    <div class="font-bold text-gray-800 text-xs sm:text-sm" x-text="row.label"></div>
                                                    <div class="flex items-center gap-1.5 mt-1">
                                                        <span class="text-xs font-mono text-gray-400" x-text="row.key"></span>
                                                        <template x-if="row.isAdded">
                                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded px-1.5 py-0.5 text-[10px] font-semibold">
                                                                Data Baru
                                                            </span>
                                                        </template>
                                                        <template x-if="row.isChanged">
                                                            <span class="bg-amber-50 text-amber-700 border border-amber-200 rounded px-1.5 py-0.5 text-[10px] font-semibold">
                                                                Diperbarui
                                                            </span>
                                                        </template>
                                                        <template x-if="row.isRemoved">
                                                            <span class="bg-rose-50 text-rose-700 border border-rose-200 rounded px-1.5 py-0.5 text-[10px] font-semibold">
                                                                Dihapus
                                                            </span>
                                                        </template>
                                                    </div>
                                                </td>

                                                {{-- Kolom 2: DATA SEBELUM DIUBAH --}}
                                                <td class="px-5 py-3.5 align-middle">
                                                    <template x-if="!row.oldValue || row.isAdded">
                                                        <span class="text-xs text-gray-400 italic">(Tidak ada data sebelumnya)</span>
                                                    </template>
                                                    <template x-if="row.oldValue && row.isChanged">
                                                        <span class="bg-rose-50 text-rose-600 border border-rose-100 rounded-lg px-3 py-1 text-xs font-mono inline-block" x-text="row.oldValue"></span>
                                                    </template>
                                                    <template x-if="row.oldValue && !row.isChanged && !row.isAdded">
                                                        <span class="text-xs font-mono text-gray-600 px-1" x-text="row.oldValue"></span>
                                                    </template>
                                                </td>

                                                {{-- Kolom 3: DATA SESUDAH DIUBAH --}}
                                                <td class="px-5 py-3.5 align-middle">
                                                    <template x-if="row.isChanged">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs font-mono text-gray-800 font-medium" x-text="row.newValue"></span>
                                                            <span class="text-emerald-600 font-medium text-xs">• Baru</span>
                                                        </div>
                                                    </template>
                                                    <template x-if="row.isAdded">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs font-mono text-gray-800 font-medium" x-text="row.newValue"></span>
                                                            <span class="text-emerald-600 font-medium text-xs">• Baru</span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!row.isChanged && !row.isAdded">
                                                        <span class="text-xs font-mono text-gray-600 px-1" x-text="row.newValue"></span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- Kondisi Tidak Ada Kolom Khusus (Aktivitas Sistem / Broadcast) --}}
                        <template x-if="diffRows.length === 0">
                            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-gray-200">
                                <div class="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center mx-auto mb-2.5">
                                    <i data-lucide="info" class="w-5 h-5"></i>
                                </div>
                                <p class="text-sm font-bold text-[#2D2A3E]">Aktivitas Sistem Tercatat</p>
                                <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                                    Aktivitas ini dicatat sebagai peristiwa operasional sistem umum tanpa perubahan khusus pada nilai kolom data.
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- 4. Footer Modal --}}
                    <div class="px-6 py-4 border-t border-[#E2E0F7] bg-white flex justify-end">
                        <button type="button" 
                                @click="closeModal()" 
                                class="bg-gray-900 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-gray-800 transition shadow-2xs cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
