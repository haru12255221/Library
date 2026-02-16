<x-app-layout>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('books.create') }}"
                   class="text-text-secondary hover:text-text-primary hover:underline flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    書籍登録に戻る
                </a>
                <span class="text-border-light">|</span>
                <a href="{{ route('books.index') }}"
                   class="text-text-secondary hover:text-text-primary hover:underline flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0M8 5a2 2 0 000 4h8a2 2 0 000-4M8 5v0"></path>
                    </svg>
                    書籍一覧
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-text-primary mb-2">ISBNスキャン</h1>
            <p class="text-text-secondary">ISBNをスキャンするか、手動で入力してください</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- ISBNスキャン部分 -->
            <div class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <h2 class="text-xl font-semibold text-text-primary mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    ISBNスキャン
                </h2>
                
                <!-- 手動入力 -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-primary mb-2">ISBN手動入力</label>
                    <div class="flex gap-2">
                        <input type="text" id="manual-isbn" 
                               class="flex-1 border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"
                               placeholder="978-4-12-345678-9"
                               oninput="handleIsbnInput(this)"
                               onkeypress="handleKeyPress(event)"
                               maxlength="17">
                        <button onclick="fetchBookInfo()"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 transition-colors">
                            検索
                        </button>
                    </div>
                </div>

                <!-- カメラスキャン -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-primary mb-2">カメラでスキャン</label>
                    <div id="reader" class="border border-border-light rounded-md" style="width: 100%; max-width: 300px;"></div>
                </div>

                <!-- 結果表示 -->
                <div id="result" class="text-sm text-text-secondary"></div>
                
                <!-- ローディング -->
                <div id="loading" class="hidden text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <p class="mt-2 text-text-secondary">書籍情報を取得中...</p>
                </div>
            </div>

            <!-- 書籍情報表示・編集部分 -->
            <div class="bg-white rounded-lg shadow-sm border border-border-light p-4 sm:p-6">
                <h2 class="text-xl font-semibold text-text-primary mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    書籍情報
                </h2>
                
                <!-- エラーメッセージ -->
                <div id="error-message" class="hidden border border-red-400 text-text-primary px-4 py-3 rounded mb-4">
                    <p id="error-text"></p>
                </div>

                <!-- 成功メッセージ -->
                <div id="success-message" class="hidden border border-green-500 text-text-primary px-4 py-3 rounded mb-4">
                    <p id="success-text"></p>
                </div>

                <!-- 書籍情報フォーム -->
                <form id="book-form" class="hidden" action="{{ route('books.store') }}" method="POST">
                    @csrf
                    
                    <!-- 表紙画像 -->
                    <div class="mb-4 text-center">
                        <img id="book-thumbnail" src="" alt="表紙画像" 
                             class="hidden mx-auto rounded-lg shadow-md max-w-32 max-h-48">
                        <p id="no-image" class="text-text-secondary text-sm">表紙画像なし</p>
                    </div>

                    <!-- 基本情報 -->
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">タイトル *</label>
                            <input type="text" name="title" id="title" required
                                   class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">著者 *</label>
                            <input type="text" name="author" id="author" required
                                   class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">ISBN *</label>
                            <input type="text" name="isbn" id="isbn" required
                                   class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">出版社</label>
                            <input type="text" name="publisher" id="publisher"
                                   class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-primary mb-1">出版日</label>
                        <input type="date" name="published_date" id="published_date"
                               class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text-primary mb-1">説明</label>
                        <textarea name="description" id="description" rows="4"
                                  class="w-full border border-border-light rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:border-transparent"></textarea>
                    </div>

                    <!-- 隠しフィールド -->
                    <input type="hidden" name="thumbnail_url" id="thumbnail_url">

                    <!-- 登録ボタン -->
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 px-4 py-3 bg-primary text-white rounded-md hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 transition-colors font-medium">
                            書籍を登録
                        </button>
                        <button type="button" onclick="resetForm()"
                                class="px-4 py-3 bg-white text-text-primary border border-border-neutral rounded-md hover:bg-gray-50 transition-colors">
                            リセット
                        </button>
                    </div>
                </form>

                <!-- 初期メッセージ -->
                <div id="initial-message" class="text-center text-text-secondary py-8">
                    <p>ISBNをスキャンまたは入力すると、書籍情報が表示されます</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner = null;

        // ページ読み込み時にカメラを初期化
        document.addEventListener('DOMContentLoaded', function() {
            initializeCamera();
        });

        function initializeCamera() {
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
                document.getElementById('result').innerHTML = '<p class="text-text-primary underline decoration-red-400 decoration-2 underline-offset-4">カメラの初期化に失敗しました</p>';
            }
        }

        function onScanSuccess(decodedText) {
            // ISBNの基本チェック
            if (!decodedText.startsWith('978') && !decodedText.startsWith('979')) {
                showError("これはISBNではありません: " + decodedText);
                return;
            }

            document.getElementById('result').innerHTML = '<p class="text-text-primary">ISBN検出: ' + decodedText + '</p>';
            document.getElementById('manual-isbn').value = decodedText;
            
            // 書籍情報を取得
            fetchBookInfoByIsbn(decodedText);
        }

        function onScanError(errorMessage) {
            // スキャンエラーは無視（連続スキャン中の正常な動作）
        }

        // デバウンス用のタイマー
        let searchTimeout = null;

        function fetchBookInfo() {
            const isbn = document.getElementById('manual-isbn').value.trim();
            if (!isbn) {
                showError("ISBNを入力してください");
                return;
            }
            fetchBookInfoByIsbn(isbn);
        }

        // デバウンス付きの検索関数
        function debouncedSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const isbn = document.getElementById('manual-isbn').value.trim();
                if (isbn && isbn.length >= 10) {
                    fetchBookInfoByIsbn(isbn);
                }
            }, 1000); // 1秒待機
        }

        // ISBN自動フォーマット
        function formatIsbn(input) {
            // 数字とXのみを抽出
            let cleaned = input.replace(/[^0-9X]/g, '');
            
            // ISBN-13の場合のフォーマット
            if (cleaned.length >= 3) {
                if (cleaned.startsWith('978') || cleaned.startsWith('979')) {
                    // 978-4-12-345678-9 の形式
                    return cleaned.replace(/(\d{3})(\d{1})(\d{2})(\d{6})(\d{1})/, '$1-$2-$3-$4-$5');
                }
            }
            
            // ISBN-10の場合のフォーマット
            if (cleaned.length === 10) {
                // 4-12-345678-9 の形式
                return cleaned.replace(/(\d{1})(\d{2})(\d{6})(\d{1})/, '$1-$2-$3-$4');
            }
            
            return cleaned;
        }

        function fetchBookInfoByIsbn(isbn) {
            showLoading(true);
            hideAllMessages();

            fetch('/isbn-fetch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ isbn: isbn })
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success && data.data) {
                    displayBookInfo(data.data);
                } else {
                    const detailedError = getDetailedErrorMessage(data.error || "書籍情報の取得に失敗しました", isbn);
                    showError(detailedError);
                    // エラーでも手動入力フォームは表示
                    showBookForm();
                    document.getElementById('isbn').value = isbn;
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                const detailedError = getDetailedErrorMessage("通信エラーが発生しました", isbn);
                showError(detailedError);
                // エラーでも手動入力フォームは表示
                showBookForm();
                document.getElementById('isbn').value = isbn;
            });
        }

        function displayBookInfo(bookData) {
            // 成功メッセージを表示
            showSuccess(`📚 書籍情報を取得しました: 「${bookData.title}」`);
            
            // フォームに情報を設定
            document.getElementById('title').value = bookData.title || '';
            document.getElementById('author').value = bookData.author || '';
            document.getElementById('isbn').value = bookData.isbn || document.getElementById('manual-isbn').value;
            document.getElementById('publisher').value = bookData.publisher || '';
            document.getElementById('published_date').value = bookData.published_date || '';
            document.getElementById('description').value = bookData.description || '';
            document.getElementById('thumbnail_url').value = bookData.thumbnail_url || '';

            // 表紙画像の表示
            const thumbnail = document.getElementById('book-thumbnail');
            const noImage = document.getElementById('no-image');
            
            if (bookData.thumbnail_url) {
                thumbnail.src = bookData.thumbnail_url;
                thumbnail.classList.remove('hidden');
                noImage.classList.add('hidden');
            } else {
                thumbnail.classList.add('hidden');
                noImage.classList.remove('hidden');
            }

            showBookForm();
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('success-message');
            const successText = document.getElementById('success-text');
            successText.textContent = message;
            successDiv.classList.remove('hidden');
            
            // 成功メッセージを自動で隠す（5秒後）
            setTimeout(() => {
                hideSuccess();
            }, 5000);
        }

        function hideSuccess() {
            document.getElementById('success-message').classList.add('hidden');
        }

        function showBookForm() {
            document.getElementById('initial-message').style.display = 'none';
            document.getElementById('book-form').classList.remove('hidden');
        }

        function showLoading(show) {
            const loading = document.getElementById('loading');
            if (show) {
                loading.classList.remove('hidden');
            } else {
                loading.classList.add('hidden');
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            errorText.innerHTML = message; // textContent から innerHTML に変更（HTMLを表示するため）
            errorDiv.classList.remove('hidden');
            
            // エラーメッセージを自動で隠す（10秒後）
            setTimeout(() => {
                hideError();
            }, 10000);
        }

        function hideError() {
            document.getElementById('error-message').classList.add('hidden');
        }

        function hideAllMessages() {
            hideError();
            hideSuccess();
        }

        // ISBN入力時の処理
        function handleIsbnInput(input) {
            // 自動フォーマット
            const formatted = formatIsbn(input.value);
            if (formatted !== input.value) {
                input.value = formatted;
            }
            
            // デバウンス検索
            debouncedSearch();
        }

        // キーボードイベント処理
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchBookInfo();
            }
        }

        // より詳細なエラーメッセージ
        function getDetailedErrorMessage(error, isbn) {
            if (error.includes('404') || error.includes('見つかりません')) {
                return `ISBN「${isbn}」の書籍情報が見つかりませんでした。以下をご確認ください：
                        <ul class="mt-2 ml-4 list-disc text-sm">
                            <li>ISBNが正しく入力されているか</li>
                            <li>ハイフンの位置が正しいか</li>
                            <li>古い書籍の場合、データベースに登録されていない可能性があります</li>
                        </ul>`;
            }
            
            if (error.includes('通信エラー')) {
                return `通信エラーが発生しました。以下をお試しください：
                        <ul class="mt-2 ml-4 list-disc text-sm">
                            <li>インターネット接続を確認してください</li>
                            <li>しばらく時間をおいて再度お試しください</li>
                            <li>問題が続く場合は管理者にお問い合わせください</li>
                        </ul>`;
            }
            
            return error;
        }

        function resetForm() {
            // タイマーをクリア
            clearTimeout(searchTimeout);
            
            document.getElementById('book-form').reset();
            document.getElementById('book-thumbnail').classList.add('hidden');
            document.getElementById('no-image').classList.remove('hidden');
            document.getElementById('manual-isbn').value = '';
            document.getElementById('result').innerHTML = '';
            hideError();
            
            // フォームを非表示にして初期メッセージを表示
            document.getElementById('book-form').classList.add('hidden');
            document.getElementById('initial-message').style.display = 'block';
        }
    </script>
</x-app-layout>
