<?php

namespace App\Models\Attendance;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class StudentAttendance extends Model
{
    use HasFactory, BelongsToSchool;

    protected $table = 'student_attendance';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'academic_year_id',
        'academic_term_id',
        'attendance_date',
        'status',
        'arrival_time',
        'departure_time',
        'absence_reason',
        'late_reason',
        'is_excused',
        'excused_by',
        'notes',
        'marked_by',
        'marked_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'is_excused' => 'boolean',
        'marked_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\Academic\AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(Teacher::class, 'marked_by');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}
