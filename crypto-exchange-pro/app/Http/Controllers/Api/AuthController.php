<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\VerifyEmailRequest;
use App\Http\Requests\Api\TwoFactorRequest;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Models\AuditLog;
use App\Services\EmailService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $emailService;
    protected $twoFactorService;

    public function __construct(EmailService $emailService, TwoFactorService $twoFactorService)
    {
        $this->emailService = $emailService;
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => 'pending',
                'kyc_status' => 'none',
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'country' => $request->country,
            ]);

            // Create default wallets
            $this->createDefaultWallets($user);

            // Send email verification
            $this->emailService->sendVerificationEmail($user);

            // Log the registration
            AuditLog::createLog($user, 'user_registered', $user, [
                'email' => $user->email,
                'ip_address' => $request->ip(),
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please check your email for verification.',
                'data' => [
                    'user' => $user->only(['id', 'email', 'status', 'kyc_status']),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Check if user is locked
            $user = User::where('email', $request->email)->first();
            
            if ($user && $user->isLocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed login attempts.',
                ], 423);
            }

            // Attempt authentication
            if (!Auth::attempt($request->only('email', 'password'))) {
                // Increment failed login attempts
                if ($user) {
                    $user->increment('failed_login_attempts');
                    
                    if ($user->failed_login_attempts >= config('auth.max_login_attempts', 5)) {
                        $user->update([
                            'locked_until' => now()->addMinutes(config('auth.lockout_time', 15))
                        ]);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            $user = Auth::user();

            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not active. Please contact support.',
                ], 403);
            }

            // Reset failed login attempts
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Generate token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Log the login
            AuditLog::createLog($user, 'user_login', $user, [
                'ip_address' => $request->ip(),
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => $user->only(['id', 'email', 'status', 'kyc_status', 'two_factor_enabled']),
                    'token' => $token,
                    'requires_2fa' => $user->two_factor_enabled,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Log the logout
            AuditLog::createLog($user, 'user_logout', $user, [
                'ip_address' => $request->ip(),
            ], $request->ip(), $request->userAgent());

            // Revoke token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            if ($user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified.',
                ], 400);
            }

            // In a real implementation, you would verify the token
            // For now, we'll just mark as verified
            $user->update([
                'email_verified_at' => now(),
                'status' => 'active',
            ]);

            // Log the verification
            AuditLog::createLog($user, 'email_verified', $user, [
                'ip_address' => $request->ip(),
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email verification failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Enable two-factor authentication
     */
    public function enable2FA(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication is already enabled.',
                ], 400);
            }

            $secret = $this->twoFactorService->generateSecret();
            $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user->email, $secret);

            // Store secret temporarily (user needs to verify before enabling)
            $user->update(['two_factor_secret' => $secret]);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication setup initiated.',
                'data' => [
                    'secret' => $secret,
                    'qr_code_url' => $qrCodeUrl,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable two-factor authentication.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify two-factor authentication setup
     */
    public function verify2FA(TwoFactorRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->two_factor_secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication setup not initiated.',
                ], 400);
            }

            if (!$this->twoFactorService->verifyCode($user->two_factor_secret, $request->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code.',
                ], 400);
            }

            // Generate backup codes
            $backupCodes = $this->twoFactorService->generateBackupCodes();

            // Enable 2FA
            $user->update([
                'two_factor_enabled' => true,
                'backup_codes' => $backupCodes,
            ]);

            // Log the 2FA enablement
            AuditLog::createLog($user, '2fa_enabled', $user, [
                'ip_address' => $request->ip(),
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication enabled successfully.',
                'data' => [
                    'backup_codes' => $backupCodes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify two-factor authentication.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('profile');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user->only([
                        'id', 'email', 'phone', 'status', 'kyc_status', 
                        'two_factor_enabled', 'last_login_at', 'created_at'
                    ]),
                    'profile' => $user->profile ? $user->profile->only([
                        'first_name', 'last_name', 'country', 'date_of_birth'
                    ]) : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user information.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create default wallets for new user
     */
    private function createDefaultWallets(User $user): void
    {
        $currencies = explode(',', config('app.supported_currencies', 'USD,EUR'));
        $cryptos = explode(',', config('app.supported_cryptos', 'BTC,ETH,USDT'));

        // Create fiat wallets
        foreach ($currencies as $currency) {
            Wallet::create([
                'user_id' => $user->id,
                'currency' => trim($currency),
                'wallet_type' => 'spot',
                'balance_available' => 0,
                'balance_reserved' => 0,
                'balance_total' => 0,
            ]);
        }

        // Create crypto wallets
        foreach ($cryptos as $crypto) {
            Wallet::create([
                'user_id' => $user->id,
                'currency' => trim($crypto),
                'wallet_type' => 'spot',
                'balance_available' => 0,
                'balance_reserved' => 0,
                'balance_total' => 0,
            ]);
        }
    }
}