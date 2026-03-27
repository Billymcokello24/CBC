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
            // 1. Create Tenant
            $tenantId = $schoolData['code'];
            $tenant = \App\Models\Tenant::create([
                'id' => $tenantId,
                'name' => $schoolData['name'],
            ]);

            // 2. Create Domain (e.g., code.localhost or code.domain.com)
            $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
            $tenant->domains()->create([
                'domain' => $tenantId . '.' . $centralDomain,
            ]);

            // 3. Create central School record
            $schoolData['tenant_id'] = $tenant->id;
            $school = School::create($schoolData);

            // 4. Run tenant-specific provisioning in the tenant database
            $admin = $tenant->run(function () use ($school, $adminData) {
                // a. Create School Admin User in tenant DB
                $user = User::create([
                    'school_id' => $school->id, // Optional, but kept for compatibility
                    'name' => $adminData['name'],
                    'email' => $adminData['email'],
                    'password' => Hash::make($adminData['password'] ?? Str::random(12)),
                    'phone' => $adminData['phone'] ?? null,
                    'status' => 'active',
                ]);

                // b. Assign School Admin Role (Tenant DB has its own roles/permissions)
                if (!$user->hasRole('school_admin')) {
                    $user->assignRole('school_admin');
                }

                // c. Initial Setup (Academic Year & Term)
                $this->initializeSchool($school);

                return $user;
            });

            // 5. Send Welcome Email
            Mail::to($admin->email)->send(new SchoolWelcomeMail($school, $admin));

            return [
                'school' => $school,
                'admin' => $admin,
                'tenant' => $tenant,
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
