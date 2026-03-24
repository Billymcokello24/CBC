<?php

namespace App\Models\Academic;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;


class GradeLevel extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'slug',
        'school_id',
        'name',
        'code',
        'level_order',
        'category',
        'minimum_age',
        'maximum_age',
        'description',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level_order');
    }
}
