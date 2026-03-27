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
use App\Http\Controllers\StaffsController;
use App\Http\Controllers\ParentsController;
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
});

require __DIR__.'/settings.php';
