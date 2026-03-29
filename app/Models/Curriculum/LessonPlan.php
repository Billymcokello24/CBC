<?php

namespace App\Models\Curriculum;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class LessonPlan extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'class_id',
        'teacher_id',
        'subject_id',
        'academic_term_id',
        'strand_id',
        'sub_strand_id',
        'teaching_plan_id',
        'title',
        'week_number',
        'lesson_date',
        'period_number',
        'duration_minutes',
        'specific_objectives',
        'learning_outcomes',
        'key_vocabulary',
        'teaching_aids',
        'references',
        'introduction',
        'lesson_development',
        'teacher_activities',
        'learner_activities',
        'conclusion',
        'assessment_methods',
        'reflection',
        'homework',
        'status',
        'approved_by',
        'approved_at',
        'feedback',
        'is_taught',
        'taught_at',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'approved_at' => 'datetime',
        'taught_at' => 'datetime',
        'is_taught' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(Competency::class, 'lesson_plan_competencies');
    }

    public function schemeOfWork(): BelongsTo
    {
        return $this->belongsTo(SchemeOfWork::class, 'teaching_plan_id');
    }
}
