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

class ImportStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $schoolId;
    protected $importProcessId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $schoolId, int $importProcessId = null)
    {
        $this->filePath = $filePath;
        $this->schoolId = $schoolId;
        $this->importProcessId = $importProcessId;
    }

    /**
     * Execute the job.
     */
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

            DB::transaction(function () use ($rows, $roleService) {
                foreach ($rows as $index => $row) {
                    if ($this->importProcessId && $index % 5 === 0) {
                        \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['processed_rows' => $index]);
                    }
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
                    } else {
                        $user = User::create([
                            'name' => "{$normalized['first_name']} {$normalized['last_name']}",
                            'email' => $normalized['email'],
                            'phone' => $normalized['phone'],
                            'password' => Hash::make($normalized['password'] ?? 'Password123'),
                            'status' => 'active',
                            'school_id' => $this->schoolId,
                        ]);

                        $roleName = strtolower($normalized['role'] ?? 'teacher');
                        if ($roleService->isValidTemplate($roleName)) {
                            $user->assignRole($roleName);
                        }

                        $teacherData = collect($normalized)->except(['password', 'department_name', 'staff_category_name', 'staff_designation_name'])->toArray();
                        $teacherData['user_id'] = $user->id;
                        $teacherData['school_id'] = $this->schoolId;
                        $teacherData['status'] = 'active';

                        Teacher::create($teacherData);

                        Mail::to($user->email)->send(new UserCreatedMail($user, $normalized['password'] ?? 'Password123'));
                    }
                }
            });

            // Cleanup
            Storage::delete($this->filePath);

            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'completed',
                    'processed_rows' => count($rows)
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('ImportStaffJob Error: ' . $e->getMessage());
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
        if (!$handle) {
            throw new \RuntimeException('Unable to read the uploaded CSV file.');
        }

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($value) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)), $header);

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
            'phone' => $row['phone'] ?? null,
            'gender' => $row['gender'] ?? 'male',
            'role' => $row['role'] ?? 'teacher',
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'id_number' => $row['id_number'] ?? null,
            'nationality' => $row['nationality'] ?? 'Kenyan',
            'department_id' => $departmentId,
            'staff_category_id' => $categoryId,
            'staff_designation_id' => $designationId,
            'contract_type' => $row['contract_type'] ?? null,
            'employment_type' => $row['employment_type'] ?? null,
            'date_joined' => $row['date_joined'] ?? null,
            'basic_salary' => $row['basic_salary'] ?? null,
            'password' => $row['password'] ?? null,
        ];
    }
}
