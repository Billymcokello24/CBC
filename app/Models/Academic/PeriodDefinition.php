<?php

namespace App\Models\Academic;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodDefinition extends Model
{
    protected $fillable = [
        'school_id', 'name', 'period_number', 'start_time', 'end_time',
        'duration_minutes', 'period_type', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
