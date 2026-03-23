<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class SchoolContact extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id', 'contact_type', 'name', 'title', 'email', 'phone', 'is_primary'
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
