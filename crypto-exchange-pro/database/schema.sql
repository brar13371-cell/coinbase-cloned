-- CryptoExchange Pro Database Schema
-- Production-ready database structure for cryptocurrency exchange

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `crypto_exchange` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `crypto_exchange`;

-- Users table
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `status` enum('active','suspended','banned','pending') NOT NULL DEFAULT 'pending',
  `kyc_status` enum('none','pending','approved','rejected','expired') NOT NULL DEFAULT 'none',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `backup_codes` text DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_kyc_status_index` (`kyc_status`),
  KEY `users_status_index` (`status`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User profiles table
CREATE TABLE `user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `ssn_hash` varchar(255) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `source_of_funds` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallets table
CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(10) NOT NULL,
  `balance_available` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_reserved` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_total` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `wallet_type` enum('spot','futures','margin','staking') NOT NULL DEFAULT 'spot',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_currency_type_unique` (`user_id`,`currency`,`wallet_type`),
  KEY `wallets_currency_index` (`currency`),
  KEY `wallets_wallet_type_index` (`wallet_type`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallet addresses table
CREATE TABLE `wallet_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `network` varchar(50) NOT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_addresses_address_network_unique` (`address`,`network`),
  KEY `wallet_addresses_wallet_id_index` (`wallet_id`),
  CONSTRAINT `wallet_addresses_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table
CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('deposit','withdrawal','trade','fee','reward','refund','transfer') NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `amount` decimal(20,8) NOT NULL,
  `fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `txid` varchar(255) DEFAULT NULL,
  `block_height` bigint(20) DEFAULT NULL,
  `confirmations` int(11) NOT NULL DEFAULT 0,
  `required_confirmations` int(11) NOT NULL DEFAULT 1,
  `provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider_txid` varchar(255) DEFAULT NULL,
  `from_address` varchar(255) DEFAULT NULL,
  `to_address` varchar(255) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_index` (`user_id`),
  KEY `transactions_wallet_id_index` (`wallet_id`),
  KEY `transactions_type_index` (`type`),
  KEY `transactions_status_index` (`status`),
  KEY `transactions_currency_index` (`currency`),
  KEY `transactions_txid_index` (`txid`),
  KEY `transactions_provider_id_index` (`provider_id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trading pairs table
CREATE TABLE `trading_pairs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `base_currency` varchar(10) NOT NULL,
  `quote_currency` varchar(10) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `min_trade_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `max_trade_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `tick_size` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `step_size` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `maker_fee` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `taker_fee` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `price_precision` int(11) NOT NULL DEFAULT 8,
  `quantity_precision` int(11) NOT NULL DEFAULT 8,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trading_pairs_symbol_unique` (`symbol`),
  KEY `trading_pairs_base_currency_index` (`base_currency`),
  KEY `trading_pairs_quote_currency_index` (`quote_currency`),
  KEY `trading_pairs_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `type` enum('market','limit','stop','stop_limit') NOT NULL,
  `price` decimal(20,8) DEFAULT NULL,
  `stop_price` decimal(20,8) DEFAULT NULL,
  `amount` decimal(20,8) NOT NULL,
  `filled` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `remaining` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `status` enum('pending','open','partially_filled','filled','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `time_in_force` enum('GTC','IOC','FOK','GTD') NOT NULL DEFAULT 'GTC',
  `expires_at` timestamp NULL DEFAULT NULL,
  `filled_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_pair_id_index` (`pair_id`),
  KEY `orders_side_index` (`side`),
  KEY `orders_type_index` (`type`),
  KEY `orders_status_index` (`status`),
  KEY `orders_created_at_index` (`created_at`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trades table
CREATE TABLE `trades` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `buy_order_id` bigint(20) UNSIGNED NOT NULL,
  `sell_order_id` bigint(20) UNSIGNED NOT NULL,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `buyer_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `seller_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trades_buy_order_id_index` (`buy_order_id`),
  KEY `trades_sell_order_id_index` (`sell_order_id`),
  KEY `trades_pair_id_index` (`pair_id`),
  KEY `trades_timestamp_index` (`timestamp`),
  CONSTRAINT `trades_buy_order_id_foreign` FOREIGN KEY (`buy_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trades_sell_order_id_foreign` FOREIGN KEY (`sell_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trades_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Providers table
CREATE TABLE `providers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('market_data','liquidity','payment','kyc','notification','custody') NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `config` json DEFAULT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `webhook_secret` varchar(255) DEFAULT NULL,
  `last_health_check` timestamp NULL DEFAULT NULL,
  `health_status` enum('healthy','unhealthy','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `providers_type_index` (`type`),
  KEY `providers_is_enabled_index` (`is_enabled`),
  KEY `providers_priority_index` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE `admin_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','super_admin','support','compliance','finance') NOT NULL DEFAULT 'admin',
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table
CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_type` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_type` varchar(50) DEFAULT NULL,
  `details` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_actor_index` (`actor_id`,`actor_type`),
  KEY `audit_logs_subject_index` (`subject_id`,`subject_type`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC requests table
CREATE TABLE `kyc_requests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider_request_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','approved','rejected','expired') NOT NULL DEFAULT 'pending',
  `document_type` enum('passport','drivers_license','national_id','utility_bill','bank_statement') DEFAULT NULL,
  `document_front` varchar(255) DEFAULT NULL,
  `document_back` varchar(255) DEFAULT NULL,
  `selfie` varchar(255) DEFAULT NULL,
  `provider_response` json DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kyc_requests_user_id_index` (`user_id`),
  KEY `kyc_requests_provider_id_index` (`provider_id`),
  KEY `kyc_requests_status_index` (`status`),
  CONSTRAINT `kyc_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kyc_requests_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuration table
CREATE TABLE `config` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','boolean','json','decimal') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Market data table
CREATE TABLE `market_data` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `volume_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `change_24h` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `high_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `low_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `market_data_pair_id_index` (`pair_id`),
  KEY `market_data_timestamp_index` (`timestamp`),
  CONSTRAINT `market_data_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order book table
CREATE TABLE `order_book` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_book_pair_side_index` (`pair_id`,`side`),
  KEY `order_book_price_index` (`price`),
  KEY `order_book_timestamp_index` (`timestamp`),
  CONSTRAINT `order_book_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('trade','deposit','withdrawal','kyc','security','general') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_is_read_index` (`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial data
INSERT INTO `trading_pairs` (`base_currency`, `quote_currency`, `symbol`, `is_active`, `min_trade_amount`, `max_trade_amount`, `tick_size`, `step_size`, `maker_fee`, `taker_fee`, `price_precision`, `quantity_precision`) VALUES
('BTC', 'USD', 'BTCUSD', 1, 0.0001, 1000.0000, 0.01, 0.0001, 0.0010, 0.0010, 2, 8),
('ETH', 'USD', 'ETHUSD', 1, 0.001, 10000.0000, 0.01, 0.001, 0.0010, 0.0010, 2, 8),
('BTC', 'USDT', 'BTCUSDT', 1, 0.0001, 1000.0000, 0.01, 0.0001, 0.0010, 0.0010, 2, 8),
('ETH', 'USDT', 'ETHUSDT', 1, 0.001, 10000.0000, 0.01, 0.001, 0.0010, 0.0010, 2, 8);

INSERT INTO `providers` (`name`, `type`, `is_enabled`, `priority`, `config`) VALUES
('Binance', 'market_data', 1, 1, '{"api_url": "https://api.binance.com", "rate_limit": 1200}'),
('CoinGecko', 'market_data', 1, 2, '{"api_url": "https://api.coingecko.com", "rate_limit": 50}'),
('Stripe', 'payment', 1, 1, '{"api_url": "https://api.stripe.com", "webhook_tolerance": 300}'),
('Jumio', 'kyc', 1, 1, '{"api_url": "https://netverify.com", "timeout": 30}'),
('SendGrid', 'notification', 1, 1, '{"api_url": "https://api.sendgrid.com", "rate_limit": 100}');

INSERT INTO `config` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('exchange_name', 'CryptoExchange Pro', 'string', 'Exchange display name', 1),
('default_currency', 'USD', 'string', 'Default fiat currency', 1),
('trading_enabled', 'true', 'boolean', 'Enable/disable trading', 0),
('withdrawals_enabled', 'true', 'boolean', 'Enable/disable withdrawals', 0),
('kyc_required', 'true', 'boolean', 'Require KYC for trading', 0),
('min_trade_amount', '10', 'decimal', 'Minimum trade amount in USD', 1),
('max_trade_amount', '1000000', 'decimal', 'Maximum trade amount in USD', 1),
('trading_fee_percentage', '0.1', 'decimal', 'Default trading fee percentage', 1);

-- Create admin user (password: admin123)
INSERT INTO `admin_users` (`email`, `password`, `role`, `permissions`, `is_active`) VALUES
('admin@cryptoexchange.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '["*"]', 1);

COMMIT;