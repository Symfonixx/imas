<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected static $settingsCache;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    /**
     * Get the value of a setting by key, with an optional default.
     */
    public static function get(string $key, ?string $default = null)
    {
        self::loadSettingsCache();

        return self::$settingsCache[$key]->value ?? $default ?? false;
    }

    /**
     * Fetch all settings from the database if not already cached.
     */
    protected static function loadSettingsCache()
    {
        if (is_null(self::$settingsCache)) {
            self::$settingsCache = self::all()->keyBy('key');
        }
    }

    /**
     * Set the value of a setting by key.
     */
    public static function set(string $key, ?string $value): bool
    {
        self::loadSettingsCache();

        // Null clears the setting so reads fall back to their defaults.
        if ($value === null) {
            if (isset(self::$settingsCache[$key])) {
                self::$settingsCache[$key]->delete();
                unset(self::$settingsCache[$key]);
            }

            return true;
        }

        if (! isset(self::$settingsCache[$key])) {
            $model = self::create(['key' => $key, 'value' => $value]);
            self::$settingsCache[$key] = $model;
        } else {
            $model = self::$settingsCache[$key];
            $model->update(['value' => $value]);
        }

        return true;
    }
}
