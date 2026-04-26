<?php

namespace App\Jobs;

use App\Models\Curriculum\SchemeOfWork;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportSchemeEntriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $schemeId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $schemeId)
    {
        $this->filePath = $filePath;
        $this->schemeId = $schemeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        $scheme = SchemeOfWork::findOrFail($this->schemeId);
        
        try {
            $handle = fopen($fullPath, 'r');
            $header = fgetcsv($handle);
            
            if (!$header) {
                fclose($handle);
                return;
            }

            // Normalize header
            $header = array_map(fn($v) => trim(strtolower(preg_replace('/^\xEF\xBB\xBF/', '', $v))), $header);
            
            DB::transaction(function () use ($handle, $header, $scheme) {
                while (($data = fgetcsv($handle)) !== false) {
                    $row = array_combine($header, array_pad($data, count($header), ''));
                    if (empty(array_filter($row))) continue;

                    $strandId = null;
                    if (!empty($row['strand_name'])) {
                        $strand = Strand::where('subject_id', $scheme->subject_id)
                            ->where('grade_level_id', $scheme->grade_level_id)
                            ->where('name', 'like', '%' . trim($row['strand_name']) . '%')
                            ->first();

                        if (!$strand) {
                            $strand = Strand::create([
                                'subject_id' => $scheme->subject_id,
                                'grade_level_id' => $scheme->grade_level_id,
                                'school_id' => $scheme->school_id,
                                'name' => trim($row['strand_name']),
                                'code' => Str::slug(substr(trim($row['strand_name']), 0, 10)) . '-' . rand(100, 999),
                            ]);
                        }
                        $strandId = $strand->id;
                    }

                    $subStrandId = null;
                    if ($strandId && !empty($row['sub_strand_name'])) {
                        $subStrand = SubStrand::where('strand_id', $strandId)
                            ->where('name', 'like', '%' . trim($row['sub_strand_name']) . '%')
                            ->first();

                        if (!$subStrand) {
                            $subStrand = SubStrand::create([
                                'strand_id' => $strandId,
                                'school_id' => $scheme->school_id,
                                'name' => trim($row['sub_strand_name']),
                                'code' => Str::slug(substr(trim($row['sub_strand_name']), 0, 10)) . '-' . rand(100, 999),
                            ]);
                        }
                        $subStrandId = $subStrand->id;
                    }

                    $scheme->entries()->create([
                        'school_id' => $scheme->school_id,
                        'week_number' => (int)($row['week_number'] ?? 0),
                        'lesson_number' => (int)($row['lesson_number'] ?? 0),
                        'duration_minutes' => (int)($row['duration_minutes'] ?? 35),
                        'lesson_date' => !empty($row['lesson_date']) ? $row['lesson_date'] : null,
                        'strand_id' => $strandId,
                        'sub_strand_id' => $subStrandId,
                        'topic' => $row['topic'] ?? 'Untitled Lesson',
                        'key_vocabulary' => $row['key_vocabulary'] ?? '',
                        'learning_outcomes' => $row['learning_outcomes'] ?? '',
                        'learning_activities' => $row['learning_activities'] ?? '',
                        'teacher_activities' => $row['teacher_activities'] ?? '',
                        'introduction' => $row['introduction'] ?? '',
                        'lesson_development' => $row['lesson_development'] ?? '',
                        'conclusion' => $row['conclusion'] ?? '',
                        'resources' => $row['resources'] ?? '',
                        'references' => $row['references'] ?? '',
                        'assessment' => $row['assessment'] ?? '',
                        'remarks' => $row['remarks'] ?? '',
                        'reflection' => $row['reflection'] ?? '',
                        'homework' => $row['homework'] ?? '',
                        'core_competencies' => !empty($row['core_competencies']) ? explode(',', $row['core_competencies']) : null,
                        'pci' => !empty($row['pci']) ? explode(',', $row['pci']) : null,
                        'inquiry_questions' => $row['inquiry_questions'] ?? '',
                    ]);
                }
            });

            fclose($handle);
            Storage::delete($this->filePath);

        } catch (\Exception $e) {
            \Log::error('ImportSchemeEntriesJob Error: ' . $e->getMessage());
        }
    }
}
