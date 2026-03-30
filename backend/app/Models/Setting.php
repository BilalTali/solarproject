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

    protected $fillable = ['group', 'key', 'value'];

    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget('settings.' . $setting->key);
        });

        static::deleted(function ($setting) {
            Cache::forget('settings.' . $setting->key);
        });
    }

    public static function getValue(string $key, $default = null): mixed
    {
        $value = Cache::rememberForever('settings.' . $key, function () use ($key) {
            $setting = static::query()->where('key', $key)->first();
            return $setting ? (string)$setting->value : null;
        });

        return $value !== null ? $value : $default;
    }
}
