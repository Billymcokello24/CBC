<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\SchemeOfWork;
use App\Models\Curriculum\SchemeEntry;
use App\Models\Curriculum\LessonPlan;
use App\Models\Curriculum\Subject;
use App\Models\Academic\GradeLevel;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
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
            $query->where('teacher_id', $user->id);
        }

        return Inertia::render('curriculum/planner/LessonPlans', [
            'plans' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
        ]);
    }

    public function storeLessonPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'academic_term_id' => 'required',
            'title' => 'required|string|max:255',
            'lesson_date' => 'required|date',
            'week_number' => 'nullable|string',
            'strand_id' => 'nullable',
            'sub_strand_id' => 'nullable',
            'specific_objectives' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
        ]);

        LessonPlan::create(array_merge($validated, [
            'school_id' => Auth::user()->school_id,
            'teacher_id' => Auth::id(),
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
            'specific_objectives' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
        ]);

        $plan->update($validated);
        return back()->with('success', 'Lesson plan updated.');
    }

    public function destroyLessonPlan(LessonPlan $plan): RedirectResponse
    {
        $plan->delete();
        return back()->with('success', 'Lesson plan removed.');
    }
}
