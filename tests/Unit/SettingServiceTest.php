<?php

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can get setting value', function () {
    Setting::create(['key' => 'test_key', 'value' => 'test_value', 'group' => 'test']);
    Cache::forget('settings');

    expect(SettingService::get('test_key'))->toBe('test_value');
});

test('returns default when setting not found', function () {
    Cache::forget('settings');

    expect(SettingService::get('nonexistent', 'default'))->toBe('default');
});

test('returns null when setting not found and no default', function () {
    Cache::forget('settings');

    expect(SettingService::get('nonexistent'))->toBeNull();
});

test('can set setting value', function () {
    SettingService::set('new_key', 'new_value', 'test');

    expect(Setting::where('key', 'new_key')->first()->value)->toBe('new_value');
});

test('set updates existing setting', function () {
    Setting::create(['key' => 'existing_key', 'value' => 'old_value', 'group' => 'test']);

    SettingService::set('existing_key', 'updated_value', 'test');

    expect(Setting::where('key', 'existing_key')->first()->value)->toBe('updated_value');
    expect(Setting::where('key', 'existing_key')->count())->toBe(1);
});

test('set clears cache so new value is returned by get', function () {
    SettingService::set('cached_key', 'initial', 'test');

    expect(SettingService::get('cached_key'))->toBe('initial');

    SettingService::set('cached_key', 'updated', 'test');

    expect(SettingService::get('cached_key'))->toBe('updated');
});

test('getFloat returns float value', function () {
    Setting::create(['key' => 'float_key', 'value' => '12.5', 'group' => 'test']);
    Cache::forget('settings');

    expect(SettingService::getFloat('float_key'))->toBe(12.5);
});

test('getFloat returns default when not found', function () {
    Cache::forget('settings');

    expect(SettingService::getFloat('missing_float', 99.9))->toBe(99.9);
});

test('getBool returns true for truthy values', function () {
    Cache::forget('settings');

    foreach (['yes', 'true', '1', 'on'] as $truthyValue) {
        Setting::updateOrCreate(['key' => 'bool_key'], ['value' => $truthyValue, 'group' => 'test']);
        Cache::forget('settings');

        expect(SettingService::getBool('bool_key'))
            ->toBeTrue("Expected '{$truthyValue}' to be truthy");
    }
});

test('getBool returns false for falsy values', function () {
    Cache::forget('settings');

    foreach (['no', 'false', '0', 'off', 'random'] as $falsyValue) {
        Setting::updateOrCreate(['key' => 'bool_key'], ['value' => $falsyValue, 'group' => 'test']);
        Cache::forget('settings');

        expect(SettingService::getBool('bool_key'))
            ->toBeFalse("Expected '{$falsyValue}' to be falsy");
    }
});

test('getBool returns default when not found', function () {
    Cache::forget('settings');

    expect(SettingService::getBool('missing_bool', false))->toBeFalse();
    expect(SettingService::getBool('missing_bool', true))->toBeTrue();
});
