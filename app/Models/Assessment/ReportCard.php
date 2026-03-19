<?php

namespace App\Models\Assessment;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'class_id', 'academic_year_id', 'academic_term_id',
        'total_subjects', 'average_score', 'overall_grade', 'class_rank',
        'stream_rank', 'total_students_in_class', 'class_teacher_comments',
        'principal_comments', 'parent_comments', 'days_present',
        'days_absent', 'total_school_days', 'conduct_grade', 'effort_grade',
        'status', 'is_published', 'published_at', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'average_score' => 'decimal:2',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
