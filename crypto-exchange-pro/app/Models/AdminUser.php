<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the KYC requests reviewed by this admin.
     */
    public function reviewedKycRequests(): HasMany
    {
        return $this->hasMany(KycRequest::class, 'reviewed_by');
    }

    /**
     * Check if admin has permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (in_array('*', $this->permissions ?? [])) {
            return true;
        }
        
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}