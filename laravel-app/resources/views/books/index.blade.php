<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>図書館管理システム</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f8f9fa] min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-[#295d72] shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <h1 class="text-2xl font-bold text-white">図書館管理システム</h1>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- 検索フォーム -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form action="{{ route('books.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-[#4f4f4f] mb-2">検索キーワード</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="タイトルまたは著者で検索" 
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#295d72] focus:border-transparent"
                    >
                </div>
                <div class="flex items-end">
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-[#ec652b] text-white rounded-md hover:bg-[#f4a261] focus:outline-none focus:ring-2 focus:ring-[#ec652b] focus:ring-offset-2 transition-colors"
                    >
                        検索
                    </button>
                </div>
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
                                <p class="text-sm text-gray-500">ISBN: {{ $book->isbn }}</p>
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
    </main>
</body>
</html>
