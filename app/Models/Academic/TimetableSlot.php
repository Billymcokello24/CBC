<?php

namespace App\Models\Academic;

use App\Models\Curriculum\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableSlot extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'timetable_id', 'subject_id', 'teacher_id', 'period_definition_id',
        'day_of_week', 'period_number', 'start_time', 'end_time',
        'room_number', 'activity_type', 'notes', 'is_double_period'
    ];

    protected $casts = [
        'is_double_period' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function periodDefinition(): BelongsTo
    {
        return $this->belongsTo(PeriodDefinition::class);
    }
}
