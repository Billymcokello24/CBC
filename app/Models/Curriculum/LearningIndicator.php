<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class LearningIndicator extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'learning_outcome_id',
        'indicator',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function learningOutcome(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function assessmentItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentItem::class, 'performance_indicator_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
