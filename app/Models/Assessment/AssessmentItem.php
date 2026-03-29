<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentItem extends Model
{
    protected $fillable = [
        'assessment_id',
        'competency_indicator_id',
        'total_marks',
        'weight',
        'max_score',
        'display_order'
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Curriculum\CompetencyIndicator::class, 'competency_indicator_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(StudentAssessmentRating::class);
    }
}
