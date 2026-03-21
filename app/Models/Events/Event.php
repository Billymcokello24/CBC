<?php

namespace App\Models\Events;

use App\Models\School;
use App\Models\User;
use App\Models\Academic\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'event_type_id',
        'academic_year_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'venue',
        'address',
        'organizer',
        'contact_person',
        'contact_phone',
        'max_participants',
        'current_participants',
        'registration_fee',
        'requires_registration',
        'status',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_all_day' => 'boolean',
        'requires_registration' => 'boolean',
        'is_featured' => 'boolean',
        'registration_fee' => 'decimal:2',
        'max_participants' => 'integer',
        'current_participants' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
