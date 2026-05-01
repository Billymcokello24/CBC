<?php

use App\Models\School;
use App\Models\Curriculum\Subject;
use App\Models\Assessment\AssessmentType;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use Illuminate\Support\Facades\DB;

$schools = School::all();

foreach ($schools as $school) {
    echo "Processing School: {$school->name}\n";

    // 1. Subjects
    $defaultSubjects = [
        ['name' => 'Mathematics', 'code' => 'MATH', 'subject_type' => 'core'],
        ['name' => 'English', 'code' => 'ENG', 'subject_type' => 'core'],
        ['name' => 'Kiswahili', 'code' => 'KISW', 'subject_type' => 'core'],
        ['name' => 'Science and Technology', 'code' => 'SCI', 'subject_type' => 'core'],
        ['name' => 'Social Studies', 'code' => 'SOC', 'subject_type' => 'core'],
        ['name' => 'Religious Education', 'code' => 'RE', 'subject_type' => 'core'],
        ['name' => 'Creative Arts', 'code' => 'ART', 'subject_type' => 'core'],
    ];

    foreach ($defaultSubjects as $sub) {
        $subject = Subject::updateOrCreate(
            ['code' => $sub['code']],
            array_merge($sub, ['school_id' => $school->id, 'is_active' => true])
        );

        DB::table('school_subjects')->updateOrInsert(
            ['school_id' => $school->id, 'subject_id' => $subject->id],
            ['is_offered' => true]
        );
    }

    $subjects = Subject::all();

    // 2. Assessment Types
    $types = [
        ['name' => 'Formative Assessment', 'code' => $school->id . '-FORM', 'category' => 'formative'],
        ['name' => 'Summative Assessment', 'code' => $school->id . '-SUMM', 'category' => 'summative'],
        ['name' => 'Continuous Assessment (CAT)', 'code' => $school->id . '-CAT', 'category' => 'formative'],
        ['name' => 'Take Home Project', 'code' => $school->id . '-PROJ', 'category' => 'project'],
    ];

    foreach ($types as $type) {
        AssessmentType::updateOrCreate(
            ['school_id' => $school->id, 'code' => $type['code']],
            $type
        );
    }

    // 3. Academic Year & Term
    $year = AcademicYear::updateOrCreate(
        ['school_id' => $school->id, 'name' => '2026'],
        ['code' => 'AY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
    );

    $terms = [
        ['name' => 'Term 1', 'term_number' => 1, 'is_current' => true],
        ['name' => 'Term 2', 'term_number' => 2, 'is_current' => false],
        ['name' => 'Term 3', 'term_number' => 3, 'is_current' => false],
    ];

    foreach ($terms as $term) {
        AcademicTerm::updateOrCreate(
            ['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => $term['name']],
            [
                'term_number' => $term['term_number'], 
                'is_current' => $term['is_current'],
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ]
        );
    }
}

echo "Master Data Fix Completed.\n";
