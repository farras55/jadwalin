<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $roleTitle ?? __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-lg font-medium text-gray-900">Selamat datang, <strong>{{ Auth::user()->name }}</strong>!</p>
                    <p class="text-sm text-gray-600 mt-1">Anda login sebagai: <span class="capitalize font-semibold text-indigo-600">{{ Auth::user()->role }}</span></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
