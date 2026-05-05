<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Allowed redirect URL must start with this (SPA origin).
     */
    private function validatedReturnUrl(?string $url): string
    {
        $default = config('app.frontend_url') . '/login';

        if ($url === null || $url === '') {
            return $default;
        }

        $allowed = config('app.frontend_url');
        if ($allowed !== '' && str_starts_with($url, $allowed)) {
            return $url;
        }

        return $default;
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(Request $request)
    {
        $returnUrl = $this->validatedReturnUrl($request->query('return_url'));
        session(['oauth_return_url' => $returnUrl]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $this->logLoginAttempt($googleUser->getEmail(), true, $request->ip(), $request->userAgent());

            $user = $this->findOrCreateUser($googleUser);

            Auth::login($user, true);

            $plainToken = $user->createToken('google_oauth')->plainTextToken;

            $returnUrl = session()->pull('oauth_return_url', $this->validatedReturnUrl(null));
            $sep = str_contains($returnUrl, '?') ? '&' : '?';

            return redirect()->away(
                $returnUrl . $sep . 'oauth_success=1&access_token=' . urlencode($plainToken)
            );
        } catch (\Exception $e) {
            $this->logLoginAttempt('google_user', false, $request->ip(), $request->userAgent());

            $fallback = $this->validatedReturnUrl(null);
            $sep = str_contains($fallback, '?') ? '&' : '?';

            return redirect()->away($fallback . $sep . 'oauth_error=1');
        }
    }

    /**
     * Find existing user or create new one from Google data.
     */
    private function findOrCreateUser($googleUser): User
    {
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            $updates = ['auth_provider' => 'google'];
            if ($existingUser->name === null || $existingUser->name === '') {
                $updates['name'] = $googleUser->getName()
                    ?: strtok((string) $googleUser->getEmail(), '@');
            }
            $existingUser->update($updates);

            return $existingUser->fresh();
        }

        return User::create([
            'name' => $googleUser->getName() ?: strtok((string) $googleUser->getEmail(), '@'),
            'email' => $googleUser->getEmail(),
            'role' => 'student',
            'status' => 'active',
            'auth_provider' => 'google',
            'password_hash' => null,
            'password' => null,
        ]);
    }

    /**
     * Log login attempt for security auditing.
     */
    private function logLoginAttempt($email, $success, $ipAddress, $userAgent)
    {
        $userId = null;

        if ($success && $email !== 'google_user') {
            $user = User::where('email', $email)->first();
            $userId = $user ? $user->id : null;
        }

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
