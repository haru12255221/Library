<x-app-layout>
    <div class="max-w-7xl mx-auto px-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-lib-text-primary">
                    貸出履歴一覧 (全{{ $loans->count() }}件)
                </h3>
            </div>
            
            <div class="p-6">
                @if($loans->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lib-text-secondary uppercase tracking-wider">
                                        利用者
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lib-text-secondary uppercase tracking-wider">
                                        書籍
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lib-text-secondary uppercase tracking-wider">
                                        @php
                                            $linkDirection = ($sort === 'borrowed_at' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('loans.index', ['sort' => 'borrowed_at', 'direction' => $linkDirection]) }}"
                                           class="text-lib-primary hover:text-lib-primary-hover transition-colors">
                                            貸出日
                                            @if ($sort === 'borrowed_at')
                                                @if ($direction === 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lib-text-secondary uppercase tracking-wider">
                                        @php
                                            $linkDirection = ($sort === 'due_date' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('loans.index', ['sort' => 'due_date', 'direction' => $linkDirection]) }}"
                                           class="text-lib-primary hover:text-lib-primary-hover transition-colors">
                                            返却期限
                                            @if ($sort === 'due_date')
                                                @if ($direction === 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lib-text-secondary uppercase tracking-wider">
                                        @php
                                            $linkDirection = ($sort === 'status' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('loans.index', ['sort' => 'status', 'direction' => $linkDirection]) }}"
                                           class="text-lib-primary hover:text-lib-primary-hover transition-colors">
                                            状態
                                            @if ($sort === 'status')
                                                @if ($direction === 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($loans as $loan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-lib-text-primary">
                                            {{ $loan->user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-lib-text-primary">
                                            {{ $loan->book->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-lib-text-secondary">
                                            {{ $loan->borrowed_at->format('Y年m月d日') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-lib-text-secondary">
                                            {{ $loan->due_date->format('Y年m月d日') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($loan->status === 'returned')
                                                {{-- 返却済み: 成功カラー（自然な緑） --}}
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full flex items-center gap-1 bg-lib-secondary-light text-lib-secondary-hover border border-lib-secondary">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    返却済み
                                                </span>
                                            @elseif($loan->due_date->isPast())
                                                {{-- 期限切れ: エラーカラー（コーラルレッド） --}}
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full flex items-center gap-1 bg-lib-accent-light text-lib-accent-hover border border-lib-accent">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    期限切れ
                                                </span>
                                            @else
                                                {{-- 貸出中: プライマリカラー（スチールブルー） --}}
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full flex items-center gap-1 bg-lib-primary-light text-lib-primary-hover border border-lib-primary">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                    貸出中
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">
                            <img src="{{ asset('images/Library1.png') }}" alt="本" class="w-auto h-32 mx-auto">
                        </div>
                        <p class="text-lib-text-secondary text-lg mb-4">貸出履歴がありません</p>
                        <x-button :href="route('books.index')" variant="primary" size="lg">
                            書籍一覧を見る
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>