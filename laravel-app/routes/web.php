<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

// パブリックルート（認証不要）
Route::get('/', [BookController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');

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

    // ISBNスキャン・検索（認証必須）
    Route::view('/isbn-scan', 'isbn-scan')->name('isbn.scan');
    Route::post('/isbn-fetch', [BookController::class, 'fetchFromISBN'])->name('isbn.fetch');
});

// 管理者専用ルート（書籍管理）
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/books', [\App\Http\Controllers\Admin\BookController::class, 'index'])->name('books.index');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'toggleRole'])->name('users.toggle-role');
});

// 管理者専用ルート（書籍管理）
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});

// 書籍詳細は最後に配置（ワイルドカードルートのため）
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// 管理者専用ルート（貸出管理）
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/overdue', [LoanController::class, 'overdue'])->name('loans.overdue');
    Route::post('/loans/{loan}/force-return', [LoanController::class, 'forceReturn'])->name('loans.force-return');
});


require __DIR__.'/auth.php';
