<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ISBN検索API
Route::get('/book/info/{isbn}', function (Request $request, $isbn) {
    try {
        // Google Books APIを使用して書籍情報を取得
        $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn);
        $response = Http::get($url);
        
        if ($response->successful() && $response->json('totalItems') > 0) {
            $bookInfo = $response->json('items')[0]['volumeInfo'];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $bookInfo['title'] ?? 'タイトル不明',
                    'author' => implode(', ', $bookInfo['authors'] ?? ['著者不明']),
                    'isbn' => $isbn,
                    'cover' => $bookInfo['imageLinks']['thumbnail'] ?? null,
                ]
            ]);
        }
        // 書籍情報が見つからない、またはAPIリクエストが失敗した場合
        return response()->json([
            'success' => false,
            'error' => 'この ISBN の書籍情報が見つかりませんでした。'
        ], 404);
    } catch (\Exception $e) {
        // 例外が発生した場合（ネットワークエラーなど）
        return response()->json([
            'success' => false,
            'error' => 'サーバーエラーが発生しました: ' . $e->getMessage()
        ], 500);
    }
})->name('api.book.info');