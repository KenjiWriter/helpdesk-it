<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $settings = Cache::rememberForever('app_settings', function () {
                return self::pluck('value', 'key')->toArray();
            });

            return $settings[$key] ?? $default;
        } catch (\Exception $e) {
            // Fallback during migrations or if table doesn't exist
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('app_settings');
    }
}
