<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
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

    // 2. 貸出処理
    public function borrow(Request $request)
    {
        // バリデーション
        $request->validate([
            'book_id' => 'required|exists:books,id'
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
            'user_id' => auth()->id(),
            'book_id' => $request->book_id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14), // 14日後
            'status' => Loan::STATUS_BORROWED
        ]);

        return redirect()->route('books.index')->with('success', '本を借りました！');
    }

    // 3. 返却処理
    public function returnBook(Loan $loan)
    {
        // 自分の貸出記録かチェック
        if ($loan->user_id !== auth()->id()) {
            return redirect()->route('loans.my')
            ->with('error', '他人の貸出記録は操作できません');
        }

        // 返却処理
        $loan->update([
            'returned_at' => now(),
            'status' => Loan::STATUS_RETURNED
        ]);

        return redirect()->route('loans.my')->with('success', '本を返却しました！');
    }

    public function myLoans()
    {
        $myLoans = Loan::with('book')
                    ->where('user_id', auth()->id())
                    ->where('status', Loan::STATUS_BORROWED)
                    ->get();

        return view('loans.my-loans', compact('myLoans'));
    }
}
