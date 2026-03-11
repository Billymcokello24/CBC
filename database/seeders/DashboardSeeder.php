<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\GradeLevel;
use App\Models\Academic\Department;
use App\Models\Finance\FeePayment;
use App\Models\Finance\StudentFee;
use App\Models\Finance\FeeStructure;
use App\Models\Attendance\StudentAttendance;
use App\Models\Communication\Announcement;
use App\Models\Communication\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Dashboard data...');

        // Create Academic Year
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026'],
            [
                'start_date' => '2026-01-06',
                'end_date' => '2026-11-27',
                'is_current' => true,
            ]
        );

        // Create Academic Terms
        $term1 = AcademicTerm::firstOrCreate(
            ['name' => 'Term 1', 'academic_year_id' => $academicYear->id],
            [
                'start_date' => '2026-01-06',
                'end_date' => '2026-04-03',
                'is_current' => true,
            ]
        );

        // Create Grade Levels
        $gradeLevels = [];
        for ($i = 1; $i <= 9; $i++) {
            $gradeLevels[$i] = GradeLevel::firstOrCreate(
                ['name' => "Grade $i"],
                ['level_order' => $i, 'is_active' => true]
            );
        }

        // Create Departments
        $departments = [
            'Mathematics' => Department::firstOrCreate(['name' => 'Mathematics'], ['code' => 'MATH', 'is_active' => true]),
            'Languages' => Department::firstOrCreate(['name' => 'Languages'], ['code' => 'LANG', 'is_active' => true]),
            'Sciences' => Department::firstOrCreate(['name' => 'Sciences'], ['code' => 'SCI', 'is_active' => true]),
            'Humanities' => Department::firstOrCreate(['name' => 'Humanities'], ['code' => 'HUM', 'is_active' => true]),
        ];

        // Create Classes
        $classes = [];
        $streams = ['East', 'West'];
        foreach ($gradeLevels as $level => $gradeLevel) {
            foreach ($streams as $stream) {
                $classes[] = SchoolClass::firstOrCreate(
                    ['name' => "Grade $level $stream"],
                    [
                        'grade_level_id' => $gradeLevel->id,
                        'academic_year_id' => $academicYear->id,
                        'capacity' => 40,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Created ' . count($classes) . ' classes.');

        // Create Students
        $firstNames = ['John', 'Mary', 'Brian', 'Faith', 'Kevin', 'Grace', 'Peter', 'Jane', 'David', 'Sarah',
                       'Michael', 'Nancy', 'James', 'Ann', 'Daniel', 'Ruth', 'Samuel', 'Esther', 'Joseph', 'Mercy',
                       'Paul', 'Joyce', 'Mark', 'Lydia', 'Stephen', 'Hannah', 'Andrew', 'Naomi', 'Philip', 'Martha'];
        $lastNames = ['Kamau', 'Wanjiku', 'Ochieng', 'Akinyi', 'Mwangi', 'Njeri', 'Kiprop', 'Chebet', 'Mutua', 'Wafula',
                      'Otieno', 'Adhiambo', 'Kimani', 'Nyambura', 'Koech', 'Chepkorir', 'Gitau', 'Wangui', 'Oloo', 'Anyango'];

        $studentsCreated = 0;
        foreach ($classes as $class) {
            for ($i = 0; $i < rand(25, 35); $i++) {
                $gender = rand(0, 1) ? 'male' : 'female';
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];

                Student::firstOrCreate(
                    ['admission_number' => 'STU' . str_pad($studentsCreated + 1, 5, '0', STR_PAD_LEFT)],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'date_of_birth' => Carbon::now()->subYears(rand(6, 14))->subDays(rand(1, 365)),
                        'admission_date' => Carbon::now()->subMonths(rand(1, 24)),
                        'current_class_id' => $class->id,
                        'status' => 'active',
                        'boarding_status' => rand(0, 1) ? 'day' : 'boarding',
                    ]
                );
                $studentsCreated++;
            }
        }

        $this->command->info("Created $studentsCreated students.");

        // Create Teachers
        $teacherFirstNames = ['James', 'Elizabeth', 'Peter', 'Margaret', 'John', 'Catherine', 'David', 'Susan', 'Robert', 'Patricia'];
        $teacherLastNames = ['Ochieng', 'Wanjiku', 'Kiprop', 'Adhiambo', 'Mwangi', 'Chebet', 'Mutua', 'Anyango', 'Kimani', 'Nyambura'];

        $teachersCreated = 0;
        foreach ($departments as $deptName => $department) {
            for ($i = 0; $i < rand(8, 12); $i++) {
                $gender = rand(0, 1) ? 'male' : 'female';
                $firstName = $teacherFirstNames[array_rand($teacherFirstNames)];
                $lastName = $teacherLastNames[array_rand($teacherLastNames)];

                Teacher::firstOrCreate(
                    ['staff_number' => 'TCH' . str_pad($teachersCreated + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'date_of_birth' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(1, 365)),
                        'date_joined' => Carbon::now()->subMonths(rand(6, 60)),
                        'department_id' => $department->id,
                        'email' => strtolower($firstName . '.' . $lastName . $teachersCreated . '@school.ac.ke'),
                        'phone' => '+254' . rand(700000000, 799999999),
                        'contract_type' => rand(0, 1) ? 'permanent' : 'contract',
                        'status' => 'active',
                    ]
                );
                $teachersCreated++;
            }
        }

        $this->command->info("Created $teachersCreated teachers.");

        // Create Guardians
        $guardianFirstNames = ['Patrick', 'Rose', 'George', 'Florence', 'Charles', 'Alice', 'Henry', 'Dorothy', 'Edward', 'Agnes'];
        $guardiansCreated = 0;

        for ($i = 0; $i < 400; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $firstName = $guardianFirstNames[array_rand($guardianFirstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            Guardian::firstOrCreate(
                ['phone' => '+254' . rand(700000000, 799999999)],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'email' => strtolower($firstName . '.' . $lastName . $guardiansCreated . '@gmail.com'),
                    'occupation' => ['Teacher', 'Doctor', 'Engineer', 'Farmer', 'Business'][array_rand(['Teacher', 'Doctor', 'Engineer', 'Farmer', 'Business'])],
                    'status' => 'active',
                ]
            );
            $guardiansCreated++;
        }

        $this->command->info("Created $guardiansCreated guardians.");

        // Create Fee Payments
        $students = Student::where('status', 'active')->get();
        $paymentsCreated = 0;

        foreach ($students->take(200) as $student) {
            FeePayment::firstOrCreate(
                ['receipt_number' => 'RCP' . str_pad($paymentsCreated + 1, 6, '0', STR_PAD_LEFT)],
                [
                    'student_id' => $student->id,
                    'academic_term_id' => $term1->id,
                    'amount' => rand(15000, 50000),
                    'payment_date' => Carbon::now()->subDays(rand(1, 60)),
                    'payment_method' => ['cash', 'mpesa', 'bank_transfer'][array_rand(['cash', 'mpesa', 'bank_transfer'])],
                    'status' => 'confirmed',
                ]
            );
            $paymentsCreated++;
        }

        $this->command->info("Created $paymentsCreated fee payments.");

        // Create Student Fees
        foreach ($students as $student) {
            $totalAmount = rand(35000, 65000);
            $paidAmount = rand(0, $totalAmount);

            StudentFee::firstOrCreate(
                ['student_id' => $student->id, 'academic_term_id' => $term1->id],
                [
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance' => $totalAmount - $paidAmount,
                    'due_date' => Carbon::now()->addDays(rand(1, 30)),
                    'status' => $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending'),
                ]
            );
        }

        $this->command->info("Created student fee records.");

        // Create Attendance Records
        $attendanceCreated = 0;
        foreach ($students->take(500) as $student) {
            for ($day = 0; $day < 5; $day++) {
                $date = Carbon::now()->startOfWeek()->addDays($day);
                if ($date->lte(Carbon::now())) {
                    StudentAttendance::firstOrCreate(
                        ['student_id' => $student->id, 'date' => $date->toDateString()],
                        [
                            'school_class_id' => $student->current_class_id,
                            'academic_term_id' => $term1->id,
                            'status' => rand(1, 100) <= 95 ? 'present' : 'absent',
                            'check_in_time' => '07:30:00',
                            'is_late' => rand(1, 100) <= 5,
                        ]
                    );
                    $attendanceCreated++;
                }
            }
        }

        $this->command->info("Created $attendanceCreated attendance records.");

        // Create Announcements
        $announcements = [
            ['title' => 'Parents Meeting Scheduled', 'content' => 'All parents are invited for the Term 1 parents meeting on Friday.', 'type' => 'general', 'priority' => 'high'],
            ['title' => 'Sports Day Announcement', 'content' => 'Annual sports day will be held on March 20th. All students are encouraged to participate.', 'type' => 'event', 'priority' => 'normal'],
            ['title' => 'Term 1 Exams Timetable', 'content' => 'The exam timetable has been released. Please check with your class teachers.', 'type' => 'academic', 'priority' => 'high'],
            ['title' => 'Fee Payment Reminder', 'content' => 'Parents are reminded to clear outstanding fee balances by end of month.', 'type' => 'notice', 'priority' => 'urgent'],
        ];

        foreach ($announcements as $ann) {
            Announcement::firstOrCreate(
                ['title' => $ann['title']],
                array_merge($ann, [
                    'target_audience' => 'all',
                    'is_published' => true,
                    'start_date' => Carbon::now(),
                ])
            );
        }

        $this->command->info("Created announcements.");

        // Create Events
        $events = [
            ['title' => 'Term 1 Exams Begin', 'event_type' => 'exam', 'start_date' => '2026-03-15'],
            ['title' => 'Staff Meeting', 'event_type' => 'meeting', 'start_date' => '2026-03-12', 'start_time' => '14:00:00'],
            ['title' => 'Sports Day', 'event_type' => 'sports', 'start_date' => '2026-03-20'],
            ['title' => 'Report Cards Due', 'event_type' => 'deadline', 'start_date' => '2026-03-25'],
            ['title' => 'Good Friday Holiday', 'event_type' => 'holiday', 'start_date' => '2026-04-03'],
            ['title' => 'Easter Monday', 'event_type' => 'holiday', 'start_date' => '2026-04-06'],
            ['title' => 'Term 1 Closing', 'event_type' => 'academic', 'start_date' => '2026-04-03'],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(
                ['title' => $event['title'], 'start_date' => $event['start_date']],
                array_merge($event, [
                    'target_audience' => 'all',
                    'status' => 'upcoming',
                ])
            );
        }

        $this->command->info("Created events.");

        $this->command->info('Dashboard seeding completed!');
    }
}
