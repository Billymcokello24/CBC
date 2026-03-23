<?php

namespace App\Models\Health;

use App\Models\Student;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class MedicalVisit extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'student_id',
        'school_id',
        'visit_datetime',
        'visit_type',
        'chief_complaint',
        'symptoms',
        'vital_signs',
        'examination_findings',
        'diagnosis',
        'treatment_given',
        'medications_prescribed',
        'instructions',
        'referred_to_hospital',
        'referral_hospital',
        'referral_reason',
        'parent_notified',
        'parent_notified_at',
        'parent_pickup_required',
        'parent_pickup_at',
        'follow_up_date',
        'follow_up_notes',
        'attended_by',
    ];

    protected $casts = [
        'visit_datetime' => 'datetime',
        'parent_notified_at' => 'datetime',
        'parent_pickup_at' => 'datetime',
        'follow_up_date' => 'date',
        'referred_to_hospital' => 'boolean',
        'parent_notified' => 'boolean',
        'parent_pickup_required' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function attendee()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}
