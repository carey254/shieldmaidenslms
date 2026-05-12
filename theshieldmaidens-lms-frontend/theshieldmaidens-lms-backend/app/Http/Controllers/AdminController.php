<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function dashboard(Request $request)
    {
        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_revenue' => Payment::sum('amount') ?? 0,
            'active_courses' => Course::where('status', 'published')->count(),
            'draft_courses' => Course::where('status', 'draft')->count()
        ];

        // Get recent activity
        $recentActivity = ActivityLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['user'])
            ->get();

        // Get monthly revenue chart data
        $monthlyRevenue = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as revenue')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'monthly_revenue' => $monthlyRevenue
        ]);
    }

    /**
     * Get all users
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
            'phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'email_verified_at' => now()
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,'.$userId,
            'role' => 'sometimes|required|in:student,instructor,admin',
            'phone' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|in:active,inactive,banned'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['name', 'email', 'role', 'phone', 'status']));

        return response()->json([
            'user' => $user,
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser(Request $request, $userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Don't allow deletion of admins (except self)
        if ($user->role === 'admin' && $user->id !== $request->user()->id) {
            return response()->json(['message' => 'Cannot delete admin users'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get all courses
     */
    public function courses(Request $request)
    {
        $query = Course::with(['instructor']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($courses);
    }

    /**
     * Get admin analytics
     */
    public function analytics(Request $request)
    {
        // User analytics
        $userAnalytics = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // Course analytics
        $courseAnalytics = Course::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Enrollment trends
        $enrollmentTrends = Enrollment::selectRaw('DATE(created_at) as date, COUNT(*) as enrollments')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Revenue analytics
        $revenueAnalytics = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'user_analytics' => $userAnalytics,
            'course_analytics' => $courseAnalytics,
            'enrollment_trends' => $enrollmentTrends,
            'revenue_analytics' => $revenueAnalytics
        ]);
    }

    /**
     * Get admin reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'users');
        
        switch ($type) {
            case 'users':
                $data = User::select('name', 'email', 'role', 'created_at', 'last_login_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;
                
            case 'courses':
                $data = Course::with(['instructor'])
                    ->withCount(['enrollments'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;
                
            case 'revenue':
                $data = Payment::with(['user', 'course'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;
                
            case 'activity':
                $data = ActivityLog::with(['user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();
                break;
                
            default:
                $data = [];
        }

        return response()->json($data);
    }

    /**
     * Get system settings
     */
    public function settings(Request $request)
    {
        $settings = [
            'site_name' => config('app.name'),
            'site_email' => config('mail.from.address'),
            'registration_enabled' => true,
            'email_verification_required' => true,
            'payment_gateways' => ['stripe', 'paypal', 'mpesa'],
            'max_upload_size' => '50MB',
            'allowed_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'png', 'mp4', 'avi']
        ];

        return response()->json($settings);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        // This would typically update a settings table or config file
        // For now, return success response
        return response()->json(['message' => 'Settings updated successfully']);
    }
}
