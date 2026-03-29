<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class LearningOutcome extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'sub_strand_id',
        'outcome',
        'code',
        'description',
        'outcome_type',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(Competency::class, 'learning_outcome_competencies');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(LearningIndicator::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
