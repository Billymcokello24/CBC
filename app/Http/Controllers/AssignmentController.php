<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\Assignment;
use App\Models\Curriculum\AssignmentAttachment;
use App\Models\Curriculum\AssignmentSubmission;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\Subject;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicTerm;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Assignment::with(['subject', 'classroom', 'academicTerm', 'teacher', 'strand', 'subStrand'])
            ->withCount('submissions');

        if (!$user->hasAnyRole(['admin', 'principal', 'school_admin', 'super_admin', 'deputy_principal', 'hod'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            } else {
                $query->whereRaw('1 = 0'); // No teacher record, show nothing
            }
        }

        return Inertia::render('curriculum/assignments/Index', [
            'assignments' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'classes' => SchoolClass::active()->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        $subjects = Subject::active()->get(['id', 'name']);
        $classes = SchoolClass::active()->get(['id', 'name']);
        $strands = Strand::with('subStrands:id,strand_id,name,code')->get(['id', 'subject_id', 'grade_level_id', 'name', 'code']);

        return Inertia::render('curriculum/assignments/Create', [
            'subjects' => $subjects,
            'classes' => $classes,
            'strands' => $strands,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'academic_term_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignment_type' => 'required',
            'assigned_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:assigned_date',
            'total_marks' => 'nullable|numeric|min:0',
            'strand_id' => 'nullable|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->first();

        $assignment = Assignment::create(array_merge($validated, [
            'school_id' => Auth::user()->school_id,
            'teacher_id' => $teacher?->id,
            'created_by' => Auth::id(),
            'status' => 'published',
        ]));

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('assignments/attachments', 'public');
                AssignmentAttachment::create([
                    'assignment_id' => $assignment->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('curriculum.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function show(Assignment $assignment): Response
    {
        return Inertia::render('curriculum/assignments/Show', [
            'assignment' => $assignment->load(['classroom', 'subject', 'teacher', 'submissions.student', 'strand', 'subStrand', 'attachments']),
        ]);
    }

    public function edit(Assignment $assignment): Response
    {
        return Inertia::render('curriculum/assignments/Edit', [
            'assignment' => $assignment->load(['attachments']),
            'subjects' => Subject::active()->get(['id', 'name']),
            'classes' => SchoolClass::active()->get(['id', 'name']),
            'strands' => Strand::with('subStrands')->get(),
        ]);
    }

    public function download(AssignmentAttachment $attachment)
    {
        // Ensure the file exists
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroyAttachment(AssignmentAttachment $attachment): RedirectResponse
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    public function submissions(Assignment $assignment): Response
    {
        $assignment->load(['submissions.student', 'classroom', 'subject']);

        return Inertia::render('curriculum/assignments/Submissions', [
            'assignment' => $assignment,
        ]);
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => Auth::id()],
            [
                'school_id' => Auth::user()->school_id,
                'content' => $request->content,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        return back()->with('success', 'Assignment submitted successfully.');
    }

    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $request->validate([
            'marks_obtained' => 'required|integer|min:0|max:'.$submission->assignment->total_marks,
            'feedback' => 'nullable|string',
        ]);

        $submission->update(array_merge($request->only('marks_obtained', 'feedback'), [
            'graded_by' => Auth::id(),
            'graded_at' => now(),
            'final_marks' => $request->marks_obtained,
        ]));

        return back()->with('success', 'Submission graded successfully.');
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'academic_term_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignment_type' => 'required',
            'assigned_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:assigned_date',
            'total_marks' => 'nullable|numeric|min:0',
            'strand_id' => 'nullable|exists:strands,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $assignment->update($validated);

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('assignments/attachments', 'public');
                AssignmentAttachment::create([
                    'assignment_id' => $assignment->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('curriculum.assignments.show', $assignment->id)->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        // Delete associated attachment files
        foreach ($assignment->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Force delete to physically remove from DB and trigger cascade
        $assignment->forceDelete();

        return redirect()->route('curriculum.assignments.index')->with('success', 'Assignment and all associated files removed permanently.');
    }
}
