<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buy_order_id',
        'sell_order_id',
        'pair_id',
        'price',
        'amount',
        'buyer_fee',
        'seller_fee',
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
        'buyer_fee' => 'decimal:8',
        'seller_fee' => 'decimal:8',
        'timestamp' => 'datetime',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<string>
     */
    protected $appends = [
        'formatted_price',
        'formatted_amount',
        'formatted_buyer_fee',
        'formatted_seller_fee',
        'formatted_total',
    ];

    /**
     * Get the buy order for this trade.
     */
    public function buyOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'buy_order_id');
    }

    /**
     * Get the sell order for this trade.
     */
    public function sellOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sell_order_id');
    }

    /**
     * Get the trading pair for this trade.
     */
    public function tradingPair(): BelongsTo
    {
        return $this->belongsTo(TradingPair::class, 'pair_id');
    }

    /**
     * Scope to get trades by trading pair.
     */
    public function scopeByPair($query, int $pairId)
    {
        return $query->where('pair_id', $pairId);
    }

    /**
     * Scope to get trades by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->whereHas('buyOrder', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orWhereHas('sellOrder', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get recent trades.
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('timestamp', 'desc')->limit($limit);
    }

    /**
     * Get the total value of the trade.
     */
    public function getTotalValue(): float
    {
        return (float) $this->amount * (float) $this->price;
    }

    /**
     * Get the total fees for the trade.
     */
    public function getTotalFees(): float
    {
        return (float) $this->buyer_fee + (float) $this->seller_fee;
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

    /**
     * Get formatted buyer fee.
     */
    public function getFormattedBuyerFeeAttribute(): string
    {
        return number_format($this->buyer_fee, 8, '.', '');
    }

    /**
     * Get formatted seller fee.
     */
    public function getFormattedSellerFeeAttribute(): string
    {
        return number_format($this->seller_fee, 8, '.', '');
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->getTotalValue(), 8, '.', '');
    }
}