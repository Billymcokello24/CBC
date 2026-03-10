<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TeacherController extends ApiController
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): JsonResponse
    {
        $teachers = QueryBuilder::for(Teacher::class)
            ->allowedFilters([
                AllowedFilter::exact('school_id'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('gender'),
                AllowedFilter::exact('employment_type'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('staff_number'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'staff_number', 'date_joined', 'created_at'])
            ->allowedIncludes(['school', 'department', 'staffCategory', 'staffDesignation'])
            ->paginate($request->get('per_page', 15));

        return $this->paginated($teachers);
    }

    /**
     * Store a newly created teacher.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'department_id' => 'nullable|exists:departments,id',
            'staff_category_id' => 'nullable|exists:staff_categories,id',
            'staff_designation_id' => 'nullable|exists:staff_designations,id',
            'staff_number' => 'required|string|unique:teachers,staff_number',
            'tsc_number' => 'nullable|string|unique:teachers,tsc_number',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'id_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'date_joined' => 'required|date',
            'contract_type' => 'nullable|string|max:50',
            'employment_type' => 'nullable|in:full_time,part_time',
        ]);

        try {
            DB::beginTransaction();

            $teacher = Teacher::create(array_merge($validated, ['status' => 'active']));

            DB::commit();

            return $this->created(
                new TeacherResource($teacher->load(['school', 'department'])),
                'Teacher created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create teacher: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load([
            'school',
            'department',
            'staffCategory',
            'staffDesignation',
            'qualifications',
            'certifications',
            'subjectAssignments' => fn($q) => $q->with(['subject', 'class']),
        ]);

        return $this->success(new TeacherResource($teacher));
    }

    /**
     * Update the specified teacher.
     */
    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'staff_category_id' => 'nullable|exists:staff_categories,id',
            'staff_designation_id' => 'nullable|exists:staff_designations,id',
            'staff_number' => 'sometimes|string|unique:teachers,staff_number,' . $teacher->id,
            'tsc_number' => 'nullable|string|unique:teachers,tsc_number,' . $teacher->id,
            'first_name' => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string|max:20',
            'address' => 'nullable|string',
            'contract_type' => 'nullable|string|max:50',
            'employment_type' => 'nullable|in:full_time,part_time',
            'status' => 'sometimes|in:active,inactive,on_leave,terminated,resigned,retired',
        ]);

        $teacher->update($validated);

        return $this->success(
            new TeacherResource($teacher->fresh(['school', 'department'])),
            'Teacher updated successfully'
        );
    }

    /**
     * Remove the specified teacher (soft delete).
     */
    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return $this->success(null, 'Teacher deleted successfully');
    }

    /**
     * Get teacher's subject assignments.
     */
    public function subjects(Teacher $teacher): JsonResponse
    {
        $subjects = $teacher->subjectAssignments()
            ->with(['subject', 'class.gradeLevel', 'class.stream'])
            ->get();

        return $this->success($subjects);
    }

    /**
     * Get teacher's timetable.
     */
    public function timetable(Teacher $teacher, Request $request): JsonResponse
    {
        $timetable = DB::table('timetable_slots')
            ->join('timetables', 'timetable_slots.timetable_id', '=', 'timetables.id')
            ->join('subjects', 'timetable_slots.subject_id', '=', 'subjects.id')
            ->join('classes', 'timetables.class_id', '=', 'classes.id')
            ->where('timetable_slots.teacher_id', $teacher->id)
            ->where('timetables.status', 'active')
            ->select([
                'timetable_slots.*',
                'subjects.name as subject_name',
                'classes.name as class_name',
            ])
            ->orderBy('timetable_slots.day_of_week')
            ->orderBy('timetable_slots.period_number')
            ->get();

        return $this->success($timetable);
    }
}
