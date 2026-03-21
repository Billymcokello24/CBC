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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeachersController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $departmentId = (string) $request->string('department_id');

        $teachers = Teacher::query()
            ->with(['department:id,name', 'user:id,status'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($departmentId !== '' && $departmentId !== 'all', fn ($q) => $q->where('department_id', $departmentId))
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return Inertia::render('teachers/Index', [
            'teachers' => $teachers,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => Teacher::count(),
                'active' => Teacher::where('status', 'active')->count(),
                'on_leave' => Teacher::where('status', 'on_leave')->count(),
                'departments' => Department::count(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status === '' ? 'all' : $status,
                'department_id' => $departmentId === '' ? 'all' : $departmentId,
                'view' => $request->string('view', 'grid'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('teachers/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => StaffCategory::orderBy('name')->get(['id', 'name']),
            'designations' => StaffDesignation::orderBy('name')->get(['id', 'name']),
            'counties' => config('settings.counties', []),
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
            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
            ]);

            if (Role::where('name', 'teacher')->exists()) {
                $user->assignRole('teacher');
            }

            $teacherData = collect($validated)->except(['password', 'password_confirmation', 'photo'])->toArray();
            $teacherData['user_id'] = $user->id;
            $teacherData['school_id'] = DB::table('schools')->value('id');

            if ($request->hasFile('photo')) {
                $teacherData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
            }

            Teacher::create($teacherData);

            return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
        });
    }

    public function show(Teacher $teacher): Response
    {
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

        return Inertia::render('teachers/Show', [
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
            'role' => ['required', Rule::in(['primary', 'assistant'])],
        ]);

        $class = SchoolClass::findOrFail($validated['class_id']);
        
        if ($validated['role'] === 'primary') {
            $class->update(['class_teacher_id' => $teacher->user_id]);
        } else {
            $class->update(['assistant_teacher_id' => $teacher->user_id]);
        }

        return back()->with('success', "Teacher assigned as {$validated['role']} teacher for {$class->name}.");
    }

    public function edit(Teacher $teacher): Response
    {
        $teacher->load('user');
        return Inertia::render('teachers/Edit', [
            'teacher' => $teacher,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => StaffCategory::orderBy('name')->get(['id', 'name']),
            'designations' => StaffDesignation::orderBy('name')->get(['id', 'name']),
            'counties' => config('settings.counties', []),
        ]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
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
            } else {
                // Auto-create a User account for teachers that don't have one
                if (empty($userData['password'])) {
                    // Default password = email if none provided
                    $userData['password'] = Hash::make($validated['email']);
                }
                $userData['email_verified_at'] = now();
                $user = User::create($userData);
                $teacher->user_id = $user->id;

                if (Role::where('name', 'teacher')->exists()) {
                    $user->assignRole('teacher');
                }
            }

            $teacherData = collect($validated)->except(['password', 'password_confirmation', 'photo'])->toArray();

            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $teacherData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
            }

            $teacher->update($teacherData);

            return back()->with('success', 'Teacher updated successfully.');
        });
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        return DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();
            $user?->delete();

            return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
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

        return back()->with('success', 'Selected teachers deleted successfully.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="teachers_bulk_upload_template.csv"',
        ];

        $columns = [
            'first_name', 'middle_name', 'last_name', 'staff_number', 'tsc_number',
            'email', 'phone', 'gender', 'date_of_birth', 'id_number', 'nationality',
            'department_name', 'staff_category_name', 'staff_designation_name',
            'contract_type', 'employment_type', 'date_joined', 'basic_salary', 'password'
        ];

        $sample = [
            'John', 'Doe', 'Smith', 'TCH1001', 'TSC123456',
            'john.smith@example.com', '+254700000001', 'male', '1985-05-20', '12345678', 'Kenyan',
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

        $schoolId = DB::table('schools')->value('id');

        try {
            $rows = $this->parseTeacherCsv($validated['file']->getRealPath());

            if (count($rows) === 0) {
                return back()->with('error', 'The uploaded CSV file is empty.');
            }

            $createdTeachers = 0;
            $updatedTeachers = 0;

            DB::transaction(function () use ($rows, $schoolId, &$createdTeachers, &$updatedTeachers) {
                foreach ($rows as $index => $row) {
                    $line = $index + 2;
                    $normalized = $this->normalizeTeacherImportRow($row, $line);

                    $teacher = Teacher::query()->where('staff_number', $normalized['staff_number'])->first();

                    if ($teacher) {
                        $user = $teacher->user;
                        $user->update([
                            'name' => "{$normalized['first_name']} {$normalized['last_name']}",
                            'email' => $normalized['email'],
                            'phone' => $normalized['phone'] ?? $user->phone,
                        ]);

                        $teacher->update(collect($normalized)->except(['password', 'department_name', 'staff_category_name', 'staff_designation_name'])->toArray());
                        $updatedTeachers++;
                    } else {
                        $user = User::create([
                            'name' => "{$normalized['first_name']} {$normalized['last_name']}",
                            'email' => $normalized['email'],
                            'phone' => $normalized['phone'],
                            'password' => Hash::make($normalized['password'] ?? 'Password123'),
                            'status' => 'active',
                        ]);

                        if (Role::where('name', 'teacher')->exists()) {
                            $user->assignRole('teacher');
                        }

                        $teacherData = collect($normalized)->except(['password', 'department_name', 'staff_category_name', 'staff_designation_name'])->toArray();
                        $teacherData['user_id'] = $user->id;
                        $teacherData['school_id'] = $schoolId;
                        $teacherData['status'] = 'active';

                        Teacher::create($teacherData);
                        $createdTeachers++;
                    }
                }
            });

            return back()->with('success', "Bulk upload complete: {$createdTeachers} created, {$updatedTeachers} updated.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import Error: ' . $e->getMessage());
        }
    }

    protected function parseTeacherCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Unable to read the uploaded CSV file.');
        }

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($value) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)), $header);

        $requiredHeaders = ['first_name', 'last_name', 'staff_number', 'email', 'phone'];

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

    protected function normalizeTeacherImportRow(array $row, int $line): array
    {
        if (empty($row['first_name']) || empty($row['last_name']) || empty($row['staff_number']) || empty($row['email'])) {
            throw new \InvalidArgumentException("CSV line {$line}: first_name, last_name, staff_number and email are required.");
        }

        $departmentId = null;
        if (!empty($row['department_name'])) {
            $departmentId = Department::where('name', $row['department_name'])->value('id');
        }

        $categoryId = null;
        if (!empty($row['staff_category_name'])) {
            $categoryId = StaffCategory::where('name', $row['staff_category_name'])->value('id');
        }

        $designationId = null;
        if (!empty($row['staff_designation_name'])) {
            $designationId = StaffDesignation::where('name', $row['staff_designation_name'])->value('id');
        }

        return [
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'staff_number' => $row['staff_number'],
            'tsc_number' => $row['tsc_number'] ?? null,
            'email' => $row['email'],
            'phone' => $row['phone'],
            'gender' => strtolower($row['gender'] ?? 'male'),
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'id_number' => $row['id_number'] ?? null,
            'nationality' => $row['nationality'] ?? 'Kenyan',
            'department_id' => $departmentId,
            'staff_category_id' => $categoryId,
            'staff_designation_id' => $designationId,
            'contract_type' => $row['contract_type'] ?? 'Permanent',
            'employment_type' => $row['employment_type'] ?? 'Full-time',
            'date_joined' => $row['date_joined'] ?? now()->toDateString(),
            'basic_salary' => $row['basic_salary'] ?? 0,
            'password' => $row['password'] ?? null,
        ];
    }
}
