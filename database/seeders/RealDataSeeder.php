<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RealDataSeeder extends Seeder
{
    private int $schoolId;
    private int $adminUserId;
    private int $academicYearId;
    private int $academicTermId;
    private int $defaultEventTypeId;
    private int $defaultPaymentMethodId;

    private array $maleFirstNames = [
        'Brian', 'Kevin', 'Dennis', 'Peter', 'James', 'John', 'David', 'Michael', 'Daniel', 'Joseph',
        'Samuel', 'Stephen', 'Paul', 'Mark', 'Andrew', 'Philip', 'George', 'Charles', 'Henry', 'Robert',
        'Victor', 'Emmanuel', 'Patrick', 'Francis', 'Timothy', 'Simon', 'Martin', 'Benjamin', 'Nicholas', 'Vincent',
    ];

    private array $femaleFirstNames = [
        'Mary', 'Jane', 'Grace', 'Faith', 'Joy', 'Hope', 'Mercy', 'Ruth', 'Esther', 'Sarah',
        'Nancy', 'Lucy', 'Agnes', 'Catherine', 'Elizabeth', 'Margaret', 'Rose', 'Florence', 'Dorothy', 'Alice',
    ];

    private array $lastNames = [
        'Kamau', 'Wanjiku', 'Ochieng', 'Mwangi', 'Kiprop', 'Chebet', 'Mutua', 'Wafula', 'Otieno', 'Adhiambo',
        'Kimani', 'Njeri', 'Koech', 'Chepkorir', 'Gitau', 'Wangui', 'Oloo', 'Anyango', 'Kariuki', 'Nyambura',
        'Kipruto', 'Jepkosgei', 'Maina', 'Wambui', 'Rotich', 'Cheruiyot', 'Ngugi', 'Achieng', 'Kiptoo', 'Jepchirchir',
    ];

    private array $counties = ['Nairobi', 'Kiambu', 'Nakuru', 'Mombasa', 'Kisumu', 'Machakos', 'Kajiado', 'Nyeri', 'Meru'];

    public function run(): void
    {
        $this->command->info('Starting Real Data Seeder...');

        $this->createAdminUser();
        $this->createSchool();
        $this->seedAcademicData();
        $this->seedTeachers();
        $this->seedGuardians();
        $this->seedStudents();
        $this->seedAttendance();
        $this->seedFees();
        $this->seedAnnouncements();
        $this->seedEvents();

        $this->command->info('Real Data Seeder completed!');
    }

    private function createAdminUser(): void
    {
        $this->command->info('Creating admin user...');

        $existing = DB::table('users')->where('email', 'admin@cbcplatform.com')->first();

        if ($existing) {
            $this->adminUserId = $existing->id;
            DB::table('users')->where('id', $existing->id)->update([
                'password' => Hash::make('password'),
                'status' => 'active',
                'deleted_at' => null,
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->adminUserId = DB::table('users')->insertGetId([
                'name' => 'Super Admin',
                'email' => 'admin@cbcplatform.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'locale' => 'en',
                'timezone' => 'Africa/Nairobi',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Admin user ready: admin@cbcplatform.com / password');
    }

    private function createSchool(): void
    {
        $this->command->info('Creating school...');

        $existingSchoolType = DB::table('school_types')->where('code', 'PRI')->first();
        $schoolTypeId = $existingSchoolType?->id ?? DB::table('school_types')->insertGetId([
            'name' => 'Primary School',
            'code' => 'PRI',
            'description' => 'Primary Education (Grade 1-9)',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $existingSchoolLevel = DB::table('school_levels')->where('code', 'PVT')->first();
        $schoolLevelId = $existingSchoolLevel?->id ?? DB::table('school_levels')->insertGetId([
            'name' => 'Private',
            'code' => 'PVT',
            'description' => 'Private school level',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $existingSchool = DB::table('schools')->where('code', 'CBCA001')->first();
        if ($existingSchool) {
            $this->schoolId = $existingSchool->id;
        } else {
            $this->schoolId = DB::table('schools')->insertGetId([
                'name' => 'CBC Academy Nairobi',
                'code' => 'CBCA001',
                'registration_number' => 'REG-CBCA-2026',
                'school_type_id' => $schoolTypeId,
                'school_level_id' => $schoolLevelId,
                'motto' => 'Excellence Through Competency',
                'vision' => 'To nurture competent lifelong learners.',
                'mission' => 'To provide quality CBC education in a safe, modern environment.',
                'email' => 'info@cbcacademy.ac.ke',
                'phone' => '+254712345678',
                'website' => 'https://cbcacademy.ac.ke',
                'address' => 'Westlands, Nairobi',
                'county' => 'Nairobi',
                'sub_county' => 'Westlands',
                'gender_type' => 'mixed',
                'boarding_type' => 'day',
                'student_capacity' => 900,
                'status' => 'active',
                'timezone' => 'Africa/Nairobi',
                'currency' => 'KES',
                'locale' => 'en',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('school_contacts')->where('school_id', $this->schoolId)->where('contact_type', 'principal')->exists()) {
            DB::table('school_contacts')->insert([
                'school_id' => $this->schoolId,
                'contact_type' => 'principal',
                'name' => 'Dr. Jane Mwangi',
                'title' => 'Principal',
                'email' => 'principal@cbcacademy.ac.ke',
                'phone' => '+254722000111',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Created school with ID: {$this->schoolId}");
    }

    private function seedAcademicData(): void
    {
        $this->command->info('Seeding academic data...');

        $this->academicYearId = DB::table('academic_years')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => '2026 Academic Year',
            'code' => '2026',
            'start_date' => '2026-01-06',
            'end_date' => '2026-11-27',
            'is_current' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->academicTermId = DB::table('academic_terms')->insertGetId([
            'school_id' => $this->schoolId,
            'academic_year_id' => $this->academicYearId,
            'name' => 'Term 1',
            'term_number' => 1,
            'start_date' => '2026-01-06',
            'end_date' => '2026-04-03',
            'total_weeks' => 13,
            'is_current' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_terms')->insert([
            [
                'school_id' => $this->schoolId,
                'academic_year_id' => $this->academicYearId,
                'name' => 'Term 2',
                'term_number' => 2,
                'start_date' => '2026-05-04',
                'end_date' => '2026-08-07',
                'total_weeks' => 14,
                'is_current' => false,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $this->schoolId,
                'academic_year_id' => $this->academicYearId,
                'name' => 'Term 3',
                'term_number' => 3,
                'start_date' => '2026-09-01',
                'end_date' => '2026-11-27',
                'total_weeks' => 13,
                'is_current' => false,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $gradeMeta = [
            1 => ['Grade 1', 'G1', 'lower_primary', 6, 8],
            2 => ['Grade 2', 'G2', 'lower_primary', 7, 9],
            3 => ['Grade 3', 'G3', 'lower_primary', 8, 10],
            4 => ['Grade 4', 'G4', 'upper_primary', 9, 11],
            5 => ['Grade 5', 'G5', 'upper_primary', 10, 12],
            6 => ['Grade 6', 'G6', 'upper_primary', 11, 13],
            7 => ['Grade 7', 'G7', 'junior_secondary', 12, 14],
            8 => ['Grade 8', 'G8', 'junior_secondary', 13, 15],
            9 => ['Grade 9', 'G9', 'junior_secondary', 14, 16],
        ];

        foreach ($gradeMeta as $order => [$name, $code, $category, $minAge, $maxAge]) {
            DB::table('grade_levels')->insert([
                'school_id' => $this->schoolId,
                'name' => $name,
                'code' => $code,
                'level_order' => $order,
                'category' => $category,
                'minimum_age' => $minAge,
                'maximum_age' => $maxAge,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $departments = [
            ['Mathematics', 'MATH'],
            ['Languages', 'LANG'],
            ['Sciences', 'SCI'],
            ['Humanities', 'HUM'],
        ];

        foreach ($departments as [$name, $code]) {
            DB::table('departments')->insert([
                'school_id' => $this->schoolId,
                'name' => $name,
                'code' => $code,
                'description' => $name . ' Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([['East', 'E'], ['West', 'W']] as [$name, $code]) {
            DB::table('streams')->insert([
                'school_id' => $this->schoolId,
                'name' => $name,
                'code' => $code,
                'capacity' => 45,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $grades = DB::table('grade_levels')->where('school_id', $this->schoolId)->get();
        $streams = DB::table('streams')->where('school_id', $this->schoolId)->get();

        foreach ($grades as $grade) {
            foreach ($streams as $stream) {
                DB::table('classes')->insert([
                    'school_id' => $this->schoolId,
                    'grade_level_id' => $grade->id,
                    'stream_id' => $stream->id,
                    'academic_year_id' => $this->academicYearId,
                    'name' => "{$grade->name} {$stream->name}",
                    'code' => $grade->code . $stream->code,
                    'capacity' => 45,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('school_calendar_events')->insert([
            'school_id' => $this->schoolId,
            'academic_year_id' => $this->academicYearId,
            'academic_term_id' => $this->academicTermId,
            'title' => 'Term 1 Exams Begin',
            'description' => 'Mid-term examination period begins.',
            'event_type' => 'exam',
            'event_date' => '2026-03-23',
            'is_all_day' => true,
            'is_public' => true,
            'color' => '#ef4444',
            'created_by' => $this->adminUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Created academic structure: 9 grades, 18 classes');
    }

    private function seedTeachers(): void
    {
        $this->command->info('Seeding teachers...');

        $departments = DB::table('departments')->where('school_id', $this->schoolId)->get();

        $staffCategoryId = DB::table('staff_categories')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'Teaching Staff',
            'code' => 'TEACH',
            'description' => 'All teaching staff',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $designationId = DB::table('staff_designations')->insertGetId([
            'school_id' => $this->schoolId,
            'staff_category_id' => $staffCategoryId,
            'name' => 'Teacher',
            'code' => 'TCHR',
            'hierarchy_level' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = 0;

        foreach ($departments as $dept) {
            for ($i = 0; $i < 10; $i++) {
                $count++;
                $gender = rand(0, 1) ? 'male' : 'female';
                $firstName = $gender === 'male'
                    ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                    : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];

                DB::table('teachers')->insert([
                    'school_id' => $this->schoolId,
                    'department_id' => $dept->id,
                    'staff_category_id' => $staffCategoryId,
                    'staff_designation_id' => $designationId,
                    'staff_number' => 'TCH' . str_pad($count, 4, '0', STR_PAD_LEFT),
                    'tsc_number' => 'TSC' . rand(100000, 999999),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'date_of_birth' => Carbon::now()->subYears(rand(28, 55))->format('Y-m-d'),
                    'id_number' => (string) rand(10000000, 40000000),
                    'email' => strtolower($firstName . '.' . $lastName . $count) . '@school.ac.ke',
                    'phone' => '+2547' . rand(10000000, 99999999),
                    'county' => $this->counties[array_rand($this->counties)],
                    'date_joined' => Carbon::now()->subYears(rand(1, 10))->format('Y-m-d'),
                    'contract_type' => rand(0, 1) ? 'permanent' : 'contract',
                    'employment_type' => 'full_time',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("Created {$count} teachers");
    }

    private function seedGuardians(): void
    {
        $this->command->info('Seeding guardians...');

        $occupations = ['Teacher', 'Doctor', 'Engineer', 'Farmer', 'Business Owner', 'Accountant', 'Nurse', 'Civil Servant'];

        for ($i = 1; $i <= 400; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $firstName = $gender === 'male'
                ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];

            DB::table('guardians')->insert([
                'school_id' => $this->schoolId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'email' => strtolower($firstName . '.' . $lastName . $i) . '@gmail.com',
                'phone' => '+2547' . rand(10000000, 99999999),
                'id_number' => (string) rand(10000000, 40000000),
                'occupation' => $occupations[array_rand($occupations)],
                'county' => $this->counties[array_rand($this->counties)],
                'relationship_type' => rand(0, 1) ? 'father' : 'mother',
                'is_emergency_contact' => true,
                'can_pickup' => true,
                'receives_communication' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Created 400 guardians');
    }

    private function seedStudents(): void
    {
        $this->command->info('Seeding students...');

        $classes = DB::table('classes')->where('school_id', $this->schoolId)->get();
        $guardians = DB::table('guardians')->where('school_id', $this->schoolId)->pluck('id')->toArray();
        $religions = ['Christian', 'Muslim', 'Hindu'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $count = 0;

        foreach ($classes as $class) {
            $gradeLevel = DB::table('grade_levels')->where('id', $class->grade_level_id)->first();
            $baseAge = 5 + ($gradeLevel->level_order ?? 1);

            for ($i = 0; $i < rand(35, 40); $i++) {
                $count++;
                $gender = rand(0, 1) ? 'male' : 'female';
                $firstName = $gender === 'male'
                    ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                    : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];

                $studentId = DB::table('students')->insertGetId([
                    'school_id' => $this->schoolId,
                    'admission_number' => 'STU' . str_pad($count, 5, '0', STR_PAD_LEFT),
                    'upi' => 'UPI' . rand(1000000000, 9999999999),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'date_of_birth' => Carbon::now()->subYears($baseAge + rand(0, 1))->format('Y-m-d'),
                    'nationality' => 'Kenyan',
                    'religion' => $religions[array_rand($religions)],
                    'county' => $this->counties[array_rand($this->counties)],
                    'admission_date' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'admission_class_id' => $class->id,
                    'current_class_id' => $class->id,
                    'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                    'boarding_status' => rand(0, 4) > 0 ? 'day' : 'boarding',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('student_enrollments')->insert([
                    'student_id' => $studentId,
                    'class_id' => $class->id,
                    'academic_year_id' => $this->academicYearId,
                    'academic_term_id' => $this->academicTermId,
                    'enrollment_date' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'enrollment_type' => 'new',
                    'status' => 'active',
                    'enrolled_by' => $this->adminUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!empty($guardians) && Schema::hasTable('student_guardian')) {
                    DB::table('student_guardian')->insert([
                        'student_id' => $studentId,
                        'guardian_id' => $guardians[array_rand($guardians)],
                        'relationship' => rand(0, 1) ? 'father' : 'mother',
                        'is_primary_contact' => true,
                        'is_emergency_contact' => true,
                        'can_pickup' => true,
                        'receives_reports' => true,
                        'receives_fees_notification' => true,
                        'is_fee_payer' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info("Created {$count} students");
    }

    private function seedAttendance(): void
    {
        if (!Schema::hasTable('student_attendance')) {
            $this->command->warn('student_attendance table not found, skipping...');
            return;
        }

        $this->command->info('Seeding attendance...');

        $students = DB::table('students')->where('school_id', $this->schoolId)->where('status', 'active')->get();
        $count = 0;
        $startDate = Carbon::now()->subWeeks(2)->startOfWeek();

        foreach ($students as $student) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte(Carbon::now())) {
                if (!$currentDate->isWeekend()) {
                    $isPresent = rand(1, 100) <= 95;
                    $status = $isPresent ? (rand(1, 100) <= 5 ? 'late' : 'present') : 'absent';

                    DB::table('student_attendance')->insert([
                        'student_id' => $student->id,
                        'class_id' => $student->current_class_id,
                        'academic_year_id' => $this->academicYearId,
                        'academic_term_id' => $this->academicTermId,
                        'attendance_date' => $currentDate->format('Y-m-d'),
                        'status' => $status,
                        'arrival_time' => $isPresent ? ($status === 'late' ? '07:45:00' : '07:10:00') : null,
                        'marked_by' => $this->adminUserId,
                        'marked_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
                $currentDate->addDay();
            }
        }

        $this->command->info("Created {$count} attendance records");
    }

    private function seedFees(): void
    {
        if (!Schema::hasTable('fee_structures') || !Schema::hasTable('student_fees') || !Schema::hasTable('payments')) {
            $this->command->warn('Fee tables not found, skipping...');
            return;
        }

        $this->command->info('Seeding fees...');

        $feeCategoryId = DB::table('fee_categories')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'description' => 'Core tuition fee',
            'is_mandatory' => true,
            'is_recurring' => true,
            'recurrence_period' => 'term',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $feeTypeId = DB::table('fee_types')->insertGetId([
            'fee_category_id' => $feeCategoryId,
            'name' => 'Standard Tuition',
            'code' => 'STD-TUI',
            'description' => 'Standard tuition charge',
            'default_amount' => 25000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->defaultPaymentMethodId = DB::table('payment_methods')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'M-Pesa',
            'code' => 'MPESA',
            'description' => 'Mobile money payment',
            'requires_reference' => true,
            'is_online' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $grades = DB::table('grade_levels')->where('school_id', $this->schoolId)->get();

        foreach ($grades as $grade) {
            $baseFee = 15000 + ($grade->level_order * 2000);

            $feeStructureId = DB::table('fee_structures')->insertGetId([
                'school_id' => $this->schoolId,
                'academic_year_id' => $this->academicYearId,
                'academic_term_id' => $this->academicTermId,
                'grade_level_id' => $grade->id,
                'name' => "{$grade->name} Fee Structure 2026",
                'description' => 'Tuition, lunch, activities and exams',
                'total_amount' => $baseFee + 8500,
                'student_type' => 'all',
                'is_active' => true,
                'created_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('fee_structure_items')->insert([
                'fee_structure_id' => $feeStructureId,
                'fee_type_id' => $feeTypeId,
                'amount' => $baseFee + 8500,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $students = DB::table('students')->where('school_id', $this->schoolId)->where('status', 'active')->get();
        $paymentCount = 0;

        foreach ($students as $student) {
            $class = DB::table('classes')->where('id', $student->current_class_id)->first();
            if (!$class) {
                continue;
            }

            $feeStructure = DB::table('fee_structures')
                ->where('school_id', $this->schoolId)
                ->where('grade_level_id', $class->grade_level_id)
                ->where('academic_term_id', $this->academicTermId)
                ->first();

            if (!$feeStructure) {
                continue;
            }

            $totalAmount = $feeStructure->total_amount;
            $paymentPercent = rand(0, 100);
            $paidAmount = 0;
            $status = 'pending';

            if ($paymentPercent > 70) {
                $paidAmount = $totalAmount;
                $status = 'paid';
            } elseif ($paymentPercent > 30) {
                $paidAmount = round($totalAmount * (rand(40, 80) / 100), -2);
                $status = 'partial';
            }

            $studentFeeId = DB::table('student_fees')->insertGetId([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'academic_term_id' => $this->academicTermId,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'net_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance' => $totalAmount - $paidAmount,
                'due_date' => '2026-02-15',
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($paidAmount > 0) {
                $paymentCount++;
                $paymentId = DB::table('payments')->insertGetId([
                    'school_id' => $this->schoolId,
                    'student_id' => $student->id,
                    'student_fee_id' => $studentFeeId,
                    'payment_method_id' => $this->defaultPaymentMethodId,
                    'receipt_number' => 'RCP' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT),
                    'transaction_reference' => 'MP' . strtoupper(substr(md5((string) $paymentCount), 0, 8)),
                    'payment_date' => Carbon::now()->subDays(rand(1, 60))->format('Y-m-d'),
                    'amount' => $paidAmount,
                    'currency' => 'KES',
                    'status' => 'completed',
                    'received_by' => $this->adminUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('receipts')->insert([
                    'payment_id' => $paymentId,
                    'receipt_number' => 'RCP' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT),
                    'receipt_date' => Carbon::now()->subDays(rand(1, 60))->format('Y-m-d'),
                    'payer_name' => $student->first_name . ' ' . $student->last_name,
                    'description' => 'School fees payment',
                    'amount' => $paidAmount,
                    'issued_by' => $this->adminUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("Created fee records with {$paymentCount} payments");
    }

    private function seedAnnouncements(): void
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        $announcementId = DB::table('announcements')->insertGetId([
            'school_id' => $this->schoolId,
            'title' => 'Term 1 Examinations Schedule',
            'content' => 'End of Term 1 examinations will begin on March 23rd, 2026.',
            'priority' => 'high',
            'type' => 'academic',
            'created_by' => $this->adminUserId,
            'publish_at' => now(),
            'is_published' => true,
            'is_pinned' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('announcement_targets')->insert([
            'announcement_id' => $announcementId,
            'target_type' => 'all',
            'target_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('announcements')->insert([
            [
                'school_id' => $this->schoolId,
                'title' => 'Parents Meeting - March 28th',
                'content' => 'All parents are invited for the Term 1 Academic Day.',
                'priority' => 'high',
                'type' => 'general',
                'created_by' => $this->adminUserId,
                'publish_at' => now(),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $this->schoolId,
                'title' => 'Sports Day 2026',
                'content' => 'Annual Sports Day will be held on March 20th, 2026.',
                'priority' => 'normal',
                'type' => 'event',
                'created_by' => $this->adminUserId,
                'publish_at' => now(),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $this->schoolId,
                'title' => 'Fee Payment Reminder',
                'content' => 'Parents are reminded to clear outstanding fee balances.',
                'priority' => 'urgent',
                'type' => 'notice',
                'created_by' => $this->adminUserId,
                'publish_at' => now(),
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Created 4 announcements');
    }

    private function seedEvents(): void
    {
        if (!Schema::hasTable('event_types') || !Schema::hasTable('events')) {
            return;
        }

        $this->defaultEventTypeId = DB::table('event_types')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'General Event',
            'code' => 'GEN',
            'color' => '#3b82f6',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $venueId = DB::table('event_venues')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'Main Hall',
            'code' => 'MH',
            'capacity' => 500,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('events')->insert([
            [
                'school_id' => $this->schoolId,
                'event_type_id' => $this->defaultEventTypeId,
                'venue_id' => $venueId,
                'title' => 'Term 1 Examinations',
                'start_date' => '2026-03-23',
                'end_date' => '2026-03-27',
                'is_all_day' => true,
                'status' => 'scheduled',
                'created_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $this->schoolId,
                'event_type_id' => $this->defaultEventTypeId,
                'venue_id' => $venueId,
                'title' => 'Staff Meeting',
                'start_date' => '2026-03-13',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'is_all_day' => false,
                'status' => 'scheduled',
                'created_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $this->schoolId,
                'event_type_id' => $this->defaultEventTypeId,
                'venue_id' => $venueId,
                'title' => 'Sports Day 2026',
                'start_date' => '2026-03-20',
                'is_all_day' => true,
                'status' => 'scheduled',
                'created_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Created events');
    }
}
