<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeDescriptor extends Model
{
    protected $fillable = [
        'grading_scale_id', 'level_code', 'level_name', 'description',
        'min_score', 'max_score', 'points', 'color', 'display_order'
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function scale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class, 'grading_scale_id');
    }

    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentAssessment::class, 'grade_descriptor_id');
    }
}
