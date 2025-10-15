<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'currency',
        'balance_available',
        'balance_reserved',
        'balance_total',
        'wallet_type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance_available' => 'decimal:8',
        'balance_reserved' => 'decimal:8',
        'balance_total' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<string>
     */
    protected $appends = [
        'formatted_balance',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wallet addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(WalletAddress::class);
    }

    /**
     * Get the wallet transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the active address for this wallet.
     */
    public function getActiveAddress(): ?WalletAddress
    {
        return $this->addresses()
            ->where('is_active', true)
            ->first();
    }

    /**
     * Generate a new address for this wallet.
     */
    public function generateAddress(string $network = 'mainnet'): WalletAddress
    {
        // In a real implementation, this would call a wallet service
        $address = 'addr_' . uniqid() . '_' . strtolower($this->currency);
        
        return $this->addresses()->create([
            'address' => $address,
            'network' => $network,
            'is_active' => true,
        ]);
    }

    /**
     * Add balance to the wallet.
     */
    public function addBalance(float $amount, string $type = 'available'): bool
    {
        return DB::transaction(function () use ($amount, $type) {
            $this->refresh();
            
            if ($type === 'available') {
                $this->balance_available += $amount;
            } elseif ($type === 'reserved') {
                $this->balance_reserved += $amount;
            }
            
            $this->balance_total = $this->balance_available + $this->balance_reserved;
            $this->save();
            
            return true;
        });
    }

    /**
     * Subtract balance from the wallet.
     */
    public function subtractBalance(float $amount, string $type = 'available'): bool
    {
        return DB::transaction(function () use ($amount, $type) {
            $this->refresh();
            
            if ($type === 'available') {
                if ($this->balance_available < $amount) {
                    return false; // Insufficient balance
                }
                $this->balance_available -= $amount;
            } elseif ($type === 'reserved') {
                if ($this->balance_reserved < $amount) {
                    return false; // Insufficient reserved balance
                }
                $this->balance_reserved -= $amount;
            }
            
            $this->balance_total = $this->balance_available + $this->balance_reserved;
            $this->save();
            
            return true;
        });
    }

    /**
     * Move balance from available to reserved.
     */
    public function reserveBalance(float $amount): bool
    {
        return DB::transaction(function () use ($amount) {
            $this->refresh();
            
            if ($this->balance_available < $amount) {
                return false; // Insufficient balance
            }
            
            $this->balance_available -= $amount;
            $this->balance_reserved += $amount;
            $this->balance_total = $this->balance_available + $this->balance_reserved;
            $this->save();
            
            return true;
        });
    }

    /**
     * Move balance from reserved to available.
     */
    public function releaseBalance(float $amount): bool
    {
        return DB::transaction(function () use ($amount) {
            $this->refresh();
            
            if ($this->balance_reserved < $amount) {
                return false; // Insufficient reserved balance
            }
            
            $this->balance_reserved -= $amount;
            $this->balance_available += $amount;
            $this->balance_total = $this->balance_available + $this->balance_reserved;
            $this->save();
            
            return true;
        });
    }

    /**
     * Check if wallet has sufficient balance.
     */
    public function hasBalance(float $amount, string $type = 'available'): bool
    {
        if ($type === 'available') {
            return $this->balance_available >= $amount;
        } elseif ($type === 'reserved') {
            return $this->balance_reserved >= $amount;
        }
        
        return $this->balance_total >= $amount;
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance_total, 8, '.', '');
    }
}