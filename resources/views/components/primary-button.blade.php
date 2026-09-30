@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'inline-flex items-center justify-center w-full px-4 py-3 bg-[#6C5CE7] border border-transparent rounded-lg font-semibold text-white tracking-wide hover:bg-[#4F46E5] focus:bg-[#4F46E5] active:bg-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) !!}>
    {{ $slot }}
</button>
