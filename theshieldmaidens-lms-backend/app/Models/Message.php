<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'subject',
        'content',
        'recipients',
        'priority',
        'opened_count',
        'reply_count',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Send a broadcast message
     */
    public static function sendBroadcast($senderId, $subject, $content, $recipients = 'all', $priority = 'medium')
    {
        return self::create([
            'sender_id' => $senderId,
            'subject' => $subject,
            'content' => $content,
            'recipients' => $recipients,
            'priority' => $priority,
            'opened_count' => 0,
            'reply_count' => 0,
        ]);
    }
}
