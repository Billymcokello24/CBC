<?php

namespace App\Models\Assessment;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentType extends Model
{
    protected $fillable = [
        'school_id', 'name', 'code', 'category', 'description',
        'default_weight', 'requires_rubric', 'is_active'
    ];

    protected $casts = [
        'default_weight' => 'decimal:2',
        'requires_rubric' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
