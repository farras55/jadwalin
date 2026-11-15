@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-slate-200 bg-slate-50 focus:bg-white text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 rounded-lg shadow-xs transition-colors duration-200']) !!}>
