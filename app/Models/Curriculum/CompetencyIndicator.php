<?php

namespace App\Models\Curriculum;

use App\Models\Academic\GradeLevel;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyIndicator extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'competency_id',
        'grade_level_id',
        'indicator',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }
}
