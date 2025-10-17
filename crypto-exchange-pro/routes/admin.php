<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\GodController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\TradingController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\BlockchainController;
use App\Http\Controllers\Admin\CryptoController;
use App\Http\Controllers\Admin\StakingController;
use App\Http\Controllers\Admin\LendingController;
use App\Http\Controllers\Admin\DefiController;
use App\Http\Controllers\Admin\NftController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\MaintenanceController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for admin users with god-level powers to manage
| the entire cryptocurrency exchange platform.
|
*/

// God-level admin routes (super_admin only)
Route::middleware(['auth:sanctum', 'admin', 'role:super_admin'])->prefix('god')->group(function () {
    
    // System overview and health
    Route::get('overview', [GodController::class, 'getSystemOverview']);
    Route::get('health', [GodController::class, 'getSystemHealth']);
    
    // Service provider management
    Route::post('service-providers', [GodController::class, 'manageServiceProviders']);
    Route::get('service-providers', [ServiceProviderController::class, 'index']);
    Route::get('service-providers/{id}', [ServiceProviderController::class, 'show']);
    Route::put('service-providers/{id}', [ServiceProviderController::class, 'update']);
    Route::delete('service-providers/{id}', [ServiceProviderController::class, 'destroy']);
    Route::post('service-providers/{id}/test', [ServiceProviderController::class, 'testConnection']);
    Route::post('service-providers/{id}/enable', [ServiceProviderController::class, 'enable']);
    Route::post('service-providers/{id}/disable', [ServiceProviderController::class, 'disable']);
    
    // Blockchain network management
    Route::post('blockchain-networks', [GodController::class, 'manageBlockchainNetworks']);
    Route::get('blockchain-networks', [BlockchainController::class, 'index']);
    Route::get('blockchain-networks/{id}', [BlockchainController::class, 'show']);
    Route::put('blockchain-networks/{id}', [BlockchainController::class, 'update']);
    Route::delete('blockchain-networks/{id}', [BlockchainController::class, 'destroy']);
    Route::post('blockchain-networks/{id}/test', [BlockchainController::class, 'testConnection']);
    Route::post('blockchain-networks/{id}/enable', [BlockchainController::class, 'enable']);
    Route::post('blockchain-networks/{id}/disable', [BlockchainController::class, 'disable']);
    
    // Cryptocurrency management
    Route::post('cryptocurrencies', [GodController::class, 'manageCryptocurrencies']);
    Route::get('cryptocurrencies', [CryptoController::class, 'index']);
    Route::get('cryptocurrencies/{id}', [CryptoController::class, 'show']);
    Route::put('cryptocurrencies/{id}', [CryptoController::class, 'update']);
    Route::delete('cryptocurrencies/{id}', [CryptoController::class, 'destroy']);
    Route::post('cryptocurrencies/{id}/enable', [CryptoController::class, 'enable']);
    Route::post('cryptocurrencies/{id}/disable', [CryptoController::class, 'disable']);
    Route::post('cryptocurrencies/import', [CryptoController::class, 'importFromProvider']);
    Route::post('cryptocurrencies/sync', [CryptoController::class, 'syncWithProviders']);
    Route::post('cryptocurrencies/update-prices', [CryptoController::class, 'updatePrices']);
    
    // Trading pair management
    Route::post('trading-pairs', [GodController::class, 'manageTradingPairs']);
    Route::get('trading-pairs', [TradingController::class, 'getPairs']);
    Route::get('trading-pairs/{id}', [TradingController::class, 'getPair']);
    Route::put('trading-pairs/{id}', [TradingController::class, 'updatePair']);
    Route::delete('trading-pairs/{id}', [TradingController::class, 'deletePair']);
    Route::post('trading-pairs/{id}/enable', [TradingController::class, 'enablePair']);
    Route::post('trading-pairs/{id}/disable', [TradingController::class, 'disablePair']);
    Route::post('trading-pairs/auto-create', [TradingController::class, 'autoCreatePairs']);
    Route::post('trading-pairs/sync', [TradingController::class, 'syncWithProviders']);
    
    // System configuration management
    Route::post('config', [GodController::class, 'manageSystemConfig']);
    Route::get('config', [SystemController::class, 'getConfig']);
    Route::put('config', [SystemController::class, 'updateConfig']);
    Route::post('config/bulk-update', [SystemController::class, 'bulkUpdateConfig']);
    Route::post('config/reset', [SystemController::class, 'resetConfig']);
    
    // Maintenance operations
    Route::post('maintenance', [GodController::class, 'executeMaintenance']);
    Route::get('maintenance/status', [MaintenanceController::class, 'getStatus']);
    Route::post('maintenance/backup', [MaintenanceController::class, 'createBackup']);
    Route::post('maintenance/optimize', [MaintenanceController::class, 'optimizeSystem']);
    Route::post('maintenance/cleanup', [MaintenanceController::class, 'cleanupSystem']);
    Route::post('maintenance/restart-services', [MaintenanceController::class, 'restartServices']);
    
    // Security management
    Route::get('security/overview', [SecurityController::class, 'getOverview']);
    Route::get('security/alerts', [SecurityController::class, 'getAlerts']);
    Route::get('security/incidents', [SecurityController::class, 'getIncidents']);
    Route::post('security/block-ip', [SecurityController::class, 'blockIp']);
    Route::post('security/unblock-ip', [SecurityController::class, 'unblockIp']);
    Route::post('security/reset-user-2fa', [SecurityController::class, 'resetUser2FA']);
    Route::post('security/lock-user', [SecurityController::class, 'lockUser']);
    Route::post('security/unlock-user', [SecurityController::class, 'unlockUser']);
    
    // Reports and analytics
    Route::get('reports/overview', [ReportController::class, 'getOverview']);
    Route::get('reports/revenue', [ReportController::class, 'getRevenueReport']);
    Route::get('reports/users', [ReportController::class, 'getUserReport']);
    Route::get('reports/trading', [ReportController::class, 'getTradingReport']);
    Route::get('reports/transactions', [ReportController::class, 'getTransactionReport']);
    Route::get('reports/kyc', [ReportController::class, 'getKycReport']);
    Route::post('reports/export', [ReportController::class, 'exportReport']);
    
    // Notification management
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/{id}', [NotificationController::class, 'show']);
    Route::post('notifications', [NotificationController::class, 'create']);
    Route::put('notifications/{id}', [NotificationController::class, 'update']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('notifications/broadcast', [NotificationController::class, 'broadcast']);
    Route::post('notifications/send-email', [NotificationController::class, 'sendEmail']);
    Route::post('notifications/send-sms', [NotificationController::class, 'sendSms']);
    Route::post('notifications/send-push', [NotificationController::class, 'sendPush']);
});

// Regular admin routes (admin and above)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard routes
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('stats', [DashboardController::class, 'getStats']);
    Route::get('overview', [DashboardController::class, 'getOverview']);
    
    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}/status', [UserController::class, 'updateStatus']);
        Route::put('{id}/kyc-status', [UserController::class, 'updateKycStatus']);
        Route::get('{id}/transactions', [UserController::class, 'getTransactions']);
        Route::get('{id}/orders', [UserController::class, 'getOrders']);
        Route::get('{id}/wallets', [UserController::class, 'getWallets']);
        Route::post('{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('{id}/enable-2fa', [UserController::class, 'enable2FA']);
        Route::post('{id}/disable-2fa', [UserController::class, 'disable2FA']);
        Route::post('{id}/lock', [UserController::class, 'lockUser']);
        Route::post('{id}/unlock', [UserController::class, 'unlockUser']);
        Route::post('{id}/suspend', [UserController::class, 'suspendUser']);
        Route::post('{id}/unsuspend', [UserController::class, 'unsuspendUser']);
        Route::post('{id}/ban', [UserController::class, 'banUser']);
        Route::post('{id}/unban', [UserController::class, 'unbanUser']);
    });
    
    // KYC management routes
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index']);
        Route::get('{id}', [KycController::class, 'show']);
        Route::post('{id}/approve', [KycController::class, 'approve']);
        Route::post('{id}/reject', [KycController::class, 'reject']);
        Route::get('{id}/documents', [KycController::class, 'getDocuments']);
        Route::post('{id}/request-more-docs', [KycController::class, 'requestMoreDocuments']);
        Route::post('{id}/escalate', [KycController::class, 'escalate']);
    });
    
    // Transaction management routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('{id}', [TransactionController::class, 'show']);
        Route::post('{id}/approve', [TransactionController::class, 'approve']);
        Route::post('{id}/reject', [TransactionController::class, 'reject']);
        Route::post('{id}/process', [TransactionController::class, 'process']);
        Route::post('{id}/cancel', [TransactionController::class, 'cancel']);
        Route::post('{id}/refund', [TransactionController::class, 'refund']);
        Route::get('{id}/history', [TransactionController::class, 'getHistory']);
    });
    
    // Trading management routes
    Route::prefix('trading')->group(function () {
        Route::get('pairs', [TradingController::class, 'getPairs']);
        Route::get('pairs/{id}', [TradingController::class, 'getPair']);
        Route::get('orders', [TradingController::class, 'getOrders']);
        Route::get('orders/{id}', [TradingController::class, 'getOrder']);
        Route::post('orders/{id}/cancel', [TradingController::class, 'cancelOrder']);
        Route::get('trades', [TradingController::class, 'getTrades']);
        Route::get('trades/{id}', [TradingController::class, 'getTrade']);
        Route::get('market-data', [TradingController::class, 'getMarketData']);
        Route::get('order-book/{pair_id}', [TradingController::class, 'getOrderBook']);
    });
    
    // Staking management routes
    Route::prefix('staking')->group(function () {
        Route::get('pools', [StakingController::class, 'getPools']);
        Route::get('pools/{id}', [StakingController::class, 'getPool']);
        Route::post('pools', [StakingController::class, 'createPool']);
        Route::put('pools/{id}', [StakingController::class, 'updatePool']);
        Route::delete('pools/{id}', [StakingController::class, 'deletePool']);
        Route::post('pools/{id}/enable', [StakingController::class, 'enablePool']);
        Route::post('pools/{id}/disable', [StakingController::class, 'disablePool']);
        Route::get('positions', [StakingController::class, 'getPositions']);
        Route::get('positions/{id}', [StakingController::class, 'getPosition']);
        Route::post('positions/{id}/unstake', [StakingController::class, 'unstake']);
        Route::post('positions/{id}/claim-rewards', [StakingController::class, 'claimRewards']);
    });
    
    // Lending management routes
    Route::prefix('lending')->group(function () {
        Route::get('pools', [LendingController::class, 'getPools']);
        Route::get('pools/{id}', [LendingController::class, 'getPool']);
        Route::post('pools', [LendingController::class, 'createPool']);
        Route::put('pools/{id}', [LendingController::class, 'updatePool']);
        Route::delete('pools/{id}', [LendingController::class, 'deletePool']);
        Route::post('pools/{id}/enable', [LendingController::class, 'enablePool']);
        Route::post('pools/{id}/disable', [LendingController::class, 'disablePool']);
        Route::get('positions', [LendingController::class, 'getPositions']);
        Route::get('positions/{id}', [LendingController::class, 'getPosition']);
        Route::post('positions/{id}/close', [LendingController::class, 'closePosition']);
        Route::post('positions/{id}/liquidate', [LendingController::class, 'liquidatePosition']);
    });
    
    // DeFi management routes
    Route::prefix('defi')->group(function () {
        Route::get('protocols', [DefiController::class, 'getProtocols']);
        Route::get('protocols/{id}', [DefiController::class, 'getProtocol']);
        Route::post('protocols', [DefiController::class, 'createProtocol']);
        Route::put('protocols/{id}', [DefiController::class, 'updateProtocol']);
        Route::delete('protocols/{id}', [DefiController::class, 'deleteProtocol']);
        Route::post('protocols/{id}/enable', [DefiController::class, 'enableProtocol']);
        Route::post('protocols/{id}/disable', [DefiController::class, 'disableProtocol']);
        Route::get('positions', [DefiController::class, 'getPositions']);
        Route::get('positions/{id}', [DefiController::class, 'getPosition']);
        Route::post('positions/{id}/close', [DefiController::class, 'closePosition']);
    });
    
    // NFT management routes
    Route::prefix('nft')->group(function () {
        Route::get('collections', [NftController::class, 'getCollections']);
        Route::get('collections/{id}', [NftController::class, 'getCollection']);
        Route::post('collections', [NftController::class, 'createCollection']);
        Route::put('collections/{id}', [NftController::class, 'updateCollection']);
        Route::delete('collections/{id}', [NftController::class, 'deleteCollection']);
        Route::post('collections/{id}/verify', [NftController::class, 'verifyCollection']);
        Route::get('items', [NftController::class, 'getItems']);
        Route::get('items/{id}', [NftController::class, 'getItem']);
        Route::post('items/{id}/verify', [NftController::class, 'verifyItem']);
        Route::get('listings', [NftController::class, 'getListings']);
        Route::get('listings/{id}', [NftController::class, 'getListing']);
        Route::post('listings/{id}/approve', [NftController::class, 'approveListing']);
        Route::post('listings/{id}/reject', [NftController::class, 'rejectListing']);
    });
    
    // System management routes
    Route::prefix('system')->group(function () {
        Route::get('config', [SystemController::class, 'getConfig']);
        Route::put('config', [SystemController::class, 'updateConfig']);
        Route::get('providers', [SystemController::class, 'getProviders']);
        Route::put('providers/{id}', [SystemController::class, 'updateProvider']);
        Route::get('logs', [SystemController::class, 'getLogs']);
        Route::get('health', [SystemController::class, 'getHealth']);
        Route::get('performance', [SystemController::class, 'getPerformance']);
        Route::get('alerts', [SystemController::class, 'getAlerts']);
        Route::post('alerts/{id}/acknowledge', [SystemController::class, 'acknowledgeAlert']);
    });
    
    // Audit logs routes
    Route::prefix('audit')->group(function () {
        Route::get('logs', [AuditController::class, 'index']);
        Route::get('logs/{id}', [AuditController::class, 'show']);
        Route::get('stats', [AuditController::class, 'getStats']);
        Route::get('export', [AuditController::class, 'export']);
    });
    
    // Service provider routes (limited access)
    Route::prefix('service-providers')->group(function () {
        Route::get('/', [ServiceProviderController::class, 'index']);
        Route::get('{id}', [ServiceProviderController::class, 'show']);
        Route::post('{id}/test', [ServiceProviderController::class, 'testConnection']);
    });
    
    // Blockchain network routes (limited access)
    Route::prefix('blockchain-networks')->group(function () {
        Route::get('/', [BlockchainController::class, 'index']);
        Route::get('{id}', [BlockchainController::class, 'show']);
        Route::post('{id}/test', [BlockchainController::class, 'testConnection']);
    });
    
    // Cryptocurrency routes (limited access)
    Route::prefix('cryptocurrencies')->group(function () {
        Route::get('/', [CryptoController::class, 'index']);
        Route::get('{id}', [CryptoController::class, 'show']);
        Route::post('{id}/update-price', [CryptoController::class, 'updatePrice']);
    });
    
    // Trading pair routes (limited access)
    Route::prefix('trading-pairs')->group(function () {
        Route::get('/', [TradingController::class, 'getPairs']);
        Route::get('{id}', [TradingController::class, 'getPair']);
    });
    
    // Report routes (limited access)
    Route::prefix('reports')->group(function () {
        Route::get('overview', [ReportController::class, 'getOverview']);
        Route::get('revenue', [ReportController::class, 'getRevenueReport']);
        Route::get('users', [ReportController::class, 'getUserReport']);
        Route::get('trading', [ReportController::class, 'getTradingReport']);
    });
    
    // Notification routes (limited access)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('{id}', [NotificationController::class, 'show']);
        Route::post('broadcast', [NotificationController::class, 'broadcast']);
    });
});

