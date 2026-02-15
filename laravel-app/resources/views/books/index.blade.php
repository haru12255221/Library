<x-app-layout>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="max-w-7xl mx-auto px-4">
        <!-- 成功メッセージ -->
        @if(session('success'))
            <x-ui.alert type="success" dismissible class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- 検索フォーム -->
        <x-ui.card class="mb-8" x-data="searchForm()" x-init="window.addEventListener('pageshow', () => isSearching = false)">
            <form action="{{ route('books.index') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-text-primary mb-2">検索キーワード</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            x-model="searchQuery"
                            placeholder="タイトルまたは著者で検索" 
                            value="{{ request('search') }}"
                            class="w-full px-3 py-2 pr-20 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                            @input="handleInput"
                        >
                        <!-- クリアボタン -->
                        <button 
                            type="button"
                            x-show="searchQuery.length > 0"
                            @click="clearSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-text-light hover:text-text-secondary focus:outline-none"
                            title="検索をクリア"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <x-ui.button 
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full sm:w-auto min-h-[44px]"
                        x-bind:disabled="isSearching"
                        @click="isSearching = true"
                    >
                        <span x-show="!isSearching" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            検索
                        </span>
                        <span x-show="isSearching" class="flex items-center justify-center gap-2">
                            <x-ui.loading type="spinner" size="sm" />
                            検索中...
                        </span>
                    </x-ui.button>
                    
                    <x-ui.button 
                        type="button"
                        @click="resetForm"
                        x-show="searchQuery.length > 0 || hasSearchParam"
                        variant="secondary"
                        size="lg"
                        class="w-full sm:w-auto min-h-[44px]"
                    >
                        リセット
                    </x-ui.button>
                </div>
                
                <!-- 検索結果の件数表示 -->
                @if(request('search'))
                    <div class="text-sm text-text-secondary">
                        「{{ request('search') }}」の検索結果: {{ $books->total() }}件
                    </div>
                @endif
            </form>
        </x-ui.card>

        <!-- 書籍一覧 -->
        <x-ui.card padding="none">
            <div class="px-6 py-4 border-b border-border-light flex justify-between items-center">
                <h2 class="text-lg font-semibold text-text-text-primary">書籍一覧</h2>
                

            </div>
            
            <div class="p-6">
                @if($books->count() > 0)
                    <div class="grid gap-4">
                        @foreach($books as $book)
                            <x-ui.card padding="sm" class="hover:shadow-sm hover:border-border-neutral transition-all">
                                <!-- 詳細ページへのリンク -->
                                <a href="{{ route('books.show', $book) }}" class="block mb-3 group">
                                    <div class="flex items-center gap-2 text-sm text-text-secondary group-hover:underline transition-colors">
                                        <span>詳細を見る</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </a>
                                <div class="flex gap-4">
                                    <!-- 表紙画像 -->
                                    <div class="flex-shrink-0">
                                        @if($book->thumbnail_url)
                                            <img src="{{ $book->thumbnail_url }}" 
                                                 alt="{{ $book->title }}の表紙" 
                                                 class="w-16 h-20 object-cover rounded shadow-sm">
                                        @else
                                            <div class="w-16 h-20 bg-gray-200 rounded shadow-sm flex items-center justify-center">
                                                <svg class="w-8 h-8 text-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 書籍情報 -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-text-text-primary mb-1 truncate">{{ $book->display_title }}</h3>
                                        <p class="text-text-secondary mb-1">著者: {{ $book->formatted_author }}</p>
                                        
                                        <!-- 拡張情報 -->
                                        @if($book->publisher)
                                            <p class="text-sm text-text-secondary mb-1">出版社: {{ $book->formatted_publisher }}</p>
                                        @endif
                                        
                                        @if($book->published_date)
                                            <p class="text-sm text-text-secondary mb-1">出版日: {{ $book->formatted_published_date }}</p>
                                        @endif
                                        
                                        @if($book->description)
                                            <p class="text-sm text-text-secondary mb-2 line-clamp-2">{{ Str::limit($book->description, 100) }}</p>
                                        @endif
                                        
                                        <!-- ISBN -->
                                        <p class="text-xs text-text-light mb-2">ISBN: {{ $book->isbn }}</p>
                                    </div>
                                </div>
                                
                                <!-- 貸出状況 -->
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border-light">
                                    @if($book->isAvailable())
                                        {{-- 利用可能: モバイルでもテキスト表示 --}}
                                        <span class="font-medium flex items-center gap-2 text-text-primary">
                                            <img src="{{ asset('images/library-available.png') }}" alt="利用可能" class="w-auto h-12">
                                            <span class="underline decoration-green-500 decoration-2 underline-offset-4">利用可能</span>
                                        </span>
                                        @auth
                                            <x-ui.confirm-modal
                                                title="貸出確認"
                                                message="「{{ $book->title }}」を借りますか？"
                                                :action="route('loans.borrow')"
                                                confirm-text="借りる"
                                                confirm-variant="primary"
                                                :hidden-fields="['book_id' => $book->id]"
                                            >
                                                <x-ui.button variant="primary">借りる</x-ui.button>
                                            </x-ui.confirm-modal>
                                        @endauth
                                    @elseif($book->isBorrowedByMe())
                                        {{-- 貸出中（あなた）: モバイルではアイコンのみ --}}
                                        <span class="font-medium flex items-center gap-2 text-text-primary">
                                            <img src="{{ asset('images/library-borrowed.png') }}" alt="貸出中（あなた）" class="w-auto h-12">
                                            <span class="hidden md:inline underline decoration-light-blue-400 decoration-2 underline-offset-4">貸出中（あなた）</span>
                                        </span>
                                        <a href="{{ route('loans.my') }}" class="text-sm text-text-secondary hover:underline transition-colors">
                                            マイページで返却する
                                        </a>
                                    @else
                                        {{-- 貸出中です: モバイルではアイコンのみ --}}
                                        <span class="font-medium flex items-center gap-2 text-text-primary">
                                            <img src="{{ asset('images/library-unavailable.png') }}" alt="貸出中" class="w-auto h-12">
                                            <span class="hidden md:inline underline decoration-red-400 decoration-2 underline-offset-4">貸出中です</span>
                                        </span>
                                        @if($book->currentLoan)
                                            <span class="text-sm text-text-secondary">
                                                返却予定: {{ $book->currentLoan->due_date->format('Y/m/d') }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                

                            </x-ui.card>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-text-secondary text-lg">登録された書籍がありません</p>
                    </div>
                @endif

                <!-- ページネーション -->
                @if($books->hasPages())
                    <div class="mt-6">
                        {{ $books->links() }}
                    </div>
                @endif
            </div>
        </x-ui.card>

        <script>
            function searchForm() {
                return {
                    searchQuery: '{{ request('search') }}' || '',
                    hasSearchParam: {{ request('search') ? 'true' : 'false' }},
                    isSearching: false,
                    
                    handleInput() {
                        // リアルタイム検索のためのデバウンス処理
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            // 自動検索は無効化（サーバー負荷を考慮）
                            // 必要に応じて有効化可能
                            // this.submitSearch();
                        }, 500);
                    },
                    
                    clearSearch() {
                        this.searchQuery = '';
                        document.querySelector('input[name="search"]').focus();
                    },
                    
                    resetForm() {
                        this.searchQuery = '';
                        // 検索パラメータをクリアして一覧ページに戻る
                        window.location.href = '{{ route('books.index') }}';
                    },
                    
                    submitSearch() {
                        if (this.searchQuery.trim().length > 0) {
                            document.querySelector('form').submit();
                        }
                    }
                }
            }
        </script>
    </div>
</x-app-layout>
