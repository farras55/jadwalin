@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#E2E0F7] bg-slate-50 focus:bg-white text-[#2D2A3E] focus:border-[#6C5CE7] focus:ring-[#6C5CE7] rounded-lg shadow-sm transition-colors duration-200']) !!}>
