<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class StudentPreviousSchool extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id', 'school_name', 'school_address', 'school_phone',
        'last_class_attended', 'from_date', 'to_date', 'reason_for_leaving',
        'notes', 'has_transfer_certificate'
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'has_transfer_certificate' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
