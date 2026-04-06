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
        'scheme_entry_id',
        'title',
        'week_number',
        'lesson_date',
        'number_of_learners',
        'period_number',
        'duration_minutes',
        'specific_objectives',
        'learning_outcomes',
        'key_vocabulary',
        'teaching_aids',
        'references',
        'introduction',
        'lesson_development',
        'learning_activities',
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
        'core_competencies',
        'values',
        'life_skills',
        'pci',
        'inquiry_questions',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'approved_at' => 'datetime',
        'taught_at' => 'datetime',
        'is_taught' => 'boolean',
        'core_competencies' => 'array',
        'values' => 'array',
        'life_skills' => 'array',
        'learning_activities' => 'array',
        'pci' => 'array',
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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\AcademicYear::class);
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

    public function schemeOfWork()
    {
        return $this->hasOneThrough(
            SchemeOfWork::class,
            SchemeEntry::class,
            'id', // Local key on scheme_entries (matches scheme_entry_id)
            'id', // Local key on schemes_of_work (matches scheme_id on entries)
            'scheme_entry_id', // Foreign key on lesson_plans
            'scheme_id' // Foreign key on scheme_entries
        );
    }

    public function schemeEntry(): BelongsTo
    {
        return $this->belongsTo(SchemeEntry::class);
    }
}
