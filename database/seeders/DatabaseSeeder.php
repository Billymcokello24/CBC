<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            RolesAndPermissionsSeeder::class,
            SchoolTypesAndLevelsSeeder::class,
            CurriculumSeeder::class,
            DashboardSeeder::class,
            RubricSeeder::class,
        ]);

        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cbcplatform.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Create demo School Admin
        $schoolAdmin = User::create([
            'name' => 'School Administrator',
            'email' => 'school@demo.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $schoolAdmin->assignRole('school_admin');

        // Create demo Teacher
        $teacher = User::create([
            'name' => 'Demo Teacher',
            'email' => 'teacher@demo.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $teacher->assignRole('teacher');

        // Create demo Parent
        $parent = User::create([
            'name' => 'Demo Parent',
            'email' => 'parent@demo.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $parent->assignRole('parent');

        // Create demo Student
        $student = User::create([
            'name' => 'Demo Student',
            'email' => 'student@demo.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $student->assignRole('student');
    }
}
