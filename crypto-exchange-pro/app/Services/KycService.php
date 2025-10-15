<?php

namespace App\Services;

use App\Models\KycRequest;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\Log;

class KycService
{
    /**
     * Create a new KYC request
     */
    public function createKycRequest(User $user, array $data): KycRequest
    {
        // Get the primary KYC provider
        $provider = Provider::where('type', 'kyc')
            ->where('is_enabled', true)
            ->orderBy('priority')
            ->first();

        if (!$provider) {
            throw new \Exception('No KYC provider available');
        }

        // Create KYC request
        $kycRequest = KycRequest::create([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'status' => 'pending',
            'document_type' => $data['document_type'],
            'document_front' => $data['document_front'],
            'document_back' => $data['document_back'],
            'selfie' => $data['selfie'],
            'expires_at' => now()->addDays(30), // KYC requests expire in 30 days
        ]);

        // Submit to KYC provider
        $this->submitToProvider($kycRequest, $provider);

        return $kycRequest;
    }

    /**
     * Submit KYC request to provider
     */
    private function submitToProvider(KycRequest $kycRequest, Provider $provider): void
    {
        try {
            // In a real implementation, you would call the provider's API
            // For now, we'll just log it
            Log::info("KYC request submitted to provider: {$provider->name}", [
                'kyc_request_id' => $kycRequest->id,
                'user_id' => $kycRequest->user_id,
                'provider_id' => $provider->id,
            ]);

            // Simulate provider response
            $providerResponse = [
                'request_id' => 'kyc_' . uniqid(),
                'status' => 'pending',
                'submitted_at' => now()->toISOString(),
            ];

            $kycRequest->update([
                'provider_request_id' => $providerResponse['request_id'],
                'provider_response' => $providerResponse,
                'status' => 'processing',
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to submit KYC request to provider: " . $e->getMessage());
            
            $kycRequest->update([
                'status' => 'failed',
                'provider_response' => [
                    'error' => $e->getMessage(),
                    'submitted_at' => now()->toISOString(),
                ],
            ]);
        }
    }

    /**
     * Process KYC provider webhook
     */
    public function processProviderWebhook(Provider $provider, array $data): void
    {
        try {
            $kycRequest = KycRequest::where('provider_id', $provider->id)
                ->where('provider_request_id', $data['request_id'])
                ->first();

            if (!$kycRequest) {
                Log::warning("KYC webhook received for unknown request: {$data['request_id']}");
                return;
            }

            // Update KYC request with provider response
            $kycRequest->update([
                'provider_response' => $data,
                'status' => $this->mapProviderStatus($data['status']),
            ]);

            // Update user KYC status
            $user = $kycRequest->user;
            $user->update([
                'kyc_status' => $this->mapProviderStatus($data['status']),
            ]);

            Log::info("KYC webhook processed: {$kycRequest->id}", [
                'status' => $data['status'],
                'user_id' => $user->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to process KYC webhook: " . $e->getMessage());
        }
    }

    /**
     * Map provider status to internal status
     */
    private function mapProviderStatus(string $providerStatus): string
    {
        switch (strtolower($providerStatus)) {
            case 'approved':
            case 'verified':
                return 'approved';
            case 'rejected':
            case 'declined':
                return 'rejected';
            case 'pending':
            case 'processing':
                return 'processing';
            case 'expired':
                return 'expired';
            default:
                return 'pending';
        }
    }

    /**
     * Approve KYC request manually
     */
    public function approveKycRequest(KycRequest $kycRequest, int $reviewedBy, string $notes = null): bool
    {
        try {
            $kycRequest->approve($reviewedBy, $notes);
            
            // Update user KYC status
            $user = $kycRequest->user;
            $user->update(['kyc_status' => 'approved']);

            Log::info("KYC request approved manually: {$kycRequest->id}", [
                'reviewed_by' => $reviewedBy,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to approve KYC request: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject KYC request manually
     */
    public function rejectKycRequest(KycRequest $kycRequest, int $reviewedBy, string $notes = null): bool
    {
        try {
            $kycRequest->reject($reviewedBy, $notes);
            
            // Update user KYC status
            $user = $kycRequest->user;
            $user->update(['kyc_status' => 'rejected']);

            Log::info("KYC request rejected manually: {$kycRequest->id}", [
                'reviewed_by' => $reviewedBy,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to reject KYC request: " . $e->getMessage());
            return false;
        }
    }
}