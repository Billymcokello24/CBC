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
    protected $importProcessId;

    protected $gradeCache = [];
    protected $streamCache = [];
    protected $classCache = [];

    public function __construct(string $filePath, int $schoolId, int $academicYearId, int $userId, int $importProcessId = null)
    {
        $this->filePath = $filePath;
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->userId = $userId;
        $this->importProcessId = $importProcessId;
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        
        try {
            $rows = $this->parseLearnerCsv($fullPath);
            $totalRows = count($rows);
            
            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'processing',
                    'total_rows' => $totalRows
                ]);
            }

            if ($totalRows === 0) {
                if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'completed']);
                return;
            }

            $guardianUsersToUpsert = [];
            $guardiansToUpsert = [];
            $studentsToUpsert = [];
            $emailsToDispatch = [];
            $enrollmentMap = [];

            // 1. PHASE ONE: Normalization & Map Creation
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $normalized = $this->normalizeLearnerImportRow($row, $line);
                
                // Track for Grade/Class mapping
                $grade = $this->firstOrCreateImportGrade($this->schoolId, $normalized);
                $stream = $this->firstOrCreateImportStream($this->schoolId, $normalized);
                $class = $this->firstOrCreateImportClass($this->schoolId, $this->academicYearId, $grade, $stream, $normalized);

                if ($normalized['guardian_email']) {
                    $guardianUsersToUpsert[] = [
                        'name' => $normalized['guardian_name'] ?: 'Guardian',
                        'email' => $normalized['guardian_email'],
                        'phone' => $normalized['guardian_phone'],
                        'password' => Hash::make(Str::random(12)),
                        'status' => 'active',
                        'school_id' => $this->schoolId,
                        'force_password_change' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $studentsToUpsert[] = [
                    'school_id' => $this->schoolId,
                    'first_name' => $normalized['first_name'],
                    'middle_name' => $normalized['middle_name'],
                    'last_name' => $normalized['last_name'],
                    'admission_number' => $normalized['admission_number'],
                    'upi' => $normalized['upi'],
                    'gender' => $normalized['gender'],
                    'date_of_birth' => $normalized['date_of_birth'],
                    'birth_certificate_number' => $normalized['birth_certificate_number'],
                    'nationality' => $normalized['nationality'] ?: 'Kenyan',
                    'religion' => $normalized['religion'],
                    'primary_language' => $normalized['primary_language'],
                    'secondary_language' => $normalized['secondary_language'],
                    'home_address' => $normalized['home_address'],
                    'county' => $normalized['county'],
                    'sub_county' => $normalized['sub_county'],
                    'ward' => $normalized['ward'],
                    'blood_group' => $normalized['blood_group'],
                    'medical_conditions' => $normalized['medical_conditions'],
                    'allergies' => $normalized['allergies'],
                    'admission_date' => $normalized['admission_date'] ?: now()->toDateString(),
                    'boarding_status' => $normalized['boarding_status'],
                    'status' => $normalized['status'] ?: 'active',
                    'current_class_id' => $class?->id,
                    'admission_class_id' => $class?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $enrollmentMap[$normalized['admission_number']] = [
                    'class_id' => $class?->id,
                    'boarding_status' => $normalized['boarding_status'],
                    'guardian_email' => $normalized['guardian_email'],
                    'guardian_name' => $normalized['guardian_name'],
                ];

                if ($this->importProcessId && $index % 50 === 0) {
                    \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['processed_rows' => $index]);
                }
            }

            // 2. PHASE TWO: DB Execution
            DB::transaction(function () use ($guardianUsersToUpsert, $studentsToUpsert, $enrollmentMap, &$emailsToDispatch) {
                // Bulk Users (Guardian Accounts)
                if (!empty($guardianUsersToUpsert)) {
                    User::upsert($guardianUsersToUpsert, ['email'], ['name', 'phone']);
                    $foundUsers = User::whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
                    
                    $roleParent = Role::where('name', 'parent')->first();
                    
                    $gToUpsert = [];
                    foreach ($foundUsers as $email => $user) {
                        if ($roleParent) $user->assignRole($roleParent);
                        $emailsToDispatch[] = ['user' => $user, 'email' => $email];

                        $gToUpsert[] = [
                            'user_id' => $user->id,
                            'school_id' => $this->schoolId,
                            'first_name' => $user->name,
                            'last_name' => 'Guardian',
                            'email' => $email,
                            'phone' => $user->phone,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    Guardian::upsert($gToUpsert, ['email', 'school_id'], ['user_id', 'phone']);
                }

                // Bulk Students
                Student::upsert($studentsToUpsert, ['admission_number', 'school_id'], [
                    'first_name', 'middle_name', 'last_name', 'upi', 'gender', 'date_of_birth', 
                    'birth_certificate_number', 'current_class_id', 'boarding_status'
                ]);

                // Retrieve for Enrollments & Relationships
                $adms = collect($studentsToUpsert)->pluck('admission_number')->toArray();
                $foundStudents = Student::whereIn('admission_number', $adms)->where('school_id', $this->schoolId)->get()->keyBy('admission_number');
                $foundGuardians = Guardian::where('school_id', $this->schoolId)->whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
                
                $enrollments = [];
                $currentTermId = DB::table('academic_terms')->where('school_id', $this->schoolId)->where('is_current', true)->value('id');

                foreach ($foundStudents as $adm => $student) {
                    $map = $enrollmentMap[$adm];
                    if ($map['class_id']) {
                        $enrollments[] = [
                            'school_id' => $this->schoolId,
                            'student_id' => $student->id,
                            'academic_year_id' => $this->academicYearId,
                            'class_id' => $map['class_id'],
                            'academic_term_id' => $currentTermId,
                            'enrollment_date' => now()->toDateString(),
                            'enrollment_type' => 'new',
                            'status' => 'active',
                            'enrolled_by' => $this->userId,
                            'boarding_status' => $map['boarding_status'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Attach Guardian
                    if ($map['guardian_email'] && isset($foundGuardians[$map['guardian_email']])) {
                        $guardianId = $foundGuardians[$map['guardian_email']]->id;
                        DB::table('guardian_student')->updateOrInsert(
                            ['student_id' => $student->id, 'guardian_id' => $guardianId],
                            [
                                'relationship' => 'guardian',
                                'is_primary_contact' => true,
                                'is_emergency_contact' => true,
                                'can_pickup' => true,
                                'receives_reports' => true,
                                'receives_fees_notification' => true,
                                'is_fee_payer' => true,
                                'updated_at' => now()
                            ]
                        );
                    }
                }
                \App\Models\StudentEnrollment::upsert($enrollments, ['student_id', 'academic_year_id', 'school_id'], ['class_id', 'status', 'boarding_status']);
            });

            // 3. PHASE THREE: Deferred Emails
            foreach ($emailsToDispatch as $index => $item) {
                try {
                    Mail::to($item['email'])->later(now()->addSeconds($index * 0.5), new UserCreatedMail($item['user'], 'Set your password via login screen'));
                } catch (\Exception $e) {
                    \Log::warning("Delayed email failed for guardian {$item['email']}: " . $e->getMessage());
                }
            }

            Storage::delete($this->filePath);
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'completed', 'processed_rows' => $totalRows]);

        } catch (\Exception $e) {
            \Log::error('ImportStudentsJob Error: ' . $e->getMessage());
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    protected function parseLearnerCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) throw new \RuntimeException('Unable to read CSV.');
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

    protected function normalizeLearnerImportRow(array $row, int $line): array
    {
        return [
            'first_name' => trim($row['first_name'] ?? ''),
            'middle_name' => $this->nullableValue($row['middle_name'] ?? null),
            'last_name' => trim($row['last_name'] ?? ''),
            'admission_number' => trim($row['admission_number'] ?? ''),
            'upi' => $this->nullableValue($row['upi'] ?? null),
            'gender' => strtolower(trim($row['gender'] ?? 'male')),
            'date_of_birth' => trim($row['date_of_birth'] ?? ''),
            'birth_certificate_number' => $this->nullableValue($row['birth_certificate_number'] ?? null),
            'nationality' => $this->nullableValue($row['nationality'] ?? null),
            'religion' => $this->nullableValue($row['religion'] ?? null),
            'primary_language' => $this->nullableValue($row['primary_language'] ?? null),
            'secondary_language' => $this->nullableValue($row['secondary_language'] ?? null),
            'home_address' => $this->nullableValue($row['home_address'] ?? null),
            'county' => $this->nullableValue($row['county'] ?? null),
            'sub_county' => $this->nullableValue($row['sub_county'] ?? null),
            'ward' => $this->nullableValue($row['ward'] ?? null),
            'blood_group' => $this->nullableValue($row['blood_group'] ?? null),
            'medical_conditions' => $this->nullableValue($row['medical_conditions'] ?? null),
            'allergies' => $this->nullableValue($row['allergies'] ?? null),
            'grade_name' => $this->nullableValue($row['grade_name'] ?? null),
            'grade_code' => $this->nullableValue($row['grade_code'] ?? null),
            'grade_level_order' => is_numeric($row['grade_level_order'] ?? null) ? (int) $row['grade_level_order'] : null,
            'grade_category' => $row['grade_category'] ?? 'General',
            'stream_name' => $this->nullableValue($row['stream_name'] ?? null),
            'stream_code' => $this->nullableValue($row['stream_code'] ?? null),
            'class_name' => $this->nullableValue($row['class_name'] ?? null),
            'class_code' => $this->nullableValue($row['class_code'] ?? null),
            'boarding_status' => strtolower(trim($row['boarding_status'] ?? 'day')),
            'admission_date' => $this->nullableValue($row['admission_date'] ?? null),
            'status' => strtolower(trim($row['status'] ?? 'active')),
            'guardian_name' => $this->nullableValue($row['guardian_name'] ?? null),
            'guardian_email' => $this->nullableValue($row['guardian_email'] ?? null),
            'guardian_phone' => $this->nullableValue($row['guardian_phone'] ?? null),
        ];
    }

    protected function firstOrCreateImportGrade(int $schoolId, array $data): ?GradeLevel
    {
        if (!$data['grade_name']) return null;
        $ck = $data['grade_name'] . ($data['grade_code'] ?? '');
        if (isset($this->gradeCache[$ck])) return $this->gradeCache[$ck];
        $grade = GradeLevel::firstOrCreate(['school_id' => $schoolId, 'name' => $data['grade_name']], [
            'code' => $data['grade_code'] ?: Str::upper(Str::slug($data['grade_name'], '')),
            'level_order' => $data['grade_level_order'] ?? ((int) GradeLevel::max('level_order') + 1),
            'category' => $data['grade_category'], 'is_active' => true
        ]);
        return $this->gradeCache[$ck] = $grade;
    }

    protected function firstOrCreateImportStream(int $schoolId, array $data): ?Stream
    {
        if (!$data['stream_name']) return null;
        $ck = $data['stream_name'] . ($data['stream_code'] ?? '');
        if (isset($this->streamCache[$ck])) return $this->streamCache[$ck];
        $stream = Stream::firstOrCreate(['school_id' => $schoolId, 'name' => $data['stream_name']], [
            'code' => $data['stream_code'] ?: Str::upper(Str::substr($data['stream_name'], 0, 3)), 'capacity' => 40, 'is_active' => true
        ]);
        return $this->streamCache[$ck] = $stream;
    }

    protected function firstOrCreateImportClass(int $schoolId, int $yearId, ?GradeLevel $g, ?Stream $s, array $data): ?SchoolClass
    {
        if (!$g || !$data['class_name']) return null;
        $ck = $yearId . '_' . $g->id . '_' . ($s?->id ?? 0) . '_' . $data['class_name'];
        if (isset($this->classCache[$ck])) return $this->classCache[$ck];
        $q = SchoolClass::where('school_id', $schoolId)->where('academic_year_id', $yearId)->where('grade_level_id', $g->id)->where('name', $data['class_name']);
        if ($s) $q->where('stream_id', $s->id);
        $class = $q->first() ?: SchoolClass::create([
            'school_id' => $schoolId, 'grade_level_id' => $g->id, 'stream_id' => $s?->id, 'academic_year_id' => $yearId,
            'name' => $data['class_name'], 'code' => $data['class_code'] ?: Str::upper(Str::slug($data['class_name'], '')), 'capacity' => 40, 'is_active' => true
        ]);
        return $this->classCache[$ck] = $class;
    }

    protected function nullableValue(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }
}
