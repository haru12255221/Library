<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-text-primary">ユーザー管理</h1>
            <p class="text-text-secondary mt-2">全{{ $users->count() }}名</p>
        </div>

        @if(session('success'))
            <x-ui.alert type="success" dismissible class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="danger" dismissible class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザー</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メールアドレス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">権限</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出中</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">登録日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-gray-400 ml-1">(あなた)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->isAdmin())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">管理者</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">一般ユーザー</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->loans_count }}冊</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->created_at->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->id !== auth()->id())
                                        @php
                                            $newRole = $user->isAdmin() ? '一般ユーザー' : '管理者';
                                        @endphp
                                        <x-ui.confirm-modal
                                            title="権限変更"
                                            message="「{{ $user->name }}」の権限を{{ $newRole }}に変更しますか？"
                                            :action="route('admin.users.toggle-role', $user)"
                                            method="PATCH"
                                            confirm-text="変更する"
                                            confirm-variant="{{ $user->isAdmin() ? 'danger' : 'primary' }}"
                                        >
                                            <x-ui.button variant="secondary" size="sm">{{ $newRole }}にする</x-ui.button>
                                        </x-ui.confirm-modal>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
