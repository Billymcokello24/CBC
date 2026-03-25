<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;

class SystemSettingsController extends Controller
{
    /**
     * Display the global system settings.
     */
    public function index()
    {
        return Inertia::render('super-admin/settings/Index', [
            'settings' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'app_env' => config('app.env'),
                'maintenance_mode' => app()->isDownForMaintenance(),
                'registration_enabled' => config('fortify.registration', true),
            ]
        ]);
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            return back()->with('success', 'System is now ONLINE.');
        } else {
            Artisan::call('down', [
                '--secret' => 'admin-access-key',
                '--refresh' => 15,
                '--retry' => 60
            ]);
            return back()->with('success', 'System is now in MAINTENANCE mode.');
        }
    }

    /**
     * Update global settings (simulation for now).
     */
    public function update(Request $request)
    {
        // In a real app, this would update .env or a settings table
        return back()->with('success', 'Global configuration cache updated.');
    }
}
