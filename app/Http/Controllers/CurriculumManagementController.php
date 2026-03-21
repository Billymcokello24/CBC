<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\Competency;
use App\Models\Curriculum\LearningArea;
use App\Models\Academic\Department;
use App\Models\Curriculum\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CurriculumManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('curriculum/Index', [
            'stats' => [
                'learning_areas' => LearningArea::count(),
                'subjects' => Subject::count(),
                'strands' => DB::table('strands')->count(),
                'competencies' => Competency::count(),
            ],
            'recent_areas' => LearningArea::query()
                ->withCount('subjects')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get(),
            'recent_competencies' => Competency::query()
                ->orderBy('display_order')
                ->limit(4)
                ->get(),
        ]);
    }

    public function learningAreas(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $areas = LearningArea::query()
            ->withCount('subjects')
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('display_order')
            ->get()
            ->map(fn (LearningArea $area) => [
                'id' => $area->id,
                'name' => $area->name,
                'code' => $area->code,
                'description' => $area->description,
                'category' => $area->category,
                'display_order' => $area->display_order,
                'is_active' => $area->is_active,
                'subjects_count' => $area->subjects_count,
            ])
            ->values();

        return Inertia::render('curriculum/LearningAreas', [
            'areas' => $areas,
            'stats' => [
                'total' => $areas->count(),
                'active' => $areas->where('is_active', true)->count(),
                'subjects' => $areas->sum('subjects_count'),
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

    public function storeLearningArea(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('learning_areas', 'code')],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        LearningArea::create($validated);

        return back()->with('success', 'Learning area created successfully.');
    }

    public function updateLearningArea(Request $request, LearningArea $learningArea): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('learning_areas', 'code')->ignore($learningArea->id)],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $learningArea->update($validated);

        return back()->with('success', 'Learning area updated successfully.');
    }

    public function destroyLearningArea(LearningArea $learningArea): RedirectResponse
    {
        $learningArea->delete();
        return back()->with('success', 'Learning area deleted successfully.');
    }

    public function bulkLearningAreaAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:learning_areas,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = LearningArea::query()->whereIn('id', $validated['ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk learning area action completed successfully.');
    }

    public function subjects(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $subjects = Subject::query()
            ->with(['learningArea:id,name', 'department:id,name,code'])
            ->withCount('strands')
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('display_order')
            ->get()
            ->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'learning_area_id' => $subject->learning_area_id,
                'learning_area' => $subject->learningArea?->name,
                'department_id' => $subject->department_id,
                'department' => $subject->department ? [
                    'id' => $subject->department->id,
                    'name' => $subject->department->name,
                    'code' => $subject->department->code,
                ] : null,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'subject_type' => $subject->subject_type,
                'is_examinable' => $subject->is_examinable,
                'display_order' => $subject->display_order,
                'is_active' => $subject->is_active,
                'strands_count' => $subject->strands_count,
            ])
            ->values();

        return Inertia::render('curriculum/Subjects', [
            'subjects' => $subjects,
            'learningAreas' => LearningArea::query()->orderBy('display_order')->get(['id', 'name']),
            'stats' => [
                'total' => $subjects->count(),
                'active' => $subjects->where('is_active', true)->count(),
                'strands' => $subjects->sum('strands_count'),
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

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learning_area_id' => ['required', 'integer', 'exists:learning_areas,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')],
            'description' => ['nullable', 'string'],
            'subject_type' => ['required', 'string', 'max:50'],
            'is_examinable' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Subject::create($validated);
        return back()->with('success', 'Subject created successfully.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'learning_area_id' => ['required', 'integer', 'exists:learning_areas,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'description' => ['nullable', 'string'],
            'subject_type' => ['required', 'string', 'max:50'],
            'is_examinable' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $subject->update($validated);
        return back()->with('success', 'Subject updated successfully.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        $subject->delete();
        return back()->with('success', 'Subject deleted successfully.');
    }

    public function bulkSubjectAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:subjects,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = Subject::query()->whereIn('id', $validated['ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk subject action completed successfully.');
    }

    public function strands(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $strands = DB::table('strands')
            ->join('subjects', 'subjects.id', '=', 'strands.subject_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'strands.grade_level_id')
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('strands.name', 'like', "%{$search}%")->orWhere('strands.code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('strands.is_active', $status === 'active'))
            ->orderBy('strands.display_order')
            ->select('strands.*', 'subjects.name as subject_name', 'grade_levels.name as grade_name')
            ->get();

        return Inertia::render('curriculum/Strands', [
            'strands' => $strands,
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
            'grades' => DB::table('grade_levels')->orderBy('level_order')->get(['id', 'name']),
            'stats' => [
                'total' => $strands->count(),
                'active' => $strands->where('is_active', true)->count(),
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

    public function storeStrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::table('strands')->insert(array_merge($validated, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return back()->with('success', 'Strand created successfully.');
    }

    public function updateStrand(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::table('strands')->where('id', $id)->update(array_merge($validated, ['updated_at' => now()]));
        return back()->with('success', 'Strand updated successfully.');
    }

    public function destroyStrand(int $id): RedirectResponse
    {
        DB::table('strands')->where('id', $id)->delete();
        return back()->with('success', 'Strand deleted successfully.');
    }

    public function bulkStrandAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:strands,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = DB::table('strands')->whereIn('id', $validated['ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true, 'updated_at' => now()]),
            'deactivate' => $query->update(['is_active' => false, 'updated_at' => now()]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk strand action completed successfully.');
    }

    public function competencies(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $view = (string) $request->string('view', 'grid');

        $competencies = Competency::query()
            ->withCount('indicators')
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('display_order')
            ->get()
            ->map(fn (Competency $competency) => [
                'id' => $competency->id,
                'name' => $competency->name,
                'code' => $competency->code,
                'description' => $competency->description,
                'category' => $competency->category,
                'display_order' => $competency->display_order,
                'is_active' => $competency->is_active,
                'indicators_count' => $competency->indicators_count,
            ])
            ->values();

        return Inertia::render('curriculum/Competencies', [
            'competencies' => $competencies,
            'stats' => [
                'total' => $competencies->count(),
                'active' => $competencies->where('is_active', true)->count(),
                'indicators' => $competencies->sum('indicators_count'),
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

    public function storeCompetency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('competencies', 'code')],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Competency::create($validated);
        return back()->with('success', 'Competency created successfully.');
    }

    public function updateCompetency(Request $request, Competency $competency): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('competencies', 'code')->ignore($competency->id)],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $competency->update($validated);
        return back()->with('success', 'Competency updated successfully.');
    }

    public function destroyCompetency(Competency $competency): RedirectResponse
    {
        $competency->delete();
        return back()->with('success', 'Competency deleted successfully.');
    }

    public function bulkCompetencyAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:competencies,id'],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        $query = Competency::query()->whereIn('id', $validated['ids']);
        match ($validated['action']) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return back()->with('success', 'Bulk competency action completed successfully.');
    }

    public function createSubject(): Response
    {
        return Inertia::render('curriculum/CreateSubject', [
            'learningAreas' => LearningArea::query()->orderBy('display_order')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function createStrand(): Response
    {
        return Inertia::render('curriculum/CreateStrand', [
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
            'grades' => DB::table('grade_levels')->orderBy('level_order')->get(['id', 'name']),
        ]);
    }

    public function createCompetency(): Response
    {
        return Inertia::render('curriculum/CreateCompetency');
    }

    public function createLearningArea(): Response
    {
        return Inertia::render('curriculum/CreateLearningArea');
    }

    public function showLearningArea(LearningArea $learningArea): Response
    {
        $learningArea->load(['subjects' => fn ($query) => $query->orderBy('display_order')]);

        return Inertia::render('curriculum/ShowLearningArea', [
            'learningArea' => [
                'id' => $learningArea->id,
                'name' => $learningArea->name,
                'code' => $learningArea->code,
                'description' => $learningArea->description,
                'category' => $learningArea->category,
                'display_order' => $learningArea->display_order,
                'is_active' => $learningArea->is_active,
                'subjects' => $learningArea->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'subject_type' => $subject->subject_type,
                    'is_active' => $subject->is_active,
                ])->values(),
            ],
        ]);
    }

    public function editLearningArea(LearningArea $learningArea): Response
    {
        return Inertia::render('curriculum/EditLearningArea', [
            'learningArea' => [
                'id' => $learningArea->id,
                'name' => $learningArea->name,
                'code' => $learningArea->code,
                'description' => $learningArea->description,
                'category' => $learningArea->category,
                'display_order' => $learningArea->display_order,
                'is_active' => $learningArea->is_active,
            ],
        ]);
    }

    public function showSubject(Subject $subject): Response
    {
        $subject->load([
            'learningArea:id,name',
            'department.headOfDepartment:id,name',
            'strands' => fn ($query) => $query->orderBy('display_order')
        ]);

        return Inertia::render('curriculum/ShowSubject', [
            'subject' => [
                'id' => $subject->id,
                'learning_area_id' => $subject->learning_area_id,
                'learning_area' => $subject->learningArea?->name,
                'department' => $subject->department ? [
                    'id' => $subject->department->id,
                    'name' => $subject->department->name,
                    'code' => $subject->department->code,
                    'head_of_department' => $subject->department->headOfDepartment?->name,
                ] : null,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'subject_type' => $subject->subject_type,
                'is_examinable' => $subject->is_examinable,
                'display_order' => $subject->display_order,
                'is_active' => $subject->is_active,
                'strands' => $subject->strands->map(fn ($strand) => [
                    'id' => $strand->id,
                    'name' => $strand->name,
                    'code' => $strand->code,
                    'is_active' => $strand->is_active,
                ])->values(),
            ],
        ]);
    }

    public function editSubject(Subject $subject): Response
    {
        return Inertia::render('curriculum/EditSubject', [
            'subject' => [
                'id' => $subject->id,
                'learning_area_id' => $subject->learning_area_id,
                'department_id' => $subject->department_id,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'subject_type' => $subject->subject_type,
                'is_examinable' => $subject->is_examinable,
                'display_order' => $subject->display_order,
                'is_active' => $subject->is_active,
            ],
            'learningAreas' => LearningArea::query()->orderBy('display_order')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function showStrand(int $id): Response
    {
        $strand = DB::table('strands')
            ->join('subjects', 'subjects.id', '=', 'strands.subject_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'strands.grade_level_id')
            ->where('strands.id', $id)
            ->select('strands.*', 'subjects.name as subject_name', 'grade_levels.name as grade_name')
            ->firstOrFail();

        return Inertia::render('curriculum/ShowStrand', [
            'strand' => $strand,
        ]);
    }

    public function editStrand(int $id): Response
    {
        $strand = DB::table('strands')->where('id', $id)->firstOrFail();

        return Inertia::render('curriculum/EditStrand', [
            'strand' => $strand,
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
            'grades' => DB::table('grade_levels')->orderBy('level_order')->get(['id', 'name']),
        ]);
    }

    public function showCompetency(Competency $competency): Response
    {
        $competency->load(['indicators' => fn ($query) => $query->orderBy('display_order')]);

        return Inertia::render('curriculum/ShowCompetency', [
            'competency' => [
                'id' => $competency->id,
                'name' => $competency->name,
                'code' => $competency->code,
                'description' => $competency->description,
                'category' => $competency->category,
                'display_order' => $competency->display_order,
                'is_active' => $competency->is_active,
                'indicators' => $competency->indicators->map(fn ($indicator) => [
                    'id' => $indicator->id,
                    'indicator' => $indicator->indicator,
                    'is_active' => $indicator->is_active,
                ])->values(),
            ],
        ]);
    }

    public function editCompetency(Competency $competency): Response
    {
        return Inertia::render('curriculum/EditCompetency', [
            'competency' => [
                'id' => $competency->id,
                'name' => $competency->name,
                'code' => $competency->code,
                'description' => $competency->description,
                'category' => $competency->category,
                'display_order' => $competency->display_order,
                'is_active' => $competency->is_active,
            ],
        ]);
    }

    public function allocateSubject(Subject $subject): Response
    {
        $grades = DB::table('grade_levels')
            ->orderBy('level_order')
            ->get(['id', 'name', 'code'])
            ->map(function ($grade) use ($subject) {
                $allocation = DB::table('subject_grade_levels')
                    ->where('subject_id', $subject->id)
                    ->where('grade_level_id', $grade->id)
                    ->first();

                return [
                    'id' => $grade->id,
                    'name' => $grade->name,
                    'code' => $grade->code,
                    'is_allocated' => (bool) $allocation,
                    'lessons_per_week' => $allocation?->lessons_per_week ?? 1,
                    'minutes_per_lesson' => $allocation?->minutes_per_lesson ?? 35,
                    'is_compulsory' => (bool) ($allocation?->is_compulsory ?? true),
                    'is_active' => (bool) ($allocation?->is_active ?? true),
                ];
            })
            ->values();

        return Inertia::render('curriculum/AllocateSubject', [
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'subject_type' => $subject->subject_type,
            ],
            'grades' => $grades,
        ]);
    }

    public function saveSubjectAllocations(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'allocations' => ['required', 'array'],
            'allocations.*.grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'allocations.*.selected' => ['required', 'boolean'],
            'allocations.*.lessons_per_week' => ['required', 'integer', 'min:1'],
            'allocations.*.minutes_per_lesson' => ['required', 'integer', 'min:1'],
            'allocations.*.is_compulsory' => ['required', 'boolean'],
            'allocations.*.is_active' => ['required', 'boolean'],
        ]);

        foreach ($validated['allocations'] as $allocation) {
            if ($allocation['selected']) {
                DB::table('subject_grade_levels')->updateOrInsert(
                    [
                        'subject_id' => $subject->id,
                        'grade_level_id' => $allocation['grade_level_id'],
                    ],
                    [
                        'lessons_per_week' => $allocation['lessons_per_week'],
                        'minutes_per_lesson' => $allocation['minutes_per_lesson'],
                        'is_compulsory' => $allocation['is_compulsory'],
                        'is_active' => $allocation['is_active'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            } else {
                DB::table('subject_grade_levels')
                    ->where('subject_id', $subject->id)
                    ->where('grade_level_id', $allocation['grade_level_id'])
                    ->delete();
            }
        }

        return redirect()->route('curriculum.subjects.show', $subject)->with('success', 'Subject grade allocations updated successfully.');
    }
}
