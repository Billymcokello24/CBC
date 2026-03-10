<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffCategory extends Model
{
    protected $fillable = ['school_id', 'name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(StaffDesignation::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
