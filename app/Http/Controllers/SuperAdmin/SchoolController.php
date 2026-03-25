<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SchoolLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Services\SchoolOnboardingService;

class SchoolController extends Controller
{
    protected $onboardingService;

    public function __construct(SchoolOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('super-admin/schools/Index', [
            'schools' => School::withCount(['users', 'students'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    /**
     * Display activation status of all schools.
     */
    public function status()
    {
        return Inertia::render('super-admin/schools/Status', [
            'schools' => School::select('id', 'name', 'code', 'status', 'created_at')
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Display modular configuration for all schools.
     */
    public function modules()
    {
        return Inertia::render('super-admin/schools/Modules', [
            'schools' => School::select('id', 'name', 'code', 'status')
                ->withCount(['users', 'students'])
                ->get(),
            // In a real app, you'd fetch available modules from a config or DB
            'available_modules' => [
                ['id' => 'finance', 'name' => 'Finance & Fees'],
                ['id' => 'transport', 'name' => 'Transport Management'],
                ['id' => 'library', 'name' => 'Library System'],
                ['id' => 'hostel', 'name' => 'Hostel Management'],
                ['id' => 'health', 'name' => 'Medical Records'],
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('super-admin/schools/Create', [
            'schoolTypes' => SchoolType::all(),
            'schoolLevels' => SchoolLevel::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // School fields
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'county' => 'required|string|max:100',
            'school_type_id' => 'required|exists:school_types,id',
            'school_level_id' => 'required|exists:school_levels,id',
            'status' => 'required|in:active,inactive',

            // Admin account fields
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_phone' => 'nullable|string|max:20',
            'admin_password' => 'required|string|min:8',
        ]);

        $schoolData = collect($validated)->except(['admin_name', 'admin_email', 'admin_phone', 'admin_password'])->toArray();
        $adminData = [
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'phone' => $validated['admin_phone'],
            'password' => $validated['admin_password'],
        ];

        $this->onboardingService->register($schoolData, $adminData);

        return redirect()->route('super-admin.schools.index')
            ->with('success', 'School and Admin account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        return Inertia::render('super-admin/schools/Show', [
            'school' => $school->load(['users', 'schoolType', 'schoolLevel']),
            'stats' => [
                'users_count' => $school->users()->count(),
                'students_count' => $school->students()->count(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        return Inertia::render('super-admin/schools/Edit', [
            'school' => $school,
            'schoolTypes' => SchoolType::all(),
            'schoolLevels' => SchoolLevel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'county' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $school->update($validated);

        return redirect()->route('super-admin.schools.index')
            ->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        // For safety, we might want to just deactivate instead of delete
        $school->delete();

        return redirect()->route('super-admin.schools.index')
            ->with('success', 'School deleted successfully.');
    }
}
