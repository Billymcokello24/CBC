<?php

namespace Database\Seeders;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\SchoolClass;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentItem;
use App\Models\Assessment\AssessmentType;
use App\Models\Assessment\StudentAssessmentRating;
use App\Models\Curriculum\Competency;
use App\Models\Curriculum\CompetencyIndicator;
use App\Models\Curriculum\LearningIndicator;
use App\Models\Curriculum\LearningOutcome;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\Subject;
use App\Models\Curriculum\SchemeOfWork;
use App\Models\Curriculum\SchemeEntry;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantCBCSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure we have academic context
        $academicYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        $academicTerm = AcademicTerm::where('is_current', true)->first() ?? AcademicTerm::first();

        if (!$academicYear || !$academicTerm) {
            $this->command->error('Academic Year or Term not found. Please run RealDataSeeder first.');
            return;
        }

        // 2. Seed Curriculum hierarchy (Sub-strands, Outcomes, Indicators)
        $this->seedCurriculumHierarchy();

        // 3. Seed Competency Indicators
        $this->seedCompetencyIndicators();

        // 4. Seed Teacher Assignments (Subject & Class)
        $this->seedTeacherAssignments($academicYear);

        // 5. Seed Assessment Data
        $this->seedAssessmentData($academicYear, $academicTerm);

        // 6. Seed Portfolio Evidence
        $this->seedPortfolioEvidence();

        // 7. Seed Schemes of Work
        $this->seedSchemesOfWork($academicYear, $academicTerm);
        
        $this->command->info('TenantCBCSeeder completed successfully!');
    }

    private function seedCurriculumHierarchy()
    {
        $this->command->info('Seeding Curriculum Hierarchy...');
        
        $strands = Strand::all();
        foreach ($strands as $strand) {
            // Create 1-2 Sub-strands per Strand
            for ($i = 1; $i <= 2; $i++) {
                $subStrand = SubStrand::firstOrCreate([
                    'strand_id' => $strand->id,
                    'name' => "{$strand->name} Sub-topic {$i}",
                    'code' => "{$strand->code}-SS{$i}",
                ], [
                    'is_active' => true,
                ]);

                // Create 1-2 Learning Outcomes per Sub-strand
                for ($j = 1; $j <= 2; $j++) {
                    $outcome = LearningOutcome::firstOrCreate([
                        'sub_strand_id' => $subStrand->id,
                        'outcome' => "Learner should be able to perform activity {$j} for {$subStrand->name}",
                        'code' => "{$subStrand->code}-LO{$j}",
                    ], [
                        'school_id' => $strand->school_id,
                        'is_active' => true,
                    ]);

                    // Create 1-2 Learning Indicators per Outcome
                    for ($k = 1; $k <= 2; $k++) {
                        LearningIndicator::firstOrCreate([
                            'learning_outcome_id' => $outcome->id,
                            'indicator' => "Demonstrates proficiency in task {$k}",
                        ], [
                            'school_id' => $strand->school_id,
                            'description' => "Detailed indicator for outcome {$outcome->code}",
                            'is_active' => true,
                            'display_order' => $k,
                        ]);
                    }
                }
            }
        }
    }

    private function seedCompetencyIndicators()
    {
        $this->command->info('Seeding Competency Indicators...');
        
        $competencies = Competency::all();
        $gradeLevels = DB::table('grade_levels')->get(); // Using DB table as GradeLevel model might be in different namespace

        foreach ($competencies as $competency) {
            foreach ($gradeLevels as $grade) {
                // Ensure 2 indicators for each competency per grade level
                for ($i = 1; $i <= 2; $i++) {
                    CompetencyIndicator::firstOrCreate([
                        'competency_id' => $competency->id,
                        'grade_level_id' => $grade->id,
                        'indicator' => "{$competency->name} Indicator {$i} for {$grade->name}",
                    ], [
                        'school_id' => $grade->school_id,
                        'description' => "Assessment indicator for {$competency->code}",
                        'is_active' => true,
                        'display_order' => $i,
                    ]);
                }
            }
        }
    }

    private function seedTeacherAssignments($academicYear)
    {
        $this->command->info('Allocating Teachers to Subjects and Classes...');
        
        $teachers = Teacher::all();
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $classes->isEmpty() || $subjects->isEmpty()) {
            return;
        }

        foreach ($classes as $class) {
            // Assign a class teacher if not set
            if (!$class->class_teacher_id) {
                $randomTeacher = $teachers->random();
                $class->update(['class_teacher_id' => $randomTeacher->user_id]);
            }

            // Assign teachers to 5 random subjects for this class
            $randomSubjects = $subjects->random(min(5, $subjects->count()));
            foreach ($randomSubjects as $subject) {
                $teacher = $teachers->random();
                
                DB::table('teacher_subjects')->updateOrInsert([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                    'academic_year_id' => $academicYear->id,
                ], [
                    'school_id' => $class->school_id,
                    'is_primary_teacher' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedAssessmentData($academicYear, $academicTerm)
    {
        $this->command->info('Seeding Assessments and Ratings...');
        
        $assessmentTypes = AssessmentType::all();
        if ($assessmentTypes->isEmpty()) {
            // Fallback: Create basic assessment types if missing
            $types = [
                ['name' => 'Formative Assessment', 'code' => 'FORM', 'category' => 'formative'],
                ['name' => 'Summative Assessment', 'code' => 'SUMM', 'category' => 'summative'],
                ['name' => 'Project Based', 'code' => 'PROJ', 'category' => 'project'],
            ];
            foreach ($types as $type) {
                AssessmentType::create(array_merge($type, ['school_id' => SchoolClass::first()?->school_id, 'is_active' => true]));
            }
            $assessmentTypes = AssessmentType::all();
        }

        $classes = SchoolClass::with('students')->get();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        foreach ($classes as $class) {
            $students = $class->students;
            if ($students->isEmpty()) continue;

            // Get subjects assigned to this class
            $assignedSubjects = DB::table('teacher_subjects')
                ->where('class_id', $class->id)
                ->pluck('subject_id')
                ->toArray();

            foreach ($assignedSubjects as $subjectId) {
                $subject = Subject::find($subjectId);
                $teacherId = DB::table('teacher_subjects')
                    ->where('class_id', $class->id)
                    ->where('subject_id', $subjectId)
                    ->value('teacher_id');
                
                $userTeacher = Teacher::find($teacherId)?->user_id;

                // Create 2 assessments per subject
                for ($i = 1; $i <= 2; $i++) {
                    $type = $assessmentTypes->random();
                    $assessment = Assessment::firstOrCreate([
                        'school_id' => $class->school_id,
                        'class_id' => $class->id,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                        'academic_year_id' => $academicYear->id,
                        'academic_term_id' => $academicTerm->id,
                        'assessment_type_id' => $type->id,
                        'title' => "{$subject->name} {$type->name} {$i}",
                    ], [
                        'assessment_date' => now()->subDays(rand(1, 30)),
                        'status' => 'published',
                        'total_marks' => 100,
                        'passing_marks' => 50,
                        'weight' => 20,
                        'is_published' => true,
                        'created_by' => $userTeacher,
                    ]);

                    // Get some competency indicators for this grade level
                    $indicators = CompetencyIndicator::where('grade_level_id', $class->grade_level_id)->limit(3)->get();
                    
                    foreach ($indicators as $index => $indicator) {
                        $item = AssessmentItem::firstOrCreate([
                            'assessment_id' => $assessment->id,
                            'competency_indicator_id' => $indicator->id,
                        ], [
                            'total_marks' => 100,
                            'max_score' => 4,
                            'display_order' => $index,
                        ]);

                        // Ratings for each student
                        foreach ($students as $student) {
                            StudentAssessmentRating::firstOrCreate([
                                'student_id' => $student->id,
                                'assessment_item_id' => $item->id,
                            ], [
                                'teacher_id' => $userTeacher,
                                'rating_level' => ['EE', 'ME', 'AE', 'BE'][rand(0, 3)],
                                'score' => rand(1, 4),
                                'feedback' => 'Good progress shown.',
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function seedPortfolioEvidence()
    {
        $this->command->info('Seeding Portfolio Evidence...');
        
        if (!Schema::hasTable('portfolio_items')) {
            return;
        }

        $students = Student::limit(50)->get();
        $indicators = LearningIndicator::limit(20)->get();
        $teachers = User::role('teacher')->limit(5)->get();

        foreach ($students as $student) {
            // Ensure student has a portfolio
            $portfolio = DB::table('portfolios')->where('student_id', $student->id)->first();
            if (!$portfolio) {
                $portfolioId = DB::table('portfolios')->insertGetId([
                    'student_id' => $student->id,
                    'school_id' => $student->school_id,
                    'title' => "{$student->first_name}'s Portfolio",
                    'portfolio_type' => 'academic',
                    'visibility' => 'school',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $portfolioId = $portfolio->id;
            }

            // Create 2-3 portfolio items per student
            for ($i = 1; $i <= 2; $i++) {
                $itemData = [
                    'portfolio_id' => $portfolioId,
                    'title' => "Evidence Item {$i} for Student {$student->id}",
                    'description' => "Sample portfolio evidence description for demonstration purposes.",
                    'item_type' => ['image', 'video', 'document', 'link'][$i % 4],
                    'url' => 'https://via.placeholder.com/600x400',
                    'item_date' => now()->subDays(rand(1, 60))->toDateString(),
                    'is_approved' => true,
                ];

                DB::table('portfolio_items')->updateOrInsert([
                    'portfolio_id' => $portfolioId,
                    'title' => $itemData['title'],
                ], array_merge($itemData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $itemId = DB::table('portfolio_items')
                    ->where('portfolio_id', $portfolioId)
                    ->where('title', $itemData['title'])
                    ->value('id');
                
                // If there's a pivot for competencies
                if (Schema::hasTable('portfolio_item_competencies')) {
                    $compIndicator = DB::table('competency_indicators')->inRandomOrder()->first();
                    if ($compIndicator) {
                        DB::table('portfolio_item_competencies')->updateOrInsert([
                            'portfolio_item_id' => $itemId,
                            'competency_id' => $compIndicator->competency_id,
                        ], [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    private function seedSchemesOfWork($academicYear, $academicTerm)
    {
        $this->command->info('Seeding Schemes of Work...');

        $subjects = Subject::all();
        $grades = DB::table('grade_levels')->get();
        $teachers = Teacher::all();

        foreach ($grades as $grade) {
            $gradeSubjects = $subjects->random(min(3, $subjects->count()));
            
            foreach ($gradeSubjects as $subject) {
                $teacher = $teachers->random();
                
                $scheme = SchemeOfWork::firstOrCreate([
                    'school_id' => $grade->school_id,
                    'academic_year_id' => $academicYear->id,
                    'academic_term_id' => $academicTerm->id,
                    'subject_id' => $subject->id,
                    'grade_level_id' => $grade->id,
                ], [
                    'title' => "{$subject->name} Scheme - {$grade->name} - {$academicTerm->name}",
                    'description' => "Standard CBC scheme of work for {$subject->name}.",
                    'total_weeks' => 12,
                    'lessons_per_week' => 5,
                    'status' => 'approved',
                    'prepared_by' => $teacher->user_id,
                ]);

                // Create 5 entries per scheme
                $strands = Strand::where('subject_id', $subject->id)->where('grade_level_id', $grade->id)->get();
                if ($strands->isEmpty()) continue;

                for ($i = 1; $i <= 5; $i++) {
                    $strand = $strands->random();
                    $subStrand = SubStrand::where('strand_id', $strand->id)->inRandomOrder()->first();
                    
                    SchemeEntry::firstOrCreate([
                        'scheme_id' => $scheme->id,
                        'week_number' => ceil($i / 2),
                        'lesson_number' => ($i % 2) + 1,
                    ], [
                        'school_id' => $grade->school_id,
                        'strand_id' => $strand->id,
                        'sub_strand_id' => $subStrand?->id,
                        'topic' => "Topic for Lesson {$i}",
                        'learning_outcomes' => "Outcome 1 for lesson {$i}\nOutcome 2 for lesson {$i}",
                        'learning_activities' => "Activity 1\nActivity 2",
                        'resources' => "Textbook, Charts",
                        'assessment' => "Oral work, Quiz",
                        'remarks' => "Covered well",
                    ]);
                }
            }
        }
    }
}
