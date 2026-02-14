<x-app-layout>
    <div class="bg-background rounded-lg shadow m-4">
        <div class="px-6 py-4 border-b border-border-light">
            <h3 class="text-lg font-semibold text-text-primary">
                延滞一覧 ({{ $overdueLoans->count() }}件)
            </h3>
        </div>
        <div class="p-6 m-4">
            @if($overdueLoans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-light">
                        <thead class="bg-background">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">書籍情報</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">借主</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">貸出日</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">返却期限</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">延滞日数</th>
                            </tr>
                        </thead>
                        <tbody class="bg-background divide-y divide-border-light">
                            @foreach($overdueLoans as $loan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('books.show', $loan->book) }}" class="hover:text-primary transition-colors">
                                            <div class="text-sm font-medium text-text-primary">{{ $loan->book->title }}</div>
                                            <div class="text-sm text-text-secondary">著者: {{ $loan->book->author }}</div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">{{ $loan->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">{{ $loan->borrowed_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">{{ $loan->due_date->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $overdueDays = (int) $loan->due_date->diffInDays(now());
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $overdueDays >= 7 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $overdueDays }}日超過
                                        </span>
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
