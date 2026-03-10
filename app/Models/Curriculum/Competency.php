<?php
namespace App\Models\Curriculum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Competency extends Model
{
    protected $fillable = [
        'name', 'code', 'description', 'category', 'display_order', 'is_active'
    ];
    protected $casts = ['is_active' => 'boolean'];
    public function indicators(): HasMany
    {
        return $this->hasMany(CompetencyIndicator::class);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeCore($query)
    {
        return $query->where('category', 'core');
    }
}
