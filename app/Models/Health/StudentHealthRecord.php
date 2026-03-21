<?php

namespace App\Models\Health;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'blood_group',
        'genotype',
        'height_cm',
        'weight_kg',
        'vision_status',
        'hearing_status',
        'dental_status',
        'chronic_conditions',
        'allergies',
        'dietary_restrictions',
        'medications',
        'doctor_name',
        'doctor_phone',
        'hospital_name',
        'hospital_phone',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_expiry',
        'special_instructions',
        'last_physical_exam',
        'last_updated_at',
        'updated_by',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
        'last_physical_exam' => 'date',
        'last_updated_at' => 'datetime',
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
