<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

// パブリックルート（認証不要）
Route::get('/', [BookController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 認証が必要なルート
Route::middleware('auth')->group(function () {

    // プロフィール管理
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 貸出機能（一般ユーザー）
    Route::get('/my-loans', [LoanController::class, 'myLoans'])->name('loans.my');
    Route::post('/loans/borrow', [LoanController::class, 'borrow'])->name('loans.borrow');
    Route::post('/loans/return/{loan}', [LoanController::class, 'returnBook'])->name('loans.return');
});

// 管理者専用ルート（書籍管理）
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/books', [\App\Http\Controllers\Admin\BookController::class, 'index'])->name('books.index');
});

// 認証済みユーザー用ルート（書籍管理）
Route::middleware(['auth'])->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
});

// 書籍詳細は最後に配置（ワイルドカードルートのため）
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// 認証済みユーザー用ルート（管理者権限を一時的に削除）
Route::middleware(['auth'])->group(function () {
    // 貸出管理
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
});

Route::get('/isbn-scan', function () {
    return view('isbn-scan');
});
Route::post('/isbn-fetch', [\App\Http\Controllers\BookController::class, 'fetchFromISBN']);


require __DIR__.'/auth.php';
