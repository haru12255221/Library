@props([
    'href',
    'activeRoutes' => [],
])

@php
    $routes = is_array($activeRoutes) ? $activeRoutes : [$activeRoutes];
    $isActive = request()->routeIs(...$routes);
@endphp

<a href="{{ $href }}" class="relative pb-4 text-text-primary {{ $isActive ? '' : 'hover:underline hover:underline-offset-4 hover:decoration-2' }}">
    <span class="{{ $isActive ? 'font-semibold' : '' }}">
        {{ $slot }}
    </span>
    @if($isActive)
        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-text-primary rounded-full"></span>
    @endif
</a>
