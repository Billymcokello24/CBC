<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricLevel extends Model
{
    protected $fillable = [
        'rubric_criteria_id', 'level_name', 'grade_code', 'description', 
        'points', 'min_score', 'max_score', 'display_order'
    ];

    protected $casts = [
        'points' => 'float',
        'min_score' => 'float',
        'max_score' => 'float',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriteria::class, 'rubric_criteria_id');
    }
}
