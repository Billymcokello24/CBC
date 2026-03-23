<?php

namespace App\Models\Transport;

use App\Models\Student;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class StudentTransport extends Model
{
    use HasFactory, BelongsToSchool;

    protected $table = 'student_transport';

    protected $fillable = [
        'school_id',
        'student_id',
        'route_id',
        'pickup_stop_id',
        'dropoff_stop_id',
        'academic_year_id',
        'transport_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function pickupStop()
    {
        return $this->belongsTo(RouteStop::class, 'pickup_stop_id');
    }

    public function dropoffStop()
    {
        return $this->belongsTo(RouteStop::class, 'dropoff_stop_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
