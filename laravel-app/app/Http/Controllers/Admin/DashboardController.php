<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBooks = Book::count();
        $loanedBooks = Loan::whereNull('returned_at')->count();
        $overdueBooks = Loan::whereNull('returned_at')
            ->where('due_date', '<', now()->startOfDay())
            ->count();
        $totalUsers = User::count();

        $recentLoans = Loan::with(['user', 'book'])
            ->latest('borrowed_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBooks',
            'loanedBooks',
            'overdueBooks',
            'totalUsers',
            'recentLoans'
        ));
    }
}
