<x-app-layout>
    <div class="w-full sm:max-w-2xl md:max-w-4xl lg:max-w-6xl xl:max-w-7xl mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <x-ui.button href="{{ route('admin.books.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    書籍管理に戻る
                </x-ui.button>
                <div>
                    <h1 class="text-3xl font-bold text-text-primary">書籍編集</h1>
                    <p class="text-text-secondary mt-2">「{{ $book->title }}」の情報を編集</p>
                </div>
            </div>
        </div>

        <!-- エラーメッセージ -->
        @if($errors->any())
            <x-ui.alert type="error" dismissible class="mb-6">
                <div class="font-medium">入力内容に問題があります：</div>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <!-- 編集フォーム -->
        <x-ui.card>
            <form method="POST" action="{{ route('admin.books.update', $book) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- タイトル -->
                <x-forms.form-group>
                    <x-forms.label for="title" required>タイトル</x-forms.label>
                    <x-text-input 
                        id="title" 
                        name="title" 
                        type="text" 
                        :value="old('title', $book->title)" 
                        required 
                        maxlength="255"
                        class="w-full" />
                    <x-forms.error name="title" />
                </x-forms.form-group>

                <!-- 著者 -->
                <x-forms.form-group>
                    <x-forms.label for="author" required>著者</x-forms.label>
                    <x-text-input 
                        id="author" 
                        name="author" 
                        type="text" 
                        :value="old('author', $book->author)" 
                        required 
                        maxlength="255"
                        class="w-full" />
                    <x-forms.error name="author" />
                </x-forms.form-group>

                <!-- ISBN -->
                <x-forms.form-group>
                    <x-forms.label for="isbn" required>ISBN</x-forms.label>
                    <x-text-input 
                        id="isbn" 
                        name="isbn" 
                        type="text" 
                        :value="old('isbn', $book->isbn)" 
                        required 
                        placeholder="例: 978-4-12-345678-9"
                        class="w-full" />
                    <x-forms.help>ハイフンありなしどちらでも入力可能です</x-forms.help>
                    <x-forms.error name="isbn" />
                </x-forms.form-group>

                <!-- 出版社 -->
                <x-forms.form-group>
                    <x-forms.label for="publisher">出版社</x-forms.label>
                    <x-text-input 
                        id="publisher" 
                        name="publisher" 
                        type="text" 
                        :value="old('publisher', $book->publisher)" 
                        maxlength="255"
                        class="w-full" />
                    <x-forms.error name="publisher" />
                </x-forms.form-group>

                <!-- 出版日 -->
                <x-forms.form-group>
                    <x-forms.label for="published_date">出版日</x-forms.label>
                    <x-text-input 
                        id="published_date" 
                        name="published_date" 
                        type="date" 
                        :value="old('published_date', $book->published_date?->format('Y-m-d'))" 
                        max="{{ date('Y-m-d') }}"
                        class="w-full" />
                    <x-forms.error name="published_date" />
                </x-forms.form-group>

                <!-- 概要 -->
                <x-forms.form-group>
                    <x-forms.label for="description">概要</x-forms.label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4" 
                        maxlength="2000"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                        placeholder="書籍の概要や内容について（任意）">{{ old('description', $book->description) }}</textarea>
                    <x-forms.help>最大2000文字まで入力できます</x-forms.help>
                    <x-forms.error name="description" />
                </x-forms.form-group>

                <!-- サムネイルURL -->
                <x-forms.form-group>
                    <x-forms.label for="thumbnail_url">表紙画像URL</x-forms.label>
                    <x-text-input 
                        id="thumbnail_url" 
                        name="thumbnail_url" 
                        type="url" 
                        :value="old('thumbnail_url', $book->thumbnail_url)" 
                        placeholder="https://example.com/book-cover.jpg"
                        class="w-full" />
                    <x-forms.help>書籍の表紙画像のURLを入力してください（任意）</x-forms.help>
                    <x-forms.error name="thumbnail_url" />
                </x-forms.form-group>

                <!-- 現在の表紙画像プレビュー -->
                @if($book->thumbnail_url)
                    <x-forms.form-group>
                        <x-forms.label>現在の表紙画像</x-forms.label>
                        <div class="mt-2">
                            <img src="{{ $book->thumbnail_url }}" 
                                 alt="{{ $book->title }}の表紙" 
                                 class="w-32 h-40 object-cover rounded shadow-md">
                        </div>
                    </x-forms.form-group>
                @endif

                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <x-ui.button href="{{ route('admin.books.index') }}" variant="secondary">
                        キャンセル
                    </x-ui.button>
                    
                    <div class="flex items-center space-x-4">
                        <x-ui.button type="submit" variant="primary" size="lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            更新する
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>