<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Administrative roles bypass all permission checks
        if ($user->hasAnyRole(['super_admin', 'school_admin', 'admin', 'principal'])) {
            return $next($request);
        }

        // Teachers are allowed access to curriculum features by default
        // The individual controllers will handle data scoping (ownership checks)
        if ($user->hasRole('teacher') && collect($permissions)->contains(fn($p) => str_starts_with($p, 'curriculum.'))) {
            return $next($request);
        }

        // Check if user has ANY of the required permissions
        if (!empty($permissions) && !$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
