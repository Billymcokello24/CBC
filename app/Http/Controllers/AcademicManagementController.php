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
    public function classes(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $gradeId = $request->integer('grade_id');
        $view = (string) $request->string('view', 'grid');

        $classes = SchoolClass::query()
            ->with(['gradeLevel:id,name', 'stream:id,name,code', 'academicYear:id,name'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($gradeId > 0, fn ($q) => $q->where('grade_level_id', $gradeId))
            ->orderBy('name')
            ->get()
            ->map(function (SchoolClass $class) {
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
            })
            ->values();

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

    public function createGrade(): Response
    {
        return Inertia::render('grades/Create');
    }

    public function storeGrade(Request $request): RedirectResponse
    {
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
        $grade = GradeLevel::create([...$validated, 'school_id' => $schoolId]);

        return redirect()->route('grades.show', $grade->id)->with('success', 'Grade created successfully.');
    }

    public function showGrade(int $id): Response
    {
        $grade = GradeLevel::findOrFail($id);
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
                'subjects.id',
                'subjects.name',
                'subjects.code',
                'subjects.subject_type',
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
            ])
            ->values();

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
        ]);
    }

    public function editClass(int $id): Response
    {
        $class = SchoolClass::findOrFail($id);

        return Inertia::render('classes/Edit', [
            'classroom' => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'grade_level_id' => $class->grade_level_id,
                'stream_id' => $class->stream_id,
                'academic_year_id' => $class->academic_year_id,
                'capacity' => $class->capacity,
                'is_active' => $class->is_active,
            ],
            'grades' => GradeLevel::query()->orderBy('level_order')->get(['id', 'name', 'code']),
            'streams' => Stream::query()->orderBy('name')->get(['id', 'name', 'code']),
            'academicYears' => DB::table('academic_years')->select('id', 'name')->orderByDesc('start_date')->get(),
        ]);
    }

    public function updateClass(Request $request, int $id): RedirectResponse
    {
        $class = SchoolClass::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('classes', 'code')->ignore($class->id)],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $class->update($validated);

        return redirect()->route('classes.show', $class->id)->with('success', 'Class updated successfully.');
    }

    public function destroyClass(int $id): RedirectResponse
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = SchoolClass::query()->whereIn('id', $validated['class_ids']);

        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk class action completed successfully.');
    }

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

        DB::table('teacher_subjects')->where('id', $id)->update([
            ...$validated,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Subject allocation updated successfully.');
    }

    public function destroyAllocation(int $id): RedirectResponse
    {
        DB::table('teacher_subjects')->where('id', $id)->delete();
        return back()->with('success', 'Subject allocation deleted successfully.');
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


    public function editGrade(int $id): Response
    {
        $grade = GradeLevel::findOrFail($id);

        return Inertia::render('grades/Edit', [
            'grade' => [
                'id' => $grade->id,
                'name' => $grade->name,
                'code' => $grade->code,
                'category' => $grade->category,
                'level_order' => $grade->level_order,
                'minimum_age' => $grade->minimum_age,
                'maximum_age' => $grade->maximum_age,
                'is_active' => $grade->is_active,
            ],
        ]);
    }

    public function updateGrade(Request $request, int $id): RedirectResponse
    {
        $grade = GradeLevel::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('grade_levels', 'code')->ignore($grade->id)],
            'category' => ['required', 'string', 'max:100'],
            'level_order' => ['required', 'integer', 'min:1'],
            'minimum_age' => ['nullable', 'integer', 'min:1'],
            'maximum_age' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $grade->update($validated);

        return redirect()->route('grades.show', $grade->id)->with('success', 'Grade updated successfully.');
    }

    public function activateGrade(int $id): RedirectResponse
    {
        GradeLevel::whereKey($id)->update(['is_active' => true]);
        return back()->with('success', 'Grade activated successfully.');
    }

    public function deactivateGrade(int $id): RedirectResponse
    {
        GradeLevel::whereKey($id)->update(['is_active' => false]);
        return back()->with('success', 'Grade deactivated successfully.');
    }

    public function destroyGrade(int $id): RedirectResponse
    {
        GradeLevel::findOrFail($id)->delete();
        return redirect()->route('grades.index')->with('success', 'Grade deleted successfully.');
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('streams', 'code')],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolId = DB::table('schools')->value('id');
        $stream = Stream::create([...$validated, 'school_id' => $schoolId]);

        return redirect()->route('streams.show', $stream->id)->with('success', 'Stream created successfully.');
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
}
