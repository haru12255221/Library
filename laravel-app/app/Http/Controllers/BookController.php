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

        try {
            // GoogleBooksServiceを使用
            $bookData = $this->googleBooksService->fetchByIsbn($isbn);

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

        } catch (\Exception $e) {
            Log::error("ISBN fetch error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'API通信エラーが発生しました。インターネット接続を確認してください。',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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

    // 書籍編集フォーム表示
    public function edit(Book $book)
    {
        Log::info("Book edit form accessed: {$book->title} (ID: {$book->id}) by user: " . auth()->id());
        return view('books.edit', compact('book'));
    }

    // 書籍情報更新
    public function update(Request $request, Book $book)
    {
        // バリデーション（編集時はISBNの重複チェックを現在の書籍を除外）
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|regex:/^[0-9\-X]+$/|unique:books,isbn,' . $book->id,
            'publisher' => 'nullable|max:255',
            'published_date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|max:2000',
            'thumbnail_url' => 'nullable|url',
        ]);

        try {
            // 書籍情報を更新
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'publisher' => $request->publisher,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'thumbnail_url' => $request->thumbnail_url,
            ]);

            Log::info("Book updated: {$book->title} (ID: {$book->id}) by user: " . auth()->id());

            return redirect()->route('admin.books.index')
                ->with('success', '書籍情報を更新しました');
        } catch (\Exception $e) {
            Log::error("Failed to update book: {$book->title} (ID: {$book->id}) by user: " . auth()->id() . " - Error: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', '書籍情報の更新に失敗しました。しばらく時間をおいて再度お試しください。');
        }
    }

    // 書籍削除
    public function destroy(Book $book)
    {
        // 貸出中の書籍は削除できない
        if (!$book->isAvailable()) {
            Log::warning("Attempted to delete borrowed book: {$book->title} (ID: {$book->id}) by user: " . auth()->id());
            return redirect()->back()
                ->with('error', '貸出中の書籍は削除できません');
        }

        try {
            $title = $book->title;
            $bookId = $book->id;
            $book->delete();

            Log::info("Book deleted: {$title} (ID: {$bookId}) by user: " . auth()->id());

            return redirect()->route('admin.books.index')
                ->with('success', "「{$title}」を削除しました");
        } catch (\Exception $e) {
            Log::error("Failed to delete book: {$book->title} (ID: {$book->id}) by user: " . auth()->id() . " - Error: " . $e->getMessage());
            return redirect()->back()
                ->with('error', '書籍の削除に失敗しました。しばらく時間をおいて再度お試しください。');
        }
    }
}
