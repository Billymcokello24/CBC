<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentItem;
use App\Models\Assessment\StudentAssessmentRating;
use App\Models\Assessment\StudentCompetencyRating;
use App\Models\Student;
use App\Models\Assessment\ReportCard;
use App\Models\Assessment\ReportCardSubject;
use App\Models\Assessment\ReportCardCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class AssessmentGradingController extends Controller
{
    /**
     * Export the grading sheet as a PDF (Ministry Standard).
     */
    public function export(Assessment $assessment)
    {
        $assessment->load(['items.indicator', 'class.gradeLevel', 'subject', 'school']);
        
        $students = Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->get();

        $pdf = Pdf::loadView('exports.assessments.grading-sheet', [
            'assessment' => $assessment,
            'students' => $students,
            'title' => 'SBA LEARNER RECORDING FORM',
            'ministry' => 'REPUBLIC OF KENYA - MINISTRY OF EDUCATION'
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Grading_Sheet_{$assessment->subject->name}_{$assessment->class->name}.pdf");
    }

    /**
     * Download a CSV template for bulk grading.
     */
    public function downloadTemplate(Assessment $assessment)
    {
        $assessment->load(['items', 'class', 'subject']);
        $students = Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->select('admission_number', 'first_name', 'last_name')
            ->get();

        $headers = ['Admission Number', 'Student Name'];
        foreach ($assessment->items as $item) {
            $headers[] = $item->name . ' (ID:' . $item->id . ')';
        }
        $headers[] = 'Teacher Remarks';

        $callback = function() use ($students, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($students as $student) {
                $row = [$student->admission_number, $student->first_name . ' ' . $student->last_name];
                // Fill with empty values for each criterion
                for ($i = 2; $i < count($headers); $i++) {
                    $row[] = '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, "Grading_Template_{$assessment->subject->name}_{$assessment->class->name}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Redirect to the latest active assessment for the current teacher.
     */
    public function latestActive()
    {
        $assessment = Assessment::where('school_id', auth()->user()->school_id)
            ->where('teacher_id', auth()->id())
            ->whereIn('status', ['draft', 'published', 'in_progress'])
            ->latest()
            ->first();

        if (!$assessment) {
            return redirect()->route('assessments.setup')->with('info', 'You have no active assessments. Please create one to start grading.');
        }

        return redirect()->route('assessments.grading', $assessment->id);
    }

    /**
     * Display the assessment grading interface.
     */
    public function index(Assessment $assessment): Response
    {
        $assessment->load(['items.indicator.competency', 'class.gradeLevel', 'subject']);
        
        // Repair generic names if they exist using the indicators JSON or relationship
        foreach ($assessment->items as $index => $item) {
            if (!$item->name || str_starts_with(strtolower($item->name), 'indicator ')) {
                $indicatorData = collect($assessment->indicators)->firstWhere('id', (string)$item->competency_indicator_id);
                if ($indicatorData && isset($indicatorData['indicator'])) {
                    $item->name = $indicatorData['indicator'];
                } elseif ($item->indicator) {
                    $item->name = $item->indicator->indicator;
                }
            }
        }

        $students = Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number', 'photo']);

        $existingRatings = StudentAssessmentRating::whereIn('assessment_item_id', $assessment->items->pluck('id'))
            ->join('assessment_items', 'student_assessment_ratings.assessment_item_id', '=', 'assessment_items.id')
            ->select('student_assessment_ratings.*', 'assessment_items.code', 'assessment_items.name')
            ->get()
            ->groupBy('student_id');

        // Load Total Marks from student_assessments
        $studentAssessments = \App\Models\Assessment\StudentAssessment::where('assessment_id', $assessment->id)
            ->get()
            ->keyBy('student_id');

        // Find the best matching rubric for this subject
        $rubric = \App\Models\Assessment\Rubric::where('school_id', $assessment->school_id)
            ->where(function($q) use ($assessment) {
                $q->where('subject_id', $assessment->subject_id)
                  ->orWhereNull('subject_id');
            })
            ->with(['criteria.levels' => function($q) {
                $q->orderBy('min_score', 'desc');
            }])
            ->orderByRaw('subject_id IS NULL ASC') // Prioritize subject-specific rubrics
            ->first();

        // Calculate summary stats
        $studentOverallRatings = [
            'EE' => 0,
            'ME' => 0,
            'AE' => 0,
            'BE' => 0,
        ];
        
        foreach ($studentAssessments as $sa) {
            if ($sa->grade_level) {
                // Ensure key exists before incrementing
                $level = strtoupper($sa->grade_level);
                if (isset($studentOverallRatings[$level])) {
                    $studentOverallRatings[$level]++;
                }
            }
        }

        $allAssessments = Assessment::where('teacher_id', auth()->id())
            ->with(['class.gradeLevel', 'subject'])
            ->latest()
            ->get();

        return Inertia::render('assessments/CBCGrading', [
            'assessment' => $assessment,
            'allAssessments' => $allAssessments,
            'students' => $students,
            'existingRatings' => $existingRatings,
            'studentAssessments' => $studentAssessments, // Total marks
            'rubric' => $rubric, // Matching rubric
            'stats' => [
                'total' => $students->count(),
                'ee' => $studentOverallRatings['EE'] ?? 0,
                'me' => $studentOverallRatings['ME'] ?? 0,
                'ae' => $studentOverallRatings['AE'] ?? 0,
                'be' => $studentOverallRatings['BE'] ?? 0,
            ],
            'ratingScales' => [
                ['id' => 4, 'code' => 'EE', 'name' => 'Exceeding Expectation', 'color' => '#10b981'],
                ['id' => 3, 'code' => 'ME', 'name' => 'Meeting Expectation', 'color' => '#3b82f6'],
                ['id' => 2, 'code' => 'AE', 'name' => 'Approaching Expectation', 'color' => '#f59e0b'],
                ['id' => 1, 'code' => 'BE', 'name' => 'Below Expectation', 'color' => '#ef4444'],
            ]
        ]);
    }

    /**
     * Store student ratings for an assessment.
     */
    public function store(Request $request, Assessment $assessment)
    {
        $request->validate([
            'ratings.*.student_id' => 'required|exists:students,id',
            'ratings.*.marks' => 'nullable|array', // Item ID => marks
            'ratings.*.total' => 'nullable|numeric|min:0', // Direct Total
            'ratings.*.remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $assessment->load('items');

            foreach ($request->ratings as $ratingData) {
                $studentId = $ratingData['student_id'];

                // 1. Handle Individual Indicator Ratings (Optional)
                if (isset($ratingData['marks'])) {
                    foreach ($ratingData['marks'] as $itemId => $marks) {
                        if (is_null($marks) || $marks === '') {
                            StudentAssessmentRating::where([
                                'student_id' => $studentId,
                                'assessment_item_id' => $itemId,
                            ])->delete();
                            continue;
                        }

                        $item = $assessment->items->find($itemId);
                        if (!$item) continue;

                        $outOf = $item->total_marks ?: 100;
                        $percent = ($marks / $outOf) * 100;
                        
                        $perf = $this->calculatePerformanceLevel($assessment, $percent);

                        StudentAssessmentRating::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'assessment_item_id' => $itemId,
                            ],
                            [
                                'school_id' => $assessment->school_id,
                                'score' => $perf['points'],
                                'marks' => $marks,
                                'out_of' => $outOf,
                                'rating_level' => $perf['level'],
                                'feedback' => $ratingData['remarks'] ?? null,
                                'teacher_id' => auth()->id(),
                            ]
                        );
                    }
                }

                // 2. Handle Master Score (Total)
                $marksObtained = $ratingData['total'] ?? null;
                
                // If total is provided, use it. Otherwise, if indicators were provided, sum them up.
                if (is_null($marksObtained) && isset($ratingData['marks']) && !empty($ratingData['marks'])) {
                     $marksObtained = array_sum(array_filter($ratingData['marks']));
                }

                if (!is_null($marksObtained)) {
                    $totalPossible = $assessment->total_marks ?: $assessment->items->sum('total_marks') ?: 100;
                    $totalPercent = ($marksObtained / $totalPossible) * 100;
                    $totalPerf = $this->calculatePerformanceLevel($assessment, $totalPercent);

                    \App\Models\Assessment\StudentAssessment::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'assessment_id' => $assessment->id,
                        ],
                        [
                            'school_id' => $assessment->school_id,
                            'marks_obtained' => $marksObtained,
                            'percentage' => $totalPercent,
                            'grade_level' => $totalPerf['level'],
                            'teacher_comments' => $ratingData['remarks'] ?? null,
                            'graded_at' => now(),
                            'graded_by' => auth()->id(),
                        ]
                    );
                }
            }

            DB::commit();

            $this->deriveCompetencyRatings($assessment);
            $this->updateReportCards($assessment);

            return back()->with('success', 'Grading synchronized successfully. Terminal report records have been updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Critical Sync Error: ' . $e->getMessage());
        }
    }

    /**
     * Quick save a single rating (Auto-save)
     */
    public function quickSave(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'assessment_id' => 'required|exists:assessments,id',
            'assessment_item_id' => 'nullable|exists:assessment_items,id',
            'marks' => 'nullable|numeric|min:0',
        ]);

        $assessment = Assessment::findOrFail($request->assessment_id);

        // Case A: Saving Master Score (Direct Total)
        if (!$request->assessment_item_id) {
            if (is_null($request->marks) || $request->marks === '') {
                \App\Models\Assessment\StudentAssessment::where([
                    'student_id' => $request->student_id,
                    'assessment_id' => $request->assessment_id,
                ])->delete();
                return response()->json(['success' => true]);
            }

            $totalPossible = $assessment->total_marks ?: $assessment->items->sum('total_marks') ?: 100;
            $percent = ($request->marks / $totalPossible) * 100;
            $perf = $this->calculatePerformanceLevel($assessment, $percent);

            $record = \App\Models\Assessment\StudentAssessment::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'assessment_id' => $request->assessment_id,
                ],
                [
                    'school_id' => $assessment->school_id,
                    'marks_obtained' => $request->marks,
                    'percentage' => $percent,
                    'grade_level' => $perf['level'],
                    'graded_at' => now(),
                    'graded_by' => auth()->id(),
                ]
            );

            $this->updateReportCards($assessment);

            return response()->json(['success' => true, 'record' => $record]);
        }

        // Case B: Saving Individual Indicator Rating
        if (is_null($request->marks) || $request->marks === '') {
            StudentAssessmentRating::where([
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ])->delete();
            return response()->json(['success' => true]);
        }

        $item = AssessmentItem::findOrFail($request->assessment_item_id);
        $outOf = $item->total_marks ?: 100;
        $percent = ($request->marks / $outOf) * 100;
        
        $perf = $this->calculatePerformanceLevel($assessment, $percent);

        $rating = StudentAssessmentRating::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ],
            [
                'school_id' => $assessment->school_id,
                'score' => $perf['points'],
                'marks' => $request->marks,
                'out_of' => $outOf,
                'rating_level' => $perf['level'],
                'teacher_id' => auth()->id(),
            ]
        );

        $this->deriveCompetencyRatings($assessment, $request->student_id);
        $this->updateReportCards($assessment);

        return response()->json(['success' => true, 'rating' => $rating]);
    }

    /**
     * Helper to calculate performance level based on rubric or defaults.
     */
    private function calculatePerformanceLevel(Assessment $assessment, float $percentage): array
    {
        $rubric = \App\Models\Assessment\Rubric::where('school_id', $assessment->school_id)
            ->where(function($q) use ($assessment) {
                $q->where('subject_id', $assessment->subject_id)
                  ->orWhereNull('subject_id');
            })
            ->with(['criteria.levels' => function($q) {
                $q->orderBy('min_score', 'desc');
            }])
            ->orderByRaw('subject_id IS NULL ASC')
            ->first();

        if ($rubric && $rubric->criteria->isNotEmpty()) {
            foreach ($rubric->criteria[0]->levels as $level) {
                if ($percentage >= $level->min_score && $percentage <= $level->max_score) {
                    return [
                        'points' => (float) $level->points,
                        'level' => $level->grade_code,
                        'descriptor' => $level->level_name
                    ];
                }
            }
        }

        // Standard CBC Falling back
        return match(true) {
            $percentage >= 80 => ['points' => 4, 'level' => 'EE', 'descriptor' => 'Exceeding Expectation'],
            $percentage >= 60 => ['points' => 3, 'level' => 'ME', 'descriptor' => 'Meeting Expectation'],
            $percentage >= 40 => ['points' => 2, 'level' => 'AE', 'descriptor' => 'Approaching Expectation'],
            default => ['points' => 1, 'level' => 'BE', 'descriptor' => 'Below Expectation']
        };
    }

    /**
     * Bulk import marks for a specific assessment.
     */
    public function importMarks(Request $request, Assessment $assessment)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $headers = array_shift($data);

        $itemColMap = [];
        foreach ($headers as $index => $header) {
            if (preg_match('/\(ID:(\d+)\)/', $header, $matches)) {
                $itemColMap[$matches[1]] = $index;
            }
        }

        $successCount = 0;
        foreach ($data as $row) {
            $admNo = trim($row[0]);
            $student = Student::where('admission_number', $admNo)->first();
            if (!$student) continue;

            foreach ($itemColMap as $itemId => $colIndex) {
                $marks = trim($row[$colIndex]);
                if ($marks === '') continue;

                $item = AssessmentItem::find($itemId);
                if (!$item) continue;

                $outOf = $item->total_marks ?: 100;
                $percent = ($marks / $outOf) * 100;
                
                $score = match(true) {
                    $percent >= 80 => 4,
                    $percent >= 60 => 3,
                    $percent >= 40 => 2,
                    default => 1
                };

                $ratingLevel = match(intval($score)) {
                    4 => 'EE',
                    3 => 'ME',
                    2 => 'AE',
                    default => 'BE'
                };

                StudentAssessmentRating::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'assessment_item_id' => $itemId,
                    ],
                    [
                        'score' => $score,
                        'marks' => $marks,
                        'out_of' => $outOf,
                        'rating_level' => $ratingLevel,
                        'teacher_id' => auth()->id(),
                    ]
                );
            }
            $successCount++;
        }

        $this->deriveCompetencyRatings($assessment);
        $this->updateReportCards($assessment);

        return back()->with('success', "Successfully imported marks for {$successCount} students.");
    }

    /**
     * Update/Generate report cards for students in the assessment class.
     */
    protected function updateReportCards(Assessment $assessment)
    {
        $students = Student::where('current_class_id', $assessment->class_id)->get();
        
        foreach ($students as $student) {
            $reportCard = ReportCard::firstOrCreate([
                'student_id' => $student->id,
                'academic_term_id' => $assessment->academic_term_id,
                'academic_year_id' => $assessment->academic_year_id,
            ], [
                'school_id' => $assessment->school_id,
                'class_id' => $assessment->class_id,
                'status' => 'draft',
            ]);

            // Get all assessments for this subject in this term
            $termAssessments = Assessment::where('subject_id', $assessment->subject_id)
                ->where('class_id', $assessment->class_id)
                ->where('academic_term_id', $assessment->academic_term_id)
                ->with('items')
                ->get();

            $totalPossibleMarks = 0;
            $totalObtainedMarks = 0;

            foreach ($termAssessments as $termAss) {
                // 1. Check if there's a Master Score (StudentAssessment)
                $sa = \App\Models\Assessment\StudentAssessment::where([
                    'student_id' => $student->id,
                    'assessment_id' => $termAss->id
                ])->first();

                $possible = $termAss->total_marks ?: $termAss->items->sum('total_marks') ?: 100;
                $obtained = 0;

                if ($sa) {
                    $obtained = $sa->marks_obtained;
                } else {
                    // 2. Fallback to indicators (StudentAssessmentRating)
                    $itemIds = $termAss->items->pluck('id');
                    $obtained = StudentAssessmentRating::where('student_id', $student->id)
                        ->whereIn('assessment_item_id', $itemIds)
                        ->sum('marks') ?: 0;
                }

                $totalPossibleMarks += $possible;
                $totalObtainedMarks += $obtained;
            }

            if ($totalPossibleMarks > 0) {
                $terminalPercentage = ($totalObtainedMarks / $totalPossibleMarks) * 100;
                
                // Use the shared calculation logic for terminal grading
                $terminalPerf = $this->calculatePerformanceLevel($assessment, $terminalPercentage);

                ReportCardSubject::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'subject_id' => $assessment->subject_id,
                    ],
                    [
                        'marks_obtained' => $totalObtainedMarks,
                        'total_marks' => $totalPossibleMarks,
                        'percentage' => $terminalPercentage,
                        'grade' => $terminalPerf['level'],
                    ]
                );
            }

            $competencyRatings = StudentCompetencyRating::where('student_id', $student->id)
                ->where('academic_term_id', $assessment->academic_term_id)
                ->get();

            foreach ($competencyRatings as $rating) {
                ReportCardCompetency::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'competency_id' => $rating->competency_id,
                    ],
                    [
                        'rating' => $rating->rating_level,
                        'comments' => $rating->comments,
                    ]
                );
            }

            $allSubjects = ReportCardSubject::where('report_card_id', $reportCard->id)->get();
            if ($allSubjects->isNotEmpty()) {
                $avgPct = $allSubjects->avg('percentage');
                $reportCard->update([
                    'total_subjects' => $allSubjects->count(),
                    'average_score' => $avgPct,
                    'overall_grade' => match(true) {
                        $avgPct >= 80 => 'EE',
                        $avgPct >= 60 => 'ME',
                        $avgPct >= 40 => 'AE',
                        default => 'BE'
                    }
                ]);
            }
        }
    }

    /**
     * Derive competency ratings based on indicator performance.
     */
    protected function deriveCompetencyRatings(Assessment $assessment, $studentId = null)
    {
        $items = $assessment->items()->with('indicator')->get();
        $competencyIds = $items->pluck('indicator.competency_id')->unique()->filter();

        $studentsQuery = Student::where('current_class_id', $assessment->class_id);
        if ($studentId) {
            $studentsQuery->where('id', $studentId);
        }
        $students = $studentsQuery->get();

        foreach ($students as $student) {
            foreach ($competencyIds as $compId) {
                $relevantItemIds = $items->where('indicator.competency_id', $compId)->pluck('id');
                
                $ratings = StudentAssessmentRating::where('student_id', $student->id)
                    ->whereIn('assessment_item_id', $relevantItemIds)
                    ->get();

                if ($ratings->isEmpty()) continue;

                $avgScore = $ratings->avg('score');
                $derivedLevel = match(true) {
                    $avgScore >= 3.5 => 'EE',
                    $avgScore >= 2.5 => 'ME',
                    $avgScore >= 1.5 => 'AE',
                    default => 'BE'
                };

                StudentCompetencyRating::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'competency_id' => $compId,
                        'subject_id' => $assessment->subject_id,
                        'academic_year_id' => $assessment->academic_year_id,
                        'academic_term_id' => $assessment->academic_term_id,
                    ],
                    [
                        'school_id' => $assessment->school_id,
                        'rating_level' => $derivedLevel,
                        'score' => $avgScore,
                        'teacher_id' => auth()->id(),
                    ]
                );
            }
        }
    }
}
