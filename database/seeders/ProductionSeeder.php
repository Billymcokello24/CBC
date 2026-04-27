<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,     // Create essential roles (super_admin, etc.)
            SchoolTypesAndLevelsSeeder::class,    // Institutional metadata
            CurriculumSeeder::class,              // Kenya CBC Curriculum data
            ProductionAdminSeeder::class,          // Create the primary super admin
        ]);
    }
}
