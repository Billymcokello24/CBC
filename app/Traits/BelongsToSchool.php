<?php

namespace App\Traits;

use App\Models\School;
use App\Models\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function (Model $model) {
            if (auth()->check() && ! $model->school_id) {
                // If the user has a school_id, use it
                if (auth()->user()->school_id) {
                    $model->school_id = auth()->user()->school_id;
                } 
                // If it's a super_admin, they might be viewing/managing a specific school
                elseif (auth()->user()->hasRole('super_admin') && session('viewing_school_id')) {
                    $model->school_id = session('viewing_school_id');
                }
            }
        });
    }

    /**
     * Get the school that owns the model.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
