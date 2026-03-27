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
        // Only add global scope if not in a tenant-isolated context (where databases are already separate)
        // OR kept for additional safety if multiple schools exist in one tenant (unlikely here)
        static::addGlobalScope(new SchoolScope());

        static::creating(function (Model $model) {
            // First Priority: Tenancy context
            if (function_exists('tenancy') && tenancy()->initialized) {
                // Get school_id from tenant data if available
                $schoolId = tenancy()->tenant->school_id ?? null;
                if ($schoolId && !$model->school_id) {
                    $model->school_id = $schoolId;
                    return;
                }
            }

            // Fallback to Auth/Session context
            if (auth()->check()) {
                if (!auth()->user()->hasRole('super_admin')) {
                    $model->school_id = auth()->user()->school_id;
                } elseif (!$model->school_id && session('viewing_school_id')) {
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
