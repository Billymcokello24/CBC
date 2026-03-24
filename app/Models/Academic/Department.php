<?php

namespace App\Models\Academic;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToSchool;

class Department extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'code', 'description', 'status'];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function headOfDepartment(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_of_department_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function subjects(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Curriculum\Subject::class,
            \App\Models\Academic\SchoolSubject::class,
            'department_id',
            'id',
            'id',
            'subject_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