// Support admin routes (support and above)
Route::middleware(['auth:sanctum', 'admin', 'role:support,admin,super_admin'])->prefix('support')->group(function () {
    
    // User support routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::get('{id}/transactions', [UserController::class, 'getTransactions']);
        Route::get('{id}/orders', [UserController::class, 'getOrders']);
        Route::get('{id}/wallets', [UserController::class, 'getWallets']);
        Route::post('{id}/reset-password', [UserController::class, 'resetPassword']);
    });
    
    // KYC support routes
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index']);
        Route::get('{id}', [KycController::class, 'show']);
        Route::post('{id}/approve', [KycController::class, 'approve']);
        Route::post('{id}/reject', [KycController::class, 'reject']);
        Route::get('{id}/documents', [KycController::class, 'getDocuments']);
    });
    
    // Transaction support routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('{id}', [TransactionController::class, 'show']);
        Route::post('{id}/approve', [TransactionController::class, 'approve']);
        Route::post('{id}/reject', [TransactionController::class, 'reject']);
    });
    
    // Trading support routes
    Route::prefix('trading')->group(function () {
        Route::get('orders', [TradingController::class, 'getOrders']);
        Route::get('orders/{id}', [TradingController::class, 'getOrder']);
        Route::post('orders/{id}/cancel', [TradingController::class, 'cancelOrder']);
        Route::get('trades', [TradingController::class, 'getTrades']);
        Route::get('trades/{id}', [TradingController::class, 'getTrade']);
    });
    
    // Notification support routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('{id}', [NotificationController::class, 'show']);
        Route::post('send-email', [NotificationController::class, 'sendEmail']);
        Route::post('send-sms', [NotificationController::class, 'sendSms']);
    });
});

