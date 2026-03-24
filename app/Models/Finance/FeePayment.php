<?php

namespace App\Models\Finance;

use App\Models\Student;
use App\Models\Academic\AcademicTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeePayment extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'student_fee_id',
        'academic_term_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'bank_name',
        'bank_branch',
        'cheque_number',
        'mpesa_code',
        'notes',
        'received_by',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function scopeThisTerm($query)
    {
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        if ($currentTerm) {
            return $query->where('academic_term_id', $currentTerm->id);
        }
        return $query;
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year);
    }
}
