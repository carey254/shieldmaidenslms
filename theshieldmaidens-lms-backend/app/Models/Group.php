<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'facilitator_id',
        'course_id',
        'name',
        'description',
        'code',
        'max_members',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_members' => 'integer',
    ];

    public function facilitator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facilitator_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function isFull(): bool
    {
        return $this->member_count >= $this->max_members;
    }
}
