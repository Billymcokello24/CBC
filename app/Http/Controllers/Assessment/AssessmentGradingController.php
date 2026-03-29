<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\StudentAssessmentRating;
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
        
        $students = Student::where('school_class_id', $assessment->school_class_id)
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
                StudentAssessmentRating::updateOrCreate(
                    [
                        'student_id' => $ratingData['student_id'],
                        'assessment_item_id' => $ratingData['assessment_item_id'],
                    ],
                    [
                        'rating' => $ratingData['rating'],
                        'feedback' => $ratingData['feedback'] ?? null,
                    ]
                );
            }

            DB::commit();

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

        $rating = StudentAssessmentRating::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'assessment_item_id' => $request->assessment_item_id,
            ],
            [
                'rating' => $request->rating,
            ]
        );

        return response()->json([
            'success' => true,
            'rating' => $rating
        ]);
    }
}
