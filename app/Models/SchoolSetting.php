<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class SchoolSetting extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id', 'key', 'value', 'type', 'group', 'description', 'is_public'
    ];

    protected $casts = ['is_public' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getValueAttribute($value)
    {
        return match($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }
}
