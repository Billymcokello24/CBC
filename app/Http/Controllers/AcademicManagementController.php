<?php

namespace App\Http\Controllers;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use App\Models\Student;
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

    public function showClass(int $id): Response
    {
        $class = SchoolClass::query()
            ->with(['gradeLevel:id,name,code', 'stream:id,name,code', 'academicYear:id,name'])
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->findOrFail($id);

        $students = Student::query()
            ->where('current_class_id', $class->id)
            ->where('status', 'active')
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
            ],
            'students' => $students,
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
            ->withCount(['students as students_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'grade' => $class->gradeLevel?->name,
                'stream' => $class->stream?->name,
                'capacity' => $class->capacity,
                'students' => $class->students_count,
                'available_slots' => max(($class->capacity ?? 0) - $class->students_count, 0),
                'academic_year' => $class->academicYear?->name,
                'teacher' => null,
            ])
            ->values();

        return Inertia::render('classes/Allocations', [
            'allocations' => $classes,
            'stats' => [
                'classes_with_space' => $classes->where('available_slots', '>', 0)->count(),
                'full_classes' => $classes->where('available_slots', 0)->count(),
                'total_capacity' => $classes->sum('capacity'),
                'occupied_slots' => $classes->sum('students'),
            ],
        ]);
    }

    public function grades(Request $request): Response
    {
        $grades = GradeLevel::query()
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
        ]);
    }

    public function streams(Request $request): Response
    {
        $streams = Stream::query()
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
        ]);
    }
}
