<?php

namespace App\Jobs;

use App\Models\ExportProcess;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Academic\SchoolClass;
use App\Models\Finance\Invoice;
use App\Models\Finance\FeePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateInstitutionalReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exportProcessId;
    public $timeout = 600; // 10 minutes for heavy reports

    public function __construct(int $exportProcessId)
    {
        $this->exportProcessId = $exportProcessId;
    }

    public function handle(): void
    {
        $process = ExportProcess::find($this->exportProcessId);
        if (!$process || $process->status === 'cancelled') return;

        try {
            $process->update(['status' => 'processing']);
            
            $filters = $process->filters;
            /** @var \App\Models\School $school */
            $school = School::where('id', $process->school_id)->first();
            $themeColor = $school ? $school->getSetting('pdf_theme_color', '#1e40af') : '#1e40af';

            $view = '';
            $data = [];
            $fileName = '';
            $orientation = 'portrait';

            switch ($process->type) {
                case 'attendance_report':
                    $view = 'reports.attendance-pdf';
                    $fileName = 'Attendance_Report_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getAttendanceData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'enrollment_report':
                    $view = 'reports.enrollment-pdf';
                    $fileName = 'Enrollment_Report_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getEnrollmentData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'assessment_report':
                    $view = 'reports.assessment-pdf';
                    $fileName = 'Assessment_Report_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getAssessmentData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'competency_report':
                    $view = 'reports.competency-pdf';
                    $fileName = 'Competency_Report_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getCompetencyData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'learner_report':
                    $view = 'reports.learner-pdf';
                    $fileName = 'Learner_Directory_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getLearnerData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'teacher_report':
                    $view = 'reports.teacher-pdf';
                    $fileName = 'Teacher_Directory_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getTeacherData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                case 'finance_report':
                    $view = 'reports.finance-pdf';
                    $fileName = 'Financial_Summary_' . now()->format('YmdHis') . '.pdf';
                    $data = $this->getFinanceData($process->school_id, $filters, $school, $themeColor);
                    $orientation = 'landscape';
                    break;
                default:
                    throw new \Exception("Unsupported report type: {$process->type}");
            }

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);
            $content = $pdf->output();
            
            $storagePath = "exports/" . $process->user_id . "/" . $fileName;
            Storage::put($storagePath, $content);

            $process->update([
                'status' => 'completed',
                'file_path' => $storagePath,
                'file_name' => $fileName
            ]);

        } catch (\Exception $e) {
            \Log::error("Institutional Report Generation Error: " . $e->getMessage());
            $process->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    protected function getAttendanceData($schoolId, $filters, $school, $themeColor)
    {
        $classId = $filters['class_id'] ?? null;
        $students = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->get();
        
        $studentIds = $students->pluck('id');
        $attRecords = DB::table('student_attendance')
            ->selectRaw('student_id, count(*) as total, sum(case when status = "present" then 1 else 0 end) as present')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $maleTotalPossible = 0;
        $femaleTotalPossible = 0;

        foreach($students as $s) {
            $att = $attRecords->get($s->id);
            $s->att_total = $att ? $att->total : 0;
            $s->att_present = $att ? $att->present : 0;
            $s->att_rate = $s->att_total > 0 ? round(($s->att_present / $s->att_total) * 100) : 0;

            if (strtolower($s->gender) === 'male') {
                $maleCount++;
                $malePresent += $s->att_present;
                $maleTotalPossible += $s->att_total;
            } else if (strtolower($s->gender) === 'female') {
                $femaleCount++;
                $femalePresent += $s->att_present;
                $femaleTotalPossible += $s->att_total;
            }
        }

        return [
            'students' => $students,
            'stats' => (object)[
                'total' => $students->count(),
                'male' => $maleCount,
                'female' => $femaleCount,
                'avg_rate' => $students->avg('att_rate') ?? 0,
                'male_rate' => $maleTotalPossible > 0 ? round(($malePresent / $maleTotalPossible) * 100) : 0,
                'female_rate' => $femaleTotalPossible > 0 ? round(($femalePresent / $femaleTotalPossible) * 100) : 0,
            ],
            'classTitle' => $classId ? SchoolClass::find($classId)?->name : 'All Classes',
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getEnrollmentData($schoolId, $filters, $school, $themeColor)
    {
        $classes = SchoolClass::where('school_id', $schoolId)->with('gradeLevel:id,name')->get();
        $classesData = [];

        $totalMale = 0;
        $totalFemale = 0;

        foreach($classes as $c) {
            $studentsCount = DB::table('students')->where('current_class_id', $c->id)->count();
            $male = DB::table('students')->where('current_class_id', $c->id)
                ->where(fn($q) => $q->where('gender', 'Male')->orWhere('gender', 'male'))->count();
            $female = DB::table('students')->where('current_class_id', $c->id)
                ->where(fn($q) => $q->where('gender', 'Female')->orWhere('gender', 'female'))->count();
            
            $totalMale += $male;
            $totalFemale += $female;

            $classesData[] = (object)[
                'class' => $c->name,
                'grade' => $c->gradeLevel?->name ?? 'N/A',
                'male' => $male,
                'female' => $female,
                'total' => $studentsCount,
                'status' => $studentsCount > 35 ? 'Overcapacity' : ($studentsCount > 15 ? 'Optimal' : 'Low Enrollment')
            ];
        }

        return [
            'classesData' => $classesData,
            'stats' => (object)[
                'total' => $totalMale + $totalFemale,
                'male' => $totalMale,
                'female' => $totalFemale,
                'classes' => $classes->count()
            ],
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getAssessmentData($schoolId, $filters, $school, $themeColor)
    {
        $classId = $filters['class_id'] ?? null;
        $students = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->get();
        $studentIds = $students->pluck('id');
        
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereIn('student_id', $studentIds)->with(['subject', 'student'])->get();
            
        $subjectGroups = $ratings->groupBy('subject_id');
        $assessments = [];
        foreach($subjectGroups as $sid => $group) {
             $subject = $group->first()->subject;
             $avg = $group->avg('rating_level');
             $rate = min(100, round(($avg / 4) * 100));

             $maleAvg = $group->filter(fn($r) => strtolower($r->student->gender) === 'male')->avg('rating_level');
             $femaleAvg = $group->filter(fn($r) => strtolower($r->student->gender) === 'female')->avg('rating_level');

             $assessments[] = (object)[
                 'subject' => $subject?->name ?? 'Core Domain',
                 'total_assessed' => $group->groupBy('student_id')->count(),
                 'average_score' => $rate,
                 'male_avg' => $maleAvg ? min(100, round(($maleAvg / 4) * 100)) : 0,
                 'female_avg' => $femaleAvg ? min(100, round(($femaleAvg / 4) * 100)) : 0,
                 'status' => $rate >= 80 ? 'Excellent' : ($rate >= 50 ? 'Average' : 'Below Average')
             ];
        }

        return [
            'assessmentsData' => $assessments,
            'stats' => (object)[
                'total_assessed' => $ratings->groupBy('student_id')->count(),
                'avg_score' => $ratings->avg('rating_level') ? min(100, round(($ratings->avg('rating_level') / 4) * 100)) : 0
            ],
            'classTitle' => $classId ? SchoolClass::find($classId)?->name : 'All Classes',
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getCompetencyData($schoolId, $filters, $school, $themeColor)
    {
        $classId = $filters['class_id'] ?? null;
        $ratings = \App\Models\Assessment\StudentCompetencyRating::whereHas('student', function($q) use ($schoolId, $classId) {
                $q->where('school_id', $schoolId);
                if ($classId) $q->where('current_class_id', $classId);
            })
            ->with(['competency', 'subject', 'student'])
            ->get();
            
        $groups = $ratings->groupBy('competency_id');
        $competencies = [];
        
        foreach($groups as $cid => $group) {
             $comp = $group->first()->competency;
             $subj = $group->first()->subject;
             $levelArray = $group->pluck('rating_level');
             
             $competencies[] = (object)[
                 'competency' => $comp?->name ?? 'Core Competency',
                 'subject' => $subj?->name ?? 'General',
                 'ee' => $levelArray->filter(fn($l) => $l >= 4)->count(),
                 'me' => $levelArray->filter(fn($l) => $l == 3)->count(),
                 'ae' => $levelArray->filter(fn($l) => $l == 2)->count(),
                 'be' => $levelArray->filter(fn($l) => $l <= 1)->count(),
                 'male_avg' => $group->filter(fn($r) => strtolower($r->student->gender) === 'male')->avg('rating_level'),
                 'female_avg' => $group->filter(fn($r) => strtolower($r->student->gender) === 'female')->avg('rating_level'),
                 'total' => $group->count()
             ];
        }

        return [
            'competencyData' => $competencies,
            'stats' => (object)[
                'total_ratings' => $ratings->count(),
                'competencies_count' => $groups->count()
            ],
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getLearnerData($schoolId, $filters, $school, $themeColor)
    {
        $classId = $filters['class_id'] ?? null;
        $gender = $filters['gender'] ?? null;
        $status = $filters['status'] ?? null;

        $learners = Student::where('school_id', $schoolId)
            ->when($classId, fn($q) => $q->where('current_class_id', $classId))
            ->when($gender, fn($q) => $q->where('gender', $gender))
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('currentClass:id,name')
            ->get();

        return [
            'learners' => $learners,
            'stats' => (object)[
                'total' => $learners->count(),
                'male' => $learners->filter(fn($s) => strtolower($s->gender) === 'male')->count(),
                'female' => $learners->filter(fn($s) => strtolower($s->gender) === 'female')->count(),
                'active' => $learners->where('status', 'active')->count()
            ],
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getTeacherData($schoolId, $filters, $school, $themeColor)
    {
        $teachers = Teacher::where('school_id', $schoolId)->with('department:id,name')->get();

        return [
            'teachers' => $teachers,
            'stats' => (object)[
                'total' => $teachers->count(),
                'male' => $teachers->filter(fn($t) => strtolower($t->gender) === 'male')->count(),
                'female' => $teachers->filter(fn($t) => strtolower($t->gender) === 'female')->count(),
                'departments' => $teachers->pluck('department_id')->unique()->count()
            ],
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }

    protected function getFinanceData($schoolId, $filters, $school, $themeColor)
    {
        $invoices = Invoice::where('school_id', $schoolId)->with('student:id,full_name,admission_number,gender')->get();
        $payments = FeePayment::where('school_id', $schoolId)->where('status', 'successful')->get();

        $studentsData = [];
        $totalBilled = 0;
        $totalPaid = 0;
        
        $maleBilled = 0;
        $femaleBilled = 0;
        $malePaid = 0;
        $femalePaid = 0;

        foreach($invoices->groupBy('student_id') as $sid => $invs) {
            $student = $invs->first()->student;
            $billed = $invs->sum('total');
            $paid = $payments->where('student_id', $sid)->sum('amount');
            $balance = max(0, $billed - $paid);
            
            $totalBilled += $billed;
            $totalPaid += $paid;

            if ($student && strtolower($student->gender) === 'male') {
                $maleBilled += $billed;
                $malePaid += $paid;
            } else {
                $femaleBilled += $billed;
                $femalePaid += $paid;
            }
            
            $studentsData[] = (object)[
                'student' => $student?->full_name ?? 'Unknown Student',
                'adm' => $student?->admission_number ?? 'N/A',
                'gender' => $student?->gender ?? 'N/A',
                'billed' => $billed,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $balance <= 0 ? 'Cleared' : 'Pending'
            ];
        }

        return [
            'records' => $studentsData,
            'summary' => (object)[
                'totalBilled' => $totalBilled,
                'totalPaid' => $totalPaid,
                'outstanding' => max(0, $totalBilled - $totalPaid),
                'maleBilled' => $maleBilled,
                'malePaid' => $malePaid,
                'femaleBilled' => $femaleBilled,
                'femalePaid' => $femalePaid,
                'collection_rate' => $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0
            ],
            'school' => $school,
            'themeColor' => $themeColor
        ];
    }
}
