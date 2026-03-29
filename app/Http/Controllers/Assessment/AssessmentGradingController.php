<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAssessmentRating;
use App\Models\Assessment\StudentCompetencyRating;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentGradingController extends Controller
{
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
            ->get()
            ->groupBy('student_id');

        return Inertia::render('assessments/CBCGrading', [
            'assessment' => $assessment,
            'students' => $students,
            'existingRatings' => $existingRatings,
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

                $ratingLevel = match(intval($ratingData['rating'])) {
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
                        'score' => $ratingData['rating'],
                        'rating_level' => $ratingLevel,
                        'feedback' => $ratingData['feedback'] ?? null,
                        'teacher_id' => $request->user()->id,
                    ]
                );
            }

            DB::commit();

            // After successful store, derive competency ratings
            $this->deriveCompetencyRatings($assessment);

            return back()->with('success', 'Grading saved successfully.');
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
        ]);

        if (is_null($request->rating)) {
            StudentAssessmentRating::where([
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ])->delete();
            
            return response()->json(['success' => true]);
        }

        $ratingLevel = match(intval($request->rating)) {
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
                'score' => $request->rating,
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
