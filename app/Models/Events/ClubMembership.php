<?php

namespace App\Models\Events;

use App\Models\Student;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'club_id',
        'academic_year_id',
        'join_date',
        'status',
        'role',
        'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
