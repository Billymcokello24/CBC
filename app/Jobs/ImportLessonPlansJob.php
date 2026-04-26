<?php

namespace App\Jobs;

use App\Models\Curriculum\LessonPlan;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolClass;
use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportLessonPlansJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $classId;
    protected $subjectId;
    protected $schoolId;
    protected $userId;
    protected $importProcessId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $classId, int $subjectId, int $schoolId, int $userId, int $importProcessId = null)
    {
        $this->filePath = $filePath;
        $this->classId = $classId;
        $this->subjectId = $subjectId;
        $this->schoolId = $schoolId;
        $this->userId = $userId;
        $this->importProcessId = $importProcessId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        
        try {
            $handle = fopen($fullPath, 'r');
            if (!$handle) return;

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return;
            }

            // Estimate total rows (optional but good for progress)
            $totalLines = count(file($fullPath)) - 1;
            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'processing',
                    'total_rows' => $totalLines
                ]);
            }

            $teacher = Teacher::where('user_id', $this->userId)->first();
            $academic_term_id = AcademicTerm::whereHas('academicYear', fn($q) => $q->where('is_current', true))->first()?->id;
            $grade_level_id = SchoolClass::find($this->classId)?->grade_level_id;

            $i = 0;
            while (($row = fgetcsv($handle)) !== FALSE) {
                $i++;
                if ($this->importProcessId && $i % 5 === 0) {
                    \App\Models\ImportProcess::where('id', $this->importProcessId)->update(['processed_rows' => $i]);
                }
                if (count($header) !== count($row)) continue;
                $data = array_combine($header, $row);
                
                $strandId = null;
                if (!empty($data['strand'])) {
                    $strandName = trim($data['strand']);
                    $strand = Strand::whereRaw('LOWER(name) = ?', [strtolower($strandName)])
                        ->where('subject_id', $this->subjectId)
                        ->where('grade_level_id', $grade_level_id)
                        ->where('school_id', $this->schoolId)
                        ->first();

                    if (!$strand) {
                        $strand = Strand::create([
                            'name' => Str::title($strandName),
                            'subject_id' => $this->subjectId,
                            'grade_level_id' => $grade_level_id,
                            'school_id' => $this->schoolId,
                            'is_active' => true,
                            'code' => strtoupper(Str::slug($strandName, '_')) . '-' . rand(100, 999),
                        ]);
                    }
                    $strandId = $strand->id;
                }

                $subStrandId = null;
                if ($strandId && !empty($data['sub_strand'])) {
                    $subStrandName = trim($data['sub_strand']);
                    $subStrand = SubStrand::whereRaw('LOWER(name) = ?', [strtolower($subStrandName)])
                        ->where('strand_id', $strandId)
                        ->where('school_id', $this->schoolId)
                        ->first();

                    if (!$subStrand) {
                        $subStrand = SubStrand::create([
                            'name' => Str::title($subStrandName),
                            'strand_id' => $strandId,
                            'school_id' => $this->schoolId,
                            'is_active' => true,
                            'code' => strtoupper(Str::slug($subStrandName, '_')) . '-' . rand(100, 999),
                        ]);
                    }
                    $subStrandId = $subStrand->id;
                }

                LessonPlan::create([
                    'school_id' => $this->schoolId,
                    'class_id' => $this->classId,
                    'subject_id' => $this->subjectId,
                    'teacher_id' => $teacher?->id,
                    'academic_term_id' => $academic_term_id,
                    'strand_id' => $strandId,
                    'sub_strand_id' => $subStrandId,
                    'title' => $data['title'] ?? 'Untitled Lesson',
                    'lesson_date' => $data['date'] ?? now()->toDateString(),
                    'week_number' => $data['week'] ?? null,
                    'period_number' => $data['lesson'] ?? null,
                    'duration_minutes' => $data['duration'] ?? 35,
                    'number_of_learners' => $data['learners'] ?? null,
                    'learning_outcomes' => $data['outcomes'] ?? null,
                    'key_vocabulary' => $data['vocabulary'] ?? null,
                    'core_competencies' => isset($data['competencies']) ? array_map('trim', explode(',', $data['competencies'])) : [],
                    'values' => isset($data['values']) ? array_map('trim', explode(',', $data['values'])) : [],
                    'life_skills' => isset($data['life_skills']) ? array_map('trim', explode(',', $data['life_skills'])) : [],
                    'teaching_aids' => $data['aids'] ?? null,
                    'references' => $data['references'] ?? null,
                    'introduction' => $data['introduction'] ?? null,
                    'learning_activities' => isset($data['activities']) ? array_map('trim', preg_split('/[;|\n]/', $data['activities'])) : [],
                    'teacher_activities' => $data['teacher_activities'] ?? null,
                    'learner_activities' => $data['learner_activities'] ?? null,
                    'conclusion' => $data['conclusion'] ?? null,
                    'assessment_methods' => $data['assessment'] ?? null,
                    'reflection' => $data['reflection'] ?? null,
                    'homework' => $data['homework'] ?? null,
                    'pci' => isset($data['pci']) ? array_map('trim', explode(',', $data['pci'])) : [],
                    'inquiry_questions' => $data['questions'] ?? null,
                    'status' => 'draft',
                ]);
            }

            fclose($handle);
            Storage::delete($this->filePath);

            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'completed',
                    'processed_rows' => isset($i) ? $i : 0
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('ImportLessonPlansJob Error: ' . $e->getMessage());
            if ($this->importProcessId) {
                \App\Models\ImportProcess::where('id', $this->importProcessId)->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
}
