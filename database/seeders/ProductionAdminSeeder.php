<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'cloudpioneercodinghub@gmail.com';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Cloud School Admin',
                'password' => Hash::make('password'), // Change this in production immediately
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Ensure the super_admin role is assigned
        // This assumes the Spatie/Laravel-Permission roles are already seeded
        if (!$user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }

        $this->command->info("✓ Production Super Admin created/updated: {$email}");
    }
}
