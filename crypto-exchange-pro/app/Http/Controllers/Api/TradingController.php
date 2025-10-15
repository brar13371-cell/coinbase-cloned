<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlaceOrderRequest;
use App\Models\TradingPair;
use App\Models\Order;
use App\Models\Trade;
use App\Models\MarketData;
use App\Models\OrderBook;
use App\Models\AuditLog;
use App\Services\TradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TradingController extends Controller
{
    protected $tradingService;

    public function __construct(TradingService $tradingService)
    {
        $this->tradingService = $tradingService;
    }

    /**
     * Get trading pairs
     */
    public function getPairs(Request $request): JsonResponse
    {
        try {
            $pairs = TradingPair::active()
                ->with(['marketData' => function ($query) {
                    $query->latest('timestamp');
                }])
                ->get()
                ->map(function ($pair) {
                    $marketData = $pair->marketData->first();
                    return [
                        'id' => $pair->id,
                        'symbol' => $pair->symbol,
                        'base_currency' => $pair->base_currency,
                        'quote_currency' => $pair->quote_currency,
                        'price' => $marketData ? $marketData->price : 0,
                        'change_24h' => $marketData ? $marketData->change_24h : 0,
                        'volume_24h' => $marketData ? $marketData->volume_24h : 0,
                        'high_24h' => $marketData ? $marketData->high_24h : 0,
                        'low_24h' => $marketData ? $marketData->low_24h : 0,
                        'min_trade_amount' => $pair->min_trade_amount,
                        'max_trade_amount' => $pair->max_trade_amount,
                        'tick_size' => $pair->tick_size,
                        'step_size' => $pair->step_size,
                        'maker_fee' => $pair->maker_fee,
                        'taker_fee' => $pair->taker_fee,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'pairs' => $pairs,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trading pairs.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get market data for a specific pair
     */
    public function getMarketData(Request $request, int $pairId): JsonResponse
    {
        try {
            $pair = TradingPair::active()->findOrFail($pairId);
            $marketData = $pair->getLatestMarketData();

            if (!$marketData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Market data not available for this pair.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pair' => [
                        'id' => $pair->id,
                        'symbol' => $pair->symbol,
                        'base_currency' => $pair->base_currency,
                        'quote_currency' => $pair->quote_currency,
                    ],
                    'market_data' => [
                        'price' => $marketData->price,
                        'change_24h' => $marketData->change_24h,
                        'volume_24h' => $marketData->volume_24h,
                        'high_24h' => $marketData->high_24h,
                        'low_24h' => $marketData->low_24h,
                        'timestamp' => $marketData->timestamp,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch market data.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get order book for a specific pair
     */
    public function getOrderBook(Request $request, int $pairId): JsonResponse
    {
        try {
            $pair = TradingPair::active()->findOrFail($pairId);
            $limit = $request->input('limit', 50);

            $buyOrders = OrderBook::where('pair_id', $pairId)
                ->where('side', 'buy')
                ->orderBy('price', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($order) {
                    return [
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'total' => $order->price * $order->amount,
                    ];
                });

            $sellOrders = OrderBook::where('pair_id', $pairId)
                ->where('side', 'sell')
                ->orderBy('price', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($order) {
                    return [
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'total' => $order->price * $order->amount,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'pair' => [
                        'id' => $pair->id,
                        'symbol' => $pair->symbol,
                        'base_currency' => $pair->base_currency,
                        'quote_currency' => $pair->quote_currency,
                    ],
                    'order_book' => [
                        'bids' => $buyOrders,
                        'asks' => $sellOrders,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order book.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get recent trades for a specific pair
     */
    public function getRecentTrades(Request $request, int $pairId): JsonResponse
    {
        try {
            $pair = TradingPair::active()->findOrFail($pairId);
            $limit = $request->input('limit', 100);

            $trades = Trade::where('pair_id', $pairId)
                ->with(['buyOrder.user', 'sellOrder.user'])
                ->orderBy('timestamp', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($trade) {
                    return [
                        'id' => $trade->id,
                        'price' => $trade->price,
                        'amount' => $trade->amount,
                        'total' => $trade->price * $trade->amount,
                        'side' => $trade->buyOrder->side,
                        'timestamp' => $trade->timestamp,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'pair' => [
                        'id' => $pair->id,
                        'symbol' => $pair->symbol,
                        'base_currency' => $pair->base_currency,
                        'quote_currency' => $pair->quote_currency,
                    ],
                    'trades' => $trades,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent trades.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Place a new order
     */
    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user can trade
            if (!$user->canTrade()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not eligible for trading.',
                ], 403);
            }

            $pair = TradingPair::active()->findOrFail($request->pair_id);

            // Validate trade amount
            if (!$pair->isValidTradeAmount($request->amount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trade amount is outside the allowed range.',
                ], 400);
            }

            // Round price and amount to tick/step size
            $price = $request->price ? $pair->roundPrice($request->price) : null;
            $amount = $pair->roundQuantity($request->amount);

            // Create order
            $order = $this->tradingService->placeOrder($user, $pair, [
                'side' => $request->side,
                'type' => $request->type,
                'price' => $price,
                'amount' => $amount,
                'time_in_force' => $request->time_in_force ?? 'GTC',
                'expires_at' => $request->expires_at,
            ]);

            // Log the order placement
            AuditLog::createLog($user, 'order_placed', $order, [
                'pair' => $pair->symbol,
                'side' => $request->side,
                'type' => $request->type,
                'amount' => $amount,
                'price' => $price,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'pair_id' => $order->pair_id,
                        'side' => $order->side,
                        'type' => $order->type,
                        'price' => $order->price,
                        'amount' => $order->amount,
                        'filled' => $order->filled,
                        'remaining' => $order->remaining,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user orders
     */
    public function getOrders(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $pairId = $request->input('pair_id');
            $status = $request->input('status');
            $side = $request->input('side');
            $type = $request->input('type');

            $query = $user->orders()->with('tradingPair');

            if ($pairId) {
                $query->where('pair_id', $pairId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($side) {
                $query->where('side', $side);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders->items(),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, int $orderId): JsonResponse
    {
        try {
            $user = $request->user();
            $order = $user->orders()->findOrFail($orderId);

            if (!$order->isOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled.',
                ], 400);
            }

            $this->tradingService->cancelOrder($order);

            // Log the order cancellation
            AuditLog::createLog($user, 'order_cancelled', $order, [
                'pair' => $order->tradingPair->symbol,
                'side' => $order->side,
                'amount' => $order->amount,
                'price' => $order->price,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user trades
     */
    public function getUserTrades(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $pairId = $request->input('pair_id');

            $query = Trade::byUser($user->id)->with(['tradingPair', 'buyOrder', 'sellOrder']);

            if ($pairId) {
                $query->where('pair_id', $pairId);
            }

            $trades = $query->orderBy('timestamp', 'desc')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => [
                    'trades' => $trades->items(),
                    'pagination' => [
                        'current_page' => $trades->currentPage(),
                        'last_page' => $trades->lastPage(),
                        'per_page' => $trades->perPage(),
                        'total' => $trades->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trades.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}