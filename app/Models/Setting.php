<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null, $group = 'general')
    {
        $setting = static::where('key', $key)->where('group', $group)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $group = 'general', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key, 'group' => $group],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Get all settings for a group
     */
    public static function getGroup($group)
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    /**
     * Set multiple settings at once
     */
    public static function setMultiple($settings, $group = 'general')
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value, $group);
        }
    }
}