<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Get student dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Get enrolled courses count
        $enrolledCourses = Enrollment::where('student_id', $user->id)->count();
        
        // Get completed courses count
        $completedCourses = Enrollment::where('student_id', $user->id)
            ->where('status', 'completed')
            ->count();
            
        // Get overall progress
        $overallProgress = Progress::where('student_id', $user->id)
            ->avg('progress_percentage') ?? 0;
            
        // Get recent courses
        $recentCourses = Course::join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.student_id', $user->id)
            ->orderBy('enrollments.created_at', 'desc')
            ->limit(5)
            ->get(['courses.*', 'enrollments.created_at as enrolled_at']);

        return response()->json([
            'student' => $user,
            'stats' => [
                'enrolled_courses' => $enrolledCourses,
                'completed_courses' => $completedCourses,
                'overall_progress' => round($overallProgress, 2)
            ],
            'recent_courses' => $recentCourses
        ]);
    }

    /**
     * Get student's enrolled courses
     */
    public function courses(Request $request)
    {
        $user = $request->user();
        
        $courses = Course::join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.student_id', $user->id)
            ->with(['instructor', 'sections'])
            ->orderBy('enrollments.created_at', 'desc')
            ->get(['courses.*', 'enrollments.status as enrollment_status', 'enrollments.progress']);

        return response()->json($courses);
    }

    /**
     * Get student enrollments
     */
    public function enrollments(Request $request)
    {
        $user = $request->user();
        
        $enrollments = Enrollment::where('student_id', $user->id)
            ->with(['course.instructor', 'progress'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($enrollments);
    }

    /**
     * Get student progress
     */
    public function progress(Request $request)
    {
        $user = $request->user();
        
        $progress = Progress::where('student_id', $user->id)
            ->with(['course', 'lesson'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($progress);
    }

    /**
     * Get student certificates
     */
    public function certificates(Request $request)
    {
        $user = $request->user();
        
        $certificates = Certificate::where('student_id', $user->id)
            ->with(['course'])
            ->orderBy('issued_at', 'desc')
            ->paginate(10);

        return response()->json($certificates);
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request, $courseId)
    {
        $user = $request->user();
        
        // Check if course exists
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Check if already enrolled
        $existingEnrollment = Enrollment::where('student_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingEnrollment) {
            return response()->json(['message' => 'Already enrolled in this course'], 422);
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'student_id' => $user->id,
            'course_id' => $courseId,
            'status' => 'active',
            'enrolled_at' => now(),
            'progress' => 0
        ]);

        return response()->json([
            'enrollment' => $enrollment,
            'message' => 'Successfully enrolled in course'
        ], 201);
    }
}
