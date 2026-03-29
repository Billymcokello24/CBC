<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCompetencyRating extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'competency_id',
        'subject_id',
        'academic_year_id',
        'academic_term_id',
        'rating_level',
        'score',
        'feedback',
        'teacher_id'
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\Competency::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }
}
