<?php

namespace App\Models;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\SchoolClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'academic_year_id', 'academic_term_id',
        'enrollment_date', 'end_date', 'enrollment_type', 'status',
        'roll_number', 'notes', 'enrolled_by'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'end_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
