@props([
    'disabled' => false,
    'type' => 'text',
    'hasError' => false
])

@php
$baseClasses = 'w-full px-3 py-2 border rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0';

$stateClasses = $hasError 
    ? 'border-danger focus:border-danger focus:ring-danger' 
    : 'border-border-light focus:border-primary focus:ring-yellow-300';

$classes = $baseClasses . ' ' . $stateClasses;
@endphp

@if($type === 'textarea')
    <textarea @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</textarea>
@else
    <input type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
@endif
