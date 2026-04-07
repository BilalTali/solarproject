<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string|null $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['group', 'key', 'value', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            $userId = $setting->user_id ?? 'global';
            Cache::forget("settings.{$userId}." . $setting->key);
        });

        static::deleted(function ($setting) {
            $userId = $setting->user_id ?? 'global';
            Cache::forget("settings.{$userId}." . $setting->key);
        });
    }

    /**
     * Get a setting value, scoped to a user if provided.
     */
    public static function getValue(string $key, $default = null, $userId = null): mixed
    {
        // If no userId provided, attempt to use authenticated user
        if ($userId === null && auth()->check()) {
            $userId = auth()->id();
        }

        $userIdStr = $userId ?? 'global';
        
        $value = Cache::rememberForever("settings.{$userIdStr}." . $key, function () use ($key, $userId) {
            $setting = static::query()
                ->where('key', $key)
                ->where('user_id', $userId)
                ->first();
            
            // Fallback to global setting if user-specific one doesn't exist AND context is a user
            if (!$setting && $userId !== null) {
                $setting = static::query()
                    ->where('key', $key)
                    ->whereNull('user_id')
                    ->first();
            }

            return $setting ? (string)$setting->value : null;
        });

        return $value !== null ? $value : $default;
    }
}
