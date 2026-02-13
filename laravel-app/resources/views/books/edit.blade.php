<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('books.show', $book) }}"
                   class="text-primary hover:text-primary-hover transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    書籍詳細に戻る
                </a>
            </div>
            <h1 class="text-3xl font-bold text-text-primary">書籍編集</h1>
        </div>

        <!-- 書籍編集フォーム -->
        <div class="bg-background rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-text-primary mb-4 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                書籍情報
            </h2>

            <form action="{{ route('books.update', $book) }}" method="POST" class="space-y-4" x-data="{ isSubmitting: false }" @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                @csrf
                @method('PUT')

                <!-- 基本情報 -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-text-primary mb-1">
                            タイトル <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            required
                            value="{{ old('title', $book->title) }}"
                            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                        >
                        <x-forms.validation-error :messages="$errors->get('title')" field="title" />
                    </div>

                    <div>
                        <label for="author" class="block text-sm font-medium text-text-primary mb-1">
                            著者 <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="author"
                            name="author"
                            required
                            value="{{ old('author', $book->author) }}"
                            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                        >
                        <x-forms.validation-error :messages="$errors->get('author')" field="author" />
                    </div>

                    <div>
                        <label for="isbn" class="block text-sm font-medium text-text-primary mb-1">
                            ISBN <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="isbn"
                            name="isbn"
                            required
                            value="{{ old('isbn', $book->isbn) }}"
                            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                        >
                        <x-forms.validation-error :messages="$errors->get('isbn')" field="isbn" />
                    </div>
                </div>

                <!-- 拡張情報 -->
                <div class="border-t pt-4">
                    <h3 class="text-lg font-medium text-text-primary mb-3">詳細情報（任意）</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="publisher" class="block text-sm font-medium text-text-primary mb-1">出版社</label>
                            <input
                                type="text"
                                id="publisher"
                                name="publisher"
                                value="{{ old('publisher', $book->publisher) }}"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                            >
                            <x-forms.validation-error :messages="$errors->get('publisher')" field="publisher" />
                        </div>

                        <div>
                            <label for="published_date" class="block text-sm font-medium text-text-primary mb-1">出版日</label>
                            <input
                                type="date"
                                id="published_date"
                                name="published_date"
                                value="{{ old('published_date', $book->published_date?->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                            >
                            <x-forms.validation-error :messages="$errors->get('published_date')" field="published_date" />
                        </div>

                        <div>
                            <label for="thumbnail_url" class="block text-sm font-medium text-text-primary mb-1">表紙画像URL</label>
                            <input
                                type="url"
                                id="thumbnail_url"
                                name="thumbnail_url"
                                value="{{ old('thumbnail_url', $book->thumbnail_url) }}"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                            >
                            <x-forms.validation-error :messages="$errors->get('thumbnail_url')" field="thumbnail_url" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-text-primary mb-1">説明・あらすじ</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                            >{{ old('description', $book->description) }}</textarea>
                            <x-forms.validation-error :messages="$errors->get('description')" field="description" />
                        </div>
                    </div>
                </div>

                <!-- 送信ボタン -->
                <div class="flex gap-3 pt-4">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="flex-1"
                        x-bind:disabled="isSubmitting"
                    >
                        <span x-show="!isSubmitting">更新する</span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <x-ui.loading type="spinner" size="sm" color="white" />
                            更新中...
                        </span>
                    </x-ui.button>

                    <x-ui.button
                        href="{{ route('books.show', $book) }}"
                        variant="secondary"
                        size="lg">
                        キャンセル
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
