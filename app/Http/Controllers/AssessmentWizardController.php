<?php

namespace App\Http\Controllers;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Curriculum\Competency;
use App\Models\Curriculum\CompetencyIndicator;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentItem;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\Subject;
use App\Models\Academic\AcademicTerm;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentWizardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('assessments/Setup', [
            'gradeLevels' => GradeLevel::with('classes')->get(),
            'subjects' => Subject::all(),
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::all(),
            'academicTerms' => AcademicTerm::active()->get(),
            'competencies' => Competency::all(),
            'ratingScales' => [
                ['id' => 'EE', 'name' => 'Exceeding Expectation (4)'],
                ['id' => 'ME', 'name' => 'Meeting Expectation (3)'],
                ['id' => 'AE', 'name' => 'Approaching Expectation (2)'],
                ['id' => 'BE', 'name' => 'Below Expectation (1)'],
            ]
        ]);
    }

    public function getStrands(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $strands = \App\Models\Curriculum\Strand::where('subject_id', $request->subject_id)->get();
        return response()->json($strands);
    }

    public function getSubStrands(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'strand_id' => 'required|exists:strands,id',
        ]);

        $subStrands = \App\Models\Curriculum\SubStrand::where('strand_id', $request->strand_id)->get();
        return response()->json($subStrands);
    }

    public function getIndicators(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'sub_strand_id' => 'required|exists:sub_strands,id',
        ]);

        $indicators = CompetencyIndicator::where('grade_level_id', $request->grade_level_id)
            ->where('sub_strand_id', $request->sub_strand_id)
            ->get();

        return response()->json($indicators);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:assessment_types,id',
            'term_id' => 'required|exists:academic_terms,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'indicators' => 'required|array|min:1',
            'indicators.*.id' => 'required|exists:competency_indicators,id',
            'indicators.*.max_score' => 'nullable|numeric',
        ]);

        \DB::transaction(function () use ($validated, $request) {
            $assessment = Assessment::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'assessment_type_id' => $validated['type_id'], // Map to ID
                'teacher_id' => $request->user()->id,
                'school_id' => $request->user()->teacher?->school_id ?? \App\Models\School::first()->id,
                'academic_year_id' => \App\Models\Academic\AcademicYear::where('is_current', true)->first()?->id,
                'academic_term_id' => \App\Models\Academic\AcademicTerm::where('is_current', true)->first()?->id,
                'assessment_date' => now(),
                'status' => 'draft',
            ]);

            foreach ($validated['indicators'] as $index => $item) {
                AssessmentItem::create([
                    'assessment_id' => $assessment->id,
                    'competency_indicator_id' => $item['id'],
                    'max_score' => $item['max_score'] ?? 4,
                    'display_order' => $index,
                ]);
            }
        });

        return redirect()->route('assessments.index')->with('success', 'Assessment setup completed.');
    }
}
