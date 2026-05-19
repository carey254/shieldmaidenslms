<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'content',
        'category',
        'views',
        'replies_count',
        'is_pinned',
        'is_locked',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'views' => 'integer',
        'replies_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class)->orderBy('created_at');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
