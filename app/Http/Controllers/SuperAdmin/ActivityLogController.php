<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of all system activities.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            })
            ->when($request->log_name, function ($q, $logName) {
                $q->where('log_name', $logName);
            });

        return Inertia::render('super-admin/activity-logs/Index', [
            'logs' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'log_name']),
        ]);
    }

    /**
     * Display the specified activity.
     */
    public function show(Activity $activity)
    {
        return Inertia::render('super-admin/activity-logs/Show', [
            'activity' => $activity->load(['causer', 'subject']),
        ]);
    }
}
