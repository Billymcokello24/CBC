<?php

namespace App\Models\Academic;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicTerm extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id', 'academic_year_id', 'name', 'term_number', 'start_date',
        'end_date', 'is_current', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function makeCurrent(): void
    {
        static::where('school_id', $this->school_id)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }
}
