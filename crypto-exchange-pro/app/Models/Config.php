<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Scope to get public configurations.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get configuration value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }
        
        return self::castValue($config->value, $config->type);
    }

    /**
     * Set configuration value by key.
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null, bool $isPublic = false): bool
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        ) !== null;
    }

    /**
     * Cast value to appropriate type.
     */
    private static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'decimal':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}