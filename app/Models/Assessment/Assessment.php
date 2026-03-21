<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'class_id', 'subject_id', 'teacher_id',
        'academic_year_id', 'academic_term_id', 'assessment_type_id',
        'grading_scale_id', 'rubric_id', 'title', 'description',
        'instructions', 'assessment_date', 'start_time', 'end_time',
        'duration_minutes', 'total_marks', 'passing_marks', 'weight',
        'is_published', 'is_locked', 'status', 'published_at', 'created_by'
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'total_marks' => 'float',
        'passing_marks' => 'float',
        'weight' => 'float',
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }
    
    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\Subject::class);
    }

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }
}
