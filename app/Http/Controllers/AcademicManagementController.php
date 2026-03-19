<?php

namespace App\Http\Controllers;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AcademicManagementController extends Controller
{
    public function allocations(Request $request): Response
    {
        $classes = SchoolClass::query()
            ->with(['gradeLevel:id,name', 'stream:id,name', 'academicYear:id,name'])
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'grade_id' => $class->grade_level_id,
                'grade' => $class->gradeLevel?->name,
                'stream' => $class->stream?->name,
                'academic_year' => $class->academicYear?->name,
            ])
            ->values();

        $subjectAllocations = DB::table('subject_grade_levels')
            ->where('is_active', true)
            ->get(['subject_id', 'grade_level_id'])
            ->groupBy('subject_id')
            ->map(fn ($rows) => collect($rows)->pluck('grade_level_id')->values())
            ->all();

        $subjects = DB::table('subjects')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->orderBy('subjects.name')
            ->select('subjects.id', 'subjects.name', 'subjects.code', 'learning_areas.name as learning_area')
            ->get()
            ->map(function ($subject) use ($subjectAllocations) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'learning_area' => $subject->learning_area,
                    'grade_level_ids' => $subjectAllocations[$subject->id] ?? [],
                ];
            })
            ->values();

        $teachers = Teacher::query()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'staff_number'])
            ->map(fn (Teacher $teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'staff_number' => $teacher->staff_number,
            ])
            ->values();

        $academicYears = DB::table('academic_years')->orderByDesc('start_date')->get(['id', 'name']);

        $assignments = DB::table('teacher_subjects')
            ->join('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->join('classes', 'classes.id', '=', 'teacher_subjects.class_id')
            ->join('academic_years', 'academic_years.id', '=', 'teacher_subjects.academic_year_id')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->orderByDesc('teacher_subjects.id')
            ->select(
                'teacher_subjects.id',
                'teacher_subjects.teacher_id',
                'teacher_subjects.subject_id',
                'teacher_subjects.class_id',
                'teacher_subjects.academic_year_id',
                'teacher_subjects.is_primary_teacher',
                'teacher_subjects.is_active',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'learning_areas.name as learning_area_name',
                'classes.name as class_name',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name',
                'teachers.staff_number',
                'academic_years.name as academic_year_name'
            )
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'teacher_id' => $assignment->teacher_id,
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'academic_year_id' => $assignment->academic_year_id,
                'teacher' => trim($assignment->first_name . ' ' . ($assignment->middle_name ? $assignment->middle_name . ' ' : '') . $assignment->last_name),
                'staff_number' => $assignment->staff_number,
                'subject' => $assignment->subject_name,
                'subject_code' => $assignment->subject_code,
                'learning_area' => $assignment->learning_area_name,
                'class' => $assignment->class_name,
                'academic_year' => $assignment->academic_year_name,
                'is_primary_teacher' => (bool) $assignment->is_primary_teacher,
                'is_active' => (bool) $assignment->is_active,
            ])
            ->values();

        return Inertia::render('classes/Allocations', [
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'academicYears' => $academicYears,
            'assignments' => $assignments,
            'stats' => [
                'total_assignments' => $assignments->count(),
                'active_assignments' => $assignments->where('is_active', true)->count(),
                'primary_assignments' => $assignments->where('is_primary_teacher', true)->count(),
                'teachers_involved' => $assignments->pluck('teacher_id')->unique()->count(),
            ],
        ]);
    }

    public function storeAllocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'is_primary_teacher' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::table('teacher_subjects')->updateOrInsert(
            [
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ],
            [
                'is_primary_teacher' => $validated['is_primary_teacher'],
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Subject allocation saved successfully.');
    }

    public function updateAllocation(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'is_primary_teacher' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::table('teacher_subjects')->where('id', $id)->update(array_merge($validated, ['updated_at' => now()]));

        return back()->with('success', 'Subject allocation updated successfully.');
    }

    public function destroyAllocation(int $id): RedirectResponse
    {
        DB::table('teacher_subjects')->where('id', $id)->delete();
        return back()->with('success', 'Subject allocation deleted successfully.');
    }

    public function classes(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $gradeId = $request->integer('grade_id');
        $view = (string) $request->string('view', 'grid');
        $perPage = min(max((int) $request->integer('per_page', 20), 5), 1000);

        $classes = SchoolClass::query()
            ->with(['gradeLevel:id,name', 'stream:id,name,code', 'academicYear:id,name'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($gradeId > 0, fn ($q) => $q->where('grade_level_id', $gradeId))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $classes->getCollection()->transform(function (SchoolClass $class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade' => $class->gradeLevel?->name,
                'stream' => $class->stream?->name,
                'stream_code' => $class->stream?->code,
                'teacher' => null,
                'students' => $class->students_count,
                'capacity' => $class->capacity,
                'academic_year' => $class->academicYear?->name,
                'utilization' => $class->capacity ? round(($class->students_count / $class->capacity) * 100) : 0,
            ];
        });

        return Inertia::render('classes/Index', [
            'classes' => $classes,
            'stats' => [
                'total_classes' => SchoolClass::count(),
                'total_students' => Student::where('status', 'active')->count(),
                'average_class_size' => (int) round((float) Student::where('status', 'active')->count() / max(SchoolClass::count(), 1)),
                'grades_count' => GradeLevel::count(),
            ],
            'filters' => [
                'search' => $search,
                'grade_id' => $gradeId ?: null,
                'view' => in_array($view, ['grid', 'list'], true) ? $view : 'grid',
                'per_page' => $perPage,
            ],
            'grades' => GradeLevel::query()->orderBy('level_order')->get(['id', 'name']),
        ]);
    }

    public function createClass(): Response
    {
        return Inertia::render('classes/Create', [
            'grades' => GradeLevel::query()->orderBy('level_order')->get(['id', 'name', 'code']),
            'streams' => Stream::query()->orderBy('name')->get(['id', 'name', 'code']),
            'academicYears' => DB::table('academic_years')->select('id', 'name')->orderByDesc('start_date')->get(),
        ]);
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('classes', 'code')],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolId = DB::table('schools')->value('id');

        $class = SchoolClass::create([
            'school_id' => $schoolId,
            'grade_level_id' => $validated['grade_level_id'],
            'stream_id' => $validated['stream_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'capacity' => $validated['capacity'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('classes.show', $class->id)->with('success', 'Class created successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'promote'])],
        ]);

        if ($validated['action'] === 'promote') {
            return $this->bulkClassPromote($validated['class_ids']);
        }

        $query = SchoolClass::query()->whereIn('id', $validated['class_ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk class action completed successfully.');
    }

    protected function bulkClassPromote(array $classIds): RedirectResponse
    {
        $schoolId = DB::table('schools')->value('id');
        $classes = SchoolClass::whereIn('id', $classIds)->with(['gradeLevel', 'stream'])->get();
        $promotedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($classes, $schoolId, &$promotedCount, &$skippedCount) {
            foreach ($classes as $class) {
                // Find next grade
                $nextGrade = GradeLevel::where('school_id', $schoolId)
                    ->where('level_order', '>', $class->gradeLevel->level_order)
                    ->orderBy('level_order')
                    ->first();

                if (!$nextGrade) {
                    $skippedCount++;
                    continue;
                }

                // Find next academic year
                $currentYear = DB::table('academic_years')->where('id', $class->academic_year_id)->first();
                $nextYear = DB::table('academic_years')
                    ->where('school_id', $schoolId)
                    ->where('start_date', '>', $currentYear?->end_date ?? now())
                    ->orderBy('start_date')
                    ->first();

                $targetYearId = $nextYear ? $nextYear->id : $class->academic_year_id;

                // Find or create target class
                $targetClass = SchoolClass::where('school_id', $schoolId)
                    ->where('academic_year_id', $targetYearId)
                    ->where('grade_level_id', $nextGrade->id)
                    ->where('stream_id', $class->stream_id)
                    ->first();

                if (!$targetClass) {
                    // Auto-create the target class if it doesn't exist
                    $targetClass = SchoolClass::create([
                        'school_id' => $schoolId,
                        'academic_year_id' => $targetYearId,
                        'grade_level_id' => $nextGrade->id,
                        'stream_id' => $class->stream_id,
                        'name' => $nextGrade->name . ($class->stream ? ' ' . $class->stream->name : ''),
                        'code' => $nextGrade->code . ($class->stream ? $class->stream->code : ''),
                        'capacity' => $class->capacity,
                        'is_active' => true,
                    ]);
                }

                // Move all active students
                $enrollments = DB::table('student_enrollments')
                    ->where('class_id', $class->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    // Check if already enrolled in target
                    $exists = DB::table('student_enrollments')
                        ->where('student_id', $enrollment->student_id)
                        ->where('class_id', $targetClass->id)
                        ->exists();

                    if (!$exists) {
                        // Get first term of target year if available
                        $termId = DB::table('academic_terms')
                            ->where('academic_year_id', $targetYearId)
                            ->orderBy('start_date')
                            ->value('id');

                        DB::table('student_enrollments')->insert([
                            'student_id' => $enrollment->student_id,
                            'class_id' => $targetClass->id,
                            'academic_year_id' => $targetYearId,
                            'academic_term_id' => $termId,
                            'status' => 'active',
                            'enrollment_type' => 'promoted',
                            'enrollment_date' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update student's current class
                        DB::table('students')
                            ->where('id', $enrollment->student_id)
                            ->update(['current_class_id' => $targetClass->id]);

                        $promotedCount++;
                    }
                }
            }
        });

        return back()->with('success', "Promotion complete. Promoted students from selected classes. ({$promotedCount} enrollments created)");
    }

    public function showClass(Request $request, int $id): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $gender = (string) $request->string('gender');

        $class = SchoolClass::query()
            ->with(['gradeLevel:id,name,code,level_order', 'stream:id,name,code', 'academicYear:id,name', 'classTeacher:id,name,email'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->findOrFail($id);

        $subjectAllocations = DB::table('teacher_subjects')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->join('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
            ->where('teacher_subjects.class_id', $class->id)
            ->select(
                'teacher_subjects.id',
                'teacher_subjects.teacher_id',
                'teacher_subjects.subject_id',
                'teacher_subjects.academic_year_id',
                'teacher_subjects.is_primary_teacher',
                'teacher_subjects.is_active',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'subjects.subject_type',
                'learning_areas.name as learning_area_name',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name'
            )
            ->orderBy('subjects.name')
            ->get()
            ->map(fn ($allocation) => [
                'id' => $allocation->id,
                'teacher_id' => $allocation->teacher_id,
                'subject_id' => $allocation->subject_id,
                'academic_year_id' => $allocation->academic_year_id,
                'subject' => $allocation->subject_name,
                'code' => $allocation->subject_code,
                'type' => $allocation->subject_type,
                'learning_area' => $allocation->learning_area_name,
                'teacher' => trim($allocation->first_name . ' ' . ($allocation->middle_name ? $allocation->middle_name . ' ' : '') . $allocation->last_name),
                'is_primary_teacher' => (bool) $allocation->is_primary_teacher,
                'is_active' => (bool) $allocation->is_active,
            ])
            ->values();

        $students = Student::query()
            ->where('current_class_id', $class->id)
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_number', 'gender', 'status'])
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'gender' => ucfirst($student->gender),
                'status' => $student->status,
            ])
            ->values();

        $transferTargets = SchoolClass::query()
            ->where('grade_level_id', $class->grade_level_id)
            ->where('id', '!=', $class->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (SchoolClass $target) => ['id' => $target->id, 'name' => $target->name])
            ->values();

        return Inertia::render('classes/Show', [
            'classroom' => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade' => $class->gradeLevel?->name,
                'stream' => $class->stream?->name,
                'academic_year' => $class->academicYear?->name,
                'capacity' => $class->capacity,
                'students_count' => $class->students_count,
                'utilization' => $class->capacity ? round(($class->students_count / $class->capacity) * 100) : 0,
                'is_active' => $class->is_active,
                'teacher' => $class->classTeacher?->name,
                'teacher_email' => $class->classTeacher?->email,
            ],
            'subjectAllocations' => $subjectAllocations,
            'students' => $students,
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'gender' => $gender === '' ? 'all' : $gender,
            ],
            'transferTargets' => $transferTargets,
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
            'genderOptions' => [
                ['value' => 'all', 'label' => 'All Genders'],
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
            ],
        ]);
    }

    public function grades(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $grades = GradeLevel::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('level_order')
            ->get()
            ->map(function (GradeLevel $grade) {
                $classIds = DB::table('classes')->where('grade_level_id', $grade->id)->pluck('id');
                $students = $classIds->isEmpty() ? 0 : Student::whereIn('current_class_id', $classIds)->where('status', 'active')->count();

                return [
                    'id' => $grade->id,
                    'name' => $grade->name,
                    'code' => $grade->code,
                    'category' => $grade->category,
                    'level_order' => $grade->level_order,
                    'minimum_age' => $grade->minimum_age,
                    'maximum_age' => $grade->maximum_age,
                    'is_active' => $grade->is_active,
                    'classes_count' => DB::table('classes')->where('grade_level_id', $grade->id)->count(),
                    'students_count' => $students,
                    'lead_name' => null,
                ];
            })
            ->values();

        return Inertia::render('grades/Index', [
            'grades' => $grades,
            'stats' => [
                'total_grades' => $grades->count(),
                'active_grades' => $grades->where('is_active', true)->count(),
                'total_classes' => $grades->sum('classes_count'),
                'total_students' => $grades->sum('students_count'),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'view' => in_array($view, ['grid', 'list'], true) ? $view : 'grid',
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
        ]);
    }

    public function createGrade(): Response
    {
        return Inertia::render('grades/Create');
    }

    public function storeGrade(Request $request): RedirectResponse
    {
        if ($request->boolean('is_bulk')) {
            $validated = $request->validate([
                'base_name' => ['required', 'string', 'max:100'],
                'start_level' => ['required', 'integer', 'min:1'],
                'end_level' => ['required', 'integer', 'min:1', 'gte:start_level'],
                'category' => ['required', 'string', 'max:100'],
                'is_active' => ['required', 'boolean'],
            ]);

            $schoolId = DB::table('schools')->value('id');
            $created = 0;

            DB::transaction(function () use ($validated, $schoolId, &$created) {
                for ($i = $validated['start_level']; $i <= $validated['end_level']; $i++) {
                    $name = $validated['base_name'] . ' ' . $i;
                    $code = strtoupper(substr($validated['base_name'], 0, 1)) . $i;

                    // Ensure uniqueness
                    if (!GradeLevel::where('school_id', $schoolId)->where('code', $code)->exists()) {
                        GradeLevel::create([
                            'school_id' => $schoolId,
                            'name' => $name,
                            'code' => $code,
                            'category' => $validated['category'],
                            'level_order' => $i,
                            'is_active' => $validated['is_active'],
                        ]);
                        $created++;
                    }
                }
            });

            return redirect()->route('grades.index')->with('success', "Successfully created {$created} grades.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('grade_levels', 'code')],
            'category' => ['required', 'string', 'max:100'],
            'level_order' => ['required', 'integer', 'min:1'],
            'minimum_age' => ['nullable', 'integer', 'min:1'],
            'maximum_age' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolId = DB::table('schools')->value('id');
        $grade = GradeLevel::create(array_merge($validated, ['school_id' => $schoolId]));

        return redirect()->route('grades.show', $grade->id)->with('success', 'Grade created successfully.');
    }

    public function bulkGradeAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['integer', 'exists:grade_levels,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = GradeLevel::query()->whereIn('id', $validated['grade_ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk grade action completed successfully.');
    }

    public function showGrade(int $id): Response
    {
        $grade = GradeLevel::findOrFail($id);
        $classIds = SchoolClass::query()->where('grade_level_id', $grade->id)->pluck('id');

        $classes = SchoolClass::query()
            ->with(['stream:id,name,code', 'academicYear:id,name', 'classTeacher:id,name'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->where('grade_level_id', $grade->id)
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'stream' => $class->stream?->name,
                'academic_year' => $class->academicYear?->name,
                'teacher' => $class->classTeacher?->name,
                'students_count' => $class->students_count,
                'capacity' => $class->capacity,
            ])
            ->values();

        $subjects = DB::table('subject_grade_levels')
            ->join('subjects', 'subjects.id', '=', 'subject_grade_levels.subject_id')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->where('subject_grade_levels.grade_level_id', $grade->id)
            ->where('subject_grade_levels.is_active', true)
            ->orderBy('subjects.name')
            ->select(
                'subject_grade_levels.lessons_per_week',
                'subject_grade_levels.minutes_per_lesson',
                'subject_grade_levels.is_compulsory',
                'subject_grade_levels.is_active as allocation_is_active',
                'subjects.id',
                'subjects.name',
                'subjects.code',
                'subjects.subject_type',
                'subjects.is_active as subject_is_active',
                'learning_areas.name as learning_area_name'
            )
            ->get()
            ->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'subject_type' => $subject->subject_type,
                'learning_area' => $subject->learning_area_name,
                'lessons_per_week' => $subject->lessons_per_week,
                'minutes_per_lesson' => $subject->minutes_per_lesson,
                'is_compulsory' => (bool) $subject->is_compulsory,
                'is_active' => (bool) $subject->allocation_is_active,
                'subject_is_active' => (bool) $subject->subject_is_active,
            ])
            ->values();

        $studentsCount = $classIds->isEmpty()
            ? 0
            : Student::query()->whereIn('current_class_id', $classIds)->count();

        $activeStudentsCount = $classIds->isEmpty()
            ? 0
            : Student::query()->whereIn('current_class_id', $classIds)->where('status', 'active')->count();

        return Inertia::render('grades/Show', [
            'grade' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'code' => $grade->code,
                'category' => $grade->category,
                'level_order' => $grade->level_order,
                'minimum_age' => $grade->minimum_age,
                'maximum_age' => $grade->maximum_age,
                'is_active' => $grade->is_active,
                'lead_name' => null,
            ],
            'subjects' => $subjects,
            'classes' => $classes,
            'stats' => [
                'students_count' => $studentsCount,
                'active_students_count' => $activeStudentsCount,
                'subjects_count' => $subjects->count(),
                'compulsory_subjects_count' => $subjects->where('is_compulsory', true)->count(),
            ],
        ]);
    }

    public function gradeSubjects(int $id): Response
    {
        $grade = GradeLevel::findOrFail($id);

        $subjects = DB::table('subject_grade_levels')
            ->join('subjects', 'subjects.id', '=', 'subject_grade_levels.subject_id')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->leftJoin('teacher_subjects', function ($join) use ($grade) {
                $join->on('teacher_subjects.subject_id', '=', 'subjects.id')
                    ->join('classes', 'classes.id', '=', 'teacher_subjects.class_id')
                    ->where('classes.grade_level_id', '=', $grade->id);
            })
            ->leftJoin('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
            ->where('subject_grade_levels.grade_level_id', $grade->id)
            ->orderBy('subjects.name')
            ->select(
                'subjects.id as subject_id',
                'subjects.name',
                'subjects.code',
                'subjects.description',
                'subjects.subject_type',
                'subjects.is_examinable',
                'subjects.is_active as subject_is_active',
                'learning_areas.name as learning_area_name',
                'subject_grade_levels.lessons_per_week',
                'subject_grade_levels.minutes_per_lesson',
                'subject_grade_levels.is_compulsory',
                'subject_grade_levels.is_active as allocation_is_active',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name'
            )
            ->get()
            ->groupBy('subject_id')
            ->map(function ($rows) {
                $first = $rows->first();
                $teachers = collect($rows)
                    ->map(fn ($row) => trim(($row->first_name ?? '') . ' ' . ($row->middle_name ? $row->middle_name . ' ' : '') . ($row->last_name ?? '')))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'id' => $first->subject_id,
                    'name' => $first->name,
                    'code' => $first->code,
                    'description' => $first->description,
                    'subject_type' => $first->subject_type,
                    'learning_area' => $first->learning_area_name,
                    'is_examinable' => (bool) $first->is_examinable,
                    'subject_is_active' => (bool) $first->subject_is_active,
                    'is_active' => (bool) $first->allocation_is_active,
                    'lessons_per_week' => $first->lessons_per_week,
                    'minutes_per_lesson' => $first->minutes_per_lesson,
                    'is_compulsory' => (bool) $first->is_compulsory,
                    'teachers' => $teachers,
                    'teachers_count' => $teachers->count(),
                ];
            })
            ->values();

        return Inertia::render('grades/Subjects', [
            'grade' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'code' => $grade->code,
                'category' => $grade->category,
                'level_order' => $grade->level_order,
                'is_active' => $grade->is_active,
            ],
            'subjects' => $subjects,
            'stats' => [
                'total' => $subjects->count(),
                'active' => $subjects->where('is_active', true)->count(),
                'compulsory' => $subjects->where('is_compulsory', true)->count(),
                'examinable' => $subjects->where('is_examinable', true)->count(),
            ],
        ]);
    }

    public function gradeStudents(Request $request, int $id): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $grade = GradeLevel::findOrFail($id);
        $classIds = SchoolClass::query()->where('grade_level_id', $grade->id)->pluck('id');

        $students = Student::query()
            ->with(['currentClass:id,name,stream_id', 'currentClass.stream:id,name'])
            ->when(!$classIds->isEmpty(), fn ($q) => $q->whereIn('current_class_id', $classIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_number', 'gender', 'status', 'current_class_id'])
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'gender' => ucfirst($student->gender),
                'status' => $student->status,
                'class_name' => $student->currentClass?->name,
                'stream_name' => $student->currentClass?->stream?->name,
            ])
            ->values();

        return Inertia::render('grades/Students', [
            'grade' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'code' => $grade->code,
                'category' => $grade->category,
                'level_order' => $grade->level_order,
                'is_active' => $grade->is_active,
            ],
            'students' => $students,
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
            'stats' => [
                'total' => $students->count(),
                'active' => $students->where('status', 'active')->count(),
            ],
        ]);
    }

    public function streams(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $streams = Stream::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->get()
            ->map(function (Stream $stream) {
                $classIds = DB::table('classes')->where('stream_id', $stream->id)->pluck('id');
                $students = $classIds->isEmpty() ? 0 : Student::whereIn('current_class_id', $classIds)->where('status', 'active')->count();

                return [
                    'id' => $stream->id,
                    'name' => $stream->name,
                    'code' => $stream->code,
                    'capacity' => $stream->capacity,
                    'is_active' => $stream->is_active,
                    'classes_count' => DB::table('classes')->where('stream_id', $stream->id)->count(),
                    'students_count' => $students,
                    'lead_name' => null,
                ];
            })
            ->values();

        return Inertia::render('streams/Index', [
            'streams' => $streams,
            'stats' => [
                'total_streams' => $streams->count(),
                'active_streams' => $streams->where('is_active', true)->count(),
                'total_classes' => $streams->sum('classes_count'),
                'total_students' => $streams->sum('students_count'),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'view' => in_array($view, ['grid', 'list'], true) ? $view : 'grid',
            ],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
        ]);
    }

    public function createStream(): Response
    {
        return Inertia::render('streams/Create');
    }

    public function storeStream(Request $request): RedirectResponse
    {
        if ($request->boolean('is_bulk')) {
            $validated = $request->validate([
                'names' => ['required', 'string'],
                'capacity' => ['nullable', 'integer', 'min:1'],
                'is_active' => ['required', 'boolean'],
            ]);

            $schoolId = DB::table('schools')->value('id');
            $names = array_map('trim', explode(',', $validated['names']));
            $created = 0;

            DB::transaction(function () use ($names, $validated, $schoolId, &$created) {
                foreach ($names as $name) {
                    if (empty($name)) continue;

                    $code = strtoupper(substr($name, 0, 3));

                    // Ensure uniqueness
                    if (!Stream::where('school_id', $schoolId)->where('code', $code)->exists()) {
                        Stream::create([
                            'school_id' => $schoolId,
                            'name' => $name,
                            'code' => $code,
                            'capacity' => $validated['capacity'] ?? 40,
                            'is_active' => $validated['is_active'],
                        ]);
                        $created++;
                    }
                }
            });

            return redirect()->route('streams.index')->with('success', "Successfully created {$created} streams.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('streams', 'code')],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolId = DB::table('schools')->value('id');
        $stream = Stream::create(array_merge($validated, ['school_id' => $schoolId]));

        return redirect()->route('streams.show', $stream->id)->with('success', 'Stream created successfully.');
    }

    public function autoCreateClasses(): RedirectResponse
    {
        $schoolId = DB::table('schools')->value('id');
        $academicYearId = DB::table('academic_years')->where('is_current', true)->value('id')
            ?? DB::table('academic_years')->orderByDesc('start_date')->value('id');

        if (!$academicYearId) {
            return back()->with('error', 'No academic year found. Please create one first.');
        }

        $grades = GradeLevel::where('school_id', $schoolId)->where('is_active', true)->get();
        $streams = Stream::where('school_id', $schoolId)->where('is_active', true)->get();
        $created = 0;

        DB::transaction(function () use ($grades, $streams, $schoolId, $academicYearId, &$created) {
            foreach ($grades as $grade) {
                if ($streams->isEmpty()) {
                    // Create single class for the grade
                    $this->createClassIfNotExists($schoolId, $academicYearId, $grade, null, $created);
                } else {
                    foreach ($streams as $stream) {
                        $this->createClassIfNotExists($schoolId, $academicYearId, $grade, $stream, $created);
                    }
                }
            }
        });

        return back()->with('success', "Auto-generation complete. Created {$created} new classes.");
    }

    protected function createClassIfNotExists(int $schoolId, int $academicYearId, GradeLevel $grade, ?Stream $stream, int &$created): void
    {
        $name = $grade->name . ($stream ? ' ' . $stream->name : '');
        $code = $grade->code . ($stream ? $stream->code : '');

        $exists = SchoolClass::where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $grade->id)
            ->where(function($q) use ($stream) {
                if ($stream) {
                    $q->where('stream_id', $stream->id);
                } else {
                    $q->whereNull('stream_id');
                }
            })
            ->exists();

        if (!$exists) {
            SchoolClass::create([
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'grade_level_id' => $grade->id,
                'stream_id' => $stream?->id,
                'name' => $name,
                'code' => $code,
                'capacity' => $stream?->capacity ?? 40,
                'is_active' => true,
            ]);
            $created++;
        }
    }

    public function showStream(int $id): Response
    {
        $stream = Stream::findOrFail($id);
        $classes = SchoolClass::query()
            ->with(['gradeLevel:id,name', 'academicYear:id,name', 'classTeacher:id,name'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->where('stream_id', $stream->id)
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade' => $class->gradeLevel?->name,
                'academic_year' => $class->academicYear?->name,
                'teacher' => $class->classTeacher?->name,
                'students_count' => $class->students_count,
                'capacity' => $class->capacity,
            ])
            ->values();

        return Inertia::render('streams/Show', [
            'stream' => [
                'id' => $stream->id,
                'name' => $stream->name,
                'code' => $stream->code,
                'capacity' => $stream->capacity,
                'is_active' => $stream->is_active,
                'lead_name' => null,
            ],
            'classes' => $classes,
        ]);
    }

    public function editStream(int $id): Response
    {
        $stream = Stream::findOrFail($id);

        return Inertia::render('streams/Edit', [
            'stream' => [
                'id' => $stream->id,
                'name' => $stream->name,
                'code' => $stream->code,
                'capacity' => $stream->capacity,
                'is_active' => $stream->is_active,
            ],
        ]);
    }

    public function updateStream(Request $request, int $id): RedirectResponse
    {
        $stream = Stream::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('streams', 'code')->ignore($stream->id)],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $stream->update($validated);

        return redirect()->route('streams.show', $stream->id)->with('success', 'Stream updated successfully.');
    }

    public function activateStream(int $id): RedirectResponse
    {
        Stream::whereKey($id)->update(['is_active' => true]);
        return back()->with('success', 'Stream activated successfully.');
    }

    public function deactivateStream(int $id): RedirectResponse
    {
        Stream::whereKey($id)->update(['is_active' => false]);
        return back()->with('success', 'Stream deactivated successfully.');
    }

    public function destroyStream(int $id): RedirectResponse
    {
        Stream::findOrFail($id)->delete();
        return redirect()->route('streams.index')->with('success', 'Stream deleted successfully.');
    }

    public function bulkStreamAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stream_ids' => ['required', 'array', 'min:1'],
            'stream_ids.*' => ['integer', 'exists:streams,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = Stream::query()->whereIn('id', $validated['stream_ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk stream action completed successfully.');
    }

    public function classSubjects(int $id): Response
    {
        $class = SchoolClass::query()
            ->with(['gradeLevel:id,name,code,level_order', 'stream:id,name,code', 'academicYear:id,name', 'classTeacher:id,name,email'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->findOrFail($id);

        $subjects = DB::table('teacher_subjects')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->leftJoin('learning_areas', 'learning_areas.id', '=', 'subjects.learning_area_id')
            ->join('teachers', 'teachers.id', '=', 'teacher_subjects.teacher_id')
            ->leftJoin('subject_grade_levels', function ($join) use ($class) {
                $join->on('subject_grade_levels.subject_id', '=', 'subjects.id')
                    ->where('subject_grade_levels.grade_level_id', '=', $class->grade_level_id);
            })
            ->where('teacher_subjects.class_id', $class->id)
            ->select(
                'teacher_subjects.id',
                'teacher_subjects.teacher_id',
                'teacher_subjects.subject_id',
                'teacher_subjects.academic_year_id',
                'teacher_subjects.is_primary_teacher',
                'teacher_subjects.is_active',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'subjects.description as subject_description',
                'subjects.subject_type',
                'subjects.is_examinable',
                'subjects.is_active as subject_is_active',
                'learning_areas.name as learning_area_name',
                'subject_grade_levels.lessons_per_week',
                'subject_grade_levels.minutes_per_lesson',
                'subject_grade_levels.is_compulsory',
                'teachers.first_name',
                'teachers.middle_name',
                'teachers.last_name'
            )
            ->orderBy('subjects.name')
            ->get()
            ->map(fn ($subject) => [
                'allocation_id' => $subject->id,
                'teacher_id' => $subject->teacher_id,
                'subject_id' => $subject->subject_id,
                'academic_year_id' => $subject->academic_year_id,
                'name' => $subject->subject_name,
                'code' => $subject->subject_code,
                'description' => $subject->subject_description,
                'subject_type' => $subject->subject_type,
                'learning_area' => $subject->learning_area_name,
                'teacher' => trim($subject->first_name . ' ' . ($subject->middle_name ? $subject->middle_name . ' ' : '') . $subject->last_name),
                'is_primary_teacher' => (bool) $subject->is_primary_teacher,
                'is_active' => (bool) $subject->is_active,
                'subject_is_active' => (bool) $subject->subject_is_active,
                'is_examinable' => (bool) $subject->is_examinable,
                'lessons_per_week' => $subject->lessons_per_week,
                'minutes_per_lesson' => $subject->minutes_per_lesson,
                'is_compulsory' => (bool) $subject->is_compulsory,
            ])
            ->values();

        $teachers = Teacher::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(fn (Teacher $teacher) => [
                'id' => $teacher->id,
                'name' => trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name),
            ])
            ->values();

        return Inertia::render('classes/Subjects', [
            'classroom' => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade' => $class->gradeLevel?->name,
                'grade_id' => $class->grade_level_id,
                'stream' => $class->stream?->name,
                'academic_year' => $class->academicYear?->name,
                'academic_year_id' => $class->academic_year_id,
                'students_count' => $class->students_count,
                'teacher' => $class->classTeacher?->name,
            ],
            'subjects' => $subjects,
            'teachers' => $teachers,
            'stats' => [
                'total' => $subjects->count(),
                'active' => $subjects->where('is_active', true)->count(),
                'compulsory' => $subjects->where('is_compulsory', true)->count(),
                'examinable' => $subjects->where('is_examinable', true)->count(),
            ],
        ]);
    }

    // Departments management
    public function departments(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $perPage = min(max((int) $request->integer('per_page', 20), 5), 1000);

        $query = \App\Models\Academic\Department::query()
            ->with('headOfDepartment')
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name');

        $departments = $query->paginate($perPage)->withQueryString();

        $departments->getCollection()->transform(function ($dept) {
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'code' => $dept->code,
                'description' => $dept->description,
                'head_of_department' => $dept->headOfDepartment?->name,
                'is_active' => (bool) $dept->is_active,
            ];
        });

        return Inertia::render('departments/Index', [
            'departments' => $departments,
            'stats' => [
                'total' => \App\Models\Academic\Department::count(),
                'active' => \App\Models\Academic\Department::where('is_active', true)->count(),
            ],
            'filters' => [
                'search' => $search,
                'view' => $request->string('view', 'grid'),
                'per_page' => $perPage,
            ],
            'teachers' => \App\Models\Teacher::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name'])->map(fn($t) => ['id' => $t->id, 'name' => trim($t->first_name . ' ' . ($t->middle_name ? $t->middle_name . ' ' : '') . $t->last_name)]),
        ]);
    }

    public function createDepartment(): Response
    {
        $teachers = \App\Models\Teacher::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name'])->map(fn($t) => ['id' => $t->id, 'name' => trim($t->first_name . ' ' . ($t->middle_name ? $t->middle_name . ' ' : '') . $t->last_name)]);
        return Inertia::render('departments/Create', [
            'teachers' => $teachers,
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'head_of_department_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['school_id'] = DB::table('schools')->value('id');
        $validated['is_active'] = $request->boolean('is_active', true);

        \App\Models\Academic\Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function editDepartment(int $id): Response
    {
        $dept = \App\Models\Academic\Department::findOrFail($id);
        $teachers = \App\Models\Teacher::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name'])->map(fn($t) => ['id' => $t->id, 'name' => trim($t->first_name . ' ' . ($t->middle_name ? $t->middle_name . ' ' : '') . $t->last_name)]);

        return Inertia::render('departments/Edit', [
            'department' => $dept,
            'teachers' => $teachers,
        ]);
    }

    public function updateDepartment(Request $request, int $id): RedirectResponse
    {
        $dept = \App\Models\Academic\Department::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'head_of_department_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $dept->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active', $dept->is_active),
        ]));

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(int $id): RedirectResponse
    {
        $dept = \App\Models\Academic\Department::findOrFail($id);
        $dept->delete();
        return back()->with('success', 'Department deleted successfully.');
    }
}
