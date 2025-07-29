<x-app-layout>
    <div class="max-w-4xl mx-auto px-4">
        <!-- 戻るボタン -->
        <div class="mb-6">
            <a href="{{ route('books.index') }}" 
               class="inline-flex items-center gap-2 text-primary hover:text-primary-hover transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                書籍一覧に戻る
            </a>
        </div>

        <!-- 書籍詳細 -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="md:flex">
                <!-- 表紙画像 -->
                <div class="md:w-1/3 bg-gray-50 flex items-center justify-center p-8">
                    @if($book->thumbnail_url)
                        <img src="{{ $book->thumbnail_url }}" 
                             alt="{{ $book->title }}の表紙" 
                             class="max-w-full max-h-80 object-contain rounded-lg shadow-md">
                    @else
                        <div class="w-48 h-64 bg-gray-200 rounded-lg shadow-md flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <p class="text-sm">表紙画像なし</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 書籍情報 -->
                <div class="md:w-2/3 p-8">
                    <!-- タイトル -->
                    <h1 class="text-3xl font-bold text-[#4f4f4f] mb-4">{{ $book->title }}</h1>
                    
                    <!-- 基本情報 -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-start gap-3">
                            <span class="text-sm font-medium text-gray-500 w-20 flex-shrink-0">著者</span>
                            <span class="text-gray-800">{{ $book->formatted_author }}</span>
                        </div>
                        
                        @if($book->publisher)
                            <div class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-500 w-20 flex-shrink-0">出版社</span>
                                <span class="text-gray-800">{{ $book->formatted_publisher }}</span>
                            </div>
                        @endif
                        
                        @if($book->published_date)
                            <div class="flex items-start gap-3">
                                <span class="text-sm font-medium text-gray-500 w-20 flex-shrink-0">出版日</span>
                                <span class="text-gray-800">{{ $book->formatted_published_date }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-start gap-3">
                            <span class="text-sm font-medium text-gray-500 w-20 flex-shrink-0">ISBN</span>
                            <span class="text-gray-800 font-mono">{{ $book->isbn }}</span>
                        </div>
                    </div>

                    <!-- 貸出状況 -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-[#4f4f4f] mb-3">貸出状況</h3>
                        
                        @if($book->isAvailable())
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('images/library-available.png') }}" alt="利用可能" class="w-auto h-12">
                                    <span class="text-lg font-medium text-green-600">利用可能</span>
                                </div>
                                @auth
                                    <form method="POST" action="{{ route('loans.borrow') }}" class="inline" 
                                          onsubmit="return confirm('「{{ $book->title }}」を借りますか？')">
                                        @csrf
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <x-ui.button type="submit" variant="primary" size="lg">
                                            この本を借りる
                                        </x-ui.button>
                                    </form>
                                @else
                                    <p class="text-sm text-gray-500">ログインすると借りることができます</p>
                                @endauth
                            </div>
                        @elseif($book->isBorrowedByMe())
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('images/library-borrowed.png') }}" alt="貸出中（あなた）" class="w-auto h-12">
                                    <span class="text-lg font-medium text-[#295d72]">貸出中（あなた）</span>
                                </div>
                                <a href="{{ route('loans.my') }}" 
                                   class="px-6 py-3 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors font-medium">
                                    マイページで返却する
                                </a>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('images/library-unavailable.png') }}" alt="貸出中" class="w-auto h-12">
                                    <span class="text-lg font-medium text-red-600">貸出中</span>
                                </div>
                                @if($book->currentLoan)
                                    <span class="text-sm text-gray-500">
                                        返却予定: {{ $book->currentLoan->due_date->format('Y年m月d日') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>


                </div>
            </div>

            <!-- 説明文 -->
            @if($book->description)
                <div class="border-t border-gray-200 p-8">
                    <h3 class="text-xl font-semibold text-[#4f4f4f] mb-4">内容紹介</h3>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        {{ $book->description }}
                    </div>
                </div>
            @endif
        </div>

        <!-- 関連書籍（同じ著者の他の書籍） -->
        @if($relatedBooks && $relatedBooks->count() > 0)
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-[#4f4f4f] mb-4">同じ著者の他の書籍</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($relatedBooks as $relatedBook)
                        <a href="{{ route('books.show', $relatedBook) }}" 
                           class="block border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-[#295d72] transition-all">
                            <div class="flex gap-3">
                                @if($relatedBook->thumbnail_url)
                                    <img src="{{ $relatedBook->thumbnail_url }}" 
                                         alt="{{ $relatedBook->title }}の表紙" 
                                         class="w-12 h-16 object-cover rounded shadow-sm flex-shrink-0">
                                @else
                                    <div class="w-12 h-16 bg-gray-200 rounded shadow-sm flex-shrink-0"></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-medium text-[#4f4f4f] text-sm truncate">{{ $relatedBook->title }}</h4>
                                    @if($relatedBook->published_date)
                                        <p class="text-xs text-gray-500 mt-1">{{ $relatedBook->formatted_published_date }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>