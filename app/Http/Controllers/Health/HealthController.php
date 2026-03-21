<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use App\Models\Health\MedicalVisit;
use App\Models\Health\StudentHealthRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HealthController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_visits_today' => MedicalVisit::where('school_id', $schoolId)->whereDate('visit_datetime', now())->count(),
            'referred_cases' => MedicalVisit::where('school_id', $schoolId)->where('referred_to_hospital', true)->count(),
            'common_condition' => MedicalVisit::where('school_id', $schoolId)
                ->select('diagnosis')
                ->groupBy('diagnosis')
                ->orderByRaw('COUNT(*) DESC')
                ->first()?->diagnosis ?? 'None',
            'medical_alerts' => StudentHealthRecord::whereHas('student', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->whereNotNull('chronic_conditions')->count(),
        ];

        $recentVisits = MedicalVisit::with(['student'])
            ->where('school_id', $schoolId)
            ->orderBy('visit_datetime', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('health/Index', [
            'stats' => $stats,
            'recentVisits' => $recentVisits,
        ]);
    }

    public function visits(): Response
    {
        $schoolId = auth()->user()->school_id;
        $visits = MedicalVisit::with(['student', 'attendee'])
            ->where('school_id', $schoolId)
            ->orderBy('visit_datetime', 'desc')
            ->paginate(15);

        return Inertia::render('health/MedicalVisits', [
            'visits' => $visits,
        ]);
    }

    public function records(): Response
    {
        $schoolId = auth()->user()->school_id;
        $records = StudentHealthRecord::whereHas('student', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with(['student'])
            ->paginate(15);

        return Inertia::render('health/HealthRecords', [
            'records' => $records,
        ]);
    }
}
