<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Create a new request
     */
    public static function createRequest($userId, $title, $description, $type = 'course')
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'status' => 'pending',
        ]);
    }
}
