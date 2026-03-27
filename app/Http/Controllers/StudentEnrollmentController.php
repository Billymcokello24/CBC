<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\Stream;
use App\Models\Academic\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $enrollmentType = (string) $request->string('enrollment_type');
        $classId = $request->integer('class_id');
        $academicYearId = $request->integer('academic_year_id');
        $view = (string) $request->string('view', 'grid');

        $query = StudentEnrollment::query()
            ->with([
                'class:id,name,code,grade_level_id,stream_id,academic_year_id',
                'class.gradeLevel:id,name,level_order',
                'class.stream:id,name,code',
                'academicYear:id,name',
            ])
            ->whereHas('student')
            ->whereHas('class')
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhereRaw("concat(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                })->orWhereHas('class', function ($classQuery) use ($search) {
                    $classQuery->where('name', 'like', "%{$search}%");
                });
            })
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($enrollmentType !== '' && $enrollmentType !== 'all', fn ($q) => $q->where('enrollment_type', $enrollmentType))
            ->when($classId > 0, fn ($q) => $q->where('class_id', $classId))
            ->when($academicYearId > 0, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->get();

        $groups = $query
            ->groupBy(fn (StudentEnrollment $enrollment) => (string) $enrollment->class_id)
            ->map(function ($items) {
                $first = $items->first();
                $class = $first?->class;

                return [
                    'class_id' => $class?->id,
                    'class_name' => $class?->name,
                    'grade_name' => $class?->gradeLevel?->name,
                    'stream_name' => $class?->stream?->name,
                    'academic_year' => $first?->academicYear?->name,
                    'total_learners' => $items->count(),
                    'active_learners' => $items->where('status', 'active')->count(),
                    'new_enrollments' => $items->whereIn('enrollment_type', ['new', 'transfer'])->count(),
                    'promoted_learners' => $items->where('enrollment_type', 'continuing')->count(),
                ];
            })
            ->filter(fn ($group) => $group['class_id'] !== null)
            ->sortBy('class_name')
            ->values();

        $statsBase = StudentEnrollment::query()->whereHas('student');

        return Inertia::render('students/Enrollments', [
            'groups' => $groups,
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'active' => (clone $statsBase)->where('status', 'active')->count(),
                'new' => (clone $statsBase)->whereIn('enrollment_type', ['new', 'transfer'])->count(),
                'promoted' => (clone $statsBase)->where('enrollment_type', 'continuing')->count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'enrollment_type' => $enrollmentType === '' ? 'all' : $enrollmentType,
                'class_id' => $classId ?: null,
                'academic_year_id' => $academicYearId ?: null,
                'view' => in_array($view, ['grid', 'list'], true) ? $view : 'grid',
            ],
            'classes' => DB::table('classes')->select('id', 'name')->orderBy('name')->get(),
            'academicYears' => DB::table('academic_years')->select('id', 'name')->orderByDesc('start_date')->get(),
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'transferred', 'label' => 'Transferred'],
                ['value' => 'withdrawn', 'label' => 'Withdrawn'],
                ['value' => 'promoted', 'label' => 'Promoted'],
                ['value' => 'repeated', 'label' => 'Repeated'],
            ],
            'enrollmentTypeOptions' => [
                ['value' => 'all', 'label' => 'All Types'],
                ['value' => 'new', 'label' => 'New'],
                ['value' => 'promoted', 'label' => 'Promoted'],
                ['value' => 'repeated', 'label' => 'Repeated'],
                ['value' => 'transferred_in', 'label' => 'Transferred In'],
                ['value' => 'rejoined', 'label' => 'Rejoined'],
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('students/enrollments/Create', [
            'academicYears' => AcademicYear::active()->orderByDesc('start_date')->get(['id', 'name']),
            'academicTerms' => AcademicTerm::active()->orderBy('name')->get(['id', 'name']),
            'classes' => \App\Models\Academic\SchoolClass::active()->with(['gradeLevel', 'stream'])->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_id' => $c->grade_level_id,
                'stream_id' => $c->stream_id,
            ]),
            'streams' => Stream::active()->orderBy('name')->get(['id', 'name']),
            'grades' => GradeLevel::active()->orderBy('level_order')->get(['id', 'name']),
            'studentId' => $request->query('student_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'admission_number' => 'required|string',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,completed,transferred,withdrawn,promoted,repeated',
            'entry_type' => 'required|in:new,transfer,continuing',
            'boarding_status' => 'required|in:day,boarding',
            'sponsor_type' => 'nullable|string',
            'previous_school' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Unique check for student + academic year
        $exists = StudentEnrollment::where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['academic_year_id' => 'This student is already enrolled for the selected academic year.']);
        }

        DB::transaction(function () use ($validated) {
            $enrollment = StudentEnrollment::create(array_merge($validated, [
                'school_id' => auth()->user()->school_id,
                'enrolled_by' => auth()->id(),
                'enrollment_type' => $validated['entry_type'],
            ]));

            // Update student bio record
            $student = Student::find($validated['student_id']);
            $student->update([
                'current_class_id' => $validated['class_id'],
                'status' => $validated['status'] === 'active' ? 'active' : $student->status,
                'admission_number' => $validated['admission_number'],
                'boarding_status' => $validated['boarding_status'],
            ]);
        });

        return redirect()->route('students.enrollments')
            ->with('success', "{$student->full_name} enrolled successfully in {$enrollment->class->name}.");
    }

    public function show(Request $request, SchoolClass $class): Response
    {
        $search = trim((string) $request->string('search'));
        $studentStatus = (string) $request->string('student_status');
        $enrollmentStatus = (string) $request->string('enrollment_status');

        $query = StudentEnrollment::query()
            ->whereHas('student')
            ->with([
                'student:id,first_name,middle_name,last_name,admission_number,status',
                'academicYear:id,name',
                'academicTerm:id,name',
                'enrolledBy:id,name',
            ])
            ->where('class_id', $class->id)
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhereRaw("concat(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                });
            })
            ->when($studentStatus !== '' && $studentStatus !== 'all', fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('status', $studentStatus)))
            ->when($enrollmentStatus !== '' && $enrollmentStatus !== 'all', fn ($q) => $q->where('status', $enrollmentStatus))
            ->orderByDesc('enrollment_date')
            ->get();

        $students = $query->map(function (StudentEnrollment $enrollment) {
            $student = $enrollment->student;

            return [
                'enrollment_id' => $enrollment->id,
                'learner_id' => $enrollment->student_id,
                'learner_name' => $student ? trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name) : 'Unknown Learner',
                'admission_number' => $student?->admission_number,
                'learner_status' => $student?->status,
                'enrollment_status' => $enrollment->status,
                'enrollment_type' => $enrollment->enrollment_type,
                'enrollment_date' => optional($enrollment->enrollment_date)?->format('Y-m-d'),
                'end_date' => optional($enrollment->end_date)?->format('Y-m-d'),
                'roll_number' => $enrollment->roll_number,
                'notes' => $enrollment->notes,
                'enrolled_by' => $enrollment->enrolledBy?->name,
                'academic_year' => $enrollment->academicYear?->name,
                'academic_term' => $enrollment->academicTerm?->name,
                'can_demote' => $student?->status === 'active',
            ];
        })->values();

        $class->load(['gradeLevel:id,name,level_order', 'stream:id,name,code', 'academicYear:id,name']);

        return Inertia::render('students/EnrollmentGroup', [
            'group' => [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'grade_name' => $class->gradeLevel?->name,
                'stream_name' => $class->stream?->name,
                'academic_year' => $class->academicYear?->name,
                'total_learners' => $students->count(),
                'active_learners' => $students->where('enrollment_status', 'active')->count(),
            ],
            'learners' => $students,
            'filters' => [
                'search' => $search,
                'learner_status' => $studentStatus === '' ? 'all' : $studentStatus,
                'enrollment_status' => $enrollmentStatus === '' ? 'all' : $enrollmentStatus,
            ],
            'learnerStatusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'suspended', 'label' => 'Suspended'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
            'enrollmentStatusOptions' => [
                ['value' => 'all', 'label' => 'All Enrollment Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'promoted', 'label' => 'Promoted'],
                ['value' => 'repeated', 'label' => 'Repeated'],
            ],
        ]);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => ['integer', 'exists:student_enrollments,id'],
        ]);

        StudentEnrollment::whereIn('id', $validated['enrollment_ids'])->delete();

        return back()->with('success', count($validated['enrollment_ids']) . ' enrollment records removed.');
    }
}
