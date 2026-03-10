<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\School\SchoolController;
use App\Http\Controllers\Api\V1\Student\StudentController;
use App\Http\Controllers\Api\V1\Student\GuardianController;
use App\Http\Controllers\Api\V1\Teacher\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Here are the API routes for the CBC Integrated Learning Management System.
| These routes are loaded by the RouteServiceProvider and assigned the "api"
| middleware group with the "api/v1" prefix.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Schools Management
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index']);
        Route::post('/', [SchoolController::class, 'store'])->middleware('permission:schools.create');
        Route::get('{school}', [SchoolController::class, 'show']);
        Route::put('{school}', [SchoolController::class, 'update'])->middleware('permission:schools.update');
        Route::delete('{school}', [SchoolController::class, 'destroy'])->middleware('permission:schools.delete');
        Route::get('{school}/statistics', [SchoolController::class, 'statistics']);
        Route::get('{school}/settings', [SchoolController::class, 'settings']);
        Route::put('{school}/settings', [SchoolController::class, 'updateSetting'])->middleware('permission:schools.settings');
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

    // Teachers Management
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store'])->middleware('permission:teachers.create');
        Route::get('{teacher}', [TeacherController::class, 'show']);
        Route::put('{teacher}', [TeacherController::class, 'update'])->middleware('permission:teachers.update');
        Route::delete('{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:teachers.delete');
        Route::get('{teacher}/subjects', [TeacherController::class, 'subjects']);
        Route::get('{teacher}/timetable', [TeacherController::class, 'timetable']);
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
