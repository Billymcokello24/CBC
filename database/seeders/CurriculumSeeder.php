<?php

namespace Database\Seeders;

use App\Models\Curriculum\Competency;
use App\Models\Curriculum\LearningArea;
use App\Models\Curriculum\Subject;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // Core CBC Competencies
        $competencies = [
            ['name' => 'Communication and Collaboration', 'code' => 'CC', 'category' => 'core', 'description' => 'Ability to communicate effectively and work with others'],
            ['name' => 'Critical Thinking and Problem Solving', 'code' => 'CTPS', 'category' => 'core', 'description' => 'Ability to think critically and solve problems'],
            ['name' => 'Creativity and Imagination', 'code' => 'CI', 'category' => 'core', 'description' => 'Ability to think creatively and use imagination'],
            ['name' => 'Citizenship', 'code' => 'CIT', 'category' => 'core', 'description' => 'Understanding of civic responsibilities and national values'],
            ['name' => 'Digital Literacy', 'code' => 'DL', 'category' => 'core', 'description' => 'Ability to use digital technologies effectively'],
            ['name' => 'Learning to Learn', 'code' => 'LTL', 'category' => 'core', 'description' => 'Ability to acquire knowledge and skills independently'],
            ['name' => 'Self-Efficacy', 'code' => 'SE', 'category' => 'core', 'description' => 'Belief in one\'s ability to succeed'],
        ];

        foreach ($competencies as $index => $competency) {
            Competency::create(array_merge($competency, ['display_order' => $index + 1]));
        }

        // Learning Areas and Subjects
        $learningAreas = [
            [
                'name' => 'Languages',
                'code' => 'LANG',
                'category' => 'core',
                'description' => 'Language and communication skills',
                'subjects' => [
                    ['name' => 'English', 'code' => 'ENG', 'subject_type' => 'core', 'is_examinable' => true],
                    ['name' => 'Kiswahili', 'code' => 'KIS', 'subject_type' => 'core', 'is_examinable' => true],
                    ['name' => 'Indigenous Languages', 'code' => 'IND', 'subject_type' => 'optional', 'is_examinable' => false],
                    ['name' => 'Kenya Sign Language', 'code' => 'KSL', 'subject_type' => 'optional', 'is_examinable' => false],
                    ['name' => 'Foreign Languages', 'code' => 'FL', 'subject_type' => 'optional', 'is_examinable' => true],
                ]
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'category' => 'core',
                'description' => 'Mathematical concepts and applications',
                'subjects' => [
                    ['name' => 'Mathematics', 'code' => 'MAT', 'subject_type' => 'core', 'is_examinable' => true],
                ]
            ],
            [
                'name' => 'Integrated Science',
                'code' => 'SCI',
                'category' => 'core',
                'description' => 'Scientific inquiry and knowledge',
                'subjects' => [
                    ['name' => 'Integrated Science', 'code' => 'ISC', 'subject_type' => 'core', 'is_examinable' => true],
                    ['name' => 'Health Education', 'code' => 'HE', 'subject_type' => 'core', 'is_examinable' => false],
                ]
            ],
            [
                'name' => 'Pre-Technical and Pre-Career Education',
                'code' => 'PTPC',
                'category' => 'core',
                'description' => 'Technical and career preparation',
                'subjects' => [
                    ['name' => 'Agriculture', 'code' => 'AGR', 'subject_type' => 'optional', 'is_examinable' => true],
                    ['name' => 'Home Science', 'code' => 'HS', 'subject_type' => 'optional', 'is_examinable' => true],
                    ['name' => 'Computer Science', 'code' => 'CS', 'subject_type' => 'optional', 'is_examinable' => true],
                    ['name' => 'Business Studies', 'code' => 'BS', 'subject_type' => 'optional', 'is_examinable' => true],
                ]
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SS',
                'category' => 'core',
                'description' => 'Social and civic knowledge',
                'subjects' => [
                    ['name' => 'Social Studies', 'code' => 'SOC', 'subject_type' => 'core', 'is_examinable' => true],
                ]
            ],
            [
                'name' => 'Religious Education',
                'code' => 'RE',
                'category' => 'core',
                'description' => 'Religious and moral education',
                'subjects' => [
                    ['name' => 'Christian Religious Education', 'code' => 'CRE', 'subject_type' => 'optional', 'is_examinable' => true],
                    ['name' => 'Islamic Religious Education', 'code' => 'IRE', 'subject_type' => 'optional', 'is_examinable' => true],
                    ['name' => 'Hindu Religious Education', 'code' => 'HRE', 'subject_type' => 'optional', 'is_examinable' => true],
                ]
            ],
            [
                'name' => 'Creative Arts',
                'code' => 'CA',
                'category' => 'core',
                'description' => 'Artistic expression and creativity',
                'subjects' => [
                    ['name' => 'Art and Craft', 'code' => 'AC', 'subject_type' => 'core', 'is_examinable' => false],
                    ['name' => 'Music', 'code' => 'MUS', 'subject_type' => 'optional', 'is_examinable' => false],
                    ['name' => 'Drama', 'code' => 'DRA', 'subject_type' => 'optional', 'is_examinable' => false],
                ]
            ],
            [
                'name' => 'Physical and Health Education',
                'code' => 'PHE',
                'category' => 'core',
                'description' => 'Physical fitness and health',
                'subjects' => [
                    ['name' => 'Physical Education', 'code' => 'PE', 'subject_type' => 'core', 'is_examinable' => false],
                    ['name' => 'Sports', 'code' => 'SPT', 'subject_type' => 'optional', 'is_examinable' => false],
                ]
            ],
        ];

        foreach ($learningAreas as $areaIndex => $areaData) {
            $subjects = $areaData['subjects'];
            unset($areaData['subjects']);

            $learningArea = LearningArea::create(array_merge($areaData, ['display_order' => $areaIndex + 1]));

            foreach ($subjects as $subIndex => $subject) {
                Subject::create(array_merge($subject, [
                    'learning_area_id' => $learningArea->id,
                    'display_order' => $subIndex + 1,
                ]));
            }
        }
    }
}
