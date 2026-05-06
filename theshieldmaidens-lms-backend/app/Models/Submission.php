<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'attachments',
        'score',
        'feedback',
        'status',
        'attempts',
        'submitted_at',
        'graded_at',
        'time_spent_minutes',
        'assignment_id',
        'user_id',
        'graded_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'attempts' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'time_spent_minutes' => 'decimal:2',
        'attachments' => 'array',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeGraded($query)
    {
        return $query->whereNotNull('score');
    }

    public function scopeUngraded($query)
    {
        return $query->whereNull('score');
    }

    public function scopeLate($query)
    {
        return $query->whereHas('assignment', function ($q) {
            $q->where('due_date', '<', 'submitted_at');
        });
    }

    public function isGraded(): bool
    {
        return $this->score !== null;
    }

    public function isPassed(): bool
    {
        if (!$this->isGraded()) {
            return false;
        }

        return $this->score >= $this->assignment->passing_score;
    }

    public function isLate(): bool
    {
        return $this->assignment->due_date && 
               $this->submitted_at->gt($this->assignment->due_date);
    }

    public function getGradePercentageAttribute(): float
    {
        if (!$this->isGraded() || $this->assignment->max_score == 0) {
            return 0;
        }

        return ($this->score / $this->assignment->max_score) * 100;
    }
}
