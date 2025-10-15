<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TradingController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MarketController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Public market data
Route::prefix('market')->group(function () {
    Route::get('pairs', [MarketController::class, 'getPairs']);
    Route::get('pairs/{id}/data', [MarketController::class, 'getMarketData']);
    Route::get('pairs/{id}/orderbook', [MarketController::class, 'getOrderBook']);
    Route::get('pairs/{id}/trades', [MarketController::class, 'getRecentTrades']);
    Route::get('tickers', [MarketController::class, 'getTickers']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('2fa/enable', [AuthController::class, 'enable2FA']);
        Route::post('2fa/verify', [AuthController::class, 'verify2FA']);
        Route::post('2fa/disable', [AuthController::class, 'disable2FA']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Wallet routes
    Route::prefix('wallets')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::get('{id}', [WalletController::class, 'show']);
        Route::post('{id}/deposit-address', [WalletController::class, 'generateDepositAddress']);
        Route::post('deposit', [WalletController::class, 'createDeposit']);
        Route::post('withdraw', [WalletController::class, 'createWithdrawal']);
        Route::get('{id}/transactions', [WalletController::class, 'getTransactions']);
        Route::get('transactions/all', [WalletController::class, 'getAllTransactions']);
    });

    // Trading routes
    Route::prefix('trading')->group(function () {
        Route::get('pairs', [TradingController::class, 'getPairs']);
        Route::get('pairs/{id}/data', [TradingController::class, 'getMarketData']);
        Route::get('pairs/{id}/orderbook', [TradingController::class, 'getOrderBook']);
        Route::get('pairs/{id}/trades', [TradingController::class, 'getRecentTrades']);
        Route::post('orders', [TradingController::class, 'placeOrder']);
        Route::get('orders', [TradingController::class, 'getOrders']);
        Route::delete('orders/{id}', [TradingController::class, 'cancelOrder']);
        Route::get('trades', [TradingController::class, 'getUserTrades']);
    });

    // KYC routes
    Route::prefix('kyc')->group(function () {
        Route::get('status', [KycController::class, 'getStatus']);
        Route::post('submit', [KycController::class, 'submitKyc']);
        Route::get('documents', [KycController::class, 'getDocuments']);
        Route::post('documents/upload', [KycController::class, 'uploadDocument']);
        Route::delete('documents/{id}', [KycController::class, 'deleteDocument']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'getUnread']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'delete']);
    });

    // User profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'getProfile']);
        Route::put('/', [AuthController::class, 'updateProfile']);
        Route::post('avatar', [AuthController::class, 'uploadAvatar']);
        Route::delete('avatar', [AuthController::class, 'deleteAvatar']);
    });

    // Security routes
    Route::prefix('security')->group(function () {
        Route::get('sessions', [AuthController::class, 'getSessions']);
        Route::delete('sessions/{id}', [AuthController::class, 'revokeSession']);
        Route::post('sessions/revoke-all', [AuthController::class, 'revokeAllSessions']);
        Route::get('audit-logs', [AuthController::class, 'getAuditLogs']);
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard routes
    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('stats', [App\Http\Controllers\Admin\DashboardController::class, 'getStats']);
    
    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::get('{id}', [App\Http\Controllers\Admin\UserController::class, 'show']);
        Route::put('{id}/status', [App\Http\Controllers\Admin\UserController::class, 'updateStatus']);
        Route::put('{id}/kyc-status', [App\Http\Controllers\Admin\UserController::class, 'updateKycStatus']);
        Route::get('{id}/transactions', [App\Http\Controllers\Admin\UserController::class, 'getTransactions']);
        Route::get('{id}/orders', [App\Http\Controllers\Admin\UserController::class, 'getOrders']);
    });
    
    // KYC management routes
    Route::prefix('kyc')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\KycController::class, 'index']);
        Route::get('{id}', [App\Http\Controllers\Admin\KycController::class, 'show']);
        Route::post('{id}/approve', [App\Http\Controllers\Admin\KycController::class, 'approve']);
        Route::post('{id}/reject', [App\Http\Controllers\Admin\KycController::class, 'reject']);
        Route::get('{id}/documents', [App\Http\Controllers\Admin\KycController::class, 'getDocuments']);
    });
    
    // Transaction management routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TransactionController::class, 'index']);
        Route::get('{id}', [App\Http\Controllers\Admin\TransactionController::class, 'show']);
        Route::post('{id}/approve', [App\Http\Controllers\Admin\TransactionController::class, 'approve']);
        Route::post('{id}/reject', [App\Http\Controllers\Admin\TransactionController::class, 'reject']);
        Route::post('{id}/process', [App\Http\Controllers\Admin\TransactionController::class, 'process']);
    });
    
    // Trading management routes
    Route::prefix('trading')->group(function () {
        Route::get('pairs', [App\Http\Controllers\Admin\TradingController::class, 'getPairs']);
        Route::post('pairs', [App\Http\Controllers\Admin\TradingController::class, 'createPair']);
        Route::put('pairs/{id}', [App\Http\Controllers\Admin\TradingController::class, 'updatePair']);
        Route::delete('pairs/{id}', [App\Http\Controllers\Admin\TradingController::class, 'deletePair']);
        Route::get('orders', [App\Http\Controllers\Admin\TradingController::class, 'getOrders']);
        Route::get('trades', [App\Http\Controllers\Admin\TradingController::class, 'getTrades']);
    });
    
    // System management routes
    Route::prefix('system')->group(function () {
        Route::get('config', [App\Http\Controllers\Admin\SystemController::class, 'getConfig']);
        Route::put('config', [App\Http\Controllers\Admin\SystemController::class, 'updateConfig']);
        Route::get('providers', [App\Http\Controllers\Admin\SystemController::class, 'getProviders']);
        Route::put('providers/{id}', [App\Http\Controllers\Admin\SystemController::class, 'updateProvider']);
        Route::get('logs', [App\Http\Controllers\Admin\SystemController::class, 'getLogs']);
        Route::get('health', [App\Http\Controllers\Admin\SystemController::class, 'getHealth']);
    });
    
    // Audit logs routes
    Route::prefix('audit')->group(function () {
        Route::get('logs', [App\Http\Controllers\Admin\AuditController::class, 'index']);
        Route::get('logs/{id}', [App\Http\Controllers\Admin\AuditController::class, 'show']);
        Route::get('stats', [App\Http\Controllers\Admin\AuditController::class, 'getStats']);
    });
});

// Webhook routes (no authentication required)
Route::prefix('webhooks')->group(function () {
    Route::post('stripe', [App\Http\Controllers\Webhook\StripeController::class, 'handle']);
    Route::post('binance', [App\Http\Controllers\Webhook\BinanceController::class, 'handle']);
    Route::post('jumio', [App\Http\Controllers\Webhook\JumioController::class, 'handle']);
    Route::post('sendgrid', [App\Http\Controllers\Webhook\SendGridController::class, 'handle']);
});

// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});