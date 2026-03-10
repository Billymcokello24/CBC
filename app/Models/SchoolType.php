<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
