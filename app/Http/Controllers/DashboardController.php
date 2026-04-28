<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use App\Models\Finance\FeePayment;
use App\Models\Finance\StudentFee;
use App\Models\Academic\SchoolSubject;
use App\Models\Attendance\StudentAttendance;
use App\Models\Communication\Announcement;
use App\Models\Communication\Event;
use App\Models\Academic\PeriodDefinition;
use App\Models\Academic\TimetableSlot;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Curriculum\SchemeOfWork;
use App\Models\Curriculum\LessonPlan;
use App\Models\Curriculum\AssignmentSubmission;
use App\Models\Curriculum\Subject;
use App\Models\Academic\Department;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Route to role-specific dashboard
        if ($user->hasRole('super_admin') && !session('viewing_school_id')) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal'])) {
            return $this->adminDashboard();
        }

        if ($user->hasAnyRole(['teacher', 'hod', 'class_teacher']) || ($user->teacher)) {
            return $this->teacherDashboard();
        }

        if ($user->hasRole('parent')) {
            return $this->parentDashboard();
        }

        if ($user->hasRole('student')) {
            return $this->studentDashboard();
        }

        if ($user->hasRole('finance_officer')) {
            return $this->financeDashboard();
        }

        // Default: admin dashboard
        return $this->adminDashboard();
    }

    // ─────────────────────────────────────────
    // ADMIN DASHBOARD (original)
    // ─────────────────────────────────────────
    private function adminDashboard()
    {
        try {
            $currentYear = AcademicYear::where('is_current', true)->first();
            $currentTerm = AcademicTerm::where('is_current', true)->first();

            $stats = $this->getStats();
            
            return Inertia::render('Dashboard', [
                'dashboardType' => 'admin',
                'stats' => $stats,
                'classCapacity' => $this->getClassCapacity(),
                'weeklyAttendance' => $this->getWeeklyAttendance(),
                'enrollmentTrends' => $this->getEnrollmentTrends(),
                'recentActivities' => $this->getRecentActivities(),
                'subjectAnalytics' => $this->getSubjectAnalytics(),
                'streamAnalytics' => $this->getStreamAnalytics(),
                'learnerQuickAccess' => $this->getLearnerQuickAccess(),
                'currentYear' => $currentYear,
                'currentTerm' => $currentTerm,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return Inertia::render('Dashboard', [
                'dashboardType' => 'admin',
                'stats' => $this->getDefaultStats(),
                'classCapacity' => ['labels' => [], 'actual' => [], 'expected' => []],
                'weeklyAttendance' => $this->getDefaultWeeklyAttendance(),
                'recentActivities' => $this->getDefaultRecentActivities(),
                'subjectAnalytics' => [],
                'streamAnalytics' => [],
                'learnerQuickAccess' => [],
                'currentYear' => null,
                'currentTerm' => null,
            ]);
        }
    }

    private function getLearnerQuickAccess(): array
    {
        try {
            return Student::where('status', 'active')
                ->with('currentClass:id,name')
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->full_name,
                    'class' => $s->currentClass?->name ?? 'Unassigned',
                    'photo' => $s->photo_url,
                    'admission_number' => $s->admission_number
                ])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────
    // TEACHER DASHBOARD
    // ─────────────────────────────────────────
    private function teacherDashboard()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $academicYear = AcademicYear::where('is_current', true)->first();

        // 1. Role Identification
        $isClassTeacher = SchoolClass::where('class_teacher_id', $user->id)->exists();
        $isHod = $teacher && \App\Models\Academic\Department::where('head_of_department_id', $teacher->id)->exists();

        // 2. Base Data (Shared)
        $myClasses = collect();
        $mySubjects = collect();
        $todaysTimetable = [];
        $recentAssessments = [];
        $attendanceStats = [];
        $syllabusProgress = collect();
        $pendingTasks = [];
        $totalStudents = 0;

        // Class Teacher Specific Data
        $classManagement = null;
        
        // HOD Specific Data
        $departmentData = null;

        if ($teacher) {
            // A. Get class-teacher assignments
            $classTeacherClasses = SchoolClass::where('class_teacher_id', $user->id)
                ->with(['gradeLevel', 'stream', 'activeStudents'])
                ->get();

            // B. Get subject assignments
            $subjectAssignments = \App\Models\TeacherSubject::where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->with(['subject', 'schoolClass.gradeLevel', 'schoolClass.stream'])
                ->get();

            // Normalize classes for the overview
            $myClasses = $classTeacherClasses->toBase()->map(fn($c) => [
                'id' => $c->id, 'name' => $c->name, 'code' => $c->code,
                'grade' => $c->gradeLevel?->name, 'stream' => $c->stream?->name,
                'is_class_teacher' => true, 'learner_count' => $c->active_students_count ?? $c->students()->where('status', 'active')->count()
            ])->merge(
                $subjectAssignments->toBase()->map(fn($a) => [
                    'id' => $a->schoolClass?->id, 'name' => $a->schoolClass?->name, 'code' => $a->schoolClass?->code,
                    'grade' => $a->schoolClass?->gradeLevel?->name, 'stream' => $a->schoolClass?->stream?->name,
                    'is_class_teacher' => false, 'learner_count' => $a->schoolClass?->students()->where('status', 'active')->count() ?? 0
                ])
            )->unique('id')->filter()->values();

            // Normalize subjects
            $mySubjects = $subjectAssignments->toBase()->map(fn($a) => [
                'id' => $a->subject?->id, 'name' => $a->subject?->name, 'code' => $a->subject?->code,
                'class_name' => $a->schoolClass?->name, 'grade_level_id' => $a->schoolClass?->grade_level_id,
                'is_primary' => $a->is_primary_teacher
            ])->unique(fn($s) => ($s['id'] ?? 0) . '-' . ($s['class_name'] ?? ''))->values();

            $classIds = $myClasses->pluck('id')->filter()->toArray();
            $totalStudents = Student::whereIn('current_class_id', $classIds)->where('status', 'active')->count();

            // C. Today's timetable
            $dayOfWeek = strtolower(date('l'));
            $todaysTimetable = TimetableSlot::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->with(['subject', 'periodDefinition', 'timetable.schoolClass'])
                ->orderBy('start_time')
                ->get()
                ->map(fn($slot) => [
                    'id' => $slot->id, 'subject' => $slot->subject?->name, 'class' => $slot->timetable?->schoolClass?->name,
                    'room' => $slot->room_number, 'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'duration' => $slot->periodDefinition?->duration_minutes ?? $slot->start_time->diffInMinutes($slot->end_time),
                    'status' => 'scheduled'
                ]);

            // D. Recent Assessments
            $recentAssessments = Assessment::where('teacher_id', $user->id)
                ->with(['subject', 'class', 'assessmentType'])
                ->latest()
                ->limit(5)
                ->get();

            // E. Attendance Summary (Last 7 days)
            $attendanceStats = StudentAttendance::whereIn('class_id', $classIds)
                ->where('attendance_date', '>=', now()->subDays(7))
                ->select('attendance_date', DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'))
                ->groupBy('attendance_date')
                ->orderBy('attendance_date')
                ->get()
                ->map(fn($stat) => [
                    'date' => Carbon::parse($stat->attendance_date)->format('M d'),
                    'rate' => $stat->total > 0 ? round(($stat->present_count / $stat->total) * 100) : 100
                ]);

            // F. Syllabus Progress
            $syllabusProgress = $mySubjects->map(function($subject) use ($teacher, $academicYear) {
                $scheme = SchemeOfWork::where('subject_id', $subject['id'])
                    ->where('grade_level_id', $subject['grade_level_id'])
                    ->where('academic_year_id', $academicYear?->id)
                    ->first();
                $totalLessons = $scheme ? ($scheme->total_weeks * $scheme->lessons_per_week) : 0;
                $completedLessons = LessonPlan::where('teacher_id', $teacher->id)
                    ->where('subject_id', $subject['id'])
                    ->where('is_taught', true)
                    ->count();
                return [
                    'subject' => $subject['name'], 'class' => $subject['class_name'],
                    'progress' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
                    'completed' => $completedLessons, 'total' => $totalLessons
                ];
            });

            // G. Pending Tasks
            $pendingGradingCount = Assessment::where('teacher_id', $user->id)->where('status', 'published')->count();
            $pendingAssignmentsCount = AssignmentSubmission::whereHas('assignment', fn($q) => $q->where('teacher_id', $teacher->id))
                ->where('status', '!=', 'graded')->count();
            $pendingTasks = [
                ['title' => 'Assessments to Grade', 'count' => $pendingGradingCount, 'link' => '/assessments/grading', 'icon' => 'GraduationCap'],
                ['title' => 'Assignments to Review', 'count' => $pendingAssignmentsCount, 'link' => '/curriculum/assignments', 'icon' => 'ClipboardCheck'],
                ['title' => 'Upload Learning Resource', 'count' => null, 'link' => '/curriculum/resources/create', 'icon' => 'FilePlus'],
                ['title' => 'Create New Assignment', 'count' => null, 'link' => '/curriculum/assignments/create', 'icon' => 'PlusCircle'],
            ];

            // 3. Class Teacher Specific Data
            if ($isClassTeacher) {
                $myClass = SchoolClass::where('class_teacher_id', $user->id)->with(['stream', 'gradeLevel'])->first();
                if ($myClass) {
                    $classStudents = Student::where('current_class_id', $myClass->id)
                        ->orderBy('first_name')
                        ->get(['id', 'first_name', 'last_name', 'admission_number', 'photo', 'gender', 'status']);
                    
                    $classAssessments = Assessment::where('class_id', $myClass->id)
                        ->with(['subject', 'assessmentType'])
                        ->latest()
                        ->limit(5)
                        ->get();

                    $startOfWeek = Carbon::now()->startOfWeek();
                    $studentIds = $classStudents->pluck('id')->toArray();
                    $totalRecords = StudentAttendance::whereIn('student_id', $studentIds)->where('attendance_date', '>=', $startOfWeek)->count();
                    $presentRecords = StudentAttendance::whereIn('student_id', $studentIds)->where('attendance_date', '>=', $startOfWeek)->where('status', 'present')->count();
                    $classAttendanceRate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 95;

                    $classManagement = [
                        'class' => $myClass,
                        'students' => $classStudents,
                        'total_students' => $classStudents->count(),
                        'boys_count' => $classStudents->where('gender', 'male')->count(),
                        'girls_count' => $classStudents->where('gender', 'female')->count(),
                        'attendance_rate' => $classAttendanceRate,
                        'recent_assessments' => $classAssessments,
                    ];
                }
            }

            // 4. HOD Specific Data
            if ($isHod) {
                $department = \App\Models\Academic\Department::where('head_of_department_id', $teacher->id)->first();
                if ($department) {
                    $deptTeachers = Teacher::where('department_id', $department->id)->where('status', 'active')->get(['id', 'first_name', 'last_name', 'photo', 'email', 'user_id']);
                    $deptSubjects = \App\Models\Curriculum\Subject::where('department_id', $department->id)->where('is_active', true)->get();
                    $subjectIds = $deptSubjects->pluck('id')->toArray();
                    $deptAssessments = Assessment::whereIn('subject_id', $subjectIds)->with(['subject', 'class', 'assessmentType'])->latest()->limit(10)->get();
                    $deptClassIds = Assessment::whereIn('subject_id', $subjectIds)->distinct()->pluck('class_id')->toArray();
                    $deptStudentsCount = Student::whereIn('current_class_id', $deptClassIds)->count();

                    $departmentData = [
                        'department' => $department,
                        'teachers' => $deptTeachers,
                        'subjects' => $deptSubjects,
                        'total_students' => $deptStudentsCount,
                        'recent_assessments' => $deptAssessments,
                    ];
                }
            }
        }

        return Inertia::render('dashboards/TeacherDashboard', [
            'dashboardType' => 'teacher',
            'isClassTeacher' => $isClassTeacher,
            'isHod' => $isHod,
            'teacher' => $teacher,
            'myClasses' => $myClasses,
            'mySubjects' => $mySubjects,
            'totalStudents' => $totalStudents,
            'todaysTimetable' => $todaysTimetable,
            'recentAssessments' => $recentAssessments,
            'attendanceStats' => $attendanceStats,
            'syllabusProgress' => $syllabusProgress,
            'pendingTasks' => $pendingTasks,
            'classManagement' => $classManagement,
            'departmentData' => $departmentData,
            'academicYear' => $academicYear?->name,
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // PARENT DASHBOARD
    // ─────────────────────────────────────────
    private function parentDashboard()
    {
        $user = Auth::user();
        $guardian = Guardian::where('user_id', $user->id)->first();

        $children = collect();
        $childrenResults = collect();

        if ($guardian) {
            $children = $guardian->students()
                ->with(['currentClass'])
                ->get(['students.id', 'first_name', 'last_name', 'admission_number', 'photo', 'gender', 'current_class_id']);

            // Get recent results for each child
            $childIds = $children->pluck('id')->toArray();
            $childrenResults = \App\Models\Assessment\StudentAssessment::whereIn('student_id', $childIds)
                ->with(['assessment.subject', 'assessment.assessmentType'])
                ->latest('graded_at')
                ->limit(10)
                ->get();
        }

        return Inertia::render('dashboards/ParentDashboard', [
            'dashboardType' => 'parent',
            'guardian' => $guardian,
            'children' => $children,
            'childrenResults' => $childrenResults,
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // STUDENT DASHBOARD
    // ─────────────────────────────────────────
    private function studentDashboard()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->with('currentClass')->first();

        $myResults = collect();
        $upcomingAssessments = collect();

        if ($student) {
            $myResults = \App\Models\Assessment\StudentAssessment::where('student_id', $student->id)
                ->with(['assessment.subject', 'assessment.assessmentType'])
                ->latest('graded_at')
                ->limit(10)
                ->get();

            $upcomingAssessments = \App\Models\Assessment\Assessment::where('class_id', $student->current_class_id)
                ->where('assessment_date', '>=', now())
                ->with(['subject', 'assessmentType'])
                ->orderBy('assessment_date')
                ->limit(5)
                ->get();
        }

        return Inertia::render('dashboards/StudentDashboard', [
            'dashboardType' => 'student',
            'student' => $student,
            'myResults' => $myResults,
            'upcomingAssessments' => $upcomingAssessments,
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // FINANCE DASHBOARD
    // ─────────────────────────────────────────
    private function financeDashboard()
    {
        $feeCollection = 0;
        $pendingFees = 0;
        $todayPayments = 0;
        $monthlyPayments = 0;

        try {
            if (Schema::hasTable('fee_payments')) {
                $feeCollection = FeePayment::whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year)
                    ->sum('amount') ?? 0;

                $todayPayments = FeePayment::whereDate('payment_date', Carbon::today())->sum('amount') ?? 0;
                $monthlyPayments = FeePayment::whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year)
                    ->count();
            }

            if (Schema::hasTable('student_fees')) {
                $pendingFees = StudentFee::where('status', 'pending')
                    ->orWhere('status', 'partial')
                    ->sum(DB::raw('total_amount - paid_amount')) ?? 0;
            }
        } catch (\Exception $e) {
            Log::error('Finance dashboard error: ' . $e->getMessage());
        }

        return Inertia::render('dashboards/FinanceDashboard', [
            'dashboardType' => 'finance',
            'feeCollection' => $feeCollection,
            'pendingFees' => $pendingFees,
            'todayPayments' => $todayPayments,
            'monthlyPayments' => $monthlyPayments,
            'totalStudents' => Student::where('status', 'active')->count(),
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // SHARED PRIVATE METHODS (unchanged)
    // ─────────────────────────────────────────
    private function getStats(): array
    {
        try {
            $previousTermDate = Carbon::now()->subMonths(3)->toDateString();
            
            $learnerStats = Student::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN created_at < ? THEN 1 ELSE 0 END) as previous
            ", [$previousTermDate])->first();
            
            $totalStudents = $learnerStats->total;
            $studentGrowth = $learnerStats->previous > 0
                ? round((($totalStudents - $learnerStats->previous) / $learnerStats->previous) * 100, 1)
                : 0;

            $teacherStats = Teacher::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN created_at < ? THEN 1 ELSE 0 END) as previous
            ", [$previousTermDate])->first();
            
            $totalTeachers = $teacherStats->total;
            $teacherGrowth = $teacherStats->previous > 0
                ? round((($totalTeachers - $teacherStats->previous) / $teacherStats->previous) * 100, 1)
                : 0;

            $classStats = SchoolClass::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN created_at < ? THEN 1 ELSE 0 END) as previous
            ", [$previousTermDate])->first();
            
            $totalClasses = $classStats->total;
            $classGrowth = $classStats->previous > 0
                ? round((($totalClasses - $classStats->previous) / $classStats->previous) * 100, 1)
                : 0;

            $attendanceRate = $this->calculateAttendanceRate();

            $feeStats = (object) ['total' => 0, 'previous' => 0];
            try {
                $dbFeeStats = FeePayment::selectRaw("
                    SUM(CASE WHEN MONTH(payment_date) = ? AND YEAR(payment_date) = ? THEN amount ELSE 0 END) as total,
                    SUM(CASE WHEN MONTH(payment_date) = ? AND YEAR(payment_date) = ? THEN amount ELSE 0 END) as previous
                ", [Carbon::now()->month, Carbon::now()->year, Carbon::now()->subMonth()->month, Carbon::now()->subMonth()->year])->first();
                
                if ($dbFeeStats) {
                    $feeStats->total = $dbFeeStats->total;
                    $feeStats->previous = $dbFeeStats->previous;
                }
            } catch (\Exception $e) {
                Log::warning('Fee collection query failed: ' . $e->getMessage());
            }

            $feeCollection = $feeStats->total ?? 0;
            $collectionGrowth = ($feeStats->previous ?? 0) > 0
                ? round((($feeCollection - $feeStats->previous) / $feeStats->previous) * 100, 1)
                : 0;

            $pendingFees = 0;
            try {
                $pendingFees = StudentFee::sum('balance') ?? 0;
            } catch (\Exception $e) {
                Log::warning('Pending fees query failed: ' . $e->getMessage());
            }

            $totalDepartments = \App\Models\Academic\Department::count();
            $totalSubjects = Subject::count();

            return [
                'total_learners' => (int) $totalStudents,
                'learner_growth' => $studentGrowth,
                'total_teachers' => (int) $totalTeachers,
                'teacher_growth' => $teacherGrowth,
                'total_classes' => (int) $totalClasses,
                'class_growth' => $classGrowth,
                'attendance_rate' => $attendanceRate,
                'fee_collection' => (float) $feeCollection,
                'collection_growth' => $collectionGrowth,
                'pending_fees' => (float) $pendingFees,
                'total_departments' => (int) $totalDepartments,
                'total_subjects' => (int) $totalSubjects,
            ];
        } catch (\Exception $e) {
            Log::error('getStats error: ' . $e->getMessage());
            return $this->getZeroStats();
        }
    }

    private function getZeroStats(): array
    {
        return [
            'total_learners' => 0, 'learner_growth' => 0, 'total_teachers' => 0,
            'teacher_growth' => 0, 'total_classes' => 0, 'attendance_rate' => 0,
            'fee_collection' => 0, 'pending_fees' => 0, 'total_guardians' => 0,
            'total_subjects' => 0,
        ];
    }

    private function calculateAttendanceRate(): float
    {
        try {
            if (!Schema::hasTable('student_attendances')) return 0;
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $totalRecords = StudentAttendance::whereBetween('attendance_date', [$startOfWeek, $endOfWeek])->count();
            $presentRecords = StudentAttendance::whereBetween('attendance_date', [$startOfWeek, $endOfWeek])->where('status', 'present')->count();
            return $totalRecords === 0 ? 0 : round(($presentRecords / $totalRecords) * 100, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getEnrollmentTrends(): array
    {
        try {
            $months = []; $newStudents = []; $withdrawals = [];
            $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
            
            $admissions = Student::where('admission_date', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(admission_date, '%Y-%m') as month_key, COUNT(*) as count")
                ->groupBy('month_key')
                ->pluck('count', 'month_key');

            $withdrawalStats = Student::where('withdrawal_date', '>=', $sixMonthsAgo)
                ->where('status', 'withdrawn')
                ->selectRaw("DATE_FORMAT(withdrawal_date, '%Y-%m') as month_key, COUNT(*) as count")
                ->groupBy('month_key')
                ->pluck('count', 'month_key');

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $key = $date->format('Y-m');
                $months[] = $date->format('M');
                $newStudents[] = $admissions[$key] ?? 0;
                $withdrawals[] = $withdrawalStats[$key] ?? 0;
            }
            return ['labels' => $months, 'datasets' => [
                ['label' => 'New Students', 'data' => $newStudents, 'color' => 'rgb(59, 130, 246)'],
                ['label' => 'Withdrawals', 'data' => $withdrawals, 'color' => 'rgb(239, 68, 68)'],
            ]];
        } catch (\Exception $e) { return ['labels' => [], 'datasets' => []]; }
    }

    private function getClassPerformance(): array
    {
        try {
            $currentTerm = AcademicTerm::current()->first();
            if (!$currentTerm) return $this->getEmptyClassPerformance();

            $performanceData = DB::table('student_assessments')
                ->join('assessments', 'student_assessments.assessment_id', '=', 'assessments.id')
                ->join('classes', 'assessments.class_id', '=', 'classes.id')
                ->where('assessments.academic_term_id', $currentTerm->id)
                ->whereNotNull('student_assessments.percentage');

            // Manual scoping for performance data
            $schoolId = Auth::user()->school_id ?? session('viewing_school_id');
            if ($schoolId) {
                $performanceData->where('assessments.school_id', $schoolId);
            }

            $results = $performanceData->select('classes.name', DB::raw('AVG(student_assessments.percentage) as average'))
                ->groupBy('classes.id', 'classes.name')
                ->orderBy('average', 'desc')
                ->limit(6)
                ->get();

            if ($results->isEmpty()) return $this->getEmptyClassPerformance();

            return [
                'labels' => $results->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Average Score %',
                        'data' => $results->pluck('average')->map(fn($v) => round($v, 1))->toArray(),
                        'color' => 'rgb(16, 185, 129)'
                    ],
                ]
            ];
        } catch (\Exception $e) {
            return $this->getEmptyClassPerformance();
        }
    }

    private function getEmptyClassPerformance(): array
    {
        return ['labels' => [], 'datasets' => [
            ['label' => 'Average Score', 'data' => [], 'color' => 'rgb(16, 185, 129)'],
        ]];
    }

    private function getDepartmentPerformance(): array
    {
        try {
            $performance = DB::table('student_assessments')
                ->join('assessments', 'student_assessments.assessment_id', '=', 'assessments.id')
                ->join('subjects', 'assessments.subject_id', '=', 'subjects.id')
                ->join('departments', 'subjects.department_id', '=', 'departments.id')
                ->select('departments.name', DB::raw('AVG(student_assessments.percentage) as average'))
                ->groupBy('departments.id', 'departments.name')
                ->orderBy('average', 'desc')
                ->get();

            return [
                'labels' => $performance->pluck('name')->toArray(),
                'data' => $performance->pluck('average')->map(fn($v) => round($v, 1))->toArray(),
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }

    private function getSubjectAnalytics(): array
    {
        try {
            return DB::table('subjects')
                ->leftJoin('assessments', 'subjects.id', '=', 'assessments.subject_id')
                ->leftJoin('student_assessments', 'assessments.id', '=', 'student_assessments.assessment_id')
                ->select(
                    'subjects.name',
                    'subjects.code',
                    DB::raw('COUNT(DISTINCT assessments.id) as assessment_count'),
                    DB::raw('AVG(student_assessments.percentage) as avg_score')
                )
                ->groupBy('subjects.id', 'subjects.name', 'subjects.code')
                ->orderBy('avg_score', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($s) => (array)$s)
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getNewStudentsThisMonth(): array
    {
        try {
            return Student::whereMonth('admission_date', Carbon::now()->month)
                ->whereYear('admission_date', Carbon::now()->year)
                ->with('currentClass:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->full_name,
                    'class' => $s->currentClass?->name ?? 'Unassigned',
                    'date' => $s->admission_date ? $s->admission_date->format('M d') : 'N/A',
                    'photo' => $s->photo_url,
                ])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getGradeDistribution(): array
    {
        try {
            $grades = \App\Models\Academic\GradeLevel::withCount('students')->get();
            return [
                'labels' => $grades->pluck('name')->toArray(),
                'data' => $grades->pluck('students_count')->toArray(),
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }

    private function getClassCapacity(): array
    {
        try {
            // Pick classes that actually have students first to ensure the graph shows "real" data
            $classes = SchoolClass::withCount('activeStudents')
                ->where('is_active', true)
                ->orderBy('active_students_count', 'desc')
                ->limit(8)
                ->get();

            // Fallback if no classes have students, just get any 8 classes
            if ($classes->sum('active_students_count') == 0) {
                $classes = SchoolClass::limit(8)->get();
                $classes->loadCount('activeStudents');
            }

            return [
                'labels' => $classes->pluck('name')->toArray(),
                'actual' => $classes->pluck('active_students_count')->toArray(),
                'expected' => $classes->pluck('capacity')->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('getClassCapacity error: ' . $e->getMessage());
            return ['labels' => [], 'actual' => [], 'expected' => []];
        }
    }

    private function getStreamAnalytics(): array
    {
        try {
            return DB::table('streams')
                ->leftJoin('classes', 'streams.id', '=', 'classes.stream_id')
                ->leftJoin('students', 'classes.id', '=', 'students.current_class_id')
                ->select('streams.name', DB::raw('COUNT(students.id) as student_count'))
                ->groupBy('streams.id', 'streams.name')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getGenderDistribution(): array
    {
        try {
            $boys = Student::active()->where('gender', 'male')->count();
            $girls = Student::active()->where('gender', 'female')->count();
            if ($boys === 0 && $girls === 0) return ['labels' => ['Boys', 'Girls'], 'datasets' => [['label' => 'Learners', 'data' => [0, 0]]]];
            return ['labels' => ['Boys', 'Girls'], 'datasets' => [['label' => 'Learners', 'data' => [$boys, $girls]]]];
        } catch (\Exception $e) { return ['labels' => ['Boys', 'Girls'], 'datasets' => [['label' => 'Learners', 'data' => [0, 0]]]]; }
    }

    private function getWeeklyAttendance(): array
    {
        try {
            if (!Schema::hasTable('student_attendances')) return $this->getEmptyWeeklyAttendance();
            
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            $presentData = []; $absentData = [];
            
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();

            $stats = StudentAttendance::whereBetween('attendance_date', [$startOfWeek, $endOfWeek])
                ->selectRaw("
                    attendance_date as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                ")
                ->groupBy('attendance_date')
                ->get()
                ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));
            
            $currentDate = Carbon::now()->startOfWeek();
            for ($i = 0; $i < 5; $i++) {
                $key = $currentDate->toDateString();
                $item = $stats[$key] ?? null;
                
                if ($item && $item->total > 0) {
                    $presentRate = round(($item->present / $item->total) * 100, 1);
                    $absentRate = 100 - $presentRate;
                } else {
                    $presentRate = 0;
                    $absentRate = 0;
                }
                
                $presentData[] = $presentRate;
                $absentData[] = $absentRate;
                $currentDate->addDay();
            }
            
            return ['labels' => $days, 'datasets' => [
                ['label' => 'Present %', 'data' => $presentData, 'color' => 'rgb(16, 185, 129)'],
                ['label' => 'Absent %', 'data' => $absentData, 'color' => 'rgb(239, 68, 68)'],
            ]];
        } catch (\Exception $e) {
            return $this->getEmptyWeeklyAttendance();
        }
    }

    private function getEmptyWeeklyAttendance(): array
    {
        return ['labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], 'datasets' => [
            ['label' => 'Present %', 'data' => [0, 0, 0, 0, 0], 'color' => 'rgb(16, 185, 129)'],
            ['label' => 'Absent %', 'data' => [0, 0, 0, 0, 0], 'color' => 'rgb(239, 68, 68)'],
        ]];
    }

    private function getRecentEnrollments(): array
    {
        try {
            return Student::with(['currentClass:id,name'])
                ->latest('admission_date')
                ->limit(5)
                ->get()
                ->map(fn($student) => [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'class_name' => $student->currentClass?->name ?? 'Unassigned',
                    'date' => $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A',
                    'status' => $student->status,
                    'initials' => strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1))
                ])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRecentActivities(): array
    {
        try {
            // Fetch recent activity logs that are relevant to this school
            // Using spatie/laravel-activitylog if available
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                return \Spatie\Activitylog\Models\Activity::latest()
                    ->limit(10)
                    ->get()
                    ->map(fn($activity) => [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'user' => $activity->causer?->name ?? 'System',
                        'time' => $activity->created_at->diffForHumans(),
                        'icon' => $this->getActivityIcon($activity->description),
                        'color' => $this->getActivityColor($activity->description),
                    ])
                    ->toArray();
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getActivityIcon($description): string
    {
        $desc = strtolower($description);
        if (str_contains($desc, 'student')) return 'Users';
        if (str_contains($desc, 'teacher')) return 'GraduationCap';
        if (str_contains($desc, 'fee') || str_contains($desc, 'payment')) return 'CreditCard';
        if (str_contains($desc, 'class')) return 'School';
        return 'Activity';
    }

    private function getActivityColor($description): string
    {
        $desc = strtolower($description);
        if (str_contains($desc, 'created') || str_contains($desc, 'added')) return 'text-emerald-500';
        if (str_contains($desc, 'updated') || str_contains($desc, 'modified')) return 'text-blue-500';
        if (str_contains($desc, 'deleted') || str_contains($desc, 'removed')) return 'text-red-500';
        return 'text-slate-500';
    }

    private function getUpcomingEvents(): array
    {
        try {
            // Using the new scoped Event model
            return Event::upcoming()
                ->limit(5)
                ->get()
                ->map(fn($event) => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('M d, Y'),
                    'time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : 'All Day',
                    'type' => $event->event_type ?? 'General',
                    'location' => $event->location ?? 'School Grounds',
                ])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getNotificationsCount(): int
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $query = DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->where('notifiable_type', get_class($user))
                    ->whereNull('read_at');
                
                // Manual scoping for notifications if school_id exists
                if (Schema::hasColumn('notifications', 'school_id')) {
                    $schoolId = $user->school_id ?? session('viewing_school_id');
                    if ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }
                }
                
                return $query->count();
            }
            return 0;
        } catch (\Exception $e) { return 0; }
    }

    private function getDefaultStats(): array
    {
        return $this->getZeroStats();
    }

    private function getDefaultEnrollmentTrends(): array
    {
        return ['labels' => [], 'datasets' => []];
    }

    private function getDefaultClassPerformance(): array
    {
        return $this->getEmptyClassPerformance();
    }

    private function getDefaultGenderDistribution(): array
    {
        return ['labels' => ['Boys', 'Girls'], 'datasets' => [['label' => 'Learners', 'data' => [0, 0]]]];
    }

    private function getDefaultWeeklyAttendance(): array
    {
        return $this->getEmptyWeeklyAttendance();
    }

    private function getDefaultRecentActivities(): array
    {
        return [];
    }

    private function getDefaultUpcomingEvents(): array
    {
        return [];
    }
}
