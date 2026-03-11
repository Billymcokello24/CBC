<?php

namespace App\Http\Controllers;

use App\Models\Academic\SchoolClass;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'total_students' => $items->count(),
                    'active_students' => $items->where('status', 'active')->count(),
                    'new_enrollments' => $items->where('enrollment_type', 'new')->count(),
                    'promoted_students' => $items->where('enrollment_type', 'promoted')->count(),
                ];
            })
            ->filter(fn ($group) => $group['class_id'] !== null)
            ->sortBy('class_name')
            ->values();

        $statsBase = StudentEnrollment::query();

        return Inertia::render('students/Enrollments', [
            'groups' => $groups,
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'active' => (clone $statsBase)->where('status', 'active')->count(),
                'new' => (clone $statsBase)->where('enrollment_type', 'new')->count(),
                'promoted' => (clone $statsBase)->where('enrollment_type', 'promoted')->count(),
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

    public function show(Request $request, SchoolClass $class): Response
    {
        $search = trim((string) $request->string('search'));
        $studentStatus = (string) $request->string('student_status');
        $enrollmentStatus = (string) $request->string('enrollment_status');

        $query = StudentEnrollment::query()
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
                'student_id' => $enrollment->student_id,
                'student_name' => $student ? trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name) : 'Unknown Student',
                'admission_number' => $student?->admission_number,
                'student_status' => $student?->status,
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
                'total_students' => $students->count(),
                'active_students' => $students->where('enrollment_status', 'active')->count(),
            ],
            'students' => $students,
            'filters' => [
                'search' => $search,
                'student_status' => $studentStatus === '' ? 'all' : $studentStatus,
                'enrollment_status' => $enrollmentStatus === '' ? 'all' : $enrollmentStatus,
            ],
            'studentStatusOptions' => [
                ['value' => 'all', 'label' => 'All Student Statuses'],
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
}
