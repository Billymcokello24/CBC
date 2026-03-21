<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Timetable;
use App\Models\Academic\TimetableSlot;
use App\Models\Academic\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TimetableController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        // If not a teacher, show the global classes list for timetables
        if (!$teacher) {
            return $this->globalIndex();
        }

        $slots = TimetableSlot::where('teacher_id', $teacher->id)
            ->with(['subject', 'schoolClass.gradeLevel', 'schoolClass.stream', 'periodDefinition'])
            ->get()
            ->map(fn($slot) => [
                'id' => $slot->id,
                'subject' => $slot->subject?->name,
                'class' => $slot->schoolClass?->name,
                'class_id' => $slot->class_id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time->format('H:i'),
                'end_time' => $slot->end_time->format('H:i'),
                'room' => $slot->room_number,
                'period_id' => $slot->period_definition_id,
            ]);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Mock periods for now or fetch from DB
        $periods = [
            ['id' => 1, 'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:40', 'type' => 'lesson'],
            ['id' => 2, 'name' => 'Period 2', 'start_time' => '08:40', 'end_time' => '09:20', 'type' => 'lesson'],
            ['id' => 3, 'name' => 'Short Break', 'start_time' => '09:20', 'end_time' => '09:40', 'type' => 'break'],
            ['id' => 4, 'name' => 'Period 3', 'start_time' => '09:40', 'end_time' => '10:20', 'type' => 'lesson'],
            ['id' => 5, 'name' => 'Period 4', 'start_time' => '10:20', 'end_time' => '11:00', 'type' => 'lesson'],
            ['id' => 6, 'name' => 'Long Break', 'start_time' => '11:00', 'end_time' => '11:30', 'type' => 'break'],
            ['id' => 7, 'name' => 'Period 5', 'start_time' => '11:30', 'end_time' => '12:10', 'type' => 'lesson'],
        ];

        return Inertia::render('timetables/Personalized', [
            'timetable' => $slots,
            'teacher' => $teacher,
            'days' => $days,
            'periods' => $periods,
        ]);
    }

    protected function globalIndex(): Response
    {
        $classes = SchoolClass::with(['gradeLevel', 'stream'])
            ->where('status', 'active')
            ->get();

        return Inertia::render('timetables/GlobalIndex', [
            'classes' => $classes,
        ]);
    }

    public function classTimetable(int $classId): Response
    {
        $timetable = Timetable::where('class_id', $classId)
            ->where('status', 'active')
            ->with(['slots.subject', 'slots.teacher', 'slots.periodDefinition'])
            ->first();

        return Inertia::render('timetables/ClassView', [
            'timetable' => $timetable,
            'classId' => $classId,
        ]);
    }
}
