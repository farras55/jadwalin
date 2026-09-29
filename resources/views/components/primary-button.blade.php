@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'inline-flex items-center justify-center w-full px-4 py-3 bg-[#0B6B5A] border border-transparent rounded-lg font-semibold text-white tracking-wide hover:bg-[#085245] focus:bg-[#085245] active:bg-[#085245] focus:outline-none focus:ring-2 focus:ring-[#0B6B5A] focus:ring-offset-2 transition ease-in-out duration-150']) !!}>
    {{ $slot }}
</button>