// Compliance admin routes (compliance and above)
Route::middleware(['auth:sanctum', 'admin', 'role:compliance,admin,super_admin'])->prefix('compliance')->group(function () {
    
    // KYC compliance routes
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index']);
        Route::get('{id}', [KycController::class, 'show']);
        Route::post('{id}/approve', [KycController::class, 'approve']);
        Route::post('{id}/reject', [KycController::class, 'reject']);
        Route::get('{id}/documents', [KycController::class, 'getDocuments']);
        Route::post('{id}/escalate', [KycController::class, 'escalate']);
    });
    
    // Transaction compliance routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('{id}', [TransactionController::class, 'show']);
        Route::post('{id}/flag', [TransactionController::class, 'flagTransaction']);
        Route::post('{id}/unflag', [TransactionController::class, 'unflagTransaction']);
        Route::get('flagged', [TransactionController::class, 'getFlaggedTransactions']);
    });
    
    // User compliance routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::post('{id}/flag', [UserController::class, 'flagUser']);
        Route::post('{id}/unflag', [UserController::class, 'unflagUser']);
        Route::get('flagged', [UserController::class, 'getFlaggedUsers']);
    });
    
    // Audit compliance routes
    Route::prefix('audit')->group(function () {
        Route::get('logs', [AuditController::class, 'index']);
        Route::get('logs/{id}', [AuditController::class, 'show']);
        Route::get('export', [AuditController::class, 'export']);
    });
});

