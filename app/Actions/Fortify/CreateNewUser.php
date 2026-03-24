<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

use App\Services\SchoolOnboardingService;
use App\Models\SchoolType;
use App\Models\SchoolLevel;
use Illuminate\Support\Str;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    protected $onboardingService;

    public function __construct(SchoolOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, array_merge($this->profileRules(), [
            'school_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ]))->validate();

        // Prepare school data with defaults for public registration
        $schoolData = [
            'name' => $input['school_name'],
            'code' => strtoupper(Str::random(8)), // Generate a temporary code
            'email' => $input['email'],
            'phone' => 'N/A',
            'county' => 'Nairobi', // Default for now
            'school_type_id' => SchoolType::first()?->id ?? 1,
            'school_level_id' => SchoolLevel::first()?->id ?? 1,
            'status' => 'active',
        ];

        $adminData = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ];

        $result = $this->onboardingService->register($schoolData, $adminData);

        return $result['admin'];
    }
}
