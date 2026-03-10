<?php

namespace App\Models\Academic;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'code', 'description', 'head_of_department_id', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function headOfDepartment(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_of_department_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
