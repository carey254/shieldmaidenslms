<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'audience',
        'is_active',
        'is_pinned',
        'starts_at',
        'expires_at',
        'views_count',
        'course_id',
        'category',
        'priority',
        'is_featured',
        'application_link',
        'show_on_home',
        'show_in_portals',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_home' => 'boolean',
        'show_in_portals' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'views_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeForAudience($query, $audience)
    {
        return $query->where('audience', $audience);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_pinned', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Shape expected by the Vue student/home announcement UIs.
     */
    public function toPortalArray(): array
    {
        $priority = $this->priority ?: ($this->type === 'urgent' ? 'urgent' : 'medium');
        $category = $this->category ?: 'general';

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $category,
            'priority' => $priority,
            'is_featured' => (bool) $this->is_featured,
            'is_pinned' => (bool) $this->is_pinned,
            'application_link' => $this->application_link,
            'expiry_date' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'audience' => $this->audience,
            'type' => $this->type,
        ];
    }
}
