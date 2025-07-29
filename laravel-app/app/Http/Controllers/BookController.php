<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\GoogleBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    protected $googleBooksService;

    public function __construct(GoogleBooksService $googleBooksService)
    {
        $this->googleBooksService = $googleBooksService;
        

    }
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
        // 拡張バリデーション
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|unique:books|regex:/^[0-9\-X]+$/',
            'publisher' => 'nullable|max:255',
            'published_date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|max:2000',
            'thumbnail_url' => 'nullable|url',
        ]);

        // データベースに保存（拡張フィールド対応）
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'publisher' => $request->publisher,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        Log::info("Book registered: {$book->title} (ID: {$book->id})");

        // 一覧画面にリダイレクト
        return redirect()->route('books.index')->with('success', '書籍を登録しました');
    } 

    // ISBN検索（拡張版）
    public function fetchFromISBN(Request $request)
    {
        $request->validate([
            'isbn' => 'required|string'
        ]);

        $isbn = $request->isbn;
        Log::info("Fetching book info for ISBN: {$isbn}");

        // GoogleBooksServiceを使用
        $bookData = $this->googleBooksService->fetchByIsbn($isbn);
        dd($bookData);

        if ($bookData) {
            Log::info("Book found: {$bookData['title']}");
            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $bookData['title'],
                    'author' => $bookData['authors'],
                    'publisher' => $bookData['publisher'],
                    'published_date' => $bookData['published_date'],
                    'description' => $bookData['description'],
                    'thumbnail_url' => $bookData['thumbnail_url'],
                    'page_count' => $bookData['page_count'],
                    'language' => $bookData['language'],
                ]
            ]);
        }

        Log::warning("No book found for ISBN: {$isbn}");
        return response()->json([
            'success' => false,
            'error' => '書籍が見つかりませんでした。手動で入力してください。'
        ], 404);
    }

    // 書籍詳細表示
    public function show(Book $book)
    {
        // 関連書籍を取得（同じ著者の他の書籍、最大6冊）
        $relatedBooks = Book::where('author', $book->author)
            ->where('id', '!=', $book->id)
            ->limit(6)
            ->get();

        return view('books.show', compact('book', 'relatedBooks'));
    }


}
