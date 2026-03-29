<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\School\SchoolController;
use App\Http\Controllers\Api\V1\Student\StudentController;
use App\Http\Controllers\Api\V1\Student\GuardianController;
use App\Http\Controllers\Api\V1\Staff\StaffController;
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

    // Tenant-scoped APIs (students, staffs, guardians) are served from routes/tenant_api.php
});
