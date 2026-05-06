<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'type',
        'max_score',
        'passing_score',
        'time_limit_minutes',
        'attempts_allowed',
        'available_from',
        'due_date',
        'available_until',
        'is_published',
        'auto_grade',
        'submission_requirements',
        'course_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2',
        'time_limit_minutes' => 'integer',
        'attempts_allowed' => 'integer',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'available_until' => 'datetime',
        'is_published' => 'boolean',
        'auto_grade' => 'boolean',
        'submission_requirements' => 'array',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('available_from')
              ->orWhere('available_from', '<=', now());
        })
        ->where(function ($q) {
            $q->whereNull('available_until')
              ->orWhere('available_until', '>=', now());
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getSubmissionCountAttribute()
    {
        return $this->submissions()->count();
    }

    public function getGradedCountAttribute()
    {
        return $this->submissions()->whereNotNull('score')->count();
    }

    public function getAverageScoreAttribute()
    {
        return $this->submissions()
            ->whereNotNull('score')
            ->avg('score');
    }

    public function isCurrentlyAvailable(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        if ($this->available_until && $this->available_until->isPast()) {
            return false;
        }

        return true;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
}
