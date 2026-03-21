<?php

namespace App\Models\Library;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_copy_id',
        'library_card_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'condition_at_loan',
        'condition_at_return',
        'renewals',
        'max_renewals',
        'fine_amount',
        'fine_paid',
        'notes',
        'loaned_by',
        'returned_to',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'decimal:2',
        'renewals' => 'integer',
        'max_renewals' => 'integer',
    ];

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function loanedBy()
    {
        return $this->belongsTo(User::class, 'loaned_by');
    }

    public function returnedTo()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }
}
