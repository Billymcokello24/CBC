<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifySchool
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->hasRole('super_admin')) {
                // For super admins, we check if they are viewing a specific school
                $viewingSchoolId = session('viewing_school_id');
                if ($viewingSchoolId) {
                    config(['app.current_school_id' => $viewingSchoolId]);
                }
            } else {
                // For regular users, the school is fixed to their profile
                if ($user->school_id) {
                    config(['app.current_school_id' => $user->school_id]);
                    session(['viewing_school_id' => $user->school_id]);
                }
            }
        }

        return $next($request);
    }
}
