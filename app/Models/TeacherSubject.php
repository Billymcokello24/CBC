<?php

namespace App\Models;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\SchoolClass;
use App\Models\Curriculum\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'is_primary_teacher',
        'is_active',
    ];

    protected $casts = [
        'is_primary_teacher' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
