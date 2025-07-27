<x-app-layout>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="max-w-7xl mx-auto px-4">
        <!-- 成功メッセージ -->
        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        <!-- 検索フォーム -->
        <x-search-form 
            :action="route('books.index')" 
            placeholder="タイトルまたは著者で検索"
            :value="request('search')"
        >
            {{ $books->count() }}
        </x-search-form>

        <!-- 書籍一覧 -->
        <x-page-header title="書籍一覧">
            <x-slot name="actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <x-button :href="route('books.create')" variant="primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            書籍を登録
                        </x-button>
                    @endif
                @endauth
            </x-slot>
            
            @if($books->count() > 0)
                <div class="grid gap-4">
                    @foreach($books as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    title="登録された書籍がありません"
                    :image="asset('images/Library1.png')"
                >
                    @auth
                        @if(auth()->user()->isAdmin())
                            <x-button :href="route('books.create')" variant="primary">
                                書籍を登録する
                            </x-button>
                        @endif
                    @endauth
                </x-empty-state>
            @endif
        </x-page-header>

        <script>
            function searchForm() {
                return {
                    searchQuery: '{{ request('search') }}' || '',
                    hasSearchParam: {{ request('search') ? 'true' : 'false' }},
                    
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
