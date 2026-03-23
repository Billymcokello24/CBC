<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuperAdminDashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        return Inertia::render('super-admin/Dashboard', [
            'stats' => [
                'total_schools' => School::count(),
                'active_schools' => School::where('status', 'active')->count(),
                'total_users' => User::count(),
                'total_students' => Student::count(),
                'total_teachers' => Teacher::count(),
                'system_performance' => $this->getSystemPerformance(),
            ],
            'recent_schools' => School::withCount(['users', 'students'])
                ->latest()
                ->limit(5)
                ->get(),
            'school_distribution' => $this->getSchoolDistribution(),
        ]);
    }

    /**
     * Impersonate a specific school's admin.
     */
    public function impersonate(School $school)
    {
        session(['viewing_school_id' => $school->id]);
        
        return redirect()->route('dashboard')->with('success', "Now viewing dashboard for: {$school->name}");
    }

    /**
     * Stop impersonating and return to global dashboard.
     */
    public function stopImpersonating()
    {
        session()->forget('viewing_school_id');
        
        return redirect()->route('super-admin.dashboard')->with('success', 'Returned to global dashboard.');
    }

    /**
     * Get basic system performance metrics.
     */
    private function getSystemPerformance()
    {
        // Placeholder for real performance monitoring
        return [
            'cpu_load' => 12,
            'memory_usage' => 45,
            'disk_space' => 82,
            'status' => 'healthy'
        ];
    }

    /**
     * Get school distribution data (e.g. by county).
     */
    private function getSchoolDistribution()
    {
        return School::groupBy('county')
            ->select('county as label', DB::raw('count(*) as value'))
            ->get();
    }
}
