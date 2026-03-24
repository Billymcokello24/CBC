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

        if ($user->hasRole('hod')) {
            return $this->hodDashboard();
        }

        if ($user->hasRole('class_teacher')) {
            return $this->classTeacherDashboard();
        }

        if ($user->hasAnyRole(['teacher'])) {
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

            return Inertia::render('Dashboard', [
                'dashboardType' => 'admin',
                'stats' => $this->getStats(),
                'enrollmentTrends' => $this->getEnrollmentTrends(),
                'classPerformance' => $this->getClassPerformance(),
                'genderDistribution' => $this->getGenderDistribution(),
                'weeklyAttendance' => $this->getWeeklyAttendance(),
                'recentActivities' => $this->getRecentActivities(),
                'upcomingEvents' => $this->getUpcomingEvents(),
                'notificationsCount' => $this->getNotificationsCount(),
                'currentYear' => $currentYear,
                'currentTerm' => $currentTerm,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return Inertia::render('Dashboard', [
                'dashboardType' => 'admin',
                'stats' => $this->getDefaultStats(),
                'enrollmentTrends' => $this->getDefaultEnrollmentTrends(),
                'classPerformance' => $this->getDefaultClassPerformance(),
                'genderDistribution' => $this->getDefaultGenderDistribution(),
                'weeklyAttendance' => $this->getDefaultWeeklyAttendance(),
                'recentActivities' => $this->getDefaultRecentActivities(),
                'upcomingEvents' => $this->getDefaultUpcomingEvents(),
                'notificationsCount' => 0,
                'currentYear' => null,
                'currentTerm' => null,
            ]);
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

        $myClasses = collect();
        $mySubjects = collect();
        $recentAssessments = [];
        $totalStudents = 0;
        $todaysTimetable = [];
        $attendanceStats = [];

        if ($teacher) {
            // 1. Get class-teacher assignments
            $classTeacherClasses = SchoolClass::where('class_teacher_id', $user->id)
                ->with(['gradeLevel', 'stream', 'activeStudents'])
                ->get();

            // 2. Get subject assignments (classes + subjects)
            $subjectAssignments = \App\Models\TeacherSubject::where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->with(['subject', 'schoolClass.gradeLevel', 'schoolClass.stream'])
                ->get();

            // Normalize classes
            $myClasses = $classTeacherClasses->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'grade' => $c->gradeLevel?->name,
                'stream' => $c->stream?->name,
                'is_class_teacher' => true,
                'learner_count' => $c->active_students_count ?? $c->students()->where('status', 'active')->count()
            ])->merge(
                $subjectAssignments->map(fn($a) => [
                    'id' => $a->schoolClass?->id,
                    'name' => $a->schoolClass?->name,
                    'code' => $a->schoolClass?->code,
                    'grade' => $a->schoolClass?->gradeLevel?->name,
                    'stream' => $a->schoolClass?->stream?->name,
                    'is_class_teacher' => false,
                    'learner_count' => $a->schoolClass?->students()->where('status', 'active')->count() ?? 0
                ])
            )->unique('id')->filter()->values();

            // Normalize subjects
            $mySubjects = $subjectAssignments->map(fn($a) => [
                'id' => $a->subject?->id,
                'name' => $a->subject?->name,
                'code' => $a->subject?->code,
                'class_name' => $a->schoolClass?->name,
                'is_primary' => $a->is_primary_teacher
            ])->unique(fn($s) => $s['id'] . '-' . $s['class_name'])->values();

            $classIds = $myClasses->pluck('id')->toArray();
            $totalLearnersCount = Student::whereIn('current_class_id', $classIds)->where('status', 'active')->count();

            // 3. Today's timetable
            $dayOfWeek = strtolower(date('l'));
            $todaysTimetable = TimetableSlot::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->with(['subject', 'periodDefinition', 'timetable.schoolClass'])
                ->orderBy('start_time')
                ->get()
                ->map(fn($slot) => [
                    'id' => $slot->id,
                    'subject' => $slot->subject?->name,
                    'class' => $slot->timetable?->schoolClass?->name,
                    'room' => $slot->room_number,
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'duration' => $slot->periodDefinition?->duration_minutes ?? $slot->start_time->diffInMinutes($slot->end_time),
                    'status' => 'scheduled'
                ]);

            // 4. Recent Assessments (by this teacher)
            $recentAssessments = Assessment::where('teacher_id', $user->id)
                ->with(['subject', 'class', 'assessmentType'])
                ->latest()
                ->limit(5)
                ->get();

            // 5. Attendance Summary for their classes (Last 7 days)
            $attendanceStats = StudentAttendance::whereIn('class_id', $classIds)
                ->where('attendance_date', '>=', now()->subDays(7))
                ->select('attendance_date', DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'))
                ->groupBy('attendance_date')
                ->orderBy('attendance_date')
                ->get()
                ->map(fn($stat) => [
                    'date' => $stat->attendance_date->format('M d'),
                    'rate' => $stat->total > 0 ? round(($stat->present_count / $stat->total) * 100) : 100
                ]);
        }

        return Inertia::render('dashboards/TeacherDashboard', [
            'dashboardType' => 'teacher',
            'teacher' => $teacher,
            'myClasses' => $myClasses,
            'mySubjects' => $mySubjects,
            'totalStudents' => $totalStudents,
            'todaysTimetable' => $todaysTimetable,
            'recentAssessments' => $recentAssessments,
            'attendanceStats' => $attendanceStats,
            'academicYear' => $academicYear?->name,
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // CLASS TEACHER DASHBOARD
    // ─────────────────────────────────────────
    private function classTeacherDashboard()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        // Find the class(es) this user is class teacher of
        $myClass = SchoolClass::where('class_teacher_id', $user->id)->first();

        $students = collect();
        $recentAssessments = collect();
        $attendanceRate = 0;

        if ($myClass) {
            $students = Student::where('current_class_id', $myClass->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'admission_number', 'photo', 'gender', 'status']);

            $recentAssessments = \App\Models\Assessment\Assessment::where('class_id', $myClass->id)
                ->with(['subject', 'assessmentType'])
                ->latest()
                ->limit(5)
                ->get();

            // Calculate attendance rate for this class this week
            if (Schema::hasTable('student_attendances')) {
                $startOfWeek = Carbon::now()->startOfWeek();
                $studentIds = $students->pluck('id')->toArray();
                $totalRecords = StudentAttendance::whereIn('student_id', $studentIds)
                    ->where('date', '>=', $startOfWeek)
                    ->count();
                $presentRecords = StudentAttendance::whereIn('student_id', $studentIds)
                    ->where('date', '>=', $startOfWeek)
                    ->where('status', 'present')
                    ->count();
                $attendanceRate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 95;
            }
        }

        return Inertia::render('dashboards/ClassTeacherDashboard', [
            'dashboardType' => 'class_teacher',
            'teacher' => $teacher,
            'myClass' => $myClass,
            'students' => $students,
            'totalStudents' => $students->count(),
            'boysCount' => $students->where('gender', 'male')->count(),
            'girlsCount' => $students->where('gender', 'female')->count(),
            'attendanceRate' => $attendanceRate,
            'recentAssessments' => $recentAssessments,
            'notificationsCount' => $this->getNotificationsCount(),
        ]);
    }

    // ─────────────────────────────────────────
    // HOD DASHBOARD
    // ─────────────────────────────────────────
    private function hodDashboard()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $department = $teacher?->department;

        $departmentTeachers = collect();
        $departmentSubjects = collect();
        $recentAssessments = collect();
        $totalStudents = 0;

        if ($department) {
            $departmentTeachers = Teacher::where('department_id', $department->id)
                ->where('status', 'active')
                ->get(['id', 'first_name', 'last_name', 'photo', 'email', 'user_id']);

            // Get subjects in this department
            $departmentSubjects = \App\Models\Curriculum\Subject::where('department_id', $department->id)
                ->where('is_active', true)
                ->get();

            $subjectIds = $departmentSubjects->pluck('id')->toArray();

            // Recent assessments for department subjects
            $recentAssessments = \App\Models\Assessment\Assessment::whereIn('subject_id', $subjectIds)
                ->with(['subject', 'class', 'assessmentType'])
                ->latest()
                ->limit(10)
                ->get();

            // Students in classes that have these subjects
            $classIds = \App\Models\Assessment\Assessment::whereIn('subject_id', $subjectIds)
                ->distinct()
                ->pluck('class_id')
                ->toArray();
            $totalStudents = Student::whereIn('current_class_id', $classIds)->count();
        }

        return Inertia::render('dashboards/HodDashboard', [
            'dashboardType' => 'hod',
            'teacher' => $teacher,
            'department' => $department,
            'departmentTeachers' => $departmentTeachers,
            'departmentSubjects' => $departmentSubjects,
            'totalStudents' => $totalStudents,
            'recentAssessments' => $recentAssessments,
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
            $totalStudents = Student::active()->count();
            $previousTermDate = Carbon::now()->subMonths(3);
            
            $previousStudents = Student::active()
                ->where('created_at', '<', $previousTermDate)
                ->count();
            
            $studentGrowth = $previousStudents > 0
                ? round((($totalStudents - $previousStudents) / $previousStudents) * 100, 1)
                : 0;

            $totalTeachers = Teacher::active()->count();
            $previousTeachersCount = Teacher::active()
                ->where('created_at', '<', $previousTermDate)
                ->count();
            
            $teacherGrowth = $previousTeachersCount > 0
                ? round((($totalTeachers - $previousTeachersCount) / $previousTeachersCount) * 100, 1)
                : 0;

            $totalClasses = SchoolClass::where('is_active', true)->count();
            $attendanceRate = $this->calculateAttendanceRate();

            $feeCollection = 0;
            if (Schema::hasTable('fee_payments')) {
                $feeCollection = FeePayment::whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year)
                    ->sum('amount') ?? 0;
            }

            $pendingFees = 0;
            if (Schema::hasTable('student_fees')) {
                $pendingFees = StudentFee::whereIn('status', ['pending', 'partial'])
                    ->sum(DB::raw('total_amount - paid_amount')) ?? 0;
            }

            $totalGuardians = Guardian::where('is_active', true)->count();
            $totalSubjects = \App\Models\Curriculum\Subject::where('is_active', true)->count();

            return [
                'total_learners' => $totalStudents,
                'learner_growth' => $studentGrowth,
                'total_teachers' => $totalTeachers,
                'teacher_growth' => $teacherGrowth,
                'total_classes' => $totalClasses,
                'attendance_rate' => $attendanceRate,
                'fee_collection' => $feeCollection,
                'pending_fees' => $pendingFees,
                'total_guardians' => $totalGuardians,
                'total_subjects' => $totalSubjects,
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
            $totalRecords = StudentAttendance::whereBetween('date', [$startOfWeek, $endOfWeek])->count();
            $presentRecords = StudentAttendance::whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'present')->count();
            return $totalRecords === 0 ? 0 : round(($presentRecords / $totalRecords) * 100, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getEnrollmentTrends(): array
    {
        try {
            $months = []; $newStudents = []; $withdrawals = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->format('M');
                $newStudents[] = Student::whereMonth('admission_date', $date->month)->whereYear('admission_date', $date->year)->count();
                $withdrawals[] = Student::whereMonth('withdrawal_date', $date->month)->whereYear('withdrawal_date', $date->year)->where('status', 'withdrawn')->count();
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
                ->join('school_classes', 'assessments.class_id', '=', 'school_classes.id')
                ->where('assessments.academic_term_id', $currentTerm->id)
                ->whereNotNull('student_assessments.percentage');

            // Manual scoping for performance data
            $schoolId = Auth::user()->school_id ?? session('viewing_school_id');
            if ($schoolId) {
                $performanceData->where('assessments.school_id', $schoolId);
            }

            $results = $performanceData->select('school_classes.name', DB::raw('AVG(student_assessments.percentage) as average'))
                ->groupBy('school_classes.id', 'school_classes.name')
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
            $presentData = [];
            $absentData = [];
            
            $startOfWeek = Carbon::now()->startOfWeek();
            
            for ($i = 0; $i < 5; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $total = StudentAttendance::whereDate('date', $date)->count();
                $present = StudentAttendance::whereDate('date', $date)->where('status', 'present')->count();
                
                if ($total > 0) {
                    $presentRate = round(($present / $total) * 100, 1);
                    $absentRate = 100 - $presentRate;
                } else {
                    $presentRate = 0;
                    $absentRate = 0;
                }
                
                $presentData[] = $presentRate;
                $absentData[] = $absentRate;
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
}
