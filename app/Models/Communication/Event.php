<?php

namespace App\Models\Communication;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToSchool;

class Event extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'is_all_day',
        'is_recurring',
        'recurrence_pattern',
        'target_audience',
        'is_mandatory',
        'max_participants',
        'registration_deadline',
        'status',
        'created_by',
        'attachments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'is_mandatory' => 'boolean',
        'attachments' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year);
    }
}
