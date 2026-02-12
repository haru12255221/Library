<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct()
    {
        // 管理者権限チェック
        $this->middleware('admin');
    }

    /**
     * 管理者専用書籍一覧
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Book::query();

        if ($search) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'like', "%{$escaped}%")
                    ->orWhere('author', 'like', "%{$escaped}%")
                    ->orWhere('isbn', 'like', "%{$escaped}%");
            });
        }

        $books = $query->with('currentLoan')
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return view('admin.books.index', compact('books', 'search'));
    }
}