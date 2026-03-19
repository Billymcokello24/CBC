<?php

namespace Database\Seeders;

use App\Models\Assessment\AssessmentType;
use App\Models\Assessment\Rubric;
use App\Models\Assessment\RubricCriteria;
use App\Models\Assessment\RubricLevel;
use App\Models\Curriculum\Subject;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class RubricSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->error('No school found to seed rubrics for.');
            return;
        }

        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'school_admin');
        })->first() ?? User::first();
        $adminId = $admin?->id;

        // 1. Ensure Assessment Types exist
        $assessmentTypes = [
            ['name' => 'Main Examination', 'code' => 'EXAM', 'category' => 'summative'],
            ['name' => 'Mid-Term Assessment', 'code' => 'MID', 'category' => 'summative'],
            ['name' => 'Continuous Assessment (CAT)', 'code' => 'CAT', 'category' => 'formative'],
            ['name' => 'Project-Based Assessment', 'code' => 'PROJ', 'category' => 'formative'],
        ];

        foreach ($assessmentTypes as $type) {
            AssessmentType::firstOrCreate(
                ['school_id' => $school->id, 'code' => $type['code']],
                ['name' => $type['name'], 'category' => $type['category'], 'is_active' => true]
            );
        }

        $examType = AssessmentType::where('code', 'EXAM')->first();
        $catType = AssessmentType::where('code', 'CAT')->first();

        // 2. Standard CBC Primary Rubric
        $rubric1 = Rubric::create([
            'school_id' => $school->id,
            'assessment_type_id' => $examType->id,
            'name' => 'Standard CBC Primary Rubric',
            'description' => 'Official 4-level CBC grading scale for primary education.',
            'total_points' => 100,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        $criteria1 = RubricCriteria::create([
            'rubric_id' => $rubric1->id,
            'criterion_name' => 'Overall Competency',
            'max_points' => 100,
        ]);

        $levels1 = [
            ['level_name' => 'Exceeding Expectation', 'grade_code' => 'EE', 'min_score' => 80, 'max_score' => 100, 'points' => 4, 'description' => 'Learner consistently demonstrates high levels of competency.'],
            ['level_name' => 'Meeting Expectation', 'grade_code' => 'ME', 'min_score' => 60, 'max_score' => 79, 'points' => 3, 'description' => 'Learner demonstrates expected level of competency.'],
            ['level_name' => 'Approaching Expectation', 'grade_code' => 'AE', 'min_score' => 40, 'max_score' => 59, 'points' => 2, 'description' => 'Learner is beginning to develop competency with support.'],
            ['level_name' => 'Below Expectation', 'grade_code' => 'BE', 'min_score' => 0, 'max_score' => 39, 'points' => 1, 'description' => 'Learner requires significant support and intervention.'],
        ];

        foreach ($levels1 as $index => $level) {
            RubricLevel::create(array_merge($level, [
                'rubric_criteria_id' => $criteria1->id,
                'display_order' => $index
            ]));
        }

        // 3. Literacy/Language Rubric
        $english = Subject::where('code', 'ENG')->first();
        $rubric2 = Rubric::create([
            'school_id' => $school->id,
            'subject_id' => $english?->id,
            'assessment_type_id' => $catType->id,
            'name' => 'English Literacy Proficiency',
            'description' => 'Rubric for assessing reading and writing fluency.',
            'total_points' => 50,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        $criteria2 = RubricCriteria::create([
            'rubric_id' => $rubric2->id,
            'criterion_name' => 'Literacy Skills',
            'max_points' => 50,
        ]);

        $levels2 = [
            ['level_name' => 'Fluent Speaker & Writer', 'grade_code' => 'L1', 'min_score' => 45, 'max_score' => 50, 'points' => 5, 'description' => 'Exceptional usage of language structure.'],
            ['level_name' => 'Proficient', 'grade_code' => 'L2', 'min_score' => 35, 'max_score' => 44, 'points' => 4, 'description' => 'Clear communication with minor errors.'],
            ['level_name' => 'Intermediate', 'grade_code' => 'L3', 'min_score' => 25, 'max_score' => 34, 'points' => 3, 'description' => 'Developing basic fluency.'],
            ['level_name' => 'Beginner', 'grade_code' => 'L4', 'min_score' => 0, 'max_score' => 24, 'points' => 2, 'description' => 'Struggles with basic language constructs.'],
        ];

        foreach ($levels2 as $index => $level) {
            RubricLevel::create(array_merge($level, [
                'rubric_criteria_id' => $criteria2->id,
                'display_order' => $index
            ]));
        }

        // 4. Numeracy Rubric
        $math = Subject::where('code', 'MAT')->first();
        $rubric3 = Rubric::create([
            'school_id' => $school->id,
            'subject_id' => $math?->id,
            'assessment_type_id' => $catType->id,
            'name' => 'Numeracy & Logic Rubric',
            'description' => 'Focused on mathematical calculation and logical reasoning.',
            'total_points' => 40,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        $criteria3 = RubricCriteria::create([
            'rubric_id' => $rubric3->id,
            'criterion_name' => 'Numerical Logic',
            'max_points' => 40,
        ]);

        $levels3 = [
            ['level_name' => 'Highly Logical', 'grade_code' => 'P1', 'min_score' => 35, 'max_score' => 40, 'points' => 10, 'description' => 'Solves complex problems independently.'],
            ['level_name' => 'Logical', 'grade_code' => 'P2', 'min_score' => 25, 'max_score' => 34, 'points' => 8, 'description' => 'Good understanding of core concepts.'],
            ['level_name' => 'Average Reasoning', 'grade_code' => 'P3', 'min_score' => 15, 'max_score' => 24, 'points' => 6, 'description' => 'Moderate support needed for complex tasks.'],
            ['level_name' => 'Basic Reasoning', 'grade_code' => 'P4', 'min_score' => 0, 'max_score' => 14, 'points' => 4, 'description' => 'Requires constant guidance.'],
        ];

        foreach ($levels3 as $index => $level) {
            RubricLevel::create(array_merge($level, [
                'rubric_criteria_id' => $criteria3->id,
                'display_order' => $index
            ]));
        }
    }
}
