<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentType;
use App\Models\Assessment\GradingScale;
use App\Models\Assessment\ReportCard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('assessments/Index', [
            'assessments' => Assessment::with(['class', 'subject', 'teacher', 'assessmentType'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        $schoolId = auth()->user()->school_id;

        return Inertia::render('assessments/Create', [
            'assessmentTypes' => AssessmentType::where('school_id', $schoolId)->where('is_active', true)->get(),
            'gradingScales' => GradingScale::where('school_id', $schoolId)->where('is_active', true)->get(),
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
            'subjects' => \App\Models\Curriculum\Subject::whereHas('schoolSubjects', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->where('is_offered', true);
            })->get(),
            'rubrics' => \App\Models\Assessment\Rubric::where('school_id', $schoolId)->where('is_active', true)->with(['assessmentType', 'criteria.levels'])->get(),
        ]);
    }

    public function gradingIndex(): Response
    {
        $schoolId = auth()->user()->school_id;
        
        $assessments = Assessment::where('school_id', $schoolId)
            ->with(['class', 'subject', 'assessmentType'])
            ->whereIn('status', ['published', 'in_progress', 'completed'])
            ->latest()
            ->paginate(20);

        return Inertia::render('assessments/GradingIndex', [
            'assessments' => $assessments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'rubric_id' => 'required|exists:rubrics,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'grading_scale_id' => 'nullable|exists:grading_scales,id',
            'assessment_date' => 'required|date',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,scheduled,published',
        ]);

        $assessment = new Assessment();
        $assessment->fill($validated);
        $assessment->school_id = auth()->user()->school_id;
        $assessment->teacher_id = auth()->user()->id; // Assuming the logged in user is the teacher
        $assessment->created_by = auth()->user()->id;
        
        // Default academic year and term - should be fetched from active session
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();
        
        $assessment->academic_year_id = $activeYear?->id;
        $assessment->academic_term_id = $activeTerm?->id;
        
        $assessment->save();

        return redirect()->route('assessments.index')->with('success', 'Assessment created successfully.');
    }

    public function grading(int $id): Response
    {
        $assessment = Assessment::with(['class', 'subject', 'assessmentType', 'gradingScale.descriptors'])
            ->findOrFail($id);

        $students = \App\Models\Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'admission_no', 'photo']);

        $results = \App\Models\Assessment\StudentAssessment::where('assessment_id', $id)
            ->get()
            ->keyBy('student_id');

        return Inertia::render('assessments/Grading', [
            'assessment' => $assessment,
            'students' => $students,
            'existingResults' => $results,
        ]);
    }

    public function storeGrading(Request $request, int $id)
    {
        $assessment = Assessment::with('gradingScale.descriptors')->findOrFail($id);
        
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.marks_obtained' => 'nullable|numeric|min:0',
            'results.*.feedback' => 'nullable|string',
            'results.*.is_absent' => 'required|boolean',
        ]);

        foreach ($validated['results'] as $result) {
            $marks = $result['marks_obtained'];
            $gradeDescriptor = null;
            $gradeLevel = null;

            if ($marks !== null && !$result['is_absent']) {
                $gradeDescriptor = $assessment->gradingScale->descriptors
                    ->where('min_score', '<=', $marks)
                    ->where('max_score', '>=', $marks)
                    ->first();
                $gradeLevel = $gradeDescriptor?->level_code;
            }

            \App\Models\Assessment\StudentAssessment::updateOrCreate(
                ['assessment_id' => $id, 'student_id' => $result['student_id']],
                [
                    'marks_obtained' => $marks,
                    'percentage' => $marks ? ($marks / $assessment->total_marks) * 100 : null,
                    'grade_descriptor_id' => $gradeDescriptor?->id,
                    'grade_level' => $gradeLevel,
                    'feedback' => $result['feedback'],
                    'is_absent' => $result['is_absent'],
                    'graded_at' => now(),
                    'graded_by' => auth()->user()->id,
                ]
            );
        }

        return redirect()->route('assessments.index')->with('success', 'Grades saved successfully.');
    }

    public function reportCards(): Response
    {
        $schoolId = auth()->user()->school_id;
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        $classes = \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get();
        
        // Fetch students for the first class as default if needed, or allow filtering
        $students = \App\Models\Student::where('school_id', $schoolId)->with('currentClass')->paginate(20);
        
        return Inertia::render('assessments/ReportCards', [
            'classes' => $classes,
            'students' => $students,
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
        ]);
    }

    public function showReport(int $studentId): Response
    {
        $student = \App\Models\Student::with('currentClass')->findOrFail($studentId);
        $schoolId = auth()->user()->school_id;
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        // High-quality sample data matching CBC requirements
        $results = [
            [
                'subject' => 'Language Activities', 
                'opening' => ['score' => 78, 'level' => 'ME'], 
                'mid' => ['score' => 82, 'level' => 'EE'], 
                'end' => ['score' => 80, 'level' => 'ME'], 
                'average' => ['score' => 80, 'level' => 'ME'],
                'comments' => 'Excellent communication skills and vocabulary.'
            ],
            [
                'subject' => 'Mathematical Activities', 
                'opening' => ['score' => 65, 'level' => 'ME'], 
                'mid' => ['score' => 70, 'level' => 'ME'], 
                'end' => ['score' => 72, 'level' => 'ME'], 
                'average' => ['score' => 69, 'level' => 'ME'],
                'comments' => 'Strong logical reasoning. Keep practicing geometry.'
            ],
            [
                'subject' => 'Environmental Activities', 
                'opening' => ['score' => 85, 'level' => 'EE'], 
                'mid' => ['score' => 88, 'level' => 'EE'], 
                'end' => ['score' => 90, 'level' => 'EE'], 
                'average' => ['score' => 88, 'level' => 'EE'],
                'comments' => 'Highly curious and protective of the environment.'
            ],
            [
                'subject' => 'Psychomotor Activities', 
                'opening' => ['score' => 70, 'level' => 'ME'], 
                'mid' => ['score' => 75, 'level' => 'ME'], 
                'end' => ['score' => 80, 'level' => 'ME'], 
                'average' => ['score' => 75, 'level' => 'ME'],
                'comments' => 'Great coordination and physical agility.'
            ],
            [
                'subject' => 'Creative Arts', 
                'opening' => ['score' => 92, 'level' => 'EE'], 
                'mid' => ['score' => 95, 'level' => 'EE'], 
                'end' => ['score' => 94, 'level' => 'EE'], 
                'average' => ['score' => 94, 'level' => 'EE'],
                'comments' => 'Remarkable artistic talent and creativity.'
            ],
        ];

        return Inertia::render('assessments/ReportForm', [
            'student' => $student,
            'academicYear' => $activeYear,
            'academicTerm' => $activeTerm,
            'results' => $results,
            'performanceLevels' => [
                ['code' => 'EE', 'label' => 'Exceeding Expectation', 'range' => '80-100'],
                ['code' => 'ME', 'label' => 'Meeting Expectation', 'range' => '60-79'],
                ['code' => 'AE', 'label' => 'Approaching Expectation', 'range' => '40-59'],
                ['code' => 'BE', 'label' => 'Below Expectation', 'range' => '0-39'],
            ],
            'attendance' => ['days_present' => 85, 'total_days' => 90],
        ]);
    }

    public function rubrics(): Response
    {
        $schoolId = auth()->user()->school_id;
        
        $rubrics = \App\Models\Assessment\Rubric::where('school_id', $schoolId)
            ->with(['subject', 'assessmentType', 'criteria.levels'])
            ->latest()
            ->paginate(20);

        $subjects = \App\Models\Curriculum\Subject::whereHas('schoolSubjects', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId)->where('is_offered', true);
        })->get();

        return Inertia::render('assessments/Rubrics', [
            'rubrics' => $rubrics,
            'subjects' => $subjects,
        ]);
    }

    public function rubricCreate(): Response
    {
        $schoolId = auth()->user()->school_id;

        return Inertia::render('assessments/RubricForm', [
            'subjects' => \App\Models\Curriculum\Subject::whereHas('schoolSubjects', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->where('is_offered', true);
            })->get(),
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::where('school_id', $schoolId)->get(),
        ]);
    }

    public function rubricStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'total_points' => 'required|numeric|min:0',
            'levels' => 'required|array|min:1',
            'levels.*.level_name' => 'required|string',
            'levels.*.grade_code' => 'required|string',
            'levels.*.min_score' => 'required|numeric',
            'levels.*.max_score' => 'required|numeric',
            'levels.*.points' => 'required|numeric',
            'levels.*.description' => 'nullable|string',
        ]);

        $rubric = \App\Models\Assessment\Rubric::create([
            'school_id' => auth()->user()->school_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'subject_id' => $validated['subject_id'] ?: null,
            'assessment_type_id' => $validated['assessment_type_id'],
            'total_points' => $validated['total_points'],
            'created_by' => auth()->user()->id,
        ]);

        $criteria = $rubric->criteria()->create([
            'criterion_name' => 'General Performance',
            'max_points' => $validated['total_points'],
        ]);

        foreach ($validated['levels'] as $index => $levelData) {
            $criteria->levels()->create(array_merge($levelData, ['display_order' => $index]));
        }

        return redirect()->route('assessments.rubrics')->with('success', 'Rubric created successfully.');
    }

    public function rubricEdit(int $id): Response
    {
        $schoolId = auth()->user()->school_id;
        $rubric = \App\Models\Assessment\Rubric::with(['criteria.levels'])->findOrFail($id);

        return Inertia::render('assessments/RubricForm', [
            'rubric' => $rubric,
            'subjects' => \App\Models\Curriculum\Subject::whereHas('schoolSubjects', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->where('is_offered', true);
            })->get(),
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::where('school_id', $schoolId)->get(),
        ]);
    }

    public function rubricUpdate(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'total_points' => 'required|numeric|min:0',
            'levels' => 'required|array|min:1',
            'levels.*.level_name' => 'required|string',
            'levels.*.grade_code' => 'required|string',
            'levels.*.min_score' => 'required|numeric',
            'levels.*.max_score' => 'required|numeric',
            'levels.*.points' => 'required|numeric',
            'levels.*.description' => 'nullable|string',
        ]);

        $rubric = \App\Models\Assessment\Rubric::findOrFail($id);
        $rubric->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'subject_id' => $validated['subject_id'] ?: null,
            'assessment_type_id' => $validated['assessment_type_id'],
            'total_points' => $validated['total_points'],
        ]);

        $criteria = $rubric->criteria()->first();
        if (!$criteria) {
            $criteria = $rubric->criteria()->create([
                'criterion_name' => 'General Performance',
                'max_points' => $validated['total_points'],
            ]);
        } else {
            $criteria->update(['max_points' => $validated['total_points']]);
            $criteria->levels()->delete();
        }

        foreach ($validated['levels'] as $index => $levelData) {
            $criteria->levels()->create(array_merge($levelData, ['display_order' => $index]));
        }

        return redirect()->route('assessments.rubrics')->with('success', 'Rubric updated successfully.');
    }

    public function results(): Response
    {
        $schoolId = auth()->user()->school_id;
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        // Fetch students with their assessment results for the current term
        $students = \App\Models\Student::where('school_id', $schoolId)
            ->with(['currentClass', 'assessmentResults.assessment.subject', 'assessmentResults.assessment.assessmentType'])
            ->paginate(20);

        return Inertia::render('assessments/Results', [
            'students' => $students,
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
        ]);
    }

    public function exportResults()
    {
        // Placeholder for results export logic
        return response()->json(['message' => 'Export logic not implemented yet.']);
    }

    public function importResults(Request $request)
    {
        // Placeholder for results import logic
        return redirect()->back()->with('success', 'Results imported successfully (Placeholder).');
    }

    public function importTemplate()
    {
        // Placeholder for assessment import template
        return response()->json(['message' => 'Template logic not implemented yet.']);
    }

    public function import(Request $request)
    {
        // Placeholder for assessment import logic
        return redirect()->back()->with('success', 'Assessments imported successfully (Placeholder).');
    }
}
