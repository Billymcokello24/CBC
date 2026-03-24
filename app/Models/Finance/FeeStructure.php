<?php

namespace App\Models\Finance;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeStructure extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'academic_year_id',
        'grade_level_id',
        'boarding_status',
        'tuition_fee',
        'boarding_fee',
        'lunch_fee',
        'transport_fee',
        'activity_fee',
        'exam_fee',
        'uniform_fee',
        'books_fee',
        'other_fee',
        'total_fee',
        'description',
        'is_active',
    ];

    protected $casts = [
        'tuition_fee' => 'decimal:2',
        'boarding_fee' => 'decimal:2',
        'lunch_fee' => 'decimal:2',
        'transport_fee' => 'decimal:2',
        'activity_fee' => 'decimal:2',
        'exam_fee' => 'decimal:2',
        'uniform_fee' => 'decimal:2',
        'books_fee' => 'decimal:2',
        'other_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }
}
