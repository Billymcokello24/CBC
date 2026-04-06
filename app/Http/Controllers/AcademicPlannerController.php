<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\SchemeOfWork;
use App\Models\Curriculum\SchemeEntry;
use App\Models\Curriculum\LessonPlan;
use App\Models\Curriculum\Subject;
use App\Models\Academic\GradeLevel;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolClass;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AcademicPlannerController extends Controller
{
    public function schemes(Request $request): Response
    {
        $user = Auth::user();
        $query = SchemeOfWork::with(['subject', 'gradeLevel', 'academicTerm', 'preparedBy']);

        // Role-based filtering
        if (!$user->hasRole(['admin', 'principal'])) {
            $query->where('prepared_by', $user->id);
        }

        return Inertia::render('curriculum/planner/Schemes', [
            'schemes' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
            'terms' => AcademicTerm::whereHas('academicYear', fn($q) => $q->where('is_current', true))->get(),
        ]);
    }

    public function showScheme(SchemeOfWork $scheme): Response
    {
        $scheme->load(['subject', 'gradeLevel', 'academicTerm', 'preparedBy', 'entries.strand', 'entries.subStrand', 'entries.lessonPlan']);

        return Inertia::render('curriculum/planner/SchemeDetails', [
            'scheme' => $scheme,
            'strands' => Strand::where('subject_id', $scheme->subject_id)
                ->where('grade_level_id', $scheme->grade_level_id)
                ->get(['id', 'name']),
        ]);
    }

    public function storeScheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_weeks' => 'required|integer|min:1',
            'lessons_per_week' => 'required|integer|min:1',
        ]);

        $activeYear = AcademicYear::where('is_current', true)->first();

        SchemeOfWork::create(array_merge($validated, [
            'school_id' => Auth::user()->school_id,
            'academic_year_id' => $activeYear?->id,
            'prepared_by' => Auth::id(),
            'status' => 'draft',
        ]));

        return back()->with('success', 'Scheme of work created successfully.');
    }

    public function lessonPlans(Request $request): Response
    {
        $user = Auth::user();
        $query = LessonPlan::with(['subject', 'classroom', 'academicTerm', 'teacher']);

        if (!$user->hasRole(['admin', 'principal'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $query->where('teacher_id', $teacher?->id ?? 0);
        }

        return Inertia::render('curriculum/planner/LessonPlans', [
            'plans' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
            'classes' => SchoolClass::active()->get(['id', 'name']),
            'terms' => AcademicTerm::whereHas('academicYear', fn($q) => $q->where('is_current', true))->get(),
            'strands' => \App\Models\Curriculum\Strand::all(['id', 'name', 'subject_id', 'grade_level_id']),
            'sub_strands' => \App\Models\Curriculum\SubStrand::all(['id', 'name', 'strand_id']),
            'assessmentTypes' => \App\Models\Assessment\AssessmentType::all(['id', 'name']),
            'rubrics' => \App\Models\Assessment\Rubric::all(['id', 'name', 'subject_id', 'assessment_type_id']),
        ]);
    }

    public function storeLessonPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'strand_id' => 'nullable|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'scheme_entry_id' => 'nullable|exists:scheme_entries,id',
            'title' => 'required|string|max:255',
            'lesson_date' => 'required|date',
            'week_number' => 'nullable|string',
            'period_number' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'specific_objectives' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'key_vocabulary' => 'nullable|string',
            'teaching_aids' => 'nullable|string',
            'references' => 'nullable|string',
            'introduction' => 'nullable|string',
            'lesson_development' => 'nullable|string',
            'teacher_activities' => 'nullable|string',
            'learner_activities' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'assessment_methods' => 'nullable|string',
            'reflection' => 'nullable|string',
            'homework' => 'nullable|string',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return back()->with('error', 'Only teachers can create lesson plans.');
        }

        LessonPlan::create(array_merge($validated, [
            'school_id' => Auth::user()->school_id,
            'teacher_id' => $teacher->id,
            'status' => 'draft',
        ]));

        return back()->with('success', 'Lesson plan created successfully.');
    }

    // --- Approval Workflows ---

    public function submitScheme(SchemeOfWork $scheme): RedirectResponse
    {
        $scheme->update(['status' => 'submitted']);
        return back()->with('success', 'Scheme of work submitted for review.');
    }

    public function approveScheme(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $scheme->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Scheme of work approved.');
    }

    public function rejectScheme(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $scheme->update(['status' => 'draft']);
        return back()->with('info', 'Scheme of work returned to draft.');
    }

    public function submitLessonPlan(LessonPlan $plan): RedirectResponse
    {
        $plan->update(['status' => 'pending']);
        return back()->with('success', 'Lesson plan submitted for review.');
    }

    public function approveLessonPlan(Request $request, LessonPlan $plan): RedirectResponse
    {
        $plan->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Lesson plan approved.');
    }

    public function rejectLessonPlan(Request $request, LessonPlan $plan): RedirectResponse
    {
        $plan->update([
            'status' => 'draft',
            'feedback' => $request->feedback
        ]);
        return back()->with('info', 'Lesson plan returned for revision.');
    }

    public function updateScheme(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_weeks' => 'required|integer|min:1',
            'lessons_per_week' => 'required|integer|min:1',
        ]);

        $scheme->update($validated);
        return back()->with('success', 'Scheme updated.');
    }

    public function destroyScheme(SchemeOfWork $scheme): RedirectResponse
    {
        $scheme->delete();
        return back()->with('success', 'Scheme removed.');
    }

    public function updateLessonPlan(Request $request, LessonPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'lesson_date' => 'required|date',
            'week_number' => 'nullable|string',
            'period_number' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'specific_objectives' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'key_vocabulary' => 'nullable|string',
            'teaching_aids' => 'nullable|string',
            'references' => 'nullable|string',
            'introduction' => 'nullable|string',
            'lesson_development' => 'nullable|string',
            'teacher_activities' => 'nullable|string',
            'learner_activities' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'assessment_methods' => 'nullable|string',
            'reflection' => 'nullable|string',
            'homework' => 'nullable|string',
            'strand_id' => 'nullable|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'scheme_entry_id' => 'nullable|exists:scheme_entries,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $plan->update($validated);
        return back()->with('success', 'Lesson plan updated.');
    }

    public function destroyLessonPlan(LessonPlan $plan): RedirectResponse
    {
        $plan->delete();
        return back()->with('success', 'Lesson plan removed.');
    }

    public function storeSchemeEntry(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1',
            'lesson_number' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer',
            'lesson_date' => 'nullable|date',
            'strand_id' => 'required|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'topic' => 'required|string|max:255',
            'key_vocabulary' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'learning_activities' => 'nullable|string',
            'teacher_activities' => 'nullable|string',
            'introduction' => 'nullable|string',
            'lesson_development' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'resources' => 'nullable|string',
            'references' => 'nullable|string',
            'assessment' => 'nullable|string',
            'remarks' => 'nullable|string',
            'reflection' => 'nullable|string',
            'homework' => 'nullable|string',
            'core_competencies' => 'nullable|array',
            'pci' => 'nullable|array',
            'inquiry_questions' => 'nullable|string',
        ]);

        $scheme->entries()->create(array_merge($validated, [
            'school_id' => $scheme->school_id,
        ]));

        return back()->with('success', 'Entry added to scheme.');
    }

    public function updateSchemeEntry(Request $request, SchemeOfWork $scheme, SchemeEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1',
            'lesson_number' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer',
            'lesson_date' => 'nullable|date',
            'strand_id' => 'required|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'topic' => 'required|string|max:255',
            'key_vocabulary' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'learning_activities' => 'nullable|string',
            'teacher_activities' => 'nullable|string',
            'introduction' => 'nullable|string',
            'lesson_development' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'resources' => 'nullable|string',
            'references' => 'nullable|string',
            'assessment' => 'nullable|string',
            'remarks' => 'nullable|string',
            'reflection' => 'nullable|string',
            'homework' => 'nullable|string',
            'core_competencies' => 'nullable|array',
            'pci' => 'nullable|array',
            'inquiry_questions' => 'nullable|string',
        ]);

        // Ensure array fields are not null if sent as empty
        $validated['core_competencies'] = $request->input('core_competencies', []);
        $validated['pci'] = $request->input('pci', []);

        $entry->update($validated);

        return back()->with('success', 'Entry updated successfully.');
    }

    public function downloadSchemePdf(SchemeOfWork $scheme)
    {
        $scheme->load([
            'school',
            'subject', 
            'gradeLevel', 
            'academicTerm.academicYear', 
            'preparedBy', 
            'entries.strand', 
            'entries.subStrand'
        ]);

        $school = $scheme->school;
        $themeColor = $school?->getSetting('pdf_theme_color', '#1e40af') ?? '#1e40af';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.scheme', compact('scheme', 'school', 'themeColor'))
            ->setPaper('a4', 'landscape');
            
        $filename = "Scheme_of_Work_" . ($scheme->subject?->name ?? 'Export') . "_" . ($scheme->gradeLevel?->name ?? '') . ".pdf";
        return $pdf->download($filename);
    }

    public function downloadSchemeEntryPdf(SchemeOfWork $scheme, SchemeEntry $entry)
    {
        $scheme->load(['school', 'subject', 'gradeLevel', 'academicTerm.academicYear', 'preparedBy']);
        $entry->load(['strand', 'subStrand']);

        $school = $scheme->school;
        $themeColor = $school?->getSetting('pdf_theme_color', '#1e40af') ?? '#1e40af';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.scheme_entry', compact('scheme', 'entry', 'school', 'themeColor'))
            ->setPaper('a4', 'portrait');
            
        return $pdf->download("Lesson_Instruction_{$entry->week_number}_{$entry->lesson_number}.pdf");
    }

    public function destroySchemeEntry(SchemeOfWork $scheme, SchemeEntry $entry): RedirectResponse
    {
        $entry->delete();
        return back()->with('success', 'Entry removed.');
    }

    public function bulkDeleteSchemeEntries(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $request->validate([
            'entry_ids' => 'required|array|min:1',
            'entry_ids.*' => 'integer|exists:scheme_entries,id',
        ]);

        SchemeEntry::where('scheme_id', $scheme->id)
            ->whereIn('id', $request->input('entry_ids'))
            ->delete();

        $count = count($request->input('entry_ids'));
        return back()->with('success', "{$count} entries removed successfully.");
    }

    public function showSchemeEntry(SchemeOfWork $scheme, SchemeEntry $entry): Response
    {
        $entry->load(['strand', 'subStrand', 'scheme.subject', 'scheme.gradeLevel']);
        
        return Inertia::render('curriculum/planner/SchemeEntryDetails', [
            'scheme' => $scheme->load(['subject', 'gradeLevel']),
            'entry' => $entry,
            'strands' => Strand::where('subject_id', $scheme->subject_id)
                ->where('grade_level_id', $scheme->grade_level_id)
                ->get(['id', 'name']),
        ]);
    }
    
    public function downloadSchemeTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="scheme_of_work_template.csv"',
        ];

        $columns = [
            'week_number',
            'lesson_number',
            'duration_minutes',
            'lesson_date',
            'strand_name',
            'sub_strand_name',
            'topic',
            'key_vocabulary',
            'learning_outcomes',
            'learning_activities',
            'teacher_activities',
            'introduction',
            'lesson_development',
            'conclusion',
            'resources',
            'references',
            'assessment',
            'remarks',
            'reflection',
            'homework',
            'core_competencies',
            'pci',
            'inquiry_questions',
        ];

        $sample = [
            '1', '1', '35', date('Y-m-d'), 'Numbers', 'Addition', 'Intro to 3-digit addition',
            'Sum, Regroup, Digit', 'Add 3-digit numbers', 'Learners to add 3-digit numbers using placeholders', 'Demonstrate addition on the abacus',
            'Start with a 2-digit review', 'Move from physical to abstract representation', 'Oral quiz on sum values', 'Abacus, Base-ten blocks',
            'Oxford Primary Math Pg 12', 'Group work on addition', 'Excellent grasp by most learners', 'Good lesson engagement',
            'Exercise 1.4 for practice', 'Communication, Self-efficacy', 'Financial Literacy', 'How do we regroup when sum exceeds 9?'
        ];

        $callback = function () use ($columns, $sample) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, $columns);
            fputcsv($handle, $sample);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importSchemeEntries(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        try {
            $handle = fopen($request->file('file')->getRealPath(), 'r');
            $header = fgetcsv($handle);
            
            if (!$header) {
                fclose($handle);
                return back()->with('error', 'Empty CSV file.');
            }

            // Normalize header
            $header = array_map(fn($v) => trim(strtolower(preg_replace('/^\xEF\xBB\xBF/', '', $v))), $header);
            
            $required = ['week_number', 'lesson_number', 'topic'];
            foreach ($required as $col) {
                if (!in_array($col, $header)) {
                    fclose($handle);
                    return back()->with('error', "Missing required column: {$col}");
                }
            }

            $count = 0;
            DB::transaction(function () use ($handle, $header, $scheme, &$count) {
                while (($data = fgetcsv($handle)) !== false) {
                    $row = array_combine($header, array_pad($data, count($header), ''));
                    
                    // Basic row skip if empty
                    if (empty(array_filter($row))) continue;

                    // Find strand if name provided — auto-create if not found
                    $strandId = null;
                    if (!empty($row['strand_name'])) {
                        $strand = Strand::where('subject_id', $scheme->subject_id)
                            ->where('grade_level_id', $scheme->grade_level_id)
                            ->where('name', 'like', '%' . trim($row['strand_name']) . '%')
                            ->first();

                        if (!$strand) {
                            $strand = Strand::create([
                                'subject_id' => $scheme->subject_id,
                                'grade_level_id' => $scheme->grade_level_id,
                                'school_id' => $scheme->school_id,
                                'name' => trim($row['strand_name']),
                                'code' => \Illuminate\Support\Str::slug(substr(trim($row['strand_name']), 0, 10)) . '-' . rand(100, 999),
                            ]);
                        }
                        $strandId = $strand->id;
                    }

                    $subStrandId = null;
                    if ($strandId && !empty($row['sub_strand_name'])) {
                        $subStrand = SubStrand::where('strand_id', $strandId)
                            ->where('name', 'like', '%' . trim($row['sub_strand_name']) . '%')
                            ->first();

                        if (!$subStrand) {
                            $subStrand = SubStrand::create([
                                'strand_id' => $strandId,
                                'school_id' => $scheme->school_id,
                                'name' => trim($row['sub_strand_name']),
                                'code' => \Illuminate\Support\Str::slug(substr(trim($row['sub_strand_name']), 0, 10)) . '-' . rand(100, 999),
                            ]);
                        }
                        $subStrandId = $subStrand->id;
                    }

                    $scheme->entries()->create([
                        'school_id' => $scheme->school_id,
                        'week_number' => (int)($row['week_number'] ?? 0),
                        'lesson_number' => (int)($row['lesson_number'] ?? 0),
                        'duration_minutes' => (int)($row['duration_minutes'] ?? 35),
                        'lesson_date' => !empty($row['lesson_date']) ? $row['lesson_date'] : null,
                        'strand_id' => $strandId,
                        'sub_strand_id' => $subStrandId,
                        'topic' => $row['topic'] ?? 'Untitled Lesson',
                        'key_vocabulary' => $row['key_vocabulary'] ?? '',
                        'learning_outcomes' => $row['learning_outcomes'] ?? '',
                        'learning_activities' => $row['learning_activities'] ?? '',
                        'teacher_activities' => $row['teacher_activities'] ?? '',
                        'introduction' => $row['introduction'] ?? '',
                        'lesson_development' => $row['lesson_development'] ?? '',
                        'conclusion' => $row['conclusion'] ?? '',
                        'resources' => $row['resources'] ?? '',
                        'references' => $row['references'] ?? '',
                        'assessment' => $row['assessment'] ?? '',
                        'remarks' => $row['remarks'] ?? '',
                        'reflection' => $row['reflection'] ?? '',
                        'homework' => $row['homework'] ?? '',
                        'core_competencies' => !empty($row['core_competencies']) ? explode(',', $row['core_competencies']) : null,
                        'pci' => !empty($row['pci']) ? explode(',', $row['pci']) : null,
                        'inquiry_questions' => $row['inquiry_questions'] ?? '',
                    ]);
                    $count++;
                }
            });

            fclose($handle);
            return back()->with('success', "Imported {$count} entries successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing CSV: ' . $e->getMessage());
        }
    }

    public function bulkGenerateLessons(Request $request, SchemeOfWork $scheme): RedirectResponse
    {
        $validated = $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:scheme_entries,id',
            'start_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'lessons_per_week' => 'required|integer|min:1|max:10',
        ]);

        $entryIds = $validated['entry_ids'];
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $lessonsPerWeek = $validated['lessons_per_week'];
        $duration = $validated['duration_minutes'];

        $entries = SchemeEntry::whereIn('id', $entryIds)
            ->orderBy('week_number')
            ->orderBy('lesson_number')
            ->get();

        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'You must have a teacher profile to generate lesson plans.');
        }

        DB::transaction(function () use ($scheme, $entries, $startDate, $lessonsPerWeek, $duration, $teacher) {
            $currentDate = $startDate->copy();
            $lessonCountInWeek = 0;

            foreach ($entries as $index => $entry) {
                // Simple logic: 1 lesson per day, skip weekends
                while ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                }

                LessonPlan::create([
                    'school_id' => $scheme->school_id,
                    'class_id' => $scheme->grade_level_id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $scheme->subject_id,
                    'academic_term_id' => $scheme->academic_term_id,
                    'strand_id' => $entry->strand_id,
                    'sub_strand_id' => $entry->sub_strand_id,
                    'scheme_entry_id' => $entry->id,
                    'week_number' => $entry->week_number,
                    'lesson_date' => $currentDate->toDateString(),
                    'duration_minutes' => $duration,
                    'title' => $entry->topic,
                    'learning_outcomes' => $entry->learning_outcomes,
                    'key_vocabulary' => $entry->key_vocabulary,
                    'core_competencies' => $entry->core_competencies,
                    'pci' => $entry->pci,
                    'inquiry_questions' => $entry->inquiry_questions,
                    'teaching_aids' => $entry->resources,
                    'references' => $entry->references,
                    'introduction' => $entry->introduction,
                    'lesson_development' => $entry->lesson_development,
                    'teacher_activities' => $entry->teacher_activities,
                    'learner_activities' => $entry->learning_activities,
                    'conclusion' => $entry->conclusion,
                    'status' => 'draft',
                ]);

                $lessonCountInWeek++;
                if ($lessonCountInWeek >= $lessonsPerWeek) {
                    $lessonCountInWeek = 0;
                    // Move to next week (assuming 7 days gap from start of this week's lessons)
                    // But simpler is just to keep adding days.
                    $currentDate->addDay();
                } else {
                    $currentDate->addDay();
                }
            }
        });

        return back()->with('success', "Generated " . count($entryIds) . " lesson plans.");
    }
}
