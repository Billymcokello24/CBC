<?php

namespace App\Http\Controllers\Api\V1\School;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SchoolController extends ApiController
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request): JsonResponse
    {
        $schools = QueryBuilder::for(School::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('school_type_id'),
                AllowedFilter::exact('school_level_id'),
                AllowedFilter::exact('county'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['schoolType', 'schoolLevel', 'branches'])
            ->paginate($request->get('per_page', 15));

        return $this->paginated($schools);
    }

    /**
     * Store a newly created school.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code',
            'registration_number' => 'nullable|string|unique:schools,registration_number',
            'school_type_id' => 'required|exists:school_types,id',
            'school_level_id' => 'required|exists:school_levels,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'gender_type' => 'nullable|in:mixed,boys,girls',
            'boarding_type' => 'nullable|in:day,boarding,day_and_boarding',
        ]);

        $validated['status'] = 'pending_approval';

        $school = School::create($validated);

        return $this->created(
            new SchoolResource($school->load(['schoolType', 'schoolLevel'])),
            'School created successfully'
        );
    }

    /**
     * Display the specified school.
     */
    public function show(School $school): JsonResponse
    {
        $school->load([
            'schoolType',
            'schoolLevel',
            'branches',
            'contacts',
            'academicYears' => fn($q) => $q->latest()->take(5),
        ]);

        return $this->success(new SchoolResource($school));
    }

    /**
     * Update the specified school.
     */
    public function update(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:schools,code,' . $school->id,
            'registration_number' => 'nullable|string|unique:schools,registration_number,' . $school->id,
            'school_type_id' => 'sometimes|exists:school_types,id',
            'school_level_id' => 'sometimes|exists:school_levels,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'gender_type' => 'nullable|in:mixed,boys,girls',
            'boarding_type' => 'nullable|in:day,boarding,day_and_boarding',
            'status' => 'sometimes|in:active,inactive,suspended,pending_approval',
            'motto' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
        ]);

        $school->update($validated);

        return $this->success(
            new SchoolResource($school->fresh(['schoolType', 'schoolLevel'])),
            'School updated successfully'
        );
    }

    /**
     * Remove the specified school.
     */
    public function destroy(School $school): JsonResponse
    {
        $school->delete();

        return $this->success(null, 'School deleted successfully');
    }

    /**
     * Get school statistics.
     */
    public function statistics(School $school): JsonResponse
    {
        $stats = [
            'students' => [
                'total' => $school->students()->count(),
                'active' => $school->students()->where('status', 'active')->count(),
                'by_gender' => $school->students()->where('status', 'active')
                    ->selectRaw('gender, COUNT(*) as count')
                    ->groupBy('gender')
                    ->pluck('count', 'gender'),
            ],
            'teachers' => [
                'total' => $school->teachers()->count(),
                'active' => $school->teachers()->where('status', 'active')->count(),
            ],
            'classes' => [
                'total' => $school->classes()->count(),
                'active' => $school->classes()->where('is_active', true)->count(),
            ],
            'academic_year' => $school->currentAcademicYear()?->only(['id', 'name', 'code']),
            'current_term' => $school->currentTerm()?->only(['id', 'name', 'term_number']),
        ];

        return $this->success($stats);
    }

    /**
     * Get school settings.
     */
    public function settings(School $school): JsonResponse
    {
        $settings = $school->settings()
            ->where('is_public', true)
            ->get()
            ->groupBy('group')
            ->map(fn($group) => $group->pluck('value', 'key'));

        return $this->success($settings);
    }

    /**
     * Update school setting.
     */
    public function updateSetting(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'type' => 'nullable|in:string,boolean,integer,json,array',
            'group' => 'nullable|string',
        ]);

        $school->setSetting(
            $validated['key'],
            $validated['value'],
            $validated['type'] ?? 'string',
            $validated['group'] ?? 'general'
        );

        return $this->success(null, 'Setting updated successfully');
    }
}
