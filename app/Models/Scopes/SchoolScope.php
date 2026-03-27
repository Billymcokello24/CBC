<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SchoolScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Tenancy-aware bypass
        if (function_exists('tenancy') && tenancy()->initialized) {
            // In a database-per-tenant architecture, the database is already isolated.
            // We only apply the scope if we want an extra layer of safety within the same DB.
            $schoolId = tenancy()->tenant->school_id ?? null;
            if ($schoolId) {
                $builder->where($model->getTable() . '.school_id', $schoolId);
            }
            return;
        }

        // 2. Auth/Session context (fallback for central DB or single-DB setup)
        if (Auth::hasUser()) {
            $user = Auth::user();

            // Super Admin bypasses the scope unless they have a session school_id set (for impersonation)
            if ($user->hasRole('super_admin')) {
                $sessionSchoolId = session('viewing_school_id');
                if ($sessionSchoolId) {
                    $builder->where($model->getTable() . '.school_id', $sessionSchoolId);
                }
                return;
            }

            // Regular school users are always restricted to their school
            if ($user->school_id) {
                $builder->where($model->getTable() . '.school_id', $user->school_id);
            } else {
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
