<?php

namespace App\Models;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Department;
use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class School extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'registration_number',
        'school_type_id',
        'school_level_id',
        'logo',
        'motto',
        'vision',
        'mission',
        'email',
        'phone',
        'alternate_phone',
        'website',
        'address',
        'postal_code',
        'county',
        'sub_county',
        'ward',
        'latitude',
        'longitude',
        'established_date',
        'gender_type',
        'boarding_type',
        'student_capacity',
        'status',
        'timezone',
        'currency',
        'locale',
    ];

    protected $casts = [
        'established_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'email', 'phone'])
            ->logOnlyDirty();
    }

    public function schoolType(): BelongsTo
    {
        return $this->belongsTo(SchoolType::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(SchoolBranch::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(SchoolSetting::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SchoolDocument::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SchoolContact::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function currentAcademicYear()
    {
        return $this->academicYears()->where('is_current', true)->first();
    }

    public function academicTerms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class);
    }

    public function currentTerm()
    {
        return $this->academicTerms()->where('is_current', true)->first();
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function gradeLevels(): HasMany
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
