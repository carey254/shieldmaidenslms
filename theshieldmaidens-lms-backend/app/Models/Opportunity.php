<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'organization',
        'location',
        'remote_option',
        'deadline',
        'requirements',
        'benefits',
        'application_link',
        'contact_email',
        'visibility',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'remote_option' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function applications()
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('deadline', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('deadline', '>', now());
    }

    public function scopeVisibleTo($query, $userRole)
    {
        if ($userRole === 'admin') {
            return $query;
        }
        
        return $query->where('visibility', 'all')
                    ->orWhere('visibility', $userRole);
    }

    public function getDaysUntilDeadlineAttribute()
    {
        if ($this->deadline <= now()) {
            return 0;
        }
        
        return now()->diffInDays($this->deadline);
    }

    public function getIsExpiredAttribute()
    {
        return $this->deadline <= now();
    }

    public function getApplicationCountAttribute()
    {
        return $this->applications()->count();
    }
}
