<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    // 1. 貸出一覧（管理者用）
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'borrowed_at');
        $direction = $request->input('direction', 'desc');

        $sortable = ['borrowed_at', 'due_date', 'status'];
        if (!in_array($sort, $sortable)) {
            $sort = 'borrowed_at';
            $direction = 'desc';
        }

        $loans = Loan::with(['user', 'book'])
                    ->orderBy($sort, $direction)
                    ->get();

        return view('loans.index', compact('loans', 'sort', 'direction'));
    }

    // 2. 貸出処理（管理者のみ）
    public function borrow(Request $request)
    {
        // バリデーション
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // 既に貸出中かチェック
        $existingLoan = Loan::where('book_id', $request->book_id)
                            ->where('status', Loan::STATUS_BORROWED)
                            ->first();

        if ($existingLoan) {
            return redirect()->route('books.index')->with('error', 'この本は既に貸出中です');
        }

        // 貸出記録作成
        Loan::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED
        ]);

        return redirect()->route('books.index')->with('success', '本を貸し出しました！');
    }

    // 3. 返却処理（管理者のみ）
    public function returnBook(Loan $loan)
    {
        $loan->update([
            'returned_at' => now(),
            'status' => Loan::STATUS_RETURNED
        ]);

        return redirect()->route('admin.loans.index')->with('success', '返却処理が完了しました！');
    }

    public function myLoans(Request $request)
    {
        $query = Loan::with('book')
                    ->where('user_id', auth()->id())
                    ->where('status', Loan::STATUS_BORROWED);

        // 検索機能を追加
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('book', function ($bookQuery) use ($search) {
                $bookQuery->where('title', 'like', "%{$search}%")
                         ->orWhere('author', 'like', "%{$search}%")
                         ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $myLoans = $query->get();

        return view('loans.my-loans', compact('myLoans'));
    }
}
