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
            'subjects' => Subject::orderBy('name')->get(),
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
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'total_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,published,closed',
            'source' => 'required|in:internal,ministry',
            'indicators' => 'required|array|min:1',
            'indicators.*.id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (in_array($value, ['knowledge', 'skills', 'communication', 'values', 'creativity', 'thinking'])) {
                        return;
                    }
                    if (!\App\Models\Curriculum\CompetencyIndicator::where('id', $value)->exists()) {
                        $fail('The selected learning indicator does not exist.');
                    }
                }
            ],
            'indicators.*.indicator' => 'nullable|string',
            'indicators.*.name' => 'nullable|string',
            'indicators.*.max_score' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        
        // If user is an admin and not a teacher, use the class teacher
        if (!$teacher) {
            $schoolClass = \App\Models\Academic\SchoolClass::find($validated['class_id']);
            if ($schoolClass && $schoolClass->class_teacher_id) {
                $teacher = \App\Models\Teacher::where('user_id', $schoolClass->class_teacher_id)->first();
            }
        }

        // Final fallback: if still no teacher, pick the first teacher in the school
        if (!$teacher) {
             $teacher = \App\Models\Teacher::where('school_id', $user->school_id)->first();
        }
        
        if (!$teacher) {
            abort(404, 'No teacher record found to associate with this assessment.');
        }

        $assessmentId = \DB::transaction(function () use ($validated, $request, $teacher, $user) {
            $assessment = Assessment::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'assessment_type_id' => $validated['type_id'], 
                'total_marks' => $request->input('total_marks', 100),
                'passing_marks' => $request->input('passing_marks', 50),
                'weight' => $request->input('weight', 10),
                'teacher_id' => $teacher->id,
                'school_id' => $user->school_id,
                'academic_year_id' => $validated['academic_year_id'],
                'academic_term_id' => $validated['term_id'],
                'assessment_date' => $request->input('date', now()->toDateString()),
                'status' => $request->input('status', 'draft'),
                'source' => $validated['source'],
                'indicators' => $validated['indicators'], // Save indicators to JSON field
                'created_by' => $user->id,
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
                        'competency_indicator_id' => is_numeric($item['id']) ? $item['id'] : null,
                        'name' => $item['indicator'] ?? 'Indicator ' . ($index + 1),
                        'code' => !is_numeric($item['id']) ? $item['id'] : null,
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
