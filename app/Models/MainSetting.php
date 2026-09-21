<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainSetting extends Model
{
    protected $table = 'main_setting';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'value',
    ];

    /**
     * Get a setting value by name with optional default.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        try {
            $setting = static::where('name', $name)->first();
            if ($setting && $setting->value !== null && $setting->value !== '') {
                return $setting->value;
            }
        } catch (\Throwable $e) {
            return $default;
        }
        return $default;
    }

    /**
     * Set a setting value by name.
     *
     * @param string $name
     * @param mixed $value
     * @return static|null
     */
    public static function set(string $name, $value)
    {
        try {
            return static::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }
}
