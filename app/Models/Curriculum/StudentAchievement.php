<?php

namespace App\Models\Curriculum;

use App\Models\Academic\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class StudentAchievement extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'achievement_type',
        'title',
        'description',
        'level',
        'position',
        'achievement_date',
        'certificate_file',
        'photo',
        'remarks',
        'verified_by',
        'is_verified',
    ];

    protected $casts = [
        'achievement_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
