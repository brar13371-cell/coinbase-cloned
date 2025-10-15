<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pair_id',
        'price',
        'volume_24h',
        'change_24h',
        'high_24h',
        'low_24h',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:8',
        'volume_24h' => 'decimal:8',
        'change_24h' => 'decimal:4',
        'high_24h' => 'decimal:8',
        'low_24h' => 'decimal:8',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the trading pair for this market data.
     */
    public function tradingPair(): BelongsTo
    {
        return $this->belongsTo(TradingPair::class, 'pair_id');
    }

    /**
     * Scope to get market data by trading pair.
     */
    public function scopeByPair($query, int $pairId)
    {
        return $query->where('pair_id', $pairId);
    }

    /**
     * Scope to get latest market data.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('timestamp', 'desc');
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 8, '.', '');
    }

    /**
     * Get formatted volume.
     */
    public function getFormattedVolumeAttribute(): string
    {
        return number_format($this->volume_24h, 8, '.', '');
    }

    /**
     * Get formatted change percentage.
     */
    public function getFormattedChangeAttribute(): string
    {
        return number_format($this->change_24h, 2, '.', '') . '%';
    }
}