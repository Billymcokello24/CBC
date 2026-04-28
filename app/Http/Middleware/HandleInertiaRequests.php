<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'school' => ($user && $user->school) ? array_merge($user->school->toArray(), [
                    'logo' => $user->school->logo ? (str_starts_with($user->school->logo, 'http') ? $user->school->logo : \Illuminate\Support\Facades\Storage::url($user->school->logo)) : null,
                    'theme_color' => $user->school->getSetting('pdf_theme_color', '#1e40af'),
                ]) : null,
                'roles' => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'is_super_admin' => $user ? $user->hasRole('super_admin') : false,
                'teacher' => $user ? $user->teacher : null,
                'teacher_roles' => $user ? [
                    'is_teacher' => $user->hasRole('teacher'),
                    'is_class_teacher' => $user->hasRole('class_teacher') || \App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->exists(),
                    'is_hod' => $user->hasRole('hod') || ($user->teacher && \App\Models\Academic\Department::where('head_of_department_id', $user->teacher->id)->exists()),
                    'assigned_classes_count' => ($user->teacher) ? \App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->count() : 0,
                    'assigned_subjects_count' => ($user->teacher) ? $user->teacher->subjectAssignments()->where('is_active', true)->count() : 0,
                ] : null,
                'impersonating' => [
                    'active' => $user && $user->hasRole('super_admin') && session()->has('viewing_school_id'),
                    'school_name' => session()->has('viewing_school_id') ? \App\Models\School::find(session('viewing_school_id'))?->name : null,
                ],
                'active_imports' => $user ? \App\Models\ImportProcess::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->get() : [],
                'recent_imports' => $user ? \App\Models\ImportProcess::where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'failed'])
                    ->latest()
                    ->take(5)
                    ->get() : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ]);
    }
}
