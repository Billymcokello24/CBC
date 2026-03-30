<?php

namespace Database\Seeders;

use App\Models\Curriculum\Subject;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\LearningOutcome;
use App\Models\Curriculum\Competency;
use App\Models\Curriculum\CompetencyIndicator;
use App\Models\Assessment\Assessment;
use App\Models\Assessment\AssessmentType;
use App\Models\Assessment\AssessmentItem;
use App\Models\Assessment\StudentAssessmentRating;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\GradeLevel;
use App\Models\Student;
use App\Models\User;
use App\Models\Curriculum\PortfolioItem; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantCBCSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = DB::table('schools')->first()?->id;
        if (!$schoolId) return;

        $this->seedCurriculumHierarchy($schoolId);
        $this->seedCompetencies($schoolId);
        $this->seedTeacherAllocations($schoolId);
        $this->seedAssessmentsAndRatings($schoolId);
        $this->seedPortfolioEvidence($schoolId);
    }

    private function seedCurriculumHierarchy(int $schoolId): void
    {
        $subjectsData = [
            'Mathematics' => [
                'Numbers' => ['Whole Numbers', 'Addition', 'Subtraction', 'Multiplication', 'Division'],
                'Measurement' => ['Length', 'Mass', 'Capacity', 'Time', 'Money'],
                'Geometry' => ['Lines', 'Shapes'],
            ],
            'English' => [
                'Listening' => ['Attentive Listening', 'Following Instructions'],
                'Speaking' => ['Oral Communication', 'Public Speaking', 'Vocabulary'],
                'Reading' => ['Comprehension', 'Word Recognition'],
                'Writing' => ['Creative Writing', 'Grammar'],
            ],
            'Integrated Science' => [
                'Living Things' => ['Plants', 'Animals', 'Human Body'],
                'Environment' => ['Weather', 'Soil', 'Water'],
                'Technology' => ['Tools', 'Energy'],
            ],
            'Creative Arts' => [
                'Art & Craft' => ['Drawing', 'Painting', 'Modeling'],
                'Music' => ['Rhythm', 'Melody', 'Instruments'],
            ]
        ];

        $grades = GradeLevel::where('school_id', $schoolId)->get();

        foreach ($subjectsData as $subjectName => $strands) {
            $subject = Subject::updateOrCreate(['name' => $subjectName], ['is_active' => true]);
            
            // Link subject to school
            DB::table('school_subjects')->updateOrInsert(
                ['school_id' => $schoolId, 'subject_id' => $subject->id],
                ['is_offered' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            foreach ($grades->take(6) as $grade) {
                foreach ($strands as $strandName => $subStrands) {
                    $strand = Strand::updateOrCreate(
                        ['school_id' => $schoolId, 'subject_id' => $subject->id, 'grade_level_id' => $grade->id, 'name' => $strandName],
                        ['code' => strtoupper(substr($strandName, 0, 3)) . $grade->code . $schoolId, 'display_order' => 1, 'is_active' => true]
                    );

                    foreach ($subStrands as $index => $ssName) {
                        $subStrand = SubStrand::updateOrCreate(
                            ['school_id' => $schoolId, 'strand_id' => $strand->id, 'name' => "{$ssName} ({$grade->name})"],
                            ['code' => strtoupper(substr($ssName, 0, 3)) . '-' . $strand->id, 'display_order' => $index + 1, 'is_active' => true]
                        );

                        LearningOutcome::updateOrCreate(
                            ['school_id' => $schoolId, 'sub_strand_id' => $subStrand->id, 'outcome' => "Analyze and apply {$ssName} concepts in Grade {$grade->name}."],
                            ['description' => "Standard learning outcome for {$ssName} in {$grade->name}", 'is_active' => true]
                        );
                    }
                }
            }
        }
    }

    private function seedCompetencies(int $schoolId): void
    {
        $competencies = [
            ['name' => 'Communication and Collaboration', 'code' => 'CC'],
            ['name' => 'Critical Thinking and Problem Solving', 'code' => 'CTPS'],
            ['name' => 'Creativity and Imagination', 'code' => 'CI'],
            ['name' => 'Citizenship', 'code' => 'CIT'],
            ['name' => 'Digital Literacy', 'code' => 'DL'],
            ['name' => 'Learning to Learn', 'code' => 'LTL'],
            ['name' => 'Self-Efficacy', 'code' => 'SE'],
        ];

        $grades = GradeLevel::where('school_id', $schoolId)->get();

        foreach ($competencies as $compData) {
            $comp = Competency::withoutGlobalScope('school_or_global')->where('code', $compData['code'])->first();
            
            if (!$comp) {
                $comp = Competency::create(array_merge($compData, ['school_id' => $schoolId, 'category' => 'core', 'is_active' => true]));
            }

            foreach ($grades as $grade) {
                CompetencyIndicator::updateOrCreate(
                    ['school_id' => $schoolId, 'competency_id' => $comp->id, 'grade_level_id' => $grade->id, 'indicator' => "Demonstrates {$compData['name']} competencies at Grade {$grade->name} level."],
                    ['description' => "Detailed indicator for {$compData['name']} mastery in {$grade->name}", 'display_order' => 1, 'is_active' => true]
                );
            }
        }
    }

    private function seedTeacherAllocations(int $schoolId): void
    {
        $teachers = Teacher::where('school_id', $schoolId)->get();
        if ($teachers->isEmpty()) return;

        $subjects = Subject::whereIn('name', ['Mathematics', 'English', 'Integrated Science', 'Creative Arts'])->get();
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        $academicYearId = DB::table('academic_years')->where('school_id', $schoolId)->where('is_current', true)->value('id');

        foreach ($classes as $index => $class) {
            $classTeacher = $teachers[$index % $teachers->count()];
            $class->update(['class_teacher_id' => $classTeacher->user_id]); // class_teacher_id expects user_id in some models, or teacher_id? Actually SchoolClass model might use user_id.

            foreach ($subjects as $sIndex => $subject) {
                $subjectTeacher = $teachers[($index + $sIndex) % $teachers->count()];
                
                TeacherSubject::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'teacher_id' => $subjectTeacher->id,
                        'subject_id' => $subject->id,
                        'class_id' => $class->id,
                    ],
                    ['academic_year_id' => $academicYearId, 'status' => 'active', 'is_primary_teacher' => true]
                );
            }
        }
    }

    private function seedAssessmentsAndRatings(int $schoolId): void
    {
        $types = [
            'FORMATIVE' => ['name' => 'Formative Assessment', 'category' => 'formative'],
            'SUMMATIVE' => ['name' => 'Summative Assessment', 'category' => 'summative'],
            'PROJECT' => ['name' => 'Project-Based Assessment', 'category' => 'project'],
        ];

        foreach ($types as $code => $data) {
            AssessmentType::updateOrCreate(['code' => $code, 'school_id' => $schoolId], array_merge($data, ['is_active' => true]));
        }

        $formativeType = AssessmentType::where('code', 'FORMATIVE')->where('school_id', $schoolId)->first();
        
        $academicYearId = DB::table('academic_years')->where('school_id', $schoolId)->where('is_current', true)->value('id');
        $academicTermId = DB::table('academic_terms')->where('school_id', $schoolId)->where('is_current', true)->value('id');
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        $subjects = Subject::whereIn('name', ['Mathematics', 'English', 'Integrated Science', 'Creative Arts'])->get();
        $teachers = Teacher::where('school_id', $schoolId)->get();
        
        if ($classes->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) return;

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                $indicators = CompetencyIndicator::where('school_id', $schoolId)
                    ->where('grade_level_id', $class->grade_level_id)
                    ->get();
                if ($indicators->isEmpty()) continue;

                $teacher = $teachers->random();

                // Create a formative assessment
                $fAssessment = Assessment::create([
                    'school_id' => $schoolId,
                    'title' => "{$subject->name} Progressive Tasks - {$class->name}",
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                    'assessment_type_id' => $formativeType->id,
                    'academic_year_id' => $academicYearId,
                    'academic_term_id' => $academicTermId,
                    'status' => 'published',
                    'due_date' => now()->subDays(rand(5, 15)),
                    'assessment_date' => now()->subDays(rand(5, 15)),
                    'created_by' => $teacher->user_id,
                    'teacher_id' => $teacher->user_id 
                ]);

                foreach ($indicators->take(2) as $indicator) {
                    $item = AssessmentItem::create([
                        'assessment_id' => $fAssessment->id,
                        'competency_indicator_id' => $indicator->id,
                        'item_name' => "Task: " . substr($indicator->indicator, 0, 50),
                        'max_score' => 100,
                        'weight' => 50
                    ]);

                    $students = Student::where('current_class_id', $class->id)->get();
                    foreach ($students as $student) {
                        $score = rand(30, 98);
                        $rating = 'BE';
                        if ($score >= 75) $rating = 'EE';
                        elseif ($score >= 55) $rating = 'ME';
                        elseif ($score >= 40) $rating = 'AE';

                        StudentAssessmentRating::create([
                            'student_id' => $student->id,
                            'assessment_item_id' => $item->id,
                            'score' => $score,
                            'rating_level' => $rating, 
                            'feedback' => $score > 70 ? 'Satisfactory progress.' : 'Needs more practice.',
                            'teacher_id' => $teacher->user_id, // Analytics page uses teacher_id matching logged-in user
                            'created_at' => Carbon::parse($fAssessment->due_date)->subDay()
                        ]);
                    }
                }
            }
        }
    }

    private function seedPortfolioEvidence(int $schoolId): void
    {
        $students = Student::where('school_id', $schoolId)->limit(50)->get();
        $teachers = Teacher::where('school_id', $schoolId)->limit(5)->get();
        $subjects = Subject::whereIn('name', ['Mathematics', 'English', 'Creative Arts'])->get();

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                if (rand(0, 1)) {
                    $currentClass = $student->currentClass;
                    if (!$currentClass) continue;

                    $indicator = CompetencyIndicator::where('school_id', $schoolId)->where('grade_level_id', $currentClass->grade_level_id)->first();
                    if (!$indicator) continue;

                    $pItem = PortfolioItem::create([
                        'school_id' => $schoolId,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'title' => "{$subject->name} Activity Evidence",
                        'description' => "Sample evidence for student's work in {$subject->name}.",
                        'type' => 'image',
                        'tags' => json_encode([$subject->name, 'CBC']),
                        'status' => 'reviewed',
                        'review_score' => rand(3, 4),
                        'reviewer_id' => $teachers->random()->user_id,
                        'reviewed_at' => now()->subDays(rand(1, 10))
                    ]);

                    if (method_exists($pItem, 'indicators')) {
                         $pItem->indicators()->attach($indicator->id);
                    }
                }
            }
        }
    }
}
