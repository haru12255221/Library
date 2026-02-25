<x-app-layout>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <div class="w-full sm:max-w-2xl md:max-w-4xl lg:max-w-6xl xl:max-w-7xl mx-auto px-4">
        <!-- 成功・エラーメッセージ -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-success px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-danger px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- ページタイトル -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-text-primary">マイページ</h2>
                <p class="text-text-secondary mt-2">借りている本の一覧と返却ができます</p>
            </div>
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('profile.edit') }}" 
                    class="text-text-primary hover:text-text-light transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" transform="scale(-1, 1)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    設定
                </a>
            </div>
        </div>

        <!-- 検索フォーム -->
        <x-ui.card class="mb-8" x-data="searchForm()">
            <form action="{{ route('loans.my') }}" method="GET" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text-text-primary mb-2">借りている本を検索</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input"
                            x-model="searchQuery"
                            placeholder="タイトル、著者、またはISBNで検索" 
                            value="{{ request('search') }}"
                            class="w-full px-3 py-2 pr-20 border border-border-light rounded-md focus:outline-none focus:ring-2 focus:ring-primary-hover focus:border-transparent"
                            @input="handleInput"
                        >
                        <!-- クリアボタン -->
                        <button 
                            type="button"
                            x-show="searchQuery.length > 0"
                            @click="clearSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            title="検索をクリア"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <x-ui.button 
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full sm:w-auto min-h-[44px]"
                        x-bind:disabled="isSearching"
                        @click="isSearching = true"
                    >
                        <span x-show="!isSearching" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            検索
                        </span>
                        <span x-show="isSearching" class="flex items-center justify-center gap-2">
                            <x-ui.loading type="spinner" size="sm" />
                            検索中...
                        </span>
                    </x-ui.button>
                    
                    <x-ui.button 
                        type="button"
                        @click="startBarcodeScanning"
                        variant="secondary"
                        size="lg"
                        class="w-full sm:w-auto min-h-[44px]"
                        id="barcode-scan-btn"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            バーコードスキャン
                        </span>
                    </x-ui.button>
                    
                    <x-ui.button 
                        type="button"
                        @click="resetForm"
                        x-show="searchQuery.length > 0 || hasSearchParam"
                        variant="secondary"
                        size="lg"
                        class="w-full sm:w-auto min-h-[44px]"
                    >
                        リセット
                    </x-ui.button>
                </div>
                
                <!-- バーコードスキャナー -->
                <div id="barcode-scanner" class="hidden mt-4">
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-medium text-gray-900">バーコードスキャン</h3>
                            <button type="button" onclick="stopBarcodeScanning()" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="reader" class="border border-gray-300 rounded-md mx-auto" style="width: 100%; max-width: 300px;"></div>
                        <div id="scan-result" class="mt-3 text-sm text-gray-600"></div>
                    </div>
                </div>
                
                <!-- 検索結果の件数表示 -->
                @if(request('search'))
                    <div class="text-sm text-gray-600">
                        「{{ request('search') }}」の検索結果: {{ $myLoans->count() }}件
                    </div>
                @endif
            </form>
        </x-ui.card>

        <!-- 借りている本の一覧 -->
        <div class="bg-background rounded-lg shadow">
            <div class="px-6 py-4 border-b border-border-light">
                <h3 class="text-lg font-semibold text-text-primary">
                    借りている本 ({{ $myLoans->count() }}冊)
                </h3>
            </div>
            
            <div class="p-6">
                @if($myLoans->count() > 0)
                    <div class="grid gap-4">
                        @foreach($myLoans as $loan)
                            <div class="border border-border-light rounded-lg p-4 hover:shadow-md transition-all">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-text-primary mb-2">
                                            {{ $loan->book->title }}
                                        </h4>
                                        <p class="text-text-secondary mb-1">著者: {{ $loan->book->author }}</p>
                                        <p class="text-sm text-text-light mb-2">ISBN: {{ $loan->book->isbn }}</p>
                                        
                                        <div class="flex gap-4 text-sm">
                                            <span class="text-primary">
                                                借りた日: {{ $loan->borrowed_at->format('Y年m月d日') }}
                                            </span>
                                            <span class="text-danger">
                                                返却期限: {{ $loan->due_date->format('Y年m月d日') }}
                                            </span>
                                        </div>
                                        
                                        <!-- 期限チェック -->
                                        @php
                                            $now = now();
                                            $dueDate = $loan->due_date;
                                            $daysUntilDue = $now->diffInDays($dueDate, false);
                                            $isPast = $dueDate->isPast();
                                        @endphp
                                        
                                        @if($isPast)
                                            <div class="mt-2 font-medium text-danger-hover">
                                                ⚠️ 返却期限を過ぎています
                                            </div>
                                        @elseif($daysUntilDue <= 3 && $daysUntilDue >= 0)
                                            <div class="mt-2 font-medium text-danger">
                                                ⚠️ 返却期限が近づいています (残り{{ floor($daysUntilDue) }}日)
                                            </div>

                                        @else
                                            <div class="mt-2 font-medium text-success">
                                                返却期限まで余裕があります (残り{{ floor($daysUntilDue) }}日)
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 返却ボタン（管理者のみ） -->
                                    @if(auth()->user()->isAdmin())
                                        <div class="ml-4">
                                            <form method="POST" action="{{ route('loans.return', $loan) }}"
                                                    onsubmit="return confirm('「{{ $loan->book->title }}」を返却しますか？')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-4 py-2 bg-danger text-text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 hover:bg-danger-hover transition-colors">
                                                    返却する
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">
                            <img src="{{ asset('images/Library1.png') }}" alt="本" class="w-auto h-32 mx-auto">
                        </div>
                        <p class="text-text-light text-lg mb-4">現在借りている本はありません</p>
                        <a href="{{ route('books.index') }}" 
                            class="inline-block px-6 py-3 bg-primary text-text-white rounded-md hover:bg-primary-hover transition-colors">
                            書籍一覧を見る
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 統計情報 -->
        @if($myLoans->count() > 0)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-background rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-primary">{{ $myLoans->count() }}</div>
                    <div class="text-secondary">借用中の本</div>
                </div>
                
                <div class="bg-background rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-success">
                        {{ $myLoans->filter(function($loan) { 
                            $daysUntil = now()->diffInDays($loan->due_date, false);
                            return $daysUntil <= 3 && $daysUntil >= 0 && !$loan->due_date->isPast();
                        })->count() }}
                    </div>
                    <div class="text-text-secondary">返却期限が近い本</div>
                </div>
                
                <div class="bg-background rounded-lg shadow p-6 text-center">
                    <div class="text-3xl font-bold text-danger">
                        {{ $myLoans->filter(function($loan) { return $loan->due_date->isPast(); })->count() }}
                    </div>
                    <div class="text-text-secondary">期限切れの本</div>
                </div>
            </div>
        @endif
    </div>

    <script>
        let html5QrcodeScanner = null;
        
        function searchForm() {
            return {
                searchQuery: '{{ request('search') }}' || '',
                hasSearchParam: {{ request('search') ? 'true' : 'false' }},
                isSearching: false,
                
                handleInput() {
                    // リアルタイム検索のためのデバウンス処理
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        // 自動検索は無効化（サーバー負荷を考慮）
                        // 必要に応じて有効化可能
                        // this.submitSearch();
                    }, 500);
                },
                
                clearSearch() {
                    this.searchQuery = '';
                    document.querySelector('input[name="search"]').focus();
                },
                
                resetForm() {
                    this.searchQuery = '';
                    // 検索パラメータをクリアして一覧ページに戻る
                    window.location.href = '{{ route('loans.my') }}';
                },
                
                submitSearch() {
                    if (this.searchQuery.trim().length > 0) {
                        document.querySelector('form').submit();
                    }
                },
                
                startBarcodeScanning() {
                    startBarcodeScanning();
                }
            }
        }

        function startBarcodeScanning() {
            const scannerDiv = document.getElementById('barcode-scanner');
            const resultDiv = document.getElementById('scan-result');
            
            scannerDiv.classList.remove('hidden');
            resultDiv.innerHTML = '<p class="text-blue-600">カメラを起動しています...</p>';
            
            try {
                html5QrcodeScanner = new Html5Qrcode("reader");
                html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    onScanSuccess,
                    onScanError
                );
            } catch (error) {
                console.error("カメラの初期化に失敗:", error);
                resultDiv.innerHTML = '<p class="text-red-500">カメラの初期化に失敗しました</p>';
            }
        }

        function stopBarcodeScanning() {
            const scannerDiv = document.getElementById('barcode-scanner');
            const resultDiv = document.getElementById('scan-result');
            
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner = null;
                    scannerDiv.classList.add('hidden');
                    resultDiv.innerHTML = '';
                }).catch(err => {
                    console.error("カメラの停止に失敗:", err);
                    scannerDiv.classList.add('hidden');
                    resultDiv.innerHTML = '';
                });
            } else {
                scannerDiv.classList.add('hidden');
                resultDiv.innerHTML = '';
            }
        }

        function onScanSuccess(decodedText) {
            // ISBNの基本チェック
            if (!decodedText.startsWith('978') && !decodedText.startsWith('979')) {
                document.getElementById('scan-result').innerHTML = '<p class="text-red-500">これはISBNではありません: ' + decodedText + '</p>';
                return;
            }

            document.getElementById('scan-result').innerHTML = '<p class="text-green-600">✅ ISBN検出: ' + decodedText + '</p>';
            
            // 検索フィールドに値を設定
            const searchInput = document.getElementById('search-input');
            searchInput.value = decodedText;
            
            // Alpine.jsのデータも更新
            const alpineData = Alpine.$data(searchInput.closest('[x-data]'));
            if (alpineData) {
                alpineData.searchQuery = decodedText;
            }
            
            // スキャナーを停止
            stopBarcodeScanning();
            
            // 自動で検索を実行
            setTimeout(() => {
                document.querySelector('form').submit();
            }, 500);
        }

        function onScanError(errorMessage) {
            // スキャンエラーは無視（連続スキャン中の正常な動作）
        }
    </script>
</x-app-layout>