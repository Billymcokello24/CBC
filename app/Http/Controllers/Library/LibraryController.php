<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\Book;
use App\Models\Library\BookCategory;
use App\Models\Library\BookLoan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_books' => Book::where('school_id', $schoolId)->sum('total_copies'),
            'available_books' => Book::where('school_id', $schoolId)->sum('available_copies'),
            'active_loans' => BookLoan::whereHas('bookCopy.book', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->where('status', 'loaned')->count(),
            'overdue_loans' => BookLoan::whereHas('bookCopy.book', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->where('status', 'loaned')->where('due_date', '<', now())->count(),
        ];

        $recentLoans = BookLoan::with(['bookCopy.book', 'loanedBy'])
            ->whereHas('bookCopy.book', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('loan_date', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('library/Index', [
            'stats' => $stats,
            'recentLoans' => $recentLoans,
        ]);
    }

    public function books(): Response
    {
        $schoolId = auth()->user()->school_id;
        $books = Book::where('school_id', $schoolId)
            ->with(['category', 'subject'])
            ->orderBy('title')
            ->paginate(12);

        $categories = BookCategory::where('school_id', $schoolId)->get();

        return Inertia::render('library/Books', [
            'books' => $books,
            'categories' => $categories,
        ]);
    }

    public function loans(): Response
    {
        $schoolId = auth()->user()->school_id;
        $loans = BookLoan::with(['bookCopy.book', 'loanedBy'])
            ->whereHas('bookCopy.book', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->orderBy('loan_date', 'desc')
            ->paginate(15);

        return Inertia::render('library/Loans', [
            'loans' => $loans,
        ]);
    }
}
