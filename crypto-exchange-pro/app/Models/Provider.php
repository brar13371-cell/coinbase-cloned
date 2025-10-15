<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'is_enabled',
        'priority',
        'config',
        'api_endpoint',
        'api_key',
        'api_secret',
        'webhook_secret',
        'last_health_check',
        'health_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
        'last_health_check' => 'datetime',
    ];

    /**
     * Get the transactions for this provider.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the KYC requests for this provider.
     */
    public function kycRequests(): HasMany
    {
        return $this->hasMany(KycRequest::class);
    }

    /**
     * Scope to get providers by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get enabled providers.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to get providers by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Check if provider is healthy.
     */
    public function isHealthy(): bool
    {
        return $this->health_status === 'healthy';
    }

    /**
     * Check if provider is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Update health status.
     */
    public function updateHealthStatus(string $status): bool
    {
        return $this->update([
            'health_status' => $status,
            'last_health_check' => now(),
        ]);
    }

    /**
     * Get configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set configuration value.
     */
    public function setConfig(string $key, $value): bool
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        
        return $this->update(['config' => $config]);
    }
}