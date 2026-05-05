<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log user activity
     */
    public static function log($userId, $title, $description, $type = 'system', $metadata = [])
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }
}
