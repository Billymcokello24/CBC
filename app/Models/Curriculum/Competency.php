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
}
