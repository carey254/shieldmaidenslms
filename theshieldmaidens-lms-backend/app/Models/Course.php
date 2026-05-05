<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'category',
        'difficulty_level',
        'duration',
        'duration_hours',
        'status',
        'visibility',
        'modules_count',
        'thumbnail',
        'price',
        'max_students',
        'start_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
        'max_students' => 'integer',
        'modules_count' => 'integer',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Published and visible on student catalog */
    public function scopeCatalogVisible($query)
    {
        return $query->where('status', 'published')
            ->whereIn('visibility', ['public', 'scholarship']);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function getEnrolledCountAttribute()
    {
        return $this->enrollments()->count();
    }

    public function getCompletedCountAttribute()
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->enrolled_count === 0) {
            return 0;
        }
        
        return ($this->completed_count / $this->enrolled_count) * 100;
    }
}
