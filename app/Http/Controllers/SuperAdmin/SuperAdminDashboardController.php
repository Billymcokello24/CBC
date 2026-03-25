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
use Spatie\Activitylog\Models\Activity;

class SuperAdminDashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(Request $request)
    {
        return Inertia::render('super-admin/Dashboard', [
            'stats' => [
                'total_schools' => School::count(),
                'active_schools' => School::where('status', 'active')->count(),
                'total_users' => User::count(),
                'total_learners' => Student::count(),
                'total_teachers' => Teacher::count(),
                'system_performance' => $this->getSystemPerformance(),
                'recent_activities_count' => Activity::where('created_at', '>=', now()->subHours(24))->count(),
            ],
            'monitoring_urls' => [
                'telescope' => url('/telescope'),
                'horizon' => url('/horizon'),
            ],
            'recent_activities' => Activity::with('causer')
                ->latest()
                ->limit(8)
                ->get(),
            'recent_schools' => School::withCount(['users', 'students as learners_count'])
                ->latest()
                ->limit(5)
                ->get(),
            'school_distribution' => $this->getSchoolDistribution(),
            'impersonation_logs' => DB::table('impersonation_logs')
                ->join('schools', 'impersonation_logs.school_id', '=', 'schools.id')
                ->join('users', 'impersonation_logs.user_id', '=', 'users.id')
                ->select('impersonation_logs.*', 'schools.name as school_name', 'users.name as admin_name')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Impersonate a specific school's admin.
     */
    public function impersonate(School $school, Request $request)
    {
        DB::table('impersonation_logs')->insert([
            'user_id' => auth()->id(),
            'school_id' => $school->id,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['viewing_school_id' => $school->id]);
        
        return redirect()->route('dashboard')->with('success', "Now viewing dashboard for: {$school->name}");
    }

    /**
     * Stop impersonating and return to global dashboard.
     */
    public function stopImpersonating()
    {
        DB::table('impersonation_logs')
            ->where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        session()->forget('viewing_school_id');
        
        return redirect()->route('super-admin.dashboard')->with('success', 'Returned to global dashboard.');
    }

    /**
     * Get basic system performance metrics.
     */
    private function getSystemPerformance()
    {
        try {
            // CPU Load (Unix/Linux)
            $load = sys_getloadavg();
            $cpuLoad = isset($load[0]) ? round($load[0] * 100 / 4, 1) : 0; // Assuming 4 cores for % estimation

            // Memory Usage
            $free = shell_exec('free');
            $free = (string) trim((string) $free);
            $free_arr = explode("\n", $free);
            $mem = isset($free_arr[1]) ? explode(" ", (string) preg_replace('/\s+/', ' ', $free_arr[1])) : [];
            $memUsage = (isset($mem[2]) && isset($mem[1]) && $mem[1] > 0) ? round($mem[2] / $mem[1] * 100, 1) : 0;

            // Disk Space
            $diskFree = disk_free_space('/');
            $diskTotal = disk_total_space('/');
            $diskUsage = $diskTotal > 0 ? round(($diskTotal - $diskFree) / $diskTotal * 100, 1) : 0;

            return [
                'cpu_load' => $cpuLoad,
                'memory_usage' => $memUsage,
                'disk_space' => $diskUsage,
                'status' => ($cpuLoad > 80 || $memUsage > 90 || $diskUsage > 95) ? 'warning' : 'healthy'
            ];
        } catch (\Exception $e) {
            return [
                'cpu_load' => 0,
                'memory_usage' => 0,
                'disk_space' => 0,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
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
