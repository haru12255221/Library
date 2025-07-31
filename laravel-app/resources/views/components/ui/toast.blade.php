@props([
    'type' => 'info',
    'message' => '',
    'dismissible' => true,
    'duration' => 5000,
    'id' => null
])

@php
$types = [
    'success' => [
        'bg' => 'bg-white',
        'border' => 'border-l-4 border-green-400',
        'text' => 'text-gray-900',
        'iconBg' => 'bg-green-100',
        'iconColor' => 'text-green-600',
        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    ],
    'error' => [
        'bg' => 'bg-white',
        'border' => 'border-l-4 border-red-400',
        'text' => 'text-gray-900',
        'iconBg' => 'bg-red-100',
        'iconColor' => 'text-red-600',
        'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
    ],
    'warning' => [
        'bg' => 'bg-white',
        'border' => 'border-l-4 border-yellow-400',
        'text' => 'text-gray-900',
        'iconBg' => 'bg-yellow-100',
        'iconColor' => 'text-yellow-600',
        'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z'
    ],
    'info' => [
        'bg' => 'bg-white',
        'border' => 'border-l-4 border-blue-400',
        'text' => 'text-gray-900',
        'iconBg' => 'bg-blue-100',
        'iconColor' => 'text-blue-600',
        'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    ]
];

$typeConfig = $types[$type];
$toastId = $id ?? 'toast-' . uniqid();
@endphp

<div 
    id="{{ $toastId }}"
    class="toast max-w-sm w-full {{ $typeConfig['bg'] }} shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out {{ $typeConfig['border'] }}"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    x-data="{ 
        show: true, 
        timer: null,
        paused: false,
        duration: {{ $duration }},
        init() {
            this.startTimer();
        },
        startTimer() {
            if (this.duration > 0) {
                this.timer = setTimeout(() => {
                    this.remove();
                }, this.duration);
            }
        },
        pauseTimer() {
            if (this.timer && !this.paused) {
                clearTimeout(this.timer);
                this.paused = true;
            }
        },
        resumeTimer() {
            if (this.paused) {
                this.paused = false;
                this.startTimer();
            }
        },
        remove() {
            this.show = false;
            setTimeout(() => {
                this.$el.remove();
            }, 300);
        }
    }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transform ease-in duration-300 transition"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-full opacity-0"
    @mouseenter="pauseTimer()"
    @mouseleave="resumeTimer()"
>
    <div class="p-4">
        <div class="flex items-start">
            <!-- アイコン -->
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-8 w-8 rounded-full {{ $typeConfig['iconBg'] }}">
                    <svg class="h-5 w-5 {{ $typeConfig['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeConfig['icon'] }}"></path>
                    </svg>
                </div>
            </div>
            
            <!-- メッセージ -->
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium {{ $typeConfig['text'] }}">
                    {{ $message ?: $slot }}
                </p>
            </div>
            
            <!-- 閉じるボタン -->
            @if($dismissible)
                <div class="ml-4 flex-shrink-0 flex">
                    <button 
                        @click="remove()"
                        class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        aria-label="閉じる"
                    >
                        <span class="sr-only">閉じる</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>