@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-[#0B6B5A] text-left text-base font-medium text-[#0B6B5A] bg-[#0B6B5A]/10 focus:outline-none focus:text-[#085245] focus:bg-[#0B6B5A]/20 focus:border-[#085245] transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:bg-slate-50 focus:border-slate-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
