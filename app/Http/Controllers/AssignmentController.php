<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\Assignment;
use App\Models\Curriculum\AssignmentAttachment;
use App\Models\Curriculum\AssignmentSubmission;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\Subject;
use App\Models\Curriculum\SubmissionAttachment;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicTerm;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Services\Curriculum\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Assignment::with(['subject', 'classroom', 'academicTerm', 'teacher', 'strand', 'subStrand'])
            ->withCount('submissions');

        // Role-based filtering
        if ($user->hasRole('parent')) {
            $guardian = Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->students()->pluck('students.id');
                $classIds = $guardian->students()->pluck('current_class_id');
                $query->whereIn('class_id', $classIds);
                
                // For guardians, we also want to know if their specific children have submitted
                $query->with(['submissions' => function($q) use ($studentIds) {
                    $q->whereIn('student_id', $studentIds);
                }]);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (!$user->hasAnyRole(['admin', 'principal', 'school_admin', 'super_admin', 'deputy_principal', 'hod'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return Inertia::render('curriculum/assignments/Index', [
            'assignments' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'classes' => SchoolClass::active()->get(['id', 'name']),
            'children' => $user->hasRole('guardian') ? Guardian::where('user_id', $user->id)->first()?->students()->get(['students.id', 'first_name', 'last_name', 'current_class_id']) : [],
            'userRole' => $user->roles->first()?->name ?? 'user',
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
        $user = Auth::user();
        $assignment->load(['classroom', 'subject', 'teacher', 'strand', 'subStrand', 'attachments']);
        
        $children = [];
        $submissions = [];

        if ($user->hasRole('parent')) {
            $guardian = Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                // Get children of this guardian who are in the assignment's class
                $children = $guardian->students()
                    ->where('current_class_id', $assignment->class_id)
                    ->get(['students.id', 'first_name', 'last_name']);
                
                $studentIds = $children->pluck('id');
                $submissions = AssignmentSubmission::with('attachments')
                    ->where('assignment_id', $assignment->id)
                    ->whereIn('student_id', $studentIds)
                    ->get();
            }
        } else {
            $assignment->load('submissions.student');
        }

        return Inertia::render('curriculum/assignments/Show', [
            'assignment' => $assignment,
            'submissions' => $submissions,
            'children' => $children,
            'userRole' => $user->roles->first()?->name ?? 'user',
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
        $assignment->load([
            'submissions.student', 
            'submissions.attachments', 
            'classroom', 
            'subject'
        ]);

        return Inertia::render('curriculum/assignments/Submissions', [
            'assignment' => $assignment,
        ]);
    }

    public function vault(Request $request): Response
    {
        $user = Auth::user();
        
        // Get all graded submissions grouped by Subject and Class
        $query = AssignmentSubmission::with(['assignment.subject', 'assignment.classroom', 'student', 'attachments'])
            ->whereNotNull('graded_at')
            ->where('status', 'graded');

        // Teacher's view: only their assignments
        if (!$user->hasAnyRole(['admin', 'principal', 'school_admin', 'super_admin'])) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->whereHas('assignment', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                });
            }
        }

        $submissions = $query->latest('graded_at')->get();

        // Structure into virtual folders: Subject -> Class -> Submissions
        $vault = $submissions->groupBy(function($s) {
            return $s->assignment->subject->name ?? 'Uncategorized';
        })->map(function($subjectGroup) {
            return $subjectGroup->groupBy(function($s) {
                return $s->assignment->classroom->name ?? 'No Class';
            });
        });

        return Inertia::render('curriculum/assignments/Vault', [
            'vault' => $vault,
        ]);
    }

    public function reviewSubmission(Assignment $assignment, AssignmentSubmission $submission): Response
    {
        $submission->load(['student', 'attachments']);
        $assignment->load(['subject', 'classroom']);

        // Find next and previous submissions in the same assignment
        $allSubmissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($submission->id, $allSubmissions);
        $prevId = $currentIndex > 0 ? $allSubmissions[$currentIndex - 1] : null;
        $nextId = $currentIndex < count($allSubmissions) - 1 ? $allSubmissions[$currentIndex + 1] : null;

        return Inertia::render('curriculum/assignments/Review', [
            'assignment' => $assignment,
            'submission' => $submission,
            'prevId' => $prevId,
            'nextId' => $nextId,
        ]);
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'content' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $request->student_id],
            [
                'school_id' => Auth::user()->school_id,
                'content' => $request->content,
                'submitted_at' => now(),
                'status' => 'submitted',
                'attempt_number' => \DB::raw('attempt_number + 1'),
            ]
        );

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('assignments/submissions', 'public');
                SubmissionAttachment::create([
                    'submission_id' => $submission->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return back()->with('success', 'Assignment submitted successfully.');
    }


    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $request->validate([
            'marks_obtained' => 'required|numeric|min:0|max:'.$submission->assignment->total_marks,
            'feedback' => 'nullable|string',
            'annotations' => 'nullable|array',
            'marked_images' => 'nullable|array',
        ]);

        $submission->update([
            'marks_obtained' => $request->marks_obtained,
            'feedback' => $request->feedback,
            'private_notes' => $request->annotations ? json_encode($request->annotations) : $submission->private_notes,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
            'final_marks' => $request->marks_obtained,
            'status' => 'graded',
        ]);

        // Automatically generate and store the marked file in the vault
        // Use client-side snapshots if provided for 100% visual fidelity
        $markedPath = $this->assignmentService->generateAndStoreMarkedPdf(
            $submission, 
            $request->marked_images ?? []
        );
        $submission->update(['marked_file_path' => $markedPath]);

        return back()->with('success', 'Submission graded and marked material saved to vault.');
    }

    public function downloadCompiledPdf(AssignmentSubmission $submission)
    {
        // Prioritize the pre-generated marked copy if it exists
        $path = $submission->marked_file_path;
        
        if (!$path || !Storage::disk('public')->exists($path)) {
            $path = $this->assignmentService->generateAndStoreMarkedPdf($submission);
            $submission->update(['marked_file_path' => $path]);
        }

        return Storage::disk('public')->download($path, 'marked_assignment_'.$submission->id.'.pdf');
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
