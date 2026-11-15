<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-900 font-medium">Pusat Notifikasi</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Pusat Notifikasi
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Lihat informasi, pengumuman jadwal, dan pemberitahuan aktivitas akun Anda.
                </p>
            </div>

            @if($unreadNotifCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-card transition"
                    >
                        <i data-lucide="check-check" class="w-4 h-4 text-indigo-600"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{
        selectedNotification: null,
        openDetail(notif) {
            this.selectedNotification = notif;
        },
        closeDetail() {
            this.selectedNotification = null;
        }
    }" @keydown.escape.window="closeDetail()">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-3.5 rounded-xl border border-emerald-200 shadow-card flex items-center gap-2.5 text-xs font-medium">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Notification List Card --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    <div 
                        class="flex items-start gap-3.5 px-4 sm:px-5 py-4 transition cursor-pointer {{ !$notification->is_read ? 'bg-indigo-50/30 hover:bg-indigo-50/50' : 'hover:bg-slate-50/80' }}"
                        @click="openDetail({
                            id: {{ $notification->id }},
                            title: {{ json_encode($notification->title) }},
                            message: {{ json_encode($notification->message) }},
                            type: {{ json_encode($notification->type ?? 'Info') }},
                            time: {{ json_encode($notification->created_at->translatedFormat('d F Y, H:i') . ' WIB (' . $notification->created_at->diffForHumans() . ')') }},
                            isRead: {{ $notification->is_read ? 'true' : 'false' }},
                            readUrl: {{ json_encode(route('notifications.read', $notification)) }}
                        })"
                    >
                        {{-- Unread Status Indicator --}}
                        <div class="mt-1.5 shrink-0">
                            <span class="block h-2 w-2 rounded-full {{ !$notification->is_read ? 'bg-indigo-600 ring-2 ring-indigo-200' : 'bg-slate-300' }}"></span>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                    {{ $notification->title }}
                                </h2>
                                <span class="text-[11px] text-slate-400 shrink-0 font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-600 leading-relaxed line-clamp-2">
                                {{ $notification->message }}
                            </p>

                            <div class="mt-2.5 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    @if($notification->type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $notification->type }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                        Lihat Rincian &rarr;
                                    </span>

                                    @if(!$notification->is_read)
                                        <form
                                            action="{{ route('notifications.read', $notification) }}"
                                            method="POST"
                                            @click.stop
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="text-[11px] font-semibold text-slate-500 hover:text-slate-800"
                                            >
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Belum Ada Notifikasi
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm">
                            Pemberitahuan aktivitas, perubahan jadwal, dan persetujuan akan tampil di halaman ini.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        @endif

        {{-- MODAL DETAIL NOTIFIKASI --}}
        <div 
            x-show="selectedNotification !== null" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" 
            style="display: none;"
        >
            {{-- Backdrop --}}
            <div 
                x-show="selectedNotification !== null"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" 
                @click="closeDetail()"
            ></div>

            {{-- Dialog Content --}}
            <div 
                x-show="selectedNotification !== null"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-white rounded-xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden z-10"
                @click.stop
            >
                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 bg-slate-50/50">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200" x-text="selectedNotification?.type || 'Notifikasi'"></span>
                            <span class="text-[11px] text-slate-400" x-text="selectedNotification?.time || ''"></span>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900" x-text="selectedNotification?.title || ''"></h3>
                    </div>

                    <button 
                        type="button" 
                        @click="closeDetail()" 
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                        title="Tutup"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5">
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-wrap" x-text="selectedNotification?.message || ''"></p>
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
                    <template x-if="selectedNotification && !selectedNotification.isRead">
                        <form :action="selectedNotification?.readUrl" method="POST">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition"
                            >
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Tandai Sudah Dibaca</span>
                            </button>
                        </form>
                    </template>
                    <template x-if="selectedNotification && selectedNotification.isRead">
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1">
                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Sudah dibaca
                        </span>
                    </template>

                    <button 
                        type="button" 
                        @click="closeDetail()" 
                        class="px-4 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
