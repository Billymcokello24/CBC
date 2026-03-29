<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\School;

class Competency extends Model
{
    protected $fillable = [
        'school_id', 'name', 'code', 'description', 'category', 'display_order', 'is_active'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('school_or_global', function (Builder $builder) {
            if (Auth::check() && !Auth::user()->hasRole('super_admin')) {
                $builder->where(function ($query) {
                    $query->where('school_id', Auth::user()->school_id)
                          ->orWhereNull('school_id');
                });
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && !Auth::user()->hasRole('super_admin')) {
                $model->school_id = Auth::user()->school_id;
            }
        });
    }

    protected $casts = ['is_active' => 'boolean'];

    public function indicators(): HasMany
    {
        return $this->hasMany(CompetencyIndicator::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCore($query)
    {
        return $query->where('category', 'core');
    }

    public function assessmentItems(): HasMany
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentItem::class);
    }

    /**
     * Derives the competency level for a student based on continuous assessment items.
     */
    public function getDerivedLevelForStudent(int $studentId, int $academicYearId = null): ?string
    {
        $query = \App\Models\Assessment\StudentAssessment::query()
            ->join('assessment_items', 'student_assessments.assessment_id', '=', 'assessment_items.assessment_id')
            ->where('assessment_items.competency_id', $this->id)
            ->where('student_assessments.student_id', $studentId);

        if ($academicYearId) {
            $query->join('assessments', 'student_assessments.assessment_id', '=', 'assessments.id')
                  ->where('assessments.academic_year_id', $academicYearId);
        }

        // Get all ratings (EE, ME, AE, BE)
        $ratings = $query->pluck('grade_level')
            ->filter()
            ->values();

        if ($ratings->isEmpty()) return null;

        // Calculate Mode (Most frequent rating)
        $counts = array_count_values($ratings->toArray());
        arsort($counts);
        
        return key($counts); // Returns the first (most frequent) key
    }
}
