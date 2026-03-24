<?php

namespace App\Models\Assessment;

use App\Models\Curriculum\Subject;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class Rubric extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'description', 'subject_id',
        'assessment_type_id', 'total_points', 'is_template',
        'is_active', 'created_by'
    ];

    protected $casts = [
        'total_points' => 'decimal:2',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriteria::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
