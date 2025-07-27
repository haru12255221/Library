@props(['book'])

<div class="bg-white rounded-lg shadow border border-gray-200 hover:border-lib-primary transition-all p-6">
    <a href="{{ route('books.show', $book) }}">
        <!-- 詳細ページへのリンク -->
        <a href="{{ route('books.show', $book) }}" class="block mb-3 group">
            <div class="flex items-center gap-2 text-sm text-lib-primary hover:text-lib-primary-hover transition-colors">
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
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- 書籍情報 -->
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-lib-text-primary mb-1 truncate">{{ $book->title }}</h3>
                <p class="text-lib-text-secondary mb-1">著者: {{ $book->formatted_author }}</p>
                
                <!-- 拡張情報 -->
                @if($book->publisher)
                    <p class="text-sm text-lib-text-light mb-1">出版社: {{ $book->formatted_publisher }}</p>
                @endif
                
                @if($book->published_date)
                    <p class="text-sm text-lib-text-light mb-1">出版日: {{ $book->formatted_published_date }}</p>
                @endif
                
                @if($book->description)
                    <p class="text-sm text-lib-text-secondary mb-2 line-clamp-2">{{ Str::limit($book->description, 100) }}</p>
                @endif
                
                <!-- ISBN -->
                <p class="text-xs text-lib-text-light mb-2">ISBN: {{ $book->isbn }}</p>
            </div>
        </div>
        
        <!-- 貸出状況 -->
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
            @if($book->isAvailable())
                {{-- 利用可能 --}}
                <span class="font-medium flex items-center gap-2 text-lib-success">
                    <img src="{{ asset('images/library-available.png') }}" alt="利用可能" class="w-auto h-12">
                    利用可能
                </span>
                @auth
                    <form method="POST" action="{{ route('loans.borrow') }}" class="inline" onsubmit="return confirm('「{{ $book->title }}」を借りますか？')">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <x-button type="submit" variant="primary">
                            借りる
                        </x-button>
                    </form>
                @endauth
            @elseif($book->isBorrowedByMe())
                {{-- 貸出中（あなた） --}}
                <span class="font-medium flex items-center gap-2 text-lib-primary">
                    <img src="{{ asset('images/library-borrowed.png') }}" alt="貸出中（あなた）" class="w-auto h-12">
                    <span class="hidden md:inline">貸出中（あなた）</span>
                </span>
                <a href="{{ route('loans.my') }}" class="text-sm underline transition-colors text-lib-primary hover:text-lib-primary-hover">
                    マイページで返却する
                </a>
            @else
                {{-- 貸出中です --}}
                <span class="font-medium flex items-center gap-2 text-lib-error">
                    <img src="{{ asset('images/library-unavailable.png') }}" alt="貸出中" class="w-auto h-12">
                    <span class="hidden md:inline">貸出中です</span>
                </span>
                @if($book->currentLoan)
                    <span class="text-sm text-lib-text-light">
                        返却予定: {{ $book->currentLoan->due_date->format('Y/m/d') }}
                    </span>
                @endif
            @endif
        </div>
    </a>
</div>