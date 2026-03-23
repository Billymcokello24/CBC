<?php

namespace App\Models\Hostel;

use App\Models\Student;
use App\Models\Academic\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class HostelAllocation extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'hostel_id',
        'room_id',
        'bed_id',
        'academic_year_id',
        'allocation_date',
        'end_date',
        'status',
        'notes',
        'allocated_by',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}
