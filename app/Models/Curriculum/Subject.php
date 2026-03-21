<?php
namespace App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Subject extends Model
{
    protected $fillable = [
        'learning_area_id', 'department_id', 'name', 'code', 'description',
        'subject_type', 'is_examinable', 'display_order', 'is_active'
    ];
    protected $casts = [
        'is_examinable' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function learningArea(): BelongsTo
    {
        return $this->belongsTo(LearningArea::class);
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Department::class);
    }
    public function strands(): HasMany
    {
        return $this->hasMany(Strand::class);
    }
    public function schoolSubjects(): HasMany
    {
        return $this->hasMany(\App\Models\Academic\SchoolSubject::class);
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(\App\Models\Academic\TimetableSlot::class);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeCore($query)
    {
        return $query->where('subject_type', 'core');
    }
}
