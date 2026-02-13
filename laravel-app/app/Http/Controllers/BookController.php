<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    protected $bookSearchService;

    public function __construct(BookSearchService $bookSearchService)
    {
        $this->bookSearchService = $bookSearchService;
    }
    // Requestはクラス名、indexはメソッド名
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Book::query();

        if ($search) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'like', "%{$escaped}%")
                    ->orWhere('author', 'like', "%{$escaped}%");
            });
        }

        $books = $query->with('currentLoan')->paginate(20);

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
        // バリデーション（ISBNのユニーク制約は削除 → 複数冊対応）
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|regex:/^[0-9\-X]+$/',
            'publisher' => 'nullable|max:255',
            'published_date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|max:2000',
            'thumbnail_url' => 'nullable|url',
        ]);

        // 同じISBNの最大copy_numberを取得して+1
        $maxCopy = Book::where('isbn', $request->isbn)->max('copy_number') ?? 0;
        $copyNumber = $maxCopy + 1;

        // データベースに保存
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'copy_number' => $copyNumber,
            'publisher' => $request->publisher,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        $copyLabel = $copyNumber > 1 ? "（冊{$copyNumber}）" : '';
        Log::info("Book registered: {$book->title}{$copyLabel} (ID: {$book->id})");

        $message = $copyNumber > 1
            ? "書籍を登録しました（{$copyNumber}冊目）"
            : '書籍を登録しました';

        return redirect()->route('books.index')->with('success', $message);
    } 

    // ISBN検索（拡張版）
    public function fetchFromISBN(Request $request)
    {
        $request->validate([
            'isbn' => 'required|string'
        ]);

        $isbn = $request->isbn;
        Log::info("Fetching book info for ISBN: {$isbn}");

        $bookData = $this->bookSearchService->fetchByIsbn($isbn);

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

    // 書籍編集フォーム
    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    // 書籍更新処理
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|regex:/^[0-9\-X]+$/',
            'publisher' => 'nullable|max:255',
            'published_date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|max:2000',
            'thumbnail_url' => 'nullable|url',
        ]);

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'publisher' => $request->publisher,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        Log::info("Book updated: {$book->title} (ID: {$book->id})");

        return redirect()->route('books.show', $book)->with('success', '書籍情報を更新しました');
    }

    // 書籍削除処理
    public function destroy(Book $book)
    {
        if (!$book->isAvailable()) {
            return back()->with('error', '貸出中の書籍は削除できません');
        }

        $title = $book->title;
        $book->delete();

        Log::info("Book deleted: {$title} (ID: {$book->id})");

        return redirect()->route('books.index')->with('success', "「{$title}」を削除しました");
    }
}
