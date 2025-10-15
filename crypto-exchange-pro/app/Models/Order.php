<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'pair_id',
        'side',
        'type',
        'price',
        'stop_price',
        'amount',
        'filled',
        'remaining',
        'status',
        'time_in_force',
        'expires_at',
        'filled_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:8',
        'stop_price' => 'decimal:8',
        'amount' => 'decimal:8',
        'filled' => 'decimal:8',
        'remaining' => 'decimal:8',
        'expires_at' => 'datetime',
        'filled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<string>
     */
    protected $appends = [
        'formatted_price',
        'formatted_amount',
        'formatted_filled',
        'formatted_remaining',
        'formatted_total',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trading pair for this order.
     */
    public function tradingPair(): BelongsTo
    {
        return $this->belongsTo(TradingPair::class, 'pair_id');
    }

    /**
     * Get the trades for this order.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buy_order_id')
            ->orWhere('sell_order_id', $this->id);
    }

    /**
     * Scope to get orders by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get orders by trading pair.
     */
    public function scopeByPair($query, int $pairId)
    {
        return $query->where('pair_id', $pairId);
    }

    /**
     * Scope to get orders by side.
     */
    public function scopeBySide($query, string $side)
    {
        return $query->where('side', $side);
    }

    /**
     * Scope to get orders by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get open orders.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'partially_filled']);
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
     * Check if order is open.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'partially_filled']);
    }

    /**
     * Check if order is filled.
     */
    public function isFilled(): bool
    {
        return $this->status === 'filled';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the total value of the order.
     */
    public function getTotalValue(): float
    {
        return (float) $this->amount * (float) $this->price;
    }

    /**
     * Get the filled value of the order.
     */
    public function getFilledValue(): float
    {
        return (float) $this->filled * (float) $this->price;
    }

    /**
     * Get the remaining value of the order.
     */
    public function getRemainingValue(): float
    {
        return (float) $this->remaining * (float) $this->price;
    }

    /**
     * Fill the order partially.
     */
    public function fillPartially(float $amount, float $price): bool
    {
        return DB::transaction(function () use ($amount, $price) {
            $this->refresh();
            
            $fillAmount = min($amount, $this->remaining);
            $this->filled += $fillAmount;
            $this->remaining -= $fillAmount;
            
            if ($this->remaining <= 0) {
                $this->status = 'filled';
                $this->filled_at = now();
            } else {
                $this->status = 'partially_filled';
            }
            
            $this->save();
            
            return true;
        });
    }

    /**
     * Fill the order completely.
     */
    public function fillCompletely(): bool
    {
        return DB::transaction(function () {
            $this->refresh();
            
            $this->filled = $this->amount;
            $this->remaining = 0;
            $this->status = 'filled';
            $this->filled_at = now();
            $this->save();
            
            return true;
        });
    }

    /**
     * Cancel the order.
     */
    public function cancel(): bool
    {
        return DB::transaction(function () {
            $this->refresh();
            
            if (!$this->isOpen()) {
                return false; // Cannot cancel non-open order
            }
            
            $this->status = 'cancelled';
            $this->cancelled_at = now();
            $this->save();
            
            return true;
        });
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? number_format($this->price, 8, '.', '') : '0.00000000';
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 8, '.', '');
    }

    /**
     * Get formatted filled.
     */
    public function getFormattedFilledAttribute(): string
    {
        return number_format($this->filled, 8, '.', '');
    }

    /**
     * Get formatted remaining.
     */
    public function getFormattedRemainingAttribute(): string
    {
        return number_format($this->remaining, 8, '.', '');
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->getTotalValue(), 8, '.', '');
    }
}