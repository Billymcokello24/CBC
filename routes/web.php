<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\Academic\TimetableController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\AcademicManagementController;
use App\Http\Controllers\CurriculumManagementController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Library\LibraryController;
use App\Http\Controllers\Transport\TransportController;
use App\Http\Controllers\Hostel\HostelController;
use App\Http\Controllers\Health\HealthController;
use App\Http\Controllers\Communication\CommunicationController;
use App\Http\Controllers\Events\EventsController;
use App\Http\Controllers\Settings\SettingsController;

Route::inertia('/', 'Welcome', [
    'canLogin' => true,
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — accessible to ALL authenticated users (role-specific data returned by controller)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin Routes
    Route::middleware(['role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('impersonate/{school}', [SuperAdminDashboardController::class, 'impersonate'])->name('impersonate');
        Route::post('stop-impersonating', [SuperAdminDashboardController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Schools Management
        Route::resource('schools', SchoolController::class);

        // Global RBAC Management
        Route::resource('roles', \App\Http\Controllers\SuperAdmin\GlobalRolesController::class);
    });

    // ──────────────────────────────────────────────
    // STUDENTS
    // ──────────────────────────────────────────────
    // NOTE: Specific routes (create, export, template) MUST come before wildcard {student} routes
    Route::middleware(['check_permission:students.view'])->group(function () {
        Route::get('guardian/portal', [StudentsController::class, 'guardianDashboard'])->name('guardian.portal');
        Route::get('students/export-pdf', [StudentsController::class, 'exportPdf'])->name('students.export-pdf');
        Route::get('students/template/download', [StudentsController::class, 'downloadTemplate'])->name('students.template.download');
    });

    Route::middleware(['check_permission:students.create'])->group(function () {
        Route::get('students/create', [StudentsController::class, 'create'])->name('students.create');
        Route::post('students', [StudentsController::class, 'store'])->name('students.store');
        Route::post('students/bulk-upload', [StudentsController::class, 'bulkUpload'])->name('students.bulk-upload');
    });

    Route::middleware(['check_permission:students.update'])->group(function () {
        Route::post('students/promote', [StudentsController::class, 'promote'])->name('students.promote');
    });

    Route::middleware(['check_permission:students.delete'])->group(function () {
        Route::post('students/bulk-delete', [StudentsController::class, 'bulkDelete'])->name('students.bulk-delete');
        Route::post('students/enrollments/bulk-delete', [StudentEnrollmentController::class, 'bulkDelete'])->name('students.enrollments.bulk-delete');
    });

    // Wildcard routes AFTER specific routes
    Route::middleware(['check_permission:students.view,students.view_own'])->group(function () {
        Route::get('students', [StudentsController::class, 'index'])->name('students.index');
        Route::get('students/enrollments', [StudentEnrollmentController::class, 'index'])->name('students.enrollments');
        Route::get('students/enrollments/groups/{class}', [StudentEnrollmentController::class, 'show'])->name('students.enrollments.groups.show');
        Route::get('students/{student}', [StudentsController::class, 'show'])->name('students.show');
    });

    Route::middleware(['check_permission:students.create'])->group(function () {
        Route::post('students/{student}/guardian', [StudentsController::class, 'storeGuardian'])->name('students.guardian.store');
    });

    Route::middleware(['check_permission:students.update'])->group(function () {
        Route::get('students/{student}/edit', [StudentsController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}', [StudentsController::class, 'update'])->name('students.update');
        Route::patch('students/{student}/suspend', [StudentsController::class, 'suspend'])->name('students.suspend');
        Route::patch('students/{student}/activate', [StudentsController::class, 'activate'])->name('students.activate');
        Route::patch('students/{student}/demote', [StudentsController::class, 'demote'])->name('students.demote');
    });

    Route::middleware(['check_permission:students.transfer'])->group(function () {
        Route::patch('students/{student}/transfer', [StudentsController::class, 'transfer'])->name('students.transfer');
    });

    Route::middleware(['check_permission:students.delete'])->group(function () {
        Route::delete('students/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');
    });

    // ──────────────────────────────────────────────
    // TEACHERS
    // ──────────────────────────────────────────────
    // NOTE: Specific routes (create, template) MUST come before wildcard {teacher} routes
    Route::middleware(['check_permission:teachers.create'])->group(function () {
        Route::get('teachers/create', [TeachersController::class, 'create'])->name('teachers.create');
        Route::get('teachers/template/download', [TeachersController::class, 'downloadTemplate'])->name('teachers.template.download');
        Route::post('teachers', [TeachersController::class, 'store'])->name('teachers.store');
        Route::post('teachers/bulk-upload', [TeachersController::class, 'bulkUpload'])->name('teachers.bulk-upload');
    });

    Route::middleware(['check_permission:teachers.delete'])->group(function () {
        Route::post('teachers/bulk-delete', [TeachersController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    });

    // Wildcard routes AFTER specific routes
    Route::middleware(['check_permission:teachers.view,teachers.view_own'])->group(function () {
        Route::get('teachers', [TeachersController::class, 'index'])->name('teachers.index');
        Route::get('teachers/{teacher}', [TeachersController::class, 'show'])->name('teachers.show');
    });

    Route::middleware(['check_permission:teachers.update'])->group(function () {
        Route::get('teachers/{teacher}/edit', [TeachersController::class, 'edit'])->name('teachers.edit');
        Route::put('teachers/{teacher}', [TeachersController::class, 'update'])->name('teachers.update');
    });

    Route::middleware(['check_permission:teachers.assign_subjects'])->group(function () {
        Route::post('teachers/{teacher}/assign-class-teacher', [TeachersController::class, 'assignClassTeacher'])->name('teachers.assign-class-teacher');
    });

    Route::middleware(['check_permission:teachers.delete'])->group(function () {
        Route::delete('teachers/{teacher}', [TeachersController::class, 'destroy'])->name('teachers.destroy');
    });

    // ──────────────────────────────────────────────
    // CLASSES, GRADES, STREAMS, DEPARTMENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:classes.view'])->group(function () {
        // Classes
        Route::get('classes', [AcademicManagementController::class, 'classes'])->name('classes.index');
        Route::get('classes/allocations', [AcademicManagementController::class, 'allocations'])->name('classes.allocations');
        Route::get('classes/allocation', [AcademicManagementController::class, 'allocations'])->name('classes.allocation');

        // Grades
        Route::get('grades', [AcademicManagementController::class, 'grades'])->name('grades.index');

        // Streams
        Route::get('streams', [AcademicManagementController::class, 'streams'])->name('streams.index');

        // Departments
        Route::get('departments', [AcademicManagementController::class, 'departments'])->name('departments.index');
        Route::get('departments/export', [AcademicManagementController::class, 'exportDepartments'])->name('departments.export');
        Route::get('departments/create', [AcademicManagementController::class, 'createDepartment'])->name('departments.create');
    });

    Route::middleware(['check_permission:classes.create'])->group(function () {
        Route::get('classes/create', [AcademicManagementController::class, 'createClass'])->name('classes.create');
        Route::post('classes', [AcademicManagementController::class, 'storeClass'])->name('classes.store');
        Route::post('classes/auto-create', [AcademicManagementController::class, 'autoCreateClasses'])->name('classes.auto-create');
        Route::post('classes/allocations', [AcademicManagementController::class, 'storeAllocation'])->name('classes.allocations.store');

        Route::get('grades/create', [AcademicManagementController::class, 'createGrade'])->name('grades.create');
        Route::post('grades', [AcademicManagementController::class, 'storeGrade'])->name('grades.store');

        Route::get('streams/create', [AcademicManagementController::class, 'createStream'])->name('streams.create');
        Route::post('streams', [AcademicManagementController::class, 'storeStream'])->name('streams.store');

        Route::post('departments', [AcademicManagementController::class, 'storeDepartment'])->name('departments.store');
        Route::post('departments/{id}/subjects', [AcademicManagementController::class, 'storeDepartmentSubject'])->name('departments.subjects.store');
    });

    Route::middleware(['check_permission:classes.view'])->group(function () {
        // Classes View Details
        Route::get('classes/{id}/subjects', [AcademicManagementController::class, 'classSubjects'])->name('classes.subjects');
        Route::get('classes/{id}', [AcademicManagementController::class, 'showClass'])->name('classes.show');

        // Grades View Details
        Route::get('grades/{id}/subjects', [AcademicManagementController::class, 'gradeSubjects'])->name('grades.subjects');
        Route::get('grades/{id}/students', [AcademicManagementController::class, 'gradeStudents'])->name('grades.students');
        Route::get('grades/{id}', [AcademicManagementController::class, 'showGrade'])->name('grades.show');

        // Streams View Details
        Route::get('streams/{id}', [AcademicManagementController::class, 'showStream'])->name('streams.show');

        // Departments View Details
        Route::get('departments/{id}', [AcademicManagementController::class, 'showDepartment'])->name('departments.show');
        Route::get('departments/{id}/export-results', [AcademicManagementController::class, 'exportDepartmentResults'])->name('departments.export-results');
    });

    Route::middleware(['check_permission:classes.update'])->group(function () {
        Route::get('classes/{id}/edit', [AcademicManagementController::class, 'editClass'])->name('classes.edit');
        Route::put('classes/{id}', [AcademicManagementController::class, 'updateClass'])->name('classes.update');
        Route::put('classes/allocations/{id}', [AcademicManagementController::class, 'updateAllocation'])->name('classes.allocations.update');
        Route::post('classes/bulk-action', [AcademicManagementController::class, 'bulkAction'])->name('classes.bulk-action');
        Route::patch('classes/{id}/activate', [AcademicManagementController::class, 'activateClass'])->name('classes.activate');
        Route::patch('classes/{id}/deactivate', [AcademicManagementController::class, 'deactivateClass'])->name('classes.deactivate');

        Route::get('grades/{id}/edit', [AcademicManagementController::class, 'editGrade'])->name('grades.edit');
        Route::put('grades/{id}', [AcademicManagementController::class, 'updateGrade'])->name('grades.update');
        Route::post('grades/bulk-action', [AcademicManagementController::class, 'bulkGradeAction'])->name('grades.bulk-action');
        Route::patch('grades/{id}/activate', [AcademicManagementController::class, 'activateGrade'])->name('grades.activate');
        Route::patch('grades/{id}/deactivate', [AcademicManagementController::class, 'deactivateGrade'])->name('grades.deactivate');

        Route::get('streams/{id}/edit', [AcademicManagementController::class, 'editStream'])->name('streams.edit');
        Route::put('streams/{id}', [AcademicManagementController::class, 'updateStream'])->name('streams.update');
        Route::post('streams/bulk-action', [AcademicManagementController::class, 'bulkStreamAction'])->name('streams.bulk-action');
        Route::patch('streams/{id}/activate', [AcademicManagementController::class, 'activateStream'])->name('streams.activate');
        Route::patch('streams/{id}/deactivate', [AcademicManagementController::class, 'deactivateStream'])->name('streams.deactivate');

        Route::get('departments/{id}/edit', [AcademicManagementController::class, 'editDepartment'])->name('departments.edit');
        Route::put('departments/{id}', [AcademicManagementController::class, 'updateDepartment'])->name('departments.update');
        Route::patch('departments/{id}/activate', [AcademicManagementController::class, 'activateDepartment'])->name('departments.activate');
        Route::patch('departments/{id}/deactivate', [AcademicManagementController::class, 'deactivateDepartment'])->name('departments.deactivate');
    });

    Route::middleware(['check_permission:classes.delete'])->group(function () {
        Route::delete('classes/{id}', [AcademicManagementController::class, 'destroyClass'])->name('classes.destroy');
        Route::delete('classes/allocations/{id}', [AcademicManagementController::class, 'destroyAllocation'])->name('classes.allocations.destroy');
        Route::delete('grades/{id}', [AcademicManagementController::class, 'destroyGrade'])->name('grades.destroy');
        Route::delete('streams/{id}', [AcademicManagementController::class, 'destroyStream'])->name('streams.destroy');
        Route::delete('departments/{id}', [AcademicManagementController::class, 'destroyDepartment'])->name('departments.destroy');
        Route::delete('departments/{id}/subjects/{subjectId}', [AcademicManagementController::class, 'destroyDepartmentSubject'])->name('departments.subjects.destroy');
        Route::post('departments/bulk-action', [AcademicManagementController::class, 'bulkDepartmentAction'])->name('departments.bulk-action');
    });

    // Guardians
    Route::middleware(['check_permission:guardians.view,guardians.view_own'])->group(function () {
        Route::inertia('guardians', 'guardians/Index')->name('guardians.index');
    });

    // ──────────────────────────────────────────────
    // CURRICULUM
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:curriculum.view'])->group(function () {
        Route::get('curriculum', [CurriculumManagementController::class, 'index'])->name('curriculum.index');
        Route::get('curriculum/learning-areas', [CurriculumManagementController::class, 'learningAreas'])->name('curriculum.learning-areas');
        Route::get('curriculum/learning-areas/{learningArea}', [CurriculumManagementController::class, 'showLearningArea'])->name('curriculum.learning-areas.show');
        Route::get('curriculum/subjects', [CurriculumManagementController::class, 'subjects'])->name('curriculum.subjects');
        Route::get('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'showSubject'])->name('curriculum.subjects.show');
        Route::get('curriculum/strands', [CurriculumManagementController::class, 'strands'])->name('curriculum.strands');
        Route::get('curriculum/strands/{id}', [CurriculumManagementController::class, 'showStrand'])->name('curriculum.strands.show');
        Route::get('curriculum/competencies', [CurriculumManagementController::class, 'competencies'])->name('curriculum.competencies');
        Route::get('curriculum/competencies/{competency}', [CurriculumManagementController::class, 'showCompetency'])->name('curriculum.competencies.show');
    });

    Route::middleware(['check_permission:curriculum.create'])->group(function () {
        Route::get('curriculum/learning-areas/create', [CurriculumManagementController::class, 'createLearningArea'])->name('curriculum.learning-areas.create');
        Route::post('curriculum/learning-areas', [CurriculumManagementController::class, 'storeLearningArea'])->name('curriculum.learning-areas.store');
        Route::get('curriculum/subjects/create', [CurriculumManagementController::class, 'createSubject'])->name('curriculum.subjects.create');
        Route::post('curriculum/subjects', [CurriculumManagementController::class, 'storeSubject'])->name('curriculum.subjects.store');
        Route::get('curriculum/strands/create', [CurriculumManagementController::class, 'createStrand'])->name('curriculum.strands.create');
        Route::post('curriculum/strands', [CurriculumManagementController::class, 'storeStrand'])->name('curriculum.strands.store');
        Route::get('curriculum/competencies/create', [CurriculumManagementController::class, 'createCompetency'])->name('curriculum.competencies.create');
        Route::post('curriculum/competencies', [CurriculumManagementController::class, 'storeCompetency'])->name('curriculum.competencies.store');
    });

    Route::middleware(['check_permission:curriculum.update'])->group(function () {
        Route::get('curriculum/learning-areas/{learningArea}/edit', [CurriculumManagementController::class, 'editLearningArea'])->name('curriculum.learning-areas.edit');
        Route::put('curriculum/learning-areas/{learningArea}', [CurriculumManagementController::class, 'updateLearningArea'])->name('curriculum.learning-areas.update');
        Route::post('curriculum/learning-areas/bulk-action', [CurriculumManagementController::class, 'bulkLearningAreaAction'])->name('curriculum.learning-areas.bulk-action');
        Route::get('curriculum/subjects/{subject}/edit', [CurriculumManagementController::class, 'editSubject'])->name('curriculum.subjects.edit');
        Route::get('curriculum/subjects/{subject}/allocate', [CurriculumManagementController::class, 'allocateSubject'])->name('curriculum.subjects.allocate');
        Route::put('curriculum/subjects/{subject}/allocate', [CurriculumManagementController::class, 'saveSubjectAllocations'])->name('curriculum.subjects.allocate.save');
        Route::put('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'updateSubject'])->name('curriculum.subjects.update');
        Route::post('curriculum/subjects/bulk-action', [CurriculumManagementController::class, 'bulkSubjectAction'])->name('curriculum.subjects.bulk-action');
        Route::get('curriculum/strands/{id}/edit', [CurriculumManagementController::class, 'editStrand'])->name('curriculum.strands.edit');
        Route::put('curriculum/strands/{id}', [CurriculumManagementController::class, 'updateStrand'])->name('curriculum.strands.update');
        Route::post('curriculum/strands/bulk-action', [CurriculumManagementController::class, 'bulkStrandAction'])->name('curriculum.strands.bulk-action');
        Route::get('curriculum/competencies/{competency}/edit', [CurriculumManagementController::class, 'editCompetency'])->name('curriculum.competencies.edit');
        Route::put('curriculum/competencies/{competency}', [CurriculumManagementController::class, 'updateCompetency'])->name('curriculum.competencies.update');
        Route::post('curriculum/competencies/bulk-action', [CurriculumManagementController::class, 'bulkCompetencyAction'])->name('curriculum.competencies.bulk-action');
    });

    Route::middleware(['check_permission:curriculum.delete'])->group(function () {
        Route::delete('curriculum/learning-areas/{learningArea}', [CurriculumManagementController::class, 'destroyLearningArea'])->name('curriculum.learning-areas.destroy');
        Route::delete('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'destroySubject'])->name('curriculum.subjects.destroy');
        Route::delete('curriculum/strands/{id}', [CurriculumManagementController::class, 'destroyStrand'])->name('curriculum.strands.destroy');
        Route::delete('curriculum/competencies/{competency}', [CurriculumManagementController::class, 'destroyCompetency'])->name('curriculum.competencies.destroy');
    });

    // ──────────────────────────────────────────────
    // ASSESSMENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:assessments.view,assessments.view_own'])->group(function () {
        Route::get('assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('assessments/results', [AssessmentController::class, 'results'])->name('assessments.results');
        Route::get('assessments/bulk-upload', [AssessmentController::class, 'bulkUploadView'])->name('assessments.bulk-upload');
        Route::post('assessments/bulk-upload', [AssessmentController::class, 'bulkUpload'])->name('assessments.bulk-upload.store');
        Route::get('assessments/grading', [AssessmentController::class, 'gradingIndex'])->name('assessments.grading');
        Route::get('assessments/{assessment}/grading', [AssessmentController::class, 'grading'])->name('assessments.grading.show');
        Route::get('assessments/report-cards', [AssessmentController::class, 'reportCards'])->name('assessments.report-cards');
        Route::get('assessments/report-cards/{student}', [AssessmentController::class, 'showReport'])->name('assessments.report-cards.show');
        Route::get('assessments/rubrics', [AssessmentController::class, 'rubrics'])->name('assessments.rubrics');
        Route::get('assessments/results', [AssessmentController::class, 'results'])->name('assessments.results');
        Route::get('assessments/results/export', [AssessmentController::class, 'exportResults'])->name('assessments.results.export');
    });

    Route::middleware(['check_permission:assessments.create'])->group(function () {
        Route::get('assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('assessments', [AssessmentController::class, 'store'])->name('assessments.store');
        Route::get('assessments/rubrics/create', [AssessmentController::class, 'rubricCreate'])->name('assessments.rubrics.create');
        Route::post('assessments/rubrics', [AssessmentController::class, 'rubricStore'])->name('assessments.rubrics.store');
        Route::get('assessments/import-template', [AssessmentController::class, 'importTemplate'])->name('assessments.import-template');
        Route::post('assessments/import', [AssessmentController::class, 'import'])->name('assessments.import');
        Route::post('assessments/results/import', [AssessmentController::class, 'importResults'])->name('assessments.results.import');
    });

    Route::middleware(['check_permission:assessments.update'])->group(function () {
        Route::get('assessments/rubrics/{id}/edit', [AssessmentController::class, 'rubricEdit'])->name('assessments.rubrics.edit');
        Route::put('assessments/rubrics/{id}', [AssessmentController::class, 'rubricUpdate'])->name('assessments.rubrics.update');
    });

    Route::middleware(['check_permission:assessments.grade'])->group(function () {
        Route::post('assessments/{assessment}/grading', [AssessmentController::class, 'storeGrading'])->name('assessments.store-grading');
    });

    // ──────────────────────────────────────────────
    // ATTENDANCE
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:attendance.view,attendance.view_own'])->group(function () {
        Route::inertia('attendance', 'attendance/Index')->name('attendance.index');
        Route::inertia('attendance/reports', 'attendance/Reports')->name('attendance.reports');
    });

    Route::middleware(['check_permission:attendance.mark'])->group(function () {
        Route::inertia('attendance/mark', 'attendance/Mark')->name('attendance.mark');
    });

    // ──────────────────────────────────────────────
    // FINANCE
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:finance.view,finance.view_own'])->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/fee-structures', [FinanceController::class, 'feeStructures'])->name('finance.fee-structures');
        Route::get('finance/invoices', [FinanceController::class, 'invoices'])->name('finance.invoices');
        Route::get('finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');
    });

    Route::middleware(['check_permission:finance.create'])->group(function () {
        Route::inertia('finance/payments/create', 'finance/CreatePayment')->name('finance.payments.create');
    });

    // ──────────────────────────────────────────────
    // COMMUNICATION
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:communication.messages,communication.notifications'])->group(function () {
        Route::get('communication', [CommunicationController::class, 'index'])->name('communication.index');
        Route::get('communication/messages', [CommunicationController::class, 'messages'])->name('communication.messages');
    });

    Route::middleware(['check_permission:communication.announcements'])->group(function () {
        Route::get('communication/announcements', [CommunicationController::class, 'announcements'])->name('communication.announcements');
    });

    // ──────────────────────────────────────────────
    // LIBRARY
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:library.view'])->group(function () {
        Route::get('library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('library/books', [LibraryController::class, 'books'])->name('library.books');
        Route::get('library/loans', [LibraryController::class, 'loans'])->name('library.loans');
    });

    // ──────────────────────────────────────────────
    // TRANSPORT, HOSTEL, HEALTH
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:transport.view'])->group(function () {
        Route::get('transport', [TransportController::class, 'index'])->name('transport.index');
        Route::get('transport/vehicles', [TransportController::class, 'vehicles'])->name('transport.vehicles');
        Route::get('transport/routes', [TransportController::class, 'routes'])->name('transport.routes');
    });

    Route::middleware(['check_permission:hostel.view'])->group(function () {
        Route::get('hostels', [HostelController::class, 'index'])->name('hostels.index');
        Route::get('hostels/allocations', [HostelController::class, 'allocations'])->name('hostels.allocations');
    });

    Route::middleware(['check_permission:health.view,health.view_own'])->group(function () {
        Route::get('health', [HealthController::class, 'index'])->name('health.index');
        Route::get('health/visits', [HealthController::class, 'visits'])->name('health.visits');
        Route::get('health/records', [HealthController::class, 'records'])->name('health.records');
    });

    // ──────────────────────────────────────────────
    // EVENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:events.view'])->group(function () {
        Route::get('events', [EventsController::class, 'index'])->name('events.index');
        Route::get('events/clubs', [EventsController::class, 'clubs'])->name('events.clubs');
        Route::get('events/list', [EventsController::class, 'events'])->name('events.list');
    });

    // Career Guidance — accessible to all
    Route::inertia('career', 'career/Index')->name('career.index');
    Route::inertia('career/pathways', 'career/Pathways')->name('career.pathways');

    // ──────────────────────────────────────────────
    // REPORTS & ANALYTICS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:reports.view,reports.view_own'])->group(function () {
        Route::inertia('reports', 'reports/Index')->name('reports.index');
        Route::inertia('reports/academic', 'reports/Academic')->name('reports.academic');
        Route::inertia('reports/financial', 'reports/Financial')->name('reports.financial');
        Route::inertia('reports/attendance', 'reports/Attendance')->name('reports.attendance');
        Route::inertia('reports/analytics', 'reports/Analytics')->name('reports.analytics');
    });

    // ──────────────────────────────────────────────
    // TIMETABLE
    // ──────────────────────────────────────────────
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('timetable/my', [TimetableController::class, 'index'])->name('timetable.my');
        Route::get('timetable/class/{classId}', [TimetableController::class, 'classTimetable'])->name('timetable.class');
        
        Route::middleware(['check_permission:timetable.view'])->group(function () {
            Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
        });
    });

    // ──────────────────────────────────────────────
    // ATTENDANCE
    // ──────────────────────────────────────────────
    Route::middleware(['auth', 'verified', 'check_permission:attendance.view,attendance.view_own'])->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/class/{classId}', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    });

    Route::middleware(['check_permission:settings.view,settings.view_own'])->group(function () {
        Route::get('settings/school', [SettingsController::class, 'schoolProfile'])->name('settings.school');
        Route::get('settings/academic-year', [SettingsController::class, 'academicSettings'])->name('settings.academic-year');
        Route::get('settings/users', [SettingsController::class, 'users'])->name('settings.users');
        Route::get('settings/system', [SettingsController::class, 'system'])->name('settings.system');
    });
});

require __DIR__.'/settings.php';
