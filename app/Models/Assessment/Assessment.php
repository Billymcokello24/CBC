<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Traits\BelongsToSchool;

class Assessment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'class_id', 'subject_id', 'teacher_id',
        'academic_year_id', 'academic_term_id', 'assessment_type_id',
        'grading_scale_id', 'rubric_id', 'title', 'description',
        'instructions', 'assessment_date', 'start_time', 'end_time',
        'duration_minutes', 'total_marks', 'passing_marks', 'weight',
        'is_published', 'is_locked', 'status', 'published_at', 'created_by',
        'lesson_plan_id', 'sub_strand_id',
        'core_competencies',
        'pci',
        'indicators',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'core_competencies' => 'array',
        'pci' => 'array',
        'indicators' => 'array',
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

    public function items(): HasMany
    {
        return $this->hasMany(AssessmentItem::class);
    }

    public function studentRatings(): HasManyThrough
    {
        return $this->hasManyThrough(StudentAssessmentRating::class, AssessmentItem::class);
    }

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\LessonPlan::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\SubStrand::class);
    }
}
