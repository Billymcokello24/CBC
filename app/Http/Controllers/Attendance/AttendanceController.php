<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Academic\SchoolClass;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\AcademicTerm;
use App\Models\Attendance\StudentAttendance;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        // Get classes assigned to this teacher (either as class teacher or subject teacher)
        $classIds = \App\Models\TeacherSubject::where('teacher_id', $teacher?->id)->pluck('class_id')
            ->merge(SchoolClass::where('class_teacher_id', $user->id)->pluck('id'))
            ->unique();

        $classes = SchoolClass::whereIn('id', $classIds)
            ->with(['gradeLevel', 'stream'])
            ->get();

        return Inertia::render('attendance/Mark', [
            'classes' => $classes,
        ]);
    }

    public function create(int $classId): Response
    {
        $class = SchoolClass::with(['students' => fn($q) => $q->where('status', 'active')])->findOrFail($classId);
        
        $activeYear = AcademicYear::where('is_current', true)->first();
        $activeTerm = AcademicTerm::where('academic_year_id', $activeYear?->id)->where('is_current', true)->first();

        // Check if attendance already marked for today
        $existing = StudentAttendance::where('class_id', $classId)
            ->where('attendance_date', today())
            ->get()
            ->keyBy('student_id');

        return Inertia::render('attendance/MarkClass', [
            'classroom' => $class,
            'students' => $class->students,
            'existing' => $existing,
            'activeYear' => $activeYear,
            'activeTerm' => $activeTerm,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required',
            'academic_term_id' => 'required',
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.notes' => 'nullable|string',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->first();

        foreach ($validated['attendance'] as $record) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_id' => $validated['class_id'],
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'academic_year_id' => $validated['academic_year_id'],
                    'academic_term_id' => $validated['academic_term_id'],
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null,
                    'marked_by' => $teacher?->id,
                    'marked_at' => now(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance marked successfully.');
    }
}
