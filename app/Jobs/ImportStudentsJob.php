<?php

namespace App\Jobs;

use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\Stream;
use App\Models\Student;
use App\Models\User;
use App\Models\Guardian;
use App\Mail\ParentNotificationMail;
use Illuminate\Support\Facades\Password;
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

    public $timeout = 600; // Longer for students

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

            $emailsToDispatch = [];
            $chunkSize = 100;
            $chunks = array_chunk($rows, $chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                // 1. Check Cancellation & Update Progress
                if ($this->importProcessId) {
                    $process = \App\Models\ImportProcess::find($this->importProcessId);
                    if ($process && $process->status === 'canceled') {
                    throw new \RuntimeException('Canceled');
                    }
                    $process->update(['processed_rows' => ($chunkIndex * $chunkSize)]);
                }

                $guardianUsersToUpsert = [];
                $studentsToUpsert = [];
                $enrollmentMap = [];

                // 2. Normalize Chunk
                foreach ($chunk as $index => $row) {
                    $line = ($chunkIndex * $chunkSize) + $index + 2;
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
                            'status' => 'active', 'school_id' => $this->schoolId, 'force_password_change' => true,
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }

                    $studentsToUpsert[] = [
                        'school_id' => $this->schoolId, 'first_name' => $normalized['first_name'], 'middle_name' => $normalized['middle_name'],
                        'last_name' => $normalized['last_name'], 'admission_number' => $normalized['admission_number'],
                        'upi' => $normalized['upi'], 'gender' => $normalized['gender'], 'date_of_birth' => $normalized['date_of_birth'],
                        'birth_certificate_number' => $normalized['birth_certificate_number'], 'nationality' => $normalized['nationality'] ?: 'Kenyan',
                        'religion' => $normalized['religion'], 'primary_language' => $normalized['primary_language'], 'secondary_language' => $normalized['secondary_language'],
                        'home_address' => $normalized['home_address'], 'county' => $normalized['county'], 'sub_county' => $normalized['sub_county'],
                        'ward' => $normalized['ward'], 'blood_group' => $normalized['blood_group'], 'medical_conditions' => $normalized['medical_conditions'],
                        'allergies' => $normalized['allergies'], 'admission_date' => $normalized['admission_date'] ?: now()->toDateString(),
                        'boarding_status' => $normalized['boarding_status'], 'status' => $normalized['status'] ?: 'active',
                        'current_class_id' => $class?->id, 'admission_class_id' => $class?->id,
                        'created_at' => now(), 'updated_at' => now(),
                    ];

                    $enrollmentMap[$normalized['admission_number']] = [
                        'class_id' => $class?->id, 'boarding_status' => $normalized['boarding_status'],
                        'guardian_email' => $normalized['guardian_email'], 'guardian_name' => $normalized['guardian_name'],
                    ];
                }

                // 3. DB Batch for Chunk
                DB::transaction(function () use ($guardianUsersToUpsert, $studentsToUpsert, $enrollmentMap, &$emailsToDispatch) {
                    if (!empty($guardianUsersToUpsert)) {
                        User::upsert($guardianUsersToUpsert, ['email'], ['name', 'phone']);
                        $fUsers = User::whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
                        $roleP = Role::where('name', 'parent')->first();
                        $gUpsert = [];
                        foreach ($fUsers as $email => $u) {
                            if ($roleP) $u->assignRole($roleP);
                            $emailsToDispatch[] = ['user' => $u, 'email' => $email];
                            $gUpsert[] = [
                                'user_id' => $u->id, 'school_id' => $this->schoolId, 'first_name' => $u->name, 'last_name' => 'Guardian',
                                'email' => $email, 'phone' => $u->phone, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                            ];
                        }
                        Guardian::upsert($gUpsert, ['email', 'school_id'], ['user_id', 'phone']);
                    }

                    Student::upsert($studentsToUpsert, ['admission_number', 'school_id'], [
                        'first_name', 'middle_name', 'last_name', 'upi', 'date_of_birth', 'birth_certificate_number', 'current_class_id', 'boarding_status'
                    ]);

                    $stds = Student::whereIn('admission_number', collect($studentsToUpsert)->pluck('admission_number'))->where('school_id', $this->schoolId)->get()->keyBy('admission_number');
                    $grds = Guardian::where('school_id', $this->schoolId)->whereIn('email', collect($guardianUsersToUpsert)->pluck('email'))->get()->keyBy('email');
                    
                    $enrolls = [];
                    $termId = DB::table('academic_terms')->where('school_id', $this->schoolId)->where('is_current', true)->value('id');

                    foreach ($stds as $adm => $s) {
                        $m = $enrollmentMap[$adm];
                        if ($m['class_id']) {
                        $enrolls[] = [
                            'school_id' => $this->schoolId, 'student_id' => $s->id, 'academic_year_id' => $this->academicYearId,
                            'class_id' => $m['class_id'], 'academic_term_id' => $termId, 'enrollment_date' => now()->toDateString(),
                            'enrollment_type' => 'new', 'status' => 'active', 'enrolled_by' => $this->userId, 'boarding_status' => $m['boarding_status'],
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                        }
                        if ($m['guardian_email'] && isset($grds[$m['guardian_email']])) {
                        DB::table('student_guardian')->updateOrInsert(['student_id' => $s->id, 'guardian_id' => $grds[$m['guardian_email']]->id], ['relationship' => 'guardian', 'is_primary_contact' => true, 'updated_at' => now()]);
                        }
                    }
                    \App\Models\StudentEnrollment::upsert($enrolls, ['student_id', 'academic_year_id', 'school_id'], ['class_id', 'status', 'boarding_status']);
                });
            }

            // 4. POST-SYNC EMAILS (Send one unique email per guardian)
            $uniqueEmailsToDispatch = collect($emailsToDispatch)->unique('email');
            foreach ($uniqueEmailsToDispatch as $idx => $item) {
                try {
                    $u = $item['user'];
                    $guardian = Guardian::where('user_id', $u->id)->first();
                    if ($guardian) {
                        $students = $guardian->students()->get();
                        $token = Password::createToken($u);
                        $resetUrl = url(route('password.reset', [
                            'token' => $token,
                            'email' => $u->email,
                        ], false));

                        Mail::to($u->email)->later(now()->addSeconds($idx * 0.5), new ParentNotificationMail($guardian, $students, 'As provided during system onboarding', $resetUrl));
                    }
                } catch (\Exception $e) {}
            }

            Storage::delete($this->filePath);
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'completed', 'processed_rows' => $totalRows]);

        } catch (\Exception $e) {
            \Log::error('Import Error: ' . $e->getMessage());
            if ($this->importProcessId) \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    protected function parseLearnerCsv(string $path): array
    {
        $h = fopen($path, 'r');
        $header = array_map(fn ($v) => Str::slug(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $v)), '_'), fgetcsv($h) ?: []);
        $rows = [];
        while (($d = fgetcsv($h)) !== false) {
            if ($d === [null] || count(array_filter($d, fn ($v) => trim((string) $v) !== '')) === 0) continue;
            $r = []; foreach ($header as $i => $c) $r[$c] = isset($d[$i]) ? trim((string) $d[$i]) : null;
            $rows[] = $r;
        }
        fclose($h); return $rows;
    }

    protected function normalizeLearnerImportRow(array $row, int $line): array
    {
        return [
            'first_name' => trim($row['first_name'] ?? ''), 'middle_name' => $this->nullableValue($row['middle_name'] ?? null), 'last_name' => trim($row['last_name'] ?? ''),
            'admission_number' => trim($row['admission_number'] ?? ''), 'upi' => $this->nullableValue($row['upi'] ?? null), 'gender' => strtolower(trim($row['gender'] ?? 'male')),
            'date_of_birth' => trim($row['date_of_birth'] ?? ''), 'birth_certificate_number' => $this->nullableValue($row['birth_certificate_number'] ?? null),
            'nationality' => $this->nullableValue($row['nationality'] ?? null), 'religion' => $this->nullableValue($row['religion'] ?? null),
            'primary_language' => $this->nullableValue($row['primary_language'] ?? null), 'secondary_language' => $this->nullableValue($row['secondary_language'] ?? null),
            'home_address' => $this->nullableValue($row['home_address'] ?? null), 'county' => $this->nullableValue($row['county'] ?? null),
            'sub_county' => $this->nullableValue($row['sub_county'] ?? null), 'ward' => $this->nullableValue($row['ward'] ?? null),
            'blood_group' => $this->nullableValue($row['blood_group'] ?? null), 'medical_conditions' => $this->nullableValue($row['medical_conditions'] ?? null),
            'allergies' => $this->nullableValue($row['allergies'] ?? null), 'grade_name' => $this->nullableValue($row['grade_name'] ?? null),
            'grade_code' => $this->nullableValue($row['grade_code'] ?? null), 'grade_level_order' => is_numeric($row['grade_level_order'] ?? null) ? (int) $row['grade_level_order'] : null,
            'grade_category' => $row['grade_category'] ?? 'General', 'stream_name' => $this->nullableValue($row['stream_name'] ?? null),
            'stream_code' => $this->nullableValue($row['stream_code'] ?? null), 'class_name' => $this->nullableValue($row['class_name'] ?? null),
            'class_code' => $this->nullableValue($row['class_code'] ?? null), 'boarding_status' => strtolower(trim($row['boarding_status'] ?? 'day')),
            'admission_date' => $this->nullableValue($row['admission_date'] ?? null), 'status' => strtolower(trim($row['status'] ?? 'active')),
            'guardian_name' => $this->nullableValue($row['guardian_name'] ?? null), 'guardian_email' => $this->nullableValue($row['guardian_email'] ?? null),
            'guardian_phone' => $this->nullableValue($row['guardian_phone'] ?? null),
        ];
    }

    protected function firstOrCreateImportGrade(int $sId, array $d): ?GradeLevel
    {
        if (!$d['grade_name']) return null; $ck = $d['grade_name'] . ($d['grade_code'] ?? '');
        if (isset($this->gradeCache[$ck])) return $this->gradeCache[$ck];
        return $this->gradeCache[$ck] = GradeLevel::firstOrCreate(['school_id' => $sId, 'name' => $d['grade_name']], ['code' => $d['grade_code'] ?: Str::upper(Str::slug($d['grade_name'], '')), 'level_order' => $d['grade_level_order'] ?? ((int) GradeLevel::max('level_order') + 1), 'category' => $d['grade_category'], 'is_active' => true]);
    }

    protected function firstOrCreateImportStream(int $sId, array $d): ?Stream
    {
        if (!$d['stream_name']) return null; $ck = $d['stream_name'] . ($d['stream_code'] ?? '');
        if (isset($this->streamCache[$ck])) return $this->streamCache[$ck];
        return $this->streamCache[$ck] = Stream::firstOrCreate(['school_id' => $sId, 'name' => $d['stream_name']], ['code' => $d['stream_code'] ?: Str::upper(Str::substr($d['stream_name'], 0, 3)), 'capacity' => 40, 'is_active' => true]);
    }

    protected function firstOrCreateImportClass(int $sId, int $yId, ?GradeLevel $g, ?Stream $s, array $d): ?SchoolClass
    {
        if (!$g || !$d['class_name']) return null; $ck = $yId . '_' . $g->id . '_' . ($s?->id ?? 0) . '_' . $d['class_name'];
        if (isset($this->classCache[$ck])) return $this->classCache[$ck];
        $q = SchoolClass::where('school_id', $sId)->where('academic_year_id', $yId)->where('grade_level_id', $g->id)->where('name', $d['class_name']);
        if ($s) $q->where('stream_id', $s->id);
        return $this->classCache[$ck] = $q->first() ?: SchoolClass::create(['school_id' => $sId, 'grade_level_id' => $g->id, 'stream_id' => $s?->id, 'academic_year_id' => $yId, 'name' => $d['class_name'], 'code' => $d['class_code'] ?: Str::upper(Str::slug($d['class_name'], '')), 'capacity' => 40, 'is_active' => true]);
    }

    protected function nullableValue(mixed $v): ?string { $t = trim((string) $v); return $t === '' ? null : $t; }
}
