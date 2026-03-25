<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SchoolWelcomeMail;

class SchoolOnboardingService
{
    /**
     * Register a new school and provision its first admin account.
     *
     * @param array $schoolData
     * @param array $adminData
     * @return array
     */
    public function register(array $schoolData, array $adminData): array
    {
        return DB::transaction(function () use ($schoolData, $adminData) {
            // 1. Create School
            $school = School::create($schoolData);

            // 2. Create School Admin User
            $admin = User::create([
                'school_id' => $school->id,
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => Hash::make($adminData['password'] ?? Str::random(12)),
                'phone' => $adminData['phone'] ?? null,
                'status' => 'active',
            ]);

            // 3. Assign School Admin Role
            if ($admin->hasRole('school_admin') === false) {
                $admin->assignRole('school_admin');
            }

            // 4. Initial Setup (Academic Year & Term)
            $this->initializeSchool($school);

            // 5. Send Welcome Email
            Mail::to($admin->email)->send(new SchoolWelcomeMail($school, $admin));

            return [
                'school' => $school,
                'admin' => $admin,
            ];
        });
    }

    /**
     * Initialize essential records for a new school.
     */
    private function initializeSchool(School $school): void
    {
        // Create current academic year if not exists
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => date('Y') . ' Academic Year',
            'code' => date('Y'),
            'start_date' => date('Y-01-01'),
            'end_date' => date('Y-12-31'),
            'is_current' => true,
            'status' => 'active',
        ]);

        // Create Term 1
        AcademicTerm::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'term_number' => 1,
            'start_date' => date('Y-01-01'),
            'end_date' => date('Y-04-30'),
            'is_current' => true,
            'status' => 'active',
        ]);
    }
}
