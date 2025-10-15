<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradingPair extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'base_currency',
        'quote_currency',
        'symbol',
        'is_active',
        'min_trade_amount',
        'max_trade_amount',
        'tick_size',
        'step_size',
        'maker_fee',
        'taker_fee',
        'price_precision',
        'quantity_precision',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'min_trade_amount' => 'decimal:8',
        'max_trade_amount' => 'decimal:8',
        'tick_size' => 'decimal:8',
        'step_size' => 'decimal:8',
        'maker_fee' => 'decimal:4',
        'taker_fee' => 'decimal:4',
    ];

    /**
     * Get the orders for this trading pair.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pair_id');
    }

    /**
     * Get the trades for this trading pair.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'pair_id');
    }

    /**
     * Get the market data for this trading pair.
     */
    public function marketData(): HasMany
    {
        return $this->hasMany(MarketData::class, 'pair_id');
    }

    /**
     * Get the order book for this trading pair.
     */
    public function orderBook(): HasMany
    {
        return $this->hasMany(OrderBook::class, 'pair_id');
    }

    /**
     * Scope to get active trading pairs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get trading pairs by base currency.
     */
    public function scopeByBaseCurrency($query, string $currency)
    {
        return $query->where('base_currency', $currency);
    }

    /**
     * Scope to get trading pairs by quote currency.
     */
    public function scopeByQuoteCurrency($query, string $currency)
    {
        return $query->where('quote_currency', $currency);
    }

    /**
     * Get the latest market data.
     */
    public function getLatestMarketData(): ?MarketData
    {
        return $this->marketData()
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    /**
     * Get the current price.
     */
    public function getCurrentPrice(): ?float
    {
        $marketData = $this->getLatestMarketData();
        return $marketData ? (float) $marketData->price : null;
    }

    /**
     * Get the 24h change percentage.
     */
    public function get24hChange(): ?float
    {
        $marketData = $this->getLatestMarketData();
        return $marketData ? (float) $marketData->change_24h : null;
    }

    /**
     * Get the 24h volume.
     */
    public function get24hVolume(): ?float
    {
        $marketData = $this->getLatestMarketData();
        return $marketData ? (float) $marketData->volume_24h : null;
    }

    /**
     * Get the 24h high.
     */
    public function get24hHigh(): ?float
    {
        $marketData = $this->getLatestMarketData();
        return $marketData ? (float) $marketData->high_24h : null;
    }

    /**
     * Get the 24h low.
     */
    public function get24hLow(): ?float
    {
        $marketData = $this->getLatestMarketData();
        return $marketData ? (float) $marketData->low_24h : null;
    }

    /**
     * Check if the trading pair is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Validate trade amount.
     */
    public function isValidTradeAmount(float $amount): bool
    {
        return $amount >= $this->min_trade_amount && $amount <= $this->max_trade_amount;
    }

    /**
     * Round price to tick size.
     */
    public function roundPrice(float $price): float
    {
        return round($price / $this->tick_size) * $this->tick_size;
    }

    /**
     * Round quantity to step size.
     */
    public function roundQuantity(float $quantity): float
    {
        return round($quantity / $this->step_size) * $this->step_size;
    }
}