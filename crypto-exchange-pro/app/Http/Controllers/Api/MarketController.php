<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TradingPair;
use App\Models\MarketData;
use App\Models\OrderBook;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketController extends Controller
{
    /**
     * Get all trading pairs
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
     * Get market tickers
     */
    public function getTickers(Request $request): JsonResponse
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
                        'symbol' => $pair->symbol,
                        'price' => $marketData ? $marketData->price : 0,
                        'change_24h' => $marketData ? $marketData->change_24h : 0,
                        'volume_24h' => $marketData ? $marketData->volume_24h : 0,
                        'high_24h' => $marketData ? $marketData->high_24h : 0,
                        'low_24h' => $marketData ? $marketData->low_24h : 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'tickers' => $pairs,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch market tickers.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}