<?php

namespace App\Models\Academic;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added HasFactory import

use App\Traits\BelongsToSchool;

use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableTemplate extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'description', 'template_type',
        'is_default', 'is_active', 'created_by'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }
}
