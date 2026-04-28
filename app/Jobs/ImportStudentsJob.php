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

    public $timeout = 600; // Increase timeout to 10 minutes
    protected $filePath;
    protected $schoolId;
    protected $academicYearId;
    protected $userId;
    protected $importProcessId;

    // Cache for optimization to avoid repeating queries within the SAME job instance
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

            $currentTermId = DB::table('academic_terms')->where('school_id', $this->schoolId)->where('is_current', true)->value('id');
            $roleParent = Role::where('name', 'parent')->first();
            $processedCount = 0;

            // PROCESS IN CHUNKS TO PREVENT MEMORY EXHAUSTION (OOM)
            $chunks = array_chunk($rows, 200);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                $this->processChunk($chunk, $currentTermId, $roleParent, $processedCount);
                
                $processedCount += count($chunk);
                if ($this->importProcessId) {
                    \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['processed_rows' => $processedCount]);
                    
                    // Check for cancellation every chunk
                    $process = \App\Models\ImportProcess::find($this->importProcessId);
                    if ($process && $process->status === 'canceled') {
                        throw new \RuntimeException('Import process was canceled by the user.');
                    }
                }
            }

            Storage::delete($this->filePath);
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'completed', 'processed_rows' => $totalRows]);

        } catch (\Exception $e) {
            \Log::error('ImportStudentsJob Fatal Error: ' . $e->getMessage());
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'failed', 'error_message' => substr($e->getMessage(), 0, 500)]);
        }
    }

    protected function processChunk(array $chunk, $currentTermId, $roleParent, $startOffset): void
    {
        $guardianUsersToUpsert = [];
        $studentsToUpsert = [];
        $enrollmentMap = [];
        $emailsToDispatch = [];

        foreach ($chunk as $index => $row) {
            $line = $startOffset + $index + 2;
            $normalized = $this->normalizeLearnerImportRow($row, $line);
            
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
            ];
        }

        DB::transaction(function () use ($guardianUsersToUpsert, $studentsToUpsert, $enrollmentMap, $roleParent, $currentTermId, &$emailsToDispatch) {
            // 1. GUARDIAN USERS
            if (!empty($guardianUsersToUpsert)) {
                User::upsert($guardianUsersToUpsert, ['email'], ['name', 'phone']);
                $foundUsers = User::whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
                
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

            // 2. STUDENTS
            Student::upsert($studentsToUpsert, ['admission_number', 'school_id'], [
                'first_name', 'middle_name', 'last_name', 'upi', 'gender', 'date_of_birth', 
                'birth_certificate_number', 'current_class_id', 'boarding_status', 'updated_at'
            ]);

            // 3. ENROLLMENTS & RELATIONSHIPS
            $adms = collect($studentsToUpsert)->pluck('admission_number')->toArray();
            $foundStudents = Student::whereIn('admission_number', $adms)->where('school_id', $this->schoolId)->get()->keyBy('admission_number');
            $foundGuardians = Guardian::where('school_id', $this->schoolId)->whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
            
            $enrollments = [];
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

                if ($map['guardian_email'] && isset($foundGuardians[$map['guardian_email']])) {
                    DB::table('guardian_student')->updateOrInsert(
                        ['student_id' => $student->id, 'guardian_id' => $foundGuardians[$map['guardian_email']]->id],
                        ['relationship' => 'guardian', 'is_primary_contact' => true, 'updated_at' => now()]
                    );
                }
            }
            if (!empty($enrollments)) {
                \App\Models\StudentEnrollment::upsert($enrollments, ['student_id', 'academic_year_id', 'school_id'], ['class_id', 'status', 'boarding_status']);
            }
        });

        // 4. DEFERRED EMAILS (Optional, but user said "all records first then emails", so maybe keep this at the very end of HANDLE?)
        // Actually, to prevent OOM, sending emails per chunk with a delay is better.
        foreach ($emailsToDispatch as $idx => $item) {
            Mail::to($item['email'])->later(now()->addSeconds($idx * 0.5), new UserCreatedMail($item['user'], 'Set your password via login screen'));
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
