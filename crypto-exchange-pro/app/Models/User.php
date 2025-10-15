<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'phone',
        'role',
        'status',
        'kyc_status',
        'two_factor_enabled',
        'two_factor_secret',
        'backup_codes',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'backup_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'backup_codes' => 'array',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's wallets.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get the user's transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user's orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's KYC requests.
     */
    public function kycRequests(): HasMany
    {
        return $this->hasMany(KycRequest::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user is locked due to failed login attempts.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if user has completed KYC.
     */
    public function hasCompletedKyc(): bool
    {
        return $this->kyc_status === 'approved';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get wallet by currency.
     */
    public function getWallet(string $currency, string $type = 'spot'): ?Wallet
    {
        return $this->wallets()
            ->where('currency', $currency)
            ->where('wallet_type', $type)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get total balance for a currency across all wallet types.
     */
    public function getTotalBalance(string $currency): float
    {
        return $this->wallets()
            ->where('currency', $currency)
            ->where('is_active', true)
            ->sum('balance_total');
    }

    /**
     * Check if user can trade.
     */
    public function canTrade(): bool
    {
        return $this->isActive() && 
               $this->hasCompletedKyc() && 
               !$this->isLocked();
    }
}