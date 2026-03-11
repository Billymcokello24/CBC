<?php

namespace App\Models\Attendance;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'academic_term_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
        'is_late',
        'late_minutes',
        'reason',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
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
