<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-text-primary">ユーザー管理</h1>
            <p class="text-text-secondary mt-2">全{{ $users->count() }}名</p>
        </div>

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

        <div class="bg-white rounded-lg shadow-sm border border-border-light overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-light">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">ユーザー</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden md:table-cell">メールアドレス</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">権限</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden sm:table-cell">貸出中</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider hidden lg:table-cell">登録日</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <div class="text-sm font-medium text-text-primary">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-text-light ml-1">(あなた)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden md:table-cell">{{ $user->email }}</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    @if($user->isAdmin())
                                        <span class="text-xs font-medium text-text-primary underline decoration-light-blue-400 decoration-2 underline-offset-4">管理者</span>
                                    @else
                                        <span class="text-xs font-medium text-text-secondary">一般</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden sm:table-cell">{{ $user->loans_count }}冊</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-text-secondary hidden lg:table-cell">{{ $user->created_at->format('Y/m/d') }}</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
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
                                        <span class="text-xs text-text-light">-</span>
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
