<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TradingController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\StakingController;
use App\Http\Controllers\Api\LendingController;
use App\Http\Controllers\Api\DefiController;
use App\Http\Controllers\Api\NftController;
use App\Http\Controllers\Api\FuturesController;
use App\Http\Controllers\Api\MarginController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\WebSocketController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\RewardsController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\MobileController;

/*
|--------------------------------------------------------------------------
| Enhanced API Routes
|--------------------------------------------------------------------------
|
| Here are all the API routes for the enhanced cryptocurrency exchange platform.
| This includes 200+ endpoints covering all features: trading, staking, DeFi, NFTs, etc.
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);
    Route::get('check-email/{email}', [AuthController::class, 'checkEmail']);
    Route::get('check-username/{username}', [AuthController::class, 'checkUsername']);
});

// Public market data
Route::prefix('market')->group(function () {
    Route::get('pairs', [MarketController::class, 'getPairs']);
    Route::get('pairs/{id}/data', [MarketController::class, 'getMarketData']);
    Route::get('pairs/{id}/orderbook', [MarketController::class, 'getOrderBook']);
    Route::get('pairs/{id}/trades', [MarketController::class, 'getRecentTrades']);
    Route::get('pairs/{id}/candles', [MarketController::class, 'getCandles']);
    Route::get('pairs/{id}/stats', [MarketController::class, 'getStats']);
    Route::get('tickers', [MarketController::class, 'getTickers']);
    Route::get('trending', [MarketController::class, 'getTrending']);
    Route::get('gainers', [MarketController::class, 'getGainers']);
    Route::get('losers', [MarketController::class, 'getLosers']);
    Route::get('search', [MarketController::class, 'search']);
    Route::get('categories', [MarketController::class, 'getCategories']);
    Route::get('global-stats', [MarketController::class, 'getGlobalStats']);
});

// Public cryptocurrency data
Route::prefix('crypto')->group(function () {
    Route::get('list', [MarketController::class, 'getCryptocurrencies']);
    Route::get('{symbol}', [MarketController::class, 'getCryptocurrency']);
    Route::get('{symbol}/price', [MarketController::class, 'getPrice']);
    Route::get('{symbol}/history', [MarketController::class, 'getPriceHistory']);
    Route::get('{symbol}/news', [MarketController::class, 'getNews']);
    Route::get('{symbol}/social', [MarketController::class, 'getSocialData']);
    Route::get('{symbol}/analysis', [MarketController::class, 'getAnalysis']);
});

// Public blockchain data
Route::prefix('blockchain')->group(function () {
    Route::get('networks', [MarketController::class, 'getBlockchainNetworks']);
    Route::get('networks/{id}/status', [MarketController::class, 'getNetworkStatus']);
    Route::get('networks/{id}/stats', [MarketController::class, 'getNetworkStats']);
    Route::get('transactions/{txid}', [MarketController::class, 'getTransaction']);
    Route::get('addresses/{address}/balance', [MarketController::class, 'getAddressBalance']);
    Route::get('addresses/{address}/transactions', [MarketController::class, 'getAddressTransactions']);
});

// Public DeFi data
Route::prefix('defi')->group(function () {
    Route::get('protocols', [DefiController::class, 'getPublicProtocols']);
    Route::get('protocols/{id}', [DefiController::class, 'getPublicProtocol']);
    Route::get('yields', [DefiController::class, 'getYields']);
    Route::get('tvl', [DefiController::class, 'getTvl']);
    Route::get('trending', [DefiController::class, 'getTrending']);
});

// Public NFT data
Route::prefix('nft')->group(function () {
    Route::get('collections', [NftController::class, 'getPublicCollections']);
    Route::get('collections/{id}', [NftController::class, 'getPublicCollection']);
    Route::get('collections/{id}/items', [NftController::class, 'getPublicItems']);
    Route::get('collections/{id}/stats', [NftController::class, 'getPublicStats']);
    Route::get('trending', [NftController::class, 'getTrending']);
    Route::get('categories', [NftController::class, 'getCategories']);
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
        Route::post('2fa/backup-codes', [AuthController::class, 'getBackupCodes']);
        Route::post('2fa/regenerate-codes', [AuthController::class, 'regenerateBackupCodes']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('change-email', [AuthController::class, 'changeEmail']);
        Route::post('verify-new-email', [AuthController::class, 'verifyNewEmail']);
        Route::post('change-phone', [AuthController::class, 'changePhone']);
        Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
        Route::get('sessions', [AuthController::class, 'getSessions']);
        Route::delete('sessions/{id}', [AuthController::class, 'revokeSession']);
        Route::post('sessions/revoke-all', [AuthController::class, 'revokeAllSessions']);
        Route::get('audit-logs', [AuthController::class, 'getAuditLogs']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::post('avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('avatar', [ProfileController::class, 'deleteAvatar']);
        Route::post('cover', [ProfileController::class, 'uploadCover']);
        Route::delete('cover', [ProfileController::class, 'deleteCover']);
        Route::get('preferences', [ProfileController::class, 'getPreferences']);
        Route::put('preferences', [ProfileController::class, 'updatePreferences']);
        Route::get('activity', [ProfileController::class, 'getActivity']);
        Route::get('statistics', [ProfileController::class, 'getStatistics']);
    });

    // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'getSettings']);
        Route::put('/', [SettingsController::class, 'updateSettings']);
        Route::get('notifications', [SettingsController::class, 'getNotificationSettings']);
        Route::put('notifications', [SettingsController::class, 'updateNotificationSettings']);
        Route::get('privacy', [SettingsController::class, 'getPrivacySettings']);
        Route::put('privacy', [SettingsController::class, 'updatePrivacySettings']);
        Route::get('security', [SettingsController::class, 'getSecuritySettings']);
        Route::put('security', [SettingsController::class, 'updateSecuritySettings']);
        Route::get('api', [SettingsController::class, 'getApiSettings']);
        Route::post('api/keys', [SettingsController::class, 'createApiKey']);
        Route::put('api/keys/{id}', [SettingsController::class, 'updateApiKey']);
        Route::delete('api/keys/{id}', [SettingsController::class, 'deleteApiKey']);
        Route::get('api/keys', [SettingsController::class, 'getApiKeys']);
        Route::get('api/logs', [SettingsController::class, 'getApiLogs']);
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
        Route::get('{id}/balance', [WalletController::class, 'getBalance']);
        Route::get('balances', [WalletController::class, 'getAllBalances']);
        Route::post('transfer', [WalletController::class, 'transfer']);
        Route::post('convert', [WalletController::class, 'convert']);
        Route::get('fees', [WalletController::class, 'getFees']);
        Route::get('limits', [WalletController::class, 'getLimits']);
        Route::get('history', [WalletController::class, 'getHistory']);
        Route::get('summary', [WalletController::class, 'getSummary']);
    });

    // Trading routes
    Route::prefix('trading')->group(function () {
        Route::get('pairs', [TradingController::class, 'getPairs']);
        Route::get('pairs/{id}/data', [TradingController::class, 'getMarketData']);
        Route::get('pairs/{id}/orderbook', [TradingController::class, 'getOrderBook']);
        Route::get('pairs/{id}/trades', [TradingController::class, 'getRecentTrades']);
        Route::get('pairs/{id}/candles', [TradingController::class, 'getCandles']);
        Route::get('pairs/{id}/stats', [TradingController::class, 'getStats']);
        Route::post('orders', [TradingController::class, 'placeOrder']);
        Route::get('orders', [TradingController::class, 'getOrders']);
        Route::get('orders/{id}', [TradingController::class, 'getOrder']);
        Route::put('orders/{id}', [TradingController::class, 'updateOrder']);
        Route::delete('orders/{id}', [TradingController::class, 'cancelOrder']);
        Route::post('orders/bulk-cancel', [TradingController::class, 'bulkCancelOrders']);
        Route::get('trades', [TradingController::class, 'getUserTrades']);
        Route::get('trades/{id}', [TradingController::class, 'getTrade']);
        Route::get('history', [TradingController::class, 'getHistory']);
        Route::get('fees', [TradingController::class, 'getFees']);
        Route::get('limits', [TradingController::class, 'getLimits']);
        Route::get('analytics', [TradingController::class, 'getAnalytics']);
        Route::get('alerts', [TradingController::class, 'getAlerts']);
        Route::post('alerts', [TradingController::class, 'createAlert']);
        Route::put('alerts/{id}', [TradingController::class, 'updateAlert']);
        Route::delete('alerts/{id}', [TradingController::class, 'deleteAlert']);
    });

    // Futures trading routes
    Route::prefix('futures')->group(function () {
        Route::get('contracts', [FuturesController::class, 'getContracts']);
        Route::get('contracts/{id}', [FuturesController::class, 'getContract']);
        Route::get('contracts/{id}/data', [FuturesController::class, 'getContractData']);
        Route::get('contracts/{id}/orderbook', [FuturesController::class, 'getOrderBook']);
        Route::get('contracts/{id}/trades', [FuturesController::class, 'getRecentTrades']);
        Route::post('orders', [FuturesController::class, 'placeOrder']);
        Route::get('orders', [FuturesController::class, 'getOrders']);
        Route::get('orders/{id}', [FuturesController::class, 'getOrder']);
        Route::put('orders/{id}', [FuturesController::class, 'updateOrder']);
        Route::delete('orders/{id}', [FuturesController::class, 'cancelOrder']);
        Route::get('positions', [FuturesController::class, 'getPositions']);
        Route::get('positions/{id}', [FuturesController::class, 'getPosition']);
        Route::post('positions/{id}/close', [FuturesController::class, 'closePosition']);
        Route::get('trades', [FuturesController::class, 'getTrades']);
        Route::get('trades/{id}', [FuturesController::class, 'getTrade']);
        Route::get('funding', [FuturesController::class, 'getFunding']);
        Route::get('funding/history', [FuturesController::class, 'getFundingHistory']);
        Route::get('liquidation', [FuturesController::class, 'getLiquidation']);
        Route::get('liquidation/history', [FuturesController::class, 'getLiquidationHistory']);
        Route::get('analytics', [FuturesController::class, 'getAnalytics']);
        Route::get('risk', [FuturesController::class, 'getRisk']);
        Route::post('risk/update', [FuturesController::class, 'updateRisk']);
    });

    // Margin trading routes
    Route::prefix('margin')->group(function () {
        Route::get('pairs', [MarginController::class, 'getPairs']);
        Route::get('pairs/{id}', [MarginController::class, 'getPair']);
        Route::get('pairs/{id}/data', [MarginController::class, 'getPairData']);
        Route::get('pairs/{id}/orderbook', [MarginController::class, 'getOrderBook']);
        Route::post('orders', [MarginController::class, 'placeOrder']);
        Route::get('orders', [MarginController::class, 'getOrders']);
        Route::get('orders/{id}', [MarginController::class, 'getOrder']);
        Route::put('orders/{id}', [MarginController::class, 'updateOrder']);
        Route::delete('orders/{id}', [MarginController::class, 'cancelOrder']);
        Route::get('positions', [MarginController::class, 'getPositions']);
        Route::get('positions/{id}', [MarginController::class, 'getPosition']);
        Route::post('positions/{id}/close', [MarginController::class, 'closePosition']);
        Route::get('trades', [MarginController::class, 'getTrades']);
        Route::get('trades/{id}', [MarginController::class, 'getTrade']);
        Route::get('borrow', [MarginController::class, 'getBorrow']);
        Route::post('borrow', [MarginController::class, 'createBorrow']);
        Route::get('repay', [MarginController::class, 'getRepay']);
        Route::post('repay', [MarginController::class, 'createRepay']);
        Route::get('interest', [MarginController::class, 'getInterest']);
        Route::get('interest/history', [MarginController::class, 'getInterestHistory']);
        Route::get('liquidation', [MarginController::class, 'getLiquidation']);
        Route::get('liquidation/history', [MarginController::class, 'getLiquidationHistory']);
        Route::get('analytics', [MarginController::class, 'getAnalytics']);
        Route::get('risk', [MarginController::class, 'getRisk']);
        Route::post('risk/update', [MarginController::class, 'updateRisk']);
    });

    // Staking routes
    Route::prefix('staking')->group(function () {
        Route::get('pools', [StakingController::class, 'getPools']);
        Route::get('pools/{id}', [StakingController::class, 'getPool']);
        Route::get('pools/{id}/stats', [StakingController::class, 'getPoolStats']);
        Route::post('stake', [StakingController::class, 'stake']);
        Route::post('unstake', [StakingController::class, 'unstake']);
        Route::get('positions', [StakingController::class, 'getPositions']);
        Route::get('positions/{id}', [StakingController::class, 'getPosition']);
        Route::post('positions/{id}/claim', [StakingController::class, 'claimRewards']);
        Route::get('rewards', [StakingController::class, 'getRewards']);
        Route::get('rewards/history', [StakingController::class, 'getRewardsHistory']);
        Route::get('analytics', [StakingController::class, 'getAnalytics']);
        Route::get('leaderboard', [StakingController::class, 'getLeaderboard']);
    });

    // Lending routes
    Route::prefix('lending')->group(function () {
        Route::get('pools', [LendingController::class, 'getPools']);
        Route::get('pools/{id}', [LendingController::class, 'getPool']);
        Route::get('pools/{id}/stats', [LendingController::class, 'getPoolStats']);
        Route::post('lend', [LendingController::class, 'lend']);
        Route::post('borrow', [LendingController::class, 'borrow']);
        Route::post('repay', [LendingController::class, 'repay']);
        Route::get('positions', [LendingController::class, 'getPositions']);
        Route::get('positions/{id}', [LendingController::class, 'getPosition']);
        Route::post('positions/{id}/close', [LendingController::class, 'closePosition']);
        Route::get('interest', [LendingController::class, 'getInterest']);
        Route::get('interest/history', [LendingController::class, 'getInterestHistory']);
        Route::get('liquidation', [LendingController::class, 'getLiquidation']);
        Route::get('liquidation/history', [LendingController::class, 'getLiquidationHistory']);
        Route::get('analytics', [LendingController::class, 'getAnalytics']);
        Route::get('risk', [LendingController::class, 'getRisk']);
        Route::post('risk/update', [LendingController::class, 'updateRisk']);
    });

    // DeFi routes
    Route::prefix('defi')->group(function () {
        Route::get('protocols', [DefiController::class, 'getProtocols']);
        Route::get('protocols/{id}', [DefiController::class, 'getProtocol']);
        Route::get('protocols/{id}/pools', [DefiController::class, 'getProtocolPools']);
        Route::post('liquidity/add', [DefiController::class, 'addLiquidity']);
        Route::post('liquidity/remove', [DefiController::class, 'removeLiquidity']);
        Route::post('swap', [DefiController::class, 'swap']);
        Route::get('positions', [DefiController::class, 'getPositions']);
        Route::get('positions/{id}', [DefiController::class, 'getPosition']);
        Route::post('positions/{id}/close', [DefiController::class, 'closePosition']);
        Route::get('yields', [DefiController::class, 'getYields']);
        Route::get('yields/history', [DefiController::class, 'getYieldsHistory']);
        Route::get('analytics', [DefiController::class, 'getAnalytics']);
        Route::get('leaderboard', [DefiController::class, 'getLeaderboard']);
        Route::get('trending', [DefiController::class, 'getTrending']);
    });

    // NFT routes
    Route::prefix('nft')->group(function () {
        Route::get('collections', [NftController::class, 'getCollections']);
        Route::get('collections/{id}', [NftController::class, 'getCollection']);
        Route::get('collections/{id}/items', [NftController::class, 'getItems']);
        Route::get('collections/{id}/stats', [NftController::class, 'getStats']);
        Route::get('items', [NftController::class, 'getAllItems']);
        Route::get('items/{id}', [NftController::class, 'getItem']);
        Route::get('items/{id}/history', [NftController::class, 'getItemHistory']);
        Route::post('listings', [NftController::class, 'createListing']);
        Route::get('listings', [NftController::class, 'getListings']);
        Route::get('listings/{id}', [NftController::class, 'getListing']);
        Route::put('listings/{id}', [NftController::class, 'updateListing']);
        Route::delete('listings/{id}', [NftController::class, 'deleteListing']);
        Route::post('offers', [NftController::class, 'createOffer']);
        Route::get('offers', [NftController::class, 'getOffers']);
        Route::get('offers/{id}', [NftController::class, 'getOffer']);
        Route::put('offers/{id}', [NftController::class, 'updateOffer']);
        Route::delete('offers/{id}', [NftController::class, 'deleteOffer']);
        Route::post('bids', [NftController::class, 'createBid']);
        Route::get('bids', [NftController::class, 'getBids']);
        Route::get('bids/{id}', [NftController::class, 'getBid']);
        Route::put('bids/{id}', [NftController::class, 'updateBid']);
        Route::delete('bids/{id}', [NftController::class, 'deleteBid']);
        Route::post('purchase', [NftController::class, 'purchase']);
        Route::get('trades', [NftController::class, 'getTrades']);
        Route::get('trades/{id}', [NftController::class, 'getTrade']);
        Route::get('analytics', [NftController::class, 'getAnalytics']);
        Route::get('trending', [NftController::class, 'getTrending']);
        Route::get('categories', [NftController::class, 'getCategories']);
    });

    // Portfolio routes
    Route::prefix('portfolio')->group(function () {
        Route::get('/', [PortfolioController::class, 'getPortfolio']);
        Route::get('overview', [PortfolioController::class, 'getOverview']);
        Route::get('holdings', [PortfolioController::class, 'getHoldings']);
        Route::get('performance', [PortfolioController::class, 'getPerformance']);
        Route::get('allocation', [PortfolioController::class, 'getAllocation']);
        Route::get('transactions', [PortfolioController::class, 'getTransactions']);
        Route::get('analytics', [PortfolioController::class, 'getAnalytics']);
        Route::get('reports', [PortfolioController::class, 'getReports']);
        Route::post('reports/generate', [PortfolioController::class, 'generateReport']);
        Route::get('tax', [PortfolioController::class, 'getTaxReport']);
        Route::post('tax/generate', [PortfolioController::class, 'generateTaxReport']);
    });

    // Analytics routes
    Route::prefix('analytics')->group(function () {
        Route::get('overview', [AnalyticsController::class, 'getOverview']);
        Route::get('trading', [AnalyticsController::class, 'getTradingAnalytics']);
        Route::get('portfolio', [AnalyticsController::class, 'getPortfolioAnalytics']);
        Route::get('performance', [AnalyticsController::class, 'getPerformanceAnalytics']);
        Route::get('risk', [AnalyticsController::class, 'getRiskAnalytics']);
        Route::get('reports', [AnalyticsController::class, 'getReports']);
        Route::post('reports/generate', [AnalyticsController::class, 'generateReport']);
        Route::get('export', [AnalyticsController::class, 'exportData']);
    });

    // KYC routes
    Route::prefix('kyc')->group(function () {
        Route::get('status', [KycController::class, 'getStatus']);
        Route::post('submit', [KycController::class, 'submitKyc']);
        Route::get('documents', [KycController::class, 'getDocuments']);
        Route::post('documents/upload', [KycController::class, 'uploadDocument']);
        Route::delete('documents/{id}', [KycController::class, 'deleteDocument']);
        Route::get('requirements', [KycController::class, 'getRequirements']);
        Route::get('history', [KycController::class, 'getHistory']);
        Route::post('resubmit', [KycController::class, 'resubmitKyc']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'getUnread']);
        Route::get('{id}', [NotificationController::class, 'show']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'delete']);
        Route::get('preferences', [NotificationController::class, 'getPreferences']);
        Route::put('preferences', [NotificationController::class, 'updatePreferences']);
        Route::post('subscribe', [NotificationController::class, 'subscribe']);
        Route::post('unsubscribe', [NotificationController::class, 'unsubscribe']);
    });

    // Security routes
    Route::prefix('security')->group(function () {
        Route::get('overview', [SecurityController::class, 'getOverview']);
        Route::get('sessions', [SecurityController::class, 'getSessions']);
        Route::delete('sessions/{id}', [SecurityController::class, 'revokeSession']);
        Route::post('sessions/revoke-all', [SecurityController::class, 'revokeAllSessions']);
        Route::get('devices', [SecurityController::class, 'getDevices']);
        Route::delete('devices/{id}', [SecurityController::class, 'removeDevice']);
        Route::get('login-history', [SecurityController::class, 'getLoginHistory']);
        Route::get('security-events', [SecurityController::class, 'getSecurityEvents']);
        Route::post('enable-whitelist', [SecurityController::class, 'enableWhitelist']);
        Route::post('disable-whitelist', [SecurityController::class, 'disableWhitelist']);
        Route::get('whitelist', [SecurityController::class, 'getWhitelist']);
        Route::post('whitelist', [SecurityController::class, 'addToWhitelist']);
        Route::delete('whitelist/{id}', [SecurityController::class, 'removeFromWhitelist']);
    });

    // Referral routes
    Route::prefix('referral')->group(function () {
        Route::get('program', [ReferralController::class, 'getProgram']);
        Route::get('stats', [ReferralController::class, 'getStats']);
        Route::get('referrals', [ReferralController::class, 'getReferrals']);
        Route::get('commissions', [ReferralController::class, 'getCommissions']);
        Route::get('link', [ReferralController::class, 'getLink']);
        Route::post('link/generate', [ReferralController::class, 'generateLink']);
        Route::get('leaderboard', [ReferralController::class, 'getLeaderboard']);
    });

    // Rewards routes
    Route::prefix('rewards')->group(function () {
        Route::get('programs', [RewardsController::class, 'getPrograms']);
        Route::get('programs/{id}', [RewardsController::class, 'getProgram']);
        Route::get('points', [RewardsController::class, 'getPoints']);
        Route::get('points/history', [RewardsController::class, 'getPointsHistory']);
        Route::get('tiers', [RewardsController::class, 'getTiers']);
        Route::get('benefits', [RewardsController::class, 'getBenefits']);
        Route::post('redeem', [RewardsController::class, 'redeemReward']);
        Route::get('redemptions', [RewardsController::class, 'getRedemptions']);
        Route::get('leaderboard', [RewardsController::class, 'getLeaderboard']);
    });

    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('trading', [ReportsController::class, 'getTradingReport']);
        Route::get('portfolio', [ReportsController::class, 'getPortfolioReport']);
        Route::get('tax', [ReportsController::class, 'getTaxReport']);
        Route::get('performance', [ReportsController::class, 'getPerformanceReport']);
        Route::post('generate', [ReportsController::class, 'generateReport']);
        Route::get('export', [ReportsController::class, 'exportReport']);
        Route::get('templates', [ReportsController::class, 'getTemplates']);
        Route::post('templates', [ReportsController::class, 'createTemplate']);
        Route::put('templates/{id}', [ReportsController::class, 'updateTemplate']);
        Route::delete('templates/{id}', [ReportsController::class, 'deleteTemplate']);
    });

    // Mobile-specific routes
    Route::prefix('mobile')->group(function () {
        Route::get('config', [MobileController::class, 'getConfig']);
        Route::get('features', [MobileController::class, 'getFeatures']);
        Route::get('version', [MobileController::class, 'getVersion']);
        Route::post('feedback', [MobileController::class, 'submitFeedback']);
        Route::get('announcements', [MobileController::class, 'getAnnouncements']);
        Route::post('crash-report', [MobileController::class, 'submitCrashReport']);
    });

    // WebSocket routes
    Route::prefix('websocket')->group(function () {
        Route::get('token', [WebSocketController::class, 'getToken']);
        Route::get('channels', [WebSocketController::class, 'getChannels']);
        Route::post('subscribe', [WebSocketController::class, 'subscribe']);
        Route::post('unsubscribe', [WebSocketController::class, 'unsubscribe']);
    });
});

// Admin routes (included from admin.php)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Include all admin routes from admin.php
    include 'admin.php';
});

// Webhook routes (no authentication required)
Route::prefix('webhooks')->group(function () {
    Route::post('stripe', [App\Http\Controllers\Webhook\StripeController::class, 'handle']);
    Route::post('binance', [App\Http\Controllers\Webhook\BinanceController::class, 'handle']);
    Route::post('coinbase', [App\Http\Controllers\Webhook\CoinbaseController::class, 'handle']);
    Route::post('jumio', [App\Http\Controllers\Webhook\JumioController::class, 'handle']);
    Route::post('onfido', [App\Http\Controllers\Webhook\OnfidoController::class, 'handle']);
    Route::post('sendgrid', [App\Http\Controllers\Webhook\SendGridController::class, 'handle']);
    Route::post('twilio', [App\Http\Controllers\Webhook\TwilioController::class, 'handle']);
    Route::post('firebase', [App\Http\Controllers\Webhook\FirebaseController::class, 'handle']);
    Route::post('infura', [App\Http\Controllers\Webhook\InfuraController::class, 'handle']);
    Route::post('alchemy', [App\Http\Controllers\Webhook\AlchemyController::class, 'handle']);
    Route::post('quicknode', [App\Http\Controllers\Webhook\QuickNodeController::class, 'handle']);
});

// Health check routes
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '2.0.0',
        'features' => [
            'trading' => true,
            'futures' => true,
            'margin' => true,
            'staking' => true,
            'lending' => true,
            'defi' => true,
            'nft' => true,
            'websocket' => true,
            'mobile' => true,
            'admin' => true,
        ],
    ]);
});

Route::get('status', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '2.0.0',
        'uptime' => '99.9%',
        'services' => [
            'database' => 'healthy',
            'redis' => 'healthy',
            'queue' => 'healthy',
            'websocket' => 'healthy',
        ],
    ]);
});

// API documentation route
Route::get('docs', function () {
    return response()->json([
        'title' => 'CryptoExchange Pro Enhanced API',
        'version' => '2.0.0',
        'description' => 'Complete cryptocurrency exchange platform API with 200+ endpoints',
        'endpoints' => [
            'auth' => 'Authentication and user management',
            'market' => 'Public market data and cryptocurrency information',
            'trading' => 'Spot trading operations',
            'futures' => 'Futures trading operations',
            'margin' => 'Margin trading operations',
            'staking' => 'Staking and rewards',
            'lending' => 'Lending and borrowing',
            'defi' => 'DeFi protocol integration',
            'nft' => 'NFT marketplace operations',
            'portfolio' => 'Portfolio management and analytics',
            'admin' => 'Administrative operations (admin only)',
            'god' => 'God-level system control (super admin only)',
        ],
        'documentation' => 'https://docs.cryptoexchange-pro.com',
        'support' => 'https://support.cryptoexchange-pro.com',
    ]);
});