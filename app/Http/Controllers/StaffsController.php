<?php

namespace App\Http\Controllers;

use App\Models\Academic\Department;
use App\Models\Academic\SchoolClass;
use App\Models\StaffCategory;
use App\Models\StaffDesignation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserCreatedMail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Services\RoleTemplateService;

class StaffsController extends Controller
{
    protected $roleService;

    public function __construct(RoleTemplateService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Safely count users with the given roles, optionally filtered by department.
     */
    protected function safeRoleCount(array $roleNames, ?int $departmentId = null): int
    {
        $existingRoles = Role::whereIn('name', $roleNames)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->toArray();

        if (empty($existingRoles)) {
            return 0;
        }

        $query = User::role($existingRoles);

        if ($departmentId) {
            $query->whereHas('teacher', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->count();
    }

    protected function transformStaffRow(Teacher $teacher): array
    {
        return [
            'id' => $teacher->id,
            'first_name' => $teacher->first_name,
            'middle_name' => $teacher->middle_name,
            'last_name' => $teacher->last_name,
            'full_name' => $teacher->full_name,
            'staff_number' => $teacher->staff_number,
            'status' => $teacher->status,
            'photo_url' => $teacher->photo_url,
            'department' => $teacher->department ? [
                'id' => $teacher->department->id,
                'name' => $teacher->department->name,
            ] : null,
            'user' => [
                'id' => $teacher->user->id,
                'email' => $teacher->user->email,
                'roles' => $teacher->user->roles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ]),
            ],
        ];
    }

    public function directory(): Response
    {
        $user = auth()->user();
        $isRestrictedHOD = $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin']);
        $hodDeptId = null;

        if ($isRestrictedHOD) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
        }

        $query = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.guard_name', 'web');

        if ($isRestrictedHOD) {
            $query->join('teachers', 'teachers.user_id', '=', 'model_has_roles.model_id')
                  ->where('teachers.department_id', $hodDeptId);
        }

        $roleCounts = $query->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name');

        $roles = $this->roleService->getTemplates()->map(function ($role) use ($roleCounts) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => str_replace('_', ' ', ucwords($role->name, '_')),
                'count' => $roleCounts[$role->name] ?? 0,
            ];
        });

        // Add Parents/Guardians specifically to the directory view
        $roles_array = $roles->toArray();
        $roles_array[] = [
            'id' => 999,
            'name' => 'parent',
            'display_name' => 'Parents',
            'count' => \App\Models\Guardian::count(),
            'is_custom' => true,
            'href' => '/parents'
        ];

        return Inertia::render('staffs/Directory', [
            'roles' => $roles_array,
        ]);
    }

    public function roleDirectory(Request $request, string $roleName)
    {
        if ($roleName === 'parent') {
            return redirect()->route('parents.index');
        }

        $user = auth()->user();
        $isRestrictedHOD = $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin']);
        $hodDeptId = null;

        if ($isRestrictedHOD) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
        }

        $role = Role::where('name', $roleName)->firstOrFail();
        
        $search = trim((string) $request->string('search'));
        $departmentId = $isRestrictedHOD ? (string) $hodDeptId : (string) $request->string('department_id');

        $query = Teacher::query()
            ->whereHas('user', function ($q) use ($roleName) {
                $q->role($roleName);
            })
            ->with(['department:id,name', 'user.roles'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($departmentId !== '' && $departmentId !== 'all', fn ($q) => $q->where('department_id', $departmentId));

        $staffs = $query->orderBy('first_name')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return Inertia::render('staffs/RoleDirectory', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => str_replace('_', ' ', ucwords($role->name, '_')),
            ],
            'staffs' => $staffs,
            'departments' => Department::when($isRestrictedHOD, fn ($q) => $q->where('id', $hodDeptId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'department_id' => $departmentId === '' ? 'all' : $departmentId,
                'view' => $request->string('view', 'grid'),
            ],
        ]);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $departmentId = (string) $request->string('department_id');
        $roleName = (string) $request->string('role');

        $user = auth()->user();
        $isRestrictedHOD = $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin']);
        $hodDeptId = null;

        if ($isRestrictedHOD) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
            // Force department filter for HODs
            $departmentId = (string) $hodDeptId;
        }

        $query = Teacher::query()
            ->with(['department:id,name', 'user.roles'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($departmentId !== '' && $departmentId !== 'all', fn ($q) => $q->where('department_id', $departmentId));

        if ($roleName !== '' && $roleName !== 'all') {
            $query->whereHas('user', function ($q) use ($roleName) {
                $q->withRole($roleName);
            });
        }

        $teachers = $query->orderBy('first_name')
            ->paginate($request->integer('per_page', 20))
            ->through(fn ($teacher) => $this->transformStaffRow($teacher));

        // Calculate detailed stats - Scoped to department if HOD
        $statsBase = Teacher::query();
        if ($isRestrictedHOD && $hodDeptId) {
            $statsBase->where('department_id', $hodDeptId);
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->where('status', 'active')->count(),
            'teaching' => $this->safeRoleCount(['teacher', 'hod', 'class_teacher'], $isRestrictedHOD ? $hodDeptId : null),
            'admins' => $this->safeRoleCount(['school_admin', 'principal', 'deputy_principal'], $isRestrictedHOD ? $hodDeptId : null),
            'non_teaching' => $this->safeRoleCount(['librarian', 'nurse', 'finance_officer', 'clerk', 'security', 'driver'], $isRestrictedHOD ? $hodDeptId : null),
            'on_leave' => (clone $statsBase)->where('status', 'on_leave')->count(),
            'departments' => $isRestrictedHOD ? 1 : Department::count(),
        ];

        return Inertia::render('staffs/Index', [
            'teachers' => $teachers,
            'departments' => Department::when($isRestrictedHOD, fn ($q) => $q->where('id', $hodDeptId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'roles' => $this->roleService->getTemplates(),
            'availableClasses' => SchoolClass::active()
                ->forCurrentYear()
                ->with(['gradeLevel', 'stream'])
                ->get()
                ->map(fn ($cls) => [
                    'id' => $cls->id,
                    'name' => "{$cls->gradeLevel?->name} {$cls->stream?->name}",
                ]),
            'availableSubjects' => \App\Models\Curriculum\Subject::active()->orderBy('name')->get(['id', 'name', 'code']),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'department_id' => $departmentId === '' ? 'all' : $departmentId,
                'role' => $roleName === '' ? 'all' : $roleName,
                'view' => $request->string('view', 'grid'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('staffs/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => StaffCategory::orderBy('name')->get(['id', 'name']),
            'designations' => StaffDesignation::orderBy('name')->get(['id', 'name']),
            'counties' => config('settings.counties', []),
            'roles' => $this->roleService->getTemplates(), // Sourced from global templates
            'preselectedRole' => $request->query('role'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'staff_number' => ['required', 'string', 'max:50', Rule::unique('teachers', 'staff_number')],
            'tsc_number' => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'tsc_number')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'department_id' => ['required', 'exists:departments,id'],
            'staff_category_id' => ['nullable', 'exists:staff_categories,id'],
            'staff_designation_id' => ['nullable', 'exists:staff_designations,id'],
            'contract_type' => ['nullable', 'string'],
            'employment_type' => ['nullable', 'string'],
            'date_joined' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave', 'suspended', 'terminated'])],
            'photo' => ['nullable', 'image', 'max:2048'],
            'role' => ['required', 'string'], // Use role from dropdown templates
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'county' => ['nullable', 'string', 'max:100'],
            'sub_county' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'nhif_number' => ['nullable', 'string', 'max:100'],
            'nssf_number' => ['nullable', 'string', 'max:100'],
            'kra_pin' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $schoolId = auth()->user()->school_id;

            $user = User::create([
                'school_id' => $schoolId,
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
            ]);

            if ($this->roleService->isValidTemplate($validated['role'])) {
                $user->assignRole($validated['role']);
            }

            $teacherData = collect($validated)->except(['password', 'password_confirmation', 'photo', 'role'])->toArray();
            $teacherData['user_id'] = $user->id;
            $teacherData['school_id'] = $schoolId;

            if ($request->hasFile('photo')) {
                $teacherData['photo'] = $request->file('photo')->store('staffs/photos', 'public');
            }

            Teacher::create($teacherData);

            // Send Welcome Email
            Mail::to($user->email)->send(new UserCreatedMail($user, $validated['password']));

            return redirect()->route('staffs.index')->with('success', 'Staff member created successfully.');
        });
    }

    public function show(Teacher $teacher): Response
    {
        $user = auth()->user();

        // Scope for HODs
        if ($user && $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
            abort_unless($teacher->department_id == $hodDeptId, 403, 'You do not have permission to view staff in another department.');
        }

        $teacher->load([
            'user', 
            'department', 
            'staffCategory', 
            'staffDesignation', 
            'classesAsTeacher.gradeLevel', 
            'classesAsTeacher.stream',
            'subjectAssignments.subject',
            'subjectAssignments.schoolClass'
        ]);

        return Inertia::render('staffs/Show', [
            'teacher' => $teacher,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => StaffCategory::orderBy('name')->get(['id', 'name']),
            'designations' => StaffDesignation::orderBy('name')->get(['id', 'name']),
            'availableClasses' => SchoolClass::active()
                ->forCurrentYear()
                ->with(['gradeLevel', 'stream', 'classTeacher'])
                ->get()
                ->map(fn ($cls) => [
                    'id' => $cls->id,
                    'name' => $cls->name,
                    'grade' => $cls->gradeLevel?->name,
                    'is_assigned' => !is_null($cls->class_teacher_id),
                    'current_teacher' => $cls->classTeacher?->name,
                ]),
            'counties' => config('settings.counties', []),
        ]);
    }

    public function assignClassTeacher(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'assignment_role' => ['required', Rule::in(['primary', 'assistant'])],
        ]);

        $class = SchoolClass::findOrFail($validated['class_id']);
        
        if ($validated['assignment_role'] === 'primary') {
            $class->update(['class_teacher_id' => $teacher->user_id]);
        } else {
            $class->update(['assistant_teacher_id' => $teacher->user_id]);
        }

        return back()->with('success', "Staff member assigned as {$validated['assignment_role']} teacher for {$class->name}.");
    }

    public function changeRole(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        if ($this->roleService->isValidTemplate($validated['role'])) {
            $teacher->user->syncRoles([$validated['role']]);
            return back()->with('success', "System role updated to " . strtoupper($validated['role']) . ".");
        }

        return back()->with('error', "Invalid role template selected.");
    }

    public function assignHOD(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $department = Department::findOrFail($validated['department_id']);
        $department->update(['head_id' => $teacher->user_id]);
        
        // Ensure they have the HOD role too
        if (!$teacher->user->hasRole('hod')) {
            $teacher->user->assignRole('hod');
        }

        return back()->with('success', "Staff assigned as HOD for {$department->name}.");
    }

    public function assignSubjects(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.subject_id' => ['required', 'exists:subjects,id'],
            'assignments.*.class_id' => ['required', 'exists:classes,id'],
        ]);

        DB::transaction(function () use ($teacher, $validated) {
            // Clear existing assignments if necessary, or just sync
            // For now, let's assume we are adding or replacing
            $teacher->subjectAssignments()->delete();
            
            foreach ($validated['assignments'] as $assign) {
                $teacher->subjectAssignments()->create([
                    'school_id' => $teacher->school_id,
                    'subject_id' => $assign['subject_id'],
                    'class_id' => $assign['class_id'],
                    'academic_year_id' => AcademicYear::current()->id ?? 1,
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Subject assignments updated successfully.");
    }

    public function edit(Teacher $teacher): Response
    {
        $user = auth()->user();

        // Scope for HODs
        if ($user && $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
            abort_unless($teacher->department_id == $hodDeptId, 403, 'You do not have permission to edit staff in another department.');
        }

        $teacher->load('user.roles');
        return Inertia::render('staffs/Edit', [
            'teacher' => $teacher,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => StaffCategory::orderBy('name')->get(['id', 'name']),
            'designations' => StaffDesignation::orderBy('name')->get(['id', 'name']),
            'counties' => config('settings.counties', []),
            'roles' => $this->roleService->getTemplates(),
        ]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $user = auth()->user();

        // Scope for HODs
        if ($user && $user->hasRole('hod') && !$user->hasAnyRole(['super_admin', 'school_admin', 'principal', 'deputy_principal', 'admin'])) {
            $hodDeptId = DB::table('teachers')->where('user_id', $user->id)->value('department_id');
            abort_unless($teacher->department_id == $hodDeptId, 403, 'You do not have permission to update staff in another department.');
        }
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'staff_number' => ['required', 'string', 'max:50', Rule::unique('teachers', 'staff_number')->ignore($teacher->id)],
            'tsc_number' => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'tsc_number')->ignore($teacher->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'department_id' => ['required', 'exists:departments,id'],
            'staff_category_id' => ['nullable', 'exists:staff_categories,id'],
            'staff_designation_id' => ['nullable', 'exists:staff_designations,id'],
            'contract_type' => ['nullable', 'string'],
            'employment_type' => ['nullable', 'string'],
            'date_joined' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave', 'suspended', 'terminated'])],
            'photo' => ['nullable', 'image', 'max:2048'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'county' => ['nullable', 'string', 'max:100'],
            'sub_county' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'nhif_number' => ['nullable', 'string', 'max:100'],
            'nssf_number' => ['nullable', 'string', 'max:100'],
            'kra_pin' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'role' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $request, $teacher) {
            $user = $teacher->user;

            $userData = [
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if ($user) {
                // Update existing User record
                $user->update($userData);
                
                // Update role
                if ($this->roleService->isValidTemplate($validated['role'])) {
                    $user->syncRoles([$validated['role']]);
                }
            } else {
                // Auto-create a User account for staff that don't have one
                if (empty($userData['password'])) {
                    $userData['password'] = Hash::make($validated['email']);
                }
                $userData['email_verified_at'] = now();
                $user = User::create($userData);
                $teacher->user_id = $user->id;

                if ($this->roleService->isValidTemplate($validated['role'])) {
                    $user->assignRole($validated['role']);
                }
            }

            $teacherData = collect($validated)->except(['password', 'password_confirmation', 'photo'])->toArray();

            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $teacherData['photo'] = $request->file('photo')->store('staffs/photos', 'public');
            }

            $teacher->update($teacherData);

            return back()->with('success', 'Staff member updated successfully.');
        });
    }

    public function updatePhoto(Request $request, Teacher $teacher): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }

        $teacher->update([
            'photo' => $request->file('photo')->store('staffs/photos', 'public'),
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        return DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();
            $user?->delete();

            return redirect()->route('staffs.index')->with('success', 'Staff member deleted successfully.');
        });
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:teachers,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $teachers = Teacher::whereIn('id', $validated['ids'])->get();
            foreach ($teachers as $teacher) {
                $user = $teacher->user;
                $teacher->delete();
                $user?->delete();
            }
        });

        return back()->with('success', 'Selected staff members deleted successfully.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:teachers,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave', 'suspended', 'terminated'])],
        ]);

        DB::transaction(function () use ($validated) {
            $teachers = Teacher::whereIn('id', $validated['ids'])->get();
            foreach ($teachers as $teacher) {
                $teacher->update(['status' => $validated['status']]);
                // Sync user status - active remains active, others map to inactive for system access
                $teacher->user->update(['status' => $validated['status'] === 'active' ? 'active' : 'inactive']);
            }
        });

        return back()->with('success', 'Selected staff status updated successfully.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="staffs_bulk_upload_template.csv"',
        ];

        $columns = [
            'first_name', 'middle_name', 'last_name', 'staff_number', 'tsc_number',
            'email', 'phone', 'gender', 'role', 'date_of_birth', 'id_number', 'nationality',
            'department_name', 'staff_category_name', 'staff_designation_name',
            'contract_type', 'employment_type', 'date_joined', 'basic_salary', 'password'
        ];

        $sample = [
            'John', 'Doe', 'Smith', 'TCH1001', 'TSC123456',
            'john.smith@example.com', '+254700000001', 'male', 'teacher', '1985-05-20', '12345678', 'Kenyan',
            'Mathematics', 'Teaching Staff', 'Senior Teacher',
            'Permanent', 'Full-time', '2020-01-01', '50000', 'Password123'
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
        $schoolId = auth()->user()->school_id;

        try {
            $path = $validated['file']->store('temp/imports');
            
            \App\Jobs\ImportStaffJob::dispatch(
                $path,
                (int) $schoolId
            );

            return back()->with('success', 'Staff members are being imported in the background. You will see them in the list shortly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start import: ' . $e->getMessage());
        }
    }
}
