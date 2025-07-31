@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'text-sm text-gray-500 mt-1 ' . $class]) }}>
    {{ $slot }}
</div>