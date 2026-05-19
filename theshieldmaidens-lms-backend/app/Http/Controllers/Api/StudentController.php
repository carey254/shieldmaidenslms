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
use App\Models\Message;
use App\Models\Opportunity;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\ClassSession;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Discussion;
use App\Models\Group;

class StudentController extends Controller
{
    /**
     * Get student dashboard statistics
     */
    public function getDashboardStats()
    {
        $student = Auth::user();
        
        // Total enrolled courses
        $totalCourses = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->count();

        // Completed courses
        $completedCourses = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        // In-progress courses
        $inProgressCourses = $totalCourses - $completedCourses;

        // Average completion rate
        $avgCompletion = $totalCourses > 0 ? round(($completedCourses / $totalCourses) * 100, 2) : 0;

        // Recent activities
        $recentActivities = Activity::where('user_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->count();

        $stats = [
            'totalCourses' => $totalCourses,
            'completedCourses' => $completedCourses,
            'inProgressCourses' => $inProgressCourses,
            'avgCompletion' => $avgCompletion,
            'recentActivities' => $recentActivities,
        ];

        return response()->json($stats);
    }

    /**
     * Get available courses for enrollment
     */
    public function getCourses()
    {
        $student = Auth::user();

        $enrolledCourseIds = Enrollment::where('user_id', $student->id)->pluck('course_id');

        $courses = Course::query()
            ->catalogVisible()
            ->whereNotIn('id', $enrolledCourseIds)
            ->with('instructor')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'category' => $course->category,
                    'level' => $course->difficulty_level,
                    'duration' => $course->duration,
                    'modules_count' => $course->modules_count,
                    'instructor' => $course->instructor ? $course->instructor->name : 'Not assigned',
                ];
            });

        return response()->json($courses);
    }

    /**
     * Course IDs the current student is enrolled in (for curriculum UI badges).
     */
    public function myEnrolledCourseIds()
    {
        $ids = Enrollment::where('user_id', Auth::id())->pluck('course_id')->values();

        return response()->json(['course_ids' => $ids]);
    }

    /**
     * Enrolled courses with metadata for dashboard / My Courses.
     */
    public function myEnrollments()
    {
        $list = Enrollment::query()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'description', 'duration', 'difficulty_level');
            }])
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(function (Enrollment $e) {
                $c = $e->course;

                return [
                    'course_id' => $e->course_id,
                    'title' => $c?->title ?? 'Course',
                    'description' => $c?->description,
                    'duration' => $c?->duration,
                    'level' => $c?->difficulty_level,
                    'progress' => (int) $e->progress,
                    'enrolled_at' => $e->enrolled_at?->toISOString(),
                ];
            });

        return response()->json(['enrollments' => $list]);
    }

    /**
     * Get course details
     */
    public function getCourseDetails($courseId)
    {
        $student = Auth::user();

        $course = Course::with('instructor')->find($courseId);

        if (!$course || $course->status !== 'published') {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $enrollment = Enrollment::where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->first();

        $courseDetails = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'category' => $course->category,
            'duration' => $course->duration,
            'modules_count' => $course->modules_count,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'email' => $course->instructor->email,
            ] : null,
            'isEnrolled' => (bool) $enrollment,
            'enrollment' => $enrollment ? [
                'enrolled_at' => $enrollment->enrolled_at,
                'completed_at' => $enrollment->completed_at,
                'progress' => (int) $enrollment->progress,
            ] : null,
        ];

        return response()->json($courseDetails);
    }

    /**
     * Enroll in a course
     */
    public function enrollCourse($courseId)
    {
        $student = Auth::user();

        $courseModel = Course::query()
            ->where('id', $courseId)
            ->catalogVisible()
            ->first();

        if (!$courseModel) {
            return response()->json(['message' => 'Course not found or not open for enrollment'], 404);
        }

        $existingEnrollment = Enrollment::where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->exists();

        if ($existingEnrollment) {
            return response()->json(['message' => 'Already enrolled in this course'], 422);
        }

        $current = Enrollment::where('course_id', $courseId)->count();
        if ($courseModel->max_students && $current >= $courseModel->max_students) {
            return response()->json(['message' => 'This course has reached maximum enrollments'], 423);
        }

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $courseId,
            'progress' => 0,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        try {
            Activity::create([
                'user_id' => $student->id,
                'title' => 'Course Enrolled',
                'description' => "Enrolled in course: {$courseModel->title}",
                'type' => 'course',
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Successfully enrolled in course',
            'course_id' => (int) $courseId,
        ]);
    }

    /**
     * Get student's messages
     */
    public function getMessages()
    {
        $student = Auth::user();
        
        // Get messages sent to students or all users
        $messages = Message::where(function($query) use ($student) {
                $query->where('recipients', 'students')
                      ->orWhere('recipients', 'all')
                      ->orWhereJsonContains('recipients', $student->id);
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
     * Get student's meetings
     */
    public function getMeetings()
    {
        $student = Auth::user();
        
        // Get courses the student is enrolled in
        $enrolledCourseIds = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->pluck('course_id');

        // Get instructors for enrolled courses
        $instructorIds = DB::table('courses')
            ->whereIn('id', $enrolledCourseIds)
            ->whereNotNull('instructor_id')
            ->pluck('instructor_id');

        $meetings = DB::table('meetings')
            ->whereJsonContains('participants', $student->id)
            ->orWhereJsonContains('participants', $instructorIds)
            ->orWhere('created_by', $student->id)
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
     * Get available opportunities for students
     */
    public function getOpportunities()
    {
        $opportunities = Opportunity::visibleTo('student')
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
     * Get student notifications
     */
    public function getNotifications()
    {
        $student = Auth::user();
        
        $notifications = Notification::forUser($student->id)
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
     * Get student announcements (portal + optional course-scoped).
     */
    public function getAnnouncements()
    {
        $student = Auth::user();

        $enrolledCourseIds = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->pluck('course_id');

        $rows = Announcement::query()
            ->active()
            ->where('show_in_portals', true)
            ->where(function ($q) {
                $q->whereIn('audience', ['all', 'students']);
            })
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereNull('course_id');
                if ($enrolledCourseIds->isNotEmpty()) {
                    $q->orWhereIn('course_id', $enrolledCourseIds);
                }
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(fn (Announcement $a) => $a->toPortalArray())
            ->values()
            ->all();

        return response()->json($rows);
    }

    /**
     * Get student class sessions (live / Google Meet style links).
     */
    public function getSessions()
    {
        $student = Auth::user();

        $enrolledCourseIds = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->pluck('course_id');

        if ($enrolledCourseIds->isEmpty()) {
            return response()->json(['sessions' => []]);
        }

        $sessions = ClassSession::query()
            ->whereIn('course_id', $enrolledCourseIds)
            ->with(['course:id,title', 'instructor:id,name'])
            ->orderBy('starts_at')
            ->limit(40)
            ->get()
            ->map(function (ClassSession $s) {
                $now = now();
                $status = $s->status;
                if ($status === 'scheduled' && $s->starts_at && $s->starts_at->lte($now) && ($s->ends_at === null || $s->ends_at->gte($now))) {
                    $status = 'live';
                }

                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'description' => $s->description,
                    'course_id' => $s->course_id,
                    'course_name' => $s->course?->title,
                    'instructor' => $s->instructor ? ['name' => $s->instructor->name] : null,
                    'scheduled_at' => $s->starts_at?->toISOString(),
                    'duration' => $s->duration_minutes,
                    'meeting_link' => $s->meeting_link,
                    'recording_url' => $s->recording_url,
                    'status' => $status,
                ];
            });

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Get student's submissions (quizzes and assignments)
     */
    public function getSubmissions()
    {
        $student = Auth::user();

        $submissions = Submission::where('user_id', $student->id)
            ->with(['assignment' => function ($q) {
                $q->select('id', 'title', 'due_date', 'max_score', 'passing_score');
            }])
            ->orderByDesc('submitted_at')
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'title' => $submission->assignment ? $submission->assignment->title : 'Unknown Assignment',
                    'submittedDate' => $submission->submitted_at ? $submission->submitted_at->format('F j, Y') : 'N/A',
                    'status' => ucfirst($submission->status),
                    'score' => $submission->score ? $submission->score . '/' . ($submission->assignment ? $submission->assignment->max_score : 100) : null,
                ];
            });

        return response()->json($submissions);
    }

    /**
     * Get community discussions
     */
    public function getCommunity()
    {
        $student = Auth::user();

        // Get enrolled course IDs
        $enrolledCourseIds = DB::table('enrollments')
            ->where('user_id', $student->id)
            ->pluck('course_id');

        // Get discussions from enrolled courses or general discussions
        $discussions = Discussion::query()
            ->where(function ($query) use ($enrolledCourseIds) {
                $query->whereNull('course_id')
                      ->orWhereIn('course_id', $enrolledCourseIds);
            })
            ->notLocked()
            ->with(['user:id,name', 'course:id,title'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($discussion) {
                return [
                    'id' => $discussion->id,
                    'title' => $discussion->title,
                    'author' => $discussion->user ? $discussion->user->name : 'Unknown',
                    'preview' => substr(strip_tags($discussion->content), 0, 100) . '...',
                    'replies_count' => $discussion->replies_count,
                    'views' => $discussion->views,
                    'category' => $discussion->category,
                    'course' => $discussion->course ? $discussion->course->title : null,
                    'created_at' => $discussion->created_at->toISOString(),
                ];
            });

        return response()->json($discussions);
    }

    /**
     * Get student's certificates (only for completed courses)
     */
    public function getCertificates()
    {
        $student = Auth::user();

        // Get enrollments where course is completed
        $completedEnrollments = Enrollment::where('user_id', $student->id)
            ->whereNotNull('completed_at')
            ->where('status', 'completed')
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'category');
            }])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'title' => $enrollment->course ? $enrollment->course->title : 'Course',
                    'completedDate' => $enrollment->completed_at ? $enrollment->completed_at->format('F j, Y') : 'N/A',
                    'certificateId' => 'CERT-' . strtoupper(substr(md5($enrollment->id . $enrollment->course_id), 0, 8)),
                ];
            });

        return response()->json($completedEnrollments);
    }

    /**
     * Get student's groups (assigned by facilitator)
     */
    public function getGroups()
    {
        $student = Auth::user();

        // Get groups the student is a member of
        $groups = Group::query()
            ->active()
            ->whereHas('members', function ($query) use ($student) {
                $query->where('user_id', $student->id);
            })
            ->with(['facilitator:id,name', 'course:id,title'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($group) {
                $isMember = $group->members()->where('user_id', $student->id)->exists();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'memberCount' => $group->member_count,
                    'maxMembers' => $group->max_members,
                    'isJoined' => $isMember,
                    'facilitator' => $group->facilitator ? $group->facilitator->name : null,
                    'course' => $group->course ? $group->course->title : null,
                    'code' => $group->code,
                ];
            });

        return response()->json($groups);
    }
}
