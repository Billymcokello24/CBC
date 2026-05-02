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
use App\Http\Controllers\AssessmentWizardController;
use App\Http\Controllers\Curriculum\CompetencyController;
use App\Http\Controllers\CurriculumManagementController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\AcademicPlannerController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\LearningAreaController;
use App\Http\Controllers\LearningResourceController;
use App\Http\Controllers\StaffsController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Library\LibraryController;
use App\Http\Controllers\Transport\TransportController;
use App\Http\Controllers\Hostel\HostelController;
use App\Http\Controllers\Health\HealthController;
use App\Http\Controllers\Communication\CommunicationController;
use App\Http\Controllers\Events\EventsController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\ParentPortalController;

Route::inertia('/', 'Welcome', [
    'canLogin' => true,
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/modules', [\App\Http\Controllers\LandingController::class, 'modules'])->name('landing.modules');
Route::get('/about', [\App\Http\Controllers\LandingController::class, 'about'])->name('landing.about');
Route::get('/contact', [\App\Http\Controllers\LandingController::class, 'contact'])->name('landing.contact');
Route::get('/pricing', [\App\Http\Controllers\LandingController::class, 'pricing'])->name('landing.pricing');
Route::get('/faq', [\App\Http\Controllers\LandingController::class, 'faq'])->name('landing.faq');
Route::post('/book-demo', [\App\Http\Controllers\LandingController::class, 'bookDemo'])->name('demo.book');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — accessible to ALL authenticated users (role-specific data returned by controller)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ──────────────────────────────────────────────
    // IMPORT MANAGEMENT
    // ──────────────────────────────────────────────
    Route::post('imports/{process}/cancel', [\App\Http\Controllers\ImportController::class, 'cancel'])->name('imports.cancel');
    Route::get('imports/active', [\App\Http\Controllers\ImportController::class, 'getActiveImports'])->name('imports.active');

    // ──────────────────────────────────────────────
    // EXPORT MANAGEMENT
    // ──────────────────────────────────────────────
    Route::post('exports/start', [\App\Http\Controllers\ExportController::class, 'start'])->name('exports.start');
    Route::post('exports/start-delete', [\App\Http\Controllers\ExportController::class, 'startDelete'])->name('exports.start-delete');
    Route::get('exports/{process}/status', [\App\Http\Controllers\ExportController::class, 'status'])->name('exports.status');
    Route::post('exports/{process}/cancel', [\App\Http\Controllers\ExportController::class, 'cancel'])->name('exports.cancel');
    Route::get('exports/{process}/download', [\App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');



    // User Import Status Tracker
    Route::get('/api/user/imports/{importProcess}', function (\App\Models\ImportProcess $importProcess) {
        abort_unless($importProcess->user_id === auth()->id(), 403);
        return response()->json($importProcess);
    });

    // Super Admin Routes
    Route::middleware(['role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('impersonate/{school}', [SuperAdminDashboardController::class, 'impersonate'])->name('impersonate');
        Route::post('stop-impersonating', [SuperAdminDashboardController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Schools Management
        Route::get('schools/status', [\App\Http\Controllers\SuperAdmin\SchoolController::class, 'status'])->name('schools.status');
        Route::get('schools/modules', [\App\Http\Controllers\SuperAdmin\SchoolController::class, 'modules'])->name('schools.modules');
        Route::resource('schools', SchoolController::class);

        // Global User Management
        Route::get('users', [\App\Http\Controllers\SuperAdmin\UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-status', [\App\Http\Controllers\SuperAdmin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::put('users/{user}/reset-password', [\App\Http\Controllers\SuperAdmin\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('users/{user}', [\App\Http\Controllers\SuperAdmin\UserController::class, 'destroy'])->name('users.destroy');

        // Activity Logs
        Route::get('activity-logs', [\App\Http\Controllers\SuperAdmin\ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Broadcasts
        Route::get('broadcasts', [\App\Http\Controllers\SuperAdmin\BroadcastController::class, 'index'])->name('broadcasts.index');
        Route::post('broadcasts', [\App\Http\Controllers\SuperAdmin\BroadcastController::class, 'send'])->name('broadcasts.send');

        // Global System Settings
        Route::get('settings', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/toggle-maintenance', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'toggleMaintenance'])->name('settings.toggle-maintenance');

        // Global RBAC Management
        Route::resource('roles', \App\Http\Controllers\SuperAdmin\GlobalRolesController::class);
    });

    // ──────────────────────────────────────────────
    // PARENT PORTAL
    // ──────────────────────────────────────────────
    Route::prefix('guardian')->name('guardian.')->group(function () {
        Route::get('portal', [ParentPortalController::class, 'dashboard'])->name('portal');
        Route::get('children', [ParentPortalController::class, 'children'])->name('children');
        Route::get('children/{student}', [ParentPortalController::class, 'childProfile'])->name('children.show');
        Route::get('assignments', [ParentPortalController::class, 'assignments'])->name('assignments');
        Route::get('assignments/{assignment}', [ParentPortalController::class, 'assignmentShow'])->name('assignments.show');
        Route::get('assignments/submissions/{submission}/download-marked', [ParentPortalController::class, 'downloadMarkedAssignment'])->name('assignments.submissions.download-marked');
        Route::post('assignments/{assignment}/submit', [ParentPortalController::class, 'submitAssignment'])->name('assignments.submit');
        Route::get('resources', [ParentPortalController::class, 'resources'])->name('resources');
    });

    // ──────────────────────────────────────────────
    // STUDENTS
    // ──────────────────────────────────────────────
    // NOTE: Specific routes (create, export, template) MUST come before wildcard {student} routes
    Route::middleware(['check_permission:students.view'])->group(function () {
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
        Route::get('students/enrollments/create', [StudentEnrollmentController::class, 'create'])->name('students.enrollments.create');
        Route::post('students/enrollments', [StudentEnrollmentController::class, 'store'])->name('students.enrollments.store');
    });

    // Wildcard routes AFTER specific routes
    Route::middleware(['check_permission:students.view,students.view_own'])->group(function () {
        Route::get('students', [StudentsController::class, 'index'])->name('students.index');
        Route::get('students/enrollments', [StudentEnrollmentController::class, 'index'])->name('students.enrollments');
        Route::get('students/enrollments/groups/{class}', [StudentEnrollmentController::class, 'show'])->name('students.enrollments.groups.show');
        Route::get('students/fees/{student}', [\App\Http\Controllers\Finance\FinanceController::class, 'studentFees'])->name('students.fees.show');
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
        Route::patch('students/{student}/withdraw', [StudentsController::class, 'withdraw'])->name('students.withdraw');
        Route::post('students/{student}/photo', [StudentsController::class, 'updatePhoto'])->name('students.photo.update');
    });

    Route::middleware(['check_permission:students.transfer'])->group(function () {
        Route::patch('students/{student}/transfer', [StudentsController::class, 'transfer'])->name('students.transfer');
    });

    Route::middleware(['check_permission:students.delete'])->group(function () {
        Route::delete('students/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');
    });

    // ──────────────────────────────────────────────
    // USERS & STAFFS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:staffs.view'])->group(function () {
        Route::get('staffs/directory', [StaffsController::class, 'directory'])->name('staffs.directory');
        Route::get('staffs/directory/export-pdf', [StaffsController::class, 'exportDirectoryPdf'])->name('staffs.directory.export-pdf');
        Route::get('staffs/directory/{role}', [StaffsController::class, 'roleDirectory'])->name('staffs.role-directory');
        Route::get('staffs/template/download', [StaffsController::class, 'downloadTemplate'])->name('staffs.template.download');
    });

    Route::middleware(['check_permission:staffs.create'])->group(function () {
        Route::get('staffs/create', [StaffsController::class, 'create'])->name('staffs.create');
        Route::post('staffs', [StaffsController::class, 'store'])->name('staffs.store');
        Route::post('staffs/bulk-upload', [StaffsController::class, 'bulkUpload'])->name('staffs.bulk-upload');
    });

    Route::middleware(['check_permission:staffs.delete'])->group(function () {
        Route::post('staffs/bulk-delete', [StaffsController::class, 'bulkDelete'])->name('staffs.bulk-delete');
        Route::delete('staffs/{staff}', [StaffsController::class, 'destroy'])->name('staffs.destroy');
    });

    Route::middleware(['check_permission:staffs.view,staffs.view_own'])->group(function () {
        Route::get('staffs/export-pdf', [StaffsController::class, 'exportPdf'])->name('staffs.export-pdf');
        Route::get('staffs', [StaffsController::class, 'index'])->name('staffs.index');
        Route::get('staffs/{teacher}', [StaffsController::class, 'show'])->name('staffs.show');
    });

    Route::middleware(['check_permission:staffs.update'])->group(function () {
        Route::get('staffs/{teacher}/edit', [StaffsController::class, 'edit'])->name('staffs.edit');
        Route::match(['PUT', 'PATCH'], 'staffs/{teacher}', [StaffsController::class, 'update'])->name('staffs.update');
        Route::post('staffs/{teacher}/photo', [StaffsController::class, 'updatePhoto'])->name('staffs.photo.update');
    });

    Route::middleware(['check_permission:staffs.assign_subjects'])->group(function () {
        Route::post('staffs/{teacher}/assign-class-teacher', [StaffsController::class, 'assignClassTeacher'])->name('staffs.assign-class-teacher');
    });

    // ──────────────────────────────────────────────
    // PARENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:guardians.view'])->group(function () {
        Route::get('parents/export-pdf', [ParentsController::class, 'exportPdf'])->name('parents.export-pdf');
        Route::resource('parents', ParentsController::class);
    });

    // ──────────────────────────────────────────────
    // CLASSES, GRADES, STREAMS, DEPARTMENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:classes.view'])->group(function () {
        // PDF Exports
        Route::get('departments/export-pdf', [AcademicManagementController::class, 'exportDepartmentsPdf'])->name('departments.export-pdf');
        Route::get('classes/export-pdf', [AcademicManagementController::class, 'exportClassesPdf'])->name('classes.export-pdf');
        Route::get('grades/export-pdf', [AcademicManagementController::class, 'exportGradesPdf'])->name('grades.export-pdf');
        Route::get('streams/export-pdf', [AcademicManagementController::class, 'exportStreamsPdf'])->name('streams.export-pdf');

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
        Route::post('departments/{id}/teachers', [AcademicManagementController::class, 'addStaffToDepartment'])->name('departments.teachers.store');
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
        Route::patch('classes/{id}/assign-teacher', [AcademicManagementController::class, 'assignTeacher'])->name('classes.assign-teacher');

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
        Route::patch('departments/{id}/assign-head', [AcademicManagementController::class, 'assignDepartmentHead'])->name('departments.assign-head');
    });

    Route::middleware(['check_permission:classes.delete'])->group(function () {
        Route::delete('classes/{id}', [AcademicManagementController::class, 'destroyClass'])->name('classes.destroy');
        Route::delete('classes/allocations/{id}', [AcademicManagementController::class, 'destroyAllocation'])->name('classes.allocations.destroy');
        Route::delete('grades/{id}', [AcademicManagementController::class, 'destroyGrade'])->name('grades.destroy');
        Route::delete('streams/{id}', [AcademicManagementController::class, 'destroyStream'])->name('streams.destroy');
        Route::delete('departments/{id}', [AcademicManagementController::class, 'destroyDepartment'])->name('departments.destroy');
        Route::delete('departments/{id}/subjects/{subjectId}', [AcademicManagementController::class, 'destroyDepartmentSubject'])->name('departments.subjects.destroy');
        Route::delete('departments/{id}/teachers/{teacherId}', [AcademicManagementController::class, 'removeStaffFromDepartment'])->name('departments.teachers.destroy');
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
        Route::get('curriculum/subjects/export-pdf', [CurriculumManagementController::class, 'exportPdf'])->name('curriculum.subjects.export-pdf');
        Route::get('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'showSubject'])->name('curriculum.subjects.show');
        Route::get('curriculum/strands', [CurriculumManagementController::class, 'strands'])->name('curriculum.strands');
        Route::get('curriculum/strands/{id}', [CurriculumManagementController::class, 'showStrand'])->name('curriculum.strands.show');
        
        // Syllabus Viewing
        Route::get('curriculum/syllabus', [SyllabusController::class, 'index'])->name('curriculum.syllabus.index');
        Route::get('curriculum/syllabus/subjects/template', [SyllabusController::class, 'downloadSubjectTemplate'])->name('curriculum.syllabus.subjects.template');
        Route::get('curriculum/syllabus/topics/{strand}', [SyllabusController::class, 'showTopic'])->name('curriculum.syllabus.topics.show');
        Route::get('curriculum/syllabus/{subject}/{grade}', [SyllabusController::class, 'show'])->name('curriculum.syllabus.show');
        Route::resource('curriculum/competencies', \App\Http\Controllers\Curriculum\CompetencyController::class)->names('curriculum.competencies');
        Route::get('curriculum/competencies/template', [\App\Http\Controllers\Curriculum\CompetencyController::class, 'downloadTemplate'])->name('curriculum.competencies.template');
        Route::post('curriculum/competencies/import', [\App\Http\Controllers\Curriculum\CompetencyController::class, 'importIndicators'])->name('curriculum.competencies.import');
        Route::post('curriculum/competencies/indicators', [\App\Http\Controllers\Curriculum\CompetencyController::class, 'storeIndicator'])->name('curriculum.competencies.storeIndicator');
        Route::delete('curriculum/competencies/indicators/{indicator}', [\App\Http\Controllers\Curriculum\CompetencyController::class, 'destroyIndicator'])->name('curriculum.competencies.destroyIndicator');
        Route::get('curriculum/progress/{student}/{subject}', [SyllabusController::class, 'getStudentProgress'])->name('curriculum.progress.show');
    });

    Route::middleware(['check_permission:curriculum.create'])->group(function () {
        Route::get('curriculum/learning-areas/create', [CurriculumManagementController::class, 'createLearningArea'])->name('curriculum.learning-areas.create');
        Route::post('curriculum/learning-areas', [CurriculumManagementController::class, 'storeLearningArea'])->name('curriculum.learning-areas.store');
        Route::get('curriculum/subjects/create', [CurriculumManagementController::class, 'createSubject'])->name('curriculum.subjects.create');
        Route::post('curriculum/subjects', [CurriculumManagementController::class, 'storeSubject'])->name('curriculum.subjects.store');
        Route::get('curriculum/strands/create', [CurriculumManagementController::class, 'createStrand'])->name('curriculum.strands.create');
        Route::post('curriculum/strands', [CurriculumManagementController::class, 'storeStrand'])->name('curriculum.strands.store');

        // Syllabus Creation
        Route::post('curriculum/syllabus/outcomes', [SyllabusController::class, 'storeOutcome'])->name('curriculum.syllabus.outcomes.store');
        Route::post('curriculum/syllabus/indicators', [SyllabusController::class, 'storeIndicator'])->name('curriculum.syllabus.indicators.store');
        Route::post('curriculum/syllabus/subjects', [SyllabusController::class, 'storeSubject'])->name('curriculum.syllabus.subjects.store');
        Route::post('curriculum/syllabus/subjects/bulk', [SyllabusController::class, 'bulkStoreSubjects'])->name('curriculum.syllabus.subjects.bulk');
        Route::post('curriculum/syllabus/topics', [SyllabusController::class, 'storeStrand'])->name('curriculum.syllabus.topics.store');
        Route::post('curriculum/syllabus/sub-topics', [SyllabusController::class, 'storeSubStrand'])->name('curriculum.syllabus.sub-topics.store');
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

        // Syllabus Updates
        Route::put('curriculum/syllabus/outcomes/{outcome}', [SyllabusController::class, 'updateOutcome'])->name('curriculum.syllabus.outcomes.update');
        Route::put('curriculum/syllabus/subjects/{subject}', [SyllabusController::class, 'updateSubject'])->name('curriculum.syllabus.subjects.update');
        Route::put('curriculum/syllabus/topics/{strand}', [SyllabusController::class, 'updateStrand'])->name('curriculum.syllabus.topics.update');
        Route::put('curriculum/syllabus/sub-topics/{subStrand}', [SyllabusController::class, 'updateSubStrand'])->name('curriculum.syllabus.sub-topics.update');
        Route::post('curriculum/syllabus/achievements', [SyllabusController::class, 'markAchieved'])->name('curriculum.syllabus.achievements.mark');
    });

    Route::middleware(['check_permission:curriculum.delete'])->group(function () {
        Route::delete('curriculum/learning-areas/{learningArea}', [CurriculumManagementController::class, 'destroyLearningArea'])->name('curriculum.learning-areas.destroy');
        Route::delete('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'destroySubject'])->name('curriculum.subjects.destroy');
        Route::delete('curriculum/strands/{id}', [CurriculumManagementController::class, 'destroyStrand'])->name('curriculum.strands.destroy');

        // Syllabus Deletion
        Route::delete('curriculum/syllabus/outcomes/{outcome}', [SyllabusController::class, 'destroyOutcome'])->name('curriculum.syllabus.outcomes.destroy');
        Route::delete('curriculum/syllabus/subjects/{subject}', [SyllabusController::class, 'destroySubject'])->name('curriculum.syllabus.subjects.destroy');
        Route::delete('curriculum/syllabus/topics/{strand}', [SyllabusController::class, 'destroyStrand'])->name('curriculum.syllabus.topics.destroy');
        Route::delete('curriculum/syllabus/sub-topics/{subStrand}', [SyllabusController::class, 'destroySubStrand'])->name('curriculum.syllabus.sub-topics.destroy');
    });

    // Academic Planner (Schemes & Lesson Plans) - Accessible with curriculum.view
    Route::middleware(['check_permission:curriculum.view'])->group(function () {
        Route::get('curriculum/planner/schemes', [AcademicPlannerController::class, 'schemes'])->name('curriculum.planner.schemes');
        Route::get('curriculum/planner/schemes/{scheme}', [AcademicPlannerController::class, 'showScheme'])->name('curriculum.planner.schemes.show');
        Route::post('curriculum/planner/schemes', [AcademicPlannerController::class, 'storeScheme'])->name('curriculum.planner.schemes.store');
        Route::put('curriculum/planner/schemes/{scheme}', [AcademicPlannerController::class, 'updateScheme'])->name('curriculum.planner.schemes.update');
        Route::delete('curriculum/planner/schemes/{scheme}', [AcademicPlannerController::class, 'destroyScheme'])->name('curriculum.planner.schemes.destroy');
        Route::post('curriculum/planner/schemes/{scheme}/submit', [AcademicPlannerController::class, 'submitScheme'])->name('curriculum.planner.schemes.submit');
        Route::post('curriculum/planner/schemes/{scheme}/approve', [AcademicPlannerController::class, 'approveScheme'])->name('curriculum.planner.schemes.approve');
        Route::post('curriculum/planner/schemes/{scheme}/reject', [AcademicPlannerController::class, 'rejectScheme'])->name('curriculum.planner.schemes.reject');
        Route::post('curriculum/planner/schemes/{scheme}/entries', [AcademicPlannerController::class, 'storeSchemeEntry'])->name('curriculum.planner.schemes.entries.store');
        Route::put('curriculum/planner/schemes/{scheme}/entries/{entry}', [AcademicPlannerController::class, 'updateSchemeEntry'])->name('curriculum.planner.schemes.entries.update');
        Route::get('curriculum/planner/schemes/{scheme}/entries/{entry}', [AcademicPlannerController::class, 'showSchemeEntry'])->name('curriculum.planner.schemes.entries.show');
        Route::delete('curriculum/planner/schemes/{scheme}/entries/{entry}', [AcademicPlannerController::class, 'destroySchemeEntry'])->name('curriculum.planner.schemes.entries.destroy');
        Route::get('curriculum/planner/schemes/template/download', [AcademicPlannerController::class, 'downloadSchemeTemplate'])->name('curriculum.planner.schemes.template.download');
        Route::post('curriculum/planner/schemes/{scheme}/entries/{entry}/generate-lessons', [AcademicPlannerController::class, 'generateLessonsFromScheme']);
        Route::post('curriculum/planner/schemes/{scheme}/bulk-generate-lessons', [AcademicPlannerController::class, 'bulkGenerateLessons'])->name('curriculum.planner.schemes.bulk-generate-lessons');
        Route::post('curriculum/planner/schemes/{scheme}/import', [AcademicPlannerController::class, 'importSchemeEntries'])->name('curriculum.planner.schemes.import');
        Route::post('curriculum/planner/schemes/{scheme}/bulk-delete-entries', [AcademicPlannerController::class, 'bulkDeleteSchemeEntries'])->name('curriculum.planner.schemes.bulk-delete-entries');
        Route::get('curriculum/planner/schemes/{scheme}/download', [AcademicPlannerController::class, 'downloadSchemePdf'])->name('curriculum.planner.schemes.download');
        Route::get('curriculum/planner/schemes/{scheme}/entries/{entry}/download', [AcademicPlannerController::class, 'downloadSchemeEntryPdf'])->name('curriculum.planner.schemes.entries.download');

        Route::get('curriculum/planner/lesson-plans', [AcademicPlannerController::class, 'lessonPlansIndex'])->name('curriculum.planner.lesson-plans');
        Route::get('curriculum/planner/lesson-plans/grade/{gradeLevel}', [AcademicPlannerController::class, 'lessonPlansGrade'])->name('curriculum.planner.lesson-plans.grade');
        Route::get('curriculum/planner/lesson-plans/class/{schoolClass}/subjects', [AcademicPlannerController::class, 'lessonPlansClassSubjects'])->name('curriculum.planner.lesson-plans.class.subjects');
        Route::get('curriculum/planner/lesson-plans/class/{schoolClass}/subject/{subject}', [AcademicPlannerController::class, 'lessonPlansClassSubject'])->name('curriculum.planner.lesson-plans.class.subject');
        Route::get('curriculum/planner/lesson-plans/class/{schoolClass}/subject/{subject}/download-all', [AcademicPlannerController::class, 'downloadAllLessonPlansPdf'])->name('curriculum.planner.lesson-plans.download-all');
        Route::post('curriculum/planner/lesson-plans', [AcademicPlannerController::class, 'storeLessonPlan'])->name('curriculum.planner.lesson-plans.store');
        Route::post('curriculum/planner/lesson-plans/bulk-upload', [AcademicPlannerController::class, 'importLessonPlans'])->name('curriculum.planner.lesson-plans.bulk-upload');
        Route::post('curriculum/planner/lesson-plans/bulk-delete', [AcademicPlannerController::class, 'bulkDeleteLessonPlans'])->name('curriculum.planner.lesson-plans.bulk-delete');
        Route::get('curriculum/planner/lesson-plans/template', [AcademicPlannerController::class, 'downloadLessonPlanTemplate'])->name('curriculum.planner.lesson-plans.template');
        Route::put('curriculum/planner/lesson-plans/{plan}', [AcademicPlannerController::class, 'updateLessonPlan'])->name('curriculum.planner.lesson-plans.update');
        Route::delete('curriculum/planner/lesson-plans/{plan}', [AcademicPlannerController::class, 'destroyLessonPlan'])->name('curriculum.planner.lesson-plans.destroy');
        Route::get('curriculum/planner/lesson-plans/{plan}', [AcademicPlannerController::class, 'showLessonPlan'])->name('curriculum.planner.lesson-plans.show');
        Route::get('curriculum/planner/lesson-plans/{plan}/download', [AcademicPlannerController::class, 'downloadLessonPlanPdf'])->name('curriculum.planner.lesson-plans.download');
        Route::post('curriculum/planner/lesson-plans/{plan}/submit', [AcademicPlannerController::class, 'submitLessonPlan'])->name('curriculum.planner.lesson-plans.submit');
        Route::post('curriculum/planner/lesson-plans/{plan}/approve', [AcademicPlannerController::class, 'approveLessonPlan'])->name('curriculum.planner.lesson-plans.approve');
        Route::post('curriculum/planner/lesson-plans/{plan}/reject', [AcademicPlannerController::class, 'rejectLessonPlan'])->name('curriculum.planner.lesson-plans.reject');
    });

    // Assignments & Learning Resources
    Route::middleware(['check_permission:curriculum.view'])->group(function () {
        Route::get('curriculum/assignments', [AssignmentController::class, 'index'])->name('curriculum.assignments.index');
        Route::get('curriculum/assignments/vault', [AssignmentController::class, 'vault'])->name('curriculum.assignments.vault');
        Route::get('curriculum/assignments/attachments/{attachment}/download', [AssignmentController::class, 'download'])->name('curriculum.assignments.attachments.download');
        Route::get('curriculum/resources', [LearningResourceController::class, 'index'])->name('curriculum.resources.index');
    });

    Route::middleware(['check_permission:curriculum.create'])->group(function () {
        Route::get('curriculum/assignments/create', [AssignmentController::class, 'create'])->name('curriculum.assignments.create');
        Route::post('curriculum/assignments', [AssignmentController::class, 'store'])->name('curriculum.assignments.store');
        Route::post('curriculum/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('curriculum.assignments.submit');
        
        Route::post('curriculum/resources', [LearningResourceController::class, 'store'])->name('curriculum.resources.store');
        Route::post('curriculum/resources/folders', [LearningResourceController::class, 'storeFolder'])->name('curriculum.resources.folders.store');
        Route::get('curriculum/resources/create', [LearningResourceController::class, 'create'])->name('curriculum.resources.create');
    });

    Route::middleware(['check_permission:curriculum.update'])->group(function () {
        Route::get('curriculum/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('curriculum.assignments.edit');
        Route::put('curriculum/assignments/{assignment}', [AssignmentController::class, 'update'])->name('curriculum.assignments.update');
        Route::post('curriculum/assignments/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('curriculum.assignments.submissions.grade');
        
        Route::put('curriculum/resources/{resource}', [LearningResourceController::class, 'update'])->name('curriculum.resources.update');
        Route::get('curriculum/resources/{resource}/edit', [LearningResourceController::class, 'edit'])->name('curriculum.resources.edit');
    });

    Route::middleware(['check_permission:curriculum.view'])->group(function () {
        Route::get('curriculum/assignments/{assignment}', [AssignmentController::class, 'show'])->name('curriculum.assignments.show');
        Route::get('curriculum/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('curriculum.assignments.submissions');
        Route::get('curriculum/assignments/{assignment}/submissions/{submission}/review', [AssignmentController::class, 'reviewSubmission'])->name('curriculum.assignments.submissions.review');
        Route::get('curriculum/assignments/submissions/{submission}/download-compiled', [AssignmentController::class, 'downloadCompiledPdf'])->name('curriculum.assignments.submissions.download-compiled');
        Route::get('curriculum/resources/{resource}', [LearningResourceController::class, 'show'])->name('curriculum.resources.show');
    });

    Route::middleware(['check_permission:curriculum.delete'])->group(function () {
        Route::delete('curriculum/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('curriculum.assignments.destroy');
        Route::delete('curriculum/assignments/attachments/{attachment}', [AssignmentController::class, 'destroyAttachment'])->name('curriculum.assignments.attachments.destroy');
        
        Route::delete('curriculum/resources/{resource}', [LearningResourceController::class, 'destroy'])->name('curriculum.resources.destroy');
        Route::post('curriculum/resources/bulk-delete', [LearningResourceController::class, 'bulkDelete'])->name('curriculum.resources.bulk-delete');
    });

    // ──────────────────────────────────────────────
    // ASSESSMENTS & GRADING (CBC MODERN)
    // ──────────────────────────────────────────────
    Route::group(['prefix' => 'assessments', 'as' => 'assessments.'], function () {
        // Assessment Management
        Route::get('/', [AssessmentController::class, 'index'])->name('index');
        Route::get('/setup', [AssessmentWizardController::class, 'index'])->name('setup');
        Route::post('/setup', [AssessmentWizardController::class, 'store'])->name('setup.store');
        
        // Metadata Lookups
        Route::get('/strands', [AssessmentWizardController::class, 'getStrands'])->name('strands');
        Route::get('/sub-strands', [AssessmentWizardController::class, 'getSubStrands'])->name('sub-strands');
        Route::get('/indicators', [AssessmentWizardController::class, 'getIndicators'])->name('indicators');
        
        // Feature Sections
        Route::get('/results', [AssessmentController::class, 'results'])->name('results');
        Route::get('/results/pdf', [AssessmentController::class, 'exportResultsPdf'])->name('results.pdf');
        Route::get('/results/template', [AssessmentController::class, 'downloadResultsTemplate'])->name('results.template');
        Route::get('/analytics', [AssessmentController::class, 'analytics'])->name('analytics');
        Route::get('/report-cards', [AssessmentController::class, 'reportCards'])->name('report-cards');
        Route::get('/report-cards/{student}', [AssessmentController::class, 'showReport'])->name('report-cards.show');
        Route::get('/report-cards/{student}/pdf', [AssessmentController::class, 'exportPdf'])->name('report-cards.pdf');
        Route::get('/rubrics', [AssessmentController::class, 'rubrics'])->name('rubrics');
        Route::get('/rubrics/create', [AssessmentController::class, 'rubricCreate'])->name('rubrics.create');
        Route::post('/rubrics', [AssessmentController::class, 'rubricStore'])->name('rubrics.store');
        Route::get('/rubrics/{rubric}/edit', [AssessmentController::class, 'rubricEdit'])->name('rubrics.edit');
        Route::put('/rubrics/{rubric}', [AssessmentController::class, 'rubricUpdate'])->name('rubrics.update');
        Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
        Route::get('/portfolio/{studentId}', [PortfolioController::class, 'show'])->name('portfolio.show');
        
        // Grading Terminal (High-Fidelity)
        Route::get('/latest-active', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'latestActive'])->name('latest-active');
        Route::post('/grading/quick-save', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'quickSave'])->name('grading.quick-save');
        Route::get('/grading/{assessment}', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'index'])->name('grading');
        Route::get('/grading/{assessment}/export', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'export'])->name('grading.export');
        Route::get('/grading/{assessment}/template', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'downloadTemplate'])->name('grading.template');
        Route::post('/grading/{assessment}/import', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'importMarks'])->name('grading.import');
        Route::post('/grading/{assessment}', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'store'])->name('grading.store');
        
        // Resource Actions (CRUD)
        Route::middleware(['check_permission:assessments.create'])->group(function () {
            Route::get('/create', [AssessmentController::class, 'create'])->name('create');
            Route::post('/', [AssessmentController::class, 'store'])->name('store');
        });

        Route::middleware(['check_permission:assessments.update'])->group(function () {
            Route::get('/{assessment}/edit', [AssessmentController::class, 'edit'])->name('edit');
            Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('update');
        });

        Route::middleware(['check_permission:assessments.delete'])->group(function () {
            Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('destroy');
        });

        // The "Show" route (Defaults to Grading Terminal for CBC logic)
        Route::get('/{assessment}', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'index'])->name('show');
    });

    // ──────────────────────────────────────────────
    // ATTENDANCE
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:attendance.view,attendance.view_own'])->group(function () {
        Route::inertia('attendance', 'attendance/Index')->name('attendance.overview');
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
    // REPORTS & ANALYTICS (CBC Ready)
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:reports.view,reports.view_own'])->group(function () {
        Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/report-cards', [\App\Http\Controllers\ReportController::class, 'progressReport'])->name('reports.report-cards');
        Route::get('reports/subject-performance', [\App\Http\Controllers\ReportController::class, 'subjectReport'])->name('reports.subject-performance');
        Route::get('reports/class-summary', [\App\Http\Controllers\ReportController::class, 'classSummary'])->name('reports.class-summary');
        
        Route::get('api/reports/progress/{student}', [\App\Http\Controllers\ReportController::class, 'getStudentProgress'])->name('api.reports.progress');
        Route::get('api/reports/subject-analysis', [\App\Http\Controllers\ReportController::class, 'getSubjectAnalysis'])->name('api.reports.subject-analysis');
        Route::get('api/reports/class-analysis', [\App\Http\Controllers\ReportController::class, 'getClassAnalysis'])->name('api.reports.class-analysis');
        
        // Generic placeholders transformed into actual endpoints
        Route::get('reports/attendance', [\App\Http\Controllers\ReportController::class, 'attendanceReport'])->name('reports.attendance');
        Route::get('reports/assessments', [\App\Http\Controllers\ReportController::class, 'assessmentReport'])->name('reports.assessments');
        Route::get('reports/competency-mastery', [\App\Http\Controllers\ReportController::class, 'competencyReport'])->name('reports.competency-mastery');
        Route::get('reports/learner-profile', [\App\Http\Controllers\ReportController::class, 'learnerReport'])->name('reports.learner-profile');
        Route::get('reports/teacher-metrics', [\App\Http\Controllers\ReportController::class, 'teacherReport'])->name('reports.teacher-metrics');
        Route::get('reports/finance', [\App\Http\Controllers\ReportController::class, 'financeReport'])->name('reports.finance');
        Route::get('reports/enrollment', [\App\Http\Controllers\ReportController::class, 'enrollmentReport'])->name('reports.enrollment');
        
        // Data Pipelines & Export Actions
        Route::get('api/reports/attendance-analysis', [\App\Http\Controllers\ReportController::class, 'getAttendanceAnalysis'])->name('api.reports.attendance-analysis');
        Route::get('reports/attendance/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportAttendancePdf'])->name('reports.attendance.export-pdf');
        
        Route::get('api/reports/assessment-analysis', [\App\Http\Controllers\ReportController::class, 'getAssessmentAnalysis'])->name('api.reports.assessment-analysis');
        Route::get('reports/assessments/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportAssessmentPdf'])->name('reports.assessments.export-pdf');
        
        Route::get('api/reports/competency-analysis', [\App\Http\Controllers\ReportController::class, 'getCompetencyAnalysis'])->name('api.reports.competency-analysis');
        Route::get('reports/competency-mastery/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportCompetencyPdf'])->name('reports.competency-mastery.export-pdf');
        
        Route::get('api/reports/learner-analysis', [\App\Http\Controllers\ReportController::class, 'getLearnerAnalysis'])->name('api.reports.learner-analysis');
        Route::get('reports/learner-profile/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportLearnerPdf'])->name('reports.learner-profile.export-pdf');
        
        Route::get('api/reports/teacher-analysis', [\App\Http\Controllers\ReportController::class, 'getTeacherAnalysis'])->name('api.reports.teacher-analysis');
        Route::get('reports/teacher-metrics/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportTeacherPdf'])->name('reports.teacher-metrics.export-pdf');
        
        Route::get('api/reports/finance-analysis', [\App\Http\Controllers\ReportController::class, 'getFinanceAnalysis'])->name('api.reports.finance-analysis');
        Route::get('reports/finance/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportFinancePdf'])->name('reports.finance.export-pdf');
        
        Route::get('api/reports/enrollment-analysis', [\App\Http\Controllers\ReportController::class, 'getEnrollmentAnalysis'])->name('api.reports.enrollment-analysis');
        Route::get('reports/enrollment/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportEnrollmentPdf'])->name('reports.enrollment.export-pdf');

        
        // Legacy/Archive reports
        Route::inertia('reports/academic', 'reports/Academic')->name('reports.academic');
        Route::inertia('reports/financial', 'reports/Financial')->name('reports.financial');
        Route::inertia('reports/analytics', 'reports/Analytics')->name('reports.analytics');
        
        // Audit Logs (Dedicated Page)
        Route::get('audit-logs', [\App\Http\Controllers\Audit\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{activity}', [\App\Http\Controllers\Audit\AuditLogController::class, 'show'])->name('audit-logs.show');
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
        Route::post('settings/school/logo', [SettingsController::class, 'updateSchoolLogo'])->name('settings.school.logo');
        Route::put('settings/school', [SettingsController::class, 'updateSchoolProfile'])->name('settings.school.update');
        Route::put('settings/school/security', [SettingsController::class, 'updateSecuritySettings'])->name('settings.school.security');

        Route::get('settings/academic-years', [SettingsController::class, 'academicSettings'])->name('settings.academic-year');
        Route::post('settings/academic-years', [SettingsController::class, 'storeAcademicYear'])->name('settings.academic-year.store');
        Route::put('settings/academic-years/{year}', [SettingsController::class, 'updateAcademicYear'])->name('settings.academic-year.update');
        Route::match(['post', 'patch'], 'settings/academic-years/{year}/current', [SettingsController::class, 'setCurrentAcademicYear'])->name('settings.academic-year.current');
        Route::put('settings/academic-terms/{term}', [SettingsController::class, 'updateAcademicTerm'])->name('settings.academic-term.update');

        Route::post('settings/assessment-types', [SettingsController::class, 'storeAssessmentType'])->name('settings.assessment-types.store');
        Route::put('settings/assessment-types/{id}', [SettingsController::class, 'updateAssessmentType'])->name('settings.assessment-types.update');

        Route::get('settings/users', [SettingsController::class, 'users'])->name('settings.users');
        Route::get('settings/system', [SettingsController::class, 'system'])->name('settings.system');
    });
});


// Custom Multi-Step Registration
Route::get('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'showRegistrationForm'])->name('register');
Route::post('/register/send-otp', [\App\Http\Controllers\Auth\RegistrationController::class, 'sendOtp'])->name('register.send-otp');
Route::post('/register/verify-otp', [\App\Http\Controllers\Auth\RegistrationController::class, 'verifyOtp'])->name('register.verify-otp');
Route::post('/register/complete', [\App\Http\Controllers\Auth\RegistrationController::class, 'register'])->name('register.complete');

require __DIR__.'/settings.php';

Route::get('/test-mail', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('This is a test email to verify SMTP configuration.', function ($message) {
            $message->to('billyochiengokello@gmail.com')
                ->subject('SMTP Verification Test');
        });
        return 'Email sent successfully via ' . config('mail.mailers.smtp.host');
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});
