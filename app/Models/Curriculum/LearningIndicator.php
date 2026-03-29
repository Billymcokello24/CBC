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
        'learning_outcome_id',
        'indicator',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
