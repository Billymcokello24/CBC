<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudentsController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $classId = $request->integer('class_id');
        $gender = (string) $request->string('gender');
        $boardingStatus = (string) $request->string('boarding_status');
        $county = trim((string) $request->string('county'));
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);

        $query = Student::query()
            ->select('students.*')
            ->with(['currentClass:id,name,code'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId > 0, fn ($q) => $q->where('current_class_id', $classId))
            ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
            ->when($boardingStatus !== '' && $boardingStatus !== 'all', fn ($q) => $q->where('boarding_status', $boardingStatus))
            ->when($county !== '', fn ($q) => $q->where('county', $county))
            ->orderBy('first_name')
            ->orderBy('last_name');

        $students = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Student $student) => $this->transformStudentRow($student));

        $statsBase = Student::query();
        $totalStudents = (clone $statsBase)->count();
        $activeStudents = (clone $statsBase)->where('status', 'active')->count();
        $boys = (clone $statsBase)->where('gender', 'male')->count();
        $girls = (clone $statsBase)->where('gender', 'female')->count();
        $newThisTerm = (clone $statsBase)->whereDate('admission_date', '>=', now()->subMonths(3))->count();
        $previousTerm = (clone $statsBase)->whereDate('admission_date', '<', now()->subMonths(3))->count();
        $growth = $previousTerm > 0 ? round(($newThisTerm / $previousTerm) * 100, 1) : 0.0;

        return Inertia::render('students/Index', [
            'students' => $students,
            'stats' => [
                'total' => $totalStudents,
                'active' => $activeStudents,
                'boys' => $boys,
                'girls' => $girls,
                'growth' => $growth,
            ],
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'class_id' => $classId ?: null,
                'gender' => $gender === '' ? 'all' : $gender,
                'boarding_status' => $boardingStatus === '' ? 'all' : $boardingStatus,
                'county' => $county,
                'per_page' => $perPage,
                'show_filters' => filter_var($request->input('show_filters', true), FILTER_VALIDATE_BOOLEAN),
            ],
            'classes' => DB::table('classes')->select('id', 'name')->orderBy('name')->get(),
            'counties' => Student::query()->whereNotNull('county')->distinct()->orderBy('county')->pluck('county'),
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'graduated', 'label' => 'Graduated'],
                ['value' => 'transferred', 'label' => 'Transferred'],
                ['value' => 'withdrawn', 'label' => 'Withdrawn'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
            'genderOptions' => [
                ['value' => 'all', 'label' => 'All Genders'],
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'boardingOptions' => [
                ['value' => 'all', 'label' => 'All Boarding Types'],
                ['value' => 'day', 'label' => 'Day'],
                ['value' => 'boarding', 'label' => 'Boarding'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('students/Create', [
            'classes' => DB::table('classes')->select('id', 'name')->orderBy('name')->get(),
            'counties' => Student::query()->whereNotNull('county')->distinct()->orderBy('county')->pluck('county'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'admission_number' => ['required', 'string', 'max:255', Rule::unique('students', 'admission_number')],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'county' => ['nullable', 'string', 'max:255'],
            'boarding_status' => ['required', Rule::in(['day', 'boarding'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'suspended'])],
        ]);

        $schoolId = DB::table('schools')->value('id');

        $student = Student::create([
            'school_id' => $schoolId,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'admission_number' => $validated['admission_number'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'admission_date' => now()->toDateString(),
            'admission_class_id' => $validated['class_id'] ?? null,
            'current_class_id' => $validated['class_id'] ?? null,
            'county' => $validated['county'] ?? null,
            'boarding_status' => $validated['boarding_status'],
            'status' => $validated['status'],
            'nationality' => 'Kenyan',
        ]);

        return redirect()->route('students.show', $student)->with('success', 'Student added successfully.');
    }

    public function show(Student $student): Response
    {
        $student->load(['currentClass:id,name,code', 'admissionClass:id,name,code', 'guardians:id,first_name,last_name,phone,email']);

        return Inertia::render('students/Show', [
            'student' => [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'upi' => $student->upi,
                'name' => $student->full_name,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'gender' => ucfirst($student->gender),
                'date_of_birth' => optional($student->date_of_birth)?->format('Y-m-d'),
                'age' => $student->age,
                'class' => $student->currentClass?->name,
                'status' => $student->status,
                'boarding_status' => $student->boarding_status,
                'county' => $student->county,
                'admission_date' => optional($student->admission_date)?->format('Y-m-d'),
                'blood_group' => $student->blood_group,
                'religion' => $student->religion,
                'nationality' => $student->nationality,
                'photo' => $student->photo,
                'guardians' => $student->guardians->map(fn ($guardian) => [
                    'id' => $guardian->id,
                    'name' => trim($guardian->first_name . ' ' . $guardian->last_name),
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                ])->values(),
            ],
        ]);
    }

    public function edit(Student $student): Response
    {
        return Inertia::render('students/Edit', [
            'student' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'date_of_birth' => optional($student->date_of_birth)?->format('Y-m-d'),
                'class_id' => $student->current_class_id,
                'county' => $student->county,
                'boarding_status' => $student->boarding_status,
                'status' => $student->status,
            ],
            'classes' => DB::table('classes')->select('id', 'name')->orderBy('name')->get(),
            'counties' => Student::query()->whereNotNull('county')->distinct()->orderBy('county')->pluck('county'),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'admission_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'admission_number')->ignore($student->id)->where(fn ($q) => $q->where('school_id', $student->school_id)),
            ],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'county' => ['nullable', 'string', 'max:255'],
            'boarding_status' => ['required', Rule::in(['day', 'boarding'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'suspended'])],
        ]);

        $student->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'admission_number' => $validated['admission_number'] ?? null,
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'current_class_id' => $validated['class_id'] ?? null,
            'county' => $validated['county'] ?? null,
            'boarding_status' => $validated['boarding_status'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function suspend(Student $student): RedirectResponse
    {
        $student->update(['status' => 'suspended']);

        return back()->with('success', 'Student suspended successfully.');
    }

    public function activate(Student $student): RedirectResponse
    {
        $student->update(['status' => 'active']);

        return back()->with('success', 'Student activated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function promote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $studentIds = collect($validated['student_ids'])->unique()->values();

        $students = DB::table('students')
            ->join('classes', 'classes.id', '=', 'students.current_class_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
            ->leftJoin('streams', 'streams.id', '=', 'classes.stream_id')
            ->whereIn('students.id', $studentIds)
            ->where('students.status', 'active')
            ->select(
                'students.id as student_id',
                'students.school_id',
                'students.current_class_id',
                'classes.grade_level_id',
                'classes.stream_id',
                'classes.academic_year_id',
                'grade_levels.level_order',
                'streams.code as stream_code'
            )
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No active students selected for promotion.');
        }

        $promoted = 0;
        $skipped = 0;
        $currentTermId = DB::table('academic_terms')->where('is_current', true)->value('id');
        $actorId = auth()->id();

        DB::transaction(function () use ($students, $currentTermId, $actorId, &$promoted, &$skipped) {
            foreach ($students as $student) {
                $nextGrade = DB::table('grade_levels')
                    ->where('school_id', $student->school_id)
                    ->where('level_order', $student->level_order + 1)
                    ->first();

                if (!$nextGrade) {
                    $skipped++;
                    continue;
                }

                $nextClassQuery = DB::table('classes')
                    ->where('school_id', $student->school_id)
                    ->where('academic_year_id', $student->academic_year_id)
                    ->where('grade_level_id', $nextGrade->id);

                if ($student->stream_id) {
                    $nextClassQuery->where('stream_id', $student->stream_id);
                }

                $nextClass = $nextClassQuery->first();

                if (!$nextClass) {
                    $skipped++;
                    continue;
                }

                DB::table('student_enrollments')
                    ->where('student_id', $student->student_id)
                    ->where('academic_year_id', $student->academic_year_id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'promoted',
                        'end_date' => now()->toDateString(),
                        'updated_at' => now(),
                    ]);

                DB::table('students')
                    ->where('id', $student->student_id)
                    ->update([
                        'current_class_id' => $nextClass->id,
                        'updated_at' => now(),
                    ]);

                $exists = DB::table('student_enrollments')
                    ->where('student_id', $student->student_id)
                    ->where('class_id', $nextClass->id)
                    ->where('academic_year_id', $student->academic_year_id)
                    ->exists();

                if (!$exists) {
                    DB::table('student_enrollments')->insert([
                        'student_id' => $student->student_id,
                        'class_id' => $nextClass->id,
                        'academic_year_id' => $student->academic_year_id,
                        'academic_term_id' => $currentTermId,
                        'enrollment_date' => now()->toDateString(),
                        'enrollment_type' => 'promoted',
                        'status' => 'active',
                        'enrolled_by' => $actorId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $promoted++;
            }
        });

        if ($promoted === 0) {
            return back()->with('error', 'Selected students could not be promoted. Check if next classes exist.');
        }

        $message = "Promoted {$promoted} student" . ($promoted === 1 ? '' : 's') . '.';
        if ($skipped > 0) {
            $message .= " {$skipped} skipped because no matching next class was found.";
        }

        return back()->with('success', $message);
    }

    public function demote(Student $student): RedirectResponse
    {
        if ($student->status !== 'active' || !$student->current_class_id) {
            return back()->with('error', 'Only active students with a current class can be demoted.');
        }

        $current = DB::table('classes')
            ->join('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
            ->where('classes.id', $student->current_class_id)
            ->select('classes.id as class_id', 'classes.school_id', 'classes.stream_id', 'classes.academic_year_id', 'grade_levels.level_order')
            ->first();

        if (!$current) {
            return back()->with('error', 'Current class record could not be found.');
        }

        $previousGrade = DB::table('grade_levels')
            ->where('school_id', $current->school_id)
            ->where('level_order', $current->level_order - 1)
            ->first();

        if (!$previousGrade) {
            return back()->with('error', 'No previous grade exists for this student.');
        }

        $previousClassQuery = DB::table('classes')
            ->where('school_id', $current->school_id)
            ->where('academic_year_id', $current->academic_year_id)
            ->where('grade_level_id', $previousGrade->id);

        if ($current->stream_id) {
            $previousClassQuery->where('stream_id', $current->stream_id);
        }

        $previousClass = $previousClassQuery->first();

        if (!$previousClass) {
            return back()->with('error', 'No matching previous class was found for this student.');
        }

        DB::transaction(function () use ($student, $current, $previousClass) {
            DB::table('student_enrollments')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $current->academic_year_id)
                ->where('status', 'active')
                ->update([
                    'status' => 'repeated',
                    'end_date' => now()->toDateString(),
                    'updated_at' => now(),
                ]);

            $student->update([
                'current_class_id' => $previousClass->id,
            ]);

            $exists = DB::table('student_enrollments')
                ->where('student_id', $student->id)
                ->where('class_id', $previousClass->id)
                ->where('academic_year_id', $current->academic_year_id)
                ->exists();

            if (!$exists) {
                DB::table('student_enrollments')->insert([
                    'student_id' => $student->id,
                    'class_id' => $previousClass->id,
                    'academic_year_id' => $current->academic_year_id,
                    'academic_term_id' => DB::table('academic_terms')->where('is_current', true)->value('id'),
                    'enrollment_date' => now()->toDateString(),
                    'enrollment_type' => 'repeated',
                    'status' => 'active',
                    'enrolled_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Student demoted successfully.');
    }

    public function transfer(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'target_class_id' => ['required', 'integer', 'exists:classes,id'],
        ]);

        if (!$student->current_class_id) {
            return back()->with('error', 'Student has no current class to transfer from.');
        }

        $currentClass = DB::table('classes')->where('id', $student->current_class_id)->first();
        $targetClass = DB::table('classes')->where('id', $validated['target_class_id'])->first();

        if (!$currentClass || !$targetClass) {
            return back()->with('error', 'Current or target class could not be found.');
        }

        if ((int) $currentClass->grade_level_id !== (int) $targetClass->grade_level_id) {
            return back()->with('error', 'Transfers are only allowed between classes of the same grade level.');
        }

        DB::transaction(function () use ($student, $currentClass, $targetClass) {
            DB::table('student_enrollments')
                ->where('student_id', $student->id)
                ->where('class_id', $currentClass->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'transferred',
                    'end_date' => now()->toDateString(),
                    'updated_at' => now(),
                ]);

            $student->update([
                'current_class_id' => $targetClass->id,
            ]);

            DB::table('student_enrollments')->insert([
                'student_id' => $student->id,
                'class_id' => $targetClass->id,
                'academic_year_id' => $targetClass->academic_year_id,
                'academic_term_id' => DB::table('academic_terms')->where('is_current', true)->value('id'),
                'enrollment_date' => now()->toDateString(),
                'enrollment_type' => 'transferred_in',
                'status' => 'active',
                'enrolled_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Student transferred successfully.');
    }

    private function transformStudentRow(Student $student): array
    {
        return [
            'id' => $student->id,
            'admission_number' => $student->admission_number,
            'name' => $student->full_name,
            'gender' => ucfirst($student->gender),
            'class' => $student->currentClass?->name,
            'class_id' => $student->current_class_id,
            'status' => $student->status,
            'photo' => $student->photo,
            'age' => $student->age,
            'admission_date' => optional($student->admission_date)?->format('Y-m-d'),
        ];
    }
}
