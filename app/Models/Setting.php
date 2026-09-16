<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Setting;
class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Tiny per-request cache. The layout reads settings on every page,
     * so without this each page load would repeat the same queries.
     * Statics reset automatically between requests — nothing to clean up.
     */
    protected static array $cache = [];

    /**
     * Setting::get('clinic_name', 'Fallback') — falls back gracefully,
     * so the app works correctly even before an admin fills in settings.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $value = static::where('key', $key)->value('value');

        return static::$cache[$key] = $value ?? $default;
    }

    /**
     * Setting::set('clinic_name', 'New Name') — upsert + refresh the cache.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        static::$cache[$key] = $value;
    }
}
