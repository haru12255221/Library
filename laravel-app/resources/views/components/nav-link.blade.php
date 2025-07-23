@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 text-sm font-semibold leading-5 text-primary focus:outline-none transition duration-150 ease-in-out no-underline'
            : 'inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-text-primary hover:underline focus:outline-none focus:underline transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
