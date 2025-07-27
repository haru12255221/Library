@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-lib-primary text-white hover:bg-lib-primary-hover focus:ring-lib-primary',
    'secondary' => 'bg-lib-secondary text-white hover:bg-lib-secondary-hover focus:ring-lib-secondary',
    'danger' => 'bg-lib-accent text-white hover:bg-lib-accent-hover focus:ring-lib-accent',
    'outline-primary' => 'border-2 border-lib-primary text-lib-primary hover:bg-lib-primary hover:text-white focus:ring-lib-primary',
    'outline-secondary' => 'border-2 border-lib-secondary text-lib-secondary hover:bg-lib-secondary hover:text-white focus:ring-lib-secondary',
    'outline-danger' => 'border-2 border-lib-accent text-lib-accent hover:bg-lib-accent hover:text-white focus:ring-lib-accent',
    'ghost' => 'text-lib-primary hover:bg-lib-primary-light focus:ring-lib-primary',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button 
        type="{{ $type }}" 
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif