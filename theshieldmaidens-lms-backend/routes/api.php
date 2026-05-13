<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\UserRequestController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes without rate limiting for now
Route::get('/test', function() {
    return response()->json([
        'message' => 'API connection successful',
        'timestamp' => now()->toISOString(),
        'server' => 'Laravel Backend',
        'version' => app()->version()
    ]);
});

Route::get('/ping', function() {
    return response()->json(['ping' => 'pong']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Secure authentication endpoints with enhanced security
Route::post('/secure-register', [AuthController::class, 'secureRegister']);
Route::post('/secure-login', [AuthController::class, 'secureLogin']);
Route::post('/secure-forgot-password', [AuthController::class, 'secureForgotPassword']);

// Public curriculum catalog (published courses)
Route::get('/catalog/courses', [CatalogController::class, 'courses']);
Route::get('/catalog/home-feed', [CatalogController::class, 'homeFeed']);

// Protected routes (still require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User request routes
    Route::get('/requests', [UserRequestController::class, 'index']);
    Route::post('/requests', [UserRequestController::class, 'store']);
    
    // Instructor routes (require instructor role)
    Route::middleware('instructor')->prefix('instructor')->group(function () {
        Route::get('/dashboard/stats', [InstructorController::class, 'getDashboardStats']);
        Route::get('/dashboard/courses', [InstructorController::class, 'getRecentCourses']);
        Route::get('/dashboard/activities', [InstructorController::class, 'getRecentActivities']);
        Route::get('/dashboard/tasks', [InstructorController::class, 'getPendingTasks']);
        Route::get('/messages', [InstructorController::class, 'getMessages']);
        Route::post('/messages/send', [InstructorController::class, 'sendMessage']);
        Route::get('/meetings', [InstructorController::class, 'getMeetings']);
        Route::get('/opportunities', [InstructorController::class, 'getOpportunities']);
        Route::get('/notifications', [InstructorController::class, 'getNotifications']);
        Route::get('/announcements', [InstructorController::class, 'getPortalAnnouncements']);
        Route::get('/class-sessions', [InstructorController::class, 'listClassSessions']);
        Route::post('/class-sessions', [InstructorController::class, 'storeClassSession']);
        Route::put('/class-sessions/{id}', [InstructorController::class, 'updateClassSession']);
        Route::delete('/class-sessions/{id}', [InstructorController::class, 'deleteClassSession']);
    });
    
    // Facilitator routes (require facilitator role)
    Route::middleware('facilitator')->prefix('facilitator')->group(function () {
        Route::get('/dashboard/stats', [InstructorController::class, 'getDashboardStats']);
        Route::get('/dashboard/courses', [InstructorController::class, 'getRecentCourses']);
        Route::get('/dashboard/activities', [InstructorController::class, 'getRecentActivities']);
        Route::get('/dashboard/tasks', [InstructorController::class, 'getPendingTasks']);
        Route::get('/messages', [InstructorController::class, 'getMessages']);
        Route::post('/messages/send', [InstructorController::class, 'sendMessage']);
        Route::get('/meetings', [InstructorController::class, 'getMeetings']);
        Route::get('/opportunities', [InstructorController::class, 'getOpportunities']);
        Route::get('/notifications', [InstructorController::class, 'getNotifications']);
        Route::get('/announcements', [InstructorController::class, 'getPortalAnnouncements']);
        Route::get('/class-sessions', [InstructorController::class, 'listClassSessions']);
        Route::post('/class-sessions', [InstructorController::class, 'storeClassSession']);
        Route::put('/class-sessions/{id}', [InstructorController::class, 'updateClassSession']);
        Route::delete('/class-sessions/{id}', [InstructorController::class, 'deleteClassSession']);
    });
    
    // Student routes (require student role)
    Route::middleware('student')->prefix('student')->group(function () {
        Route::get('/dashboard/stats', [StudentController::class, 'getDashboardStats']);
        Route::get('/courses', [StudentController::class, 'getCourses']);
        Route::get('/courses/{courseId}', [StudentController::class, 'getCourseDetails']);
        Route::post('/courses/{courseId}/enroll', [StudentController::class, 'enrollCourse']);
        Route::get('/enrollments/course-ids', [StudentController::class, 'myEnrolledCourseIds']);
        Route::get('/enrollments', [StudentController::class, 'myEnrollments']);
        Route::get('/messages', [StudentController::class, 'getMessages']);
        Route::get('/meetings', [StudentController::class, 'getMeetings']);
        Route::get('/sessions', [StudentController::class, 'getSessions']);
        Route::get('/opportunities', [StudentController::class, 'getOpportunities']);
        Route::get('/notifications', [StudentController::class, 'getNotifications']);
        Route::get('/announcements', [StudentController::class, 'getAnnouncements']);
    });
    
    // Admin routes (require admin role)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats']);

        Route::get('/dashboard/top-courses', [AdminController::class, 'dashboardTopCourses']);
        Route::get('/dashboard/recent-enrollments', [AdminController::class, 'dashboardRecentEnrollments']);
        Route::get('/dashboard/recent-activities', [AdminController::class, 'dashboardRecentActivities']);

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus']);

        Route::get('/students', [AdminController::class, 'getStudents']);

        Route::get('/active-users', [AdminController::class, 'getActiveUsers']);
        Route::get('/activities', [AdminController::class, 'getActivities']);
        Route::get('/requests', [AdminController::class, 'getRequests']);
        Route::post('/requests/{id}/approve', [AdminController::class, 'approveRequest']);
        Route::post('/requests/{id}/deny', [AdminController::class, 'denyRequest']);
        Route::get('/messages', [AdminController::class, 'getMessages']);
        Route::post('/messages/send', [AdminController::class, 'sendMessage']);

        Route::get('/courses', [AdminController::class, 'getCourses']);
        Route::post('/courses', [AdminController::class, 'createCourse']);
        Route::patch('/courses/{courseId}', [AdminController::class, 'updateCourse']);
        Route::post('/courses/{courseId}/assign-instructor', [AdminController::class, 'assignInstructor']);

        Route::get('/categories', [AdminController::class, 'getCategories']);
        Route::post('/categories', [AdminController::class, 'createCategory']);
        Route::patch('/categories/{categoryId}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{categoryId}', [AdminController::class, 'deleteCategory']);

        Route::get('/facilitators', [AdminController::class, 'getFacilitators']);
        Route::post('/facilitators', [AdminController::class, 'storeFacilitator']);
        Route::put('/facilitators/{id}/courses', [AdminController::class, 'updateFacilitatorCourses']);
        Route::delete('/facilitators/{id}', [AdminController::class, 'destroyFacilitator']);

        Route::get('/assignments', [AdminController::class, 'getAssignments']);
        Route::post('/assignments', [AdminController::class, 'storeAssignment']);
        Route::delete('/assignments/{id}', [AdminController::class, 'destroyAssignment']);

        Route::get('/exams', [AdminController::class, 'getExams']);
        Route::post('/exams', [AdminController::class, 'storeExam']);
        Route::delete('/exams/{id}', [AdminController::class, 'destroyExam']);

        Route::get('/enrollments', [AdminController::class, 'getEnrollments']);
        Route::post('/enrollments', [AdminController::class, 'storeEnrollment']);
        Route::put('/enrollments/{id}/progress', [AdminController::class, 'updateEnrollmentProgress']);
        Route::delete('/enrollments/{id}', [AdminController::class, 'destroyEnrollment']);
        Route::get('/enrollments/stats', [AdminController::class, 'getEnrollmentStats']);

        Route::get('/opportunities', [AdminController::class, 'getOpportunities']);
        Route::post('/opportunities', [AdminController::class, 'createOpportunity']);

        Route::get('/notifications/stats', [AdminController::class, 'getNotificationStats']);
        Route::get('/notification-templates', [AdminController::class, 'getNotificationTemplates']);
        Route::get('/notifications', [AdminController::class, 'getNotifications']);
        Route::post('/notifications', [AdminController::class, 'storeAdminNotification']);
        Route::delete('/notifications/{id}', [AdminController::class, 'destroyAdminNotification']);
        Route::post('/notifications/send', [AdminController::class, 'sendNotification']);

        Route::post('/meetings', [AdminController::class, 'createMeeting']);
        Route::put('/meetings/{meetingId}', [AdminController::class, 'updateMeeting']);

        Route::get('/settings', [AdminController::class, 'getSettings']);
        Route::put('/settings', [AdminController::class, 'updateSettings']);

        Route::get('/announcements', [AdminController::class, 'getAnnouncements']);
        Route::post('/announcements', [AdminController::class, 'createAnnouncement']);
        Route::delete('/announcements/{id}', [AdminController::class, 'deleteAnnouncement']);

        Route::get('/reports', [AdminController::class, 'getReports']);
        Route::post('/reports/generate', [AdminController::class, 'generateReport']);
        Route::post('/reports/{type}', [AdminController::class, 'showReport']);

        Route::get('/system/status', [AdminController::class, 'systemStatus']);
        Route::get('/system/logs', [AdminController::class, 'systemLogs']);
        Route::get('/config', [AdminController::class, 'getConfig']);
        Route::put('/config', [AdminController::class, 'putConfig']);
    });
});

// Fallback route
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact support@theshieldmaidens.org'
    ], 404);
});

