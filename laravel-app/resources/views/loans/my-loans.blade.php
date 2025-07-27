<x-app-layout>
    <div class="max-w-7xl mx-auto px-4">
        <!-- 成功・エラーメッセージ -->
        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error">
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- ページタイトル -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-[#4f4f4f]">マイページ</h2>
            <p class="text-gray-600 mt-2">借りている本の一覧と返却ができます</p>
        </div>

        <!-- 借りている本の一覧 -->
        <div class="card">
            <div class="card-header">
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
                                            <div class="mt-2 font-medium flex items-center gap-1 text-lib-accent">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                返却期限を過ぎています
                                            </div>
                                        @elseif($daysUntilDue <= 3 && $daysUntilDue >= 0)
                                            <div class="mt-2 font-medium flex items-center gap-1 text-yellow-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                返却期限が近づいています (残り{{ floor($daysUntilDue) }}日)
                                            </div>
                                        @else
                                            <div class="mt-2 font-medium flex items-center gap-1 text-lib-secondary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                返却期限まで余裕があります (残り{{ floor($daysUntilDue) }}日)
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 返却ボタン -->
                                    <div class="ml-4">
                                        <form method="POST" action="{{ route('loans.return', $loan) }}" 
                                                onsubmit="return confirm('「{{ $loan->book->title }}」を返却しますか？')">
                                            @csrf
                                            <x-button type="submit" variant="primary">
                                                返却する
                                            </x-button>
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
                        <x-button :href="route('books.index')" variant="primary" size="lg">
                            書籍一覧を見る
                        </x-button>
                    </div>
                @endif
            </div>
        </div>

        <!-- 統計情報 -->
        @if($myLoans->count() > 0)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 借用中の本 (Primary Color) -->
                <div class="card card-body text-center hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-lib-primary">{{ $myLoans->count() }}</div>
                    <div class="text-gray-600 mt-2">借用中の本</div>
                    <div class="mt-3 h-1 rounded-full bg-lib-primary"></div>
                </div>
                
                <!-- 返却期限が近い本 (Success Color) -->
                <div class="card card-body text-center hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-lib-secondary">
                        {{ $myLoans->filter(function($loan) { 
                            $daysUntil = now()->diffInDays($loan->due_date, false);
                            return $daysUntil <= 3 && $daysUntil >= 0 && !$loan->due_date->isPast();
                        })->count() }}
                    </div>
                    <div class="text-gray-600 mt-2">返却期限が近い本</div>
                    <div class="mt-3 h-1 rounded-full bg-lib-secondary"></div>
                </div>
                
                <!-- 期限切れの本 (Error Color) -->
                <div class="card card-body text-center hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-lib-accent">
                        {{ $myLoans->filter(function($loan) { return $loan->due_date->isPast(); })->count() }}
                    </div>
                    <div class="text-gray-600 mt-2">期限切れの本</div>
                    <div class="mt-3 h-1 rounded-full bg-lib-accent"></div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>