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
use App\Models\Announcement;
use App\Models\ClassSession;

class InstructorController extends Controller
{
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
        $role = Auth::user()->role;

        $opportunities = Opportunity::visibleTo($role)
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

    /**
     * Announcements visible in facilitator / instructor portals.
     */
    public function getPortalAnnouncements()
    {
        $rows = Announcement::query()
            ->active()
            ->where('show_in_portals', true)
            ->whereIn('audience', ['all', 'facilitators', 'students'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->map(fn (Announcement $a) => $a->toPortalArray())
            ->values()
            ->all();

        return response()->json($rows);
    }

    public function listClassSessions()
    {
        $uid = Auth::id();
        $courseIds = DB::table('courses')->where('instructor_id', $uid)->pluck('id');

        if ($courseIds->isEmpty()) {
            return response()->json(['sessions' => []]);
        }

        $sessions = ClassSession::query()
            ->whereIn('course_id', $courseIds)
            ->with(['course:id,title'])
            ->orderBy('starts_at')
            ->limit(80)
            ->get()
            ->map(function (ClassSession $s) {
                return [
                    'id' => $s->id,
                    'course_id' => $s->course_id,
                    'course_title' => $s->course?->title,
                    'title' => $s->title,
                    'description' => $s->description,
                    'meeting_link' => $s->meeting_link,
                    'starts_at' => $s->starts_at?->toISOString(),
                    'ends_at' => $s->ends_at?->toISOString(),
                    'duration_minutes' => $s->duration_minutes,
                    'status' => $s->status,
                    'recording_url' => $s->recording_url,
                ];
            });

        return response()->json(['sessions' => $sessions]);
    }

    public function storeClassSession(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_link' => 'nullable|string|max:2048',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'duration_minutes' => 'nullable|integer|min:5|max:720',
            'status' => 'sometimes|in:scheduled,live,completed,cancelled',
        ]);

        $course = Course::findOrFail($data['course_id']);
        if ((int) $course->instructor_id !== (int) Auth::id()) {
            return response()->json(['message' => 'You can only schedule sessions for courses assigned to you.'], 403);
        }

        $session = ClassSession::create([
            'course_id' => $course->id,
            'instructor_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
        ]);

        return response()->json(['message' => 'Session created', 'session' => $session], 201);
    }

    public function updateClassSession(Request $request, int $id)
    {
        $session = ClassSession::findOrFail($id);
        if ((int) $session->instructor_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'meeting_link' => 'nullable|string|max:2048',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:5|max:720',
            'status' => 'sometimes|in:scheduled,live,completed,cancelled',
            'recording_url' => 'nullable|string|max:2048',
        ]);

        $session->fill($data);
        $session->save();

        return response()->json(['message' => 'Session updated', 'session' => $session]);
    }

    public function deleteClassSession(int $id)
    {
        $session = ClassSession::findOrFail($id);
        if ((int) $session->instructor_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $session->delete();

        return response()->json(['message' => 'Session deleted']);
    }
}
