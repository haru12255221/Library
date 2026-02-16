<x-app-layout>
    <div class="max-w-7xl mx-auto px-4">
        <!-- 成功・エラーメッセージ -->
        @if(session('success'))
            <x-ui.alert type="success" dismissible class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" dismissible class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <!-- ページタイトル -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-text-primary">マイページ</h2>
                <p class="text-text-secondary mt-2">借りている本の一覧と返却ができます</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('profile.edit') }}"
                    class="text-text-primary hover:text-text-light transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" transform="scale(-1, 1)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    設定
                </a>
            </div>
        </div>

        <!-- 借りている本の一覧 -->
        <div class="bg-background rounded-lg shadow-sm border border-border-light">
            <div class="px-4 sm:px-6 py-4 border-b border-border-light">
                <h3 class="text-lg font-semibold text-text-primary">
                    借りている本 ({{ $myLoans->count() }}冊)
                </h3>
            </div>
            
            <div class="p-3 sm:p-6">
                @if($myLoans->count() > 0)
                    <div class="grid gap-4">
                        @foreach($myLoans as $loan)
                            <div class="border border-border-light rounded-lg p-4 hover:shadow-sm transition-all">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base sm:text-lg font-semibold text-text-primary mb-2">
                                            {{ $loan->book->title }}
                                        </h4>
                                        <p class="text-text-secondary mb-1">著者: {{ $loan->book->author }}</p>
                                        <p class="text-sm text-text-light mb-2">ISBN: {{ $loan->book->isbn }}</p>

                                        <div class="flex flex-col sm:flex-row sm:gap-4 gap-1 text-sm text-text-secondary">
                                            <span>
                                                借りた日: {{ $loan->borrowed_at->format('Y年m月d日') }}
                                            </span>
                                            <span>
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
                                            <div class="mt-2 font-medium text-text-primary underline decoration-red-500 decoration-2 underline-offset-4">
                                                返却期限を過ぎています
                                            </div>
                                        @elseif($daysUntilDue <= 3 && $daysUntilDue >= 0)
                                            <div class="mt-2 font-medium text-text-primary underline decoration-yellow-500 decoration-2 underline-offset-4">
                                                返却期限が近づいています (残り{{ floor($daysUntilDue) }}日)
                                            </div>

                                        @else
                                            <div class="mt-2 font-medium text-text-secondary">
                                                返却期限まで余裕があります (残り{{ floor($daysUntilDue) }}日)
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 返却ボタン -->
                                    <div class="sm:ml-4">
                                        <x-ui.confirm-modal
                                            title="返却確認"
                                            message="「{{ $loan->book->title }}」を返却しますか？"
                                            :action="route('loans.return', $loan)"
                                            confirm-text="返却する"
                                            confirm-variant="danger"
                                        >
                                            <x-ui.button variant="danger">返却する</x-ui.button>
                                        </x-ui.confirm-modal>
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
                        <p class="text-text-light text-lg mb-4">現在借りている本はありません</p>
                        <a href="{{ route('books.index') }}" 
                            class="inline-block px-6 py-3 bg-primary text-text-white rounded-md hover:bg-primary-hover transition-colors">
                            書籍一覧を見る
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 統計情報 -->
        @if($myLoans->count() > 0)
            <div class="mt-8 grid grid-cols-3 gap-3 sm:gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-border-light p-3 sm:p-6 text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-text-primary">{{ $myLoans->count() }}</div>
                    <div class="text-xs sm:text-sm text-text-secondary">借用中の本</div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-border-light p-3 sm:p-6 text-center">
                    @php
                        $nearDueCount = $myLoans->filter(function($loan) {
                            $daysUntil = now()->diffInDays($loan->due_date, false);
                            return $daysUntil <= 3 && $daysUntil >= 0 && !$loan->due_date->isPast();
                        })->count();
                    @endphp
                    <div class="text-2xl sm:text-3xl font-bold text-text-primary {{ $nearDueCount > 0 ? 'underline decoration-yellow-500 decoration-2 underline-offset-4' : '' }}">{{ $nearDueCount }}</div>
                    <div class="text-xs sm:text-sm text-text-secondary">返却期限が近い本</div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-border-light p-3 sm:p-6 text-center">
                    @php
                        $overdueCount = $myLoans->filter(function($loan) { return $loan->due_date->isPast(); })->count();
                    @endphp
                    <div class="text-2xl sm:text-3xl font-bold text-text-primary {{ $overdueCount > 0 ? 'underline decoration-red-400 decoration-2 underline-offset-4' : '' }}">{{ $overdueCount }}</div>
                    <div class="text-xs sm:text-sm text-text-secondary">期限切れの本</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>