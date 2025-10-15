<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider_id',
        'provider_request_id',
        'status',
        'document_type',
        'document_front',
        'document_back',
        'selfie',
        'provider_response',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider_response' => 'array',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the KYC request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider that processed the KYC request.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the admin user who reviewed the KYC request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by');
    }

    /**
     * Scope to get KYC requests by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending KYC requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved KYC requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected KYC requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if KYC request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if KYC request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if KYC request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if KYC request is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Approve the KYC request.
     */
    public function approve(int $reviewedBy, string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Reject the KYC request.
     */
    public function reject(int $reviewedBy, string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Get the document URL.
     */
    public function getDocumentUrl(string $type): ?string
    {
        $field = $type === 'front' ? 'document_front' : 'document_back';
        $path = $this->$field;
        
        if (!$path) {
            return null;
        }
        
        return asset('storage/' . $path);
    }

    /**
     * Get the selfie URL.
     */
    public function getSelfieUrl(): ?string
    {
        if (!$this->selfie) {
            return null;
        }
        
        return asset('storage/' . $this->selfie);
    }
}