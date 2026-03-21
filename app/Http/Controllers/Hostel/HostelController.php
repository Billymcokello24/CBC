<?php

namespace App\Http\Controllers\Hostel;

use App\Http\Controllers\Controller;
use App\Models\Hostel\Hostel;
use App\Models\Hostel\HostelAllocation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HostelController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_hostels' => Hostel::where('school_id', $schoolId)->count(),
            'total_capacity' => Hostel::where('school_id', $schoolId)->sum('total_capacity'),
            'current_occupancy' => Hostel::where('school_id', $schoolId)->sum('current_occupancy'),
            'available_beds' => Hostel::where('school_id', $schoolId)->sum('total_capacity') - 
                               Hostel::where('school_id', $schoolId)->sum('current_occupancy'),
        ];

        $hostels = Hostel::where('school_id', $schoolId)
            ->withCount('allocations')
            ->get();

        return Inertia::render('hostel/Index', [
            'stats' => $stats,
            'hostels' => $hostels,
        ]);
    }

    public function allocations(): Response
    {
        $schoolId = auth()->user()->school_id;
        $allocations = HostelAllocation::whereHas('hostel', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with(['student', 'hostel', 'academicYear'])
            ->orderBy('allocation_date', 'desc')
            ->paginate(15);

        return Inertia::render('hostel/Allocations', [
            'allocations' => $allocations,
        ]);
    }
}