// Finance admin routes (finance and above)
Route::middleware(['auth:sanctum', 'admin', 'role:finance,admin,super_admin'])->prefix('finance')->group(function () {
    
    // Transaction finance routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('{id}', [TransactionController::class, 'show']);
        Route::post('{id}/approve', [TransactionController::class, 'approve']);
        Route::post('{id}/reject', [TransactionController::class, 'reject']);
        Route::post('{id}/process', [TransactionController::class, 'process']);
        Route::post('{id}/refund', [TransactionController::class, 'refund']);
    });
    
    // Revenue finance routes
    Route::prefix('revenue')->group(function () {
        Route::get('overview', [ReportController::class, 'getRevenueReport']);
        Route::get('daily', [ReportController::class, 'getDailyRevenue']);
        Route::get('monthly', [ReportController::class, 'getMonthlyRevenue']);
        Route::get('yearly', [ReportController::class, 'getYearlyRevenue']);
        Route::post('export', [ReportController::class, 'exportRevenueReport']);
    });
    
    // Wallet finance routes
    Route::prefix('wallets')->group(function () {
        Route::get('/', [UserController::class, 'getWallets']);
        Route::get('{id}', [UserController::class, 'getWallet']);
        Route::post('{id}/freeze', [UserController::class, 'freezeWallet']);
        Route::post('{id}/unfreeze', [UserController::class, 'unfreezeWallet']);
    });
});