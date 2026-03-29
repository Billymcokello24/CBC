<?php

namespace App\Models\Assessment;

use App\Models\Curriculum\Competency;
use App\Models\Curriculum\LearningIndicator;
use App\Models\Curriculum\SubStrand;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentItem extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'assessment_id',
        'sub_strand_id',
        'performance_indicator_id',
        'competency_id',
        'max_score',
        'display_order',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function performanceIndicator(): BelongsTo
    {
        return $this->belongsTo(LearningIndicator::class, 'performance_indicator_id');
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(StudentEvidence::class);
    }
}
