<x-app-layout>
    <div class="max-w-lg mx-auto px-4 py-20 text-center">
        <div class="mb-6">
            <span class="text-8xl font-bold text-primary/30">419</span>
        </div>
        <h1 class="text-2xl font-bold text-text-primary mb-3">ページの有効期限が切れました</h1>
        <p class="text-text-secondary mb-8">しばらく操作がなかったため、セッションが切れました。ページを再読み込みしてください。</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('books.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            ページを再読み込み
        </a>
    </div>
</x-app-layout>
