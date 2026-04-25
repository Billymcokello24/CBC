<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\CurriculumResource;
use App\Models\Curriculum\Assignment;
use App\Models\Curriculum\AssignmentSubmission;
use App\Models\Curriculum\SubmissionAttachment;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\TeacherSubject;
use App\Services\Curriculum\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParentPortalController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Get the authenticated parent's guardian model and verify ownership of a student.
     */
    private function guardian()
    {
        $guardian = auth()->user()?->guardian;
        abort_unless($guardian, 403, 'No guardian profile linked to this account.');
        return $guardian;
    }

    private function authorizeChild(Guardian $guardian, Student $student): void
    {
        abort_unless(
            $guardian->students()->whereKey($student->id)->exists(),
            403,
            'You do not have access to this student.'
        );
    }

    /**
     * Parent Dashboard — overview stats for all children.
     */
    public function dashboard(): Response
    {
        $guardian = $this->guardian();
        $guardian->load(['students.currentClass:id,name,code']);

        $studentIds = $guardian->students->pluck('id');
        $classIds = $guardian->students->pluck('current_class_id')->filter()->unique();

        // Aggregate stats
        $totalAssignments = DB::table('assignments')
            ->whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->count();

        $pendingSubmissions = DB::table('assignments')
            ->whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->where('due_date', '>=', now())
            ->whereNotExists(function ($q) use ($studentIds) {
                $q->select(DB::raw(1))
                    ->from('assignment_submissions')
                    ->whereColumn('assignment_submissions.assignment_id', 'assignments.id')
                    ->whereIn('assignment_submissions.student_id', $studentIds);
            })
            ->count();

        $submittedCount = DB::table('assignment_submissions')
            ->whereIn('student_id', $studentIds)
            ->count();

        $gradedCount = DB::table('assignment_submissions')
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('graded_at')
            ->count();

        return Inertia::render('guardians/Dashboard', [
            'guardian' => [
                'id' => $guardian->id,
                'name' => $guardian->full_name,
                'email' => $guardian->email,
                'phone' => $guardian->phone,
                'relationship_type' => $guardian->relationship_type,
            ],
            'children' => $guardian->students->map(fn(Student $s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'admission_number' => $s->admission_number,
                'class' => $s->currentClass?->name,
                'status' => $s->status,
                'photo_url' => $s->photo_url,
                'gender' => $s->gender,
            ])->values(),
            'stats' => [
                'total_children' => $guardian->students->count(),
                'total_assignments' => $totalAssignments,
                'pending_submissions' => $pendingSubmissions,
                'submitted' => $submittedCount,
                'graded' => $gradedCount,
            ],
        ]);
    }

    /**
     * My Children — list all linked children.
     */
    public function children(): Response
    {
        $guardian = $this->guardian();
        $guardian->load([
            'students.currentClass.gradeLevel',
            'students.currentClass.stream',
            'students.currentClass.classTeacher',
        ]);

        return Inertia::render('guardians/Children/Index', [
            'children' => $guardian->students->map(fn(Student $s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'admission_number' => $s->admission_number,
                'gender' => $s->gender,
                'date_of_birth' => $s->date_of_birth?->format('Y-m-d'),
                'age' => $s->age,
                'status' => $s->status,
                'photo_url' => $s->photo_url,
                'class_name' => $s->currentClass?->name,
                'grade_name' => $s->currentClass?->gradeLevel?->name,
                'stream_name' => $s->currentClass?->stream?->name,
                'class_teacher' => $s->currentClass?->classTeacher ? [
                    'name' => $s->currentClass->classTeacher->name,
                ] : null,
            ])->values(),
        ]);
    }

    /**
     * Child Profile — the main workspace with all sections.
     */
    public function childProfile(Student $student): Response
    {
        $guardian = $this->guardian();
        $this->authorizeChild($guardian, $student);

        $student->load([
            'currentClass.gradeLevel',
            'currentClass.stream',
            'currentClass.classTeacher',
            'currentClass.assistantTeacher',
        ]);

        // Subjects & Teachers for the child's class
        $subjectsWithTeachers = [];
        if ($student->current_class_id) {
            $subjectsWithTeachers = DB::table('teacher_subjects')
                ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
                ->join('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
                ->where('teacher_subjects.class_id', $student->current_class_id)
                ->where('teacher_subjects.is_active', true)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                    'teachers.id as teacher_id',
                    'teachers.first_name as teacher_first_name',
                    'teachers.last_name as teacher_last_name',
                    'teachers.photo as teacher_photo',
                    'teachers.phone as teacher_phone',
                    'teachers.email as teacher_email',
                    'teacher_subjects.is_primary_teacher'
                )
                ->get()
                ->groupBy('subject_id')
                ->map(function ($teachers, $subjectId) {
                    $first = $teachers->first();
                    return [
                        'id' => $first->subject_id,
                        'name' => $first->subject_name,
                        'code' => $first->subject_code,
                        'teachers' => $teachers->map(fn($t) => [
                            'id' => $t->teacher_id,
                            'name' => trim($t->teacher_first_name . ' ' . $t->teacher_last_name),
                            'photo_url' => $t->teacher_photo ? asset('storage/' . $t->teacher_photo) : null,
                            'phone' => $t->teacher_phone,
                            'email' => $t->teacher_email,
                            'is_primary' => (bool)$t->is_primary_teacher,
                        ])->values(),
                    ];
                })->values();
        }

        // Assignments for the child's class
        $assignments = Assignment::with(['subject:id,name', 'teacher:id,first_name,last_name', 'attachments'])
            ->where('class_id', $student->current_class_id)
            ->where('status', 'published')
            ->latest('due_date')
            ->limit(20)
            ->get()
            ->map(function (Assignment $a) use ($student) {
                $submission = AssignmentSubmission::where('assignment_id', $a->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'description' => $a->description,
                    'assignment_type' => $a->assignment_type,
                    'subject' => $a->subject?->name,
                    'teacher' => $a->teacher ? trim($a->teacher->first_name . ' ' . $a->teacher->last_name) : null,
                    'assigned_date' => $a->assigned_date?->format('Y-m-d'),
                    'due_date' => $a->due_date?->format('Y-m-d H:i'),
                    'total_marks' => $a->total_marks,
                    'attachments_count' => $a->attachments->count(),
                    'is_overdue' => $a->due_date && $a->due_date->isPast(),
                    'submission' => $submission ? [
                        'id' => $submission->id,
                        'status' => $submission->status,
                        'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i'),
                        'marks_obtained' => $submission->marks_obtained,
                        'final_marks' => $submission->final_marks,
                        'feedback' => $submission->feedback,
                        'grade' => $submission->grade,
                    ] : null,
                ];
            });

        // Attendance summary
        $attendanceSummary = DB::table('student_attendance')
            ->where('student_id', $student->id)
            ->selectRaw("
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
            ")
            ->first();

        // Recent attendance
        $recentAttendance = DB::table('student_attendance')
            ->where('student_id', $student->id)
            ->orderByDesc('attendance_date')
            ->limit(30)
            ->get(['attendance_date', 'status', 'notes']);

        // Results / Assessments
        $results = DB::table('student_assessments')
            ->join('assessments', 'assessments.id', '=', 'student_assessments.assessment_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'assessments.subject_id')
            ->leftJoin('assessment_types', 'assessment_types.id', '=', 'assessments.assessment_type_id')
            ->where('student_assessments.student_id', $student->id)
            ->orderByDesc('assessments.assessment_date')
            ->limit(20)
            ->select(
                'student_assessments.*',
                'assessments.title as assessment_title',
                'assessment_types.name as assessment_type',
                'assessments.assessment_date',
                'assessments.total_marks as total_marks',
                'subjects.name as subject_name'
            )
            ->get();

        // Timetable
        $timetable = [];
        if ($student->current_class_id) {
            $timetable = DB::table('timetable_slots')
                ->join('timetables', 'timetables.id', '=', 'timetable_slots.timetable_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'timetable_slots.subject_id')
                ->leftJoin('teachers', 'teachers.id', '=', 'timetable_slots.teacher_id')
                ->where('timetables.class_id', $student->current_class_id)
                ->where('timetables.status', 'active')
                ->select(
                    'timetable_slots.day_of_week',
                    'timetable_slots.start_time',
                    'timetable_slots.end_time',
                    'timetable_slots.activity_type',
                    'subjects.name as subject_name',
                    'teachers.first_name as teacher_first_name',
                    'teachers.last_name as teacher_last_name'
                )
                ->orderBy('timetable_slots.day_of_week')
                ->orderBy('timetable_slots.start_time')
                ->get()
                ->map(fn($s) => [
                    'day' => $s->day_of_week,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'type' => $s->activity_type,
                    'subject' => $s->subject_name,
                    'teacher' => $s->teacher_first_name ? trim($s->teacher_first_name . ' ' . $s->teacher_last_name) : null,
                ]);
        }

        // Learning Resources for the child's grade
        $resourcesBySubject = [];
        if ($student->currentClass?->grade_level_id) {
            $resourcesBySubject = CurriculumResource::with(['subject:id,name,code', 'folder:id,name'])
                ->where('school_id', $student->school_id)
                ->where(function($q) use ($student) {
                    $q->where('grade_level_id', $student->currentClass->grade_level_id)
                      ->orWhereHas('folder', function($fq) use ($student) {
                          $fq->where('grade_level_id', $student->currentClass->grade_level_id);
                      });
                })
                ->get()
                ->groupBy(function ($r) {
                    return $r->subject->name ?? 'General Reference';
                })->map(function ($items, $subjectName) {
                    return [
                        'subject_name' => $subjectName,
                        'resources' => $items->map(fn($r) => [
                            'id' => $r->id,
                            'title' => $r->title,
                            'description' => $r->description,
                            'resource_type' => $r->resource_type,
                            'file_path' => $r->file_path,
                            'url' => $r->url,
                            'folder' => $r->folder?->name,
                            'download_url' => $r->file_path ? asset('storage/' . $r->file_path) : null,
                        ]),
                    ];
                })->values();
        }

        return Inertia::render('guardians/Children/Show', [
            'child' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                'age' => $student->age,
                'status' => $student->status,
                'photo_url' => $student->photo_url,
                'class_name' => $student->currentClass?->name,
                'grade_name' => $student->currentClass?->gradeLevel?->name,
                'stream_name' => $student->currentClass?->stream?->name,
                'class_teacher' => $student->currentClass?->classTeacher ? [
                    'name' => $student->currentClass->classTeacher->name,
                ] : null,
                'assistant_teacher' => $student->currentClass?->assistantTeacher ? [
                    'name' => $student->currentClass->assistantTeacher->name,
                ] : null,
            ],
            'subjects' => $subjectsWithTeachers,
            'assignments' => $assignments,
            'resources' => $resourcesBySubject,
            'attendance_summary' => $attendanceSummary ? [
                'total_days' => (int)$attendanceSummary->total_days,
                'present' => (int)$attendanceSummary->present,
                'absent' => (int)$attendanceSummary->absent,
                'late' => (int)$attendanceSummary->late,
                'percentage' => $attendanceSummary->total_days > 0
                    ? round(($attendanceSummary->present / $attendanceSummary->total_days) * 100, 1)
                    : 0,
            ] : null,
            'recent_attendance' => $recentAttendance,
            'results' => $results,
            'timetable' => $timetable,
        ]);
    }

    /**
     * View a single assignment with submission details.
     */
    public function assignmentShow(Assignment $assignment, Request $request): Response
    {
        $guardian = $this->guardian();
        $studentId = $request->query('student_id');
        $student = Student::findOrFail($studentId);
        $this->authorizeChild($guardian, $student);

        // Verify the assignment belongs to the child's class
        abort_unless($assignment->class_id == $student->current_class_id, 403);

        $assignment->load(['subject:id,name', 'teacher:id,first_name,last_name,phone,email', 'attachments', 'strand:id,name', 'subStrand:id,name']);

        $submission = AssignmentSubmission::with('attachments')
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return Inertia::render('guardians/Assignments/Show', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'assignment_type' => $assignment->assignment_type,
                'subject' => $assignment->subject?->name,
                'teacher' => $assignment->teacher ? [
                    'name' => trim($assignment->teacher->first_name . ' ' . $assignment->teacher->last_name),
                    'phone' => $assignment->teacher->phone,
                    'email' => $assignment->teacher->email,
                ] : null,
                'strand' => $assignment->strand?->name,
                'sub_strand' => $assignment->subStrand?->name,
                'assigned_date' => $assignment->assigned_date?->format('Y-m-d'),
                'due_date' => $assignment->due_date?->format('Y-m-d H:i'),
                'total_marks' => $assignment->total_marks,
                'allow_late_submission' => $assignment->allow_late_submission,
                'max_attempts' => $assignment->max_attempts,
                'is_overdue' => $assignment->due_date && $assignment->due_date->isPast(),
                'attachments' => $assignment->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'file_type' => $a->file_type,
                    'file_size' => $a->file_size,
                    'download_url' => route('curriculum.assignments.attachments.download', $a->id),
                ]),
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
            ],
            'submission' => $submission ? [
                'id' => $submission->id,
                'content' => $submission->content,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i'),
                'marks_obtained' => $submission->marks_obtained,
                'final_marks' => $submission->final_marks,
                'feedback' => $submission->feedback,
                'grade' => $submission->grade,
                'private_notes' => $submission->private_notes ? json_decode($submission->private_notes) : null,
                'attachments' => $submission->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'file_type' => $a->file_type,
                    'file_size' => $a->file_size,
                    'file_path' => $a->file_path,
                ]),
            ] : null,
        ]);
    }

    /**
     * Submit an assignment with optional file uploads.
     */
    public function submitAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $guardian = $this->guardian();
        $studentId = $request->input('student_id');
        $student = Student::findOrFail($studentId);
        $this->authorizeChild($guardian, $student);

        abort_unless($assignment->class_id == $student->current_class_id, 403);

        $request->validate([
            'content' => 'nullable|string',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480', // 20MB per file
        ]);

        // Must have content or files
        if (!$request->filled('content') && !$request->hasFile('files')) {
            return back()->with('error', 'Please provide either text content or upload files.');
        }

        try {
            DB::beginTransaction();

            $submission = AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ],
                [
                    'content' => $request->input('content'),
                    'submitted_at' => now(),
                    'is_late' => $assignment->due_date && $assignment->due_date->isPast(),
                    'status' => 'submitted',
                ]
            );

            // Handle file attachments
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('submissions/' . $submission->id, 'public');
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

            DB::commit();

            return back()->with('success', 'Assignment submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error submitting assignment: ' . $e->getMessage());
        }
    }

    /**
     * All assignments across all children.
     */
    public function assignments(): Response
    {
        $guardian = $this->guardian();
        $guardian->load(['students.currentClass:id,name']);

        $children = $guardian->students->map(function ($student) {
            $assignments = Assignment::with(['subject:id,name', 'classroom:id,name', 'teacher:id,first_name,last_name'])
                ->where('class_id', $student->current_class_id)
                ->where('status', 'published')
                ->latest('due_date')
                ->get()
                ->map(function (Assignment $a) use ($student) {
                    $submission = AssignmentSubmission::where('student_id', $student->id)
                        ->where('assignment_id', $a->id)
                        ->first();

                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'subject' => $a->subject?->name,
                        'class' => $a->classroom?->name,
                        'teacher' => $a->teacher ? trim($a->teacher->first_name . ' ' . $a->teacher->last_name) : 'TBD',
                        'assignment_type' => $a->assignment_type,
                        'due_date' => $a->due_date?->format('Y-m-d H:i'),
                        'is_overdue' => $a->due_date && $a->due_date->isPast(),
                        'total_marks' => $a->total_marks,
                        'status' => $submission ? $submission->status : 'pending',
                        'submitted' => !!$submission,
                    ];
                });

            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'class' => $student->currentClass?->name,
                'assignments' => $assignments,
            ];
        });

        return Inertia::render('guardians/Assignments/Index', [
            'children' => $children,
            'total_assignments' => $children->sum(fn($c) => count($c['assignments'])),
        ]);
    }

    public function downloadMarkedAssignment(AssignmentSubmission $submission)
    {
        $guardian = $this->guardian();
        $this->authorizeChild($guardian, $submission->student);

        // Prioritize the pre-generated marked copy if it exists
        $path = $submission->marked_file_path;
        
        if (!$path || !Storage::disk('public')->exists($path)) {
            $path = $this->assignmentService->generateAndStoreMarkedPdf($submission);
            $submission->update(['marked_file_path' => $path]);
        }

        return Storage::disk('public')->download($path, 'marked_assignment_'.$submission->id.'.pdf');
    }

    /**
     * Learning resources organized by child and grade.
     */
    public function resources(): Response
    {
        $guardian = $this->guardian();
        $guardian->load(['students.currentClass.gradeLevel']);

        $children = $guardian->students->map(function ($student) {
            $resources = [];
            if ($student->currentClass?->grade_level_id) {
                $resources = CurriculumResource::with(['subject:id,name', 'folder:id,name'])
                    ->where('school_id', $student->school_id)
                    ->where(function($q) use ($student) {
                        $q->where('grade_level_id', $student->currentClass->grade_level_id)
                          ->orWhereHas('folder', function($fq) use ($student) {
                              $fq->where('grade_level_id', $student->currentClass->grade_level_id);
                          });
                    })
                    ->latest()
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'subject' => $r->subject?->name ?? 'General',
                        'folder' => $r->folder?->name,
                        'resource_type' => $r->resource_type,
                        'file_path' => $r->file_path,
                        'url' => $r->url,
                        'download_url' => $r->file_path ? asset('storage/' . $r->file_path) : null,
                    ]);
            }

            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'class' => $student->currentClass?->name,
                'resources' => $resources,
            ];
        });

        return Inertia::render('guardians/Resources/Index', [
            'children' => $children,
            'total_resources' => $children->sum(fn($c) => count($c['resources'])),
        ]);
    }
}
