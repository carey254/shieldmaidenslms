<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash',
        'auth_provider',
        'is_admin',
        'role',
        'status',
        'department',
        'last_login_at',
        'reset_token',
        'reset_token_expires_at',
        'login_attempts',
        'last_login_attempt_at',
        'locked_until',
        'password_reset_attempts',
        'last_password_reset_attempt_at',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array<int, string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'is_admin',
        'is_instructor',
        'role',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the admin status of the user.
     */
    public function getIsAdminAttribute(): bool
    {
        return (bool) $this->attributes['is_admin'] ?? false;
    }

    /**
     * Get the instructor status of the user.
     */
    public function getIsInstructorAttribute(): bool
    {
        return $this->role === 'instructor';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'last_login_attempt_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_password_reset_attempt_at' => 'datetime',
        ];
    }

    /**
     * Get the activities for the user.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the requests for the user.
     */
    public function requests()
    {
        return $this->hasMany(UserRequest::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin' || $this->is_admin;
    }

    /**
     * Check if user is an instructor.
     */
    public function isInstructor()
    {
        return $this->role === 'instructor';
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Log user activity
     */
    public function logActivity($title, $description, $type = 'system', $metadata = [])
    {
        return Activity::log($this->id, $title, $description, $type, $metadata);
    }

    /**
     * Create a request
     */
    public function createRequest($title, $description, $type = 'course')
    {
        return UserRequest::createRequest($this->id, $title, $description, $type);
    }

    /**
     * Check if user account is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until > now();
    }

    /**
     * Lock user account for specified minutes
     */
    public function lockAccount(int $minutes = 15): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Unlock user account
     */
    public function unlockAccount(): void
    {
        $this->locked_until = null;
        $this->login_attempts = 0;
        $this->save();
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(): void
    {
        $this->login_attempts++;
        $this->last_login_attempt_at = now();
        
        // Lock account after 5 failed attempts
        if ($this->login_attempts >= 5) {
            $this->lockAccount(15); // Lock for 15 minutes
        }
        
        $this->save();
    }

    /**
     * Reset login attempts on successful login
     */
    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->last_login_attempt_at = null;
        $this->save();
    }

    /**
     * Check if user has exceeded password reset attempts
     */
    public function hasExceededPasswordResetAttempts(): bool
    {
        // Reset counter if last attempt was more than 1 hour ago
        if ($this->last_password_reset_attempt_at && $this->last_password_reset_attempt_at->lt(now()->subHour())) {
            $this->password_reset_attempts = 0;
            $this->save();
        }
        
        return $this->password_reset_attempts >= 5;
    }

    /**
     * Increment password reset attempts
     */
    public function incrementPasswordResetAttempts(): void
    {
        $this->password_reset_attempts++;
        $this->last_password_reset_attempt_at = now();
        $this->save();
    }

    /**
     * Reset password reset attempts
     */
    public function resetPasswordResetAttempts(): void
    {
        $this->password_reset_attempts = 0;
        $this->last_password_reset_attempt_at = null;
        $this->save();
    }
}
