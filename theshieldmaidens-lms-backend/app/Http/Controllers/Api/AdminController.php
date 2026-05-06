<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        $stats = [
            'totalUsers' => User::count(),
            'activeCourses' => DB::table('courses')->where('status', 'published')->count(),
            'totalEnrollments' => DB::table('enrollments')->count(),
            'completionRate' => $this->calculateCompletionRate(),
            'complianceRate' => $this->calculateComplianceRate(),
        ];

        return response()->json($stats);
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

        return response()->json($users);
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

        return response()->json(['courses' => $courses]);
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
     * Get all notifications
     */
    public function getNotifications()
    {
        $notifications = Notification::with(['sender', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'sender' => $notification->sender ? $notification->sender->name : 'System',
                    'recipient_type' => $notification->recipient_type,
                    'recipient_id' => $notification->recipient_id,
                    'read_at' => $notification->read_at,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
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
        ]);
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
            'role' => 'required|in:student,facilitator,admin',
            'status' => 'sometimes|in:active,pending_setup,disabled',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
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
            'role' => 'sometimes|in:student,facilitator,admin',
            'status' => 'sometimes|in:active,pending_setup,disabled',
        ]);

        $oldRole = $user->role;
        $oldStatus = $user->status;

        $user->update(array_filter($request->only(['name', 'email', 'role', 'status']), fn($v) => !is_null($v)));

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
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'type' => $announcement->type,
                    'audience' => $announcement->audience,
                    'is_active' => $announcement->is_active,
                    'is_pinned' => $announcement->is_pinned,
                    'starts_at' => $announcement->starts_at?->toISOString(),
                    'expires_at' => $announcement->expires_at?->toISOString(),
                    'views_count' => $announcement->views_count,
                    'course_id' => $announcement->course_id,
                    'course_title' => $announcement->course?->title,
                    'created_by' => $announcement->creator->name,
                    'created_at' => $announcement->created_at->toISOString(),
                ];
            });

        return response()->json(['announcements' => $announcements]);
    }

    /**
     * Create announcement
     */
    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,course,system,urgent',
            'audience' => 'required|in:all,students,facilitators,admins',
            'is_pinned' => 'sometimes|boolean',
            'starts_at' => 'sometimes|nullable|date',
            'expires_at' => 'sometimes|nullable|date|after:starts_at',
            'course_id' => 'sometimes|nullable|exists:courses,id',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'audience' => $request->audience,
            'is_active' => true,
            'is_pinned' => $request->input('is_pinned', false),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $request->input('expires_at'),
            'course_id' => $request->course_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Announcement Created',
            'description' => "Announcement '{$announcement->title}' was created for {$announcement->audience}",
            'type' => 'announcement',
        ]);

        return response()->json([
            'message' => 'Announcement created successfully',
            'announcement' => $announcement
        ], 201);
    }
}
