<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Activity;
use App\Models\UserRequest;
use App\Models\Message;
use App\Models\Course;
use App\Models\Opportunity;
use App\Models\Notification;
use App\Models\Category;
use App\Models\SystemSetting;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Exam;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics (flat keys + stats[] for Vue admin dashboard).
     */
    public function getStats()
    {
        $totalUsers = User::count();
        $activeCourses = DB::table('courses')->where('status', 'published')->count();
        $totalEnrollments = DB::table('enrollments')->count();
        $completionRate = $this->calculateCompletionRate();
        $complianceRate = $this->calculateComplianceRate();

        $statsCards = [
            [
                'label' => 'Total Users',
                'value' => $totalUsers,
                'unit' => '',
                'icon' => 'fas fa-users',
                'color' => '#6366f1',
                'trend' => 'neutral',
                'trendIcon' => 'fas fa-minus',
                'change' => '—',
            ],
            [
                'label' => 'Published Courses',
                'value' => $activeCourses,
                'unit' => '',
                'icon' => 'fas fa-book',
                'color' => '#f97316',
                'trend' => 'neutral',
                'trendIcon' => 'fas fa-minus',
                'change' => '—',
            ],
            [
                'label' => 'Enrollments',
                'value' => $totalEnrollments,
                'unit' => '',
                'icon' => 'fas fa-user-graduate',
                'color' => '#10b981',
                'trend' => 'neutral',
                'trendIcon' => 'fas fa-minus',
                'change' => '—',
            ],
            [
                'label' => 'Completion rate',
                'value' => $completionRate,
                'unit' => '%',
                'icon' => 'fas fa-chart-line',
                'color' => '#8b5cf6',
                'trend' => 'neutral',
                'trendIcon' => 'fas fa-minus',
                'change' => '—',
            ],
        ];

        return response()->json([
            'stats' => $statsCards,
            'totalUsers' => $totalUsers,
            'activeCourses' => $activeCourses,
            'totalEnrollments' => $totalEnrollments,
            'completionRate' => $completionRate,
            'complianceRate' => $complianceRate,
        ]);
    }

    /**
     * Get all users
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'status', 'created_at', 'updated_at')
            ->with(['activities' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($user) {
                $lastActivity = $user->activities->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'progress' => $this->calculateUserProgress($user->id),
                    'created_at' => $user->created_at->toISOString(),
                    'last_active' => $lastActivity ? $lastActivity->created_at->toISOString() : $user->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'users' => $users,
            'data' => $users,
        ]);
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        $activeUsers = Activity::with('user')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'currentAction' => $activity->description,
                    'actionTime' => $activity->created_at->timestamp,
                ];
            });

        return response()->json($activeUsers);
    }

    /**
     * Get recent activities
     */
    public function getActivities()
    {
        $activities = Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'user' => $activity->user ? $activity->user->name : 'System',
                    'type' => $activity->type,
                    'color' => $this->getActivityColor($activity->type),
                    'timestamp' => $activity->created_at->timestamp,
                ];
            });

        return response()->json($activities);
    }

    /**
     * Get user requests
     */
    public function getRequests()
    {
        $requests = UserRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'user' => $request->user ? $request->user->name : 'Unknown',
                    'type' => $request->type,
                    'status' => $request->status,
                    'timestamp' => $request->created_at->timestamp,
                ];
            });

        return response()->json($requests);
    }

    /**
     * Approve a request
     */
    public function approveRequest($id)
    {
        $request = UserRequest::findOrFail($id);
        $request->status = 'approved';
        $request->approved_by = Auth::id();
        $request->approved_at = now();
        $request->save();

        // Log activity
        Activity::create([
            'user_id' => $request->user_id,
            'title' => 'Request Approved',
            'description' => "Request '{$request->title}' was approved",
            'type' => 'request',
        ]);

        return response()->json(['message' => 'Request approved successfully']);
    }

    /**
     * Deny a request
     */
    public function denyRequest($id)
    {
        $request = UserRequest::findOrFail($id);
        $request->status = 'denied';
        $request->approved_by = Auth::id();
        $request->approved_at = now();
        $request->save();

        // Log activity
        Activity::create([
            'user_id' => $request->user_id,
            'title' => 'Request Denied',
            'description' => "Request '{$request->title}' was denied",
            'type' => 'request',
        ]);

        return response()->json(['message' => 'Request denied successfully']);
    }

    /**
     * Get messages
     */
    public function getMessages()
    {
        $messages = Message::orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'message' => $message->content,
                    'recipients' => $message->recipients,
                    'priority' => $message->priority,
                    'opened' => $message->opened_count,
                    'replies' => $message->reply_count,
                    'timestamp' => $message->created_at->timestamp,
                ];
            });

        return response()->json($messages);
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate()
    {
        $totalEnrollments = DB::table('enrollments')->count();
        if ($totalEnrollments === 0) {
            return 0;
        }

        $completedEnrollments = DB::table('enrollments')
            ->whereNotNull('completed_at')
            ->count();

        return round(($completedEnrollments / $totalEnrollments) * 100, 2);
    }

    /**
     * Calculate compliance rate
     */
    private function calculateComplianceRate()
    {
        $totalUsers = User::where('role', 'student')->count();
        if ($totalUsers == 0) return 0;

        $activeUsers = User::where('role', 'student')
            ->where('status', 'active')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        return round(($activeUsers / $totalUsers) * 100, 2);
    }

    /**
     * Calculate user progress
     */
    private function calculateUserProgress($userId)
    {
        $totalEnrollments = DB::table('enrollments')
            ->where('user_id', $userId)
            ->count();

        if ($totalEnrollments === 0) {
            return 0;
        }

        $completedEnrollments = DB::table('enrollments')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();

        return round(($completedEnrollments / $totalEnrollments) * 100, 2);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,medium,high,urgent'
        ]);

        $message = Message::sendBroadcast(
            Auth::id(),
            $request->subject,
            $request->message,
            $request->recipients,
            $request->priority ?? 'medium'
        );

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Message Sent',
            'description' => "Message '{$request->subject}' sent to {$request->recipients}",
            'type' => 'message',
        ]);

        return response()->json(['message' => 'Message sent successfully', 'data' => $message]);
    }

    /**
     * Assign instructor to course
     */
    public function assignInstructor(Request $request, $courseId)
    {
        $request->validate([
            'instructor_id' => 'required|exists:users,id'
        ]);

        $instructor = User::findOrFail($request->instructor_id);
        
        // Update course with instructor
        $updated = DB::table('courses')
            ->where('id', $courseId)
            ->update(['instructor_id' => $request->instructor_id]);

        if ($updated) {
            // Log activity
            Activity::create([
                'user_id' => Auth::id(),
                'title' => 'Instructor Assigned',
                'description' => "Instructor {$instructor->name} assigned to course ID: {$courseId}",
                'type' => 'course',
            ]);

            return response()->json(['message' => 'Instructor assigned successfully']);
        }

        return response()->json(['message' => 'Course not found'], 404);
    }

    /**
     * Create a meeting
     */
    public function createMeeting(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'scheduled_at' => 'required|date',
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id'
        ]);

        $meeting = DB::table('meetings')->insertGetId([
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'participants' => json_encode($request->participants),
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Meeting Created',
            'description' => "Meeting '{$request->title}' scheduled for {$request->scheduled_at}",
            'type' => 'meeting',
        ]);

        return response()->json(['message' => 'Meeting created successfully', 'meeting_id' => $meeting]);
    }

    /**
     * Update a meeting
     */
    public function updateMeeting(Request $request, $meetingId)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'scheduled_at' => 'sometimes|date',
            'participants' => 'sometimes|array',
            'participants.*' => 'exists:users,id'
        ]);

        $updateData = [];
        foreach (['title', 'description', 'scheduled_at'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if ($request->has('participants')) {
            $updateData['participants'] = json_encode($request->participants);
        }

        $updateData['updated_at'] = now();

        $updated = DB::table('meetings')
            ->where('id', $meetingId)
            ->update($updateData);

        if ($updated) {
            // Log activity
            Activity::create([
                'user_id' => Auth::id(),
                'title' => 'Meeting Updated',
                'description' => "Meeting ID: {$meetingId} has been updated",
                'type' => 'meeting',
            ]);

            return response()->json(['message' => 'Meeting updated successfully']);
        }

        return response()->json(['message' => 'Meeting not found'], 404);
    }

    /**
     * Get activity color based on type
     */
    private function getActivityColor($type)
    {
        $colors = [
            'course' => '#ff9900',
            'user' => '#ff9900',
            'assignment' => '#ff9900',
            'quiz' => '#ff9900',
            'request' => '#ff9900',
            'message' => '#ff9900',
            'meeting' => '#ff9900',
            'system' => '#333333',
        ];

        return $colors[$type] ?? '#ff9900';
    }

    /**
     * Create a new course
     */
    public function createCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'duration' => 'nullable|string|max:255',
            'duration_hours' => 'nullable|integer|min:1',
            'modules_count' => 'nullable|integer|min:0|max:500',
            'price' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'instructor_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,review,published,archived',
            'visibility' => 'nullable|in:public,private,scholarship',
            'thumbnail' => 'nullable|string|max:2048',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description ?? '',
            'category' => $request->category,
            'difficulty_level' => $request->input('difficulty_level', 'beginner'),
            'duration' => $request->input('duration'),
            'duration_hours' => $request->input('duration_hours'),
            'modules_count' => (int) $request->input('modules_count', 0),
            'price' => $request->input('price', 0),
            'max_students' => $request->input('max_students', 9999),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'instructor_id' => $request->instructor_id,
            'thumbnail' => $request->input('thumbnail'),
            'visibility' => $request->input('visibility', 'public'),
            'status' => $request->input('status', 'draft'),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($course->status === 'published') {
            try {
                $this->sendNotificationToRole('student', 'New Course Available',
                    "A new course '{$course->title}' is now available for enrollment!",
                    'course', 'medium', ['course_id' => $course->id]);
                if ($course->instructor_id) {
                    $this->sendNotificationToUser($course->instructor_id, 'Course Assignment',
                        "You have been assigned to teach '{$course->title}'",
                        'course', 'high', ['course_id' => $course->id]);
                }
            } catch (\Throwable $e) {
                // Notifications table / setup may not be ready yet
                report($e);
            }
        }

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $this->mapCourseForAdmin($course->fresh(['instructor', 'creator'])),
        ], 201);
    }

    /**
     * Update an existing course.
     */
    public function updateCourse(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'category' => 'sometimes|nullable|string',
            'difficulty_level' => 'sometimes|in:beginner,intermediate,advanced',
            'duration' => 'sometimes|nullable|string|max:255',
            'duration_hours' => 'sometimes|nullable|integer|min:1',
            'modules_count' => 'sometimes|nullable|integer|min:0|max:500',
            'price' => 'sometimes|nullable|numeric|min:0',
            'max_students' => 'sometimes|nullable|integer|min:1',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'instructor_id' => 'sometimes|nullable|exists:users,id',
            'status' => 'sometimes|in:draft,review,published,archived',
            'visibility' => 'sometimes|in:public,private,scholarship',
            'thumbnail' => 'sometimes|nullable|string|max:2048',
        ]);

        $course->fill(array_filter($request->only([
            'title', 'description', 'category', 'difficulty_level', 'duration', 'duration_hours',
            'modules_count', 'price', 'max_students', 'start_date', 'end_date', 'instructor_id',
            'status', 'visibility', 'thumbnail',
        ]), fn ($v) => $v !== null));
        $course->updated_by = Auth::id();
        $course->save();

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $this->mapCourseForAdmin($course->fresh(['instructor', 'creator'])),
        ]);
    }

    private function mapCourseForAdmin(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'category' => $course->category,
            'duration' => $course->duration,
            'duration_hours' => $course->duration_hours,
            'modules_count' => $course->modules_count,
            'difficulty_level' => $course->difficulty_level,
            'modules' => $course->modules_count,
            'status' => $course->status,
            'visibility' => $course->visibility,
            'image' => $course->thumbnail,
            'thumbnail' => $course->thumbnail,
            'price' => $course->price,
            'max_students' => $course->max_students,
            'enrolled_count' => $course->enrolled_count,
            'start_date' => $course->start_date?->format('Y-m-d'),
            'end_date' => $course->end_date?->format('Y-m-d'),
            'instructor' => $course->instructor ? $course->instructor->name : null,
            'instructor_id' => $course->instructor_id,
            'created_at' => $course->created_at?->toISOString(),
            'videos' => 0,
            'assignments' => 0,
        ];
    }

    /**
     * Create a new opportunity
     */
    public function createOpportunity(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:internship,training,job,scholarship',
            'organization' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'deadline' => 'required|date|after:today',
            'requirements' => 'required|string',
            'benefits' => 'required|string',
            'contact_email' => 'required|email',
            'visibility' => 'required|in:all,students,instructors',
            'application_link' => 'nullable|url',
        ]);

        $opportunity = Opportunity::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'organization' => $request->organization,
            'location' => $request->location,
            'deadline' => $request->deadline,
            'requirements' => $request->requirements,
            'benefits' => $request->benefits,
            'contact_email' => $request->contact_email,
            'visibility' => $request->visibility,
            'application_link' => $request->application_link,
            'status' => 'active',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Send notification to relevant users
        $this->sendNotificationToRole($request->visibility, 'New Opportunity Available', 
            "A new {$request->type} opportunity '{$opportunity->title}' is now available!", 
            'opportunity', 'medium', ['opportunity_id' => $opportunity->id]);

        return response()->json(['message' => 'Opportunity created successfully', 'opportunity' => $opportunity]);
    }

    /**
     * Send notification to users
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:system,course,opportunity,assignment,general',
            'priority' => 'required|in:high,medium,low',
            'recipients' => 'required|array',
            'recipients.*.type' => 'required|in:user,role',
            'recipients.*.id' => 'required|string',
        ]);

        $notifications = [];

        foreach ($request->recipients as $recipient) {
            if ($recipient['type'] === 'user') {
                $notification = $this->sendNotificationToUser(
                    $recipient['id'], 
                    $request->title, 
                    $request->message, 
                    $request->type, 
                    $request->priority
                );
                $notifications[] = $notification;
            } elseif ($recipient['type'] === 'role') {
                $roleNotifications = $this->sendNotificationToRole(
                    $recipient['id'], 
                    $request->title, 
                    $request->message, 
                    $request->type, 
                    $request->priority
                );
                $notifications = array_merge($notifications, $roleNotifications);
            }
        }

        return response()->json([
            'message' => 'Notifications sent successfully', 
            'count' => count($notifications)
        ]);
    }

    /**
     * Get all courses
     */
    public function getCourses()
    {
        $courses = Course::with(['instructor', 'creator'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($course) => $this->mapCourseForAdmin($course));

        return response()->json([
            'courses' => $courses,
            'data' => $courses,
        ]);
    }

    /**
     * Get all opportunities
     */
    public function getOpportunities()
    {
        $opportunities = Opportunity::with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => substr($opportunity->description, 0, 100) . '...',
                    'type' => $opportunity->type,
                    'organization' => $opportunity->organization,
                    'location' => $opportunity->location,
                    'deadline' => $opportunity->deadline->format('Y-m-d'),
                    'days_until_deadline' => $opportunity->days_until_deadline,
                    'is_expired' => $opportunity->is_expired,
                    'visibility' => $opportunity->visibility,
                    'status' => $opportunity->status,
                    'application_count' => $opportunity->application_count,
                    'created_by' => $opportunity->creator->name,
                    'created_at' => $opportunity->created_at->toISOString(),
                ];
            });

        return response()->json(['opportunities' => $opportunities]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = Category::with(['creator', 'updater'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'is_active' => $category->is_active,
                    'sort_order' => $category->sort_order,
                    'course_count' => $category->course_count,
                    'active_course_count' => $category->active_course_count,
                    'created_by' => $category->creator?->name,
                    'updated_by' => $category->updater?->name,
                    'created_at' => $category->created_at->toISOString(),
                    'updated_at' => $category->updated_at->toISOString(),
                ];
            });

        return response()->json(['categories' => $categories]);
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'color' => $request->input('color', '#6366f1'),
            'icon' => $request->icon,
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => true,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Category Created',
            'description' => "Category '{$category->name}' was created",
            'type' => 'category',
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Update an existing category
     */
    public function updateCategory(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $categoryId,
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $categoryId,
            'description' => 'sometimes|nullable|string',
            'color' => 'sometimes|nullable|string|max:7',
            'icon' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $category->update(array_filter($request->only([
            'name', 'slug', 'description', 'color', 'icon', 'is_active', 'sort_order'
        ]), fn($v) => !is_null($v)));
        $category->updated_by = Auth::id();
        $category->save();

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Category Updated',
            'description' => "Category '{$category->name}' was updated",
            'type' => 'category',
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Delete a category
     */
    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        // Check if category has courses
        if ($category->courses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated courses'
            ], 422);
        }

        $categoryName = $category->name;
        $category->delete();

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Category Deleted',
            'description' => "Category '{$categoryName}' was deleted",
            'type' => 'category',
        ]);

        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Admin Notification Center list (stored as broadcast messages).
     */
    public function getNotifications()
    {
        try {
            $rows = Message::with('sender')->orderByDesc('created_at')->limit(100)->get();
            $notifications = $rows->map(function (Message $m) {
                $meta = is_array($m->recipients) ? $m->recipients : [];
                $composeType = $meta['compose_type'] ?? 'announcement';

                return [
                    'id' => $m->id,
                    'subject' => $m->subject,
                    'message' => $m->content,
                    'type' => $composeType,
                    'priority' => $m->priority,
                    'status' => $m->status,
                    'sender' => $m->sender?->name ?? 'System',
                    'created_at' => $m->created_at->toISOString(),
                ];
            });

            return response()->json(['notifications' => $notifications]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['notifications' => []]);
        }
    }

    /**
     * Create or save draft notification (admin center → messages table).
     */
    public function storeAdminNotification(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:20',
            'status' => 'nullable|in:draft,sent,scheduled',
        ]);

        $status = $request->input('status', 'draft');
        $subject = $request->input('subject') ?: 'Untitled';
        $body = $request->input('message') ?: '';

        $meta = [
            'compose_type' => $request->input('type', 'announcement'),
            'recipientType' => $request->input('recipientType'),
            'roles' => $request->input('roles', []),
            'courses' => $request->input('courses', []),
        ];

        $message = Message::create([
            'sender_id' => Auth::id(),
            'subject' => $subject,
            'content' => $body,
            'recipients' => $meta,
            'priority' => $request->input('priority', 'medium'),
            'opened_count' => 0,
            'reply_count' => 0,
            'status' => $status === 'sent' ? 'sent' : ($status === 'scheduled' ? 'scheduled' : 'draft'),
            'scheduled_at' => $request->filled('scheduledDate') ? Carbon::parse($request->input('scheduledDate')) : null,
        ]);

        return response()->json([
            'message' => 'Saved',
            'notification' => [
                'id' => $message->id,
                'subject' => $message->subject,
                'message' => $message->content,
                'type' => $meta['compose_type'],
                'status' => $message->status,
            ],
        ], 201);
    }

    /**
     * Delete a notification draft / message row.
     */
    public function destroyAdminNotification($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function getNotificationStats()
    {
        try {
            $sentToday = Message::whereDate('created_at', today())->where('status', 'sent')->count();
            $scheduled = Message::where('status', 'scheduled')->count();
            $totalRecipients = User::count();

            return response()->json([
                'sentToday' => $sentToday,
                'totalRecipients' => $totalRecipients,
                'openRate' => 0,
                'scheduled' => $scheduled,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'sentToday' => 0,
                'totalRecipients' => 0,
                'openRate' => 0,
                'scheduled' => 0,
            ]);
        }
    }

    public function getNotificationTemplates()
    {
        return response()->json([
            'templates' => [
                [
                    'id' => 1,
                    'name' => 'Welcome',
                    'type' => 'welcome',
                    'description' => 'Welcome new learners',
                    'subject' => 'Welcome to The Shield Maidens',
                ],
                [
                    'id' => 2,
                    'name' => 'Course update',
                    'type' => 'reminder',
                    'description' => 'Notify about course changes',
                    'subject' => 'Important course update',
                ],
            ],
        ]);
    }

    /**
     * Helper method to send notification to a user
     */
    private function sendNotificationToUser($userId, $title, $message, $type, $priority, $data = [])
    {
        return Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'sender_id' => Auth::id(),
            'recipient_id' => $userId,
            'recipient_type' => User::class,
            'data' => $data,
            'created_by' => Auth::id(),
            'status' => 'sent',
        ]);
    }

    /**
     * @return array<int, Notification>
     */
    private function sendNotificationToRole(string $role, string $title, string $message, string $type, string $priority, array $data = []): array
    {
        $roleMap = [
            'students' => 'student',
            'student' => 'student',
            'instructors' => 'instructor',
            'instructor' => 'instructor',
            'facilitators' => 'facilitator',
            'facilitator' => 'facilitator',
            'admins' => 'admin',
            'admin' => 'admin',
            'all' => 'all',
        ];
        $dbRole = $roleMap[$role] ?? $role;

        $query = User::query();
        if ($dbRole !== 'all') {
            $query->where('role', $dbRole);
        }

        $created = [];
        foreach ($query->cursor() as $user) {
            $created[] = $this->sendNotificationToUser($user->id, $title, $message, $type, $priority, $data);
        }

        return $created;
    }

    /**
     * Get system settings
     */
    public function getSettings(Request $request)
    {
        $group = $request->get('group');
        
        $query = SystemSetting::query();
        
        if ($group) {
            $query->byGroup($group);
        }
        
        $settings = $query->orderBy('group')->orderBy('key')->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'description' => $setting->description,
                    'is_public' => $setting->is_public,
                    'updated_by' => $setting->updater?->name,
                    'updated_at' => $setting->updated_at->toISOString(),
                ];
            });

        return response()->json(['settings' => $settings]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'required|in:text,number,boolean,json',
            'settings.*.group' => 'required|string',
        ]);

        foreach ($request->settings as $settingData) {
            SystemSetting::set(
                $settingData['key'],
                $settingData['value'],
                $settingData['type'],
                $settingData['group'],
                Auth::id()
            );
        }

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Settings Updated',
            'description' => count($request->settings) . ' system settings were updated',
            'type' => 'system',
        ]);

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Create user with role
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,facilitator,instructor,admin',
            'status' => 'sometimes|in:active,pending_setup,disabled',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'is_admin' => $request->role === 'admin',
            'status' => $request->input('status', 'active'),
            'auth_provider' => 'local',
        ]);

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'User Created',
            'description' => "User '{$user->name}' ({$user->role}) was created",
            'type' => 'user',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id', 'name', 'email', 'role', 'status', 'created_at'])
        ], 201);
    }

    /**
     * Update user status and role
     */
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'role' => 'sometimes|in:student,facilitator,instructor,admin',
            'status' => 'sometimes|in:active,pending_setup,disabled',
            'password' => 'sometimes|nullable|string|min:8',
            'department' => 'sometimes|nullable|string|max:255',
        ]);

        $oldRole = $user->role;
        $oldStatus = $user->status;

        $user->update(array_filter($request->only(['name', 'email', 'role', 'status', 'department']), fn ($v) => ! is_null($v)));

        if ($request->filled('password')) {
            $user->password = $request->password;
            $user->save();
        }

        if ($request->has('role')) {
            $user->is_admin = $request->role === 'admin';
            $user->save();
        }

        // Log role/status changes
        $changes = [];
        if ($oldRole !== $user->role) {
            $changes[] = "role from {$oldRole} to {$user->role}";
        }
        if ($oldStatus !== $user->status) {
            $changes[] = "status from {$oldStatus} to {$user->status}";
        }

        if (!empty($changes)) {
            Activity::create([
                'user_id' => Auth::id(),
                'title' => 'User Updated',
                'description' => "User '{$user->name}' updated: " . implode(', ', $changes),
                'type' => 'user',
            ]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->only(['id', 'name', 'email', 'role', 'status', 'updated_at'])
        ]);
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats()
    {
        $stats = [
            'total_enrollments' => DB::table('enrollments')->count(),
            'active_enrollments' => DB::table('enrollments')->where('status', 'active')->count(),
            'completed_enrollments' => DB::table('enrollments')->whereNotNull('completed_at')->count(),
            'courses_by_popularity' => DB::table('enrollments')
                ->select('course_id', DB::raw('count(*) as enrollment_count'))
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->groupBy('course_id', 'courses.title')
                ->orderBy('enrollment_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_enrollments' => DB::table('enrollments')
                ->select('enrollments.*', 'users.name as student_name', 'courses.title as course_title')
                ->join('users', 'enrollments.user_id', '=', 'users.id')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->orderBy('enrollments.created_at', 'desc')
                ->limit(20)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get announcements
     */
    public function getAnnouncements()
    {
        $announcements = Announcement::with(['creator', 'course'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Announcement $announcement) {
                $base = $announcement->toPortalArray();

                return array_merge($base, [
                    'content' => $announcement->content,
                    'type' => $announcement->type,
                    'audience' => $announcement->audience,
                    'is_active' => $announcement->is_active,
                    'starts_at' => $announcement->starts_at?->toISOString(),
                    'expires_at' => $announcement->expires_at?->toISOString(),
                    'views_count' => $announcement->views_count,
                    'course_id' => $announcement->course_id,
                    'course_title' => $announcement->course?->title,
                    'created_by' => $announcement->creator?->name,
                    'show_on_home' => (bool) $announcement->show_on_home,
                    'show_in_portals' => (bool) $announcement->show_in_portals,
                ]);
            });

        return response()->json(['announcements' => $announcements]);
    }

    /**
     * Create announcement (supports legacy admin UI fields: category, priority, expiry_date, is_featured).
     */
    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'sometimes|in:general,course,system,urgent',
            'audience' => 'sometimes|in:all,students,facilitators,admins',
            'is_pinned' => 'sometimes|boolean',
            'starts_at' => 'sometimes|nullable|date',
            'expires_at' => 'sometimes|nullable|date',
            'expiry_date' => 'sometimes|nullable|date',
            'course_id' => 'sometimes|nullable|exists:courses,id',
            'category' => 'sometimes|nullable|string|max:64',
            'priority' => 'sometimes|nullable|in:low,medium,high,urgent',
            'is_featured' => 'sometimes|boolean',
            'application_link' => 'sometimes|nullable|string|max:2048',
            'show_on_home' => 'sometimes|boolean',
            'show_in_portals' => 'sometimes|boolean',
        ]);

        $priority = $request->input('priority', 'medium');
        $type = $request->input('type');
        if (! $type) {
            $type = $priority === 'urgent' ? 'urgent' : 'general';
        }

        $expiresAt = $request->input('expires_at') ?: $request->input('expiry_date');

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $type,
            'audience' => $request->input('audience', 'all'),
            'category' => $request->input('category', 'general'),
            'priority' => $priority,
            'is_active' => true,
            'is_pinned' => $request->boolean('is_pinned', false),
            'is_featured' => $request->boolean('is_featured', false),
            'application_link' => $request->input('application_link'),
            'show_on_home' => $request->boolean('show_on_home', true),
            'show_in_portals' => $request->boolean('show_in_portals', true),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $expiresAt,
            'course_id' => $request->input('course_id'),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Announcement Created',
            'description' => "Announcement '{$announcement->title}' was created for {$announcement->audience}",
            'type' => 'announcement',
        ]);

        return response()->json([
            'message' => 'Announcement created successfully',
            'announcement' => $announcement,
        ], 201);
    }

    public function deleteAnnouncement($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted']);
    }

    public function dashboardTopCourses()
    {
        $rows = DB::table('enrollments')
            ->select('courses.id', 'courses.title', DB::raw('COUNT(*) as enrollments_count'))
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('enrollments_count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'enrollments_count' => (int) $r->enrollments_count,
            ]);

        return response()->json(['courses' => $rows]);
    }

    public function dashboardRecentEnrollments()
    {
        $rows = DB::table('enrollments')
            ->select(
                'enrollments.id',
                'enrollments.created_at',
                'users.name as student_name',
                'courses.title as course_title'
            )
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->orderByDesc('enrollments.created_at')
            ->limit(15)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'student_name' => $r->student_name,
                'course_title' => $r->course_title,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['enrollments' => $rows]);
    }

    public function dashboardRecentActivities()
    {
        $activities = Activity::with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'description' => $a->description,
                'user' => $a->user?->name ?? 'System',
                'type' => $a->type,
                'timestamp' => $a->created_at->timestamp,
            ]);

        return response()->json(['activities' => $activities]);
    }

    public function getFacilitators()
    {
        $users = User::whereIn('role', ['facilitator', 'instructor'])
            ->orderBy('name')
            ->get();

        $data = $users->map(function (User $u) {
            $courses = Course::where('instructor_id', $u->id)
                ->select('id', 'title')
                ->get();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'department' => $u->department,
                'specialization' => $u->department,
                'status' => $u->status === 'active' ? 'active' : 'inactive',
                'courses' => $courses,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function storeFacilitator(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'specialization' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'instructor',
            'is_admin' => false,
            'status' => 'active',
            'department' => $request->input('specialization'),
            'auth_provider' => 'local',
        ]);

        return response()->json(['message' => 'Created', 'data' => ['id' => $user->id]], 201);
    }

    public function updateFacilitatorCourses(Request $request, int $id)
    {
        $request->validate([
            'course_ids' => 'required|array',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $user = User::whereIn('role', ['facilitator', 'instructor'])->findOrFail($id);

        Course::whereIn('id', $request->course_ids)->update(['instructor_id' => $user->id]);

        return response()->json(['message' => 'Assignments updated']);
    }

    public function destroyFacilitator(int $id)
    {
        $user = User::whereIn('role', ['facilitator', 'instructor'])->findOrFail($id);
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot remove yourself'], 422);
        }
        Course::where('instructor_id', $user->id)->update(['instructor_id' => null]);
        $user->delete();

        return response()->json(['message' => 'Removed']);
    }

    public function getAssignments()
    {
        $assignments = Assignment::with('course')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Assignment $a) {
                $courseId = $a->course_id;
                $totalStudents = DB::table('enrollments')->where('course_id', $courseId)->count();
                $submittedCount = $a->submissions()->count();
                $gradedCount = $a->submissions()->whereNotNull('score')->count();

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'description' => $a->description,
                    'course_id' => $courseId,
                    'course_title' => $a->course?->title ?? '',
                    'due_date' => $a->due_date?->toISOString(),
                    'status' => $a->is_published ? 'published' : 'draft',
                    'total_students' => $totalStudents,
                    'submitted_count' => $submittedCount,
                    'graded_count' => $gradedCount,
                ];
            });

        return response()->json(['data' => $assignments]);
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'due_date' => 'nullable|date',
            'max_points' => 'nullable|numeric|min:0',
        ]);

        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->description,
            'type' => 'assignment',
            'max_score' => $request->input('max_points', 100),
            'passing_score' => 60,
            'due_date' => $request->due_date,
            'is_published' => true,
            'auto_grade' => false,
            'course_id' => $request->course_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Created', 'data' => ['id' => $assignment->id]], 201);
    }

    public function destroyAssignment(int $id)
    {
        Assignment::where('id', $id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function getExams()
    {
        $exams = Exam::with(['course.instructor'])
            ->orderByDesc('start_date')
            ->get()
            ->map(function (Exam $e) {
                $course = $e->course;
                $totalStudents = $course
                    ? DB::table('enrollments')->where('course_id', $course->id)->count()
                    : 0;

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'description' => $e->description,
                    'course_id' => $e->course_id,
                    'course_title' => $course?->title ?? '',
                    'facilitator_name' => $course?->instructor?->name ?? '—',
                    'start_date' => $e->start_date->toISOString(),
                    'duration_minutes' => $e->duration_minutes,
                    'max_points' => $e->max_points,
                    'passing_score' => $e->passing_score,
                    'status' => $e->status,
                    'total_students' => $totalStudents,
                    'submitted_count' => 0,
                ];
            });

        return response()->json(['data' => $exams]);
    }

    public function storeExam(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'max_points' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        $start = Carbon::parse($request->start_date);
        $exam = Exam::create([
            'title' => $request->title,
            'description' => $request->description ?? '',
            'course_id' => $request->course_id,
            'start_date' => $start,
            'end_date' => $start->copy()->addMinutes((int) $request->input('duration_minutes', 60)),
            'duration_minutes' => (int) $request->input('duration_minutes', 60),
            'max_points' => (int) $request->input('max_points', 100),
            'passing_score' => (int) $request->input('passing_score', 60),
            'status' => 'scheduled',
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Created', 'data' => ['id' => $exam->id]], 201);
    }

    public function destroyExam(int $id)
    {
        Exam::where('id', $id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function getEnrollments()
    {
        $rows = DB::table('enrollments')
            ->select(
                'enrollments.*',
                'users.name as student_name',
                'users.email as student_email',
                'courses.title as course_title',
                'courses.category as course_category'
            )
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->orderByDesc('enrollments.created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_id' => $r->user_id,
                    'course_id' => $r->course_id,
                    'student_name' => $r->student_name,
                    'student_email' => $r->student_email,
                    'course_title' => $r->course_title,
                    'course_category' => $r->course_category,
                    'enrollment_date' => $r->enrolled_at ?? $r->created_at,
                    'progress' => (int) $r->progress,
                    'status' => $r->status,
                    'completion_date' => $r->completed_at,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function storeEnrollment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'status' => 'nullable|in:active,pending,suspended,completed,withdrawn',
        ]);

        $ui = $request->input('status', 'active');
        $status = match ($ui) {
            'completed' => 'completed',
            'suspended', 'withdrawn' => 'withdrawn',
            'pending' => 'active',
            default => 'active',
        };

        DB::table('enrollments')->updateOrInsert(
            ['user_id' => $request->student_id, 'course_id' => $request->course_id],
            [
                'progress' => 0,
                'status' => $status,
                'enrolled_at' => now(),
                'completed_at' => $status === 'completed' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['message' => 'Enrollment saved'], 201);
    }

    public function updateEnrollmentProgress(Request $request, int $id)
    {
        $request->validate(['progress' => 'required|integer|min:0|max:100']);

        $row = DB::table('enrollments')->where('id', $id)->first();
        if (! $row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $completedAt = $request->progress >= 100 ? now() : null;
        $status = $request->progress >= 100 ? 'completed' : 'active';

        DB::table('enrollments')->where('id', $id)->update([
            'progress' => $request->progress,
            'status' => $status,
            'completed_at' => $completedAt,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Updated']);
    }

    public function destroyEnrollment(int $id)
    {
        DB::table('enrollments')->where('id', $id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function getStudents()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'status']);

        return response()->json(['data' => $students]);
    }

    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 422);
        }
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin accounts'], 403);
        }
        $user->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function updateUserStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        $incoming = $request->input('status');
        $mapped = $incoming === 'suspended' ? 'disabled' : $incoming;
        if (! in_array($mapped, ['active', 'pending_setup', 'disabled'], true)) {
            return response()->json(['message' => 'Invalid status'], 422);
        }
        $user->status = $mapped;
        $user->save();

        return response()->json(['message' => 'Updated', 'user' => $user->only(['id', 'status'])]);
    }

    public function getReports(Request $request)
    {
        return response()->json(['reports' => []]);
    }

    public function generateReport(Request $request)
    {
        return response()->json(['message' => 'Report queued', 'status' => 'ok']);
    }

    public function showReport(Request $request, string $type)
    {
        return response()->json([
            'type' => $type,
            'rows' => [],
            'generated_at' => now()->toISOString(),
        ]);
    }

    public function systemStatus()
    {
        return response()->json([
            'status' => [
                'server' => [
                    'status' => 'healthy',
                    'icon' => 'fas fa-check-circle',
                    'message' => 'Laravel API',
                    'cpu' => 12,
                    'memory' => 45,
                    'disk' => 30,
                ],
                'database' => [
                    'status' => 'healthy',
                    'icon' => 'fas fa-check-circle',
                    'message' => 'Connected',
                    'connections' => 4,
                    'queryTime' => 12,
                ],
                'application' => [
                    'status' => 'healthy',
                    'icon' => 'fas fa-check-circle',
                    'message' => 'Running',
                    'version' => app()->version(),
                ],
            ],
        ]);
    }

    public function systemLogs()
    {
        return response()->json([
            'logs' => [
                ['level' => 'info', 'message' => 'API ready', 'timestamp' => now()->toISOString()],
            ],
        ]);
    }

    public function getConfig()
    {
        return response()->json([
            'config' => [
                'platformName' => 'Shield Maidens LMS',
                'defaultLanguage' => 'en',
                'maintenanceMode' => false,
                'sessionTimeout' => 120,
                'twoFactorAuth' => false,
                'passwordComplexity' => 'medium',
                'autoBackup' => true,
                'backupFrequency' => 'daily',
            ],
        ]);
    }

    public function putConfig(Request $request)
    {
        return response()->json(['message' => 'Config endpoint stub — persist via settings API if needed']);
    }
}
