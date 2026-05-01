<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
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
        $assessment->load(['items.indicator', 'class', 'subject']);
        
        $students = Student::where('current_class_id', $assessment->class_id)
            ->orderBy('first_name')
            ->get();

        $existingRatings = StudentAssessmentRating::whereIn('assessment_item_id', $assessment->items->pluck('id'))
            ->join('assessment_items', 'student_assessment_ratings.assessment_item_id', '=', 'assessment_items.id')
            ->select('student_assessment_ratings.*', 'assessment_items.code', 'assessment_items.name')
            ->get()
            ->groupBy('student_id');

        // Calculate summary stats
        $ratings = StudentAssessmentRating::whereIn('assessment_item_id', $assessment->items->pluck('id'))->get();
        $studentOverallRatings = [];
        
        foreach ($students as $student) {
            $studentRatings = $ratings->where('student_id', $student->id);
            if ($studentRatings->isNotEmpty()) {
                $avg = $studentRatings->avg('score');
                $level = match(true) {
                    $avg >= 3.5 => 'EE',
                    $avg >= 2.5 => 'ME',
                    $avg >= 1.5 => 'AE',
                    default => 'BE'
                };
                $studentOverallRatings[$level] = ($studentOverallRatings[$level] ?? 0) + 1;
            }
        }

        $allAssessments = Assessment::where('teacher_id', auth()->id())
            ->with(['class', 'subject'])
            ->latest()
            ->get();

        return Inertia::render('assessments/CBCGrading', [
            'assessment' => $assessment,
            'allAssessments' => $allAssessments,
            'students' => $students,
            'existingRatings' => $existingRatings,
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
            'ratings' => 'required|array',
            'ratings.*.student_id' => 'required|exists:students,id',
            'ratings.*.assessment_item_id' => 'required|exists:assessment_items,id',
            'ratings.*.rating' => 'nullable|integer|min:1|max:4',
            'ratings.*.marks' => 'nullable|numeric|min:0',
            'ratings.*.out_of' => 'nullable|integer|min:1',
            'ratings.*.feedback' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->ratings as $ratingData) {
                if (is_null($ratingData['rating'])) {
                    StudentAssessmentRating::where([
                        'student_id' => $ratingData['student_id'],
                        'assessment_item_id' => $ratingData['assessment_item_id'],
                    ])->delete();
                    continue;
                }

                $score = $ratingData['rating'];
                
                // If marks are provided, calculate score (1-4)
                if (isset($ratingData['marks']) && isset($ratingData['out_of'])) {
                    $percent = ($ratingData['marks'] / $ratingData['out_of']) * 100;
                    $score = match(true) {
                        $percent >= 80 => 4,
                        $percent >= 60 => 3,
                        $percent >= 40 => 2,
                        default => 1
                    };
                }

                $ratingLevel = match(intval($score)) {
                    4 => 'EE',
                    3 => 'ME',
                    2 => 'AE',
                    1 => 'BE',
                    default => null
                };

                StudentAssessmentRating::updateOrCreate(
                    [
                        'student_id' => $ratingData['student_id'],
                        'assessment_item_id' => $ratingData['assessment_item_id'],
                    ],
                    [
                        'score' => $score,
                        'marks' => $ratingData['marks'] ?? null,
                        'out_of' => $ratingData['out_of'] ?? 100,
                        'rating_level' => $ratingLevel,
                        'feedback' => $ratingData['feedback'] ?? null,
                        'teacher_id' => $request->user()->id,
                    ]
                );
            }

            DB::commit();

            // After successful store, derive competency ratings
            $this->deriveCompetencyRatings($assessment);
            
            // Auto-generate/Update Report Cards
            $this->updateReportCards($assessment);

            return back()->with('success', 'Grading saved successfully. Report cards have been updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save grading: ' . $e->getMessage());
        }
    }

    /**
     * Quick save a single rating (Auto-save)
     */
    public function quickSave(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'assessment_item_id' => 'required|exists:assessment_items,id',
            'rating' => 'nullable|integer|min:1|max:4',
            'marks' => 'nullable|numeric|min:0',
            'out_of' => 'nullable|integer|min:1',
        ]);

        if (is_null($request->rating)) {
            StudentAssessmentRating::where([
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ])->delete();
            
            return response()->json(['success' => true]);
        }

        $score = $request->rating;

        if ($request->filled('marks')) {
            $outOf = $request->out_of ?? 100;
            $percent = ($request->marks / $outOf) * 100;
            $score = match(true) {
                $percent >= 80 => 4,
                $percent >= 60 => 3,
                $percent >= 40 => 2,
                default => 1
            };
        }

        $ratingLevel = match(intval($score)) {
            4 => 'EE',
            3 => 'ME',
            2 => 'AE',
            1 => 'BE',
            default => null
        };

        $rating = StudentAssessmentRating::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ],
            [
                'score' => $score,
                'marks' => $request->marks,
                'out_of' => $request->out_of ?? 100,
                'rating_level' => $ratingLevel,
                'teacher_id' => $request->user()->id,
            ]
        );

        // Derive competency ratings for this specific student/assessment
        $assessment = $rating->item->assessment;
        $this->deriveCompetencyRatings($assessment, $request->student_id);

        return response()->json([
            'success' => true,
            'rating' => $rating
        ]);
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

        // Find the column indices for items
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
                $ratingVal = trim($row[$colIndex]);
                if ($ratingVal === '') continue;

                $score = intval($ratingVal);
                if ($score < 1 || $score > 4) continue;

                $ratingLevel = match($score) {
                    4 => 'EE',
                    3 => 'ME',
                    2 => 'AE',
                    1 => 'BE',
                    default => 'BE'
                };

                StudentAssessmentRating::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'assessment_item_id' => $itemId,
                    ],
                    [
                        'score' => $score,
                        'rating_level' => $ratingLevel,
                        'teacher_id' => auth()->id(),
                    ]
                );
            }
            $successCount++;
        }

        // Trigger updates
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
            // Find or create report card for this term
            $reportCard = ReportCard::firstOrCreate([
                'student_id' => $student->id,
                'academic_term_id' => $assessment->academic_term_id,
                'academic_year_id' => $assessment->academic_year_id,
            ], [
                'school_id' => $assessment->school_id,
                'class_id' => $assessment->class_id,
                'status' => 'draft',
            ]);

            // Calculate subject average for this student
            $allAssessmentsInTerm = Assessment::where('subject_id', $assessment->subject_id)
                ->where('academic_term_id', $assessment->academic_term_id)
                ->pluck('id');

            $allRatings = StudentAssessmentRating::where('student_id', $student->id)
                ->whereHas('item', function($q) use ($allAssessmentsInTerm) {
                    $q->whereIn('assessment_id', $allAssessmentsInTerm);
                })
                ->get();

            if ($allRatings->isNotEmpty()) {
                $avgScore = $allRatings->avg('score');
                $grade = match(true) {
                    $avgScore >= 3.5 => 'EE',
                    $avgScore >= 2.5 => 'ME',
                    $avgScore >= 1.5 => 'AE',
                    default => 'BE'
                };

                ReportCardSubject::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'subject_id' => $assessment->subject_id,
                    ],
                    [
                        'marks_obtained' => $avgScore,
                        'total_marks' => 4.0,
                        'percentage' => ($avgScore / 4) * 100,
                        'grade' => $grade,
                    ]
                );
            }

            // Sync competency ratings for the report card
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

            // Update overall report card totals
            $subjectResults = ReportCardSubject::where('report_card_id', $reportCard->id)->get();
            if ($subjectResults->isNotEmpty()) {
                $reportCard->update([
                    'total_subjects' => $subjectResults->count(),
                    'average_score' => $subjectResults->avg('percentage'),
                    'overall_grade' => match(true) {
                        $subjectResults->avg('marks_obtained') >= 3.5 => 'EE',
                        $subjectResults->avg('marks_obtained') >= 2.5 => 'ME',
                        $subjectResults->avg('marks_obtained') >= 1.5 => 'AE',
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
                // Get all indicator ratings for this competency in this assessment
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
                        'teacher_id' => $assessment->teacher_id,
                    ]
                );
            }
        }
    }
}
