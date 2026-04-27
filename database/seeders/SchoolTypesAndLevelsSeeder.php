<?php

namespace Database\Seeders;

use App\Models\SchoolLevel;
use App\Models\SchoolType;
use Illuminate\Database\Seeder;

class SchoolTypesAndLevelsSeeder extends Seeder
{
    public function run(): void
    {
        // School Types
        $schoolTypes = [
            ['name' => 'Primary School', 'code' => 'PRIMARY', 'description' => 'Primary education (PP1 to Grade 6)'],
            ['name' => 'Junior Secondary School', 'code' => 'JSS', 'description' => 'Junior Secondary education (Grade 7-9)'],
            ['name' => 'Senior Secondary School', 'code' => 'SSS', 'description' => 'Senior Secondary education (Grade 10-12)'],
            ['name' => 'Mixed School', 'code' => 'MIXED', 'description' => 'Primary and Secondary combined'],
            ['name' => 'Special Needs School', 'code' => 'SPECIAL', 'description' => 'Schools for learners with special needs'],
        ];

        foreach ($schoolTypes as $type) {
            SchoolType::updateOrCreate(['code' => $type['code']], $type);
        }

        // School Levels
        $schoolLevels = [
            ['name' => 'National School', 'code' => 'NATIONAL', 'description' => 'National level school'],
            ['name' => 'Extra County School', 'code' => 'EXTRA_COUNTY', 'description' => 'Extra county level school'],
            ['name' => 'County School', 'code' => 'COUNTY', 'description' => 'County level school'],
            ['name' => 'Sub-County School', 'code' => 'SUB_COUNTY', 'description' => 'Sub-county level school'],
            ['name' => 'Private School', 'code' => 'PRIVATE', 'description' => 'Private/Independent school'],
        ];

        foreach ($schoolLevels as $level) {
            SchoolLevel::updateOrCreate(['code' => $level['code']], $level);
        }
    }
}
