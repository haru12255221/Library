<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-text-primary">ダッシュボード</h1>
        </div>

        <!-- 統計カード -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <div class="text-xs sm:text-sm font-medium text-text-secondary">総蔵書数</div>
                <div class="text-2xl sm:text-3xl font-bold text-text-primary mt-1 sm:mt-2">{{ $totalBooks }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <div class="text-xs sm:text-sm font-medium text-text-secondary">貸出中</div>
                <div class="text-2xl sm:text-3xl font-bold text-text-primary mt-1 sm:mt-2">{{ $loanedBooks }}</div>
            </div>
            <a href="{{ route('loans.overdue') }}" class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6 hover:border-border-neutral transition-colors">
                <div class="text-xs sm:text-sm font-medium text-text-secondary">延滞中</div>
                <div class="text-2xl sm:text-3xl font-bold text-text-primary mt-1 sm:mt-2 {{ $overdueBooks > 0 ? 'underline decoration-red-400 decoration-2 underline-offset-4' : '' }}">{{ $overdueBooks }}</div>
            </a>
            <div class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <div class="text-xs sm:text-sm font-medium text-text-secondary">登録ユーザー数</div>
                <div class="text-2xl sm:text-3xl font-bold text-text-primary mt-1 sm:mt-2">{{ $totalUsers }}</div>
            </div>
        </div>

        <!-- 最近の貸出 -->
        <div class="bg-white rounded-lg shadow-sm border border-border-light">
            <div class="px-4 sm:px-6 py-4 border-b border-border-light flex justify-between items-center">
                <h3 class="text-lg font-semibold text-text-primary">最近の貸出</h3>
                <a href="{{ route('loans.index') }}" class="text-sm text-text-secondary hover:underline">すべて見る</a>
            </div>
            @if($recentLoans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-light">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase">書籍</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase">借主</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase hidden sm:table-cell">貸出日</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase">状態</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light">
                            @foreach($recentLoans as $loan)
                                <tr>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm">
                                        <a href="{{ route('books.show', $loan->book) }}" class="text-text-primary hover:underline font-medium">
                                            {{ $loan->book->title }}
                                        </a>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary">{{ $loan->user->name }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden sm:table-cell">{{ $loan->borrowed_at->format('Y/m/d') }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        @if($loan->returned_at)
                                            <span class="text-xs font-medium text-text-primary underline decoration-green-500 decoration-2 underline-offset-4">返却済み</span>
                                        @elseif($loan->due_date->isPast())
                                            <span class="text-xs font-medium text-text-primary underline decoration-red-400 decoration-2 underline-offset-4">期限切れ</span>
                                        @else
                                            <span class="text-xs font-medium text-text-primary underline decoration-yellow-500 decoration-2 underline-offset-4">貸出中</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-text-secondary">貸出記録がありません</div>
            @endif
        </div>
    </div>
</x-app-layout>
