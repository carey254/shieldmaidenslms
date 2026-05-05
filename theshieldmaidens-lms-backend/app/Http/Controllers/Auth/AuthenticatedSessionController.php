<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');
        $email = $request->email;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->logLoginAttempt($email, false, null, $ipAddress, $userAgent);
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ]);
        }

        // Check if user uses Google auth
        if ($user->auth_provider === 'google') {
            $this->logLoginAttempt($email, false, $user->id, $ipAddress, $userAgent);
            return back()->withErrors([
                'email' => 'This account uses Google authentication. Please sign in with Google.',
            ]);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password_hash)) {
            $this->logLoginAttempt($email, false, $user->id, $ipAddress, $userAgent);
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        // Check user status
        if ($user->status !== 'active') {
            $this->logLoginAttempt($email, false, $user->id, $ipAddress, $userAgent);
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact administrator.',
            ]);
        }

        // Log successful login
        $this->logLoginAttempt($email, true, $user->id, $ipAddress, $userAgent);

        // Authenticate user
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect based on role
        return $this->redirectToDashboard($user);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect user to appropriate dashboard based on role.
     */
    private function redirectToDashboard($user)
    {
        // Check if user is admin via whitelist
        $isAdmin = DB::table('admin_whitelist')
            ->where('email', $user->email)
            ->exists();

        if ($isAdmin) {
            return redirect('/admin/dashboard');
        }

        // Check user role in database
        switch ($user->role) {
            case 'facilitator':
                return redirect('/instructor/dashboard');
            case 'student':
            default:
                return redirect('/student/dashboard');
        }
    }

    /**
     * Log login attempt for security auditing.
     */
    private function logLoginAttempt($email, $success, $userId, $ipAddress, $userAgent)
    {
        DB::table('login_logs')->insert([
            'user_id' => $userId,
            'email' => $email,
            'success' => $success,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
