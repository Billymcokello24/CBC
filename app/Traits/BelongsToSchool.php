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
     * The "boot" method of the model.
     */
    protected static function bootBelongsToSchool()
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function (Model $model) {
            if (auth()->check()) {
                if (!auth()->user()->hasRole('super_admin')) {
                    // Regular users must always use their own school_id
                    $model->school_id = auth()->user()->school_id;
                } elseif (!$model->school_id && session('viewing_school_id')) {
                    // Super admins use session school_id if not explicitly providing one
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
