<?php

namespace App\Models\Finance;

use App\Models\Student;
use App\Models\Academic\AcademicTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class StudentFee extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_structure_id',
        'academic_term_id',
        'total_amount',
        'paid_amount',
        'balance',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partial']);
    }
}
