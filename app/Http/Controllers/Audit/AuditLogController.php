<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of institutional audit logs.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $schoolId = $user->school_id ?? session('viewing_school_id');

        $query = Activity::with('causer')
            ->whereHasMorph('causer', [\App\Models\User::class], function($q) use ($schoolId) {
                if ($schoolId) $q->where('school_id', $schoolId);
            });

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Search in properties/description
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('properties', 'like', "%{$request->search}%");
            });
        }

        $logs = $query->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn($activity) => [
                'id' => $activity->id,
                'event' => $activity->event ?? 'action',
                'description' => $activity->description,
                'who' => $activity->causer?->name ?? 'System',
                'role' => $activity->causer?->getRoleNames()->first() ?? 'Staff',
                'gadget' => $activity->properties['user_agent'] ?? 'Web Interface',
                'ip' => $activity->properties['ip_address'] ?? 'Internal',
                'where' => $activity->properties['url'] ?? $activity->subject_type,
                'status' => 'success', // Placeholder for status if tracking failed actions
                'when' => $activity->created_at->format('M d, Y H:i:s'),
                'time_ago' => $activity->created_at->diffForHumans(),
                'properties' => $activity->properties,
            ]);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'user_id', 'event']),
        ]);
    }

    /**
     * Display the specified audit log entry.
     */
    public function show(Activity $activity): Response
    {
        $activity->load('causer');
        
        return Inertia::render('AuditLogs/Show', [
            'log' => [
                'id' => $activity->id,
                'event' => $activity->event,
                'description' => $activity->description,
                'who' => $activity->causer?->name,
                'causer' => $activity->causer,
                'subject' => $activity->subject,
                'properties' => $activity->properties,
                'ip' => $activity->properties['ip_address'] ?? 'N/A',
                'agent' => $activity->properties['user_agent'] ?? 'N/A',
                'created_at' => $activity->created_at->toDateTimeString(),
            ]
        ]);
    }
}
