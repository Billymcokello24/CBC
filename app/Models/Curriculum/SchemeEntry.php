<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToSchool;

class SchemeEntry extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'scheme_id',
        'week_number',
        'lesson_number',
        'strand_id',
        'sub_strand_id',
        'topic',
        'duration_minutes',
        'lesson_date',
        'key_vocabulary',
        'learning_outcomes',
        'learning_activities',
        'teacher_activities',
        'introduction',
        'lesson_development',
        'conclusion',
        'resources',
        'references',
        'assessment',
        'remarks',
        'reflection',
        'homework',
        'core_competencies',
        'pci',
        'inquiry_questions',
    ];

    protected $casts = [
        'core_competencies' => 'array',
        'pci' => 'array',
        'lesson_date' => 'date',
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_id');
    }

    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function lessonPlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LessonPlan::class);
    }
}
