<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RealDataSeeder extends Seeder
{
    private $schoolId;

    // Kenyan names data
    private $maleFirstNames = [
        'Brian', 'Kevin', 'Dennis', 'Peter', 'James', 'John', 'David', 'Michael', 'Daniel', 'Joseph',
        'Samuel', 'Stephen', 'Paul', 'Mark', 'Andrew', 'Philip', 'George', 'Charles', 'Henry', 'Robert',
        'Victor', 'Emmanuel', 'Patrick', 'Francis', 'Timothy', 'Simon', 'Martin', 'Benjamin', 'Nicholas', 'Vincent'
    ];

    private $femaleFirstNames = [
        'Mary', 'Jane', 'Grace', 'Faith', 'Joy', 'Hope', 'Mercy', 'Ruth', 'Esther', 'Sarah',
        'Nancy', 'Lucy', 'Agnes', 'Catherine', 'Elizabeth', 'Margaret', 'Rose', 'Florence', 'Dorothy', 'Alice'
    ];

    private $lastNames = [
        'Kamau', 'Wanjiku', 'Ochieng', 'Mwangi', 'Kiprop', 'Chebet', 'Mutua', 'Wafula', 'Otieno', 'Adhiambo',
        'Kimani', 'Njeri', 'Koech', 'Chepkorir', 'Gitau', 'Wangui', 'Oloo', 'Anyango', 'Kariuki', 'Nyambura',
        'Kipruto', 'Jepkosgei', 'Maina', 'Wambui', 'Rotich', 'Cheruiyot', 'Ngugi', 'Achieng', 'Kiptoo', 'Jepchirchir'
    ];

    private $counties = ['Nairobi', 'Kiambu', 'Nakuru', 'Mombasa', 'Kisumu', 'Machakos', 'Kajiado', 'Nyeri', 'Meru'];

    public function run(): void
    {
        $this->command->info('Starting Real Data Seeder...');

        // Create school first
        $this->createSchool();

        // Seed academic data
        $this->seedAcademicData();
        $this->seedTeachers();
        $this->seedGuardians();
        $this->seedStudents();
        $this->seedAttendance();
        $this->seedFees();
        $this->seedAnnouncements();
        $this->seedEvents();

        // Create admin user
        $this->createAdminUser();

        $this->command->info('Real Data Seeder completed!');
    }

    private function createSchool(): void
    {
        $this->command->info('Creating school...');

        // Create school type if not exists
        $schoolTypeId = DB::table('school_types')->insertGetId([
            'name' => 'Primary School',
            'code' => 'PRI',
            'description' => 'Primary Education (Grade 1-6)',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create school level if not exists
        $schoolLevelId = DB::table('school_levels')->insertGetId([
            'name' => 'CBC Primary',
            'code' => 'CBC-PRI',
            'description' => 'Competency Based Curriculum Primary',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create school
        $this->schoolId = DB::table('schools')->insertGetId([
            'name' => 'CBC Academy Nairobi',
            'code' => 'CBCA001',
            'school_type_id' => $schoolTypeId,
            'school_level_id' => $schoolLevelId,
            'email' => 'info@cbcacademy.ac.ke',
            'phone' => '+254712345678',
            'address' => 'Westlands, Nairobi',
            'county' => 'Nairobi',
            'sub_county' => 'Westlands',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Created school with ID: {$this->schoolId}");
    }

    private function seedAcademicData(): void
    {
        $this->command->info('Seeding academic data...');

        // Academic Year
        $yearId = DB::table('academic_years')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => '2026',
            'start_date' => '2026-01-06',
            'end_date' => '2026-11-27',
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Academic Terms
        DB::table('academic_terms')->insert([
            ['school_id' => $this->schoolId, 'academic_year_id' => $yearId, 'name' => 'Term 1', 'start_date' => '2026-01-06', 'end_date' => '2026-04-03', 'is_current' => true, 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'academic_year_id' => $yearId, 'name' => 'Term 2', 'start_date' => '2026-05-04', 'end_date' => '2026-08-07', 'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'academic_year_id' => $yearId, 'name' => 'Term 3', 'start_date' => '2026-09-01', 'end_date' => '2026-11-27', 'is_current' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Grade Levels
        for ($i = 1; $i <= 9; $i++) {
            DB::table('grade_levels')->insert([
                'school_id' => $this->schoolId,
                'name' => "Grade $i",
                'code' => "G$i",
                'level_order' => $i,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Departments
        $departments = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Languages', 'code' => 'LANG'],
            ['name' => 'Sciences', 'code' => 'SCI'],
            ['name' => 'Humanities', 'code' => 'HUM'],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insert([
                'school_id' => $this->schoolId,
                'name' => $dept['name'],
                'code' => $dept['code'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Streams
        foreach (['East', 'West'] as $stream) {
            DB::table('streams')->insert([
                'school_id' => $this->schoolId,
                'name' => $stream,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // School Classes
        $grades = DB::table('grade_levels')->where('school_id', $this->schoolId)->get();
        $streams = DB::table('streams')->where('school_id', $this->schoolId)->get();
        $academicYear = DB::table('academic_years')->where('school_id', $this->schoolId)->where('is_current', true)->first();

        foreach ($grades as $grade) {
            foreach ($streams as $stream) {
                DB::table('classes')->insert([
                    'school_id' => $this->schoolId,
                    'name' => "{$grade->name} {$stream->name}",
                    'grade_level_id' => $grade->id,
                    'stream_id' => $stream->id,
                    'academic_year_id' => $academicYear->id,
                    'capacity' => 45,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Created academic structure: 9 grades, 18 classes');
    }

    private function seedTeachers(): void
    {
        $this->command->info('Seeding teachers...');

        $departments = DB::table('departments')->where('school_id', $this->schoolId)->get();
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
                    'staff_number' => 'TCH' . str_pad($count, 4, '0', STR_PAD_LEFT),
                    'tsc_number' => 'TSC' . rand(100000, 999999),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'date_of_birth' => Carbon::now()->subYears(rand(28, 55))->format('Y-m-d'),
                    'id_number' => rand(10000000, 40000000),
                    'email' => strtolower($firstName . '.' . $lastName . $count) . '@school.ac.ke',
                    'phone' => '+2547' . rand(10000000, 99999999),
                    'department_id' => $dept->id,
                    'date_joined' => Carbon::now()->subYears(rand(1, 10))->format('Y-m-d'),
                    'contract_type' => rand(0, 1) ? 'permanent' : 'contract',
                    'employment_type' => 'full_time',
                    'status' => 'active',
                    'county' => $this->counties[array_rand($this->counties)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("Created $count teachers");
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
                'id_number' => rand(10000000, 40000000),
                'occupation' => $occupations[array_rand($occupations)],
                'county' => $this->counties[array_rand($this->counties)],
                'status' => 'active',
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
                    'current_class_id' => $class->id,
                    'admission_class_id' => $class->id,
                    'admission_date' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                    'boarding_status' => rand(0, 4) > 0 ? 'day' : 'boarding',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link to guardian
                if (!empty($guardians) && Schema::hasTable('student_guardian')) {
                    DB::table('student_guardian')->insert([
                        'student_id' => $studentId,
                        'guardian_id' => $guardians[array_rand($guardians)],
                        'relationship' => rand(0, 1) ? 'Father' : 'Mother',
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

        $this->command->info("Created $count students");
    }

    private function seedAttendance(): void
    {
        if (!Schema::hasTable('student_attendance')) {
            $this->command->warn('student_attendance table not found, skipping...');
            return;
        }

        $this->command->info('Seeding attendance...');

        $students = DB::table('students')->where('school_id', $this->schoolId)->where('status', 'active')->get();
        $term = DB::table('academic_terms')->where('school_id', $this->schoolId)->where('is_current', true)->first();
        $count = 0;

        // Last 2 weeks of attendance
        $startDate = Carbon::now()->subWeeks(2)->startOfWeek();

        foreach ($students as $student) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte(Carbon::now())) {
                if (!$currentDate->isWeekend()) {
                    $isPresent = rand(1, 100) <= 95;

                    DB::table('student_attendance')->insert([
                        'school_id' => $this->schoolId,
                        'student_id' => $student->id,
                        'class_id' => $student->current_class_id,
                        'academic_term_id' => $term->id ?? 1,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => $isPresent ? 'present' : 'absent',
                        'check_in_time' => $isPresent ? '07:15:00' : null,
                        'is_late' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
                $currentDate->addDay();
            }
        }

        $this->command->info("Created $count attendance records");
    }

    private function seedFees(): void
    {
        if (!Schema::hasTable('fee_structures') || !Schema::hasTable('student_fees') || !Schema::hasTable('payments')) {
            $this->command->warn('Fee tables not found, skipping...');
            return;
        }

        $this->command->info('Seeding fees...');

        // Create fee category first
        $feeCategoryId = DB::table('fee_categories')->insertGetId([
            'school_id' => $this->schoolId,
            'name' => 'Tuition Fee',
            'code' => 'TUI',
            'is_mandatory' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $grades = DB::table('grade_levels')->where('school_id', $this->schoolId)->get();
        $term = DB::table('academic_terms')->where('school_id', $this->schoolId)->where('is_current', true)->first();
        $year = DB::table('academic_years')->where('school_id', $this->schoolId)->where('is_current', true)->first();

        // Create fee structures
        foreach ($grades as $grade) {
            $baseFee = 15000 + ($grade->level_order * 2000);

            DB::table('fee_structures')->insert([
                'school_id' => $this->schoolId,
                'name' => "{$grade->name} Fee Structure 2026",
                'academic_year_id' => $year->id ?? 1,
                'grade_level_id' => $grade->id,
                'total_amount' => $baseFee + 8500,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create student fees and payments
        $students = DB::table('students')->where('school_id', $this->schoolId)->where('status', 'active')->get();
        $paymentCount = 0;

        foreach ($students as $student) {
            $class = DB::table('classes')->where('id', $student->current_class_id)->first();
            if (!$class) continue;

            $feeStructure = DB::table('fee_structures')->where('school_id', $this->schoolId)->where('grade_level_id', $class->grade_level_id)->first();
            if (!$feeStructure) continue;

            $totalAmount = $feeStructure->total_amount ?? 25000;
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
                'school_id' => $this->schoolId,
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'academic_term_id' => $term->id ?? 1,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance' => $totalAmount - $paidAmount,
                'due_date' => '2026-02-15',
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($paidAmount > 0) {
                $paymentCount++;
                DB::table('payments')->insert([
                    'school_id' => $this->schoolId,
                    'student_id' => $student->id,
                    'student_fee_id' => $studentFeeId,
                    'academic_term_id' => $term->id ?? 1,
                    'receipt_number' => 'RCP' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT),
                    'amount' => $paidAmount,
                    'payment_date' => Carbon::now()->subDays(rand(1, 60))->format('Y-m-d'),
                    'payment_method' => ['cash', 'mpesa', 'bank_transfer'][rand(0, 2)],
                    'status' => 'confirmed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("Created fee records with $paymentCount payments");
    }

    private function seedAnnouncements(): void
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        DB::table('announcements')->insert([
            ['school_id' => $this->schoolId, 'title' => 'Term 1 Examinations Schedule', 'content' => 'End of Term 1 examinations will begin on March 23rd, 2026.', 'type' => 'academic', 'priority' => 'high', 'target_audience' => 'all', 'is_published' => true, 'publish_date' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Parents Meeting - March 28th', 'content' => 'All parents are invited for the Term 1 Academic Day.', 'type' => 'general', 'priority' => 'high', 'target_audience' => 'all', 'is_published' => true, 'publish_date' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Sports Day 2026', 'content' => 'Annual Sports Day will be held on March 20th, 2026.', 'type' => 'event', 'priority' => 'normal', 'target_audience' => 'all', 'is_published' => true, 'publish_date' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Fee Payment Reminder', 'content' => 'Parents are reminded to clear outstanding fee balances.', 'type' => 'finance', 'priority' => 'urgent', 'target_audience' => 'all', 'is_published' => true, 'publish_date' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Created 4 announcements');
    }

    private function seedEvents(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        DB::table('events')->insert([
            ['school_id' => $this->schoolId, 'title' => 'Term 1 Examinations', 'event_type_id' => 1, 'start_date' => '2026-03-23', 'end_date' => '2026-03-27', 'is_all_day' => true, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Staff Meeting', 'event_type_id' => 1, 'start_date' => '2026-03-13', 'start_time' => '14:00:00', 'end_time' => '16:00:00', 'is_all_day' => false, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Sports Day 2026', 'event_type_id' => 1, 'start_date' => '2026-03-20', 'is_all_day' => true, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Parents Meeting', 'event_type_id' => 1, 'start_date' => '2026-03-28', 'start_time' => '08:00:00', 'end_time' => '12:00:00', 'is_all_day' => false, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
            ['school_id' => $this->schoolId, 'title' => 'Good Friday Holiday', 'event_type_id' => 1, 'start_date' => '2026-04-03', 'is_all_day' => true, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Created 5 events');
    }

    private function createAdminUser(): void
    {
        $this->command->info('Creating admin user...');

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@cbcplatform.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Admin user created: admin@cbcplatform.com / password');
    }
}
