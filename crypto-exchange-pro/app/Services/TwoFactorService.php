<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get QR code URL for the secret
     */
    public function getQRCodeUrl(string $email, string $secret): string
    {
        $companyName = config('app.name', 'CryptoExchange Pro');
        $companyEmail = $email;
        
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $secret
        );
    }

    /**
     * Verify the provided code
     */
    public function verifyCode(string $secret, string $code): bool
    {
        try {
            return $this->google2fa->verifyKey($secret, $code);
        } catch (\Exception $e) {
            Log::error("2FA verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        return $codes;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(array $backupCodes, string $code): bool
    {
        $index = array_search($code, $backupCodes);
        if ($index !== false) {
            // Remove used backup code
            unset($backupCodes[$index]);
            return true;
        }
        return false;
    }

    /**
     * Get current TOTP code
     */
    public function getCurrentCode(string $secret): string
    {
        return $this->google2fa->getCurrentOtp($secret);
    }

    /**
     * Get remaining time for current code
     */
    public function getRemainingTime(): int
    {
        return $this->google2fa->getRemainingTime();
    }
}