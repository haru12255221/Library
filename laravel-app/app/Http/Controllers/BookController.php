<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Requestはクラス名、indexはメソッド名
    public function index(Request $request)
    {
        $query = Book::with('loans');
        // 検索パラメータがある場合の処理
        if ($request->filled('search')) {
            // %はワイルドカードでなんでもok
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('author', 'like', '%' . $request->search . '%');
        }
        // &queryを取得
        $books = $query->get();

        return view('books.index', compact('books'));
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
}
