<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderBook extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pair_id',
        'side',
        'price',
        'amount',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:8',
        'amount' => 'decimal:8',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the trading pair for this order book entry.
     */
    public function tradingPair(): BelongsTo
    {
        return $this->belongsTo(TradingPair::class, 'pair_id');
    }

    /**
     * Scope to get order book by trading pair.
     */
    public function scopeByPair($query, int $pairId)
    {
        return $query->where('pair_id', $pairId);
    }

    /**
     * Scope to get order book by side.
     */
    public function scopeBySide($query, string $side)
    {
        return $query->where('side', $side);
    }

    /**
     * Scope to get buy orders.
     */
    public function scopeBuy($query)
    {
        return $query->where('side', 'buy');
    }

    /**
     * Scope to get sell orders.
     */
    public function scopeSell($query)
    {
        return $query->where('side', 'sell');
    }

    /**
     * Scope to get latest order book.
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
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 8, '.', '');
    }
}