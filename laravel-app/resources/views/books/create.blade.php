<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('books.index') }}"
                   class="text-text-secondary hover:underline transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    書籍一覧に戻る
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-text-primary">書籍登録</h1>
            <p class="text-text-primary mt-2">ISBNスキャンまたは手動入力で書籍を登録できます</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- ISBNスキャン機能 -->
            <div class="bg-background rounded-lg shadow-sm border border-border-light p-4 sm:p-6" x-data="isbnScanner()">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-text-primary flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01m-5.01 0h.01M16 8h4m-4-4h4m-6 4h.01M12 8h.01M8 12h.01M8 8h.01M8 20h4.01M8 16h.01m0 4h4.01"></path>
                        </svg>
                        ISBNスキャン
                    </h2>
                    
                    <a href="/isbn-scan"
                       class="text-sm text-text-secondary hover:underline transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        カメラでスキャン
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="isbn-input" class="block text-sm font-medium text-text-primary mb-2">
                            ISBN番号を入力またはスキャン
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="isbn-input"
                                x-model="isbn"
                                placeholder="978-4-XXXXXXXXX"
                                class="flex-1 px-3 py-2 border border-border-neutral rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                @keyup.enter="fetchBookInfo"
                            >
                            <x-ui.button 
                                type="button"
                                @click="fetchBookInfo"
                                x-bind:disabled="!isbn || loading"
                                variant="primary"
                            >
                                <span x-show="!loading">検索</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    検索中...
                                </span>
                            </x-ui.button>
                        </div>
                    </div>
                    
                    <!-- エラーメッセージ -->
                    <div x-show="error" x-text="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-md"></div>
                    
                    <!-- 成功メッセージ -->
                    <div x-show="success" x-text="success" class="text-green-600 text-sm bg-green-50 p-3 rounded-md"></div>
                </div>
            </div>

            <!-- 書籍登録フォーム -->
            <div class="bg-background rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <h2 class="text-xl font-semibold text-text-primary mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    書籍情報
                </h2>
                
                <form action="{{ route('books.store') }}" method="POST" class="space-y-4" x-data="bookForm()" @submit.prevent="handleSubmit($el)">
                    @csrf
                    
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
                                x-model="form.title"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                placeholder="例：吾輩は猫である"
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
                                x-model="form.author"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                placeholder="例：夏目漱石"
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
                                x-model="form.isbn"
                                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                placeholder="例：978-4-00-310101-8"
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
                                    x-model="form.publisher"
                                    class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                    placeholder="例：岩波書店"
                                >
                                <x-forms.validation-error :messages="$errors->get('publisher')" field="publisher" />
                            </div>
                            
                            <div>
                                <label for="published_date" class="block text-sm font-medium text-text-primary mb-1">出版日</label>
                                <input 
                                    type="date" 
                                    id="published_date" 
                                    name="published_date" 
                                    x-model="form.published_date"
                                    class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                >
                                <x-forms.validation-error :messages="$errors->get('published_date')" field="published_date" />
                            </div>
                            
                            <div>
                                <label for="thumbnail_url" class="block text-sm font-medium text-text-primary mb-1">表紙画像URL</label>
                                <input 
                                    type="url" 
                                    id="thumbnail_url" 
                                    name="thumbnail_url" 
                                    x-model="form.thumbnail_url"
                                    class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                    placeholder="https://example.com/image.jpg"
                                >
                                <x-forms.validation-error :messages="$errors->get('thumbnail_url')" field="thumbnail_url" />
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-text-primary mb-1">説明・あらすじ</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="4"
                                    x-model="form.description"
                                    class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                                    placeholder="書籍の説明やあらすじを入力してください"
                                ></textarea>
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
                            <span x-show="!isSubmitting">書籍を登録</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <x-ui.loading type="spinner" size="sm" color="white" />
                                登録中...
                            </span>
                        </x-ui.button>
                        
                        <x-ui.button 
                            href="{{ route('books.index') }}" 
                            variant="secondary"
                            size="lg">
                            キャンセル
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function isbnScanner() {
            return {
                isbn: '',
                loading: false,
                error: '',
                success: '',
                
                async fetchBookInfo() {
                    if (!this.isbn.trim()) {
                        this.error = 'ISBNを入力してください';
                        return;
                    }
                    
                    this.loading = true;
                    this.error = '';
                    this.success = '';
                    
                    try {
                        const response = await fetch('/isbn-fetch', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ isbn: this.isbn })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // フォームに書籍情報を自動入力
                            const bookForm = Alpine.store('bookForm') || window.bookFormInstance;
                            if (bookForm) {
                                bookForm.form.title = data.data.title || '';
                                bookForm.form.author = data.data.author || '';
                                bookForm.form.isbn = this.isbn;
                                bookForm.form.publisher = data.data.publisher || '';
                                bookForm.form.published_date = data.data.published_date || '';
                                bookForm.form.thumbnail_url = data.data.thumbnail_url || '';
                                bookForm.form.description = data.data.description || '';
                            }
                            
                            // 直接DOM要素を更新（Alpine.jsのリアクティビティが効かない場合の対策）
                            document.getElementById('title').value = data.data.title || '';
                            document.getElementById('author').value = data.data.author || '';
                            document.getElementById('isbn').value = this.isbn;
                            document.getElementById('publisher').value = data.data.publisher || '';
                            document.getElementById('published_date').value = data.data.published_date || '';
                            document.getElementById('thumbnail_url').value = data.data.thumbnail_url || '';
                            document.getElementById('description').value = data.data.description || '';
                            
                            this.success = '書籍情報を取得しました！フォームに自動入力されました。';
                        } else {
                            this.error = data.error || '書籍情報の取得に失敗しました';
                        }
                    } catch (error) {
                        this.error = 'ネットワークエラーが発生しました';
                        console.error('ISBN fetch error:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
        
        function bookForm() {
            const instance = {
                isSubmitting: false,
                form: {
                    title: '',
                    author: '',
                    isbn: '',
                    publisher: '',
                    published_date: '',
                    thumbnail_url: '',
                    description: ''
                },
                
                handleSubmit(form) {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;
                    form.submit();
                }
            };
            
            // グローバルに参照できるようにする
            window.bookFormInstance = instance;
            
            return instance;
        }
    </script>
</x-app-layout>
