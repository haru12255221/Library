<x-app-layout>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="max-w-7xl mx-auto px-4">
        <!-- 検索フォーム -->
        <div class="bg-white rounded-lg shadow p-6 mb-8" x-data="searchForm()">
            <form action="{{ route('books.index') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#4f4f4f] mb-2">検索キーワード</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            x-model="searchQuery"
                            placeholder="タイトルまたは著者で検索" 
                            value="{{ request('search') }}"
                            class="w-full px-3 py-2 pr-20 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#295d72] focus:border-transparent"
                            @input="handleInput"
                        >
                        <!-- クリアボタン -->
                        <button 
                            type="button"
                            x-show="searchQuery.length > 0"
                            @click="clearSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            title="検索をクリア"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-[#ec652b] text-white rounded-md hover:bg-[#f4a261] focus:outline-none focus:ring-2 focus:ring-[#ec652b] focus:ring-offset-2 transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            検索
                        </span>
                    </button>
                    
                    <button 
                        type="button"
                        @click="resetForm"
                        x-show="searchQuery.length > 0 || hasSearchParam"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                    >
                        リセット
                    </button>
                </div>
                
                <!-- 検索結果の件数表示 -->
                @if(request('search'))
                    <div class="text-sm text-gray-600">
                        「{{ request('search') }}」の検索結果: {{ $books->count() }}件
                    </div>
                @endif
            </form>
        </div>

        <!-- 書籍一覧 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-[#4f4f4f]">書籍一覧</h2>
            </div>
            
            <div class="p-6">
                @if($books->count() > 0)
                    <div class="grid gap-4">
                        @foreach($books as $book)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-[#295d72] transition-all">
                                <h3 class="text-lg font-semibold text-[#4f4f4f] mb-2">{{ $book->title }}</h3>
                                <p class="text-gray-600 mb-1">著者: {{ $book->author }}</p>
                                <!-- 貸出状況 -->
                                <div class="flex items-center justify-between">
                                    @if($book->isAvailable())
                                        <span class="text-green-600 font-medium flex items-center gap-2">
                                            利用可能
                                        </span>
                                        @auth
                                            <form method="POST" action="{{ route('loans.borrow') }}" class="inline" onsubmit="return confirm('「{{ $book->title }}」を借りますか？')">
                                                @csrf
                                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                                <button type="submit" class="px-4 py-2 bg-[#295d72] text-white rounded-md hover:bg-[#3a7a94] transition-colors">
                                                    借りる
                                                </button>
                                            </form>
                                        @endauth
                                    @elseif($book->isBorrowedByMe())
                                        <span class="text-blue-600 font-medium flex items-center gap-2">
                                            貸出中（あなた）
                                            <img src="{{ asset('images/library-borrowed.png') }}" alt="貸出中（あなた）" class="w-auto h-12">
                                        </span>
                                        <a href="{{ route('loans.my') }}" class="text-sm text-[#ec652b] hover:text-[#f4a261] underline">
                                            マイページで返却する
                                        </a>
                                    @else
                                        <span class="text-red-600 font-medium flex items-center gap-2">
                                            貸出中
                                            <img src="{{ asset('images/library-unavailable.png') }}" alt="貸出中" class="w-auto h-12">
                                        </span>
                                        <span class="text-sm text-gray-500">利用できません</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">登録された書籍がありません</p>
                    </div>
                @endif
            </div>
        </div>

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
