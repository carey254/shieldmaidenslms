<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    /**
     * Get instructor dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Get instructor's courses count
        $totalCourses = Course::where('instructor_id', $user->id)->count();
        
        // Get total students
        $totalStudents = Course::join('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('courses.instructor_id', $user->id)
            ->distinct('enrollments.student_id')
            ->count();
            
        // Get total revenue
        $totalRevenue = Enrollment::join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('courses.instructor_id', $user->id)
            ->where('enrollments.status', 'paid')
            ->sum('enrollments.amount') ?? 0;
            
        // Get recent courses
        $recentCourses = Course::where('instructor_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'instructor' => $user,
            'stats' => [
                'total_courses' => $totalCourses,
                'total_students' => $totalStudents,
                'total_revenue' => $totalRevenue
            ],
            'recent_courses' => $recentCourses
        ]);
    }

    /**
     * Get instructor's courses
     */
    public function courses(Request $request)
    {
        $user = $request->user();
        
        $courses = Course::where('instructor_id', $user->id)
            ->withCount(['enrollments' => function($query) {
                $query->where('status', 'active');
            }])
            ->withSum('enrollments as total_revenue')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($courses);
    }

    /**
     * Store a new course
     */
    public function storeCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'level' => 'required|in:beginner,intermediate,advanced',
            'duration' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'instructor_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'level' => $request->level,
            'duration' => $request->duration,
            'status' => 'draft'
        ]);

        return response()->json([
            'course' => $course,
            'message' => 'Course created successfully'
        ], 201);
    }

    /**
     * Update a course
     */
    public function updateCourse(Request $request, $courseId)
    {
        $user = $request->user();
        
        $course = Course::where('id', $courseId)
            ->where('instructor_id', $user->id)
            ->first();
            
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'sometimes|required|string|max:100',
            'level' => 'sometimes|required|in:beginner,intermediate,advanced',
            'duration' => 'sometimes|required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($request->only(['title', 'description', 'price', 'category', 'level', 'duration']));

        return response()->json([
            'course' => $course,
            'message' => 'Course updated successfully'
        ]);
    }

    /**
     * Delete a course
     */
    public function deleteCourse(Request $request, $courseId)
    {
        $user = $request->user();
        
        $course = Course::where('id', $courseId)
            ->where('instructor_id', $user->id)
            ->first();
            
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * Get instructor's students
     */
    public function students(Request $request)
    {
        $user = $request->user();
        
        $students = Enrollment::join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->where('courses.instructor_id', $user->id)
            ->select('users.*', 'enrollments.enrolled_at', 'courses.title as course_title', 'enrollments.progress')
            ->orderBy('enrollments.enrolled_at', 'desc')
            ->paginate(20);

        return response()->json($students);
    }

    /**
     * Get instructor analytics
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        
        // Course performance
        $courseAnalytics = Course::where('instructor_id', $user->id)
            ->withCount(['enrollments' => function($query) {
                $query->where('status', 'active');
            }])
            ->withSum('enrollments as revenue')
            ->orderBy('enrollments_count', 'desc')
            ->get();

        // Monthly revenue
        $monthlyRevenue = Enrollment::join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('courses.instructor_id', $user->id)
            ->where('enrollments.status', 'paid')
            ->selectRaw('MONTH(enrollments.created_at) as month, SUM(enrollments.amount) as revenue')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'course_analytics' => $courseAnalytics,
            'monthly_revenue' => $monthlyRevenue
        ]);
    }

    /**
     * Get instructor payouts
     */
    public function payouts(Request $request)
    {
        $user = $request->user();
        
        $payouts = InstructorPayout::where('instructor_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($payouts);
    }
}
