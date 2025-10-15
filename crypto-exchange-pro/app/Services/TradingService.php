<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Trade;
use App\Models\TradingPair;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\OrderBook;
use App\Models\MarketData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TradingService
{
    /**
     * Place a new order
     */
    public function placeOrder(User $user, TradingPair $pair, array $data): Order
    {
        return DB::transaction(function () use ($user, $pair, $data) {
            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'pair_id' => $pair->id,
                'side' => $data['side'],
                'type' => $data['type'],
                'price' => $data['price'],
                'stop_price' => $data['stop_price'] ?? null,
                'amount' => $data['amount'],
                'remaining' => $data['amount'],
                'time_in_force' => $data['time_in_force'] ?? 'GTC',
                'expires_at' => $data['expires_at'] ?? null,
                'status' => 'pending',
            ]);

            // Process the order
            $this->processOrder($order);

            return $order;
        });
    }

    /**
     * Process an order
     */
    public function processOrder(Order $order): void
    {
        try {
            // Check if order is expired
            if ($order->isExpired()) {
                $order->update(['status' => 'cancelled']);
                return;
            }

            // For market orders, execute immediately
            if ($order->type === 'market') {
                $this->executeMarketOrder($order);
            } else {
                // For limit orders, add to order book
                $this->addToOrderBook($order);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process order {$order->id}: " . $e->getMessage());
            $order->update(['status' => 'rejected']);
        }
    }

    /**
     * Execute a market order
     */
    private function executeMarketOrder(Order $order): void
    {
        $pair = $order->tradingPair;
        $oppositeSide = $order->side === 'buy' ? 'sell' : 'buy';

        // Find matching orders
        $matchingOrders = Order::where('pair_id', $pair->id)
            ->where('side', $oppositeSide)
            ->where('status', 'open')
            ->where('remaining', '>', 0)
            ->orderBy('price', $order->side === 'buy' ? 'asc' : 'desc')
            ->get();

        $remainingAmount = $order->amount;
        $totalFilled = 0;
        $totalValue = 0;

        foreach ($matchingOrders as $matchingOrder) {
            if ($remainingAmount <= 0) break;

            $fillAmount = min($remainingAmount, $matchingOrder->remaining);
            $fillPrice = $matchingOrder->price;

            // Create trade
            $this->createTrade($order, $matchingOrder, $fillAmount, $fillPrice);

            // Update order amounts
            $remainingAmount -= $fillAmount;
            $totalFilled += $fillAmount;
            $totalValue += $fillAmount * $fillPrice;

            // Update matching order
            $matchingOrder->fillPartially($fillAmount, $fillPrice);
        }

        // Update the market order
        if ($totalFilled > 0) {
            $order->update([
                'filled' => $totalFilled,
                'remaining' => $order->amount - $totalFilled,
                'status' => $remainingAmount > 0 ? 'partially_filled' : 'filled',
                'filled_at' => $remainingAmount <= 0 ? now() : null,
            ]);

            // Update wallets
            $this->updateWallets($order, $totalFilled, $totalValue);
        } else {
            $order->update(['status' => 'rejected']);
        }
    }

    /**
     * Add order to order book
     */
    private function addToOrderBook(Order $order): void
    {
        // Check if there are matching orders
        $this->checkForMatches($order);

        // If order is still open, add to order book
        if ($order->status === 'pending') {
            $order->update(['status' => 'open']);

            // Add to order book
            OrderBook::create([
                'pair_id' => $order->pair_id,
                'side' => $order->side,
                'price' => $order->price,
                'amount' => $order->remaining,
            ]);
        }
    }

    /**
     * Check for matching orders
     */
    private function checkForMatches(Order $order): void
    {
        $pair = $order->tradingPair;
        $oppositeSide = $order->side === 'buy' ? 'sell' : 'buy';

        // Find matching orders
        $matchingOrders = Order::where('pair_id', $pair->id)
            ->where('side', $oppositeSide)
            ->where('status', 'open')
            ->where('remaining', '>', 0)
            ->orderBy('price', $order->side === 'buy' ? 'asc' : 'desc')
            ->get();

        $remainingAmount = $order->amount;
        $totalFilled = 0;
        $totalValue = 0;

        foreach ($matchingOrders as $matchingOrder) {
            if ($remainingAmount <= 0) break;

            // Check if prices match
            if ($order->side === 'buy' && $order->price < $matchingOrder->price) break;
            if ($order->side === 'sell' && $order->price > $matchingOrder->price) break;

            $fillAmount = min($remainingAmount, $matchingOrder->remaining);
            $fillPrice = $matchingOrder->price;

            // Create trade
            $this->createTrade($order, $matchingOrder, $fillAmount, $fillPrice);

            // Update order amounts
            $remainingAmount -= $fillAmount;
            $totalFilled += $fillAmount;
            $totalValue += $fillAmount * $fillPrice;

            // Update matching order
            $matchingOrder->fillPartially($fillAmount, $fillPrice);
        }

        // Update the order
        if ($totalFilled > 0) {
            $order->update([
                'filled' => $totalFilled,
                'remaining' => $order->amount - $totalFilled,
                'status' => $remainingAmount > 0 ? 'partially_filled' : 'filled',
                'filled_at' => $remainingAmount <= 0 ? now() : null,
            ]);

            // Update wallets
            $this->updateWallets($order, $totalFilled, $totalValue);
        }
    }

    /**
     * Create a trade
     */
    private function createTrade(Order $buyOrder, Order $sellOrder, float $amount, float $price): Trade
    {
        $pair = $buyOrder->tradingPair;
        $buyerFee = $amount * $price * $pair->taker_fee / 100;
        $sellerFee = $amount * $price * $pair->taker_fee / 100;

        return Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'pair_id' => $pair->id,
            'price' => $price,
            'amount' => $amount,
            'buyer_fee' => $buyerFee,
            'seller_fee' => $sellerFee,
            'timestamp' => now(),
        ]);
    }

    /**
     * Update wallets after trade
     */
    private function updateWallets(Order $order, float $amount, float $value): void
    {
        $pair = $order->tradingPair;
        $user = $order->user;

        if ($order->side === 'buy') {
            // User is buying base currency with quote currency
            $baseWallet = $user->getWallet($pair->base_currency);
            $quoteWallet = $user->getWallet($pair->quote_currency);

            if ($baseWallet) {
                $baseWallet->addBalance($amount);
            }

            if ($quoteWallet) {
                $quoteWallet->subtractBalance($value);
            }
        } else {
            // User is selling base currency for quote currency
            $baseWallet = $user->getWallet($pair->base_currency);
            $quoteWallet = $user->getWallet($pair->quote_currency);

            if ($baseWallet) {
                $baseWallet->subtractBalance($amount);
            }

            if ($quoteWallet) {
                $quoteWallet->addBalance($value);
            }
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            if (!$order->isOpen()) {
                return false;
            }

            // Release reserved balance
            $this->releaseReservedBalance($order);

            // Remove from order book
            OrderBook::where('pair_id', $order->pair_id)
                ->where('side', $order->side)
                ->where('price', $order->price)
                ->where('amount', $order->remaining)
                ->delete();

            // Cancel the order
            return $order->cancel();
        });
    }

    /**
     * Release reserved balance for cancelled order
     */
    private function releaseReservedBalance(Order $order): void
    {
        $pair = $order->tradingPair;
        $user = $order->user;

        if ($order->side === 'buy') {
            // Release quote currency
            $quoteWallet = $user->getWallet($pair->quote_currency);
            if ($quoteWallet) {
                $quoteWallet->releaseBalance($order->remaining * $order->price);
            }
        } else {
            // Release base currency
            $baseWallet = $user->getWallet($pair->base_currency);
            if ($baseWallet) {
                $baseWallet->releaseBalance($order->remaining);
            }
        }
    }

    /**
     * Update market data
     */
    public function updateMarketData(TradingPair $pair, array $data): void
    {
        MarketData::create([
            'pair_id' => $pair->id,
            'price' => $data['price'],
            'volume_24h' => $data['volume_24h'] ?? 0,
            'change_24h' => $data['change_24h'] ?? 0,
            'high_24h' => $data['high_24h'] ?? $data['price'],
            'low_24h' => $data['low_24h'] ?? $data['price'],
            'timestamp' => now(),
        ]);
    }
}