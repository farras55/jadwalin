@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800 focus:border-[#0B6B5A] focus:ring-[#0B6B5A] rounded-lg shadow-sm transition-colors duration-200']) !!}>
