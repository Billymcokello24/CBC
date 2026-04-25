<?php

namespace App\Models;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Department;
use App\Models\Academic\SchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Traits\BelongsToSchool;

class Teacher extends Model
{
    use HasFactory, LogsActivity, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'staff_number',
        'department_id', 'staff_category_id', 'staff_designation_id',
        'tsc_number', 'first_name', 'middle_name', 'last_name', 'gender',
        'date_of_birth', 'id_number', 'nationality', 'email', 'phone', 'alternate_phone',
        'address', 'county', 'sub_county', 'photo', 'date_joined', 'date_left',
        'contract_type', 'employment_type', 'marital_status', 'blood_group',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'bank_name', 'bank_account_number', 'bank_branch', 'basic_salary',
        'nhif_number', 'nssf_number', 'kra_pin', 'status', 'notes'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'date_left' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    protected $appends = ['full_name', 'photo_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'status', 'department_id'])
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function staffCategory(): BelongsTo
    {
        return $this->belongsTo(StaffCategory::class);
    }

    public function staffDesignation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(TeacherQualification::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(TeacherCertification::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(TeacherAppraisal::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(TeacherTraining::class);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function classesAsTeacher(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id', 'user_id');
    }

    public function assignedClasses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_subjects', 'teacher_id', 'class_id')
            ->wherePivot('is_active', true);
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(\App\Models\Academic\TimetableSlot::class, 'teacher_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('staff_number', 'like', "%{$search}%")
              ->orWhere('tsc_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
