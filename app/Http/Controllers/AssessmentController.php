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
            'type_id' => 'required|exists:assessment_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:academic_terms,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'total_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,published,closed',
            'source' => 'required|in:internal,ministry',
            'indicators' => 'required|array|min:1',
            'date' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $request, $assessment) {
            $indicatorsChanged = $assessment->source !== $validated['source'] || 
                                 json_encode($assessment->indicators) !== json_encode($validated['indicators']);

            $assessment->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'assessment_type_id' => $validated['type_id'],
                'total_marks' => $validated['total_marks'],
                'passing_marks' => $validated['passing_marks'],
                'weight' => $validated['weight'],
                'academic_year_id' => $validated['academic_year_id'],
                'academic_term_id' => $validated['term_id'],
                'assessment_date' => $validated['date'],
                'status' => $validated['status'],
                'source' => $validated['source'],
                'indicators' => $validated['indicators'],
            ]);

            // Sync Assessment Items (Criteria) only if changed
            if ($indicatorsChanged) {
                // Warning: Deleting items will delete student ratings for those items.
                $assessment->items()->delete();

                if ($validated['source'] === 'ministry') {
                    $standardCriteria = [
                        'knowledge' => 'Knowledge & Understanding',
                        'skills' => 'Skills Application',
                        'communication' => 'Communication',
                        'values' => 'Values & Attitudes',
                        'creativity' => 'Creativity',
                        'thinking' => 'Critical Thinking'
                    ];

                    $i = 0;
                    foreach ($standardCriteria as $code => $name) {
                        \App\Models\Assessment\AssessmentItem::create([
                            'assessment_id' => $assessment->id,
                            'name' => $name,
                            'code' => $code,
                            'max_score' => 4,
                            'display_order' => $i++,
                        ]);
                    }
                } else {
                    foreach ($validated['indicators'] as $index => $item) {
                        \App\Models\Assessment\AssessmentItem::create([
                            'assessment_id' => $assessment->id,
                            'competency_indicator_id' => is_numeric($item['id']) ? $item['id'] : null,
                            'name' => $item['indicator'] ?? 'Indicator ' . ($index + 1),
                            'code' => !is_numeric($item['id']) ? $item['id'] : null,
                            'max_score' => $item['max_score'] ?? 4,
                            'display_order' => $index,
                        ]);
                    }
                }
            }
        });

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

    public function reportCards(Request $request): Response
    {
        $schoolId = $this->getSchoolId();
        $activeYear = \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $activeYearIds = $this->currentAcademicYearIds($schoolId);
        $activeTerm = \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)->where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        // Load hierarchy: Grade Levels -> Classes
        $gradeLevels = \App\Models\Academic\GradeLevel::where('school_id', $schoolId)
            ->with(['classes' => function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            }])
            ->get();

        $selectedClassId = $request->input('class_id');
        $students = [];

        if ($selectedClassId) {
            // Get all assessments for this class (any term in the active year)
            $classAssessments = \App\Models\Assessment\Assessment::where('school_id', $schoolId)
                ->where('class_id', $selectedClassId)
                ->when($activeYearIds->isNotEmpty(), fn($q) => $q->whereIn('academic_year_id', $activeYearIds))
                ->with('subject')
                ->get();

            $assessmentIds = $classAssessments->pluck('id');

            // Build a lookup: assessment_id -> subject info + total_marks
            $assessmentLookup = $classAssessments->keyBy('id');

            $students = \App\Models\Student::where('current_class_id', $selectedClassId)
                ->where('school_id', $schoolId)
                ->active()
                ->get()
                ->map(function($student) use ($assessmentIds, $assessmentLookup) {
                    // Fetch master scores (from "Finalize and Post") for this student
                    $masterScores = \App\Models\Assessment\StudentAssessment::where('student_id', $student->id)
                        ->whereIn('assessment_id', $assessmentIds)
                        ->get();

                    if ($masterScores->isEmpty()) {
                        return [
                            'id' => $student->id,
                            'name' => $student->full_name,
                            'admission_number' => $student->admission_number,
                            'photo' => $student->photo_url,
                            'average' => 0,
                            'total' => 0,
                            'status' => 'pending',
                        ];
                    }

                    // Aggregate by subject
                    $bySubject = $masterScores->groupBy(function($sa) use ($assessmentLookup) {
                        return $assessmentLookup[$sa->assessment_id]->subject_id ?? 0;
                    });

                    $subjectAverages = $bySubject->map(function($group) use ($assessmentLookup) {
                        $totalMarks = 0;
                        $totalPossible = 0;
                        foreach ($group as $sa) {
                            $assessment = $assessmentLookup[$sa->assessment_id] ?? null;
                            $totalMarks += (float) $sa->marks_obtained;
                            $totalPossible += $assessment ? ($assessment->total_marks ?: 100) : 100;
                        }
                        return $totalPossible > 0 ? ($totalMarks / $totalPossible) * 100 : 0;
                    });

                    $overallAvg = $subjectAverages->count() > 0 ? $subjectAverages->avg() : 0;
                    $totalMarks = $masterScores->sum('marks_obtained');

                    return [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'admission_number' => $student->admission_number,
                        'photo' => $student->photo_url,
                        'average' => round($overallAvg, 1),
                        'total' => $totalMarks,
                        'status' => 'ready',
                    ];
                });
        }
        
        return Inertia::render('assessments/ReportCards', [
            'gradeLevels' => $gradeLevels,
            'students' => $students,
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
            'filters' => $request->only(['class_id', 'search']),
        ]);
    }

    public function showReport(int $studentId): Response
    {
        $schoolId = $this->getSchoolId();
        $student = \App\Models\Student::with(['currentClass.gradeLevel', 'school'])->findOrFail($studentId);
        
        $activeYear = \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)->where('is_current', true)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        $reportData = $this->buildStudentReportData($student, $schoolId, $activeYear);

        return Inertia::render('assessments/ReportCardDetail', [
            'student' => $student,
            'subjects' => $reportData['subjects'],
            'summary' => $reportData['summary'],
            'activeTerm' => $activeTerm,
            'activeYear' => $activeYear,
        ]);
    }

    public function exportPdf(int $studentId)
    {
        $schoolId = $this->getSchoolId();
        $student = \App\Models\Student::with(['currentClass.gradeLevel', 'school'])->findOrFail($studentId);
        $school = $student->school;
        
        $activeYear = \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('school_id', $schoolId)->where('is_current', true)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        $reportData = $this->buildStudentReportData($student, $schoolId, $activeYear);
        $themeColor = $school->getSetting('pdf_theme_color', '#1e40af');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.report-card', [
            'student' => $student,
            'school' => $school,
            'subjects' => $reportData['subjects'],
            'themeColor' => $themeColor,
            'summary' => $reportData['summary'],
            'activeTerm' => $activeTerm,
            'activeYear' => $activeYear,
        ]);

        return $pdf->download("Report_Card_{$student->admission_number}.pdf");
    }

    /**
     * Build the subject-wise performance data for a student's report card.
     * Uses StudentAssessment (master scores from "Finalize and Post") as primary source.
     */
    private function buildStudentReportData(\App\Models\Student $student, $schoolId, $activeYear): array
    {
        $activeYearIds = $this->currentAcademicYearIds($schoolId);

        // Get all assessments for this student's class in the active year
        $classAssessments = \App\Models\Assessment\Assessment::where('school_id', $schoolId)
            ->where('class_id', $student->current_class_id)
            ->when($activeYearIds->isNotEmpty(), fn($q) => $q->whereIn('academic_year_id', $activeYearIds))
            ->with(['subject', 'rubric.criteria.levels', 'gradingScale.descriptors'])
            ->get();

        $assessmentIds = $classAssessments->pluck('id');
        $assessmentLookup = $classAssessments->keyBy('id');

        // Fetch master scores (from "Finalize and Post")
        $masterScores = \App\Models\Assessment\StudentAssessment::where('student_id', $student->id)
            ->whereIn('assessment_id', $assessmentIds)
            ->get();

        // Get grading scale
        $gradingScale = \App\Models\Assessment\GradingScale::where('school_id', $schoolId)
            ->where('is_default', true)
            ->with('descriptors')
            ->first() ?? \App\Models\Assessment\GradingScale::where('school_id', $schoolId)->with('descriptors')->first();

        // Group by subject
        $bySubject = $masterScores->groupBy(function($sa) use ($assessmentLookup) {
            return $assessmentLookup[$sa->assessment_id]->subject_id ?? 0;
        });

        $subjectsData = $bySubject->map(function($group) use ($assessmentLookup, $gradingScale, $schoolId) {
            $firstAssessment = $assessmentLookup[$group->first()->assessment_id] ?? null;
            $subjectName = $firstAssessment?->subject?->name ?? 'Unknown';

            $totalMarks = 0;
            $totalPossible = 0;
            $teacherRemarks = null;
            $subjectAssessments = collect();
            foreach ($group as $sa) {
                $assessment = $assessmentLookup[$sa->assessment_id] ?? null;
                if ($assessment) {
                    $subjectAssessments->push($assessment);
                }
                $totalMarks += (float) $sa->marks_obtained;
                $totalPossible += $assessment ? ($assessment->total_marks ?: 100) : 100;
                // Use the most recent teacher comment as subject remarks
                if ($sa->teacher_comments) {
                    $teacherRemarks = $sa->teacher_comments;
                }
            }

            $percentage = $totalPossible > 0 ? ($totalMarks / $totalPossible) * 100 : 0;
            $performance = $this->resolveReportPerformance(
                $percentage,
                $subjectAssessments,
                $gradingScale,
                $schoolId,
                $firstAssessment?->subject_id
            );

            return [
                'subject_id' => $firstAssessment?->subject_id,
                'name' => $subjectName,
                'score' => $totalMarks,
                'max' => $totalPossible,
                'percentage' => round($percentage, 1),
                'rubric' => $performance['level'],
                'rubric_name' => $performance['descriptor'],
                'remarks' => $teacherRemarks ?: $this->getAutoComment($percentage),
            ];
        })->values();

        $overallTotal = $subjectsData->sum('score');
        $overallMax = $subjectsData->sum('max');
        $overallAvg = $subjectsData->count() > 0 ? $subjectsData->avg('percentage') : 0;

        $overallRubric = $gradingScale ? $gradingScale->descriptors
            ->where('min_score', '<=', $overallAvg)
            ->where('max_score', '>=', $overallAvg)
            ->first() : null;

        // Compute rank
        $classStudents = \App\Models\Student::where('current_class_id', $student->current_class_id)
            ->where('school_id', $schoolId)
            ->pluck('id');
            
        $allScores = \App\Models\Assessment\StudentAssessment::whereIn('student_id', $classStudents)
            ->whereIn('assessment_id', $assessmentIds)
            ->selectRaw('student_id, SUM(marks_obtained) as total_score')
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();
            
        $rank = '-';
        $outOf = $allScores->count() ?: 1;
        foreach ($allScores as $index => $scorePair) {
            if ($scorePair->student_id == $student->id) {
                $rank = $index + 1;
                break;
            }
        }

        return [
            'subjects' => $subjectsData,
            'summary' => [
                'total' => $overallTotal,
                'max' => $overallMax,
                'average' => round($overallAvg, 1),
                'rubric' => $overallRubric ? $overallRubric->level_code : $this->mapScoreToLevel($overallAvg),
                'rubric_name' => $overallRubric ? $overallRubric->level_name : $this->getLevelName($overallAvg),
                'rank' => $rank,
                'out_of' => $outOf,
                'class_teacher_remark' => $this->getClassTeacherRemark($overallAvg),
                'head_teacher_remark' => $this->getHeadTeacherRemark($overallAvg),
            ],
        ];
    }

    private function resolveReportPerformance(
        float $percentage,
        $subjectAssessments,
        $gradingScale,
        $schoolId,
        $subjectId
    ): array {
        $assessmentWithRubric = $subjectAssessments
            ->filter(fn($assessment) => $assessment->rubric && $assessment->rubric->criteria->isNotEmpty())
            ->sortByDesc(fn($assessment) => $assessment->assessment_date?->timestamp ?? $assessment->id)
            ->first();

        $rubric = $assessmentWithRubric?->rubric;

        if (!$rubric) {
            $rubric = \App\Models\Assessment\Rubric::where('school_id', $schoolId)
                ->where('is_active', true)
                ->where(function ($query) use ($subjectId) {
                    $query->where('subject_id', $subjectId)
                        ->orWhereNull('subject_id');
                })
                ->with(['criteria.levels' => fn($query) => $query->orderBy('min_score', 'desc')])
                ->orderByRaw('subject_id IS NULL ASC')
                ->latest()
                ->first();
        }

        if ($rubric && $rubric->criteria->isNotEmpty()) {
            foreach ($rubric->criteria->first()->levels as $level) {
                if ($percentage >= $level->min_score && $percentage <= $level->max_score) {
                    return [
                        'level' => $level->grade_code,
                        'descriptor' => $level->level_name,
                    ];
                }
            }
        }

        $descriptor = $gradingScale ? $gradingScale->descriptors
            ->where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->first() : null;

        return [
            'level' => $descriptor ? $descriptor->level_code : $this->mapScoreToLevel($percentage),
            'descriptor' => $descriptor ? $descriptor->level_name : $this->getLevelName($percentage),
        ];
    }

    private function getClassTeacherRemark(?float $score): string
    {
        if ($score === null || $score === 0.0) return 'No academic data available for this term.';
        if ($score >= 80) return 'An outstanding term! You have shown remarkable dedication and excellent understanding across all learning areas. Keep maintaining this high standard and continue to be a positive role model in class.';
        if ($score >= 60) return 'A solid performance this term. You have demonstrated a good grasp of the concepts taught. With a little more consistent effort and focus, you can certainly push your grades even higher next term.';
        if ($score >= 40) return 'Fair performance. While you have a basic understanding of the subjects, there is significant room for improvement. I encourage you to participate more in class and complete all assignments on time.';
        return 'This term\'s performance indicates a need for urgent academic intervention. We need to work together—teacher, parent, and learner—to identify the challenges and apply remedial measures before the next term.';
    }

    private function getHeadTeacherRemark(?float $score): string
    {
        if ($score === null || $score === 0.0) return 'No records to evaluate.';
        if ($score >= 80) return 'Exceptional results! Congratulations on your hard work. Keep aiming for the stars.';
        if ($score >= 60) return 'Good work. I am pleased with your progress. Strive for excellence in the coming term.';
        if ($score >= 40) return 'Average work. You possess the potential to do much better. Commit to your studies.';
        return 'Below expectation. Please step up your efforts significantly. The school will support your remedial program.';
    }

    private function mapScoreToLevel(?float $score): ?string
    {
        if ($score === null) return null;
        if ($score >= 75) return 'EE';
        if ($score >= 50) return 'ME';
        if ($score >= 30) return 'AE';
        return 'BE';
    }

    private function getLevelName(?float $score): string
    {
        if ($score === null) return 'Not Graded';
        if ($score >= 75) return 'Exceeding Expectation';
        if ($score >= 50) return 'Meeting Expectation';
        if ($score >= 30) return 'Approaching Expectation';
        return 'Below Expectation';
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
        $request->merge([
            'subject_id' => $request->filled('subject_id') ? $request->input('subject_id') : null,
            'assessment_type_id' => $request->filled('assessment_type_id') ? $request->input('assessment_type_id') : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'assessment_type_id' => 'nullable|exists:assessment_types,id',
            'total_points' => 'required|numeric|min:0',
            'is_active' => 'boolean',
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
            'assessment_type_id' => $validated['assessment_type_id'] ?: null,
            'total_points' => $validated['total_points'],
            'is_active' => $validated['is_active'],
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
        $request->merge([
            'subject_id' => $request->filled('subject_id') ? $request->input('subject_id') : null,
            'assessment_type_id' => $request->filled('assessment_type_id') ? $request->input('assessment_type_id') : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'assessment_type_id' => 'nullable|exists:assessment_types,id',
            'total_points' => 'required|numeric|min:0',
            'is_active' => 'boolean',
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
            'assessment_type_id' => $validated['assessment_type_id'] ?: null,
            'total_points' => $validated['total_points'],
            'is_active' => $validated['is_active'],
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

    public function results(Request $request): Response
    {
        $schoolId = $this->getSchoolId();
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->where('school_id', $schoolId)->first();
        $activeTerm = \App\Models\Academic\AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();
        $payload = $this->buildResultsPayload($request, $schoolId, $activeYear);

        return Inertia::render('assessments/Results', [
            'students' => $payload['students'],
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
            'analytics' => $payload['analytics'],
            'rankings' => $payload['rankings'],
            'filters' => $payload['filters'],
            'classes' => $payload['classes'],
            'grades' => $payload['grades'],
            'subjects' => $payload['subjects'],
        ]);
    }

    public function exportResultsPdf(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $school = \App\Models\School::find($schoolId);
        $activeYear = \App\Models\Academic\AcademicYear::where('is_current', true)->where('school_id', $schoolId)->first();
        $payload = $this->buildResultsPayload($request, $schoolId, $activeYear, false);
        $analytics = $payload['analytics'];

        $assessmentsData = collect($analytics['subjectAnalysis'])->map(fn($subject) => (object) [
            'subject' => $subject['name'],
            'total_assessed' => $subject['students'],
            'average_score' => $subject['score'],
            'male_avg' => $subject['male_avg'],
            'female_avg' => $subject['female_avg'],
            'status' => $subject['score'] >= 80 ? 'Excellent' : ($subject['score'] >= 50 ? 'Average' : 'Below Average'),
        ])->values()->all();

        $stats = (object) [
            'avg_score' => $analytics['overallMean'],
            'total_assessed' => $analytics['activeStudents'],
        ];

        $classTitle = collect([
            $payload['filters']['grade_level_id'] ? optional($payload['grades']->firstWhere('id', (int) $payload['filters']['grade_level_id']))->name : null,
            $payload['filters']['class_id'] ? optional($payload['classes']->firstWhere('id', (int) $payload['filters']['class_id']))->name : null,
            $payload['filters']['subject_id'] ? optional($payload['subjects']->firstWhere('id', (int) $payload['filters']['subject_id']))->name : null,
        ])->filter()->join(' / ') ?: 'All Classes';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.assessment-pdf', [
            'assessmentsData' => $assessmentsData,
            'classTitle' => $classTitle,
            'school' => $school,
            'stats' => $stats,
            'themeColor' => $school?->getSetting('pdf_theme_color', '#1e40af') ?? '#1e40af',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('assessment_results_' . now()->format('Y_m_d_His') . '.pdf');
    }

    public function downloadResultsTemplate()
    {
        $headers = [
            'Admission Number',
            'Student Name',
            'Class',
            'Grade',
            'Subject',
            'Assessment',
            'Marks Obtained',
            'Total Marks',
            'Percentage',
            'Rating',
            'Teacher Remarks',
        ];

        return response()->streamDownload(function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, ['ADM001', 'Jane Learner', 'Grade 4 East', 'Grade 4', 'Mathematics', 'End Term Assessment', '78', '100', '78', 'ME', 'Good progress']);
            fclose($file);
        }, 'assessment_results_template.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildResultsPayload(Request $request, $schoolId, $activeYear, bool $paginate = true): array
    {
        $filters = [
            'search' => $request->input('search', ''),
            'class_id' => $request->input('class_id', 'all'),
            'grade_level_id' => $request->input('grade_level_id', 'all'),
            'subject_id' => $request->input('subject_id', 'all'),
        ];

        $allowedClassIds = $this->allowedAssessmentClassIds();
        $activeYearIds = $this->currentAcademicYearIds($schoolId);
        $percentageSql = '(student_assessments.marks_obtained / NULLIF(COALESCE(assessments.total_marks, 100), 0)) * 100';

        $rows = \DB::table('student_assessments')
            ->join('assessments', 'student_assessments.assessment_id', '=', 'assessments.id')
            ->join('students', 'student_assessments.student_id', '=', 'students.id')
            ->join('classes', 'assessments.class_id', '=', 'classes.id')
            ->join('subjects', 'assessments.subject_id', '=', 'subjects.id')
            ->leftJoin('grade_levels', 'classes.grade_level_id', '=', 'grade_levels.id')
            ->where('assessments.school_id', $schoolId)
            ->where('students.school_id', $schoolId)
            ->when($activeYearIds->isNotEmpty(), fn($query) => $query->whereIn('assessments.academic_year_id', $activeYearIds))
            ->when($allowedClassIds !== null, fn($query) => $query->whereIn('assessments.class_id', $allowedClassIds))
            ->when($filters['class_id'] !== 'all', fn($query) => $query->where('assessments.class_id', $filters['class_id']))
            ->when($filters['grade_level_id'] !== 'all', fn($query) => $query->where('classes.grade_level_id', $filters['grade_level_id']))
            ->when($filters['subject_id'] !== 'all', fn($query) => $query->where('assessments.subject_id', $filters['subject_id']))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($inner) use ($search) {
                    $inner->where('students.first_name', 'like', "%{$search}%")
                        ->orWhere('students.last_name', 'like', "%{$search}%")
                        ->orWhere('students.admission_number', 'like', "%{$search}%")
                        ->orWhere('assessments.title', 'like', "%{$search}%");
                });
            })
            ->selectRaw("
                student_assessments.student_id,
                CONCAT(COALESCE(students.first_name, ''), ' ', COALESCE(students.last_name, '')) as student_name,
                students.first_name,
                students.last_name,
                students.admission_number,
                students.gender,
                assessments.id as assessment_id,
                assessments.title as assessment_title,
                assessments.assessment_date,
                assessments.class_id,
                classes.name as class_name,
                classes.grade_level_id,
                grade_levels.name as grade_name,
                assessments.subject_id,
                subjects.name as subject_name,
                student_assessments.marks_obtained,
                COALESCE(assessments.total_marks, 100) as total_marks,
                student_assessments.grade_level as rating,
                student_assessments.teacher_comments,
                {$percentageSql} as percentage
            ")
            ->get();

        $students = $this->rankStudentRows($rows);
        $page = max((int) request('page', 1), 1);
        $perPage = 30;
        $studentsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $students->forPage($page, $perPage)->values(),
            $students->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'students' => $paginate ? $studentsPaginator : $students,
            'analytics' => [
                'overallMean' => $this->weightedMean($rows),
                'totalAssessments' => $rows->pluck('assessment_id')->unique()->count(),
                'activeStudents' => $rows->pluck('student_id')->unique()->count(),
                'recordedResults' => $rows->count(),
                'subjectAnalysis' => $this->groupPerformance($rows, 'subject_id', 'subject_name'),
                'classAnalysis' => $this->groupPerformance($rows, 'class_id', 'class_name'),
                'gradeAnalysis' => $this->groupPerformance($rows, 'grade_level_id', 'grade_name'),
                'trend' => $this->trendPerformance($rows),
                'trendByClass' => $this->trendPerformanceByGroup($rows, 'class_id', 'class_name'),
                'trendByGrade' => $this->trendPerformanceByGroup($rows, 'grade_level_id', 'grade_name'),
                'trendBySubject' => $this->trendPerformanceByGroup($rows, 'subject_id', 'subject_name'),
                'distribution' => $this->performanceDistribution($students),
            ],
            'rankings' => [
                'students' => $students->take(20)->values(),
                'classes' => $this->groupPerformance($rows, 'class_id', 'class_name'),
                'grades' => $this->groupPerformance($rows, 'grade_level_id', 'grade_name'),
                'subjects' => $this->groupPerformance($rows, 'subject_id', 'subject_name'),
                'topClass' => $this->groupPerformance($rows, 'class_id', 'class_name')->first(),
                'topGrade' => $this->groupPerformance($rows, 'grade_level_id', 'grade_name')->first(),
                'topSubject' => $this->groupPerformance($rows, 'subject_id', 'subject_name')->first(),
            ],
            'filters' => $filters,
            'classes' => \App\Models\Academic\SchoolClass::where('school_id', $schoolId)
                ->when($allowedClassIds !== null, fn($query) => $query->whereIn('id', $allowedClassIds))
                ->orderBy('name')
                ->get(['id', 'name', 'grade_level_id']),
            'grades' => \App\Models\Academic\GradeLevel::where('school_id', $schoolId)->orderBy('name')->get(['id', 'name']),
            'subjects' => \App\Models\Curriculum\Subject::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function allowedAssessmentClassIds()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('teacher') || $user->hasAnyRole(['super_admin', 'school_admin'])) {
            return null;
        }

        return \App\Models\TeacherSubject::where('teacher_id', $user->teacher?->id)->pluck('class_id')
            ->merge(\App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->pluck('id'))
            ->unique()
            ->values();
    }

    private function weightedMean($rows): float
    {
        $totalMarks = $rows->sum(fn($row) => (float) $row->marks_obtained);
        $totalPossible = $rows->sum(fn($row) => (float) $row->total_marks);

        return $totalPossible > 0 ? round(($totalMarks / $totalPossible) * 100, 1) : 0.0;
    }

    private function groupPerformance($rows, string $idKey, string $nameKey)
    {
        return $rows->filter(fn($row) => $row->{$idKey} !== null)
            ->groupBy($idKey)
            ->map(function ($group) use ($idKey, $nameKey) {
                $maleRows = $group->filter(fn($row) => strtolower((string) $row->gender) === 'male');
                $femaleRows = $group->filter(fn($row) => strtolower((string) $row->gender) === 'female');

                return [
                    'id' => $group->first()->{$idKey},
                    'name' => $group->first()->{$nameKey} ?? 'Unassigned',
                    'score' => $this->weightedMean($group),
                    'students' => $group->pluck('student_id')->unique()->count(),
                    'assessments' => $group->pluck('assessment_id')->unique()->count(),
                    'records' => $group->count(),
                    'male_avg' => $maleRows->isNotEmpty() ? $this->weightedMean($maleRows) : 0,
                    'female_avg' => $femaleRows->isNotEmpty() ? $this->weightedMean($femaleRows) : 0,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });
    }

    private function rankStudentRows($rows)
    {
        $ranked = $rows->groupBy('student_id')
            ->map(function ($group) {
                $recent = $group->sortByDesc('assessment_date')->take(3);
                $mean = $this->weightedMean($group);

                return [
                    'id' => $group->first()->student_id,
                    'first_name' => $group->first()->first_name,
                    'last_name' => $group->first()->last_name,
                    'name' => trim($group->first()->student_name),
                    'admission_number' => $group->first()->admission_number,
                    'current_class_id' => $group->first()->class_id,
                    'class_name' => $group->first()->class_name,
                    'grade_level_id' => $group->first()->grade_level_id,
                    'grade_name' => $group->first()->grade_name,
                    'mean_score' => $mean,
                    'tests_count' => $group->pluck('assessment_id')->unique()->count(),
                    'subjects_count' => $group->pluck('subject_id')->unique()->count(),
                    'total_marks' => round($group->sum(fn($row) => (float) $row->marks_obtained), 1),
                    'total_possible' => round($group->sum(fn($row) => (float) $row->total_marks), 1),
                    'trajectory' => $this->weightedMean($recent) >= $mean ? 'Positive' : 'Watch',
                ];
            })
            ->sortByDesc('mean_score')
            ->values();

        $classRanks = $ranked->groupBy('current_class_id')->map(fn($group) => $group->values()->pluck('id')->flip());
        $gradeRanks = $ranked->groupBy('grade_level_id')->map(fn($group) => $group->values()->pluck('id')->flip());

        return $ranked->map(function ($student, $index) use ($classRanks, $gradeRanks) {
            $student['overall_rank'] = $index + 1;
            $student['class_rank'] = ($classRanks[$student['current_class_id']][$student['id']] ?? 0) + 1;
            $student['grade_rank'] = ($gradeRanks[$student['grade_level_id']][$student['id']] ?? 0) + 1;
            return $student;
        });
    }

    private function trendPerformance($rows)
    {
        return $rows->filter(fn($row) => $row->assessment_date)
            ->groupBy(fn($row) => \Carbon\Carbon::parse($row->assessment_date)->format('M Y'))
            ->map(fn($group, $label) => ['label' => $label, 'score' => $this->weightedMean($group)])
            ->values();
    }

    private function trendPerformanceByGroup($rows, string $idKey, string $nameKey)
    {
        $labels = $rows->filter(fn($row) => $row->assessment_date)
            ->map(fn($row) => \Carbon\Carbon::parse($row->assessment_date)->format('M Y'))
            ->unique()
            ->values();

        return $rows->filter(fn($row) => $row->{$idKey} !== null)
            ->groupBy($idKey)
            ->map(function ($group) use ($labels, $idKey, $nameKey) {
                $points = $group->filter(fn($row) => $row->assessment_date)
                    ->groupBy(fn($row) => \Carbon\Carbon::parse($row->assessment_date)->format('M Y'));

                return [
                    'id' => $group->first()->{$idKey},
                    'name' => $group->first()->{$nameKey} ?? 'Unassigned',
                    'score' => $this->weightedMean($group),
                    'points' => $labels->map(fn($label) => [
                        'label' => $label,
                        'score' => $points->has($label) ? $this->weightedMean($points->get($label)) : null,
                    ])->values(),
                ];
            })
            ->sortByDesc('score')
            ->take(6)
            ->values();
    }

    private function performanceDistribution($students): array
    {
        return [
            'EE' => $students->where('mean_score', '>=', 80)->count(),
            'ME' => $students->whereBetween('mean_score', [60, 79.999])->count(),
            'AE' => $students->whereBetween('mean_score', [40, 59.999])->count(),
            'BE' => $students->where('mean_score', '<', 40)->count(),
        ];
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

        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : null;

        // Teacher — only own assessments
        if ($user->hasRole('teacher') && !$user->hasRole('class_teacher') && !$user->hasRole('hod')) {
            if ($teacherId) {
                $query->where('teacher_id', $teacherId);
            } else {
                $query->where('id', 0); // Failsafe against data leaking
            }
            return;
        }

        // Class teacher — assessments for their class + own
        if ($user->hasRole('class_teacher')) {
            $classIds = \App\Models\Academic\SchoolClass::where('class_teacher_id', $user->id)->pluck('id')->toArray();
            $query->where(function($q) use ($teacherId, $classIds) {
                if ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                }
                if (!empty($classIds)) {
                    $q->orWhereIn('class_id', $classIds);
                }
            });
            return;
        }

        // HoD — assessments for subjects in their department
        if ($user->hasRole('hod') && $teacher?->department_id) {
            $subjectIds = \App\Models\Curriculum\Subject::where('department_id', $teacher->department_id)->pluck('id')->toArray();
            $query->whereIn('subject_id', $subjectIds);
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

        if ($user->hasRole('super_admin') && session()->has('viewing_school_id')) {
            return session('viewing_school_id');
        }

        return $user->school_id;
    }

    private function currentAcademicYearIds($schoolId)
    {
        return \App\Models\Academic\AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->pluck('id');
    }
}
