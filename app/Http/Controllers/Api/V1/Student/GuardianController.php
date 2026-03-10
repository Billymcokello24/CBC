<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\GuardianResource;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class GuardianController extends ApiController
{
    /**
     * Display a listing of guardians.
     */
    public function index(Request $request): JsonResponse
    {
        $guardians = QueryBuilder::for(Guardian::class)
            ->allowedFilters([
                AllowedFilter::exact('school_id'),
                AllowedFilter::exact('relationship_type'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('phone'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'created_at'])
            ->allowedIncludes(['school', 'students'])
            ->paginate($request->get('per_page', 15));

        return $this->paginated($guardians);
    }

    /**
     * Store a newly created guardian.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'work_address' => 'nullable|string',
            'home_address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'relationship_type' => 'nullable|in:father,mother,guardian,other',
        ]);

        $guardian = Guardian::create(array_merge($validated, ['is_active' => true]));

        return $this->created(
            new GuardianResource($guardian),
            'Guardian created successfully'
        );
    }

    /**
     * Display the specified guardian.
     */
    public function show(Guardian $guardian): JsonResponse
    {
        $guardian->load(['school', 'students']);

        return $this->success(new GuardianResource($guardian));
    }

    /**
     * Update the specified guardian.
     */
    public function update(Request $request, Guardian $guardian): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'work_address' => 'nullable|string',
            'home_address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'relationship_type' => 'nullable|in:father,mother,guardian,other',
            'is_active' => 'boolean',
        ]);

        $guardian->update($validated);

        return $this->success(
            new GuardianResource($guardian->fresh()),
            'Guardian updated successfully'
        );
    }

    /**
     * Remove the specified guardian.
     */
    public function destroy(Guardian $guardian): JsonResponse
    {
        $guardian->delete();

        return $this->success(null, 'Guardian deleted successfully');
    }

    /**
     * Get guardian's children/students.
     */
    public function students(Guardian $guardian): JsonResponse
    {
        $students = $guardian->students()
            ->with(['currentClass.gradeLevel', 'currentClass.stream'])
            ->get();

        return $this->success($students);
    }
}
