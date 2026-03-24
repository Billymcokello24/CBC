<?php

namespace App\Models\Assessment;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToSchool;

class StudentAssessment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id', 'assessment_id', 'marks_obtained', 'percentage',
        'grade_descriptor_id', 'grade_level', 'feedback', 'teacher_comments',
        'improvement_areas', 'is_absent', 'absence_reason', 'is_excused',
        'submitted_at', 'graded_at', 'graded_by'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_absent' => 'boolean',
        'is_excused' => 'boolean',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function descriptor(): BelongsTo
    {
        return $this->belongsTo(GradeDescriptor::class, 'grade_descriptor_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
