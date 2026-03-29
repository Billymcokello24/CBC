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
        $scheme->load(['subject', 'gradeLevel', 'academicTerm', 'preparedBy', 'entries.strand', 'entries.subStrand']);

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
            'strands' => Strand::all(['id', 'name', 'subject_id', 'grade_level_id']),
            'sub_strands' => SubStrand::all(['id', 'name', 'strand_id']),
        ]);
    }

    public function storeLessonPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'strand_id' => 'nullable|exists:curriculum_strands,id',
            'sub_strand_id' => 'nullable|exists:curriculum_sub_strands,id',
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
        $scheme->update(['status' => 'pending']);
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
            'strand_id' => 'nullable|exists:curriculum_strands,id',
            'sub_strand_id' => 'nullable|exists:curriculum_sub_strands,id',
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
            'strand_id' => 'required|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'topic' => 'required|string|max:255',
            'learning_outcomes' => 'nullable|string',
            'learning_activities' => 'nullable|string',
            'resources' => 'nullable|string',
            'assessment' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $scheme->entries()->create(array_merge($validated, [
            'school_id' => $scheme->school_id,
        ]));

        return back()->with('success', 'Entry added to scheme.');
    }

    public function destroySchemeEntry(SchemeOfWork $scheme, SchemeEntry $entry): RedirectResponse
    {
        $entry->delete();
        return back()->with('success', 'Entry removed.');
    }
}
