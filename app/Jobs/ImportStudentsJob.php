<?php

namespace App\Jobs;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use App\Models\Student;
use App\Models\User;
use App\Models\Guardian;
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
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $schoolId;
    protected $academicYearId;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $schoolId, int $academicYearId, int $userId)
    {
        $this->filePath = $filePath;
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        
        try {
            $rows = $this->parseLearnerCsv($fullPath);
            
            if (count($rows) === 0) {
                return;
            }

            DB::transaction(function () use ($rows) {
                foreach ($rows as $index => $row) {
                    $line = $index + 2;
                    $normalized = $this->normalizeLearnerImportRow($row, $line);
                    
                    $createdGrades = 0; // Local tracking doesn't matter for the job result yet
                    $createdStreams = 0;
                    $createdClasses = 0;
                    $guardianAccounts = 0;

                    $grade = $this->firstOrCreateImportGrade($this->schoolId, $normalized, $createdGrades);
                    $stream = $this->firstOrCreateImportStream($this->schoolId, $normalized, $createdStreams);
                    $class = $this->firstOrCreateImportClass($this->schoolId, $this->academicYearId, $grade, $stream, $normalized, $createdClasses);

                    $learner = Student::query()
                        ->where('school_id', $this->schoolId)
                        ->where('admission_number', $normalized['admission_number'])
                        ->first();

                    $studentPayload = [
                        'school_id' => $this->schoolId,
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
                    } else {
                        $learner = Student::create($studentPayload);
                    }

                    if ($class) {
                        $this->syncEnrollmentForImportedLearner($learner, $class->id, $this->academicYearId);
                    }

                    if ($normalized['guardian_name'] && $normalized['guardian_email'] && $normalized['guardian_phone'] && $normalized['guardian_password']) {
                        $this->upsertImportedGuardian($learner, $normalized, $guardianAccounts);
                    }
                }
            });

            // Cleanup
            Storage::delete($this->filePath);

        } catch (\Exception $e) {
            \Log::error('ImportStudentsJob Error: ' . $e->getMessage());
            // Optionally notify user via DB notification
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
        if (!$data['grade_name']) return null;

        $grade = GradeLevel::query()
            ->where(function ($query) use ($data) {
                $query->where('name', $data['grade_name']);
                if ($data['grade_code']) $query->orWhere('code', $data['grade_code']);
            })->first();

        if ($grade) return $grade;

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
        if (!$data['stream_name']) return null;

        $stream = Stream::query()
            ->where(function ($query) use ($data) {
                $query->where('name', $data['stream_name']);
                if ($data['stream_code']) $query->orWhere('code', $data['stream_code']);
            })->first();

        if ($stream) return $stream;

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
        if (!$grade || !$data['class_name']) return null;

        $query = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $grade->id)
            ->where('name', $data['class_name']);

        if ($stream) $query->where('stream_id', $stream->id);

        $class = $query->first();
        if ($class) return $class;

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
                'enrolled_by' => $this->userId,
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

        if (Role::query()->where('name', 'parent')->where('guard_name', 'web')->exists()) {
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

        Mail::to($user->email)->send(new UserCreatedMail($user, $data['password']));

        return $guardian;
    }

    protected function nullableValue(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }
}
