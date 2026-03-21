<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Club;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'upcoming_events' => Event::where('school_id', $schoolId)->where('start_date', '>=', today())->count(),
            'active_clubs' => Club::where('school_id', $schoolId)->where('is_active', true)->count(),
            'featured_events' => Event::where('school_id', $schoolId)->where('is_featured', true)->count(),
        ];

        $upcomingEvents = Event::where('school_id', $schoolId)
            ->where('start_date', '>=', today())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        return Inertia::render('events/Index', [
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function clubs(): Response
    {
        $schoolId = auth()->user()->school_id;
        $clubs = Club::where('school_id', $schoolId)
            ->with(['patron'])
            ->withCount('members')
            ->orderBy('name')
            ->paginate(12);

        return Inertia::render('events/Clubs', [
            'clubs' => $clubs,
        ]);
    }

    public function events(): Response
    {
        $schoolId = auth()->user()->school_id;
        $events = Event::where('school_id', $schoolId)
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return Inertia::render('events/Events', [
            'events' => $events,
        ]);
    }
}
