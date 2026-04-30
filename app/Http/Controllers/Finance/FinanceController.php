<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FeeCategory;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\Invoice;
use App\Models\Finance\StudentFee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $recentPayments = FeePayment::where('school_id', $schoolId)
            ->with('student:id,first_name,last_name')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'student' => $p->student ? $p->student->first_name . ' ' . $p->student->last_name : 'Unknown',
                'amount' => (float)$p->amount,
                'date' => $p->payment_date->format('Y-m-d'),
                'method' => $p->payment_method,
                'status' => 'completed', // Assuming all recorded payments are completed
            ]);

        $stats = [
            'total_collections' => (float)FeePayment::where('school_id', $schoolId)->sum('amount'),
            'pending_fees' => (float)Invoice::where('school_id', $schoolId)->where('status', 'pending')->sum('balance'),
            'today_collection' => (float)FeePayment::where('school_id', $schoolId)->whereDate('payment_date', now())->sum('amount'),
            'overdue' => (float)Invoice::where('school_id', $schoolId)->where('status', 'overdue')->sum('balance'),
        ];

        return Inertia::render('finance/Index', [
            'recentPayments' => $recentPayments,
            'stats' => $stats,
        ]);
    }

    public function feeStructures(): Response
    {
        $schoolId = auth()->user()->school_id;
        $structures = FeeStructure::where('is_active', true)
            ->with(['academicYear', 'gradeLevel'])
            ->get();

        return Inertia::render('finance/FeeStructures', [
            'structures' => $structures,
        ]);
    }

    public function invoices(): Response
    {
        $schoolId = auth()->user()->school_id;
        $invoices = Invoice::where('school_id', $schoolId)
            ->with(['student:id,first_name,last_name', 'academicTerm'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);

        return Inertia::render('finance/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function payments(): Response
    {
        $schoolId = auth()->user()->school_id;
        $payments = FeePayment::where('school_id', $schoolId)
            ->with(['student:id,first_name,last_name', 'invoice'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return Inertia::render('finance/Payments', [
            'payments' => $payments,
        ]);
    }

    public function studentFees(\App\Models\Student $student): Response
    {
        $student->load(['fees.academicTerm', 'currentClass']);

        $totalFees = (float)$student->fees()->sum('total_amount');
        $paidFees = (float)$student->fees()->sum('paid_amount');
        $balance = (float)$student->fees()->sum('balance');

        return Inertia::render('finance/StudentFees', [
            'learner' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->currentClass?->name,
            ],
            'fees' => $student->fees->map(fn($f) => [
                'id' => $f->id,
                'term' => $f->academicTerm?->name,
                'total' => (float)$f->total_amount,
                'paid' => (float)$f->paid_amount,
                'balance' => (float)$f->balance,
                'status' => $f->status,
                'due_date' => $f->due_date?->format('Y-m-d'),
            ]),
            'summary' => [
                'total' => $totalFees,
                'paid' => $paidFees,
                'balance' => $balance,
            ]
        ]);
    }
}
