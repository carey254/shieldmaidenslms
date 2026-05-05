<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\SecureLoginRequest;
use App\Http\Requests\Api\SecureRegisterRequest;
use App\Http\Requests\Api\SecurePasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        // Add custom validation rules
        Validator::extend('not_url', function ($attribute, $value, $parameters, $validator) {
            return !filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $value);
        });

        Validator::extend('captcha', function ($attribute, $value, $parameters, $validator) {
            // Simplified CAPTCHA validation for development
            // In production, integrate with reCAPTCHA or similar service
            return !empty($value) && strlen($value) > 10;
        });
    }
    /**
     * Register a new user
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
            'role' => 'student',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->only(['id', 'name', 'email', 'role', 'is_admin', 'is_instructor', 'created_at']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Secure register a new user with enhanced security
     *
     * @param  SecureRegisterRequest  $request
     * @return JsonResponse
     */
    public function secureRegister(SecureRegisterRequest $request): JsonResponse
    {
        // Apply rate limiting
        $key = 'register-attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many registration attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 3600); // 1 hour window

        $validated = $request->validated();
        
        // Validate CAPTCHA (simplified version)
        if (!$this->validateCaptcha($validated['captcha_token'])) {
            return response()->json([
                'message' => 'CAPTCHA validation failed. Please try again.'
            ], 422);
        }
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
            'role' => 'student',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->only(['id', 'name', 'email', 'role', 'is_admin', 'is_instructor', 'created_at']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Find user by email first (matches session login UX / clearer errors)
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided password is incorrect.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->only(['id', 'name', 'email', 'is_admin', 'is_instructor', 'role']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Secure login user with enhanced security
     *
     * @param  SecureLoginRequest  $request
     * @return JsonResponse
     */
    public function secureLogin(SecureLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        
        // Apply rate limiting
        $key = 'login-attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 900); // 15 minute window

        // Validate CAPTCHA (simplified version)
        if (!$this->validateCaptcha($credentials['captcha_token'])) {
            return response()->json([
                'message' => 'CAPTCHA validation failed. Please try again.'
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            return response()->json([
                'message' => 'Account is temporarily locked. Please try again later.',
                'locked_until' => $user->locked_until
            ], 423);
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            $user->incrementLoginAttempts();
            
            $remainingAttempts = 5 - $user->login_attempts;
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'remaining_attempts' => max(0, $remainingAttempts)
            ], 401);
        }

        // Successful login - reset attempts
        $user->resetLoginAttempts();
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->only(['id', 'name', 'email', 'is_admin', 'is_instructor', 'role']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Check if email exists in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        return response()->json([
            'exists' => $user !== null,
            'message' => $user ? 'Email exists in our system' : 'Email not found in our system'
        ]);
    }

    /**
     * Send password reset link
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Generate a reset token (simplified version - in production, use Laravel's Password facade)
        $resetToken = Str::random(60);
        $user->reset_token = $resetToken;
        $user->reset_token_expires_at = now()->addHours(1);
        $user->save();

        // For now, just return success (in production, send email)
        return response()->json([
            'message' => 'Password reset link sent to your email',
            'reset_token' => $resetToken // Only for development, remove in production
        ]);
    }

    /**
     * Secure password reset with rate limiting
     *
     * @param  SecurePasswordResetRequest  $request
     * @return JsonResponse
     */
    public function secureForgotPassword(SecurePasswordResetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Email not found in our system'
            ], 404);
        }

        // Check if user has exceeded password reset attempts
        if ($user->hasExceededPasswordResetAttempts()) {
            return response()->json([
                'message' => 'Too many password reset attempts. Please try again later.',
                'retry_after' => 60 // 1 hour
            ], 429);
        }

        // Validate CAPTCHA
        if (!$this->validateCaptcha($validated['captcha_token'])) {
            $user->incrementPasswordResetAttempts();
            return response()->json([
                'message' => 'CAPTCHA validation failed. Please try again.'
            ], 422);
        }

        // Generate a reset token
        $resetToken = Str::random(60);
        $user->reset_token = $resetToken;
        $user->reset_token_expires_at = now()->addHours(1);
        $user->resetPasswordResetAttempts(); // Reset counter on successful request
        $user->save();

        // For now, just return success (in production, send email)
        return response()->json([
            'message' => 'Password reset link sent to your email',
            'reset_token' => $resetToken // Only for development, remove in production
        ]);
    }

    /**
     * Reset password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::where('email', $request->email)
                   ->where('reset_token', $request->token)
                   ->where('reset_token_expires_at', '>', now())
                   ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid or expired reset token'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->only([
                'id',
                'name',
                'email',
                'email_verified_at',
                'is_admin',
                'is_instructor',
                'role',
            ]),
        ]);
    }

    /**
     * Validate CAPTCHA token (simplified version)
     *
     * @param  string  $token
     * @return bool
     */
    private function validateCaptcha(string $token): bool
    {
        // Simplified CAPTCHA validation for development
        // In production, integrate with reCAPTCHA or similar service
        return !empty($token) && strlen($token) > 10;
    }
}
