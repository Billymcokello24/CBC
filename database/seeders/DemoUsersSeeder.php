<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ─── 1. Super Admin (already exists) ───
        $admin = User::firstWhere('email', 'admin@cbcplatform.com');
        if ($admin) {
            $admin->syncRoles(['super_admin']);
            $this->command->info("✓ admin@cbcplatform.com → super_admin");
        }

        // ─── 2. Principal ───
        $principal = User::firstOrCreate(
            ['email' => 'principal@cbcplatform.com'],
            ['name' => 'Dr. Anne Wambui', 'password' => $password, 'email_verified_at' => now()]
        );
        $principal->syncRoles(['principal']);
        $this->command->info("✓ principal@cbcplatform.com → principal");

        // ─── 3. HOD ───
        $hodUser = User::firstOrCreate(
            ['email' => 'hod@cbcplatform.com'],
            ['name' => 'Mr. James Ochieng', 'password' => $password, 'email_verified_at' => now()]
        );
        $hodUser->syncRoles(['hod']);
        // Link to a teacher record if available
        $hodTeacher = Teacher::where('user_id', $hodUser->id)->first();
        if (!$hodTeacher) {
            $hodTeacher = Teacher::first();
            if ($hodTeacher && !$hodTeacher->user_id) {
                $hodTeacher->update(['user_id' => $hodUser->id]);
            }
        }
        $this->command->info("✓ hod@cbcplatform.com → hod");

        // ─── 4. Teacher ───
        // Use existing jane.mwangi@example.com or create new
        $teacherUser = User::firstWhere('email', 'jane.mwangi@example.com');
        if ($teacherUser) {
            $teacherUser->syncRoles(['teacher']);
            if (!$teacherUser->email_verified_at) {
                $teacherUser->update(['email_verified_at' => now()]);
            }
            $this->command->info("✓ jane.mwangi@example.com → teacher");
        } else {
            $teacherUser = User::firstOrCreate(
                ['email' => 'teacher@cbcplatform.com'],
                ['name' => 'Jane Mwangi', 'password' => $password, 'email_verified_at' => now()]
            );
            $teacherUser->syncRoles(['teacher']);
            $this->command->info("✓ teacher@cbcplatform.com → teacher");
        }

        // ─── 5. Class Teacher ───
        $ctUser = User::firstOrCreate(
            ['email' => 'classteacher@cbcplatform.com'],
            ['name' => 'Mrs. Mary Njeri', 'password' => $password, 'email_verified_at' => now()]
        );
        $ctUser->syncRoles(['class_teacher']);
        $this->command->info("✓ classteacher@cbcplatform.com → class_teacher");

        // ─── 6. Parent / Guardian ───
        $parentUser = User::firstWhere('email', 'guardian1@example.com');
        if ($parentUser) {
            $parentUser->syncRoles(['parent']);
            if (!$parentUser->email_verified_at) {
                $parentUser->update(['email_verified_at' => now()]);
            }
            $this->command->info("✓ guardian1@example.com → parent");
        } else {
            $parentUser = User::firstOrCreate(
                ['email' => 'parent@cbcplatform.com'],
                ['name' => 'John Kamau', 'password' => $password, 'email_verified_at' => now()]
            );
            $parentUser->syncRoles(['parent']);
            $this->command->info("✓ parent@cbcplatform.com → parent");
        }

        // ─── 7. Student ───
        $studentUser = User::firstOrCreate(
            ['email' => 'student@cbcplatform.com'],
            ['name' => 'Brian Otieno', 'password' => $password, 'email_verified_at' => now()]
        );
        $studentUser->syncRoles(['student']);
        // Link to a student record if available
        $studentRecord = Student::where('user_id', $studentUser->id)->first();
        if (!$studentRecord) {
            $studentRecord = Student::whereNull('user_id')->first();
            if ($studentRecord) {
                $studentRecord->update(['user_id' => $studentUser->id]);
            }
        }
        $this->command->info("✓ student@cbcplatform.com → student");

        // ─── 8. Finance Officer ───
        $financeUser = User::firstOrCreate(
            ['email' => 'finance@cbcplatform.com'],
            ['name' => 'Grace Akinyi', 'password' => $password, 'email_verified_at' => now()]
        );
        $financeUser->syncRoles(['finance_officer']);
        $this->command->info("✓ finance@cbcplatform.com → finance_officer");

        // ─── 9. Librarian ───
        $librarianUser = User::firstOrCreate(
            ['email' => 'librarian@cbcplatform.com'],
            ['name' => 'Peter Maina', 'password' => $password, 'email_verified_at' => now()]
        );
        $librarianUser->syncRoles(['librarian']);
        $this->command->info("✓ librarian@cbcplatform.com → librarian");

        // ─── 10. Nurse ───
        $nurseUser = User::firstOrCreate(
            ['email' => 'nurse@cbcplatform.com'],
            ['name' => 'Nancy Chebet', 'password' => $password, 'email_verified_at' => now()]
        );
        $nurseUser->syncRoles(['nurse']);
        $this->command->info("✓ nurse@cbcplatform.com → nurse");

        $this->command->newLine();
        $this->command->info('All demo users created/updated. Password for all: "password"');
    }
}
