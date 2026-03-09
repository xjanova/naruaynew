<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget('settings');
    }

    public static function getFloat(string $key, float $default = 0): float
    {
        return (float) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default);
        return in_array(strtolower((string) $value), ['yes', 'true', '1', 'on'], true);
    }
}
