<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Requestはクラス名、indexはメソッド名
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Book::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->with('currentLoan')->get();

        return view('books.index', compact('books', 'search'));
    }
    // 書籍登録フォーム
    public function create()
    {
        return view('books.create');
    }

    // 書籍登録処理
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'isbn' => 'required|unique:books',
        ]);

        // データベースに保存
        Book::create($request->all());

        // 一覧画面にリダイレクト
        return redirect()->route('books.index')->with('success', '書籍を登録しました');
    } 

    // スキャン
    public function fetchFromISBN(Request $request)
    {
        $isbn = $request->isbn;

        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=isbn:" . $isbn);

        if ($response->successful() && isset($response['items'][0])) {
            $book = $response['items'][0]['volumeInfo'];

            return response()->json([
                'title' => $book['title'] ?? '',
                'authors' => implode(', ', $book['authors'] ?? []),
                'thumbnail' => $book['imageLinks']['thumbnail'] ?? '',
            ]);
        }

        return response()->json(['error' => '書籍が見つかりませんでした'], 404);
    }
}
