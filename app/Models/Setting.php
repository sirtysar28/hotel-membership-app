<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Cache per-request: hindari query berulang saat satu halaman butuh banyak setting. */
    protected static array $cache = [];

    public static function get(string $key, $default = null)
    {
        if (! array_key_exists($key, static::$cache)) {
            static::$cache[$key] = static::where('key', $key)->first()?->value;
        }

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        static::$cache[$key] = $value;
    }
}
