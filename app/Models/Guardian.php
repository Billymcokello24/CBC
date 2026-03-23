<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Guardian extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'user_id', 'school_id', 'first_name', 'middle_name', 'last_name', 'id_number',
        'gender', 'date_of_birth', 'email', 'phone', 'alternate_phone', 'occupation',
        'employer', 'work_address', 'home_address', 'county', 'sub_county',
        'relationship_type', 'photo', 'is_emergency_contact', 'can_pickup',
        'receives_communication', 'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_emergency_contact' => 'boolean',
        'can_pickup' => 'boolean',
        'receives_communication' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot([
                'relationship', 'is_primary_contact', 'is_emergency_contact',
                'can_pickup', 'receives_reports', 'receives_fees_notification', 'is_fee_payer'
            ])
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('id_number', 'like', "%{$search}%");
        });
    }
}
