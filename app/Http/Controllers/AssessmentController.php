<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentType;
use App\Models\Assessment\GradingScale;
use App\Models\Assessment\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function index(Request $request): Response
    {
        $schoolId = $this->getSchoolId();
        $user = auth()->user();
        
        $query = Assessment::where('school_id', $schoolId)
            ->with(['class', 'subject', 'teacher', 'assessmentType']);

        // ── Role-based query scoping ──
        $this->applyRoleScope($query, $user);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('subject', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('class', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id') && $request->class_id !== 'all') {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id') && $request->subject_id !== 'all') {
            $query->where('subject_id', $request->subject_id);
        }

        $stats = [
            'total' => Assessment::where('school_id', $schoolId)->count(),
            'thisWeek' => Assessment::where('school_id', $schoolId)
                ->whereBetween('assessment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'pendingGrading' => Assessment::where('school_id', $schoolId)
                ->where('status', 'published')
                ->count(),
            'avgScore' => 0 
        ];

        return Inertia::render('assessments/Index', [
            'assessments' => $query->latest()->paginate($request->input('per_page', 20)),
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'class_id', 'subject_id', 'per_page', 'view']),
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(),
        ]);
    }


    public function create(): Response
    {
        $schoolId = $this->getSchoolId();

        return Inertia::render('assessments/Create', [
            'assessmentTypes' => AssessmentType::where('school_id', $schoolId)->where('is_active', true)->get(),
            'gradingScales' => GradingScale::where('school_id', $schoolId)->where('is_active', true)->get(),
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(),
            'rubrics' => \App\Models\Assessment\Rubric::where('school_id', $schoolId)->where('is_active', true)->with(['assessmentType', 'criteria.levels'])->get(),
        ]);
    }

    public function gradingIndex(): Response
    {
        $schoolId = $this->getSchoolId();
        
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
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'rubric_id' => 'required|exists:rubrics,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'grading_scale_id' => 'nullable|exists:grading_scales,id',
            'assessment_date' => 'required|date',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,scheduled,published',
            'lesson_plan_id' => 'nullable|exists:lesson_plans,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'indicators' => 'nullable|array',
            'competencies' => 'nullable|array',
        ]);

        $assessment = new Assessment();
        $assessment->fill($validated);
        $assessment->core_competencies = $validated['competencies'] ?? [];
        $assessment->indicators = $validated['indicators'] ?? [];
        $assessment->school_id = $this->getSchoolId();
        $teacherId = \App\Models\Teacher::where('user_id', auth()->id())->value('id')
            ?? \App\Models\Teacher::where('user_id', \App\Models\Academic\SchoolClass::find($validated['class_id'])?->class_teacher_id)->value('id')
            ?? \App\Models\Teacher::first()?->id;

        $assessment->teacher_id = $teacherId;
        $assessment->created_by = auth()->id();
        
        // Default academic year and term - should be fetched from active session
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();
        
        $assessment->academic_year_id = $activeYear?->id;
        $assessment->academic_term_id = $activeTerm?->id;
        
        $assessment->save();

        return redirect()->route('assessments.index')->with('success', 'Assessment created successfully.');
    }

    public function edit(int $id): Response
    {
        $schoolId = $this->getSchoolId();
        $assessment = Assessment::where('school_id', $schoolId)->findOrFail($id);

        return Inertia::render('assessments/Edit', [
            'assessment' => $assessment,
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::where('school_id', $schoolId)->where('is_active', true)->get(),
            'gradingScales' => \App\Models\Assessment\GradingScale::where('school_id', $schoolId)->where('is_active', true)->get(),
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(),
            'rubrics' => \App\Models\Assessment\Rubric::where('school_id', $schoolId)->where('is_active', true)->with(['assessmentType', 'criteria.levels'])->get(),
            'gradeLevels' => \App\Models\Academic\GradeLevel::where('school_id', $schoolId)->with('classes')->get(),
            'academicTerms' => \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)->get(),
            'academicYears' => \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->get(),
            'competencies' => \App\Models\Curriculum\Competency::where('school_id', $schoolId)->get(),
            'ratingScales' => [
                ['id' => 4, 'code' => 'EE', 'name' => 'Exceeding Expectation', 'color' => '#10b981'],
                ['id' => 3, 'code' => 'ME', 'name' => 'Meeting Expectation', 'color' => '#3b82f6'],
                ['id' => 2, 'code' => 'AE', 'name' => 'Approaching Expectation', 'color' => '#f59e0b'],
                ['id' => 1, 'code' => 'BE', 'name' => 'Below Expectation', 'color' => '#ef4444'],
            ],
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $assessment = Assessment::where('school_id', $this->getSchoolId())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'rubric_id' => 'required|exists:rubrics,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'grading_scale_id' => 'nullable|exists:grading_scales,id',
            'assessment_date' => 'required|date',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,scheduled,published',
            'lesson_plan_id' => 'nullable|exists:lesson_plans,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'indicators' => 'nullable|array',
            'competencies' => 'nullable|array',
        ]);

        $assessment->fill($validated);
        $assessment->core_competencies = $validated['competencies'] ?? [];
        $assessment->indicators = $validated['indicators'] ?? [];
        $assessment->save();

        return redirect()->route('assessments.index')->with('success', 'Assessment updated successfully.');
    }

    public function destroy(int $id)
    {
        $assessment = Assessment::where('school_id', $this->getSchoolId())->findOrFail($id);
        
        // Prevent deletion if an assessment is already fully graded or locked, unless admin.
        if ($assessment->status === 'completed' && !auth()->user()->hasRole('super_admin')) {
             return redirect()->back()->with('error', 'Cannot delete a completed assessment. Please consult administration.');
        }

        $assessment->delete();

        return redirect()->route('assessments.index')->with('success', 'Assessment removed successfully.');
    }

    public function grading(int $id): Response
    {
        $assessment = Assessment::with(['class', 'subject', 'assessmentType', 'gradingScale.descriptors', 'rubric.criteria.levels'])
            ->findOrFail($id);

        $students = \App\Models\Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number', 'photo']);

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
        $assessment = Assessment::with(['gradingScale.descriptors', 'rubric.criteria.levels'])->findOrFail($id);
        
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
                if ($assessment->grading_scale_id && $assessment->gradingScale) {
                    $gradeDescriptor = $assessment->gradingScale->descriptors
                        ->where('min_score', '<=', $marks)
                        ->where('max_score', '>=', $marks)
                        ->first();
                    $gradeLevel = $gradeDescriptor?->level_code;
                } elseif ($assessment->rubric_id && $assessment->rubric) {
                    // For rubric-based assessments, we might have multiple criteria, 
                    // but for a simple score-based lookup, we can find the level from the FIRST criterion
                    $firstCriterion = $assessment->rubric->criteria->first();
                    if ($firstCriterion) {
                        $level = $firstCriterion->levels
                            ->where('min_score', '<=', $marks)
                            ->where('max_score', '>=', $marks)
                            ->first();
                        $gradeLevel = $level?->grade_code;
                    }
                }
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
        $schoolId = $this->getSchoolId();
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
        $schoolId = $this->getSchoolId();
        $student = \App\Models\Student::with(['currentClass'])->findOrFail($studentId);
        
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('is_current', true)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        // Fetch all subjects
        $subjects = \App\Models\Curriculum\Subject::orderBy('name')->get();

        // Fetch all assessment ratings for this student in the current term
        $ratings = \App\Models\Assessment\StudentAssessmentRating::where('student_id', $studentId)
            ->with(['item.assessment.assessmentType'])
            ->whereHas('item.assessment', function($query) use ($activeTerm) {
                if ($activeTerm) {
                    $query->where('academic_term_id', $activeTerm->id);
                }
            })
            ->get();

        $results = $subjects->map(function($subject) use ($ratings) {
            $subjectRatings = $ratings->filter(fn($r) => $r->item->assessment->subject_id == $subject->id);
            
            $opening = $subjectRatings->filter(fn($r) => stripos($r->item->assessment->assessmentType->name, 'Opening') !== false);
            $mid = $subjectRatings->filter(fn($r) => stripos($r->item->assessment->assessmentType->name, 'Mid') !== false);
            $end = $subjectRatings->filter(fn($r) => stripos($r->item->assessment->assessmentType->name, 'End') !== false);

            $openingScore = $opening->count() > 0 ? $opening->avg('score') : null;
            $midScore = $mid->count() > 0 ? $mid->avg('score') : null;
            $endScore = $end->count() > 0 ? $end->avg('score') : null;
            
            $allScores = $subjectRatings->pluck('score')->filter();
            $avgScore = $allScores->count() > 0 ? $allScores->avg() : null;

            return [
                'subject' => $subject->name,
                'opening' => ['score' => $openingScore ? round($openingScore, 1) : null, 'level' => $this->mapScoreToLevel($openingScore)],
                'mid' => ['score' => $midScore ? round($midScore, 1) : null, 'level' => $this->mapScoreToLevel($midScore)],
                'end' => ['score' => $endScore ? round($endScore, 1) : null, 'level' => $this->mapScoreToLevel($endScore)],
                'average' => ['score' => $avgScore ? round($avgScore, 1) : null, 'level' => $this->mapScoreToLevel($avgScore)],
                'comments' => $avgScore ? $this->getAutoComment($avgScore) : 'No assessments logged yet.',
            ];
        })->filter(fn($r) => $r['opening']['score'] || $r['mid']['score'] || $r['end']['score'])->values();

        return Inertia::render('assessments/ReportForm', [
            'student' => $student,
            'academicYear' => $activeYear,
            'academicTerm' => $activeTerm,
            'results' => $results,
            'performanceLevels' => [
                ['code' => 'EE', 'label' => 'Exceeding Expectation', 'range' => '75-100', 'rating' => 4],
                ['code' => 'ME', 'label' => 'Meeting Expectation', 'range' => '50-74', 'rating' => 3],
                ['code' => 'AE', 'label' => 'Approaching Expectation', 'range' => '30-49', 'rating' => 2],
                ['code' => 'BE', 'label' => 'Below Expectation', 'range' => '0-29', 'rating' => 1],
            ],
            'attendance' => ['days_present' => 0, 'total_days' => 0],
        ]);
    }

    private function mapScoreToLevel(?float $score): ?string
    {
        if ($score === null) return null;
        if ($score >= 75) return 'EE';
        if ($score >= 50) return 'ME';
        if ($score >= 30) return 'AE';
        return 'BE';
    }

    private function getAutoComment(?float $score): string
    {
        if ($score === null) return 'No data available.';
        if ($score >= 80) return 'Excellent performance. Keep it up!';
        if ($score >= 60) return 'Good progress. Aim higher next term.';
        if ($score >= 40) return 'Fair performance. More effort needed.';
        return 'Requires urgent attention and remedial support.';
    }

    public function rubrics(): Response
    {
        $schoolId = $this->getSchoolId();
        
        $rubrics = \App\Models\Assessment\Rubric::where('school_id', $schoolId)
            ->with(['subject', 'assessmentType', 'criteria.levels'])
            ->latest()
            ->paginate(20);

        $subjects = \App\Models\Curriculum\Subject::orderBy('name')->get();

        return Inertia::render('assessments/Rubrics', [
            'rubrics' => $rubrics,
            'subjects' => $subjects,
        ]);
    }

    public function rubricCreate(): Response
    {
        $schoolId = $this->getSchoolId();

        return Inertia::render('assessments/RubricForm', [
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(),
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
            'school_id' => $this->getSchoolId(),
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
        $schoolId = $this->getSchoolId();
        $rubric = \App\Models\Assessment\Rubric::with(['criteria.levels'])->findOrFail($id);

        return Inertia::render('assessments/RubricForm', [
            'rubric' => $rubric,
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(),
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
        $schoolId = $this->getSchoolId();
        $user = auth()->user();
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        // Fetch students scoped by role
        $query = \App\Models\Student::where('school_id', $schoolId);

        // Teacher scoper: only students in their assigned classes
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['super_admin', 'school_admin'])) {
            $classIds = \App\Models\TeacherSubject::where('teacher_id', $user->teacher->id)->pluck('class_id')
                ->merge(\App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->pluck('id'))
                ->unique();
            $query->whereIn('current_class_id', $classIds);
        }

        $students = $query->with(['currentClass', 'assessmentRatings', 'competencyRatings.competency'])
            ->paginate(20);

        // Append calculated averages for the frontend
        $students->getCollection()->transform(function ($student) {
            $ratings = $student->assessmentRatings;
            $count = $ratings->count();
            $mean = $count > 0 ? $ratings->avg('score') : 0;
            
            $student->mean_score = round($mean, 1);
            $student->tests_count = $count;
            
            // Basic trajectory logic: compare last 3 tests to overall mean
            $last3 = $ratings->sortByDesc('created_at')->take(3);
            $last3Mean = $last3->count() > 0 ? $last3->avg('score') : 0;
            $student->trajectory = $last3Mean >= $mean ? 'Positive' : 'Declining';
            
            return $student;
        });

        return Inertia::render('assessments/Results', [
            'students' => $students,
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
        ]);
    }

    public function bulkUploadView(): Response
    {
        $schoolId = $this->getSchoolId();
        return Inertia::render('assessments/BulkUpload', [
            'assessments' => \App\Models\Assessment\Assessment::where('school_id', $schoolId)->where('teacher_id', auth()->id())->get(),
        ]);
    }

    public function bulkUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $assessment = Assessment::findOrFail($request->assessment_id);
        $file = $request->file('file');
        
        // Ensure teacher owns this assessment
        if ($assessment->teacher_id !== auth()->id() && !auth()->user()->hasRole('super_admin')) {
             return redirect()->back()->with('error', 'Unauthorized to upload marks for this assessment.');
        }

        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        // Remove header
        array_shift($data);

        $successCount = 0;
        $errorCount = 0;

        foreach ($data as $row) {
            if (count($row) < 2) continue;

            $admissionNumber = trim($row[0]);
            $score = (float) $row[1];

            $student = \App\Models\Student::where('admission_number', $admissionNumber)->first();

            if ($student && $score <= $assessment->max_marks) {
                \App\Models\Assessment\StudentAssessment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'assessment_id' => $assessment->id,
                    ],
                    [
                        'marks_obtained' => $score,
                        'graded_by' => auth()->id(),
                        'graded_at' => now(),
                        'grade_descriptor_id' => $this->calculateGradeDescriptor($score, $assessment),
                    ]
                );
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return redirect()->back()->with('success', "Processed marks: $successCount successful, $errorCount errors.");
    }

    private function calculateGradeDescriptor($score, $assessment)
    {
        if (!$assessment->rubric_id) return null;
        
        $rubric = \App\Models\Assessment\Rubric::with('criteria.levels')->find($assessment->rubric_id);
        if (!$rubric) return null;

        $firstCriterion = $rubric->criteria->first();
        if (!$firstCriterion) return null;

        $level = $firstCriterion->levels
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        return $level?->id;
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
        $headers = ['title', 'class_name', 'subject_name', 'assessment_type_code', 'source', 'assessment_date', 'total_marks'];
        $headers = [
            'Title', 
            'Grade Level', 
            'Class Name', 
            'Subject Name', 
            'Strand Name', 
            'Sub-Strand Name', 
            'Assessment Type', 
            'Academic Term', 
            'Source (Internal/Ministry)', 
            'Assessment Date (YYYY-MM-DD)', 
            'Total Marks'
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Sample row
            fputcsv($file, [
                'End of Term 1 Math Exam',
                'Grade 4',
                '4 West',
                'Mathematics',
                'Numbers',
                'Whole Numbers',
                'Summative',
                'Term 1',
                'Internal',
                date('Y-m-d'),
                '50'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="assessment_import_template.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $schoolId = $this->getSchoolId();
        $user = auth()->user();
        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        // Remove header row
        $headers = array_shift($data);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            if (count($row) < 4) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": Insufficient columns.";
                continue;
            }

            // Map columns by index
            $title          = trim($row[0] ?? '');
            $gradeName      = trim($row[1] ?? '');
            $className      = trim($row[2] ?? '');
            $subjectName    = trim($row[3] ?? '');
            $strandName     = trim($row[4] ?? '');
            $subStrandName  = trim($row[5] ?? '');
            $typeName       = trim($row[6] ?? '');
            $termName       = trim($row[7] ?? '');
            $source         = in_array(strtolower(trim($row[8] ?? '')), ['internal', 'ministry']) ? strtolower(trim($row[8])) : 'internal';
            $dateStr        = trim($row[9] ?? date('Y-m-d'));
            $totalMarks     = (float) ($row[10] ?? 100);

            if (!$title || !$className || !$subjectName) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": Missing title, class, or subject.";
                continue;
            }

            // 1. Resolve Grade Level
            $grade = \App\Models\Academic\GradeLevel::where('school_id', $schoolId)
                ->where('name', 'like', "%{$gradeName}%")
                ->first();

            // 2. Resolve Class
            $classQuery = \App\Models\Academic\SchoolClass::where('school_id', $schoolId)
                ->where('name', 'like', "%{$className}%");
            if ($grade) $classQuery->where('grade_level_id', $grade->id);
            $class = $classQuery->first();

            if (!$class) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": Class '{$className}' not found.";
                continue;
            }

            // 3. Resolve Subject
            $subject = \App\Models\Curriculum\Subject::where('name', 'like', "%{$subjectName}%")->first();

            if (!$subject) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": Subject '{$subjectName}' not found.";
                continue;
            }

            // 4. Resolve Assessment Type
            $assessmentType = AssessmentType::where('school_id', $schoolId)
                ->where(function ($q) use ($typeName) {
                    $q->where('code', $typeName)->orWhere('name', 'like', "%{$typeName}%");
                })
                ->first()
                ?? AssessmentType::where('school_id', $schoolId)->where('is_active', true)->first();

            // 5. Resolve Academic Term
            $term = \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)
                ->where('name', 'like', "%{$termName}%")
                ->first()
                ?? \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)->where('is_current', true)->first();

            // 6. Resolve Sub-Strand (Optional but recommended for indicators)
            $subStrand = null;
            if ($subStrandName && $subject) {
                $subStrand = \App\Models\Curriculum\SubStrand::whereHas('strand', fn($q) => $q->where('subject_id', $subject->id))
                    ->where('name', 'like', "%{$subStrandName}%")
                    ->first();
            }

            // 7. Resolve Teacher
            $teacherId = \App\Models\Teacher::where('user_id', $user->id)->value('id')
                ?? \App\Models\Teacher::where('user_id', $class->class_teacher_id)->value('id')
                ?? \App\Models\Teacher::first()?->id;

            try {
                \DB::transaction(function () use ($schoolId, $class, $subject, $user, $term, $assessmentType, $title, $dateStr, $totalMarks, $source, $subStrand, $teacherId, &$successCount) {
                    $assessment = Assessment::create([
                        'school_id' => $schoolId,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'academic_year_id' => $term?->academic_year_id,
                        'academic_term_id' => $term?->id,
                        'assessment_type_id' => $assessmentType?->id,
                        'title' => $title,
                        'assessment_date' => $dateStr,
                        'total_marks' => $totalMarks,
                        'passing_marks' => $totalMarks * 0.5,
                        'weight' => 100,
                        'status' => 'draft',
                        'source' => $source,
                        'created_by' => $user->id,
                    ]);

                    // 7. Auto-attach indicators if Sub-Strand is mapped
                    if ($subStrand) {
                        $indicators = \App\Models\Curriculum\CompetencyIndicator::where('sub_strand_id', $subStrand->id)
                            ->where('grade_level_id', $class->grade_level_id)
                            ->get();

                        foreach ($indicators as $index => $indicator) {
                            \App\Models\Assessment\AssessmentItem::create([
                                'assessment_id' => $assessment->id,
                                'competency_indicator_id' => $indicator->id,
                                'max_score' => 4, // Default rubric max
                                'display_order' => $index,
                            ]);
                        }
                    }

                    $successCount++;
                });
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Import complete: {$successCount} created, {$errorCount} skipped.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->back()->with($errorCount > 0 && $successCount === 0 ? 'error' : 'success', $message);
    }

    /**
     * Apply role-based scoping to an assessment query.
     * Admins/principals see all; teachers see own; HoDs see department; parents see children's classes.
     */
    private function applyRoleScope($query, $user)
    {
        if (!$user) return;

        // Admin roles bypass scoping
        if ($user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal'])) {
            return;
        }

        // Teacher — only own assessments
        if ($user->hasRole('teacher') && !$user->hasRole('class_teacher') && !$user->hasRole('hod')) {
            $query->where('teacher_id', $user->id);
            return;
        }

        // Class teacher — assessments for their class + own
        if ($user->hasRole('class_teacher')) {
            $classIds = \App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->pluck('id')->toArray();
            $query->where(function($q) use ($user, $classIds) {
                $q->where('teacher_id', $user->id)
                  ->orWhereIn('class_id', $classIds);
            });
            return;
        }

        // HoD — assessments for subjects in their department
        if ($user->hasRole('hod')) {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if ($teacher?->department_id) {
                $subjectIds = \App\Models\Curriculum\Subject::where('department_id', $teacher->department_id)->pluck('id')->toArray();
                $query->whereIn('subject_id', $subjectIds);
            }
            return;
        }

        // Parent — assessments for classes their children are in
        if ($user->hasRole('parent')) {
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $childClassIds = $guardian->students()->pluck('current_class_id')->filter()->toArray();
                $query->whereIn('class_id', $childClassIds);
            }
            return;
        }

        // Student — only assessments for their class
        if ($user->hasRole('student')) {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('class_id', $student->current_class_id);
            }
            return;
        }
    }

    public function analytics(): Response
    {
        $schoolId = $this->getSchoolId();
        $user = auth()->user();
        $teacher = $user->teacher;

        // 1. Distribution of Ratings (EE, ME, AE, BE)
        $distribution = \App\Models\Assessment\StudentAssessmentRating::where('teacher_id', $user->id)
            ->select('rating_level', DB::raw('count(*) as count'))
            ->groupBy('rating_level')
            ->get()
            ->pluck('count', 'rating_level')
            ->toArray();

        // Ensure all levels are present
        $levels = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
        $distribution = array_merge($levels, $distribution);

        // 2. Performance Trend (Average Score per Assessment)
        $trends = \App\Models\Assessment\Assessment::where('teacher_id', $user->id)
            ->where('status', 'published')
            ->with(['assessmentRatings'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'title' => $a->title ?: $a->subject?->name,
                'average' => $a->assessmentRatings->avg('score') ?? 0,
                'date' => $a->assessment_date->format('M d')
            ])->reverse()->values();

        return Inertia::render('assessments/Analytics', [
            'distribution' => $distribution,
            'trends' => $trends,
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)->get(),
        ]);
    }

    private function getSchoolId()
    {
        $user = auth()->user();
        if (!$user) return null;

        // Check if user is a teacher
        if ($user->teacher) {
            return $user->teacher->school_id;
        }

        // Check if user is a student
        if ($user->student) {
            return $user->student->school_id;
        }

        // Fallback to first school for admins
        return \App\Models\School::first()?->id;
    }
}

