<?php

namespace App\Http\Controllers;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\UserCreatedMail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 500);

        $user = auth()->user();
        $query = Student::query()
            ->select('students.*')
            ->with(['currentClass:id,name,code']);

        // Scope to teacher's classes if not a super admin
        if ($user && $user->hasRole('teacher') && !$user->hasRole('super_admin')) {
            $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
            $myClassIds = DB::table('teacher_subjects')->where('teacher_id', $teacher?->id)->pluck('class_id')
                ->merge(DB::table('classes')->where('class_teacher_id', $user->id)->pluck('id'))
                ->unique()
                ->toArray();
            $query->whereIn('current_class_id', $myClassIds);
        }

        $query->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId > 0, fn ($q) => $q->where('current_class_id', $classId))
            ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
            ->when($boardingStatus !== '' && $boardingStatus !== 'all', fn ($q) => $q->where('boarding_status', $boardingStatus))
            ->when($county !== '', fn ($q) => $q->where('county', $county))
            ->orderBy('first_name')
            ->orderBy('last_name');

        $learners = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Student $student) => $this->transformLearnerRow($student));

        $statsBase = Student::query();
        $totalLearners = (clone $statsBase)->count();
        $activeLearners = (clone $statsBase)->where('status', 'active')->count();
        $boys = (clone $statsBase)->where('gender', 'male')->count();
        $girls = (clone $statsBase)->where('gender', 'female')->count();
        $newThisTerm = (clone $statsBase)->whereDate('admission_date', '>=', now()->subMonths(3))->count();
        $previousTerm = (clone $statsBase)->whereDate('admission_date', '<', now()->subMonths(3))->count();
        $growth = $previousTerm > 0 ? round(($newThisTerm / $previousTerm) * 100, 1) : 0.0;

        return Inertia::render('students/Index', [
            'learners' => $learners,
            'stats' => [
                'total' => $totalLearners,
                'active' => $activeLearners,
                'withdrawn' => (clone $statsBase)->whereIn('status', ['withdrawn', 'inactive', 'transferred'])->count(),
                'new_this_month' => $newThisTerm,
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
            'classes' => SchoolClass::query()->select('id', 'name')->orderBy('name')->get(),
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
        $grades = GradeLevel::query()
            ->select('id', 'name', 'code', 'level_order')
            ->orderBy('level_order')
            ->get();

        $classes = SchoolClass::query()
            ->leftJoin('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
            ->leftJoin('streams', 'streams.id', '=', 'classes.stream_id')
            ->select(
                'classes.id',
                'classes.name',
                'classes.grade_level_id',
                'grade_levels.name as grade_name',
                'streams.name as stream_name'
            )
            ->orderBy('grade_levels.level_order')
            ->orderBy('classes.name')
            ->get();

        return Inertia::render('students/Create', [
            'grades' => $grades,
            'classes' => $classes,
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
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $schoolId = auth()->user()->school_id;

        $student = DB::transaction(function () use ($validated, $schoolId, $request) {
            $studentData = [
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
            ];

            if ($request->hasFile('photo')) {
                $studentData['photo'] = $request->file('photo')->store('students/photos', 'public');
            }

            $s = Student::create($studentData);

            // Create initial enrollment if class is provided
            if ($validated['class_id']) {
                $activeYear = \App\Models\Academic\AcademicYear::where('status', 'active')->first();
                $activeTerm = \App\Models\Academic\AcademicTerm::where('status', 'active')->first();
                
                if ($activeYear) {
                    \App\Models\StudentEnrollment::create([
                        'school_id' => $schoolId,
                        'student_id' => $s->id,
                        'class_id' => $validated['class_id'],
                        'academic_year_id' => $activeYear->id,
                        'academic_term_id' => $activeTerm?->id,
                        'admission_number' => $validated['admission_number'],
                        'enrollment_date' => now()->toDateString(),
                        'enrollment_type' => 'new',
                        'status' => 'active',
                        'boarding_status' => $validated['boarding_status'],
                        'enrolled_by' => auth()->id(),
                    ]);
                }
            }

            return $s;
        });

        return redirect()->route('students.show', $student)->with('success', 'Learner added successfully.');
    }

    public function show(Student $student): Response
    {
        if (auth()->user()?->hasRole('parent')) {
            $guardian = auth()->user()->guardian;
            abort_unless($guardian && $guardian->students()->whereKey($student->id)->exists(), 403);
        }

        $student->load(['currentClass:id,name,code', 'admissionClass:id,name,code', 'guardians:id,user_id,first_name,last_name,phone,email']);

        // Define available counties (Kenya)
        $counties = [
            'Mombasa', 'Kwale', 'Kilifi', 'Tana River', 'Lamu', 'Taita Taveta', 'Garissa', 'Wajir', 'Mandera', 'Marsabit',
            'Isiolo', 'Meru', 'Tharaka-Nithi', 'Embu', 'Kitui', 'Machakos', 'Makueni', 'Nyandarua', 'Nyeri', 'Kirinyaga',
            'Murang\'a', 'Kiambu', 'Turkana', 'West Pokot', 'Samburu', 'Trans Nzoia', 'Uasin Gishu', 'Elgeyo Marakwet',
            'Nandi', 'Baringo', 'Laikipia', 'Nakuru', 'Narok', 'Kajiado', 'Kericho', 'Bomet', 'Kakamega', 'Vihiga',
            'Bungoma', 'Busia', 'Siaya', 'Kisumu', 'Homa Bay', 'Migori', 'Kisii', 'Nyamira', 'Nairobi'
        ];

        $religions = ['Christian', 'Muslim', 'Hindu', 'Other', 'None'];
        $languages = ['English', 'Kiswahili', 'French', 'German', 'Arabic', 'Chinese', 'Other'];

        return Inertia::render('students/Show', [
            'learner' => [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'upi' => $student->upi,
                'name' => $student->full_name,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'gender' => $student->gender,
                'date_of_birth' => optional($student->date_of_birth)->format('Y-m-d'),
                'birth_certificate_number' => $student->birth_certificate_number,
                'nationality' => $student->nationality,
                'religion' => $student->religion,
                'home_address' => $student->home_address,
                'county' => $student->county,
                'sub_county' => $student->sub_county,
                'ward' => $student->ward,
                'photo' => $student->photo,
                'photo_url' => $student->photo_url,
                'admission_date' => optional($student->admission_date)->format('Y-m-d'),
                'admission_class_id' => $student->admission_class_id,
                'current_class_id' => $student->current_class_id,
                'boarding_status' => $student->boarding_status,
                'hostel_room' => $student->hostel_room,
                'transport_route' => $student->transport_route,
                'pickup_point' => $student->pickup_point,
                'blood_group' => $student->blood_group,
                'medical_conditions' => $student->medical_conditions,
                'allergies' => $student->allergies,
                'has_special_needs' => (bool) $student->has_special_needs,
                'special_needs_details' => $student->special_needs_details,
                'primary_language' => $student->primary_language,
                'secondary_language' => $student->secondary_language,
                'status' => $student->status,
                'graduation_date' => optional($student->graduation_date)->format('Y-m-d'),
                'withdrawal_date' => optional($student->withdrawal_date)->format('Y-m-d'),
                'withdrawal_reason' => $student->withdrawal_reason,
                'age' => $student->age,
                'class_name' => $student->currentClass?->name,
                'grade_id' => DB::table('classes')->where('id', $student->current_class_id)->value('grade_level_id'),
                'guardians' => $student->guardians->map(fn ($guardian) => [
                    'id' => $guardian->id,
                    'name' => trim($guardian->first_name . ' ' . $guardian->last_name),
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                    'has_login' => (bool) $guardian->user_id,
                ])->values(),
            ],
            'grades' => GradeLevel::query()->select('id', 'name', 'code', 'level_order')->orderBy('level_order')->get(),
            'classes' => SchoolClass::query()
                ->leftJoin('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
                ->leftJoin('streams', 'streams.id', '=', 'classes.stream_id')
                ->select('classes.id', 'classes.name', 'classes.grade_level_id', 'grade_levels.name as grade_name', 'streams.name as stream_name')
                ->orderBy('grade_levels.level_order')
                ->orderBy('classes.name')
                ->get(),
            'counties' => $counties,
            'religions' => $religions,
            'languages' => $languages,
        ]);
    }

    public function edit(Student $student): Response
    {
        $student->load(['guardians.user']);
        $linkedGuardian = $student->guardians->firstWhere('user_id', '!=', null);

        return Inertia::render('students/Edit', [
            'learner' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'date_of_birth' => optional($student->date_of_birth)?->format('Y-m-d'),
                'grade_id' => DB::table('classes')->where('id', $student->current_class_id)->value('grade_level_id'),
                'class_id' => $student->current_class_id,
                'county' => $student->county,
                'boarding_status' => $student->boarding_status,
                'status' => $student->status,
                'guardian' => $linkedGuardian ? [
                    'name' => trim($linkedGuardian->first_name . ' ' . $linkedGuardian->last_name),
                    'email' => $linkedGuardian->email,
                    'phone' => $linkedGuardian->phone,
                    'has_login' => (bool) $linkedGuardian->user_id,
                ] : null,
            ],
            'grades' => GradeLevel::query()->select('id', 'name', 'code', 'level_order')->orderBy('level_order')->get(),
            'classes' => SchoolClass::query()
                ->leftJoin('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
                ->leftJoin('streams', 'streams.id', '=', 'classes.stream_id')
                ->select('classes.id', 'classes.name', 'classes.grade_level_id', 'grade_levels.name as grade_name', 'streams.name as stream_name')
                ->orderBy('grade_levels.level_order')
                ->orderBy('classes.name')
                ->get(),
            'counties' => Student::query()->whereNotNull('county')->distinct()->orderBy('county')->pluck('county'),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->load(['guardians.user']);
        $existingGuardian = $student->guardians->firstWhere('user_id', '!=', null);
        $existingGuardianUserId = $existingGuardian?->user_id;
        $existingGuardianId = $existingGuardian?->id;

        $validated = $request->validate([
            // Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'birth_certificate_number' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'primary_language' => ['nullable', 'string', 'max:255'],
            'secondary_language' => ['nullable', 'string', 'max:255'],
            
            // Academic Details
            'admission_number' => [
                'nullable', 'string', 'max:255',
                Rule::unique('students', 'admission_number')->ignore($student->id)->where(fn ($q) => $q->where('school_id', $student->school_id)),
            ],
            'upi' => ['nullable', 'string', 'max:255'],
            'admission_date' => ['nullable', 'date'],
            'admission_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'current_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'suspended'])],
            
            // Location
            'home_address' => ['nullable', 'string'],
            'county' => ['nullable', 'string', 'max:255'],
            'sub_county' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            
            // Health
            'blood_group' => ['nullable', 'string', 'max:10'],
            'medical_conditions' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'has_special_needs' => ['nullable', 'boolean'],
            'special_needs_details' => ['nullable', 'string'],
            
            // Logistics
            'boarding_status' => ['required', Rule::in(['day', 'boarding'])],
            'hostel_room' => ['nullable', 'string', 'max:255'],
            'transport_route' => ['nullable', 'string', 'max:255'],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            
            // End of Life cycle
            'graduation_date' => ['nullable', 'date'],
            'withdrawal_date' => ['nullable', 'date'],
            'withdrawal_reason' => ['nullable', 'string'],
            
            // Media
            'photo' => ['nullable', 'image', 'max:2048'],

            // Guardian (simplified for this context)
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($existingGuardianUserId),
                Rule::unique('guardians', 'email')->ignore($existingGuardianId),
            ],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $guardianProvided = filled($validated['guardian_name'] ?? null)
            || filled($validated['guardian_email'] ?? null)
            || filled($validated['guardian_phone'] ?? null)
            || filled($validated['guardian_password'] ?? null);

        DB::transaction(function () use ($student, $validated, $guardianProvided, $request) {
            $updateData = collect($validated)->except([
                'photo', 'guardian_name', 'guardian_email', 'guardian_phone', 'guardian_password', 'guardian_password_confirmation'
            ])->toArray();

            if ($request->hasFile('photo')) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $updateData['photo'] = $request->file('photo')->store('students/photos', 'public');
            }

            $student->update($updateData);

            if ($guardianProvided) {
                $this->upsertGuardianAccountForStudent($student, [
                    'name' => $validated['guardian_name'] ?? null,
                    'email' => $validated['guardian_email'] ?? null,
                    'phone' => $validated['guardian_phone'] ?? null,
                    'password' => $validated['guardian_password'] ?? null,
                ]);
            }
        });

        return redirect()->route('students.show', $student)->with('success', 'Learner profile updated successfully.');
    }

    public function suspend(Student $student): RedirectResponse
    {
        $student->update(['status' => 'suspended']);

        return back()->with('success', 'Learner suspended successfully.');
    }

    public function activate(Student $student): RedirectResponse
    {
        $student->update(['status' => 'active']);

        return back()->with('success', 'Learner activated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Learner deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learner_ids' => ['required', 'array', 'min:1'],
            'learner_ids.*' => ['integer', 'exists:students,id'],
        ]);

        Student::whereIn('id', $validated['learner_ids'])->delete();

        return redirect()->route('students.index')->with('success', count($validated['learner_ids']) . ' learners deleted successfully.');
    }

    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $classId = $request->integer('class_id');
        $gender = (string) $request->string('gender');
        $boardingStatus = (string) $request->string('boarding_status');
        $county = trim((string) $request->string('county'));

        $students = Student::query()
            ->with(['currentClass:id,name,code'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId > 0, fn ($q) => $q->where('current_class_id', $classId))
            ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
            ->when($boardingStatus !== '' && $boardingStatus !== 'all', fn ($q) => $q->where('boarding_status', $boardingStatus))
            ->when($county !== '', fn ($q) => $q->where('county', $county))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $pdf = Pdf::loadView('reports.students-pdf', compact('students'));
        
        return $pdf->download('students-list-' . now()->format('Y-m-d') . '.pdf');
    }

    public function promote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learner_ids' => ['required', 'array', 'min:1'],
            'learner_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $learnerIds = collect($validated['learner_ids'])->unique()->values();

        $students = DB::table('students')
            ->join('classes', 'classes.id', '=', 'students.current_class_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'classes.grade_level_id')
            ->leftJoin('streams', 'streams.id', '=', 'classes.stream_id')
            ->whereIn('students.id', $learnerIds)
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
            return back()->with('error', 'No active learners selected for promotion.');
        }

        $promoted = 0;
        $skipped = 0;
        $currentTermId = DB::table('academic_terms')->where('is_current', true)->value('id');
        $actorId = auth()->id();

        DB::transaction(function () use ($students, $currentTermId, $actorId, &$promoted, &$skipped) {
            foreach ($students as $student) {
                // Find next grade level
                $nextGrade = DB::table('grade_levels')
                    ->where('school_id', $student->school_id)
                    ->where('level_order', $student->level_order + 1)
                    ->first();

                if (!$nextGrade) {
                    $skipped++;
                    continue;
                }

                // Try to find if there is a next academic year
                $currentYear = DB::table('academic_years')->where('id', $student->academic_year_id)->first();
                $nextYear = null;
                if ($currentYear) {
                    $nextYear = DB::table('academic_years')
                        ->where('school_id', $student->school_id)
                        ->where('start_date', '>', $currentYear->end_date)
                        ->orderBy('start_date')
                        ->first();
                }

                $targetYearId = $nextYear ? $nextYear->id : $student->academic_year_id;

                $nextClassQuery = DB::table('classes')
                    ->where('school_id', $student->school_id)
                    ->where('academic_year_id', $targetYearId)
                    ->where('grade_level_id', $nextGrade->id);

                // Match stream if possible
                if ($student->stream_id) {
                    $nextClassQuery->where('stream_id', $student->stream_id);
                }

                $nextClass = $nextClassQuery->first();

                // If no match with stream, try to find any class in that grade/year
                if (!$nextClass && $student->stream_id) {
                    $nextClass = DB::table('classes')
                        ->where('school_id', $student->school_id)
                        ->where('academic_year_id', $targetYearId)
                        ->where('grade_level_id', $nextGrade->id)
                        ->first();
                }

                if (!$nextClass) {
                    $skipped++;
                    continue;
                }

                // Update current enrollment to 'promoted'
                DB::table('student_enrollments')
                    ->where('student_id', $student->student_id)
                    ->where('academic_year_id', $student->academic_year_id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'promoted',
                        'end_date' => now()->toDateString(),
                        'updated_at' => now(),
                    ]);

                // Update student's current class
                DB::table('students')
                    ->where('id', $student->student_id)
                    ->update([
                        'current_class_id' => $nextClass->id,
                        'updated_at' => now(),
                    ]);

                // Create new enrollment in the target year/class
                $exists = DB::table('student_enrollments')
                    ->where('student_id', $student->student_id)
                    ->where('class_id', $nextClass->id)
                    ->where('academic_year_id', $targetYearId)
                    ->exists();

                if (!$exists) {
                    // Try to find if there is a term in the next year
                    $targetTermId = $currentTermId;
                    if ($nextYear) {
                        $targetTermId = DB::table('academic_terms')
                            ->where('academic_year_id', $nextYear->id)
                            ->orderBy('start_date')
                            ->value('id') ?? $currentTermId;
                    }

                    DB::table('student_enrollments')->insert([
                        'student_id' => $student->student_id,
                        'class_id' => $nextClass->id,
                        'academic_year_id' => $targetYearId,
                        'academic_term_id' => $targetTermId,
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
            return back()->with('error', 'Selected learners could not be promoted. Check if next classes exist.');
        }

        $message = "Promoted {$promoted} learner" . ($promoted === 1 ? '' : 's') . '.';
        if ($skipped > 0) {
            $message .= " {$skipped} skipped because no matching next class was found.";
        }

        return back()->with('success', $message);
    }

    public function demote(Student $student): RedirectResponse
    {
        if ($student->status !== 'active' || !$student->current_class_id) {
            return back()->with('error', 'Only active learners with a current class can be demoted.');
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
            return back()->with('error', 'No previous grade exists for this learner.');
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
            return back()->with('error', 'No matching previous class was found for this learner.');
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

        return back()->with('success', 'Learner demoted successfully.');
    }

    public function transfer(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'target_class_id' => ['required', 'integer', 'exists:classes,id'],
        ]);

        if (!$student->current_class_id) {
            return back()->with('error', 'Learner has no current class to transfer from.');
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

        return back()->with('success', 'Learner transferred successfully.');
    }

    private function transformLearnerRow(Student $student): array
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
            'photo_url' => $student->photo_url,
            'age' => $student->age,
            'admission_date' => optional($student->admission_date)?->format('Y-m-d'),
        ];
    }

    public function storeGuardian(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email'), Rule::unique('guardians', 'email')],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'guardian_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($student, $validated) {
            $this->createGuardianAccountForStudent($student, [
                'name' => $validated['guardian_name'],
                'email' => $validated['guardian_email'],
                'phone' => $validated['guardian_phone'],
                'password' => $validated['guardian_password'],
            ]);
        });

        return back()->with('success', 'Guardian account created and linked successfully.');
    }

    protected function createGuardianAccountForStudent(Student $student, array $data): Guardian
    {
        $nameParts = preg_split('/\s+/', trim((string) $data['name'])) ?: [];
        $firstName = array_shift($nameParts) ?: 'Guardian';
        $lastName = count($nameParts) ? array_pop($nameParts) : $firstName;
        $middleName = count($nameParts) ? implode(' ', $nameParts) : null;

        $user = User::create([
            'name' => trim((string) $data['name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'locale' => config('app.locale'),
            'timezone' => config('app.timezone'),
        ]);

        if (method_exists($user, 'assignRole') && Role::query()->where('name', 'parent')->where('guard_name', 'web')->exists()) {
            $user->assignRole('parent');
        }

        $guardian = Guardian::create([
            'user_id' => $user->id,
            'school_id' => $student->school_id,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'relationship_type' => 'guardian',
            'receives_communication' => true,
            'is_active' => true,
            'can_pickup' => true,
            'is_emergency_contact' => true,
        ]);

        $student->guardians()->attach($guardian->id, [
            'relationship' => 'guardian',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'can_pickup' => true,
            'receives_reports' => true,
            'receives_fees_notification' => true,
            'is_fee_payer' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send Welcome Email
        Mail::to($user->email)->send(new UserCreatedMail($user, $data['password']));

        return $guardian;
    }

    protected function upsertGuardianAccountForStudent(Student $student, array $data): Guardian
    {
        $existingGuardian = $student->guardians()->whereNotNull('user_id')->first();

        if ($existingGuardian) {
            $user = $existingGuardian->user;
            $nameParts = preg_split('/\s+/', trim((string) ($data['name'] ?: $existingGuardian->full_name))) ?: [];
            $firstName = array_shift($nameParts) ?: $existingGuardian->first_name;
            $lastName = count($nameParts) ? array_pop($nameParts) : $existingGuardian->last_name;
            $middleName = count($nameParts) ? implode(' ', $nameParts) : null;

            $user?->update(array_filter([
                'name' => $data['name'] ?: $existingGuardian->full_name,
                'email' => $data['email'] ?: $existingGuardian->email,
                'phone' => $data['phone'] ?: $existingGuardian->phone,
                'password' => $data['password'] ? Hash::make($data['password']) : null,
            ], fn ($value) => $value !== null && $value !== ''));

            $existingGuardian->update([
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'email' => $data['email'] ?: $existingGuardian->email,
                'phone' => $data['phone'] ?: $existingGuardian->phone,
            ]);

            return Guardian::query()->findOrFail($existingGuardian->id);
        }

        return $this->createGuardianAccountForStudent($student, [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
        ]);
    }

    public function guardianDashboard(): Response
    {
        $guardian = auth()->user()?->guardian;
        abort_unless($guardian, 403);

        $guardian->load(['students.currentClass:id,name,code']);

        return Inertia::render('guardians/Portal', [
            'guardian' => [
                'id' => $guardian->id,
                'name' => $guardian->full_name,
                'email' => $guardian->email,
                'phone' => $guardian->phone,
            ],
            'learners' => $guardian->students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->currentClass?->name,
                'status' => $student->status,
            ])->values(),
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="learners_bulk_upload_template.csv"',
        ];

        $columns = [
            'first_name',
            'middle_name',
            'last_name',
            'admission_number',
            'gender',
            'date_of_birth',
            'grade_name',
            'grade_code',
            'grade_level_order',
            'grade_category',
            'stream_name',
            'stream_code',
            'class_name',
            'class_code',
            'county',
            'boarding_status',
            'status',
            'guardian_name',
            'guardian_email',
            'guardian_phone',
            'guardian_password',
        ];

        $sample = [
            'John',
            'Kamau',
            'Mwangi',
            'STU1001',
            'male',
            '2014-05-21',
            'Grade 5',
            'G5',
            '5',
            'Primary',
            'West',
            'W',
            'Grade 5 West',
            'G5-W',
            'Kiambu',
            'day',
            'active',
            'Jane Mwangi',
            'jane.mwangi@example.com',
            '+254700000001',
            'Password123',
        ];

        $callback = function () use ($columns, $sample) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);
            fputcsv($handle, $sample);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $schoolId = auth()->user()->school_id ?: session('viewing_school_id');
        $academicYearId = DB::table('academic_years')->where('is_current', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->value('id')
            ?? DB::table('academic_years')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('start_date')->value('id');

        if (!$schoolId || !$academicYearId) {
            return back()->with('error', 'School context (ID) or academic year setup is missing, so bulk upload cannot continue.');
        }

        try {
            $rows = $this->parseLearnerCsv($validated['file']->getRealPath());

            if (count($rows) === 0) {
                return back()->with('error', 'The uploaded CSV file is empty.');
            }

            $createdLearners = 0;
            $updatedLearners = 0;
            $createdGrades = 0;
            $createdStreams = 0;
            $createdClasses = 0;
            $guardianAccounts = 0;

            DB::transaction(function () use (
                $rows,
                $schoolId,
                $academicYearId,
                &$createdLearners,
                &$updatedLearners,
                &$createdGrades,
                &$createdStreams,
                &$createdClasses,
                &$guardianAccounts
            ) {
                foreach ($rows as $index => $row) {
                    $line = $index + 2;
                    $normalized = $this->normalizeLearnerImportRow($row, $line);
                    $grade = $this->firstOrCreateImportGrade($schoolId, $normalized, $createdGrades);
                    $stream = $this->firstOrCreateImportStream($schoolId, $normalized, $createdStreams);
                    $class = $this->firstOrCreateImportClass($schoolId, $academicYearId, $grade, $stream, $normalized, $createdClasses);

                    $learner = Student::query()
                        ->where('school_id', $schoolId)
                        ->where('admission_number', $normalized['admission_number'])
                        ->first();

                    $studentPayload = [
                        'school_id' => $schoolId,
                        'first_name' => $normalized['first_name'],
                        'middle_name' => $normalized['middle_name'],
                        'last_name' => $normalized['last_name'],
                        'admission_number' => $normalized['admission_number'],
                        'gender' => $normalized['gender'],
                        'date_of_birth' => $normalized['date_of_birth'],
                        'admission_date' => now()->toDateString(),
                        'admission_class_id' => $class?->id,
                        'current_class_id' => $class?->id,
                        'county' => $normalized['county'],
                        'boarding_status' => $normalized['boarding_status'],
                        'status' => $normalized['status'],
                        'nationality' => 'Kenyan',
                    ];

                    if ($learner) {
                        $learner->update($studentPayload);
                        $updatedLearners++;
                    } else {
                        $learner = Student::create($studentPayload);
                        $createdLearners++;
                    }

                    if ($class) {
                        $this->syncEnrollmentForImportedLearner($learner, $class->id, $academicYearId);
                    }

                    if ($normalized['guardian_name'] && $normalized['guardian_email'] && $normalized['guardian_phone'] && $normalized['guardian_password']) {
                        $this->upsertImportedGuardian($learner, $normalized, $guardianAccounts);
                    }
                }
            });

            $message = "Bulk upload complete: {$createdLearners} created, {$updatedLearners} updated, {$createdGrades} grades added, {$createdStreams} streams added, {$createdClasses} classes added";
            if ($guardianAccounts > 0) {
                $message .= ", {$guardianAccounts} guardian accounts processed";
            }
            $message .= '.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Import Error: ' . $e->getMessage());
        }
    }

    protected function parseLearnerCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Unable to read the uploaded CSV file.');
        }

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($value) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)), $header);

        $requiredHeaders = [
            'first_name',
            'last_name',
            'admission_number',
            'gender',
            'date_of_birth',
        ];

        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $header, true)) {
                throw new \InvalidArgumentException("Missing required column: '{$required}'. Please download the template to see the correct format.");
            }
        }

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || count(array_filter($data, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $index => $column) {
                $row[$column] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function normalizeLearnerImportRow(array $row, int $line): array
    {
        $firstName = trim((string) ($row['first_name'] ?? ''));
        $lastName = trim((string) ($row['last_name'] ?? ''));
        $admissionNumber = trim((string) ($row['admission_number'] ?? ''));
        $gender = strtolower(trim((string) ($row['gender'] ?? 'male')));
        $dateOfBirth = trim((string) ($row['date_of_birth'] ?? ''));
        $gradeName = trim((string) ($row['grade_name'] ?? ''));
        $className = trim((string) ($row['class_name'] ?? ''));
        $boardingStatus = strtolower(trim((string) ($row['boarding_status'] ?? 'day')));
        $status = strtolower(trim((string) ($row['status'] ?? 'active')));

        if ($firstName === '' || $lastName === '' || $admissionNumber === '' || $dateOfBirth === '') {
            throw new \InvalidArgumentException("CSV line {$line}: first_name, last_name, admission_number and date_of_birth are required.");
        }

        if (!in_array($gender, ['male', 'female', 'other'], true)) {
            throw new \InvalidArgumentException("CSV line {$line}: gender must be male, female or other.");
        }

        if (!in_array($boardingStatus, ['day', 'boarding'], true)) {
            throw new \InvalidArgumentException("CSV line {$line}: boarding_status must be day or boarding.");
        }

        if (!in_array($status, ['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'suspended'], true)) {
            throw new \InvalidArgumentException("CSV line {$line}: status is invalid.");
        }

        if ($gradeName === '' && $className !== '') {
            throw new \InvalidArgumentException("CSV line {$line}: grade_name is required when class_name is provided.");
        }

        return [
            'first_name' => $firstName,
            'middle_name' => $this->nullableValue($row['middle_name'] ?? null),
            'last_name' => $lastName,
            'admission_number' => $admissionNumber,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'grade_name' => $this->nullableValue($gradeName),
            'grade_code' => $this->nullableValue($row['grade_code'] ?? null),
            'grade_level_order' => is_numeric($row['grade_level_order'] ?? null) ? (int) $row['grade_level_order'] : null,
            'grade_category' => $this->nullableValue($row['grade_category'] ?? null) ?? 'General',
            'stream_name' => $this->nullableValue($row['stream_name'] ?? null),
            'stream_code' => $this->nullableValue($row['stream_code'] ?? null),
            'class_name' => $this->nullableValue($className),
            'class_code' => $this->nullableValue($row['class_code'] ?? null),
            'county' => $this->nullableValue($row['county'] ?? null),
            'boarding_status' => $boardingStatus,
            'status' => $status,
            'guardian_name' => $this->nullableValue($row['guardian_name'] ?? null),
            'guardian_email' => $this->nullableValue($row['guardian_email'] ?? null),
            'guardian_phone' => $this->nullableValue($row['guardian_phone'] ?? null),
            'guardian_password' => $this->nullableValue($row['guardian_password'] ?? null),
        ];
    }

    protected function firstOrCreateImportGrade(int $schoolId, array $data, int &$createdGrades): ?GradeLevel
    {
        if (!$data['grade_name']) {
            return null;
        }

        $grade = GradeLevel::query()
            ->where(function ($query) use ($data) {
                $query->where('name', $data['grade_name']);
                if ($data['grade_code']) {
                    $query->orWhere('code', $data['grade_code']);
                }
            })
            ->first();

        if ($grade) {
            return $grade;
        }

        $createdGrades++;

        return GradeLevel::create([
            'school_id' => $schoolId,
            'name' => $data['grade_name'],
            'code' => $data['grade_code'] ?: Str::upper(Str::slug($data['grade_name'], '')),
            'level_order' => $data['grade_level_order'] ?? ((int) GradeLevel::query()->max('level_order') + 1),
            'category' => $data['grade_category'],
            'is_active' => true,
        ]);
    }

    protected function firstOrCreateImportStream(int $schoolId, array $data, int &$createdStreams): ?Stream
    {
        if (!$data['stream_name']) {
            return null;
        }

        $stream = Stream::query()
            ->where(function ($query) use ($data) {
                $query->where('name', $data['stream_name']);
                if ($data['stream_code']) {
                    $query->orWhere('code', $data['stream_code']);
                }
            })
            ->first();

        if ($stream) {
            return $stream;
        }

        $createdStreams++;

        return Stream::create([
            'school_id' => $schoolId,
            'name' => $data['stream_name'],
            'code' => $data['stream_code'] ?: Str::upper(Str::substr($data['stream_name'], 0, 3)),
            'capacity' => 40,
            'is_active' => true,
        ]);
    }

    protected function firstOrCreateImportClass(int $schoolId, int $academicYearId, ?GradeLevel $grade, ?Stream $stream, array $data, int &$createdClasses): ?SchoolClass
    {
        if (!$grade || !$data['class_name']) {
            return null;
        }

        $query = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $grade->id)
            ->where('name', $data['class_name']);

        if ($stream) {
            $query->where('stream_id', $stream->id);
        }

        $class = $query->first();

        if ($class) {
            return $class;
        }

        $createdClasses++;

        return SchoolClass::create([
            'school_id' => $schoolId,
            'grade_level_id' => $grade->id,
            'stream_id' => $stream?->id,
            'academic_year_id' => $academicYearId,
            'name' => $data['class_name'],
            'code' => $data['class_code'] ?: Str::upper(Str::slug($data['class_name'], '-')),
            'capacity' => 40,
            'is_active' => true,
        ]);
    }

    protected function syncEnrollmentForImportedLearner(Student $student, int $classId, int $academicYearId): void
    {
        $currentTermId = DB::table('academic_terms')->where('is_current', true)->value('id');

        DB::table('student_enrollments')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where('class_id', '!=', $classId)
            ->update([
                'status' => 'transferred',
                'end_date' => now()->toDateString(),
                'updated_at' => now(),
            ]);

        $exists = DB::table('student_enrollments')
            ->where('student_id', $student->id)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if (!$exists) {
            DB::table('student_enrollments')->insert([
                'student_id' => $student->id,
                'class_id' => $classId,
                'academic_year_id' => $academicYearId,
                'academic_term_id' => $currentTermId,
                'enrollment_date' => now()->toDateString(),
                'enrollment_type' => 'new',
                'status' => 'active',
                'enrolled_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function upsertImportedGuardian(Student $student, array $data, int &$guardianAccounts): void
    {
        $guardianAccounts++;
        $existingGuardian = Guardian::query()->where('email', $data['guardian_email'])->first();

        if ($existingGuardian && $existingGuardian->user_id) {
            $student->guardians()->syncWithoutDetaching([
                $existingGuardian->id => [
                    'relationship' => 'guardian',
                    'is_primary_contact' => true,
                    'is_emergency_contact' => true,
                    'can_pickup' => true,
                    'receives_reports' => true,
                    'receives_fees_notification' => true,
                    'is_fee_payer' => true,
                    'updated_at' => now(),
                ],
            ]);
            return;
        }

        $this->upsertGuardianAccountForStudent($student, [
            'name' => $data['guardian_name'],
            'email' => $data['guardian_email'],
            'phone' => $data['guardian_phone'],
            'password' => $data['guardian_password'],
        ]);
    }

    public function updatePhoto(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $path = $request->file('photo')->store('students/photos', 'public');
        $student->update(['photo' => $path]);

        return back()->with('success', 'Photo updated successfully.');
    }

    protected function nullableValue(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }
}
