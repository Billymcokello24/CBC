<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class SchoolBranch extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'email', 'phone', 'address',
        'county', 'sub_county', 'latitude', 'longitude', 'is_main_branch', 'is_active'
    ];

    protected $casts = [
        'is_main_branch' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
