@props([
    'type' => 'info',
    'dismissible' => false
])

@php
$baseClasses = 'px-4 py-3 rounded-md border flex items-center gap-3 mb-4 shadow-sm';

// アクセシビリティを考慮したコントラスト比の改善
$typeClasses = [
    'success' => 'bg-lib-secondary-light border-lib-secondary text-lib-secondary-900 ring-1 ring-lib-secondary/20',
    'error' => 'bg-lib-accent-light border-lib-accent text-lib-accent-900 ring-1 ring-lib-accent/20',
    'warning' => 'bg-yellow-50 border-yellow-500 text-yellow-900 ring-1 ring-yellow-500/20',
    'info' => 'bg-lib-primary-light border-lib-primary text-lib-primary-900 ring-1 ring-lib-primary/20',
];

// アクセシビリティ向上のためのアイコンとaria-label
$icons = [
    'success' => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'error' => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'warning' => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
    'info' => '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
];

// アクセシビリティ向上のためのテキストラベル
$typeLabels = [
    'success' => '成功',
    'error' => 'エラー',
    'warning' => '警告',
    'info' => '情報',
];

// ARIA role
$ariaRoles = [
    'success' => 'status',
    'error' => 'alert',
    'warning' => 'alert',
    'info' => 'status',
];
@endphp

<div {{ $attributes->merge([
    'class' => $baseClasses . ' ' . $typeClasses[$type],
    'role' => $ariaRoles[$type],
    'aria-live' => $type === 'error' || $type === 'warning' ? 'assertive' : 'polite'
]) }}>
    {!! $icons[$type] !!}
    <div class="flex-1">
        <span class="sr-only">{{ $typeLabels[$type] }}:</span>
        {{ $slot }}
    </div>
    @if($dismissible)
        <button 
            type="button" 
            class="ml-auto p-1 rounded-md hover:bg-black/10 focus:outline-none focus:ring-2 focus:ring-current focus:ring-offset-2 transition-colors"
            aria-label="アラートを閉じる"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</div>