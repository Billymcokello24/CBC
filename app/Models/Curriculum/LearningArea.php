<?php
namespace App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class LearningArea extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name', 'code', 'description', 'category', 'display_order', 'is_active'
    ];
    protected $casts = ['is_active' => 'boolean'];
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
