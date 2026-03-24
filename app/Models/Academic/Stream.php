<?php

namespace App\Models\Academic;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToSchool;

class Stream extends Model
{
    use BelongsToSchool;

    protected $fillable = ['slug', 'school_id', 'name', 'code', 'capacity', 'is_active', 'status'];

    protected $casts = ['is_active' => 'boolean'];

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
}
