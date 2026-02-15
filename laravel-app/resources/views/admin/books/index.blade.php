<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-text-primary">書籍管理</h1>
                    <p class="text-text-secondary mt-2">管理者専用：書籍の登録・編集・削除</p>
                </div>
                
                <x-ui.button href="{{ route('books.create') }}" variant="primary" size="lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    新しい書籍を登録
                </x-ui.button>
            </div>
        </div>

        <!-- 成功メッセージ -->
        @if(session('success'))
            <x-ui.alert type="success" dismissible class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- エラーメッセージ -->
        @if(session('error'))
            <x-ui.alert type="error" dismissible class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <!-- 検索フォーム -->
        <x-ui.card class="mb-6">
            <form action="{{ route('admin.books.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <x-text-input 
                        type="text" 
                        name="search" 
                        placeholder="タイトルまたは著者で検索" 
                        :value="request('search')"
                        class="w-full" />
                </div>
                <x-ui.button type="submit" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    検索
                </x-ui.button>
                @if(request('search'))
                    <x-ui.button href="{{ route('admin.books.index') }}" variant="secondary">
                        リセット
                    </x-ui.button>
                @endif
            </form>
        </x-ui.card>

        <!-- 書籍一覧テーブル -->
        <x-ui.card padding="none">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-light">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                書籍情報
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                ISBN
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                出版情報
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                貸出状況
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-border-light">
                        @forelse($books as $book)
                            <tr class="hover:bg-gray-50">
                                <!-- 書籍情報 -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($book->thumbnail_url)
                                            <img src="{{ $book->thumbnail_url }}" 
                                                 alt="{{ $book->title }}の表紙" 
                                                 class="w-12 h-16 object-cover rounded shadow-sm mr-4">
                                        @else
                                            <div class="w-12 h-16 bg-gray-200 rounded shadow-sm mr-4 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-text-primary">
                                                <a href="{{ route('books.show', $book) }}" class="hover:underline">
                                                    {{ $book->title }}
                                                </a>
                                            </div>
                                            <div class="text-sm text-text-secondary">{{ $book->formatted_author }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- ISBN -->
                                <td class="px-6 py-4 text-sm text-text-primary font-mono">
                                    {{ $book->isbn }}
                                </td>

                                <!-- 出版情報 -->
                                <td class="px-6 py-4 text-sm text-text-secondary">
                                    @if($book->publisher)
                                        <div>{{ $book->formatted_publisher }}</div>
                                    @endif
                                    @if($book->published_date)
                                        <div>{{ $book->formatted_published_date }}</div>
                                    @endif
                                </td>

                                <!-- 貸出状況 -->
                                <td class="px-6 py-4">
                                    @if($book->isAvailable())
                                        <span class="text-xs font-medium text-text-primary underline decoration-green-500 decoration-2 underline-offset-4">
                                            利用可能
                                        </span>
                                    @else
                                        <span class="text-xs font-medium text-text-primary underline decoration-red-400 decoration-2 underline-offset-4">
                                            貸出中
                                        </span>
                                        @if($book->currentLoan)
                                            <div class="text-xs text-text-secondary mt-1">
                                                返却予定: {{ $book->currentLoan->due_date->format('Y/m/d') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-text-secondary">
                                    @if(request('search'))
                                        「{{ request('search') }}」に該当する書籍が見つかりませんでした
                                    @else
                                        登録された書籍がありません
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <!-- ページネーション -->
        @if($books->hasPages())
            <div class="mt-6">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</x-app-layout>