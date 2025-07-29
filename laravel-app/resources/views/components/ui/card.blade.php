@props([
    'padding' => 'default',
    'shadow' => 'default',
    'border' => true
])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8'
];

$shadowClasses = [
    'none' => '',
    'sm' => 'shadow-sm',
    'default' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl'
];

$baseClasses = 'bg-white rounded-lg';
$borderClasses = $border ? 'border border-gray-200' : '';
$classes = $baseClasses . ' ' . $shadowClasses[$shadow] . ' ' . $borderClasses . ' ' . $paddingClasses[$padding];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @isset($header)
        <div class="border-b border-gray-200 pb-4 mb-4">
            {{ $header }}
        </div>
    @endisset
    
    <div>
        {{ $slot }}
    </div>
    
    @isset($footer)
        <div class="border-t border-gray-200 pt-4 mt-4">
            {{ $footer }}
        </div>
    @endisset
</div>