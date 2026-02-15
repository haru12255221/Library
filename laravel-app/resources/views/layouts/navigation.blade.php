<!-- 図書館管理システム統一ヘッダー -->
<header class="bg-header-bg border-b border-border-light w-full" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:py-6">
        <div class="flex justify-between items-center gap-2">
            <h1 class="text-2xl font-bold flex items-center text-text-primary flex-shrink min-w-0">
                <span class="flex-shrink-0"><a href="{{ route('books.index') }}"><img src="{{ asset('images/Vector.png') }}" alt="本" class="pr-2 w-auto h-14 sm:h-20"></a></span>
                <span class="text-text-primary truncate hidden lg:inline">「本見れたり、借りれたり」</span>
            </h1>

            <!-- デスクトップナビゲーション -->
            <nav class="hidden md:flex items-baseline gap-6 text-sm font-medium">
                <x-nav-link :href="route('books.index')" :activeRoutes="['books.index', 'home']">
                    書籍一覧
                </x-nav-link>

                @auth
                    @if(auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :activeRoutes="'admin.dashboard'">
                            ダッシュボード
                        </x-nav-link>
                        <x-nav-link :href="route('admin.books.index')" :activeRoutes="'admin.books.index'">
                            書籍管理
                        </x-nav-link>
                        <x-nav-link :href="route('loans.index')" :activeRoutes="'loans.index'">
                            貸出履歴
                        </x-nav-link>
                        <x-nav-link :href="route('loans.overdue')" :activeRoutes="'loans.overdue'">
                            延滞一覧
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :activeRoutes="'admin.users.index'">
                            ユーザー管理
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('loans.my')" :activeRoutes="'loans.my'">
                        マイページ
                    </x-nav-link>

                    {{-- ログアウトはPOSTなのでそのまま --}}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-text-primary hover:underline hover:underline-offset-4 hover:decoration-2">
                            ログアウト
                        </button>
                    </form>
                @else
                    <x-nav-link :href="route('login')" :activeRoutes="'login'">
                        ログイン
                    </x-nav-link>
                    <x-nav-link :href="route('register')" :activeRoutes="'register'">
                        登録
                    </x-nav-link>
                @endauth
            </nav>

            <!-- モバイル用ハンバーガーボタン -->
            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="text-text-secondary hover:text-text-primary focus:outline-none focus:text-text-primary">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- モバイルメニュー -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="md:hidden bg-white border-t border-border-light py-2 w-full">
        <div class="px-4 pt-2 pb-3 space-y-1 w-full">
            <a href="{{ route('books.index') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('books.index', 'home') ? 'font-semibold border-l-2 border-text-primary' : '' }}">書籍一覧</a>

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('admin.dashboard') ? 'font-semibold border-l-2 border-text-primary' : '' }}">ダッシュボード</a>
                    <a href="{{ route('books.create') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('books.create') ? 'font-semibold border-l-2 border-text-primary' : '' }}">書籍登録</a>
                    <a href="{{ route('loans.index') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('loans.index') ? 'font-semibold border-l-2 border-text-primary' : '' }}">貸出履歴</a>
                    <a href="{{ route('loans.overdue') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('loans.overdue') ? 'font-semibold border-l-2 border-text-primary' : '' }}">延滞一覧</a>
                    <a href="{{ route('admin.users.index') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('admin.users.index') ? 'font-semibold border-l-2 border-text-primary' : '' }}">ユーザー管理</a>
                @endif
                <a href="{{ route('loans.my') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('loans.my') ? 'font-semibold border-l-2 border-text-primary' : '' }}">マイページ</a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" @click="open = false" class="block w-full text-left px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px]">ログアウト</button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('login') ? 'font-semibold border-l-2 border-text-primary' : '' }}">ログイン</a>
                <a href="{{ route('register') }}" @click="open = false" class="block px-3 py-3 rounded-md text-base font-medium text-text-primary hover:bg-background min-h-[44px] {{ request()->routeIs('register') ? 'font-semibold border-l-2 border-text-primary' : '' }}">登録</a>
            @endauth
        </div>
    </div>
</header>