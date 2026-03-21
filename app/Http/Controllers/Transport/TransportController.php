<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\StudentTransport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransportController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_vehicles' => Vehicle::where('school_id', $schoolId)->count(),
            'active_routes' => TransportRoute::where('school_id', $schoolId)->where('is_active', true)->count(),
            'total_allocated_students' => StudentTransport::whereHas('route', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->where('is_active', true)->count(),
            'available_capacity' => Vehicle::where('school_id', $schoolId)->where('status', 'active')->sum('capacity') - 
                                   StudentTransport::whereHas('route', function($q) use ($schoolId) {
                                       $q->where('school_id', $schoolId);
                                   })->where('is_active', true)->count(),
        ];

        return Inertia::render('transport/Index', [
            'stats' => $stats,
        ]);
    }

    public function vehicles(): Response
    {
        $schoolId = auth()->user()->school_id;
        $vehicles = Vehicle::where('school_id', $schoolId)->get();

        return Inertia::render('transport/Vehicles', [
            'vehicles' => $vehicles,
        ]);
    }

    public function routes(): Response
    {
        $schoolId = auth()->user()->school_id;
        $routes = TransportRoute::where('school_id', $schoolId)
            ->with(['vehicle', 'stops'])
            ->withCount('allocations')
            ->get();

        return Inertia::render('transport/Routes', [
            'routes' => $routes,
        ]);
    }
}
