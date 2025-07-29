@props([
    'type' => 'spinner',
    'size' => 'md',
    'text' => null,
    'color' => 'primary'
])

@php
$sizes = [
    'sm' => 'w-4 h-4',
    'md' => 'w-6 h-6',
    'lg' => 'w-8 h-8',
    'xl' => 'w-12 h-12'
];

$colors = [
    'primary' => 'text-primary',
    'white' => 'text-white',
    'gray' => 'text-gray-500'
];

$sizeClass = $sizes[$size];
$colorClass = $colors[$color];
@endphp

@if($type === 'spinner')
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}>
        <svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        @if($text)
            <span class="ml-2 text-sm {{ $colorClass }}">{{ $text }}</span>
        @endif
    </div>
@elseif($type === 'dots')
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center space-x-1']) }}>
        <div class="w-2 h-2 {{ $colorClass }} bg-current rounded-full animate-bounce"></div>
        <div class="w-2 h-2 {{ $colorClass }} bg-current rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
        <div class="w-2 h-2 {{ $colorClass }} bg-current rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        @if($text)
            <span class="ml-3 text-sm {{ $colorClass }}">{{ $text }}</span>
        @endif
    </div>
@elseif($type === 'skeleton')
    <div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
        <div class="space-y-3">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
        </div>
    </div>
@endif