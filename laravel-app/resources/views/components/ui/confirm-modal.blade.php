{{--
    カスタム確認モーダル（Alpine.js）
    使い方: フォームの onsubmit="return confirm()" を置き換える

    <x-ui.confirm-modal
        title="返却確認"
        message="「本のタイトル」を返却しますか？"
        action="/loans/return/1"
        confirm-text="返却する"
        confirm-variant="danger"
    />
--}}

@props([
    'title' => '確認',
    'message' => '',
    'action' => '',
    'method' => 'POST',
    'confirmText' => '確認',
    'cancelText' => 'キャンセル',
    'confirmVariant' => 'primary',
    'hiddenFields' => [],
])

@php
$confirmColors = [
    'primary' => 'bg-primary hover:bg-primary-hover text-white',
    'danger' => 'bg-danger hover:bg-danger-hover text-white',
    'success' => 'bg-success hover:bg-success-hover text-white',
];
$confirmClass = $confirmColors[$confirmVariant] ?? $confirmColors['primary'];
@endphp

<div x-data="{ open: false }" class="inline">
    {{-- トリガーボタン --}}
    <div @click="open = true" class="inline cursor-pointer">
        {{ $slot }}
    </div>

    {{-- モーダル背景 --}}
    <template x-teleport="body">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="open = false">

            {{-- オーバーレイ --}}
            <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

            {{-- モーダル本体 --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6 z-10">

                {{-- アイコン --}}
                <div class="flex justify-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- タイトル --}}
                <h3 class="text-lg font-bold text-center text-text-primary mb-2">{{ $title }}</h3>

                {{-- メッセージ --}}
                <p class="text-center text-text-secondary mb-6">{{ $message }}</p>

                {{-- ボタン --}}
                <div class="flex gap-3">
                    <button @click="open = false"
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-text-primary rounded-lg font-medium transition-colors">
                        {{ $cancelText }}
                    </button>
                    <form method="POST" action="{{ $action }}" class="flex-1">
                        @csrf
                        @foreach($hiddenFields as $fieldName => $fieldValue)
                            <input type="hidden" name="{{ $fieldName }}" value="{{ $fieldValue }}">
                        @endforeach
                        <button type="submit"
                                class="w-full px-4 py-2.5 rounded-lg font-medium transition-colors {{ $confirmClass }}">
                            {{ $confirmText }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
