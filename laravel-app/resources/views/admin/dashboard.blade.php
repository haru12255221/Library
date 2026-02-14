<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-text-primary">ダッシュボード</h1>
        </div>

        <!-- 統計カード -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">総蔵書数</div>
                <div class="text-3xl font-bold text-text-primary mt-2">{{ $totalBooks }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">貸出中</div>
                <div class="text-3xl font-bold text-yellow-600 mt-2">{{ $loanedBooks }}</div>
            </div>
            <a href="{{ route('loans.overdue') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
                <div class="text-sm font-medium text-gray-500">延滞中</div>
                <div class="text-3xl font-bold {{ $overdueBooks > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">{{ $overdueBooks }}</div>
            </a>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">登録ユーザー数</div>
                <div class="text-3xl font-bold text-text-primary mt-2">{{ $totalUsers }}</div>
            </div>
        </div>

        <!-- 最近の貸出 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-text-primary">最近の貸出</h3>
                <a href="{{ route('loans.index') }}" class="text-sm text-primary hover:text-primary-hover transition-colors">すべて見る</a>
            </div>
            @if($recentLoans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">書籍</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">借主</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">貸出日</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状態</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($recentLoans as $loan)
                                <tr>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('books.show', $loan->book) }}" class="text-text-primary hover:text-primary transition-colors font-medium">
                                            {{ $loan->book->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $loan->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $loan->borrowed_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4">
                                        @if($loan->returned_at)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">返却済み</span>
                                        @elseif($loan->due_date->isPast())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">期限切れ</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">貸出中</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">貸出記録がありません</div>
            @endif
        </div>
    </div>
</x-app-layout>
