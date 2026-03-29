<?php

namespace App\Http\Controllers;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentItem;
use App\Models\Assessment\StudentAssessment;
use App\Models\Assessment\StudentEvidence;
use App\Models\Curriculum\Competency;
use App\Models\Curriculum\LearningIndicator;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssessmentWizardController extends Controller
{
    /**
     * Fetch strands for a subject and grade.
     */
    public function getStrands(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
        ]);

        $strands = Strand::where('subject_id', $request->subject_id)
            ->where('grade_level_id', $request->grade_level_id)
            ->orderBy('display_order')
            ->get(['id', 'name', 'code']);

        return response()->json($strands);
    }

    /**
     * Fetch sub-strands for a strand.
     */
    public function getSubStrands(Request $request): JsonResponse
    {
        $request->validate(['strand_id' => 'required|exists:strands,id']);

        $subStrands = SubStrand::where('strand_id', $request->strand_id)
            ->orderBy('display_order')
            ->get(['id', 'name', 'code']);

        return response()->json($subStrands);
    }

    /**
     * Fetch indicators for a sub-strand.
     */
    public function getIndicators(Request $request): JsonResponse
    {
        $request->validate(['sub_strand_id' => 'required|exists:sub_strands,id']);

        $indicators = LearningIndicator::whereHas('learningOutcome', function($q) use ($request) {
            $q->where('sub_strand_id', $request->sub_strand_id);
        })->orderBy('display_order')->get(['id', 'indicator', 'description']);

        return response()->json($indicators);
    }

    /**
     * Store a full CBC assessment from the wizard.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Assessment Info
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'assessment_date' => 'required|date',
            
            // Items (Sub-Strand / Indicator / Competency)
            'items' => 'required|array|min:1',
            'items.*.sub_strand_id' => 'required|exists:sub_strands,id',
            'items.*.performance_indicator_id' => 'nullable|exists:learning_indicators,id',
            'items.*.competency_id' => 'nullable|exists:competencies,id',
            
            // Ratings & Evidence
            'ratings' => 'required|array',
            'ratings.*.student_id' => 'required|exists:students,id',
            'ratings.*.level' => 'required|in:EE,ME,AE,BE',
            'ratings.*.remarks' => 'nullable|string',
            'ratings.*.evidence' => 'nullable|array',
            'ratings.*.evidence.*' => 'file|max:10240', // 10MB per file
        ]);

        return DB::transaction(function() use ($request, $validated) {
            $schoolId = auth()->user()->school_id;
            $activeYear = DB::table('academic_years')->where('is_current', true)->first();
            $activeTerm = DB::table('academic_terms')->where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

            // 1. Create Assessment Header
            $assessment = Assessment::create([
                'school_id' => $schoolId,
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => auth()->id(),
                'academic_year_id' => $activeYear?->id,
                'academic_term_id' => $activeTerm?->id,
                'assessment_type_id' => $validated['assessment_type_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'assessment_date' => $validated['assessment_date'],
                'status' => 'published',
                'created_by' => auth()->id(),
                'weight' => 100, // Default for formative
            ]);

            // 2. Create Assessment Items
            $items = [];
            foreach ($validated['items'] as $index => $itemData) {
                $items[] = AssessmentItem::create(array_merge($itemData, [
                    'school_id' => $schoolId,
                    'assessment_id' => $assessment->id,
                    'display_order' => $index,
                ]));
            }

            // 3. Create Student Assessments (Ratings)
            foreach ($validated['ratings'] as $ratingData) {
                $studentAssessment = StudentAssessment::create([
                    'student_id' => $ratingData['student_id'],
                    'assessment_id' => $assessment->id,
                    'grade_level' => $ratingData['level'],
                    'feedback' => $ratingData['remarks'],
                    'graded_at' => now(),
                    'graded_by' => auth()->id(),
                ]);

                // 4. Handle Evidence Uploads
                if ($request->hasFile("ratings.{$ratingData['student_id']}.evidence")) {
                    $files = $request->file("ratings.{$ratingData['student_id']}.evidence");
                    foreach ($files as $file) {
                        $path = $file->store("evidence/school_{$schoolId}/student_{$ratingData['student_id']}", 'public');
                        
                        // We link evidence to the FIRST item of the assessment by default 
                        // unless we support per-item evidence in the UI later.
                        StudentEvidence::create([
                            'school_id' => $schoolId,
                            'student_id' => $ratingData['student_id'],
                            'assessment_item_id' => $items[0]->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            }

            return redirect()->route('assessments.index')->with('success', 'CBC Assessment recorded successfully.');
        });
    }
}
