<x-app-layout>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <div class="max-w-4xl mx-auto px-4">
        <x-ui.card>
            <h1 class="text-2xl font-bold mb-6">トーストコンポーネントテスト</h1>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button 
                        onclick="showToast('success', '操作が正常に完了しました！')"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
                    >
                        成功トースト
                    </button>
                    
                    <button 
                        onclick="showToast('error', 'エラーが発生しました。')"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
                    >
                        エラートースト
                    </button>
                    
                    <button 
                        onclick="showToast('warning', '注意が必要です。')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded"
                    >
                        警告トースト
                    </button>
                    
                    <button 
                        onclick="showToast('info', '情報をお知らせします。')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        情報トースト
                    </button>
                </div>
            </div>
        </x-ui.card>
    </div>
    
    <!-- トーストコンテナ -->
    <div id="toast-container" class="toast-container"></div>
    
    <script>
        function showToast(type, message) {
            const container = document.getElementById('toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <x-ui.toast 
                    type="${type}" 
                    message="${message}" 
                    id="${toastId}"
                    :dismissible="true"
                    :duration="5000"
                />
            `;
            
            // 実際の実装では、サーバーサイドでレンダリングするか、
            // JavaScriptでDOMを直接操作します
            console.log('Toast would be shown:', { type, message, id: toastId });
            alert(`${type.toUpperCase()}: ${message}`);
        }
    </script>
</x-app-layout>