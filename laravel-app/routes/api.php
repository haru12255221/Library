<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\BookSearchService;

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

// ISBN検索API（1分あたり30回まで）
Route::middleware('throttle:30,1')->get('/book/info/{isbn}', function (Request $request, $isbn) {
    try {
        $service = app(BookSearchService::class);
        $bookData = $service->fetchByIsbn($isbn);

        if ($bookData) {
            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $bookData['title'],
                    'author' => $bookData['authors'],
                    'isbn' => $isbn,
                    'cover' => $bookData['thumbnail_url'],
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'この ISBN の書籍情報が見つかりませんでした。'
        ], 404);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("ISBN API Error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'サーバーエラーが発生しました。しばらくしてから再度お試しください。'
        ], 500);
    }
})->name('api.book.info');