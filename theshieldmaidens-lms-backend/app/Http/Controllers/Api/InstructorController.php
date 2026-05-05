<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Message;
use App\Models\Opportunity;
use App\Models\Notification;

class InstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('instructor');
    }

    /**
     * Get instructor dashboard statistics
     */
    public function getDashboardStats()
    {
        $instructor = Auth::user();
        
        // Get courses assigned to this instructor
        $instructorCourses = DB::table('courses')
            ->where('instructor_id', $instructor->id)
            ->pluck('id');

        // Total courses
        $totalCourses = $instructorCourses->count();

        // Total students across all instructor's courses
        $totalStudents = DB::table('enrollments')
            ->whereIn('course_id', $instructorCourses)
            ->distinct('user_id')
            ->count();

        // Average completion rate
        $avgCompletion = DB::table('enrollments')
            ->whereIn('course_id', $instructorCourses)
            ->whereNotNull('completed_at')
            ->count() > 0 ? 
            round((DB::table('enrollments')
                ->whereIn('course_id', $instructorCourses)
                ->whereNotNull('completed_at')
                ->count() / 
                DB::table('enrollments')
                    ->whereIn('course_id', $instructorCourses)
                    ->count()) * 100) : 0;

        // Average rating (placeholder - would need ratings table)
        $avgRating = 4.6; // This would come from a ratings table

        // Calculate changes
        $thisMonthCourses = DB::table('courses')
            ->where('instructor_id', $instructor->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $thisWeekStudents = DB::table('enrollments')
            ->whereIn('course_id', $instructorCourses)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $stats = [
            'totalCourses' => $totalCourses,
            'totalStudents' => $totalStudents,
            'avgCompletion' => $avgCompletion,
            'avgRating' => $avgRating,
            'changes' => [
                'coursesThisMonth' => $thisMonthCourses,
                'studentsThisWeek' => $thisWeekStudents,
                'completionIncrease' => 5, // Placeholder
                'ratingIncrease' => 0.3 // Placeholder
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Get instructor's recent courses
     */
    public function getRecentCourses()
    {
        $instructor = Auth::user();
        
        $courses = DB::table('courses')
            ->where('instructor_id', $instructor->id)
            ->select('id', 'title', 'description', 'image', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($course) {
                $enrolledStudents = DB::table('enrollments')
                    ->where('course_id', $course->id)
                    ->count();

                $avgCompletion = DB::table('enrollments')
                    ->where('course_id', $course->id)
                    ->whereNotNull('completed_at')
                    ->count() > 0 ?
                    round((DB::table('enrollments')
                        ->where('course_id', $course->id)
                        ->whereNotNull('completed_at')
                        ->count() / 
                        DB::table('enrollments')
                            ->where('course_id', $course->id)
                            ->count()) * 100) : 0;

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'image' => $course->image,
                    'enrolledStudents' => $enrolledStudents,
                    'avgCompletion' => $avgCompletion
                ];
            });

        return response()->json($courses);
    }

    /**
     * Get instructor's recent activities
     */
    public function getRecentActivities()
    {
        $instructor = Auth::user();
        
        $activities = Activity::with(['user', 'course'])
            ->whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->orWhere('user_id', $instructor->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type ?? 'system',
                    'description' => $activity->description,
                    'timestamp' => $activity->created_at->timestamp
                ];
            });

        return response()->json($activities);
    }

    /**
     * Get instructor's pending tasks
     */
    public function getPendingTasks()
    {
        $instructor = Auth::user();
        
        // Get courses for this instructor
        $instructorCourses = DB::table('courses')
            ->where('instructor_id', $instructor->id)
            ->pluck('id');

        // Pending assignments to grade
        $pendingAssignments = DB::table('submissions')
            ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('assignments.course_id', $instructorCourses)
            ->where('submissions.status', 'submitted')
            ->whereNull('submissions.graded_at')
            ->count();

        $tasks = [
            [
                'id' => 1,
                'title' => 'Grade Assignments',
                'description' => "{$pendingAssignments} assignments pending review",
                'dueDate' => 'Today',
                'priority' => $pendingAssignments > 0 ? 'high' : 'low',
                'urgent' => $pendingAssignments > 10,
                'action' => 'Grade Now'
            ]
        ];

        return response()->json($tasks);
    }

    /**
     * Get instructor's messages
     */
    public function getMessages()
    {
        $instructor = Auth::user();
        
        // Get messages sent to instructors or all users
        $messages = Message::where(function($query) use ($instructor) {
                $query->where('recipients', 'instructors')
                      ->orWhere('recipients', 'all')
                      ->orWhereJsonContains('recipients', $instructor->id);
            })
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'message' => $message->content,
                    'sender' => $message->sender ? $message->sender->name : 'System',
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
     * Send a message (instructor to students/admin)
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
     * Get instructor's meetings
     */
    public function getMeetings()
    {
        $instructor = Auth::user();
        
        $meetings = DB::table('meetings')
            ->whereJsonContains('participants', $instructor->id)
            ->orWhere('created_by', $instructor->id)
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'description' => $meeting->description,
                    'scheduled_at' => $meeting->scheduled_at,
                    'participants' => json_decode($meeting->participants),
                    'created_by' => $meeting->created_by,
                    'created_at' => $meeting->created_at,
                    'updated_at' => $meeting->updated_at,
                ];
            });

        return response()->json($meetings);
    }

    /**
     * Get available opportunities for instructors
     */
    public function getOpportunities()
    {
        $opportunities = Opportunity::visibleTo('instructor')
            ->active()
            ->upcoming()
            ->orderBy('deadline', 'asc')
            ->get()
            ->map(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'description' => substr($opportunity->description, 0, 150) . '...',
                    'type' => $opportunity->type,
                    'organization' => $opportunity->organization,
                    'location' => $opportunity->location,
                    'deadline' => $opportunity->deadline->format('Y-m-d'),
                    'days_until_deadline' => $opportunity->days_until_deadline,
                    'requirements' => substr($opportunity->requirements, 0, 100) . '...',
                    'benefits' => substr($opportunity->benefits, 0, 100) . '...',
                    'application_link' => $opportunity->application_link,
                    'contact_email' => $opportunity->contact_email,
                    'created_at' => $opportunity->created_at->toISOString(),
                ];
            });

        return response()->json(['opportunities' => $opportunities]);
    }

    /**
     * Get instructor notifications
     */
    public function getNotifications()
    {
        $instructor = Auth::user();
        
        $notifications = Notification::forUser($instructor->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'sender' => $notification->sender ? $notification->sender->name : 'System',
                    'read_at' => $notification->read_at,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }
}
