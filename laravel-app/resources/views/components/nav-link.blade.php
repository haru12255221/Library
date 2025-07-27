@props([
    'href',
    'activeRoutes' => [],
])

@php
    $routes = is_array($activeRoutes) ? $activeRoutes : [$activeRoutes];
    $isActive = request()->routeIs(...$routes);
@endphp

<a href="{{ $href }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
    <span class="{{ $isActive ? 'font-semibold text-primary' : '' }}">
        {{ $slot }}
    </span>
    @if($isActive)
        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
    @endif
</a>
