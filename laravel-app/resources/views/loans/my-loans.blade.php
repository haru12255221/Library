<x-app-layout>
    <div class="max-w-7xl mx-auto px-4">
        <!-- 成功・エラーメッセージ -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- ページタイトル -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-[#4f4f4f]">マイページ</h2>
            <p class="text-gray-600 mt-2">借りている本の一覧と返却ができます</p>
        </div>

        <!-- 借りている本の一覧 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-[#4f4f4f]">
                    借りている本 ({{ $myLoans->count() }}冊)
                </h3>
            </div>
            
            <div class="p-6">
                @if($myLoans->count() > 0)
                    <div class="grid gap-4">
                        @foreach($myLoans as $loan)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-[#4f4f4f] mb-2">
                                            {{ $loan->book->title }}
                                        </h4>
                                        <p class="text-gray-600 mb-1">著者: {{ $loan->book->author }}</p>
                                        <p class="text-sm text-gray-500 mb-2">ISBN: {{ $loan->book->isbn }}</p>
                                        
                                        <div class="flex gap-4 text-sm">
                                            <span class="text-primary">
                                                借りた日: {{ $loan->borrowed_at->format('Y年m月d日') }}
                                            </span>
                                            <span class="text-danger">
                                                返却期限: {{ $loan->due_date->format('Y年m月d日') }}
                                            </span>
                                        </div>
                                        
                                        <!-- 期限チェック -->
                                        @php
                                            $now = now();
                                            $dueDate = $loan->due_date;
                                            $daysUntilDue = $now->diffInDays($dueDate, false);
                                            $isPast = $dueDate->isPast();
                                        @endphp
                                        
                                        @if($isPast)
                                            <div class="mt-2 font-medium" style="color: #e3595b;">
                                                ⚠️ 返却期限を過ぎています
                                            </div>
                                        @elseif($daysUntilDue <= 3 && $daysUntilDue >= 0)
                                            <div class="mt-2 font-medium" style="color: #d6e185;">
                                                ⚠️ 返却期限が近づいています (残り{{ floor($daysUntilDue) }}日)
                                            </div>

                                        @else
                                            <div class="mt-2 font-medium" style="color: #d6e185;">
                                                返却期限まで余裕があります (残り{{ floor($daysUntilDue) }}日)
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 返却ボタン -->
                                    <div class="ml-4">
                                        <form method="POST" action="{{ route('loans.return', $loan) }}" 
                                                onsubmit="return confirm('「{{ $loan->book->title }}」を返却しますか？')">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-4 py-2 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                                                    style="background-color: #e3595b; --tw-ring-color: #e3595b;"
                                                    onmouseover="this.style.backgroundColor='#d63d3f'"
                                                    onmouseout="this.style.backgroundColor='#e3595b'">
                                                返却する
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">
                            <img src="{{ asset('images/Library1.png') }}" alt="本" class="w-auto h-32 mx-auto">
                        </div>
                        <p class="text-gray-500 text-lg mb-4">現在借りている本はありません</p>
                        <a href="{{ route('books.index') }}" 
                            class="inline-block px-6 py-3 bg-[#295d72] text-white rounded-md hover:bg-[#3a7a94] transition-colors">
                            書籍一覧を見る
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 統計情報 -->
        @if($myLoans->count() > 0)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-primary">{{ $myLoans->count() }}</div>
                    <div class="text-gray-600">借用中の本</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-success">
                        {{ $myLoans->filter(function($loan) { 
                            $daysUntil = now()->diffInDays($loan->due_date, false);
                            return $daysUntil <= 3 && $daysUntil >= 0 && !$loan->due_date->isPast();
                        })->count() }}
                    </div>
                    <div class="text-gray-600">返却期限が近い本</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-danger">
                        {{ $myLoans->filter(function($loan) { return $loan->due_date->isPast(); })->count() }}
                    </div>
                    <div class="text-gray-600">期限切れの本</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>