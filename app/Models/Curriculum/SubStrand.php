<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class SubStrand extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'strand_id',
        'name',
        'code',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(LearningOutcome::class);
    }

    public function assessmentItems(): HasMany
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentItem::class);
    }
}
