<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
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
        return redirect()->route('dashboard')->with('success', '書籍を登録しました');
    } 
}
