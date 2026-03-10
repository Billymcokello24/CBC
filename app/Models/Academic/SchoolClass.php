<?php

namespace App\Models\Academic;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'school_id', 'grade_level_id', 'stream_id', 'academic_year_id',
        'name', 'code', 'class_teacher_id', 'assistant_teacher_id',
        'room_number', 'capacity', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function assistantTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'current_class_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'class_id');
    }

    public function activeStudents()
    {
        return $this->students()->where('status', 'active');
    }

    public function getStudentCountAttribute(): int
    {
        return $this->activeStudents()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCurrentYear($query)
    {
        return $query->whereHas('academicYear', fn($q) => $q->where('is_current', true));
    }
}
