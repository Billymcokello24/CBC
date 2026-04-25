<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class ResourceFolder extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'name',
        'school_id',
        'subject_id',
        'grade_level_id',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(CurriculumResource::class, 'folder_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\GradeLevel::class, 'grade_level_id');
    }
}
