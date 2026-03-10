<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StudentController extends ApiController
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request): JsonResponse
    {
        $students = QueryBuilder::for(Student::class)
            ->allowedFilters([
                AllowedFilter::exact('school_id'),
                AllowedFilter::exact('current_class_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('gender'),
                AllowedFilter::exact('boarding_status'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('admission_number'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'admission_number', 'admission_date', 'created_at'])
            ->allowedIncludes(['school', 'currentClass', 'guardians'])
            ->paginate($request->get('per_page', 15));

        return $this->paginated($students);
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'admission_number' => 'required|string|unique:students,admission_number',
            'upi' => 'nullable|string|unique:students,upi',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'birth_certificate_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'home_address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'admission_date' => 'required|date',
            'admission_class_id' => 'nullable|exists:classes,id',
            'current_class_id' => 'nullable|exists:classes,id',
            'blood_group' => 'nullable|string|max:10',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'has_special_needs' => 'boolean',
            'special_needs_details' => 'nullable|string',
            'boarding_status' => 'nullable|in:day,boarding',
            'guardians' => 'nullable|array',
            'guardians.*.guardian_id' => 'required_with:guardians|exists:guardians,id',
            'guardians.*.relationship' => 'required_with:guardians|string',
            'guardians.*.is_primary_contact' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $student = Student::create(array_merge($validated, ['status' => 'active']));

            // Attach guardians if provided
            if (!empty($validated['guardians'])) {
                foreach ($validated['guardians'] as $guardian) {
                    $student->guardians()->attach($guardian['guardian_id'], [
                        'relationship' => $guardian['relationship'],
                        'is_primary_contact' => $guardian['is_primary_contact'] ?? false,
                        'is_emergency_contact' => true,
                        'can_pickup' => true,
                        'receives_reports' => true,
                        'receives_fees_notification' => true,
                    ]);
                }
            }

            DB::commit();

            return $this->created(
                new StudentResource($student->load(['school', 'currentClass', 'guardians'])),
                'Student created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create student: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): JsonResponse
    {
        $student->load([
            'school',
            'currentClass.gradeLevel',
            'currentClass.stream',
            'admissionClass',
            'guardians',
            'enrollments' => fn($q) => $q->latest()->take(5),
            'documents',
        ]);

        return $this->success(new StudentResource($student));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'admission_number' => 'sometimes|string|unique:students,admission_number,' . $student->id,
            'upi' => 'nullable|string|unique:students,upi,' . $student->id,
            'first_name' => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'gender' => 'sometimes|in:male,female,other',
            'date_of_birth' => 'sometimes|date|before:today',
            'home_address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'current_class_id' => 'nullable|exists:classes,id',
            'blood_group' => 'nullable|string|max:10',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'has_special_needs' => 'boolean',
            'special_needs_details' => 'nullable|string',
            'boarding_status' => 'nullable|in:day,boarding',
        ]);

        $student->update($validated);

        return $this->success(
            new StudentResource($student->fresh(['school', 'currentClass', 'guardians'])),
            'Student updated successfully'
        );
    }

    /**
     * Update student status.
     */
    public function updateStatus(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,graduated,transferred,withdrawn,suspended',
            'reason' => 'nullable|string',
            'effective_date' => 'nullable|date',
        ]);

        $previousStatus = $student->status;

        $student->update(['status' => $validated['status']]);

        // Log status change
        StudentStatusHistory::create([
            'student_id' => $student->id,
            'previous_status' => $previousStatus,
            'new_status' => $validated['status'],
            'reason' => $validated['reason'] ?? null,
            'effective_date' => $validated['effective_date'] ?? now(),
            'changed_by' => auth()->id(),
        ]);

        return $this->success(
            new StudentResource($student->fresh()),
            'Student status updated successfully'
        );
    }

    /**
     * Get student's attendance summary.
     */
    public function attendanceSummary(Student $student, Request $request): JsonResponse
    {
        $termId = $request->get('term_id');

        $summary = DB::table('attendance_summaries')
            ->where('student_id', $student->id)
            ->when($termId, fn($q) => $q->where('academic_term_id', $termId))
            ->first();

        return $this->success($summary);
    }

    /**
     * Get student's academic performance.
     */
    public function academicPerformance(Student $student, Request $request): JsonResponse
    {
        $termId = $request->get('term_id');

        $assessments = DB::table('student_assessments')
            ->join('assessments', 'student_assessments.assessment_id', '=', 'assessments.id')
            ->join('subjects', 'assessments.subject_id', '=', 'subjects.id')
            ->where('student_assessments.student_id', $student->id)
            ->when($termId, fn($q) => $q->where('assessments.academic_term_id', $termId))
            ->select([
                'subjects.name as subject',
                'assessments.title',
                'assessments.assessment_date',
                'student_assessments.marks_obtained',
                'student_assessments.percentage',
                'student_assessments.grade_level',
                'student_assessments.feedback',
            ])
            ->orderBy('assessments.assessment_date', 'desc')
            ->get();

        return $this->success($assessments);
    }

    /**
     * Remove the specified student (soft delete).
     */
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return $this->success(null, 'Student deleted successfully');
    }
}
