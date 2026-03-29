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
                ]) : null,
                'roles' => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'is_super_admin' => $user ? $user->hasRole('super_admin') : false,
                'impersonating' => [
                    'active' => $user && $user->hasRole('super_admin') && session()->has('viewing_school_id'),
                    'school_name' => session()->has('viewing_school_id') ? \App\Models\School::find(session('viewing_school_id'))?->name : null,
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ]);
    }
}
