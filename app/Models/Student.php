<?php

namespace App\Models;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\SchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'school_id', 'admission_number', 'upi', 'first_name', 'middle_name', 'last_name',
        'gender', 'date_of_birth', 'birth_certificate_number', 'nationality', 'religion',
        'home_address', 'county', 'sub_county', 'ward', 'photo', 'admission_date',
        'admission_class_id', 'current_class_id', 'blood_group', 'medical_conditions', 'allergies',
        'has_special_needs', 'special_needs_details', 'primary_language', 'secondary_language',
        'boarding_status', 'hostel_room', 'transport_route', 'pickup_point', 'status',
        'graduation_date', 'withdrawal_date', 'withdrawal_reason'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'graduation_date' => 'date',
        'withdrawal_date' => 'date',
        'has_special_needs' => 'boolean',
    ];

    protected $appends = ['full_name', 'age'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'status', 'current_class_id'])
            ->logOnlyDirty();
    }

    protected static function booted(): void
    {
        static::deleting(function (Student $student) {
            // Hard delete related records in one go
            $student->enrollments()->delete();
            $student->previousSchools()->delete();
            $student->statusHistory()->delete();
            $student->attendance()->delete();
            $student->fees()->delete();
            
            // For models with SoftDeletes, use forceDelete to completely remove
            $student->documents()->forceDelete();
            
            // Detach relationships
            $student->guardians()->detach();
            $student->siblings()->detach();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function admissionClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'admission_class_id');
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot([
                'relationship', 'is_primary_contact', 'is_emergency_contact',
                'can_pickup', 'receives_reports', 'receives_fees_notification', 'is_fee_payer'
            ])
            ->withTimestamps();
    }

    public function primaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary_contact', true)->first();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function siblings(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_siblings', 'student_id', 'sibling_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function previousSchools(): HasMany
    {
        return $this->hasMany(StudentPreviousSchool::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(\App\Models\Attendance\StudentAttendance::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\StudentFee::class);
    }

    public function assessmentResults(): HasMany
    {
        return $this->hasMany(\App\Models\Assessment\StudentAssessment::class, 'student_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInClass($query, $classId)
    {
        return $query->where('current_class_id', $classId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('admission_number', 'like', "%{$search}%")
              ->orWhere('upi', 'like', "%{$search}%");
        });
    }
}
