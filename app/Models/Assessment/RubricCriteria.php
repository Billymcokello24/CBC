<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricCriteria extends Model
{
    protected $table = 'rubric_criteria';

    protected $fillable = [
        'rubric_id', 'criterion_name', 'description', 'max_points',
        'weight', 'display_order'
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(RubricLevel::class, 'rubric_criteria_id');
    }
}
