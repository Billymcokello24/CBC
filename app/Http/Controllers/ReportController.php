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
        
        // Mocking CBC Data structure based on user request
        // In a real scenario, this would query StudentCompetencyRating, Assessment, etc.
        $reportData = [
            'learner' => [
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'grade' => $student->currentClass?->gradeLevel?->name ?? 'N/A',
                'term' => 'Term 1 2026',
                'class' => $student->currentClass?->name ?? 'N/A',
            ],
            'performance' => [
                [
                    'area' => 'Mathematics',
                    'competencies' => [
                        ['task' => 'Numbers and operations', 'level' => 4, 'feedback' => 'Exceptional ability in Mental Math'],
                        ['task' => 'Measurements', 'level' => 3, 'feedback' => 'Understands standard units well'],
                        ['task' => 'Geometry', 'level' => 3, 'feedback' => 'Identifies 3D shapes correctly'],
                    ],
                    'remarks' => 'Very strong logic skills.'
                ],
                [
                    'area' => 'English Language',
                    'competencies' => [
                        ['task' => 'Reading Comprehension', 'level' => 4, 'feedback' => 'Reads fluently with expression'],
                        ['task' => 'Writing Skills', 'level' => 3, 'feedback' => 'Good creative writing flow'],
                        ['task' => 'Listening & Speaking', 'level' => 4, 'feedback' => 'Confident in public speaking'],
                    ],
                    'remarks' => 'Enjoys classroom discussions.'
                ],
                [
                    'area' => 'Science & Technology',
                    'competencies' => [
                        ['task' => 'Environmental awareness', 'level' => 3, 'feedback' => 'Knowledgeable about conservation'],
                        ['task' => 'Digital literacy', 'level' => 2, 'feedback' => 'Needs more practice with mouse skills'],
                    ],
                    'remarks' => 'Curious and inquisitive learner.'
                ]
            ],
            'traits' => [
                ['trait' => 'Communication & Collaboration', 'level' => 4],
                ['trait' => 'Critical Thinking & Problem Solving', 'level' => 3],
                ['trait' => 'Creativity & Imagination', 'level' => 4],
                ['trait' => 'Citizenship', 'level' => 3],
                ['trait' => 'Learning to Learn', 'level' => 3],
            ],
            'comments' => [
                'teacher' => 'A very disciplined and hardworking student. Keep it up!',
                'principal' => 'Excellent progress. Focus more on digital skills next term.',
                'attendance' => '45/45 Days Present'
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
        
        // Mock data for subject performance visualization
        $analysis = [
            'subject' => 'Mathematics',
            'class' => 'Grade 4 East',
            'stats' => [
                'average_score' => 74.5,
                'total_learners' => 35,
                'exceeding' => 8,
                'meeting' => 15,
                'approaching' => 9,
                'below' => 3,
            ],
            'learners' => [
                ['name' => 'John Doe', 'adm' => '1001', 'level' => 4, 'trend' => 'up', 'participation' => 'High'],
                ['name' => 'Jane Smith', 'adm' => '1002', 'level' => 3, 'trend' => 'stable', 'participation' => 'Medium'],
                ['name' => 'Ben Carson', 'adm' => '1003', 'level' => 2, 'trend' => 'down', 'participation' => 'Low'],
                ['name' => 'Alice Wong', 'adm' => '1004', 'level' => 3, 'trend' => 'up', 'participation' => 'Medium'],
                ['name' => 'Robert Fox', 'adm' => '1005', 'level' => 1, 'trend' => 'stable', 'participation' => 'High'],
            ],
            'trends' => [
                'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                'data' => [65, 68, 70, 72, 74.5]
            ],
            'interventions' => [
                ['area' => 'Multiplication Tables', 'count' => 12, 'priority' => 'High'],
                ['area' => 'Complex Subtraction', 'count' => 5, 'priority' => 'Medium'],
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
        
        // Mock data for class summary visualization
        $analysis = [
            'className' => 'Grade 4 East',
            'grade' => 'Grade 4',
            'school' => 'Institutional Command Center',
            'term' => 'Term 1 2026',
            'stats' => [
                'total_students' => 38,
                'boys' => 18,
                'girls' => 20,
                'average_mastery' => 78,
                'attendance_rate' => 96.5,
                'published_reports' => 32,
            ],
            'subject_mastery' => [
                ['subject' => 'Mathematics', 'mastery' => 82, 'trend' => 'up'],
                ['subject' => 'English', 'mastery' => 75, 'trend' => 'stable'],
                ['subject' => 'Kiswahili', 'mastery' => 71, 'trend' => 'down'],
                ['subject' => 'Science', 'mastery' => 88, 'trend' => 'up'],
                ['subject' => 'Social Studies', 'mastery' => 68, 'trend' => 'stable'],
                ['subject' => 'CRE', 'mastery' => 92, 'trend' => 'up'],
            ],
            'top_performers' => [
                ['name' => 'Alice Kemunto', 'score' => 94, 'avatar' => 'AK'],
                ['name' => 'Brian Omondi', 'score' => 91, 'avatar' => 'BO'],
                ['name' => 'Catherine Wangechi', 'score' => 89, 'avatar' => 'CW'],
                ['name' => 'David Mutua', 'score' => 88, 'avatar' => 'DM'],
            ],
            'distribution' => [
                ['label' => 'EE', 'value' => 8, 'color' => 'emerald'],
                ['label' => 'ME', 'value' => 12, 'color' => 'blue'],
                ['label' => 'AE', 'value' => 10, 'color' => 'amber'],
                ['label' => 'BE', 'value' => 8, 'color' => 'rose'],
            ]
        ];

        return response()->json($analysis);
    }
}
