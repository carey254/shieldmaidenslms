<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Student routes
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard']);
        Route::get('/courses', [StudentController::class, 'courses']);
        Route::get('/enrollments', [StudentController::class, 'enrollments']);
        Route::get('/progress', [StudentController::class, 'progress']);
        Route::get('/certificates', [StudentController::class, 'certificates']);
        Route::post('/enroll/{course}', [StudentController::class, 'enroll']);
    });

    // Instructor routes
    Route::prefix('instructor')->middleware('role:instructor')->group(function () {
        Route::get('/dashboard', [InstructorController::class, 'dashboard']);
        Route::get('/courses', [InstructorController::class, 'courses']);
        Route::post('/courses', [InstructorController::class, 'storeCourse']);
        Route::put('/courses/{course}', [InstructorController::class, 'updateCourse']);
        Route::delete('/courses/{course}', [InstructorController::class, 'deleteCourse']);
        Route::get('/students', [InstructorController::class, 'students']);
        Route::get('/analytics', [InstructorController::class, 'analytics']);
        Route::get('/payouts', [InstructorController::class, 'payouts']);
    });

    // Admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::get('/courses', [AdminController::class, 'courses']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::get('/reports', [AdminController::class, 'reports']);
        Route::get('/settings', [AdminController::class, 'settings']);
        Route::post('/settings', [AdminController::class, 'updateSettings']);
    });

    // Common routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/notifications', [AuthController::class, 'notifications']);
});
