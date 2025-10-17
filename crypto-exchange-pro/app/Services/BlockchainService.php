<?php

namespace App\Services;

use App\Models\BlockchainNetwork;
use App\Models\Cryptocurrency;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BlockchainService
{
    /**
     * Test connection to blockchain network
     */
    public function testConnection(BlockchainNetwork $network): array
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout($network->timeout ?? 30)
                ->post($network->rpc_url, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_blockNumber',
                    'params' => [],
                    'id' => 1,
                ]);

            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['result'])) {
                    $network->update([
                        'last_health_check' => now(),
                    ]);

                    return [
                        'status' => 'success',
                        'response_time' => $responseTime,
                        'block_number' => hexdec($data['result']),
                        'network' => $network->name,
                    ];
                } else {
                    throw new \Exception('Invalid response format');
                }
            } else {
                throw new \Exception('HTTP error: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("BlockchainService@testConnection for {$network->name}: " . $e->getMessage());
            
            $network->update([
                'last_health_check' => now(),
            ]);

            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'network' => $network->name,
            ];
        }
    }

    /**
     * Get blockchain network status
     */
    public function getNetworkStatus(BlockchainNetwork $network): array
    {
        try {
            $cacheKey = "blockchain_status_{$network->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $status = $this->testConnection($network);
            
            if ($status['status'] === 'success') {
                $blockNumber = $status['block_number'];
                $gasPrice = $this->getGasPrice($network);
                $peerCount = $this->getPeerCount($network);
                
                $networkStatus = [
                    'network' => $network->name,
                    'status' => 'healthy',
                    'block_number' => $blockNumber,
                    'gas_price' => $gasPrice,
                    'peer_count' => $peerCount,
                    'response_time' => $status['response_time'],
                    'last_updated' => now()->toISOString(),
                ];
            } else {
                $networkStatus = [
                    'network' => $network->name,
                    'status' => 'unhealthy',
                    'error' => $status['error'],
                    'last_updated' => now()->toISOString(),
                ];
            }

            Cache::put($cacheKey, $networkStatus, 60); // Cache for 1 minute
            
            return $networkStatus;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getNetworkStatus for {$network->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate wallet address for cryptocurrency
     */
    public function generateAddress(Cryptocurrency $crypto, string $userId): array
    {
        try {
            $network = $crypto->blockchainNetwork;
            
            if (!$network) {
                throw new \Exception("No blockchain network configured for {$crypto->symbol}");
            }

            $addressData = $this->generateAddressForNetwork($network, $crypto, $userId);
            
            // Create wallet if it doesn't exist
            $wallet = Wallet::firstOrCreate([
                'user_id' => $userId,
                'currency' => $crypto->symbol,
                'wallet_type' => 'spot',
            ]);

            // Create wallet address
            $walletAddress = WalletAddress::create([
                'wallet_id' => $wallet->id,
                'address' => $addressData['address'],
                'network' => $network->symbol,
                'tag' => $addressData['tag'] ?? null,
                'memo' => $addressData['memo'] ?? null,
                'is_active' => true,
                'is_verified' => true,
            ]);

            return [
                'address' => $addressData['address'],
                'tag' => $addressData['tag'] ?? null,
                'memo' => $addressData['memo'] ?? null,
                'network' => $network->symbol,
                'currency' => $crypto->symbol,
                'wallet_id' => $wallet->id,
            ];
        } catch (\Exception $e) {
            Log::error("BlockchainService@generateAddress for {$crypto->symbol}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get transaction details
     */
    public function getTransaction(BlockchainNetwork $network, string $txid): array
    {
        try {
            $cacheKey = "transaction_{$network->id}_{$txid}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $transactionData = $this->getTransactionFromNetwork($network, $txid);
            
            Cache::put($cacheKey, $transactionData, 300); // Cache for 5 minutes
            
            return $transactionData;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getTransaction for {$network->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get transaction confirmations
     */
    public function getConfirmations(BlockchainNetwork $network, string $txid): int
    {
        try {
            $transaction = $this->getTransaction($network, $txid);
            $currentBlock = $this->getCurrentBlockNumber($network);
            
            if (isset($transaction['block_number'])) {
                return $currentBlock - $transaction['block_number'] + 1;
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getConfirmations for {$network->name}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if transaction is confirmed
     */
    public function isTransactionConfirmed(BlockchainNetwork $network, string $txid): bool
    {
        try {
            $confirmations = $this->getConfirmations($network, $txid);
            return $confirmations >= $network->min_confirmations;
        } catch (\Exception $e) {
            Log::error("BlockchainService@isTransactionConfirmed for {$network->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get balance for address
     */
    public function getAddressBalance(BlockchainNetwork $network, string $address, string $currency): float
    {
        try {
            $cacheKey = "balance_{$network->id}_{$address}_{$currency}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $balance = $this->getBalanceFromNetwork($network, $address, $currency);
            
            Cache::put($cacheKey, $balance, 60); // Cache for 1 minute
            
            return $balance;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getAddressBalance for {$network->name}: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Send transaction
     */
    public function sendTransaction(BlockchainNetwork $network, array $transactionData): array
    {
        try {
            $txid = $this->sendTransactionToNetwork($network, $transactionData);
            
            return [
                'txid' => $txid,
                'status' => 'pending',
                'network' => $network->name,
                'created_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error("BlockchainService@sendTransaction for {$network->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Estimate gas for transaction
     */
    public function estimateGas(BlockchainNetwork $network, array $transactionData): array
    {
        try {
            $gasEstimate = $this->estimateGasFromNetwork($network, $transactionData);
            
            return [
                'gas_limit' => $gasEstimate['gas_limit'],
                'gas_price' => $gasEstimate['gas_price'],
                'gas_price_gwei' => $gasEstimate['gas_price_gwei'],
                'total_cost' => $gasEstimate['total_cost'],
                'network' => $network->name,
            ];
        } catch (\Exception $e) {
            Log::error("BlockchainService@estimateGas for {$network->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get gas price
     */
    public function getGasPrice(BlockchainNetwork $network): array
    {
        try {
            $cacheKey = "gas_price_{$network->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $gasPrice = $this->getGasPriceFromNetwork($network);
            
            Cache::put($cacheKey, $gasPrice, 30); // Cache for 30 seconds
            
            return $gasPrice;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getGasPrice for {$network->name}: " . $e->getMessage());
            return [
                'gas_price' => $network->gas_price,
                'gas_price_gwei' => $network->gas_price_gwei,
            ];
        }
    }

    /**
     * Get peer count
     */
    public function getPeerCount(BlockchainNetwork $network): int
    {
        try {
            $cacheKey = "peer_count_{$network->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $peerCount = $this->getPeerCountFromNetwork($network);
            
            Cache::put($cacheKey, $peerCount, 60); // Cache for 1 minute
            
            return $peerCount;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getPeerCount for {$network->name}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get current block number
     */
    public function getCurrentBlockNumber(BlockchainNetwork $network): int
    {
        try {
            $cacheKey = "block_number_{$network->id}";
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                return $cached;
            }

            $blockNumber = $this->getBlockNumberFromNetwork($network);
            
            Cache::put($cacheKey, $blockNumber, 10); // Cache for 10 seconds
            
            return $blockNumber;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getCurrentBlockNumber for {$network->name}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Validate address
     */
    public function validateAddress(BlockchainNetwork $network, string $address): bool
    {
        try {
            return $this->validateAddressForNetwork($network, $address);
        } catch (\Exception $e) {
            Log::error("BlockchainService@validateAddress for {$network->name}: " . $e->getMessage());
            return false;
        }
    }

    // Private helper methods

    private function generateAddressForNetwork(BlockchainNetwork $network, Cryptocurrency $crypto, string $userId): array
    {
        // This would contain network-specific address generation logic
        // For now, return a mock address
        return [
            'address' => $this->generateMockAddress($crypto->symbol),
            'tag' => null,
            'memo' => null,
        ];
    }

    private function getTransactionFromNetwork(BlockchainNetwork $network, string $txid): array
    {
        // This would contain network-specific transaction fetching logic
        // For now, return mock data
        return [
            'txid' => $txid,
            'block_number' => $this->getCurrentBlockNumber($network) - 1,
            'confirmations' => 1,
            'status' => 'confirmed',
            'from' => '0x0000000000000000000000000000000000000000',
            'to' => '0x0000000000000000000000000000000000000000',
            'amount' => 0.0,
            'fee' => 0.0,
            'gas_used' => 21000,
            'gas_price' => $network->gas_price,
        ];
    }

    private function getBalanceFromNetwork(BlockchainNetwork $network, string $address, string $currency): float
    {
        // This would contain network-specific balance fetching logic
        // For now, return mock data
        return 0.0;
    }

    private function sendTransactionToNetwork(BlockchainNetwork $network, array $transactionData): string
    {
        // This would contain network-specific transaction sending logic
        // For now, return mock txid
        return '0x' . bin2hex(random_bytes(32));
    }

    private function estimateGasFromNetwork(BlockchainNetwork $network, array $transactionData): array
    {
        // This would contain network-specific gas estimation logic
        // For now, return mock data
        return [
            'gas_limit' => $network->gas_limit,
            'gas_price' => $network->gas_price,
            'gas_price_gwei' => $network->gas_price_gwei,
            'total_cost' => $network->gas_limit * $network->gas_price,
        ];
    }

    private function getGasPriceFromNetwork(BlockchainNetwork $network): array
    {
        // This would contain network-specific gas price fetching logic
        // For now, return network default
        return [
            'gas_price' => $network->gas_price,
            'gas_price_gwei' => $network->gas_price_gwei,
        ];
    }

    private function getPeerCountFromNetwork(BlockchainNetwork $network): int
    {
        // This would contain network-specific peer count fetching logic
        // For now, return mock data
        return 10;
    }

    private function getBlockNumberFromNetwork(BlockchainNetwork $network): int
    {
        try {
            $response = Http::timeout($network->timeout ?? 30)
                ->post($network->rpc_url, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_blockNumber',
                    'params' => [],
                    'id' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['result'])) {
                    return hexdec($data['result']);
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error("BlockchainService@getBlockNumberFromNetwork for {$network->name}: " . $e->getMessage());
            return 0;
        }
    }

    private function validateAddressForNetwork(BlockchainNetwork $network, string $address): bool
    {
        // This would contain network-specific address validation logic
        // For now, return basic validation
        if ($network->symbol === 'ETH') {
            return preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
        } elseif ($network->symbol === 'BTC') {
            return preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address);
        }
        
        return strlen($address) > 10;
    }

    private function generateMockAddress(string $currency): string
    {
        // Generate mock addresses for different currencies
        if ($currency === 'ETH') {
            return '0x' . bin2hex(random_bytes(20));
        } elseif ($currency === 'BTC') {
            return '1' . bin2hex(random_bytes(20));
        } elseif ($currency === 'USDT') {
            return '0x' . bin2hex(random_bytes(20));
        }
        
        return '0x' . bin2hex(random_bytes(20));
    }
}