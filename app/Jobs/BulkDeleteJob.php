<?php

namespace App\Jobs;

use App\Models\ExportProcess;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BulkDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $process_id;

    /**
     * Create a new job instance.
     */
    public function __construct($process_id)
    {
        $this->process_id = $process_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $process = ExportProcess::find($this->process_id);
        if (!$process || $process->status === 'cancelled') return;

        $process->update(['status' => 'processing']);

        try {
            DB::beginTransaction();

            $type = $process->type; // delete_students or delete_staff
            $filters = $process->filters;
            $school_id = $process->school_id;

            if ($type === 'delete_students') {
                $query = Student::where('school_id', $school_id);

                if (isset($filters['all']) && $filters['all']) {
                    $f = $filters['filters'] ?? [];
                    if (!empty($f['search'])) $query->search($f['search']);
                    if (!empty($f['status']) && $f['status'] !== 'all') $query->where('status', $f['status']);
                    if (!empty($f['gender']) && $f['gender'] !== 'all') $query->where('gender', $f['gender']);
                    if (!empty($f['class_id'])) $query->where('current_class_id', $f['class_id']);
                } else {
                    $query->whereIn('id', $filters['ids'] ?? []);
                }

                $studentIds = $query->pluck('id');
                $userIds = Student::whereIn('id', $studentIds)->pluck('user_id')->filter();

                // Delete related records via model events (Student has thorough boots)
                Student::whereIn('id', $studentIds)->get()->each->delete();
                
                if ($userIds->isNotEmpty()) {
                    User::whereIn('id', $userIds)->delete();
                }

            } elseif ($type === 'delete_staff') {
                $query = Teacher::where('school_id', $school_id);

                if (isset($filters['all']) && $filters['all']) {
                    $f = $filters['filters'] ?? [];
                    if (!empty($f['search'])) $query->search($f['search']);
                    if (!empty($f['status']) && $f['status'] !== 'all') $query->where('status', $f['status']);
                    if (!empty($f['department_id'])) $query->where('department_id', $f['department_id']);
                } else {
                    $query->whereIn('id', $filters['ids'] ?? []);
                }

                $teacherIds = $query->pluck('id');
                $userIds = Teacher::whereIn('id', $teacherIds)->pluck('user_id')->filter();

                // Explicitly nullify class teacher relationships to avoid FK issues
                DB::table('classes')->whereIn('class_teacher_id', $userIds)->update(['class_teacher_id' => null]);
                
                // Delete staff profiles via model events to trigger thorough cleanup
                Teacher::whereIn('id', $teacherIds)->get()->each->delete();
                
                if ($userIds->isNotEmpty()) {
                    User::whereIn('id', $userIds)->delete();
                }

            } elseif ($type === 'delete_parents') {
                $query = \App\Models\Guardian::where('school_id', $school_id);

                if (isset($filters['all']) && $filters['all']) {
                    $f = $filters['filters'] ?? [];
                    if (!empty($f['search'])) $query->search($f['search']);
                    if (!empty($f['status']) && $f['status'] !== 'all') {
                        $query->where('is_active', $f['status'] === 'active');
                    }
                } else {
                    $query->whereIn('id', $filters['ids'] ?? []);
                }

                $guardianIds = $query->pluck('id');
                $userIds = \App\Models\Guardian::whereIn('id', $guardianIds)->pluck('user_id')->filter();

                \App\Models\Guardian::whereIn('id', $guardianIds)->get()->each->delete();
                
                if ($userIds->isNotEmpty()) {
                    User::whereIn('id', $userIds)->delete();
                }
            }

            DB::commit();
            $process->update(['status' => 'completed']);

        } catch (\Exception $e) {
            DB::rollBack();
            $process->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
