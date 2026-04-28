<?php

namespace App\Jobs;

use App\Models\ExportProcess;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Academic\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneratePdfExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exportProcessId;
    public $timeout = 300;

    public function __construct(int $exportProcessId)
    {
        $this->exportProcessId = $exportProcessId;
    }

    public function handle(): void
    {
        $process = ExportProcess::find($this->exportProcessId);
        if (!$process) return;

        try {
            $process->update(['status' => 'processing']);
            
            $filters = $process->filters;
            $school = School::find($process->school_id);
            $themeColor = DB::table('school_settings')
                ->where('school_id', $school?->id)
                ->where('key', 'pdf_theme_color')
                ->value('value') ?? '#1e40af';

            $view = '';
            $data = [];
            $fileName = '';

            switch ($process->type) {
                case 'staff':
                    $view = 'pdf.staffs';
                    $fileName = 'Staff_Directory_' . now()->format('YmdHis') . '.pdf';
                    $data = [
                        'items' => $this->getStaffItems($process->school_id, $filters),
                        'school' => $school,
                        'themeColor' => $themeColor
                    ];
                    break;
                case 'students':
                    $view = 'pdf.students';
                    $fileName = 'Student_List_' . now()->format('YmdHis') . '.pdf';
                    $data = [
                        'items' => $this->getStudentItems($process->school_id, $filters),
                        'school' => $school,
                        'themeColor' => $themeColor
                    ];
                    break;
                default:
                    throw new \Exception("Unsupported export type: {$process->type}");
            }

            $pdf = Pdf::loadView($view, $data);
            $content = $pdf->output();
            
            $storagePath = "exports/" . $process->user_id . "/" . $fileName;
            Storage::put($storagePath, $content);

            $process->update([
                'status' => 'completed',
                'file_path' => $storagePath,
                'file_name' => $fileName
            ]);

        } catch (\Exception $e) {
            \Log::error("PDF Generation Error: " . $e->getMessage());
            $process->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    protected function getStaffItems($schoolId, $filters)
    {
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? 'all';
        $deptId = $filters['department_id'] ?? 'all';
        $roleName = $filters['role'] ?? 'all';

        return Teacher::query()
            ->withoutGlobalScopes()
            ->where('teachers.school_id', $schoolId)
            ->leftJoin('departments', 'teachers.department_id', '=', 'departments.id')
            ->with(['department:id,name', 'user'])
            ->select('teachers.*')
            ->when($search !== '', fn ($q) => $q->where(function($sq) use ($search) {
                $sq->where('teachers.first_name', 'like', "%$search%")
                  ->orWhere('teachers.last_name', 'like', "%$search%")
                  ->orWhere('teachers.staff_number', 'like', "%$search%");
            }))
            ->when($status !== 'all', fn ($q) => $q->where('teachers.status', $status))
            ->when($deptId !== 'all', fn ($q) => $q->where('teachers.department_id', $deptId))
            ->when($roleName !== 'all', function($q) use ($roleName) {
                $q->whereHas('user', fn($sq) => $sq->withRole($roleName));
            })
            ->orderBy('departments.name')
            ->orderBy('teachers.first_name')
            ->get();
    }

    protected function getStudentItems($schoolId, $filters)
    {
        $search = $filters['search'] ?? '';
        $classId = $filters['class_id'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $gender = $filters['gender'] ?? 'all';

        return Student::query()
            ->withoutGlobalScopes()
            ->where('students.school_id', $schoolId)
            ->leftJoin('classes', 'students.current_class_id', '=', 'classes.id')
            ->leftJoin('grade_levels', 'classes.grade_level_id', '=', 'grade_levels.id')
            ->with(['currentClass.gradeLevel', 'currentClass.stream'])
            ->select('students.*')
            ->when($search !== '', fn ($q) => $q->where(function($sq) use ($search) {
                $sq->where('students.first_name', 'like', "%$search%")
                  ->orWhere('students.last_name', 'like', "%$search%")
                  ->orWhere('students.admission_number', 'like', "%$search%");
            }))
            ->when($classId !== 'all' && $classId !== '', fn ($q) => $q->where('students.current_class_id', $classId))
            ->when($status !== 'all', fn ($q) => $q->where('students.status', $status))
            ->when($gender !== 'all', fn ($q) => $q->where('students.gender', $gender))
            ->orderBy('grade_levels.level_order')
            ->orderBy('students.first_name')
            ->get();
    }
}
