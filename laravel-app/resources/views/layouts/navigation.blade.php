<!-- 図書館管理システム統一ヘッダー -->
<header class="shadow-md bg-header-bg" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center text-text-primary">
                <span><a href="{{ route('books.index') }}"><img src="{{ asset('images/Vector.png') }}" alt="本" class="pr-2 w-auto h-20 mx-auto"></a></span>
                <span class="text-text-primary truncate max-w-[150px] md:max-w-none">「本見れたり、借りれたり」</span>
            </h1>

            <!-- デスクトップナビゲーション -->
            <nav class="hidden md:flex items-baseline gap-6 text-sm font-medium">
                <a href="{{ route('books.index') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                    <span class="{{ request()->routeIs('books.index', 'home') ? 'font-semibold text-primary' : '' }}">書籍一覧</span>
                    @if(request()->routeIs('books.index', 'home'))
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                    @endif
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('books.create') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                            <span class="{{ request()->routeIs('books.create') ? 'font-semibold text-primary' : '' }}">書籍登録</span>
                            @if(request()->routeIs('books.create'))
                                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                            @endif
                        </a>
                        <a href="{{ route('loans.index') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                            <span class="{{ request()->routeIs('loans.index') ? 'font-semibold text-primary' : '' }}">貸出履歴</span>
                            @if(request()->routeIs('loans.index'))
                                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('loans.my') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                        <span class="{{ request()->routeIs('loans.my') ? 'font-semibold text-primary' : '' }}">マイページ</span>
                        @if(request()->routeIs('loans.my'))
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="transition-colors text-text-primary hover:text-danger">
                            ログアウト
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                        <span class="{{ request()->routeIs('login') ? 'font-semibold text-primary' : '' }}">ログイン</span>
                        @if(request()->routeIs('login'))
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('register') }}" class="relative pb-4 transition-colors text-text-primary hover:text-primary">
                        <span class="{{ request()->routeIs('register') ? 'font-semibold text-primary' : '' }}">登録</span>
                        @if(request()->routeIs('register'))
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-full"></span>
                        @endif
                    </a>
                @endauth
            </nav>

            <!-- モバイル用ハンバーガーボタン -->
            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- モバイルメニュー -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="md:hidden bg-white shadow-lg py-2">
        <div class="px-4 pt-2 pb-3 space-y-1">
            <a href="{{ route('books.index') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('books.index', 'home') ? 'font-semibold text-primary' : '' }}">書籍一覧</a>

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('books.create') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('books.create') ? 'font-semibold text-primary' : '' }}">書籍登録</a>
                    <a href="{{ route('loans.index') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('loans.index') ? 'font-semibold text-primary' : '' }}">貸出履歴</a>
                @endif
                <a href="{{ route('loans.my') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('loans.my') ? 'font-semibold text-primary' : '' }}">マイページ</a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" @click="open = false" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-danger">ログアウト</button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('login') ? 'font-semibold text-primary' : '' }}">ログイン</a>
                <a href="{{ route('register') }}" @click="open = false" class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-gray-100 hover:text-primary {{ request()->routeIs('register') ? 'font-semibold text-primary' : '' }}">登録</a>
            @endauth
        </div>
    </div>
</header>