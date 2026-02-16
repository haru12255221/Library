<x-app-layout>
    <div class="bg-background rounded-lg shadow-sm border border-border-light mx-4 my-6">
        <div class="px-4 sm:px-6 py-4 border-b border-border-light">
            <h3 class="text-lg font-semibold text-text-primary">
                延滞一覧 ({{ $overdueLoans->count() }}件)
            </h3>
        </div>
        <div class="p-3 sm:p-6">
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

            @if($overdueLoans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-light">
                        <thead class="bg-background">
                            <tr>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">書籍情報</th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden sm:table-cell">借主</th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden lg:table-cell">貸出日</th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden md:table-cell">返却期限</th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">延滞</th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-background divide-y divide-border-light">
                            @foreach($overdueLoans as $loan)
                                <tr>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <a href="{{ route('books.show', $loan->book) }}" class="hover:underline">
                                            <div class="text-sm font-medium text-text-primary">{{ $loan->book->title }}</div>
                                            <div class="text-xs sm:text-sm text-text-secondary">{{ $loan->book->author }}</div>
                                        </a>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden sm:table-cell">{{ $loan->user->name }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden lg:table-cell">{{ $loan->borrowed_at->format('Y/m/d') }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden md:table-cell">{{ $loan->due_date->format('Y/m/d') }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        @php
                                            $overdueDays = (int) $loan->due_date->diffInDays(now());
                                        @endphp
                                        <span class="text-xs font-medium text-text-primary underline {{ $overdueDays >= 7 ? 'decoration-red-400' : 'decoration-yellow-500' }} decoration-2 underline-offset-4">
                                            {{ $overdueDays }}日超過
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <x-ui.confirm-modal
                                            title="強制返却"
                                            message="「{{ $loan->book->title }}」（借主: {{ $loan->user->name }}）を強制返却しますか？"
                                            :action="route('loans.force-return', $loan)"
                                            confirm-text="強制返却する"
                                            confirm-variant="danger"
                                        >
                                            <x-ui.button variant="danger" size="sm">強制返却</x-ui.button>
                                        </x-ui.confirm-modal>
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
                    <p class="text-text-secondary text-lg mb-4">現在、延滞中の書籍はありません</p>
                    <a href="{{ route('loans.index') }}"
                        class="inline-block px-6 py-3 bg-primary text-text-white rounded-md hover:bg-primary-hover transition-colors">
                        貸出履歴を見る
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
