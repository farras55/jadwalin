<x-app-layout>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Pusat Notifikasi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Lihat informasi dan pemberitahuan terbaru untuk akun Anda.
                </p>
            </div>

            {{-- Tandai Semua Dibaca --}}
            @if($unreadNotifCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        {{-- Check-check icon --}}
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="m18 7 4 4-4 4"></path>
                            <path d="m6 7 4 4-4 4"></path>
                            <path d="M2 12h20"></path>
                        </svg>

                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Notification List --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            @forelse($notifications as $notification)

                <div class="flex items-start gap-4 border-b border-gray-100 px-5 py-4 last:border-b-0
                    {{ !$notification->is_read ? 'bg-blue-50/50' : 'bg-white' }}">

                    {{-- Unread Marker --}}
                    <div class="mt-2 flex h-2.5 w-2.5 shrink-0 items-center justify-center">
                        @if(!$notification->is_read)
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        @endif
                    </div>

                    {{-- Notification Content --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900">
                                    {{ $notification->title }}
                                </h2>

                                <p class="mt-1 text-sm leading-relaxed text-gray-600">
                                    {{ $notification->message }}
                                </p>
                            </div>

                            <span class="shrink-0 text-xs text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Type --}}
                        @if($notification->type)
                            <span class="mt-2 inline-block rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                {{ $notification->type }}
                            </span>
                        @endif

                        {{-- Mark as Read --}}
                        @if(!$notification->is_read)
                            <form
                                action="{{ route('notifications.read', $notification) }}"
                                method="POST"
                                class="mt-3"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800"
                                >
                                    Tandai sudah dibaca
                                </button>
                            </form>
                        @endif

                    </div>
                </div>

            @empty

                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">

                    {{-- Inbox Icon --}}
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="text-gray-400"
                        >
                            <path d="M22 12h-6l-2 3h-4l-2-3H2"></path>
                            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"></path>
                        </svg>
                    </div>

                    <h2 class="text-base font-semibold text-gray-900">
                        Belum Ada Notifikasi
                    </h2>

                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                        Notifikasi terbaru akan muncul di halaman ini.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
</x-app-layout>