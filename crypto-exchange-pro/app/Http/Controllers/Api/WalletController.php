<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DepositRequest;
use App\Http\Requests\Api\WithdrawRequest;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get user wallets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $wallets = $user->wallets()
                ->where('is_active', true)
                ->with('addresses')
                ->get()
                ->map(function ($wallet) {
                    return [
                        'id' => $wallet->id,
                        'currency' => $wallet->currency,
                        'balance_available' => $wallet->balance_available,
                        'balance_reserved' => $wallet->balance_reserved,
                        'balance_total' => $wallet->balance_total,
                        'wallet_type' => $wallet->wallet_type,
                        'formatted_balance' => $wallet->formatted_balance,
                        'address' => $wallet->getActiveAddress()?->address,
                        'network' => $wallet->getActiveAddress()?->network,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'wallets' => $wallets,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallets.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get specific wallet
     */
    public function show(Request $request, int $walletId): JsonResponse
    {
        try {
            $user = $request->user();
            $wallet = $user->wallets()
                ->where('id', $walletId)
                ->where('is_active', true)
                ->with('addresses')
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'wallet' => [
                        'id' => $wallet->id,
                        'currency' => $wallet->currency,
                        'balance_available' => $wallet->balance_available,
                        'balance_reserved' => $wallet->balance_reserved,
                        'balance_total' => $wallet->balance_total,
                        'wallet_type' => $wallet->wallet_type,
                        'formatted_balance' => $wallet->formatted_balance,
                        'address' => $wallet->getActiveAddress()?->address,
                        'network' => $wallet->getActiveAddress()?->network,
                        'addresses' => $wallet->addresses->map(function ($address) {
                            return [
                                'id' => $address->id,
                                'address' => $address->address,
                                'network' => $address->network,
                                'tag' => $address->tag,
                                'is_active' => $address->is_active,
                            ];
                        }),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallet.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Generate deposit address
     */
    public function generateDepositAddress(Request $request, int $walletId): JsonResponse
    {
        try {
            $user = $request->user();
            $wallet = $user->wallets()
                ->where('id', $walletId)
                ->where('is_active', true)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found.',
                ], 404);
            }

            // Generate new address
            $address = $this->walletService->generateAddress($wallet, $request->input('network', 'mainnet'));

            // Log the address generation
            AuditLog::createLog($user, 'deposit_address_generated', $wallet, [
                'currency' => $wallet->currency,
                'address' => $address->address,
                'network' => $address->network,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Deposit address generated successfully.',
                'data' => [
                    'address' => [
                        'id' => $address->id,
                        'address' => $address->address,
                        'network' => $address->network,
                        'tag' => $address->tag,
                        'currency' => $wallet->currency,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate deposit address.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create deposit transaction
     */
    public function createDeposit(DepositRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $wallet = $user->wallets()
                ->where('currency', $request->currency)
                ->where('wallet_type', 'spot')
                ->where('is_active', true)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found for the specified currency.',
                ], 404);
            }

            // Create deposit transaction
            $transaction = $this->walletService->createDeposit($wallet, [
                'amount' => $request->amount,
                'txid' => $request->txid,
                'from_address' => $request->from_address,
                'memo' => $request->memo,
            ]);

            // Log the deposit creation
            AuditLog::createLog($user, 'deposit_created', $transaction, [
                'currency' => $request->currency,
                'amount' => $request->amount,
                'txid' => $request->txid,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Deposit transaction created successfully.',
                'data' => [
                    'transaction' => [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'txid' => $transaction->txid,
                        'created_at' => $transaction->created_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deposit transaction.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create withdrawal transaction
     */
    public function createWithdrawal(WithdrawRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user can withdraw
            if (!$user->canTrade()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not eligible for withdrawals.',
                ], 403);
            }

            $wallet = $user->wallets()
                ->where('currency', $request->currency)
                ->where('wallet_type', 'spot')
                ->where('is_active', true)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found for the specified currency.',
                ], 404);
            }

            // Check if user has sufficient balance
            if (!$wallet->hasBalance($request->amount + $request->fee)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance for withdrawal.',
                ], 400);
            }

            // Create withdrawal transaction
            $transaction = $this->walletService->createWithdrawal($wallet, [
                'amount' => $request->amount,
                'fee' => $request->fee,
                'to_address' => $request->to_address,
                'memo' => $request->memo,
            ]);

            // Log the withdrawal creation
            AuditLog::createLog($user, 'withdrawal_created', $transaction, [
                'currency' => $request->currency,
                'amount' => $request->amount,
                'fee' => $request->fee,
                'to_address' => $request->to_address,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal transaction created successfully.',
                'data' => [
                    'transaction' => [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'fee' => $transaction->fee,
                        'total_amount' => $transaction->total_amount,
                        'currency' => $transaction->currency,
                        'to_address' => $transaction->to_address,
                        'created_at' => $transaction->created_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create withdrawal transaction.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function getTransactions(Request $request, int $walletId): JsonResponse
    {
        try {
            $user = $request->user();
            $wallet = $user->wallets()
                ->where('id', $walletId)
                ->where('is_active', true)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found.',
                ], 404);
            }

            $transactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions->items(),
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all user transactions
     */
    public function getAllTransactions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $transactions = $user->transactions()
                ->with('wallet')
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions->items(),
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}