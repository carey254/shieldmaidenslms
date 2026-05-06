<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'updated_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get(string $key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'number' => is_numeric($setting->value) ? $setting->value + 0 : $default,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'general', ?int $userId = null): void
    {
        $setting = static::where('key', $key)->firstOrCreate(['key' => $key]);
        
        $setting->value = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
        
        $setting->type = $type;
        $setting->group = $group;
        $setting->updated_by = $userId;
        $setting->save();

        // Clear cache
        Cache::forget("system_setting_{$key}");
    }

    public static function getPublicSettings(): array
    {
        return Cache::remember('public_system_settings', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => match ($setting->type) {
                        'boolean' => (bool) $setting->value,
                        'number' => is_numeric($setting->value) ? $setting->value + 0 : $setting->value,
                        'json' => json_decode($setting->value, true),
                        default => $setting->value,
                    }];
                })
                ->toArray();
        });
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
