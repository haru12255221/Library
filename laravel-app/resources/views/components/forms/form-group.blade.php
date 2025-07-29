@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'id' => null
])

@php
$fieldId = $id ?? $name ?? uniqid('field_');
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $fieldId }}" class="block text-sm font-medium text-text-primary">
            {{ $label }}
            @if($required)
                <span class="text-danger ml-1">*</span>
            @endif
        </label>
    @endif
    
    <div>
        {{ $slot }}
    </div>
    
    @if($error)
        <p class="text-sm text-danger flex items-center gap-1" role="alert" aria-describedby="{{ $fieldId }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ $error }}
        </p>
    @endif
    
    @if($help)
        <p class="text-sm text-text-secondary" id="{{ $fieldId }}_help">{{ $help }}</p>
    @endif
</div>