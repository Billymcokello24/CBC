<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\StudentEnrollmentController;

Route::inertia('/', 'Welcome', [
    'canLogin' => true,
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('students', [StudentsController::class, 'index'])->name('students.index');
    Route::get('students/enrollments', [StudentEnrollmentController::class, 'index'])->name('students.enrollments');
    Route::get('students/create', [StudentsController::class, 'create'])->name('students.create');
    Route::post('students', [StudentsController::class, 'store'])->name('students.store');
    Route::get('students/{student}', [StudentsController::class, 'show'])->name('students.show');
    Route::get('students/{student}/edit', [StudentsController::class, 'edit'])->name('students.edit');
    Route::put('students/{student}', [StudentsController::class, 'update'])->name('students.update');
    Route::patch('students/{student}/suspend', [StudentsController::class, 'suspend'])->name('students.suspend');
    Route::patch('students/{student}/activate', [StudentsController::class, 'activate'])->name('students.activate');
    Route::patch('students/{student}/demote', [StudentsController::class, 'demote'])->name('students.demote');
    Route::post('students/promote', [StudentsController::class, 'promote'])->name('students.promote');
    Route::delete('students/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');

    // Teachers
    Route::inertia('teachers', 'teachers/Index')->name('teachers.index');
    Route::inertia('teachers/create', 'teachers/Create')->name('teachers.create');
    Route::inertia('teachers/{id}', 'teachers/Show')->name('teachers.show');

    // Classes
    Route::inertia('classes', 'classes/Index')->name('classes.index');
    Route::inertia('classes/create', 'classes/Create')->name('classes.create');
    Route::inertia('classes/{id}', 'classes/Show')->name('classes.show');

    // Guardians
    Route::inertia('guardians', 'guardians/Index')->name('guardians.index');

    // Curriculum
    Route::inertia('curriculum', 'curriculum/Index')->name('curriculum.index');
    Route::inertia('curriculum/learning-areas', 'curriculum/LearningAreas')->name('curriculum.learning-areas');
    Route::inertia('curriculum/subjects', 'curriculum/Subjects')->name('curriculum.subjects');

    // Assessments
    Route::inertia('assessments', 'assessments/Index')->name('assessments.index');
    Route::inertia('assessments/create', 'assessments/Create')->name('assessments.create');
    Route::inertia('assessments/grading', 'assessments/Grading')->name('assessments.grading');
    Route::inertia('assessments/report-cards', 'assessments/ReportCards')->name('assessments.report-cards');

    // Attendance
    Route::inertia('attendance', 'attendance/Index')->name('attendance.index');
    Route::inertia('attendance/mark', 'attendance/Mark')->name('attendance.mark');
    Route::inertia('attendance/reports', 'attendance/Reports')->name('attendance.reports');

    // Finance
    Route::inertia('finance', 'finance/Index')->name('finance.index');
    Route::inertia('finance/fee-structures', 'finance/FeeStructures')->name('finance.fee-structures');
    Route::inertia('finance/invoices', 'finance/Invoices')->name('finance.invoices');
    Route::inertia('finance/payments', 'finance/Payments')->name('finance.payments');
    Route::inertia('finance/payments/create', 'finance/CreatePayment')->name('finance.payments.create');

    // Communication
    Route::inertia('communication', 'communication/Index')->name('communication.index');
    Route::inertia('communication/announcements', 'communication/Announcements')->name('communication.announcements');
    Route::inertia('communication/messages', 'communication/Messages')->name('communication.messages');

    // Library
    Route::inertia('library', 'library/Index')->name('library.index');
    Route::inertia('library/books', 'library/Books')->name('library.books');

    // Transport
    Route::inertia('transport', 'transport/Index')->name('transport.index');
    Route::inertia('transport/vehicles', 'transport/Vehicles')->name('transport.vehicles');
    Route::inertia('transport/routes', 'transport/Routes')->name('transport.routes');

    // Hostel
    Route::inertia('hostel', 'hostel/Index')->name('hostel.index');

    // Health
    Route::inertia('health', 'health/Index')->name('health.index');
    Route::inertia('health/records', 'health/Records')->name('health.records');

    // Events
    Route::inertia('events', 'events/Index')->name('events.index');
    Route::inertia('events/clubs', 'events/Clubs')->name('events.clubs');

    // Career Guidance
    Route::inertia('career', 'career/Index')->name('career.index');
    Route::inertia('career/pathways', 'career/Pathways')->name('career.pathways');

    // Reports & Analytics
    Route::inertia('reports', 'reports/Index')->name('reports.index');
    Route::inertia('reports/academic', 'reports/Academic')->name('reports.academic');
    Route::inertia('reports/financial', 'reports/Financial')->name('reports.financial');
    Route::inertia('reports/attendance', 'reports/Attendance')->name('reports.attendance');
    Route::inertia('reports/analytics', 'reports/Analytics')->name('reports.analytics');

    // Timetable
    Route::inertia('timetable', 'timetable/Index')->name('timetable.index');
});

require __DIR__.'/settings.php';
