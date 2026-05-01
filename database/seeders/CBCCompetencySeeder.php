<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CBCCompetencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competencies = [
            [
                'name' => 'Communication and Collaboration',
                'code' => 'COM',
                'description' => 'Ability to convey information clearly and work effectively with others.',
                'category' => 'core',
                'display_order' => 1,
            ],
            [
                'name' => 'Critical Thinking and Problem Solving',
                'code' => 'CT',
                'description' => 'Ability to analyze information and develop solutions to complex problems.',
                'category' => 'core',
                'display_order' => 2,
            ],
            [
                'name' => 'Imagination and Creativity',
                'code' => 'IC',
                'description' => 'Ability to think of new ideas and create innovative solutions.',
                'category' => 'core',
                'display_order' => 3,
            ],
            [
                'name' => 'Citizenship',
                'code' => 'CIT',
                'description' => 'Understanding of rights, responsibilities, and civic participation.',
                'category' => 'core',
                'display_order' => 4,
            ],
            [
                'name' => 'Learning to Learn',
                'code' => 'LTL',
                'description' => 'Developing the skills and mindset for lifelong independent learning.',
                'category' => 'core',
                'display_order' => 5,
            ],
            [
                'name' => 'Self-efficacy',
                'code' => 'SE',
                'description' => 'Confidence in one\'s ability to succeed in specific situations.',
                'category' => 'core',
                'display_order' => 6,
            ],
            [
                'name' => 'Digital Literacy',
                'code' => 'DL',
                'description' => 'Ability to use digital technology effectively and safely.',
                'category' => 'core',
                'display_order' => 7,
            ],
        ];

        foreach ($competencies as $comp) {
            DB::table('competencies')->updateOrInsert(
                ['code' => $comp['code']],
                array_merge($comp, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
