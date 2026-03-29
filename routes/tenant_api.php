<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Api\V1\Student\StudentController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Student\GuardianController;
use App\Http\Controllers\Api\V1\Staff\StaffController;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| API routes that should run in tenant context (database switched to tenant).
| These routes are loaded by the TenancyServiceProvider along with
| other tenant routes.
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/v1')->group(function () {
    // Public tenant API endpoints
    Route::post('auth/login', [AuthController::class, 'login']);

    // Protected tenant API endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        // Authentication
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });

        // Students Management
        Route::prefix('students')->group(function () {
            Route::get('/', [StudentController::class, 'index']);
            Route::post('/', [StudentController::class, 'store'])->middleware('permission:students.create');
            Route::get('{student}', [StudentController::class, 'show']);
            Route::put('{student}', [StudentController::class, 'update'])->middleware('permission:students.update');
            Route::delete('{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete');
            Route::patch('{student}/status', [StudentController::class, 'updateStatus'])->middleware('permission:students.update');
            Route::get('{student}/attendance', [StudentController::class, 'attendanceSummary']);
            Route::get('{student}/academic-performance', [StudentController::class, 'academicPerformance']);
        });

        // Staffs Management
        Route::prefix('staffs')->group(function () {
            Route::get('/', [StaffController::class, 'index']);
            Route::post('/', [StaffController::class, 'store'])->middleware('permission:staffs.create');
            Route::get('{teacher}', [StaffController::class, 'show']);
            Route::put('{teacher}', [StaffController::class, 'update'])->middleware('permission:staffs.update');
            Route::delete('{teacher}', [StaffController::class, 'destroy'])->middleware('permission:staffs.delete');
            Route::get('{teacher}/subjects', [StaffController::class, 'subjects']);
            Route::get('{teacher}/timetable', [StaffController::class, 'timetable']);
        });

        // Guardians Management
        Route::prefix('guardians')->group(function () {
            Route::get('/', [GuardianController::class, 'index']);
            Route::post('/', [GuardianController::class, 'store'])->middleware('permission:guardians.create');
            Route::get('{guardian}', [GuardianController::class, 'show']);
            Route::put('{guardian}', [GuardianController::class, 'update'])->middleware('permission:guardians.update');
            Route::delete('{guardian}', [GuardianController::class, 'destroy'])->middleware('permission:guardians.delete');
            Route::get('{guardian}/students', [GuardianController::class, 'students']);
        });
    });
});
