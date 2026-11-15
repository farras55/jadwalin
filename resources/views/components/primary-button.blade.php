@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'inline-flex items-center justify-center w-full px-4 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-white tracking-wide hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) !!}>
    {{ $slot }}
</button>
