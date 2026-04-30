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
use App\Mail\ParentNotificationMail;
use Illuminate\Support\Facades\Password;
use App\Models\School;
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

        $teacher = null;
        $myClassIds = [];
        $isRestricted = false;

        // Scope to teacher's classes if not an admin/manager
        if ($user && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $isRestricted = true;
            $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
            
            // 1. Classes where they teach subjects
            $subjectClassIds = DB::table('teacher_subjects')
                ->where('teacher_id', $teacher?->id)
                ->where('is_active', true)
                ->pluck('class_id')
                ->toArray();
                
            // 2. Classes where they are class teacher
            $classTeacherClassIds = DB::table('classes')
                ->where('class_teacher_id', $user->id)
                ->pluck('id')
                ->toArray();
                
            // 3. Departmental scoping for HODs
            $hodClassIds = [];
            if ($user->hasRole('hod') && $teacher?->department_id) {
                $subjectIds = DB::table('subjects')
                    ->where('department_id', $teacher->department_id)
                    ->pluck('id');
                
                $hodClassIds = DB::table('teacher_subjects')
                    ->whereIn('subject_id', $subjectIds)
                    ->pluck('class_id')
                    ->toArray();
            }

            $myClassIds = array_unique(array_merge($subjectClassIds, $classTeacherClassIds, $hodClassIds));
            
            if (empty($myClassIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('current_class_id', $myClassIds);
            }
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

        // Use a scoped base for stats if restricted
        $statsBase = Student::query();
        if ($isRestricted) {
            if (empty($myClassIds)) {
                $statsBase->whereRaw('1 = 0');
            } else {
                $statsBase->whereIn('current_class_id', $myClassIds);
            }
        }

        $stats = (clone $statsBase)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as boys,
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as girls,
                SUM(CASE WHEN admission_date >= ? THEN 1 ELSE 0 END) as new_this_term,
                SUM(CASE WHEN admission_date < ? THEN 1 ELSE 0 END) as previous_term,
                SUM(CASE WHEN status IN ('withdrawn', 'inactive', 'transferred') THEN 1 ELSE 0 END) as withdrawn
            ", [now()->subMonths(3)->toDateString(), now()->subMonths(3)->toDateString()])
            ->first();

        $growth = $stats->previous_term > 0 ? round(($stats->new_this_term / $stats->previous_term) * 100, 1) : 0.0;

        return Inertia::render('students/Index', [
            'learners' => $learners,
            'stats' => [
                'total' => $stats->total,
                'active' => $stats->active,
                'withdrawn' => $stats->withdrawn,
                'new_this_month' => $stats->new_this_term,
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
            'classes' => SchoolClass::query()
                ->when($isRestricted, fn($q) => $q->whereIn('id', $myClassIds))
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
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
            'can_export' => true,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $classId = $request->integer('class_id');
        $gender = (string) $request->string('gender');
        $boardingStatus = (string) $request->string('boarding_status');

        $user = auth()->user();
        $query = Student::query()->with([
            'currentClass' => fn($q) => $q->select('id', 'name', 'code', 'grade_level_id', 'stream_id'),
            'currentClass.gradeLevel', 
            'currentClass.stream'
        ]);

        if ($user && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $isRestricted = true;
            $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
            $subjectClassIds = DB::table('teacher_subjects')->where('teacher_id', $teacher?->id)->where('is_active', true)->pluck('class_id')->toArray();
            $classTeacherIds = DB::table('classes')->where('class_teacher_id', $teacher?->id)->pluck('id')->toArray();
            $myClassIds = array_unique(array_merge($subjectClassIds, $classTeacherIds));
            $query->whereIn('current_class_id', $myClassIds);
        }

        $items = $query
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($classId > 0, fn ($q) => $q->where('current_class_id', $classId))
            ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
            ->when($boardingStatus !== '' && $boardingStatus !== 'all', fn ($q) => $q->where('boarding_status', $boardingStatus))
            ->orderBy('id', 'desc')
            ->get();

        $school = School::find(auth()->user()->school_id);
        $themeColor = DB::table('school_settings')
            ->where('school_id', $school?->id)
            ->where('key', 'pdf_theme_color')
            ->value('value') ?? '#1e40af';

        $pdf = Pdf::loadView('pdf.students', [
            'items' => $items,
            'school' => $school,
            'themeColor' => $themeColor
        ]);

        return $pdf->download('student_enrollment_' . date('Y_m_d_His') . '.pdf');
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
        $user = auth()->user();
        
        if ($user?->hasRole('parent')) {
            $guardian = $user->guardian;
            abort_unless($guardian && $guardian->students()->whereKey($student->id)->exists(), 403);
        }

        // Scope for teachers/HODs
        if ($user && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin', 'parent'])) {
            $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
            
            $subjectClassIds = DB::table('teacher_subjects')->where('teacher_id', $teacher?->id)->where('is_active', true)->pluck('class_id')->toArray();
            $classTeacherClassIds = DB::table('classes')->where('class_teacher_id', $user->id)->pluck('id')->toArray();
            $hodClassIds = [];
            if ($user->hasRole('hod') && $teacher?->department_id) {
                $subjectIds = DB::table('subjects')->where('department_id', $teacher->department_id)->pluck('id');
                $hodClassIds = DB::table('teacher_subjects')->whereIn('subject_id', $subjectIds)->pluck('class_id')->toArray();
            }

            $myClassIds = array_unique(array_merge($subjectClassIds, $classTeacherClassIds, $hodClassIds));
            
            abort_unless(in_array($student->current_class_id, $myClassIds), 403, 'You do not have permission to view this student.');
        }

        $student->load([
            'currentClass:id,name,code,grade_level_id', 
            'admissionClass:id,name,code', 
            'guardians:id,user_id,first_name,last_name,phone,email'
        ]);

        // Calculate real attendance stats
        $attendanceStats = $student->attendance()
            ->selectRaw('count(*) as total, sum(case when status = "present" then 1 else 0 end) as present')
            ->first();
        
        $attendancePercentage = $attendanceStats && $attendanceStats->total > 0 
            ? round(($attendanceStats->present / $attendanceStats->total) * 100, 1) 
            : 0;

        // Calculate performance level
        $avgRating = $student->assessmentRatings()->avg('rating_level');
        $ratingLabel = 'NOT ASSESSED';
        $performanceValue = 'N/A';
        if ($avgRating) {
            $performanceValue = number_format($avgRating, 1);
            if ($avgRating >= 3.5) $ratingLabel = 'Exceeding Expectation';
            elseif ($avgRating >= 2.5) $ratingLabel = 'Meeting Expectation';
            elseif ($avgRating >= 1.5) $ratingLabel = 'Approaching Expectation';
            else $ratingLabel = 'Below Expectation';
        }

        // Fetch enrollment history
        $enrollments = $student->enrollments()
            ->with(['class', 'academicYear', 'academicTerm'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($enrollment) => [
                'id' => $enrollment->id,
                'class_name' => $enrollment->class?->name,
                'academic_year' => $enrollment->academicYear?->name,
                'academic_term' => $enrollment->academicTerm?->name,
                'enrollment_date' => optional($enrollment->enrollment_date)->format('Y-m-d'),
                'status' => $enrollment->status,
            ]);

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
                'admission_class_name' => $student->admissionClass?->name,
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
                'grade_name' => DB::table('grade_levels')->where('id', $student->currentClass?->grade_level_id)->value('name'),
                'grade_id' => $student->currentClass?->grade_level_id,
                'guardians' => $student->guardians->map(fn ($guardian) => [
                    'id' => $guardian->id,
                    'name' => trim($guardian->first_name . ' ' . $guardian->last_name),
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                    'relationship' => $guardian->pivot->relationship,
                    'has_login' => (bool) $guardian->user_id,
                ])->values(),
                'attendance_percentage' => $attendancePercentage,
                'performance_rating' => $ratingLabel,
                'performance_value' => $performanceValue,
                'enrollments' => $enrollments,
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
        $user = auth()->user();

        // Scope for teachers/HODs
        if ($user && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $teacher = DB::table('teachers')->where('user_id', $user->id)->first();
            
            $subjectClassIds = DB::table('teacher_subjects')->where('teacher_id', $teacher?->id)->where('is_active', true)->pluck('class_id')->toArray();
            $classTeacherClassIds = DB::table('classes')->where('class_teacher_id', $user->id)->pluck('id')->toArray();
            $hodClassIds = [];
            if ($user->hasRole('hod') && $teacher?->department_id) {
                $subjectIds = DB::table('subjects')->where('department_id', $teacher->department_id)->pluck('id');
                $hodClassIds = DB::table('teacher_subjects')->whereIn('subject_id', $subjectIds)->pluck('class_id')->toArray();
            }

            $myClassIds = array_unique(array_merge($subjectClassIds, $classTeacherClassIds, $hodClassIds));
            
            abort_unless(in_array($student->current_class_id, $myClassIds), 403, 'You do not have permission to edit this student.');
        }

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

    public function withdraw(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'withdrawal_date' => 'required|date',
            'withdrawal_reason' => 'required|string|max:255',
        ]);

        $student->update([
            'status' => 'withdrawn',
            'withdrawal_date' => $validated['withdrawal_date'],
            'withdrawal_reason' => $validated['withdrawal_reason'],
        ]);

        return back()->with('success', 'Learner withdrawn successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Learner deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learner_ids' => ['nullable', 'array'],
            'learner_ids.*' => ['integer', 'exists:students,id'],
            'all_matching' => ['nullable', 'boolean'],
            'filters' => ['nullable', 'array'],
        ]);

        $ids = $validated['learner_ids'] ?? [];
        $allMatching = $validated['all_matching'] ?? false;

        DB::transaction(function () use ($ids, $allMatching, $validated) {
            $query = Student::query();
            
            if ($allMatching) {
                $filters = $validated['filters'] ?? [];
                $search = trim((string) ($filters['search'] ?? ''));
                $status = (string) ($filters['status'] ?? 'all');
                $classId = (int) ($filters['class_id'] ?? 0);
                $gender = (string) ($filters['gender'] ?? 'all');
                $boardingStatus = (string) ($filters['boarding_status'] ?? 'all');

                $query->where('school_id', auth()->user()->school_id)
                    ->when($search !== '', fn ($q) => $q->search($search))
                    ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
                    ->when($classId > 0, fn ($q) => $q->where('current_class_id', $classId))
                    ->when($gender !== '' && $gender !== 'all', fn ($q) => $q->where('gender', $gender))
                    ->when($boardingStatus !== '' && $boardingStatus !== 'all', fn ($q) => $q->where('boarding_status', $boardingStatus));
            } else {
                $query->whereIn('id', $ids);
            }

            $query->delete();
        });

        return redirect()->route('students.index')->with('success', 'Selected learners deleted successfully.');
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
        $students = $guardian->students()->get();
        $token = Password::createToken($user);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        Mail::to($user->email)->send(new ParentNotificationMail($guardian, $students, $data['password'], $resetUrl));

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

            // Send Update Email
            if ($user) {
                $emailChanged = $user->wasChanged('email');
                $passwordToSend = $data['password'] ?: Str::random(12);
                if (!$data['password']) {
                    $user->update(['password' => Hash::make($passwordToSend)]);
                }
                
                $students = $existingGuardian->students()->get();
                $token = Password::createToken($user);
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                $note = $emailChanged ? "Your account email has been updated to this address ({$user->email})." : null;

                Mail::to($user->email)->send(new ParentNotificationMail($existingGuardian, $students, $passwordToSend, $resetUrl, $note));
            }

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
            'upi',
            'gender',
            'date_of_birth',
            'birth_certificate_number',
            'nationality',
            'religion',
            'primary_language',
            'secondary_language',
            'home_address',
            'county',
            'sub_county',
            'ward',
            'blood_group',
            'medical_conditions',
            'allergies',
            'grade_name',
            'grade_code',
            'grade_level_order',
            'grade_category',
            'stream_name',
            'stream_code',
            'class_name',
            'class_code',
            'boarding_status',
            'admission_date',
            'status',
            'guardian_name',
            'guardian_email',
            'guardian_phone',
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

        $user = auth()->user();
        $schoolId = $user->school_id ?: session('viewing_school_id');

        \Log::info('Bulk Upload Initiated', [
            'user_id' => $user->id,
            'user_school_id' => $user->school_id,
            'session_school_id' => session('viewing_school_id'),
            'resolved_school_id' => $schoolId
        ]);

        // Fallback for Super Admin: find first school if none selected
        if (!$schoolId && $user->hasRole('super_admin')) {
            $schoolId = \App\Models\School::first()?->id;
            \Log::info('Super Admin Fallback School', ['fallback_school_id' => $schoolId]);
        }

        // Use correct model for Academic Year
        // Use withoutGlobalScopes() to ensure we find an active year even if the current school context is broken
        $academicYear = \App\Models\Academic\AcademicYear::withoutGlobalScopes()
            ->where('status', 'active')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->first();

        // Second fallback: find ANY active academic year and use its school if schoolId was missing
        if (!$academicYear) {
            \Log::warning('No active academic year found for school, trying global fallback', ['school_id' => $schoolId]);
            $academicYear = \App\Models\Academic\AcademicYear::withoutGlobalScopes()
                ->where('status', 'active')
                ->first();
            
            if ($academicYear && (!$schoolId || $user->hasRole('super_admin'))) {
                $schoolId = $academicYear->school_id;
                \Log::info('Global Academic Year Fallback', ['new_school_id' => $schoolId, 'academic_year_id' => $academicYear->id]);
            }
        }

        if (!$schoolId || !$academicYear) {
            \Log::error('Import failed: Final context resolution failed', ['school_id' => $schoolId, 'academic_year' => $academicYear ? $academicYear->id : 'null']);
            
            // diagnostic check for terms if school is found
            $termCount = $schoolId ? \DB::table('academic_terms')->where('school_id', $schoolId)->count() : 0;
            $extra = $termCount === 0 && $schoolId ? " No academic terms found for this school." : "";
            
            return back()->with('error', "Import failed: Missing school context (ID: " . ($schoolId ?? 'None') . ") or active academic year.{$extra} Please ensure an active academic year exists.");
        }

        $academicYearId = $academicYear->id;

        try {
            $path = $validated['file']->store('temp/imports');
            
            $importProcess = \App\Models\ImportProcess::create([
                'user_id' => auth()->id(),
                'school_id' => $schoolId,
                'type' => 'students',
                'status' => 'pending',
            ]);

            \App\Jobs\ImportStudentsJob::dispatch(
                $path,
                (int) $schoolId,
                (int) $academicYearId,
                (int) auth()->id(),
                $importProcess->id
            );

            return back()->with('success', 'Learners are being imported in the background. You will see them in the list shortly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start import: ' . $e->getMessage());
        }
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
