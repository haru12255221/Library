<x-app-layout>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#4f4f4f]">
                貸出履歴一覧 (全{{ $loans->count() }}件)
            </h3>
        </div>
        <div class="p-6">
            @if($loans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">書籍情報</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">借主</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @php
                                        $linkDirection = ($sort === 'borrowed_at' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ route('loans.index', ['sort' => 'borrowed_at', 'direction' => $linkDirection]) }}">
                                        貸出日
                                        @if ($sort === 'borrowed_at')
                                            <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @php
                                        $linkDirection = ($sort === 'due_date' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ route('loans.index', ['sort' => 'due_date', 'direction' => $linkDirection]) }}">
                                        返却期限
                                        @if ($sort === 'due_date')
                                            <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @php
                                        $linkDirection = ($sort === 'status' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ route('loans.index', ['sort' => 'status', 'direction' => $linkDirection]) }}">
                                        状態
                                        @if ($sort === 'status')
                                            <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($loans as $loan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $loan->book->title }}</div>
                                        <div class="text-sm text-gray-500">著者: {{ $loan->book->author }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->borrowed_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loan->due_date->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- ▼ここがポイント！▼ --}}
                                        @if ($loan->returned_at)
                                            {{-- 返却日が記録されていれば「返却済み」と表示 --}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                返却済み ({{ $loan->returned_at->format('Y/m/d') }})
                                            </span>
                                        @elseif ($loan->due_date->isPast())
                                            {{-- 返却されておらず、期限が過ぎていれば「期限切れ」と表示 --}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                期限切れ
                                            </span>
                                        @else
                                            {{-- それ以外（返却されておらず、期限内）は「貸出中」--}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
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
                {{-- 貸出がない場合の表示（ここは元のままでOK） --}}
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">
                        <img src="{{ asset('images/Library1.png') }}" alt="本" class="w-auto h-32 mx-auto">
                    </div>
                    <p class="text-gray-500 text-lg mb-4">現在借りられている本はありません</p>
                    <a href="{{ route('books.index') }}" 
                        class="inline-block px-6 py-3 bg-[#295d72] text-white rounded-md hover:bg-[#3a7a94] transition-colors">
                        書籍一覧を見る
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>