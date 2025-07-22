<!-- 図書館管理システム統一ヘッダー -->
<header class="bg-[#295d72] shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-white">図書館管理システム</h1>
            <nav class="flex gap-4">
                <a href="{{ route('books.index') }}" class="text-white hover:text-gray-200 transition-colors {{ request()->routeIs('books.index') || request()->routeIs('home') ? 'font-semibold' : '' }}">
                    書籍一覧
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('books.create') }}" class="text-white hover:text-gray-200 transition-colors {{ request()->routeIs('books.create') ? 'font-semibold' : '' }}">
                            書籍登録
                        </a>
                    @endif
                    <a href="{{ route('loans.my') }}" class="text-white hover:text-gray-200 transition-colors {{ request()->routeIs('loans.my') ? 'font-semibold' : '' }}">
                        マイページ
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-gray-200 transition-colors">
                            ログアウト
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-white hover:text-gray-200 transition-colors">
                        ログイン
                    </a>
                @endauth
            </nav>
        </div>
    </div>
</header>
