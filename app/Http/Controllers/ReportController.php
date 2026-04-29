<?php

namespace App\Http\Controllers;

use App\Models\Assessment\ReportCard;
use App\Models\Student;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports Hub Index
     */
    public function index(): Response
    {
        return Inertia::render('reports/Index');
    }

    /**
     * Progress Report (Report Cards)
     */
    public function progressReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        
        // Fetch students for selection if needed, or just standard listing
        $students = Student::where('school_id', $schoolId)
            ->with(['currentClass:id,name', 'currentClass.gradeLevel:id,name'])
            ->orderBy('first_name')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'admission_number' => $s->admission_number,
                'class' => $s->currentClass?->name,
                'grade' => $s->currentClass?->gradeLevel?->name,
            ]);

        $classes = SchoolClass::where('school_id', $schoolId)
            ->with('gradeLevel:id,name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade' => $c->gradeLevel?->name,
            ]);

        $terms = AcademicTerm::where('school_id', $schoolId)
            ->with('academicYear:id,name')
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('reports/ProgressReport', [
            'students' => $students,
            'classes' => $classes,
            'terms' => $terms,
            'performanceLevels' => [
                ['level' => 4, 'label' => 'Exceeding Expectation', 'code' => 'EE', 'color' => 'emerald'],
                ['level' => 3, 'label' => 'Meeting Expectation', 'code' => 'ME', 'color' => 'blue'],
                ['level' => 2, 'label' => 'Approaching Expectation', 'code' => 'AE', 'color' => 'amber'],
                ['level' => 1, 'label' => 'Below Expectation', 'code' => 'BE', 'color' => 'rose'],
            ]
        ]);
    }

    /**
     * Get specific student report data
     */
    public function getStudentProgress(Request $request, Student $student)
    {
        $termId = $request->input('term_id');
        $term = \App\Models\Academic\AcademicTerm::find($termId);
        
        $competencies = \App\Models\Assessment\StudentCompetencyRating::where('student_id', $student->id)
            ->when($termId, fn($q) => $q->where('academic_term_id', $termId))
            ->with(['subject', 'competency'])
            ->get();

        $performance = [];
        foreach($competencies as $rating) {
            $subjectName = $rating->subject?->name ?? 'Core Academic Domain';
            if (!isset($performance[$subjectName])) {
                $performance[$subjectName] = [
                    'area' => $subjectName,
                    'competencies' => [],
                    'remarks' => 'Institutional competency assessment completed.'
                ];
            }
            $performance[$subjectName]['competencies'][] = [
                'task' => $rating->competency?->name ?? 'General Assessment Task',
                'level' => (int)$rating->rating_level,
                'feedback' => $rating->feedback ?? 'Observed baseline competencies.'
            ];
        }

        if (empty($performance)) {
            $performance = [
                [
                    'area' => 'General Academic Assessment',
                    'competencies' => [
                        ['task' => 'Active participation', 'level' => 3, 'feedback' => 'Awaiting structured CBC data sync.'],
                        ['task' => 'Task completion', 'level' => 3, 'feedback' => 'Awaiting structured CBC data sync.']
                    ],
                    'remarks' => 'Assessment cycle currently in progress.'
                ]
            ];
        }

        $attCounts = DB::table('student_attendances')
            ->selectRaw('count(*) as total, sum(case when status = "present" then 1 else 0 end) as present')
            ->where('student_id', $student->id)
            ->first();
            
        $attRate = ($attCounts && $attCounts->total > 0) 
            ? "{$attCounts->present}/{$attCounts->total} Days Present" 
            : "No Records Found";

        $reportData = [
            'learner' => [
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'grade' => $student->currentClass?->gradeLevel?->name ?? 'Unassigned',
                'term' => $term?->name ?? 'Current Term',
                'class' => $student->currentClass?->name ?? 'Unassigned',
            ],
            'performance' => array_values($performance),
            'traits' => [
                ['trait' => 'Communication', 'level' => 4],
                ['trait' => 'Critical Thinking', 'level' => 3],
                ['trait' => 'Creativity', 'level' => 4],
                ['trait' => 'Citizenship', 'level' => 3],
            ],
            'comments' => [
                'teacher' => 'Academic log synchronized via database portal.',
                'principal' => 'Valid progress registered institutionally.',
                'attendance' => $attRate
            ]
        ];

        return response()->json($reportData);
    }

    /**
     * Subject Performance Report
     */
    public function subjectReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        
        $classes = SchoolClass::where('school_id', $schoolId)
            ->with(['gradeLevel:id,name', 'stream:id,name'])
            ->get();

        $subjects = DB::table('subjects')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->select('subjects.id', 'subjects.name', 'learning_areas.name as learning_area')
            ->get();

        $terms = AcademicTerm::where('school_id', $schoolId)
            ->with('academicYear:id,name')
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('reports/SubjectReport', [
            'classes' => $classes,
            'subjects' => $subjects,
            'terms' => $terms,
            'performanceLevels' => [
                ['level' => 4, 'label' => 'Exceeding Expectation', 'code' => 'EE', 'color' => 'emerald'],
                ['level' => 3, 'label' => 'Meeting Expectation', 'code' => 'ME', 'color' => 'blue'],
                ['level' => 2, 'label' => 'Approaching Expectation', 'code' => 'AE', 'color' => 'amber'],
                ['level' => 1, 'label' => 'Below Expectation', 'code' => 'BE', 'color' => 'rose'],
            ]
        ]);
    }

    /**
     * Class Performance Summary
     */
    public function classSummary(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        
        $classes = SchoolClass::where('school_id', $schoolId)
            ->with(['gradeLevel:id,name', 'stream:id,name', 'classTeacher:id,name'])
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade' => $c->gradeLevel?->name,
                'teacher' => $c->classTeacher?->name,
            ]);

        $terms = AcademicTerm::where('school_id', $schoolId)
            ->with('academicYear:id,name')
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('reports/ClassReport', [
            'classes' => $classes,
            'terms' => $terms,
            'performanceLevels' => [
                ['level' => 4, 'label' => 'Exceeding Expectation', 'code' => 'EE', 'color' => 'emerald'],
                ['level' => 3, 'label' => 'Meeting Expectation', 'code' => 'ME', 'color' => 'blue'],
                ['level' => 2, 'label' => 'Approaching Expectation', 'code' => 'AE', 'color' => 'amber'],
                ['level' => 1, 'label' => 'Below Expectation', 'code' => 'BE', 'color' => 'rose'],
            ]
        ]);
    }

    /**
     * Get specific subject analysis data
     */
    public function getSubjectAnalysis(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        
        $schoolClass = \App\Models\Academic\SchoolClass::find($classId);
        $subject = \App\Models\Curriculum\Subject::find($subjectId);
        
        // Find students in this class
        $studentIds = \App\Models\Student::where('current_class_id', $classId)->pluck('id');
        
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->when($termId, fn($q) => $q->where('academic_term_id', $termId))
            ->with('student')
            ->get();

        $learners = [];
        $counts = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
        $totalScore = 0;
        
        $studentScores = $ratings->groupBy('student_id');
        
        foreach($studentScores as $sid => $studentRatings) {
            $student = $studentRatings->first()->student;
            $avgLevel = round($studentRatings->avg('rating_level'));
            $levelCode = $avgLevel >= 4 ? 'EE' : ($avgLevel == 3 ? 'ME' : ($avgLevel == 2 ? 'AE' : 'BE'));
            
            $counts[$levelCode]++;
            $totalScore += ($avgLevel / 4) * 100;
            
            $learners[] = [
                'name' => $student->full_name,
                'adm' => $student->admission_number,
                'level' => (int) $avgLevel,
                'trend' => 'stable',
                'participation' => 'High'
            ];
        }

        if (empty($learners)) {
            $learners[] = [
                'name' => 'Demo User (Awaiting Sync)',
                'adm' => 'AWD-0000',
                'level' => 3,
                'trend' => 'up',
                'participation' => 'Medium'
            ];
            $counts['ME']++;
        }
        
        $totalAssessed = count($studentScores) > 0 ? count($studentScores) : 1;
        $avgScore = round($totalScore / $totalAssessed, 1);

        $analysis = [
            'subject' => $subject?->name ?? 'Selected Subject',
            'class' => $schoolClass?->name ?? 'All Classes',
            'stats' => [
                'average_score' => $avgScore,
                'total_learners' => count($studentIds),
                'ee' => $counts['EE'],
                'me' => $counts['ME'],
                'ae' => $counts['AE'],
                'be' => $counts['BE'],
                'below' => $counts['BE']
            ],
            'learners' => $learners,
            'trends' => [
                'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                'data' => [65, 68, 70, 72, $avgScore]
            ],
            'interventions' => [
                ['area' => 'General Topic Mastery', 'count' => $counts['BE'], 'priority' => 'High']
            ]
        ];

        return response()->json($analysis);
    }

    /**
     * Get specific class performance data
     */
    public function getClassAnalysis(Request $request)
    {
        $classId = $request->input('class_id');
        $termId = $request->input('term_id');
        
        $schoolClass = \App\Models\Academic\SchoolClass::with('classTeacher')->find($classId);
        $term = \App\Models\Academic\AcademicTerm::find($termId);
        
        $students = \App\Models\Student::where('current_class_id', $classId)->get();
        $totalStudents = $students->count();
        $boys = $students->where('gender', 'Male')->count();
        $girls = $students->where('gender', 'Female')->count();
        
        $studentIds = $students->pluck('id');
        
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)
            ->when($termId, fn($q) => $q->where('academic_term_id', $termId))
            ->with('subject')
            ->get();
            
        $subjectGroups = $ratings->groupBy('subject_id');
        $subjectMastery = [];
        
        foreach($subjectGroups as $sid => $subjectRatings) {
             $avgLevel = $subjectRatings->avg('rating_level');
             $masteryPercentage = min(100, round(($avgLevel / 4) * 100));
             $subject = $subjectRatings->first()->subject;
             
             $subjectMastery[] = [
                 'subject' => $subject?->name ?? 'Core Subject', 
                 'mastery' => $masteryPercentage, 
                 'trend' => $masteryPercentage >= 70 ? 'up' : 'stable'
             ];
        }
        
        if (empty($subjectMastery)) {
             $subjectMastery = [
                 ['subject' => 'Mathematics', 'mastery' => 0, 'trend' => 'stable'],
                 ['subject' => 'Language Activities', 'mastery' => 0, 'trend' => 'stable'],
             ];
        }
        
        $averageMastery = $ratings->count() > 0 ? min(100, round(($ratings->avg('rating_level') / 4) * 100)) : 0;
        
        $attTotal = DB::table('student_attendances')->whereIn('student_id', $studentIds)->count();
        $attPresent = DB::table('student_attendances')->whereIn('student_id', $studentIds)->where('status', 'present')->count();
        $attendanceRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : 100;
        
        $topPerformers = [];
        $distribution = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
        
        $studentScores = $ratings->groupBy('student_id');
        foreach($students as $s) {
            $studentScoreRecords = $studentScores->get($s->id);
            if ($studentScoreRecords) {
                $avg = $studentScoreRecords->avg('rating_level');
                $perf = min(100, round(($avg / 4) * 100));
                $topPerformers[] = ['name' => $s->full_name, 'score' => $perf, 'avatar' => substr($s->first_name, 0, 2)];
                
                $lvl = $avg >= 3.5 ? 'EE' : ($avg >= 2.5 ? 'ME' : ($avg >= 1.5 ? 'AE' : 'BE'));
                $distribution[$lvl]++;
            } else {
                $distribution['AE']++; // Unassessed fallback to approaching
            }
        }
        
        usort($topPerformers, fn($a, $b) => $b['score'] <=> $a['score']);
        $topPerformers = array_slice($topPerformers, 0, 4);

        if (empty($topPerformers) && $totalStudents > 0) {
            $topPerformers[] = ['name' => $students->first()->full_name, 'score' => 0, 'avatar' => 'AK'];
        }

        $analysis = [
            'className' => $schoolClass?->name ?? 'All Classes',
            'grade' => $schoolClass?->gradeLevel?->name ?? 'Level Not Found',
            'school' => 'Institutional Command Center',
            'term' => $term?->name ?? 'Current Term',
            'stats' => [
                'total_students' => $totalStudents,
                'boys' => $boys,
                'girls' => $girls,
                'average_mastery' => $averageMastery,
                'attendance_rate' => $attendanceRate,
                'published_reports' => $studentScores->count(),
            ],
            'subject_mastery' => $subjectMastery,
            'top_performers' => $topPerformers,
            'distribution' => [
                ['label' => 'EE', 'value' => $distribution['EE'], 'color' => 'emerald'],
                ['label' => 'ME', 'value' => $distribution['ME'], 'color' => 'blue'],
                ['label' => 'AE', 'value' => $distribution['AE'], 'color' => 'amber'],
                ['label' => 'BE', 'value' => $distribution['BE'], 'color' => 'rose'],
            ]
        ];
        return response()->json($analysis);
    }

    /**
     * Reports Hub Methods (Phase 2-4 Expansions)
     */
    public function attendanceReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/AttendanceReport', ['classes' => $classes]);
    }

    public function assessmentReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/AssessmentReport', ['classes' => $classes]);
    }

    public function competencyReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/CompetencyReport', ['classes' => $classes]);
    }

    public function learnerReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/LearnerReport', ['classes' => $classes]);
    }

    public function teacherReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/TeacherReport', ['classes' => $classes]);
    }

    public function financeReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/FinanceReport', ['classes' => $classes]);
    }

    public function enrollmentReport(Request $request): Response
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        return Inertia::render('reports/EnrollmentReport', ['classes' => $classes]);
    }

    // JSON Analysis & PDF Export Endpoints
    public function getAttendanceAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $classId = $request->input('class_id');
        $search = $request->input('search');
        
        $students = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->where('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%")
                   ->orWhere('admission_number', 'like', "%{$search}%");
            }))
            ->get();
        $studentIds = $students->pluck('id');
        
        // Per-student attendance
        $attRecords = DB::table('student_attendance')
            ->selectRaw('student_id, count(*) as total, sum(case when status = "present" then 1 else 0 end) as present, sum(case when status = "absent" then 1 else 0 end) as absent, sum(case when status = "late" then 1 else 0 end) as late')
            ->where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $learners = [];
        $totalPresent = 0; $totalAbsent = 0; $totalLate = 0; $totalRecords = 0;
        
        foreach($students as $student) {
            $att = $attRecords->get($student->id);
            $total = $att ? $att->total : 0;
            $present = $att ? $att->present : 0;
            $absent = $att ? $att->absent : 0;
            $late = $att ? $att->late : 0;
            $rate = $total > 0 ? round(($present / $total) * 100) : 0;
            
            $totalPresent += $present;
            $totalAbsent += $absent;
            $totalLate += $late;
            $totalRecords += $total;
            
            $learners[] = [
                'name' => $student->full_name,
                'adm' => $student->admission_number,
                'total_days' => $total,
                'present_days' => $present,
                'absent_days' => $absent,
                'late_days' => $late,
                'rate' => $rate,
                'status' => $rate >= 90 ? 'Excellent' : ($rate >= 75 ? 'Good' : ($total > 0 ? 'Needs Attention' : 'No Records'))
            ];
        }

        $overallRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // Monthly trend (last 6 months)
        $monthlyTrend = DB::table('student_attendance')
            ->selectRaw("DATE_FORMAT(attendance_date, '%Y-%m') as month, count(*) as total, sum(case when status = 'present' then 1 else 0 end) as present")
            ->where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->where('attendance_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trendLabels = [];
        $trendPresent = [];
        $trendTotal = [];
        foreach($monthlyTrend as $row) {
            $trendLabels[] = \Carbon\Carbon::parse($row->month . '-01')->format('M Y');
            $trendPresent[] = $row->present;
            $trendTotal[] = $row->total;
        }

        // Class-wise comparison
        $classComparison = [];
        if (!$classId) {
            $classStats = DB::table('student_attendance')
                ->join('school_classes', 'student_attendance.class_id', '=', 'school_classes.id')
                ->selectRaw("school_classes.name as class_name, count(*) as total, sum(case when student_attendance.status = 'present' then 1 else 0 end) as present")
                ->where('student_attendance.school_id', $schoolId)
                ->groupBy('school_classes.name')
                ->get();
            foreach($classStats as $cs) {
                $classComparison[] = [
                    'class' => $cs->class_name,
                    'rate' => $cs->total > 0 ? round(($cs->present / $cs->total) * 100) : 0,
                ];
            }
        }

        return response()->json([
             'class' => $classId ? SchoolClass::find($classId)?->name : 'All Classes',
             'stats' => [
                 'overall_rate' => $overallRate,
                 'total_learners' => count($students),
                 'total_present' => $totalPresent,
                 'total_absent' => $totalAbsent,
                 'total_late' => $totalLate,
                 'perfect_attendance' => collect($learners)->where('rate', '>=', 95)->count(),
                 'needs_attention' => collect($learners)->where('rate', '<', 75)->where('total_days', '>', 0)->count(),
             ],
             'distribution' => [
                 'present' => $totalPresent,
                 'absent' => $totalAbsent,
                 'late' => $totalLate,
             ],
             'monthly_trend' => [
                 'labels' => $trendLabels,
                 'present' => $trendPresent,
                 'total' => $trendTotal,
             ],
             'class_comparison' => $classComparison,
             'learners' => $learners
        ]);
    }

    public function exportAttendancePdf(Request $request) 
    { 
        $classId = $request->input('class_id');
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $students = \App\Models\Student::with('currentClass')->when($classId, fn($q) => $q->where('current_class_id', $classId))->get();
        
        $studentIds = $students->pluck('id');
        $attRecords = DB::table('student_attendance')
            ->selectRaw('student_id, count(*) as total, sum(case when status = "present" then 1 else 0 end) as present')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        foreach($students as $s) {
            $att = $attRecords->get($s->id);
            $s->att_total = $att ? $att->total : 0;
            $s->att_present = $att ? $att->present : 0;
            $s->att_rate = $s->att_total > 0 ? round(($s->att_present / $s->att_total) * 100) : 0;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.attendance-pdf', [
            'students' => $students,
            'classTitle' => $classId ? \App\Models\Academic\SchoolClass::find($classId)?->name : 'All Classes',
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('attendance_report.pdf');
    }

    public function getAssessmentAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $classId = $request->input('class_id');
        $search = $request->input('search');
        
        $students = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->where('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%")
                   ->orWhere('admission_number', 'like', "%{$search}%");
            }))
            ->get();
        $studentIds = $students->pluck('id');
        
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)
            ->with(['student', 'subject', 'competency'])
            ->get();
            
        // Subject-level breakdown (for bar chart)
        $subjectGroups = $ratings->groupBy('subject_id');
        $subjects = [];
        foreach($subjectGroups as $sid => $group) {
             $subject = $group->first()->subject;
             $avg = $group->avg('rating_level');
             $rate = min(100, round(($avg / 4) * 100));
             $subjects[] = [
                 'subject' => $subject?->name ?? 'Core Domain',
                 'total_assessed' => $group->groupBy('student_id')->count(),
                 'average_score' => $rate,
                 'status' => $rate >= 80 ? 'Excellent' : ($rate >= 50 ? 'Average' : 'Below Average')
             ];
        }

        // Performance level distribution (for pie chart)
        $distribution = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
        $studentScores = $ratings->groupBy('student_id');
        $learnersList = [];
        foreach($students as $s) {
            $sRatings = $studentScores->get($s->id);
            if ($sRatings && $sRatings->count() > 0) {
                $avg = $sRatings->avg('rating_level');
                $level = $avg >= 3.5 ? 'EE' : ($avg >= 2.5 ? 'ME' : ($avg >= 1.5 ? 'AE' : 'BE'));
                $distribution[$level]++;
                $score = min(100, round(($avg / 4) * 100));
                $learnersList[] = [
                    'name' => $s->full_name,
                    'adm' => $s->admission_number,
                    'score' => $score,
                    'level' => $level,
                    'subjects_count' => $sRatings->groupBy('subject_id')->count(),
                ];
            }
        }
        usort($learnersList, fn($a, $b) => $b['score'] <=> $a['score']);

        $overallAvg = count($subjects) > 0 ? round(collect($subjects)->avg('average_score')) : 0;

        return response()->json([
             'class' => $classId ? SchoolClass::find($classId)?->name : 'All Classes',
             'stats' => [
                 'total_subjects' => count($subjects),
                 'total_learners_assessed' => count($learnersList),
                 'overall_average' => $overallAvg,
                 'total_ratings' => $ratings->count(),
             ],
             'distribution' => $distribution,
             'subjects' => $subjects,
             'learners' => $learnersList,
        ]);
    }

    public function exportAssessmentPdf(Request $request) 
    { 
        $classId = $request->input('class_id');
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $students = \App\Models\Student::when($classId, fn($q) => $q->where('current_class_id', $classId))->get();
        $studentIds = $students->pluck('id');
        
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)->with(['subject'])->get();
            
        $subjectGroups = $ratings->groupBy('subject_id');
        $assessments = [];
        foreach($subjectGroups as $sid => $group) {
             $subject = $group->first()->subject;
             $avg = $group->avg('rating_level');
             $rate = min(100, round(($avg / 4) * 100));
             $assessments[] = (object)[
                 'subject' => $subject?->name ?? 'Core Domain',
                 'total_assessed' => $group->groupBy('student_id')->count(),
                 'average_score' => $rate,
                 'status' => $rate >= 80 ? 'Excellent' : ($rate >= 50 ? 'Average' : 'Below Average')
             ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.assessment-pdf', [
            'assessmentsData' => $assessments,
            'classTitle' => $classId ? \App\Models\Academic\SchoolClass::find($classId)?->name : 'All Classes',
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'landscape'); // User requested landscape for detailed academic reports
        
        return $pdf->download('assessment_report.pdf');
    }

    public function getCompetencyAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $classId = $request->input('class_id');
        $search = $request->input('search');

        $studentIds = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->pluck('id');

        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)
            ->with(['competency', 'subject'])
            ->when($search, fn($q) => $q->whereHas('competency', fn($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->get();
        
        $groups = $ratings->groupBy('competency_id');
        $competencies = [];
        $totalEE = 0; $totalME = 0; $totalAE = 0; $totalBE = 0;
        
        foreach($groups as $cid => $group) {
             $comp = $group->first()->competency;
             $subj = $group->first()->subject;
             $levelArray = $group->pluck('rating_level');
             $ee = $levelArray->filter(fn($l) => $l >= 4)->count();
             $me = $levelArray->filter(fn($l) => $l == 3)->count();
             $ae = $levelArray->filter(fn($l) => $l == 2)->count();
             $be = $levelArray->filter(fn($l) => $l <= 1)->count();
             $totalEE += $ee; $totalME += $me; $totalAE += $ae; $totalBE += $be;
             
             $competencies[] = [
                 'competency' => $comp?->name ?? 'Core Competency',
                 'subject' => $subj?->name ?? 'General',
                 'ee' => $ee, 'me' => $me, 'ae' => $ae, 'be' => $be,
                 'total' => $group->count()
             ];
        }

        return response()->json([
             'stats' => [
                 'total_tracked' => count($competencies),
                 'total_ratings' => $ratings->count(),
                 'overall_ee' => $totalEE,
                 'overall_me' => $totalME,
             ],
             'distribution' => ['EE' => $totalEE, 'ME' => $totalME, 'AE' => $totalAE, 'BE' => $totalBE],
             'competencies' => $competencies
        ]);
    }

    public function exportCompetencyPdf(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $ratings = \App\Models\Assessment\StudentCompetencyRating::with(['competency', 'subject'])->get();
        $groups = $ratings->groupBy('competency_id');
        $competencies = [];
        
        foreach($groups as $cid => $group) {
             $comp = $group->first()->competency;
             $subj = $group->first()->subject;
             $levelArray = $group->pluck('rating_level');
             $competencies[] = (object)[
                 'competency' => $comp?->name ?? 'Core Competency',
                 'subject' => $subj?->name ?? 'General',
                 'ee' => $levelArray->filter(fn($l) => $l >= 4)->count(),
                 'me' => $levelArray->filter(fn($l) => $l == 3)->count(),
                 'ae' => $levelArray->filter(fn($l) => $l == 2)->count(),
                 'be' => $levelArray->filter(fn($l) => $l <= 1)->count(),
                 'total' => $group->count()
             ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.competency-pdf', [
            'competenciesData' => $competencies,
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('competency_mastery_report.pdf');
    }

    public function getLearnerAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $classId = $request->input('class_id');
        $search = $request->input('search');
        $gender = $request->input('gender');
        $status = $request->input('status');

        $students = Student::with('currentClass')
            ->where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->when($gender, fn($q) => $q->where('gender', $gender))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->where('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%")
                   ->orWhere('admission_number', 'like', "%{$search}%");
            }))
            ->get();
        
        $allStudents = Student::where('school_id', $schoolId)->get();
        $total = $allStudents->count();
        $male = $allStudents->where('gender', 'Male')->count();
        $female = $allStudents->where('gender', 'Female')->count();
        
        $statusDistribution = [
             'Active' => $allStudents->where('status', 'active')->count(),
             'Suspended' => $allStudents->where('status', 'suspended')->count(),
             'Graduated' => $allStudents->where('status', 'graduated')->count(),
             'Withdrawn' => $allStudents->where('status', 'withdrawn')->count(),
        ];

        // Class-level enrollment (bar chart)
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel')->get();
        $classEnrollment = [];
        foreach($classes as $c) {
            $count = $allStudents->where('current_class_id', $c->id)->count();
            if ($count > 0) {
                $classEnrollment[] = ['class' => $c->name, 'count' => $count];
            }
        }

        // Enrollment growth by month (last 6 months line chart)
        $growthTrend = DB::table('students')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as count")
            ->where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $growthLabels = $growthTrend->map(fn($r) => \Carbon\Carbon::parse($r->month . '-01')->format('M Y'))->toArray();
        $growthData = $growthTrend->pluck('count')->toArray();

        $learners = [];
        foreach($students as $s) {
             $learners[] = [
                 'name' => $s->full_name ?? $s->first_name . ' ' . $s->last_name,
                 'adm' => $s->admission_number ?? 'N/A',
                 'gender' => $s->gender ?? 'Unknown',
                 'class' => $s->currentClass?->name ?? 'Unassigned',
                 'status' => ucfirst($s->status ?? 'unknown')
             ];
        }

        return response()->json([
             'stats' => [
                 'total' => $total,
                 'male' => $male,
                 'female' => $female,
                 'active' => $statusDistribution['Active']
             ],
             'gender_split' => ['Male' => $male, 'Female' => $female],
             'status_distribution' => $statusDistribution,
             'class_enrollment' => $classEnrollment,
             'growth_trend' => ['labels' => $growthLabels, 'data' => $growthData],
             'learners' => $learners
        ]);
    }

    public function exportLearnerPdf(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $students = \App\Models\Student::with('currentClass')->where('school_id', $schoolId)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.learner-pdf', [
            'learners' => $students,
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('learner_profile_report.pdf');
    }

    public function getTeacherAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');

        $teachers = \App\Models\Teacher::with('department')
            ->where('school_id', $schoolId)
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->where('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%")
                   ->orWhere('staff_number', 'like', "%{$search}%");
            }))
            ->get();
        
        $allTeachers = \App\Models\Teacher::where('school_id', $schoolId)->with('department')->get();
        $total = $allTeachers->count();
        $male = $allTeachers->where('gender', 'Male')->count();
        $female = $allTeachers->where('gender', 'Female')->count();
        $active = $allTeachers->where('status', 'active')->count();

        // Department breakdown (bar chart)
        $deptBreakdown = [];
        $deptGroups = $allTeachers->groupBy(fn($t) => $t->department?->name ?? 'Unassigned');
        foreach($deptGroups as $dept => $group) {
            $deptBreakdown[] = ['department' => $dept, 'count' => count($group)];
        }

        // Status distribution
        $statusDist = [
            'Active' => $allTeachers->where('status', 'active')->count(),
            'Inactive' => $allTeachers->where('status', 'inactive')->count(),
        ];

        $roster = [];
        foreach($teachers as $t) {
             $roster[] = [
                 'name' => $t->full_name ?? $t->first_name . ' ' . $t->last_name,
                 'id_no' => $t->tsc_number ?? $t->staff_number ?? 'N/A',
                 'department' => $t->department?->name ?? 'Unassigned',
                 'gender' => $t->gender ?? 'Unknown',
                 'email' => $t->email ?? 'N/A',
                 'status' => ucfirst($t->status ?? 'unknown')
             ];
        }

        return response()->json([
             'stats' => [
                 'total' => $total,
                 'male' => $male,
                 'female' => $female,
                 'active' => $active
             ],
             'gender_split' => ['Male' => $male, 'Female' => $female],
             'status_distribution' => $statusDist,
             'department_breakdown' => $deptBreakdown,
             'roster' => $roster
        ]);
    }

    public function exportTeacherPdf(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $teachers = \App\Models\Teacher::where('school_id', $schoolId)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.teacher-pdf', [
            'teachers' => $teachers,
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('teacher_metrics_report.pdf');
    }

    public function getFinanceAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');

        $invoices = \App\Models\Finance\Invoice::where('school_id', $schoolId)->get();
        $payments = \App\Models\Finance\FeePayment::where('school_id', $schoolId)->where('status', 'successful')->get();
        
        $totalBilled = $invoices->where('status', '!=', 'cancelled')->sum('total');
        $totalPaid = $payments->sum('amount');
        $outstanding = max(0, $totalBilled - $totalPaid);
        
        $collectionRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100) : 0;

        // Monthly collection trend (line chart)
        $monthlyPayments = DB::table('fee_payments')
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, sum(amount) as total")
            ->where('school_id', $schoolId)
            ->where('status', 'successful')
            ->where('payment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $trendLabels = $monthlyPayments->map(fn($r) => \Carbon\Carbon::parse($r->month . '-01')->format('M Y'))->toArray();
        $trendData = $monthlyPayments->pluck('total')->map(fn($v) => round($v, 2))->toArray();

        // Payment method breakdown (doughnut chart)
        $methodBreakdown = DB::table('fee_payments')
            ->selectRaw("payment_method, sum(amount) as total")
            ->where('school_id', $schoolId)
            ->where('status', 'successful')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn($r) => [ucfirst($r->payment_method ?? 'Other') => round($r->total, 2)])
            ->toArray();
        
        $records = [];
        $invoiceGroups = $invoices->where('status', '!=', 'cancelled')->groupBy('student_id');
        
        foreach($invoiceGroups as $sid => $group) {
             $student = Student::find($sid);
             if ($search && $student && !str_contains(strtolower($student->full_name), strtolower($search)) && !str_contains(strtolower($student->admission_number ?? ''), strtolower($search))) {
                 continue;
             }
             $billed = $group->sum('total');
             $paid = $payments->where('student_id', $sid)->sum('amount');
             
             $records[] = [
                 'student' => $student?->full_name ?? 'Unknown Student',
                 'adm' => $student?->admission_number ?? '-',
                 'billed' => round($billed, 2),
                 'paid' => round($paid, 2),
                 'balance' => max(0, round($billed - $paid, 2)),
                 'status' => ($billed - $paid) <= 0 ? 'Cleared' : 'Pending'
             ];
        }

        return response()->json([
             'stats' => [
                 'total_billed' => round($totalBilled, 2),
                 'total_paid' => round($totalPaid, 2),
                 'outstanding' => round($outstanding, 2),
                 'collection_rate' => $collectionRate
             ],
             'paid_vs_outstanding' => ['Paid' => round($totalPaid, 2), 'Outstanding' => round($outstanding, 2)],
             'monthly_trend' => ['labels' => $trendLabels, 'data' => $trendData],
             'method_breakdown' => $methodBreakdown,
             'records' => $records
        ]);
    }

    public function exportFinancePdf(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $invoices = \App\Models\Finance\Invoice::where('school_id', $schoolId)->get();
        $payments = \App\Models\Finance\FeePayment::where('school_id', $schoolId)->where('status', 'successful')->get();
        
        $invoiceGroups = $invoices->where('status', '!=', 'cancelled')->groupBy('student_id');
        $records = [];
        foreach($invoiceGroups as $sid => $group) {
             $student = \App\Models\Student::find($sid);
             $billed = $group->sum('amount');
             $paid = $payments->where('student_id', $sid)->sum('amount');
             
             $records[] = (object)[
                 'student' => $student?->full_name ?? 'Unknown Student',
                 'adm' => $student?->admission_number ?? '-',
                 'billed' => $billed,
                 'paid' => $paid,
                 'balance' => max(0, $billed - $paid),
                 'status' => ($billed - $paid) <= 0 ? 'Cleared' : 'Pending'
             ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.finance-pdf', [
            'records' => $records,
            'totalBilled' => $invoices->where('status', '!=', 'cancelled')->sum('amount'),
            'totalPaid' => $payments->sum('amount'),
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('finance_report.pdf');
    }

    public function getEnrollmentAnalysis(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');
        
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel')->get();
        $students = Student::where('school_id', $schoolId)->get();
        
        $totalStudents = $students->count();
        $boys = $students->where('gender', 'Male')->count();
        $girls = $students->where('gender', 'Female')->count();
        $active = $students->where('status', 'active')->count();

        $classData = [];
        foreach($classes as $c) {
            $classStudents = $students->where('current_class_id', $c->id);
            $c_boys = $classStudents->where('gender', 'Male')->count();
            $c_girls = $classStudents->where('gender', 'Female')->count();
            $c_total = $classStudents->count();
            
            if ($search && !str_contains(strtolower($c->name), strtolower($search)) && !str_contains(strtolower($c->gradeLevel?->name ?? ''), strtolower($search))) {
                continue;
            }

            $classData[] = [
                'class' => $c->name,
                'grade' => $c->gradeLevel?->name ?? 'Unassigned',
                'boys' => $c_boys,
                'girls' => $c_girls,
                'total' => $c_total,
                'status' => $c_total > 40 ? 'Overcrowded' : ($c_total == 0 ? 'Empty' : 'Optimal')
            ];
        }

        // Grade-level totals (bar chart)
        $gradeTotals = [];
        $gradeGroups = $classes->groupBy(fn($c) => $c->gradeLevel?->name ?? 'Unassigned');
        foreach($gradeGroups as $grade => $gradeClasses) {
            $gradeStudentCount = 0;
            foreach($gradeClasses as $gc) {
                $gradeStudentCount += $students->where('current_class_id', $gc->id)->count();
            }
            $gradeTotals[] = ['grade' => $grade, 'count' => $gradeStudentCount];
        }

        // Growth trend (line chart)
        $growthTrend = DB::table('students')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as count")
            ->where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $growthLabels = $growthTrend->map(fn($r) => \Carbon\Carbon::parse($r->month . '-01')->format('M Y'))->toArray();
        $growthData = $growthTrend->pluck('count')->toArray();

        return response()->json([
             'stats' => [
                 'total_learners' => $totalStudents,
                 'boys' => $boys,
                 'girls' => $girls,
                 'active' => $active,
             ],
             'gender_split' => ['Male' => $boys, 'Female' => $girls],
             'grade_totals' => $gradeTotals,
             'growth_trend' => ['labels' => $growthLabels, 'data' => $growthData],
             'classes' => $classData
        ]);
    }

    public function exportEnrollmentPdf(Request $request) 
    { 
        $schoolId = auth()->user()->school_id;
        $school = \App\Models\School::find($schoolId);
        $themeColor = $school->getSetting('theme_color', '#1e40af');

        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel')->get();
        $students = \App\Models\Student::where('school_id', $schoolId)->get();

        $data = [];
        foreach($classes as $c) {
            $classStudents = $students->where('current_class_id', $c->id);
            $c_boys = $classStudents->where('gender', 'Male')->count();
            $c_girls = $classStudents->where('gender', 'Female')->count();
            $c_total = $classStudents->count();
            
            $data[] = (object)[
                'class' => $c->name,
                'grade' => $c->gradeLevel?->name ?? 'Unassigned',
                'boys' => $c_boys,
                'girls' => $c_girls,
                'total' => $c_total,
                'status' => $c_total > 40 ? 'Overcrowded' : 'Optimal'
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.enrollment-pdf', [
            'classesData' => $data,
            'school' => $school,
            'themeColor' => $themeColor
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('enrollment_report.pdf');
    }
}
