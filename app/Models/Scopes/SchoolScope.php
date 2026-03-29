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
                // If they have no school_id and are not super_admin, they shouldn't see anything
                // unless it's a model that doesn't strictly require a school (unlikely in this SaaS)
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
