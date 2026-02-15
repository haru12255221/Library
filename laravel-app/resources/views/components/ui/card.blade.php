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
    'default' => 'shadow-sm',
    'lg' => 'shadow-md',
    'xl' => 'shadow-lg'
];

$baseClasses = 'bg-white rounded-lg';
$borderClasses = $border ? 'border border-border-light' : '';
$classes = $baseClasses . ' ' . $shadowClasses[$shadow] . ' ' . $borderClasses . ' ' . $paddingClasses[$padding];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @isset($header)
        <div class="border-b border-border-light pb-4 mb-4">
            {{ $header }}
        </div>
    @endisset
    
    <div>
        {{ $slot }}
    </div>
    
    @isset($footer)
        <div class="border-t border-border-light pt-4 mt-4">
            {{ $footer }}
        </div>
    @endisset
</div>