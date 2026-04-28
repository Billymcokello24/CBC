<?php

namespace App\Jobs;

use App\Models\Academic\Department;
use App\Models\StaffCategory;
use App\Models\StaffDesignation;
use App\Models\Teacher;
use App\Models\User;
use App\Mail\UserCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\RoleTemplateService;
use Illuminate\Support\Str;

class ImportStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $schoolId;
    protected $importProcessId;

    // Cache for structural lookups
    protected $deptCache = [];
    protected $catCache = [];
    protected $desCache = [];

    public function __construct(string $filePath, int $schoolId, int $importProcessId = null)
    {
        $this->filePath = $filePath;
        $this->schoolId = $schoolId;
        $this->importProcessId = $importProcessId;
    }

    public function handle(RoleTemplateService $roleService): void
    {
        $fullPath = Storage::path($this->filePath);
        
        try {
            $rows = $this->parseTeacherCsv($fullPath);
            $totalRows = count($rows);

            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'processing',
                    'total_rows' => $totalRows
                ]);
            }
            
            if ($totalRows === 0) {
                if ($this->importProcessId) {
                    \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'completed']);
                }
                return;
            }

            $usersToUpsert = [];
            $teachersToUpsert = [];
            $emailsToDispatch = [];
            $roleAssignments = [];
            
            // 1. PHASE ONE: Normalization & Structural Integrity (Fast Caching)
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $normalized = $this->normalizeTeacherImportRow($row, $line);
                
                // Track for User Upsert
                $usersToUpsert[] = [
                    'name' => "{$normalized['first_name']} {$normalized['last_name']}",
                    'email' => $normalized['email'],
                    'phone' => $normalized['phone'],
                    'password' => Hash::make(Str::random(12)), // Default random password for new users
                    'status' => 'active',
                    'school_id' => $this->schoolId,
                    'force_password_change' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Data for Teacher Upsert (needs User ID later)
                $normalized['index'] = $index;
                $teachersToUpsert[] = $normalized;
                
                if ($this->importProcessId && $index % 50 === 0) {
                    \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['processed_rows' => $index]);
                }
            }

            // 2. PHASE TWO: High-Speed DB Operations
            DB::transaction(function () use ($usersToUpsert, &$teachersToUpsert, $roleService, &$emailsToDispatch) {
                // Upsert Users (Match by email)
                User::upsert($usersToUpsert, ['email'], ['name', 'phone']);

                // Retrieve all User IDs for the emails in this batch
                $emails = collect($usersToUpsert)->pluck('email')->toArray();
                $foundUsers = User::whereIn('email', $emails)->get()->keyBy('email');

                $finalTeachers = [];
                foreach ($teachersToUpsert as $tData) {
                    $user = $foundUsers[$tData['email']] ?? null;
                    if (!$user) continue;

                    // Assign Roles (Model-based but necessary for permissions)
                    $roleName = strtolower($tData['role'] ?? 'teacher');
                    if ($roleService->isValidTemplate($roleName)) {
                        $user->assignRole($roleName);
                    }

                    // Prepare for email dispatch later
                    $emailsToDispatch[] = [
                        'user' => $user,
                        'email' => $user->email,
                    ];

                    $finalTeachers[] = [
                        'user_id' => $user->id,
                        'school_id' => $this->schoolId,
                        'staff_number' => $tData['staff_number'],
                        'tsc_number' => $tData['tsc_number'],
                        'first_name' => $tData['first_name'],
                        'middle_name' => $tData['middle_name'],
                        'last_name' => $tData['last_name'],
                        'email' => $tData['email'],
                        'phone' => $tData['phone'],
                        'gender' => $tData['gender'],
                        'date_of_birth' => $tData['date_of_birth'],
                        'id_number' => $tData['id_number'],
                        'nationality' => $tData['nationality'],
                        'department_id' => $tData['department_id'],
                        'staff_category_id' => $tData['staff_category_id'],
                        'staff_designation_id' => $tData['staff_designation_id'],
                        'contract_type' => $tData['contract_type'],
                        'employment_type' => $tData['employment_type'],
                        'date_joined' => $tData['date_joined'],
                        'basic_salary' => $tData['basic_salary'],
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Upsert Teachers (Match by staff_number & school_id)
                Teacher::upsert($finalTeachers, ['staff_number', 'school_id'], [
                    'user_id', 'first_name', 'middle_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth', 
                    'id_number', 'nationality', 'department_id', 'staff_category_id', 'staff_designation_id', 
                    'contract_type', 'employment_type', 'date_joined', 'basic_salary'
                ]);
            });

            // 3. PHASE THREE: Delayed Email Dispatch (Post-Sync)
            foreach ($emailsToDispatch as $item) {
                try {
                    // Send with a 1-second delay between each to avoid SMTP quotas if large batch
                    Mail::to($item['email'])->later(now()->addSeconds($index * 0.5), new UserCreatedMail($item['user'], 'Set your password via login screen'));
                } catch (\Exception $e) {
                    \Log::warning("Delayed email failed for {$item['email']}: " . $e->getMessage());
                }
            }

            Storage::delete($this->filePath);

            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'completed',
                    'processed_rows' => $totalRows
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('ImportStaffJob Fatal Error: ' . $e->getMessage());
            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    protected function parseTeacherCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) throw new \RuntimeException('Unable to read the uploaded CSV file.');

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($v) => Str::slug(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $v)), '_'), $header);

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) continue;
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

        $dept = !empty($row['department_name']) ? $row['department_name'] : 'Academic';
        if (!isset($this->deptCache[$dept])) {
            $this->deptCache[$dept] = Department::firstOrCreate(['school_id' => $this->schoolId, 'name' => $dept], [
                'code' => substr(Str::upper(Str::slug($dept, '')), 0, 100), 'is_active' => true
            ])->id;
        }

        $cat = !empty($row['staff_category_name']) ? $row['staff_category_name'] : 'Teaching Staff';
        if (!isset($this->catCache[$cat])) {
            $this->catCache[$cat] = StaffCategory::firstOrCreate(['school_id' => $this->schoolId, 'name' => $cat], [
                'code' => substr(Str::upper(Str::slug($cat, '')), 0, 100), 'is_active' => true
            ])->id;
        }

        $des = !empty($row['staff_designation_name']) ? $row['staff_designation_name'] : 'Teacher';
        if (!isset($this->desCache[$des])) {
            $this->desCache[$des] = StaffDesignation::firstOrCreate(['school_id' => $this->schoolId, 'name' => $des], [
                'staff_category_id' => $this->catCache[$cat],
                'code' => substr(Str::upper(Str::slug($des, '')), 0, 100), 'is_active' => true
            ])->id;
        }

        return [
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'staff_number' => $row['staff_number'],
            'tsc_number' => $row['tsc_number'] ?? null,
            'email' => $row['email'],
            'phone' => $row['phone'] ?? '0000000000',
            'gender' => strtolower($row['gender'] ?? 'male'),
            'role' => $row['role'] ?? 'teacher',
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'id_number' => $row['id_number'] ?? null,
            'nationality' => $row['nationality'] ?? 'Kenyan',
            'department_id' => $this->deptCache[$dept],
            'staff_category_id' => $this->catCache[$cat],
            'staff_designation_id' => $this->desCache[$des],
            'contract_type' => $row['contract_type'] ?? null,
            'employment_type' => $row['employment_type'] ?? null,
            'date_joined' => $row['date_joined'] ?? null,
            'basic_salary' => $row['basic_salary'] ?? null,
        ];
    }
}
