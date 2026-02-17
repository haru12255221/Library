<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // バリデーション（ソフトデリート済みの書籍を除外）
        $request->validate([
            'book_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('books', 'id')->whereNull('deleted_at'),
            ],
        ]);

        return DB::transaction(function () use ($request) {
            // 既に貸出中かチェック（排他ロック）
            $existingLoan = Loan::where('book_id', $request->book_id)
                                ->where('status', Loan::STATUS_BORROWED)
                                ->lockForUpdate()
                                ->first();

            if ($existingLoan) {
                return redirect()->route('books.index')->with('error', 'この本は既に貸出中です');
            }

            // 貸出記録作成
            $loan = Loan::create([
                'user_id' => auth()->id(),
                'book_id' => $request->book_id,
                'borrowed_at' => now(),
                'due_date' => now()->addDays(14), // 14日後
                'status' => Loan::STATUS_BORROWED
            ]);

            AuditLog::log('book_borrowed', $loan, "書籍ID: {$request->book_id}");

            return redirect()->route('books.index')->with('success', '本を借りました！');
        });
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

        AuditLog::log('book_returned', $loan, "書籍ID: {$loan->book_id}");

        return redirect()->route('loans.my')->with('success', '本を返却しました！');
    }

    // 強制返却（管理者用）
    public function forceReturn(Loan $loan)
    {
        if ($loan->returned_at) {
            return back()->with('error', 'この貸出は既に返却済みです');
        }

        $loan->update([
            'returned_at' => now(),
            'status' => Loan::STATUS_RETURNED,
        ]);

        AuditLog::log('book_force_returned', $loan, "書籍: {$loan->book->title}, 借主: {$loan->user->name}");

        return back()->with('success', "「{$loan->book->title}」を強制返却しました（借主: {$loan->user->name}）");
    }

    // 延滞一覧（管理者用）
    public function overdue()
    {
        $overdueLoans = Loan::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now()->startOfDay())
            ->orderBy('due_date', 'asc')
            ->get();

        return view('loans.overdue', compact('overdueLoans'));
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
