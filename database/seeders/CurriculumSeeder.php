<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\School;

class CurriculumSeeder extends Seeder
{
    public function run()
    {
        $school = School::first();
        if (!$school) return;
        
        $curriculum = [
            'Mathematics' => [
                'Numbers' => [
                    'Whole Numbers' => [
                        'Reads and writes numbers up to 10,000 in symbols and words',
                        'Identifies place value of digits in numbers up to 10,000',
                        'Orders and compares whole numbers up to 10,000'
                    ],
                    'Addition' => [
                        'Adds up to two 4-digit numbers with regrouping',
                        'Solves real life problems involving addition of whole numbers'
                    ]
                ],
                'Measurement' => [
                    'Length' => [
                        'Measures length in metres and centimetres correctly',
                        'Estimates length of common objects'
                    ]
                ]
            ],
            'English' => [
                'Listening' => [
                    'Polite Language' => [
                        'Uses appropriate polite language like "Please", "Thank you"',
                        'Responds appropriately to polite requests'
                    ],
                    'Listening for Info' => [
                        'Listen to a short story and answer oral questions',
                        'Follow multi-step oral instructions'
                    ]
                ]
            ],
            'Science and Technology' => [
                'Living Things' => [
                    'Plants' => [
                        'Identify different types of plants in the environment',
                        'Describe functions of roots, stems, and leaves'
                    ],
                    'Animals' => [
                        'Classify animals into vertebrates and invertebrates',
                        'Describe characteristics of mammals'
                    ]
                ]
            ],
            'Kiswahili' => [
                'Kusikiliza na Kuzungumza' => [
                    'Adabu na Maamkizi' => [
                        'Anatumia maamkizi mwafaka katika mazingira mbalimbali',
                        'Anajibu salamu kwa heshima na adabu'
                    ]
                ]
            ],
            'Social Studies' => [
                'Our District' => [
                    'Physical Features' => [
                        'Identifies physical features in the local environment',
                        'Importance of conserving physical features'
                    ]
                ]
            ]
        ];

        foreach ($curriculum as $subjectName => $strandsData) {
            $subjectCode = strtoupper(substr($subjectName, 0, 4)) . "-STD";
            
            $subjectId = DB::table('subjects')->where('code', $subjectCode)->value('id');
            if (!$subjectId) {
                $subjectId = DB::table('subjects')->insertGetId([
                    'school_id' => $school->id,
                    'name' => $subjectName,
                    'code' => $subjectCode,
                    'is_active' => true,
                    'subject_type' => 'core',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            foreach ($strandsData as $strandName => $subStrandsData) {
                $strandCode = strtoupper(substr($strandName, 0, 3)) . "-" . rand(100, 999);
                
                $strandId = DB::table('strands')->insertGetId([
                    'school_id' => $school->id,
                    'subject_id' => $subjectId,
                    'name' => $strandName,
                    'code' => $strandCode,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($subStrandsData as $subStrandName => $indicatorsData) {
                    $subStrandCode = strtoupper(substr($subStrandName, 0, 3)) . "-" . rand(100, 999);
                    
                    $subStrandId = DB::table('sub_strands')->insertGetId([
                        'school_id' => $school->id,
                        'strand_id' => $strandId,
                        'name' => $subStrandName,
                        'code' => $subStrandCode,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $gradeLevels = DB::table('grade_levels')->where('school_id', $school->id)->get();
                    foreach ($gradeLevels as $grade) {
                        foreach ($indicatorsData as $index => $text) {
                            DB::table('competency_indicators')->insert([
                                'school_id' => $school->id,
                                'grade_level_id' => $grade->id,
                                'sub_strand_id' => $subStrandId,
                                'indicator' => $text,
                                'code' => "IND-".rand(1000, 9999),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
        }
        
        echo "Curriculum Data Provisioned for Standard School Context.\n";
    }
}
