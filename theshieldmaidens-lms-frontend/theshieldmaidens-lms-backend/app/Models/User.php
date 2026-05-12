<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'bio',
        'avatar',
        'status',
        'email_verified_at',
        'last_login_at'
    ];

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
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    /**
     * User roles
     */
    const ROLES = [
        'student' => 'student',
        'instructor' => 'instructor', 
        'admin' => 'admin',
        'super_admin' => 'super_admin'
    ];

    /**
     * User statuses
     */
    const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
        'banned' => 'banned'
    ];

    /**
     * Check if user is a student
     */
    public function isStudent()
    {
        return $this->role === self::ROLES['student'];
    }

    /**
     * Check if user is an instructor
     */
    public function isInstructor()
    {
        return $this->role === self::ROLES['instructor'];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin()
    {
        return $this->role === self::ROLES['admin'] || $this->isSuperAdmin();
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === self::ROLES['super_admin'];
    }

    /**
     * Get the dashboard URL for the user
     */
    public function getDashboardUrlAttribute()
    {
        $urls = [
            self::ROLES['student'] => '/student/dashboard',
            self::ROLES['instructor'] => '/instructor/dashboard',
            self::ROLES['admin'] => '/admin/dashboard',
            self::ROLES['super_admin'] => '/admin/dashboard'
        ];

        return $urls[$this->role] ?? '/dashboard';
    }
}
