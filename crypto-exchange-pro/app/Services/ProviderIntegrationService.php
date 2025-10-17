<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Models\BlockchainNetwork;
use App\Models\Cryptocurrency;
use App\Models\TradingPair;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProviderIntegrationService
{
    /**
     * Test connection to a service provider
     */
    public function testConnection(ServiceProvider $provider): array
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $provider->api_key,
                    'Content-Type' => 'application/json',
                ])
                ->get($provider->api_endpoint . '/health');

            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($response->successful()) {
                $provider->update([
                    'health_status' => 'healthy',
                    'health_score' => max(0, 100 - ($responseTime / 10)),
                    'last_health_check' => now(),
                    'success_count' => $provider->success_count + 1,
                    'total_requests' => $provider->total_requests + 1,
                ]);

                return [
                    'status' => 'success',
                    'response_time' => $responseTime,
                    'health_score' => $provider->health_score,
                ];
            } else {
                $provider->update([
                    'health_status' => 'unhealthy',
                    'health_score' => 0,
                    'last_health_check' => now(),
                    'error_count' => $provider->error_count + 1,
                    'total_requests' => $provider->total_requests + 1,
                    'last_error' => $response->body(),
                ]);

                return [
                    'status' => 'error',
                    'response_time' => $responseTime,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            $provider->update([
                'health_status' => 'unhealthy',
                'health_score' => 0,
                'last_health_check' => now(),
                'error_count' => $provider->error_count + 1,
                'total_requests' => $provider->total_requests + 1,
                'last_error' => $e->getMessage(),
            ]);

            Log::error('ProviderIntegrationService@testConnection: ' . $e->getMessage());

            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get market data from provider
     */
    public function getMarketData(ServiceProvider $provider, string $symbol = null): array
    {
        try {
            $cacheKey = "market_data_{$provider->id}_{$symbol}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $endpoint = $this->getMarketDataEndpoint($provider, $symbol);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->get($endpoint);

            if ($response->successful()) {
                $data = $this->parseMarketData($provider, $response->json());
                
                Cache::put($cacheKey, $data, 60); // Cache for 1 minute
                
                return $data;
            } else {
                throw new \Exception('Failed to fetch market data: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@getMarketData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get order book from provider
     */
    public function getOrderBook(ServiceProvider $provider, string $symbol): array
    {
        try {
            $cacheKey = "order_book_{$provider->id}_{$symbol}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $endpoint = $this->getOrderBookEndpoint($provider, $symbol);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->get($endpoint);

            if ($response->successful()) {
                $data = $this->parseOrderBook($provider, $response->json());
                
                Cache::put($cacheKey, $data, 30); // Cache for 30 seconds
                
                return $data;
            } else {
                throw new \Exception('Failed to fetch order book: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@getOrderBook: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get trading pairs from provider
     */
    public function getTradingPairs(ServiceProvider $provider): array
    {
        try {
            $cacheKey = "trading_pairs_{$provider->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $endpoint = $this->getTradingPairsEndpoint($provider);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->get($endpoint);

            if ($response->successful()) {
                $data = $this->parseTradingPairs($provider, $response->json());
                
                Cache::put($cacheKey, $data, 300); // Cache for 5 minutes
                
                return $data;
            } else {
                throw new \Exception('Failed to fetch trading pairs: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@getTradingPairs: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get cryptocurrencies from provider
     */
    public function getCryptocurrencies(ServiceProvider $provider): array
    {
        try {
            $cacheKey = "cryptocurrencies_{$provider->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $endpoint = $this->getCryptocurrenciesEndpoint($provider);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->get($endpoint);

            if ($response->successful()) {
                $data = $this->parseCryptocurrencies($provider, $response->json());
                
                Cache::put($cacheKey, $data, 600); // Cache for 10 minutes
                
                return $data;
            } else {
                throw new \Exception('Failed to fetch cryptocurrencies: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@getCryptocurrencies: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Place order through provider
     */
    public function placeOrder(ServiceProvider $provider, array $orderData): array
    {
        try {
            $endpoint = $this->getPlaceOrderEndpoint($provider);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->post($endpoint, $orderData);

            if ($response->successful()) {
                return $this->parseOrderResponse($provider, $response->json());
            } else {
                throw new \Exception('Failed to place order: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@placeOrder: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel order through provider
     */
    public function cancelOrder(ServiceProvider $provider, string $orderId): array
    {
        try {
            $endpoint = $this->getCancelOrderEndpoint($provider, $orderId);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->delete($endpoint);

            if ($response->successful()) {
                return $this->parseOrderResponse($provider, $response->json());
            } else {
                throw new \Exception('Failed to cancel order: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@cancelOrder: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user balance from provider
     */
    public function getUserBalance(ServiceProvider $provider, string $userId): array
    {
        try {
            $endpoint = $this->getUserBalanceEndpoint($provider, $userId);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->get($endpoint);

            if ($response->successful()) {
                return $this->parseUserBalance($provider, $response->json());
            } else {
                throw new \Exception('Failed to get user balance: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@getUserBalance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create deposit address through provider
     */
    public function createDepositAddress(ServiceProvider $provider, string $currency, string $userId): array
    {
        try {
            $endpoint = $this->getCreateDepositAddressEndpoint($provider);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->post($endpoint, [
                    'currency' => $currency,
                    'user_id' => $userId,
                ]);

            if ($response->successful()) {
                return $this->parseDepositAddressResponse($provider, $response->json());
            } else {
                throw new \Exception('Failed to create deposit address: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@createDepositAddress: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process withdrawal through provider
     */
    public function processWithdrawal(ServiceProvider $provider, array $withdrawalData): array
    {
        try {
            $endpoint = $this->getProcessWithdrawalEndpoint($provider);
            
            $response = Http::timeout($provider->timeout)
                ->withHeaders($this->getAuthHeaders($provider))
                ->post($endpoint, $withdrawalData);

            if ($response->successful()) {
                return $this->parseWithdrawalResponse($provider, $response->json());
            } else {
                throw new \Exception('Failed to process withdrawal: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ProviderIntegrationService@processWithdrawal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get authentication headers for provider
     */
    private function getAuthHeaders(ServiceProvider $provider): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($provider->api_key) {
            $headers['Authorization'] = 'Bearer ' . $provider->api_key;
        }

        if ($provider->api_secret) {
            $headers['X-API-Secret'] = $provider->api_secret;
        }

        return $headers;
    }

    /**
     * Get market data endpoint based on provider type
     */
    private function getMarketDataEndpoint(ServiceProvider $provider, string $symbol = null): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'market_data' => $symbol ? "{$baseUrl}/api/v3/ticker/24hr?symbol={$symbol}" : "{$baseUrl}/api/v3/ticker/24hr",
            'liquidity' => $symbol ? "{$baseUrl}/v1/market/{$symbol}" : "{$baseUrl}/v1/markets",
            default => "{$baseUrl}/market-data",
        };
    }

    /**
     * Get order book endpoint based on provider type
     */
    private function getOrderBookEndpoint(ServiceProvider $provider, string $symbol): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'market_data' => "{$baseUrl}/api/v3/depth?symbol={$symbol}&limit=100",
            'liquidity' => "{$baseUrl}/v1/orderbook/{$symbol}",
            default => "{$baseUrl}/orderbook/{$symbol}",
        };
    }

    /**
     * Get trading pairs endpoint based on provider type
     */
    private function getTradingPairsEndpoint(ServiceProvider $provider): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'market_data' => "{$baseUrl}/api/v3/exchangeInfo",
            'liquidity' => "{$baseUrl}/v1/pairs",
            default => "{$baseUrl}/trading-pairs",
        };
    }

    /**
     * Get cryptocurrencies endpoint based on provider type
     */
    private function getCryptocurrenciesEndpoint(ServiceProvider $provider): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'market_data' => "{$baseUrl}/api/v3/coins",
            'liquidity' => "{$baseUrl}/v1/currencies",
            default => "{$baseUrl}/cryptocurrencies",
        };
    }

    /**
     * Get place order endpoint based on provider type
     */
    private function getPlaceOrderEndpoint(ServiceProvider $provider): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'liquidity' => "{$baseUrl}/v1/orders",
            'trading' => "{$baseUrl}/api/v3/order",
            default => "{$baseUrl}/orders",
        };
    }

    /**
     * Get cancel order endpoint based on provider type
     */
    private function getCancelOrderEndpoint(ServiceProvider $provider, string $orderId): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'liquidity' => "{$baseUrl}/v1/orders/{$orderId}",
            'trading' => "{$baseUrl}/api/v3/order?orderId={$orderId}",
            default => "{$baseUrl}/orders/{$orderId}",
        };
    }

    /**
     * Get user balance endpoint based on provider type
     */
    private function getUserBalanceEndpoint(ServiceProvider $provider, string $userId): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'liquidity' => "{$baseUrl}/v1/accounts/{$userId}/balance",
            'trading' => "{$baseUrl}/api/v3/account",
            default => "{$baseUrl}/users/{$userId}/balance",
        };
    }

    /**
     * Get create deposit address endpoint based on provider type
     */
    private function getCreateDepositAddressEndpoint(ServiceProvider $provider): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'liquidity' => "{$baseUrl}/v1/addresses",
            'trading' => "{$baseUrl}/api/v3/depositAddress",
            default => "{$baseUrl}/deposit-addresses",
        };
    }

    /**
     * Get process withdrawal endpoint based on provider type
     */
    private function getProcessWithdrawalEndpoint(ServiceProvider $provider): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        return match ($provider->type) {
            'liquidity' => "{$baseUrl}/v1/withdrawals",
            'trading' => "{$baseUrl}/api/v3/withdraw",
            default => "{$baseUrl}/withdrawals",
        };
    }

    /**
     * Parse market data based on provider type
     */
    private function parseMarketData(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'market_data' => $this->parseBinanceMarketData($data),
            'liquidity' => $this->parseLiquidityMarketData($data),
            default => $this->parseGenericMarketData($data),
        };
    }

    /**
     * Parse order book based on provider type
     */
    private function parseOrderBook(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'market_data' => $this->parseBinanceOrderBook($data),
            'liquidity' => $this->parseLiquidityOrderBook($data),
            default => $this->parseGenericOrderBook($data),
        };
    }

    /**
     * Parse trading pairs based on provider type
     */
    private function parseTradingPairs(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'market_data' => $this->parseBinanceTradingPairs($data),
            'liquidity' => $this->parseLiquidityTradingPairs($data),
            default => $this->parseGenericTradingPairs($data),
        };
    }

    /**
     * Parse cryptocurrencies based on provider type
     */
    private function parseCryptocurrencies(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'market_data' => $this->parseBinanceCryptocurrencies($data),
            'liquidity' => $this->parseLiquidityCryptocurrencies($data),
            default => $this->parseGenericCryptocurrencies($data),
        };
    }

    /**
     * Parse order response based on provider type
     */
    private function parseOrderResponse(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'liquidity' => $this->parseLiquidityOrderResponse($data),
            'trading' => $this->parseBinanceOrderResponse($data),
            default => $this->parseGenericOrderResponse($data),
        };
    }

    /**
     * Parse user balance based on provider type
     */
    private function parseUserBalance(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'liquidity' => $this->parseLiquidityUserBalance($data),
            'trading' => $this->parseBinanceUserBalance($data),
            default => $this->parseGenericUserBalance($data),
        };
    }

    /**
     * Parse deposit address response based on provider type
     */
    private function parseDepositAddressResponse(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'liquidity' => $this->parseLiquidityDepositAddressResponse($data),
            'trading' => $this->parseBinanceDepositAddressResponse($data),
            default => $this->parseGenericDepositAddressResponse($data),
        };
    }

    /**
     * Parse withdrawal response based on provider type
     */
    private function parseWithdrawalResponse(ServiceProvider $provider, array $data): array
    {
        return match ($provider->type) {
            'liquidity' => $this->parseLiquidityWithdrawalResponse($data),
            'trading' => $this->parseBinanceWithdrawalResponse($data),
            default => $this->parseGenericWithdrawalResponse($data),
        };
    }

    // Binance-specific parsers
    private function parseBinanceMarketData(array $data): array
    {
        if (isset($data[0])) {
            return array_map(function ($item) {
                return [
                    'symbol' => $item['symbol'],
                    'price' => (float) $item['lastPrice'],
                    'change_24h' => (float) $item['priceChangePercent'],
                    'volume_24h' => (float) $item['volume'],
                    'high_24h' => (float) $item['highPrice'],
                    'low_24h' => (float) $item['lowPrice'],
                ];
            }, $data);
        } else {
            return [
                'symbol' => $data['symbol'],
                'price' => (float) $data['lastPrice'],
                'change_24h' => (float) $data['priceChangePercent'],
                'volume_24h' => (float) $data['volume'],
                'high_24h' => (float) $data['highPrice'],
                'low_24h' => (float) $data['lowPrice'],
            ];
        }
    }

    private function parseBinanceOrderBook(array $data): array
    {
        return [
            'bids' => array_map(function ($bid) {
                return [
                    'price' => (float) $bid[0],
                    'amount' => (float) $bid[1],
                    'total' => (float) $bid[0] * (float) $bid[1],
                ];
            }, $data['bids']),
            'asks' => array_map(function ($ask) {
                return [
                    'price' => (float) $ask[0],
                    'amount' => (float) $ask[1],
                    'total' => (float) $ask[0] * (float) $ask[1],
                ];
            }, $data['asks']),
        ];
    }

    private function parseBinanceTradingPairs(array $data): array
    {
        return array_map(function ($symbol) {
            return [
                'symbol' => $symbol['symbol'],
                'base_currency' => $symbol['baseAsset'],
                'quote_currency' => $symbol['quoteAsset'],
                'is_active' => $symbol['status'] === 'TRADING',
                'min_trade_amount' => (float) $symbol['filters'][0]['minQty'] ?? 0,
                'max_trade_amount' => (float) $symbol['filters'][0]['maxQty'] ?? 0,
                'tick_size' => (float) $symbol['filters'][0]['tickSize'] ?? 0.01,
                'step_size' => (float) $symbol['filters'][0]['stepSize'] ?? 0.0001,
            ];
        }, $data['symbols']);
    }

    private function parseBinanceCryptocurrencies(array $data): array
    {
        return array_map(function ($coin) {
            return [
                'symbol' => $coin['symbol'],
                'name' => $coin['name'],
                'is_active' => $coin['isActive'],
                'is_tradable' => $coin['isTradingAllowed'],
                'is_depositable' => $coin['isDepositAllowed'],
                'is_withdrawable' => $coin['isWithdrawAllowed'],
                'min_deposit' => (float) $coin['minDeposit'] ?? 0,
                'min_withdrawal' => (float) $coin['minWithdraw'] ?? 0,
                'withdrawal_fee' => (float) $coin['withdrawFee'] ?? 0,
            ];
        }, $data);
    }

    private function parseBinanceOrderResponse(array $data): array
    {
        return [
            'order_id' => $data['orderId'],
            'symbol' => $data['symbol'],
            'side' => strtolower($data['side']),
            'type' => strtolower($data['type']),
            'price' => (float) $data['price'],
            'amount' => (float) $data['origQty'],
            'filled' => (float) $data['executedQty'],
            'remaining' => (float) $data['origQty'] - (float) $data['executedQty'],
            'status' => strtolower($data['status']),
            'created_at' => now()->parse($data['time'])->toISOString(),
        ];
    }

    private function parseBinanceUserBalance(array $data): array
    {
        return array_map(function ($balance) {
            return [
                'currency' => $balance['asset'],
                'available' => (float) $balance['free'],
                'reserved' => (float) $balance['locked'],
                'total' => (float) $balance['free'] + (float) $balance['locked'],
            ];
        }, $data['balances']);
    }

    private function parseBinanceDepositAddressResponse(array $data): array
    {
        return [
            'address' => $data['address'],
            'tag' => $data['tag'] ?? null,
            'currency' => $data['coin'],
            'network' => $data['network'] ?? 'mainnet',
        ];
    }

    private function parseBinanceWithdrawalResponse(array $data): array
    {
        return [
            'withdrawal_id' => $data['id'],
            'txid' => $data['txId'] ?? null,
            'status' => strtolower($data['status']),
            'amount' => (float) $data['amount'],
            'fee' => (float) $data['transactionFee'],
            'address' => $data['address'],
            'created_at' => now()->parse($data['applyTime'])->toISOString(),
        ];
    }

    // Liquidity provider parsers
    private function parseLiquidityMarketData(array $data): array
    {
        if (isset($data['data'][0])) {
            return array_map(function ($item) {
                return [
                    'symbol' => $item['symbol'],
                    'price' => (float) $item['price'],
                    'change_24h' => (float) $item['change_24h'],
                    'volume_24h' => (float) $item['volume_24h'],
                    'high_24h' => (float) $item['high_24h'],
                    'low_24h' => (float) $item['low_24h'],
                ];
            }, $data['data']);
        } else {
            return [
                'symbol' => $data['symbol'],
                'price' => (float) $data['price'],
                'change_24h' => (float) $data['change_24h'],
                'volume_24h' => (float) $data['volume_24h'],
                'high_24h' => (float) $data['high_24h'],
                'low_24h' => (float) $data['low_24h'],
            ];
        }
    }

    private function parseLiquidityOrderBook(array $data): array
    {
        return [
            'bids' => array_map(function ($bid) {
                return [
                    'price' => (float) $bid['price'],
                    'amount' => (float) $bid['amount'],
                    'total' => (float) $bid['total'],
                ];
            }, $data['bids']),
            'asks' => array_map(function ($ask) {
                return [
                    'price' => (float) $ask['price'],
                    'amount' => (float) $ask['amount'],
                    'total' => (float) $ask['total'],
                ];
            }, $data['asks']),
        ];
    }

    private function parseLiquidityTradingPairs(array $data): array
    {
        return array_map(function ($pair) {
            return [
                'symbol' => $pair['symbol'],
                'base_currency' => $pair['base_currency'],
                'quote_currency' => $pair['quote_currency'],
                'is_active' => $pair['is_active'],
                'min_trade_amount' => (float) $pair['min_trade_amount'],
                'max_trade_amount' => (float) $pair['max_trade_amount'],
                'tick_size' => (float) $pair['tick_size'],
                'step_size' => (float) $pair['step_size'],
            ];
        }, $data['pairs']);
    }

    private function parseLiquidityCryptocurrencies(array $data): array
    {
        return array_map(function ($crypto) {
            return [
                'symbol' => $crypto['symbol'],
                'name' => $crypto['name'],
                'is_active' => $crypto['is_active'],
                'is_tradable' => $crypto['is_tradable'],
                'is_depositable' => $crypto['is_depositable'],
                'is_withdrawable' => $crypto['is_withdrawable'],
                'min_deposit' => (float) $crypto['min_deposit'],
                'min_withdrawal' => (float) $crypto['min_withdrawal'],
                'withdrawal_fee' => (float) $crypto['withdrawal_fee'],
            ];
        }, $data['currencies']);
    }

    private function parseLiquidityOrderResponse(array $data): array
    {
        return [
            'order_id' => $data['order_id'],
            'symbol' => $data['symbol'],
            'side' => $data['side'],
            'type' => $data['type'],
            'price' => (float) $data['price'],
            'amount' => (float) $data['amount'],
            'filled' => (float) $data['filled'],
            'remaining' => (float) $data['remaining'],
            'status' => $data['status'],
            'created_at' => $data['created_at'],
        ];
    }

    private function parseLiquidityUserBalance(array $data): array
    {
        return array_map(function ($balance) {
            return [
                'currency' => $balance['currency'],
                'available' => (float) $balance['available'],
                'reserved' => (float) $balance['reserved'],
                'total' => (float) $balance['total'],
            ];
        }, $data['balances']);
    }

    private function parseLiquidityDepositAddressResponse(array $data): array
    {
        return [
            'address' => $data['address'],
            'tag' => $data['tag'] ?? null,
            'currency' => $data['currency'],
            'network' => $data['network'] ?? 'mainnet',
        ];
    }

    private function parseLiquidityWithdrawalResponse(array $data): array
    {
        return [
            'withdrawal_id' => $data['withdrawal_id'],
            'txid' => $data['txid'] ?? null,
            'status' => $data['status'],
            'amount' => (float) $data['amount'],
            'fee' => (float) $data['fee'],
            'address' => $data['address'],
            'created_at' => $data['created_at'],
        ];
    }

    // Generic parsers
    private function parseGenericMarketData(array $data): array
    {
        return $data;
    }

    private function parseGenericOrderBook(array $data): array
    {
        return $data;
    }

    private function parseGenericTradingPairs(array $data): array
    {
        return $data;
    }

    private function parseGenericCryptocurrencies(array $data): array
    {
        return $data;
    }

    private function parseGenericOrderResponse(array $data): array
    {
        return $data;
    }

    private function parseGenericUserBalance(array $data): array
    {
        return $data;
    }

    private function parseGenericDepositAddressResponse(array $data): array
    {
        return $data;
    }

    private function parseGenericWithdrawalResponse(array $data): array
    {
        return $data;
    }
}