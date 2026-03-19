<?php

namespace App\Models\Academic;

use App\Models\School;
use App\Models\Curriculum\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSubject extends Model
{
    protected $fillable = [
        'school_id', 'subject_id', 'department_id', 'local_code', 'is_offered', 'notes'
    ];

    protected $casts = [
        'is_offered' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
