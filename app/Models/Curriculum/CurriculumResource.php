<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Traits\BelongsToSchool;

class CurriculumResource extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'resourceable_type',
        'resourceable_id',
        'grade_level_id',
        'folder_id',
        'title',
        'resource_type',
        'description',
        'file_path',
        'url',
        'author',
        'publisher',
        'isbn',
        'year_published',
        'is_recommended',
        'is_active',
    ];

    protected $casts = [
        'is_recommended' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function folder()
    {
        return $this->belongsTo(ResourceFolder::class, 'folder_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'resourceable_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(\App\Models\Academic\GradeLevel::class, 'grade_level_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }
}
