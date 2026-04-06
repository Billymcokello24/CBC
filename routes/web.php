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

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — accessible to ALL authenticated users (role-specific data returned by controller)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
        Route::post('assignments/{assignment}/submit', [ParentPortalController::class, 'submitAssignment'])->name('assignments.submit');
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
        Route::get('staffs', [StaffsController::class, 'index'])->name('staffs.index');
        Route::get('staffs/{teacher}', [StaffsController::class, 'show'])->name('staffs.show');
    });

    Route::middleware(['check_permission:staffs.update'])->group(function () {
        Route::get('staffs/{teacher}/edit', [StaffsController::class, 'edit'])->name('staffs.edit');
        Route::match(['PUT', 'PATCH'], 'staffs/{teacher}', [StaffsController::class, 'update'])->name('staffs.update');
        Route::post('staffs/{teacher}/photo', [StaffsController::class, 'updatePhoto'])->name('staffs.photo.update');
        Route::delete('staffs/{teacher}', [StaffsController::class, 'destroy'])->name('staffs.destroy');
    });

    Route::middleware(['check_permission:staffs.assign_subjects'])->group(function () {
        Route::post('staffs/{teacher}/assign-class-teacher', [StaffsController::class, 'assignClassTeacher'])->name('staffs.assign-class-teacher');
    });

    // ──────────────────────────────────────────────
    // PARENTS
    // ──────────────────────────────────────────────
    Route::middleware(['check_permission:guardians.view'])->group(function () {
        Route::resource('parents', ParentsController::class);
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
        Route::get('curriculum/subjects/{subject}', [CurriculumManagementController::class, 'showSubject'])->name('curriculum.subjects.show');
        Route::get('curriculum/strands', [CurriculumManagementController::class, 'strands'])->name('curriculum.strands');
        Route::get('curriculum/strands/{id}', [CurriculumManagementController::class, 'showStrand'])->name('curriculum.strands.show');
        
        // Syllabus Viewing
        Route::get('curriculum/syllabus', [SyllabusController::class, 'index'])->name('curriculum.syllabus.index');
        Route::get('curriculum/syllabus/topics/{strand}', [SyllabusController::class, 'showTopic'])->name('curriculum.syllabus.topics.show');
        Route::get('curriculum/syllabus/{subject}/{grade}', [SyllabusController::class, 'show'])->name('curriculum.syllabus.show');
        Route::resource('curriculum/competencies', \App\Http\Controllers\Curriculum\CompetencyController::class)->names('curriculum.competencies');
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
        Route::post('curriculum/planner/lesson-plans', [AcademicPlannerController::class, 'storeLessonPlan'])->name('curriculum.planner.lesson-plans.store');
        Route::put('curriculum/planner/lesson-plans/{plan}', [AcademicPlannerController::class, 'updateLessonPlan'])->name('curriculum.planner.lesson-plans.update');
        Route::delete('curriculum/planner/lesson-plans/{plan}', [AcademicPlannerController::class, 'destroyLessonPlan'])->name('curriculum.planner.lesson-plans.destroy');
        Route::post('curriculum/planner/lesson-plans/{plan}/submit', [AcademicPlannerController::class, 'submitLessonPlan'])->name('curriculum.planner.lesson-plans.submit');
        Route::post('curriculum/planner/lesson-plans/{plan}/approve', [AcademicPlannerController::class, 'approveLessonPlan'])->name('curriculum.planner.lesson-plans.approve');
        Route::post('curriculum/planner/lesson-plans/{plan}/reject', [AcademicPlannerController::class, 'rejectLessonPlan'])->name('curriculum.planner.lesson-plans.reject');
    });

    // Assignments & Learning Resources
    Route::middleware(['check_permission:curriculum.view'])->group(function () {
        Route::get('curriculum/assignments', [AssignmentController::class, 'index'])->name('curriculum.assignments.index');
        Route::get('curriculum/assignments/{assignment}', [AssignmentController::class, 'show'])->name('curriculum.assignments.show');
        Route::get('curriculum/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('curriculum.assignments.submissions');
        Route::get('curriculum/assignments/attachments/{attachment}/download', [AssignmentController::class, 'download'])->name('curriculum.assignments.attachments.download');
        
        Route::get('curriculum/resources', [LearningResourceController::class, 'index'])->name('curriculum.resources.index');
    });

    Route::middleware(['check_permission:curriculum.create'])->group(function () {
        Route::get('curriculum/assignments/create', [AssignmentController::class, 'create'])->name('curriculum.assignments.create');
        Route::post('curriculum/assignments', [AssignmentController::class, 'store'])->name('curriculum.assignments.store');
        Route::post('curriculum/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('curriculum.assignments.submit');
        
        Route::post('curriculum/resources', [LearningResourceController::class, 'store'])->name('curriculum.resources.store');
    });

    Route::middleware(['check_permission:curriculum.update'])->group(function () {
        Route::get('curriculum/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('curriculum.assignments.edit');
        Route::put('curriculum/assignments/{assignment}', [AssignmentController::class, 'update'])->name('curriculum.assignments.update');
        Route::post('curriculum/assignments/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('curriculum.assignments.submissions.grade');
        
        Route::put('curriculum/resources/{resource}', [LearningResourceController::class, 'update'])->name('curriculum.resources.update');
    });

    Route::middleware(['check_permission:curriculum.delete'])->group(function () {
        Route::delete('curriculum/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('curriculum.assignments.destroy');
        Route::delete('curriculum/assignments/attachments/{attachment}', [AssignmentController::class, 'destroyAttachment'])->name('curriculum.assignments.attachments.destroy');
        
        Route::delete('curriculum/resources/{resource}', [LearningResourceController::class, 'destroy'])->name('curriculum.resources.destroy');
    });

    // ──────────────────────────────────────────────
    // ASSESSMENTS
    // ──────────────────────────────────────────────
    Route::group(['prefix' => 'assessments', 'as' => 'assessments.'], function () {
        Route::get('/setup', [AssessmentWizardController::class, 'index'])->name('setup');
        Route::post('/setup', [AssessmentWizardController::class, 'store'])->name('setup.store');
        Route::get('/strands', [AssessmentWizardController::class, 'getStrands'])->name('strands');
        Route::get('/sub-strands', [AssessmentWizardController::class, 'getSubStrands'])->name('sub-strands');
        Route::get('/indicators', [AssessmentWizardController::class, 'getIndicators'])->name('indicators');
        
        Route::get('/{assessment}/grading', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'index'])->name('grading');
        Route::post('/{assessment}/grading', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'store'])->name('grading.store');
        Route::post('/grading/quick-save', [\App\Http\Controllers\Assessment\AssessmentGradingController::class, 'quickSave'])->name('grading.quick-save');
    });

    Route::middleware(['check_permission:assessments.view,assessments.view_own'])->group(function () {
        Route::get('assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('assessments/results', [AssessmentController::class, 'results'])->name('assessments.results');
        Route::get('assessments/bulk-upload', [AssessmentController::class, 'bulkUploadView'])->name('assessments.bulk-upload');
        Route::post('assessments/bulk-upload', [AssessmentController::class, 'bulkUpload'])->name('assessments.bulk-upload.store');
        Route::get('/assessments/grading', [AssessmentController::class, 'gradingIndex'])->name('assessments.grading');
        Route::get('/assessments/analytics', [AssessmentController::class, 'analytics'])->name('assessments.analytics');
        Route::get('assessments/report-cards', [AssessmentController::class, 'reportCards'])->name('assessments.report-cards');
        Route::get('assessments/report-cards/{student}', [AssessmentController::class, 'showReport'])->name('assessments.report-cards.show');
        Route::get('assessments/rubrics', [AssessmentController::class, 'rubrics'])->name('assessments.rubrics');
        Route::get('assessments/results', [AssessmentController::class, 'results'])->name('assessments.results');
        Route::get('assessments/results/export', [AssessmentController::class, 'exportResults'])->name('assessments.results.export');

        // Portfolio Management
        Route::get('assessments/portfolio', [PortfolioController::class, 'index'])->name('assessments.portfolio');
        Route::get('assessments/portfolio/{studentId}', [PortfolioController::class, 'show'])->name('assessments.portfolio.show');
        Route::post('assessments/portfolio/{portfolioId}/item', [PortfolioController::class, 'storeItem'])->name('assessments.portfolio.store-item');
        Route::delete('assessments/portfolio/item/{itemId}', [PortfolioController::class, 'destroyItem'])->name('assessments.portfolio.destroy-item');
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
        Route::post('settings/school/logo', [SettingsController::class, 'updateSchoolLogo'])->name('settings.school.logo');
        Route::put('settings/school', [SettingsController::class, 'updateSchoolProfile'])->name('settings.school.update');
        Route::put('settings/school/security', [SettingsController::class, 'updateSecuritySettings'])->name('settings.school.security');

        Route::get('settings/academic-year', [SettingsController::class, 'academicSettings'])->name('settings.academic-year');
        Route::post('settings/academic-year', [SettingsController::class, 'storeAcademicYear'])->name('settings.academic-year.store');
        Route::put('settings/academic-year/{year}', [SettingsController::class, 'updateAcademicYear'])->name('settings.academic-year.update');
        Route::post('settings/academic-year/{year}/current', [SettingsController::class, 'setCurrentAcademicYear'])->name('settings.academic-year.current');

        Route::get('settings/users', [SettingsController::class, 'users'])->name('settings.users');
        Route::get('settings/system', [SettingsController::class, 'system'])->name('settings.system');
    });
});

require __DIR__.'/settings.php';
