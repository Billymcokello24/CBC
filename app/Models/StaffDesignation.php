<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class StaffDesignation extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id', 'staff_category_id', 'name', 'code',
        'hierarchy_level', 'description', 'responsibilities', 'is_active'
    ];
    protected $casts = ['is_active' => 'boolean'];
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    public function staffCategory(): BelongsTo
    {
        return $this->belongsTo(StaffCategory::class);
    }
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
