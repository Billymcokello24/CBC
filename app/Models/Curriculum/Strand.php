<?php

namespace App\Models\Curriculum;

use App\Models\Academic\GradeLevel;
use App\Models\Curriculum\SubStrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class Strand extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'subject_id',
        'grade_level_id',
        'name',
        'code',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function subStrands(): HasMany
    {
        return $this->hasMany(SubStrand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
