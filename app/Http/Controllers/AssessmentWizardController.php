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
        $schoolId = auth()->user()->school_id;
        
        return Inertia::render('assessments/Setup', [
            'gradeLevels' => GradeLevel::where('school_id', $schoolId)->with('classes')->get(),
            'subjects' => Subject::whereHas('schoolSubjects', fn($q) => $q->where('school_id', $schoolId)->where('is_offered', true))->get(),
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::where('school_id', $schoolId)->get(),
            'academicTerms' => AcademicTerm::where('school_id', $schoolId)->get(),
            'academicYears' => \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->get(),
            'competencies' => Competency::where('school_id', $schoolId)->get(),
            'ratingScales' => [
                ['id' => 4, 'code' => 'EE', 'name' => 'Exceeding Expectation', 'color' => '#10b981'],
                ['id' => 3, 'code' => 'ME', 'name' => 'Meeting Expectation', 'color' => '#3b82f6'],
                ['id' => 2, 'code' => 'AE', 'name' => 'Approaching Expectation', 'color' => '#f59e0b'],
                ['id' => 1, 'code' => 'BE', 'name' => 'Below Expectation', 'color' => '#ef4444'],
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
            'source' => 'required|in:internal,ministry',
            'indicators' => 'required|array|min:1',
            'indicators.*.id' => 'required|exists:competency_indicators,id',
            'indicators.*.max_score' => 'nullable|numeric',
        ]);

        $assessmentId = \DB::transaction(function () use ($validated, $request) {
            $assessment = Assessment::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'assessment_type_id' => $validated['type_id'], 
                'teacher_id' => $request->user()->id,
                'school_id' => $request->user()->school_id,
                'academic_year_id' => $validated['academic_year_id'] ?? \App\Models\Academic\AcademicYear::where('school_id', $request->user()->school_id)->where('is_current', true)->first()?->id,
                'academic_term_id' => $validated['term_id'] ?? \App\Models\Academic\AcademicTerm::where('school_id', $request->user()->school_id)->where('is_current', true)->first()?->id,
                'assessment_date' => $validated['date'],
                'status' => 'draft',
                'source' => $validated['source'],
                'indicators' => $validated['indicators'], // Save indicators to JSON field
                'created_by' => $request->user()->id,
            ]);

            // If it's a ministry/standard assessment, create the 6 standard criteria items
            if ($validated['source'] === 'ministry') {
                $standardCriteria = [
                    'knowledge' => 'Knowledge & Understanding',
                    'skills' => 'Skills Application',
                    'communication' => 'Communication',
                    'values' => 'Values & Attitudes',
                    'creativity' => 'Creativity',
                    'thinking' => 'Critical Thinking'
                ];

                reset($standardCriteria);
                $i = 0;
                foreach ($standardCriteria as $code => $name) {
                    AssessmentItem::create([
                        'assessment_id' => $assessment->id,
                        'name' => $name,
                        'code' => $code, // I hope AssessmentItem has a code field, if not name is enough
                        'max_score' => 4,
                        'display_order' => $i++,
                    ]);
                }
            } else {
                // For internal assessments, use the selected indicators as items
                foreach ($validated['indicators'] as $index => $item) {
                    AssessmentItem::create([
                        'assessment_id' => $assessment->id,
                        'competency_indicator_id' => $item['id'],
                        'name' => $item['indicator'] ?? 'Indicator ' . ($index + 1),
                        'max_score' => $item['max_score'] ?? 4,
                        'display_order' => $index,
                    ]);
                }
            }

            return $assessment->id;
        });

        return redirect()->route('assessments.grading', ['assessment' => $assessmentId])
            ->with('success', 'Assessment setup completed. You can now begin grading.');
    }
}
