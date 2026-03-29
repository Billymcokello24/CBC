<?php

namespace App\Models\Curriculum;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Assignment extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'class_id',
        'subject_id',
        'strand_id',
        'sub_strand_id',
        'teacher_id',
        'academic_term_id',
        'lesson_id',
        'title',
        'description',
        'instructions',
        'assignment_type',
        'assigned_date',
        'due_date',
        'total_marks',
        'passing_marks',
        'allow_late_submission',
        'late_submission_deadline',
        'late_penalty_percentage',
        'max_attempts',
        'is_group_assignment',
        'group_size_min',
        'group_size_max',
        'is_published',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'datetime',
        'late_submission_deadline' => 'datetime',
        'published_at' => 'datetime',
        'allow_late_submission' => 'boolean',
        'is_group_assignment' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }
}
