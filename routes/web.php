<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\AcademicManagementController;

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
    Route::get('students/enrollments/groups/{class}', [StudentEnrollmentController::class, 'show'])->name('students.enrollments.groups.show');
    Route::get('students/create', [StudentsController::class, 'create'])->name('students.create');
    Route::post('students', [StudentsController::class, 'store'])->name('students.store');
    Route::get('students/{student}', [StudentsController::class, 'show'])->name('students.show');
    Route::get('students/{student}/edit', [StudentsController::class, 'edit'])->name('students.edit');
    Route::put('students/{student}', [StudentsController::class, 'update'])->name('students.update');
    Route::patch('students/{student}/suspend', [StudentsController::class, 'suspend'])->name('students.suspend');
    Route::patch('students/{student}/activate', [StudentsController::class, 'activate'])->name('students.activate');
    Route::patch('students/{student}/demote', [StudentsController::class, 'demote'])->name('students.demote');
    Route::patch('students/{student}/transfer', [StudentsController::class, 'transfer'])->name('students.transfer');
    Route::post('students/promote', [StudentsController::class, 'promote'])->name('students.promote');
    Route::delete('students/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');

    // Teachers
    Route::inertia('teachers', 'teachers/Index')->name('teachers.index');
    Route::inertia('teachers/create', 'teachers/Create')->name('teachers.create');
    Route::inertia('teachers/{id}', 'teachers/Show')->name('teachers.show');

    // Classes
    Route::get('classes', [AcademicManagementController::class, 'classes'])->name('classes.index');
    Route::get('classes/allocations', [AcademicManagementController::class, 'allocations'])->name('classes.allocations');
    Route::get('classes/create', [AcademicManagementController::class, 'createClass'])->name('classes.create');
    Route::post('classes', [AcademicManagementController::class, 'storeClass'])->name('classes.store');
    Route::post('classes/bulk-action', [AcademicManagementController::class, 'bulkAction'])->name('classes.bulk-action');
    Route::get('classes/{id}', [AcademicManagementController::class, 'showClass'])->name('classes.show');
    Route::get('classes/{id}/edit', [AcademicManagementController::class, 'editClass'])->name('classes.edit');
    Route::put('classes/{id}', [AcademicManagementController::class, 'updateClass'])->name('classes.update');
    Route::delete('classes/{id}', [AcademicManagementController::class, 'destroyClass'])->name('classes.destroy');

    // Grades
    Route::get('grades', [AcademicManagementController::class, 'grades'])->name('grades.index');
    Route::get('grades/create', [AcademicManagementController::class, 'createGrade'])->name('grades.create');
    Route::post('grades', [AcademicManagementController::class, 'storeGrade'])->name('grades.store');
    Route::post('grades/bulk-action', [AcademicManagementController::class, 'bulkGradeAction'])->name('grades.bulk-action');
    Route::get('grades/{id}', [AcademicManagementController::class, 'showGrade'])->name('grades.show');
    Route::get('grades/{id}/edit', [AcademicManagementController::class, 'editGrade'])->name('grades.edit');
    Route::put('grades/{id}', [AcademicManagementController::class, 'updateGrade'])->name('grades.update');
    Route::patch('grades/{id}/activate', [AcademicManagementController::class, 'activateGrade'])->name('grades.activate');
    Route::patch('grades/{id}/deactivate', [AcademicManagementController::class, 'deactivateGrade'])->name('grades.deactivate');
    Route::delete('grades/{id}', [AcademicManagementController::class, 'destroyGrade'])->name('grades.destroy');

    // Streams
    Route::get('streams', [AcademicManagementController::class, 'streams'])->name('streams.index');
    Route::get('streams/create', [AcademicManagementController::class, 'createStream'])->name('streams.create');
    Route::post('streams', [AcademicManagementController::class, 'storeStream'])->name('streams.store');
    Route::post('streams/bulk-action', [AcademicManagementController::class, 'bulkStreamAction'])->name('streams.bulk-action');
    Route::get('streams/{id}', [AcademicManagementController::class, 'showStream'])->name('streams.show');
    Route::get('streams/{id}/edit', [AcademicManagementController::class, 'editStream'])->name('streams.edit');
    Route::put('streams/{id}', [AcademicManagementController::class, 'updateStream'])->name('streams.update');
    Route::patch('streams/{id}/activate', [AcademicManagementController::class, 'activateStream'])->name('streams.activate');
    Route::patch('streams/{id}/deactivate', [AcademicManagementController::class, 'deactivateStream'])->name('streams.deactivate');
    Route::delete('streams/{id}', [AcademicManagementController::class, 'destroyStream'])->name('streams.destroy');

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
