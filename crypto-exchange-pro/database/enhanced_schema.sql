-- Enhanced CryptoExchange Pro Database Schema
-- Enterprise-grade cryptocurrency exchange with admin god powers
-- Supports all modern features: futures, margin, staking, DeFi, multi-blockchain

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `crypto_exchange_pro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `crypto_exchange_pro`;

-- Enhanced users table with more fields
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('user','admin','super_admin','support','compliance','finance','developer') NOT NULL DEFAULT 'user',
  `status` enum('active','suspended','banned','pending','restricted','kyc_required') NOT NULL DEFAULT 'pending',
  `kyc_status` enum('none','pending','approved','rejected','expired','under_review') NOT NULL DEFAULT 'none',
  `kyc_level` enum('basic','intermediate','advanced','institutional') NOT NULL DEFAULT 'basic',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `backup_codes` text DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `api_permissions` json DEFAULT NULL,
  `trading_permissions` json DEFAULT NULL,
  `withdrawal_limits` json DEFAULT NULL,
  `deposit_limits` json DEFAULT NULL,
  `risk_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `tier_level` enum('bronze','silver','gold','platinum','diamond','vip') NOT NULL DEFAULT 'bronze',
  `vip_status` tinyint(1) NOT NULL DEFAULT 0,
  `vip_expires_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_api_key_unique` (`api_key`),
  UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  KEY `users_kyc_status_index` (`kyc_status`),
  KEY `users_status_index` (`status`),
  KEY `users_role_index` (`role`),
  KEY `users_tier_level_index` (`tier_level`),
  KEY `users_referred_by_index` (`referred_by`),
  CONSTRAINT `users_referred_by_foreign` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced user profiles table
CREATE TABLE `user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_2` text DEFAULT NULL,
  `ssn_hash` varchar(255) DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `employer` varchar(100) DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `source_of_funds` varchar(100) DEFAULT NULL,
  `political_exposure` tinyint(1) NOT NULL DEFAULT 0,
  `pep_details` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `preferred_language` varchar(10) NOT NULL DEFAULT 'en',
  `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
  `marketing_consent` tinyint(1) NOT NULL DEFAULT 0,
  `sms_consent` tinyint(1) NOT NULL DEFAULT 0,
  `email_consent` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced wallets table with multi-type support
CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(10) NOT NULL,
  `balance_available` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_reserved` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_total` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_borrowed` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_lent` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_staked` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `balance_rewards` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `wallet_type` enum('spot','futures','margin','staking','lending','savings','defi','nft') NOT NULL DEFAULT 'spot',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_frozen` tinyint(1) NOT NULL DEFAULT 0,
  `freeze_reason` varchar(255) DEFAULT NULL,
  `freeze_expires_at` timestamp NULL DEFAULT NULL,
  `interest_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `last_interest_calculation` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_currency_type_unique` (`user_id`,`currency`,`wallet_type`),
  KEY `wallets_currency_index` (`currency`),
  KEY `wallets_wallet_type_index` (`wallet_type`),
  KEY `wallets_is_active_index` (`is_active`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced wallet addresses table
CREATE TABLE `wallet_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `network` varchar(50) NOT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_method` varchar(50) DEFAULT NULL,
  `verification_data` json DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_addresses_address_network_unique` (`address`,`network`),
  KEY `wallet_addresses_wallet_id_index` (`wallet_id`),
  KEY `wallet_addresses_is_active_index` (`is_active`),
  CONSTRAINT `wallet_addresses_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced transactions table
CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('deposit','withdrawal','trade','fee','reward','refund','transfer','staking','lending','borrowing','margin_call','liquidation','airdrop','fork','mining','referral','bonus','penalty','adjustment') NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled','expired','requires_approval') NOT NULL DEFAULT 'pending',
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
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
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

-- Enhanced trading pairs table
CREATE TABLE `trading_pairs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `base_currency` varchar(10) NOT NULL,
  `quote_currency` varchar(10) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_spot_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_futures_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_margin_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_staking_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `min_trade_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `max_trade_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `tick_size` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `step_size` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `maker_fee` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `taker_fee` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `price_precision` int(11) NOT NULL DEFAULT 8,
  `quantity_precision` int(11) NOT NULL DEFAULT 8,
  `margin_ratio` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `liquidation_ratio` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `funding_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `funding_interval` int(11) NOT NULL DEFAULT 8,
  `stake_apy` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `min_stake_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `max_stake_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `stake_lock_period` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trading_pairs_symbol_unique` (`symbol`),
  KEY `trading_pairs_base_currency_index` (`base_currency`),
  KEY `trading_pairs_quote_currency_index` (`quote_currency`),
  KEY `trading_pairs_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced orders table
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `type` enum('market','limit','stop','stop_limit','trailing_stop','iceberg','twap','vwap') NOT NULL,
  `price` decimal(20,8) DEFAULT NULL,
  `stop_price` decimal(20,8) DEFAULT NULL,
  `trailing_distance` decimal(20,8) DEFAULT NULL,
  `amount` decimal(20,8) NOT NULL,
  `filled` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `remaining` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `status` enum('pending','open','partially_filled','filled','cancelled','rejected','expired') NOT NULL DEFAULT 'pending',
  `time_in_force` enum('GTC','IOC','FOK','GTD') NOT NULL DEFAULT 'GTC',
  `expires_at` timestamp NULL DEFAULT NULL,
  `filled_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `client_order_id` varchar(100) DEFAULT NULL,
  `parent_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_post_only` tinyint(1) NOT NULL DEFAULT 0,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `is_iceberg` tinyint(1) NOT NULL DEFAULT 0,
  `iceberg_visible_size` decimal(20,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_pair_id_index` (`pair_id`),
  KEY `orders_side_index` (`side`),
  KEY `orders_type_index` (`type`),
  KEY `orders_status_index` (`status`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_client_order_id_index` (`client_order_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced trades table
CREATE TABLE `trades` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `buy_order_id` bigint(20) UNSIGNED NOT NULL,
  `sell_order_id` bigint(20) UNSIGNED NOT NULL,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `total` decimal(20,8) NOT NULL,
  `buyer_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `seller_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `buyer_id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trades_buy_order_id_index` (`buy_order_id`),
  KEY `trades_sell_order_id_index` (`sell_order_id`),
  KEY `trades_pair_id_index` (`pair_id`),
  KEY `trades_timestamp_index` (`timestamp`),
  KEY `trades_buyer_id_index` (`buyer_id`),
  KEY `trades_seller_id_index` (`seller_id`),
  CONSTRAINT `trades_buy_order_id_foreign` FOREIGN KEY (`buy_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trades_sell_order_id_foreign` FOREIGN KEY (`sell_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trades_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service providers table (enhanced)
CREATE TABLE `service_providers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('market_data','liquidity','payment','kyc','notification','custody','blockchain','staking','lending','defi','nft','oracle','analytics') NOT NULL,
  `subtype` varchar(50) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(5,2) NOT NULL DEFAULT 1.00,
  `config` json DEFAULT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `webhook_secret` varchar(255) DEFAULT NULL,
  `rate_limit` int(11) NOT NULL DEFAULT 1000,
  `timeout` int(11) NOT NULL DEFAULT 30,
  `retry_attempts` int(11) NOT NULL DEFAULT 3,
  `last_health_check` timestamp NULL DEFAULT NULL,
  `health_status` enum('healthy','unhealthy','unknown','maintenance') NOT NULL DEFAULT 'unknown',
  `health_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `last_error` text DEFAULT NULL,
  `error_count` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `total_requests` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_providers_type_index` (`type`),
  KEY `service_providers_is_enabled_index` (`is_enabled`),
  KEY `service_providers_priority_index` (`priority`),
  KEY `service_providers_health_status_index` (`health_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blockchain networks table
CREATE TABLE `blockchain_networks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `chain_id` varchar(20) DEFAULT NULL,
  `rpc_url` varchar(255) DEFAULT NULL,
  `ws_url` varchar(255) DEFAULT NULL,
  `explorer_url` varchar(255) DEFAULT NULL,
  `is_mainnet` tinyint(1) NOT NULL DEFAULT 1,
  `is_testnet` tinyint(1) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `confirmation_blocks` int(11) NOT NULL DEFAULT 6,
  `min_confirmations` int(11) NOT NULL DEFAULT 1,
  `max_confirmations` int(11) NOT NULL DEFAULT 100,
  `gas_limit` bigint(20) NOT NULL DEFAULT 21000,
  `gas_price` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `gas_price_gwei` decimal(10,2) NOT NULL DEFAULT 0.00,
  `config` json DEFAULT NULL,
  `provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blockchain_networks_symbol_unique` (`symbol`),
  KEY `blockchain_networks_is_enabled_index` (`is_enabled`),
  KEY `blockchain_networks_provider_id_index` (`provider_id`),
  CONSTRAINT `blockchain_networks_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cryptocurrencies table
CREATE TABLE `cryptocurrencies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `symbol` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `whitepaper` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `discord` varchar(255) DEFAULT NULL,
  `reddit` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_tradable` tinyint(1) NOT NULL DEFAULT 1,
  `is_depositable` tinyint(1) NOT NULL DEFAULT 1,
  `is_withdrawable` tinyint(1) NOT NULL DEFAULT 1,
  `is_stakable` tinyint(1) NOT NULL DEFAULT 0,
  `is_lendable` tinyint(1) NOT NULL DEFAULT 0,
  `is_marginable` tinyint(1) NOT NULL DEFAULT 0,
  `is_futures` tinyint(1) NOT NULL DEFAULT 0,
  `is_defi` tinyint(1) NOT NULL DEFAULT 0,
  `is_nft` tinyint(1) NOT NULL DEFAULT 0,
  `decimals` int(11) NOT NULL DEFAULT 8,
  `min_deposit` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `max_deposit` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `min_withdrawal` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `max_withdrawal` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `withdrawal_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `deposit_fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `trading_fee` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `staking_apy` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `lending_apy` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `market_cap` decimal(20,2) DEFAULT NULL,
  `circulating_supply` decimal(20,8) DEFAULT NULL,
  `total_supply` decimal(20,8) DEFAULT NULL,
  `max_supply` decimal(20,8) DEFAULT NULL,
  `price` decimal(20,8) DEFAULT NULL,
  `price_change_24h` decimal(8,4) DEFAULT NULL,
  `volume_24h` decimal(20,8) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `blockchain_network_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contract_address` varchar(255) DEFAULT NULL,
  `provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cryptocurrencies_symbol_unique` (`symbol`),
  KEY `cryptocurrencies_is_active_index` (`is_active`),
  KEY `cryptocurrencies_is_tradable_index` (`is_tradable`),
  KEY `cryptocurrencies_blockchain_network_id_index` (`blockchain_network_id`),
  KEY `cryptocurrencies_provider_id_index` (`provider_id`),
  CONSTRAINT `cryptocurrencies_blockchain_network_id_foreign` FOREIGN KEY (`blockchain_network_id`) REFERENCES `blockchain_networks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cryptocurrencies_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced admin users table
CREATE TABLE `admin_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','support','compliance','finance','developer','marketing','operations') NOT NULL DEFAULT 'admin',
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced audit logs table
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
  `request_id` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_actor_index` (`actor_id`,`actor_type`),
  KEY `audit_logs_subject_index` (`subject_id`,`subject_type`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_request_id_index` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced KYC requests table
CREATE TABLE `kyc_requests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider_request_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','approved','rejected','expired','under_review','requires_documents') NOT NULL DEFAULT 'pending',
  `kyc_level` enum('basic','intermediate','advanced','institutional') NOT NULL DEFAULT 'basic',
  `document_type` enum('passport','drivers_license','national_id','utility_bill','bank_statement','tax_document','employment_letter','proof_of_address') DEFAULT NULL,
  `document_front` varchar(255) DEFAULT NULL,
  `document_back` varchar(255) DEFAULT NULL,
  `selfie` varchar(255) DEFAULT NULL,
  `proof_of_address` varchar(255) DEFAULT NULL,
  `additional_documents` json DEFAULT NULL,
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
  CONSTRAINT `kyc_requests_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced configuration table
CREATE TABLE `config` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','boolean','json','decimal','array','object') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `category` varchar(50) DEFAULT NULL,
  `subcategory` varchar(50) DEFAULT NULL,
  `validation_rules` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key_unique` (`key`),
  KEY `config_category_index` (`category`),
  KEY `config_is_public_index` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced market data table
CREATE TABLE `market_data` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `volume_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `change_24h` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `high_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `low_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `open_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `close_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `market_cap` decimal(20,2) DEFAULT NULL,
  `circulating_supply` decimal(20,8) DEFAULT NULL,
  `total_supply` decimal(20,8) DEFAULT NULL,
  `max_supply` decimal(20,8) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `market_data_pair_id_index` (`pair_id`),
  KEY `market_data_timestamp_index` (`timestamp`),
  CONSTRAINT `market_data_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced order book table
CREATE TABLE `order_book` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pair_id` bigint(20) UNSIGNED NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `total` decimal(20,8) NOT NULL,
  `orders_count` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_book_pair_side_index` (`pair_id`,`side`),
  KEY `order_book_price_index` (`price`),
  KEY `order_book_timestamp_index` (`timestamp`),
  CONSTRAINT `order_book_pair_id_foreign` FOREIGN KEY (`pair_id`) REFERENCES `trading_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced notifications table
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('trade','deposit','withdrawal','kyc','security','general','staking','lending','margin','futures','defi','nft','system','marketing') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_is_read_index` (`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Futures contracts table
CREATE TABLE `futures_contracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `symbol` varchar(20) NOT NULL,
  `base_currency` varchar(10) NOT NULL,
  `quote_currency` varchar(10) NOT NULL,
  `contract_size` decimal(20,8) NOT NULL,
  `tick_size` decimal(20,8) NOT NULL,
  `tick_value` decimal(20,8) NOT NULL,
  `margin_requirement` decimal(8,4) NOT NULL,
  `liquidation_ratio` decimal(8,4) NOT NULL,
  `funding_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `funding_interval` int(11) NOT NULL DEFAULT 8,
  `settlement_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `futures_contracts_symbol_unique` (`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Futures positions table
CREATE TABLE `futures_positions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `side` enum('long','short') NOT NULL,
  `size` decimal(20,8) NOT NULL,
  `entry_price` decimal(20,8) NOT NULL,
  `mark_price` decimal(20,8) NOT NULL,
  `liquidation_price` decimal(20,8) NOT NULL,
  `unrealized_pnl` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `realized_pnl` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `margin_used` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `margin_available` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `futures_positions_user_id_index` (`user_id`),
  KEY `futures_positions_contract_id_index` (`contract_id`),
  CONSTRAINT `futures_positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `futures_positions_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `futures_contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staking pools table
CREATE TABLE `staking_pools` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `apy` decimal(8,4) NOT NULL,
  `min_stake_amount` decimal(20,8) NOT NULL,
  `max_stake_amount` decimal(20,8) NOT NULL,
  `lock_period` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `total_staked` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_rewards` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staking_pools_currency_index` (`currency`),
  KEY `staking_pools_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staking positions table
CREATE TABLE `staking_positions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pool_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `apy` decimal(8,4) NOT NULL,
  `lock_period` int(11) NOT NULL DEFAULT 0,
  `unlock_date` timestamp NULL DEFAULT NULL,
  `total_rewards` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `claimed_rewards` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staking_positions_user_id_index` (`user_id`),
  KEY `staking_positions_pool_id_index` (`pool_id`),
  CONSTRAINT `staking_positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staking_positions_pool_id_foreign` FOREIGN KEY (`pool_id`) REFERENCES `staking_pools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lending pools table
CREATE TABLE `lending_pools` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `apy` decimal(8,4) NOT NULL,
  `min_lend_amount` decimal(20,8) NOT NULL,
  `max_lend_amount` decimal(20,8) NOT NULL,
  `min_borrow_amount` decimal(20,8) NOT NULL,
  `max_borrow_amount` decimal(20,8) NOT NULL,
  `collateral_ratio` decimal(8,4) NOT NULL,
  `liquidation_ratio` decimal(8,4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `total_lent` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_borrowed` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lending_pools_currency_index` (`currency`),
  KEY `lending_pools_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lending positions table
CREATE TABLE `lending_positions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pool_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('lend','borrow') NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `apy` decimal(8,4) NOT NULL,
  `collateral_amount` decimal(20,8) DEFAULT NULL,
  `collateral_currency` varchar(10) DEFAULT NULL,
  `liquidation_price` decimal(20,8) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lending_positions_user_id_index` (`user_id`),
  KEY `lending_positions_pool_id_index` (`pool_id`),
  CONSTRAINT `lending_positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lending_positions_pool_id_foreign` FOREIGN KEY (`pool_id`) REFERENCES `lending_pools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DeFi protocols table
CREATE TABLE `defi_protocols` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `protocol_type` enum('dex','lending','yield_farming','liquidity_mining','staking','governance','derivatives','insurance') NOT NULL,
  `blockchain_network_id` bigint(20) UNSIGNED NOT NULL,
  `contract_address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `apy` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `tvl` decimal(20,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `defi_protocols_protocol_type_index` (`protocol_type`),
  KEY `defi_protocols_blockchain_network_id_index` (`blockchain_network_id`),
  CONSTRAINT `defi_protocols_blockchain_network_id_foreign` FOREIGN KEY (`blockchain_network_id`) REFERENCES `blockchain_networks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DeFi positions table
CREATE TABLE `defi_positions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `protocol_id` bigint(20) UNSIGNED NOT NULL,
  `position_type` enum('liquidity_provider','yield_farmer','lender','borrower','staker','governor') NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `apy` decimal(8,4) NOT NULL,
  `rewards` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `defi_positions_user_id_index` (`user_id`),
  KEY `defi_positions_protocol_id_index` (`protocol_id`),
  CONSTRAINT `defi_positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `defi_positions_protocol_id_foreign` FOREIGN KEY (`protocol_id`) REFERENCES `defi_protocols` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NFT collections table
CREATE TABLE `nft_collections` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `discord` varchar(255) DEFAULT NULL,
  `contract_address` varchar(255) NOT NULL,
  `blockchain_network_id` bigint(20) UNSIGNED NOT NULL,
  `total_supply` int(11) NOT NULL DEFAULT 0,
  `floor_price` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `volume_24h` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nft_collections_blockchain_network_id_index` (`blockchain_network_id`),
  KEY `nft_collections_is_active_index` (`is_active`),
  CONSTRAINT `nft_collections_blockchain_network_id_foreign` FOREIGN KEY (`blockchain_network_id`) REFERENCES `blockchain_networks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NFT items table
CREATE TABLE `nft_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` bigint(20) UNSIGNED NOT NULL,
  `token_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `attributes` json DEFAULT NULL,
  `rarity_score` decimal(8,4) DEFAULT NULL,
  `rarity_rank` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nft_items_collection_token_unique` (`collection_id`,`token_id`),
  KEY `nft_items_is_active_index` (`is_active`),
  CONSTRAINT `nft_items_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `nft_collections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NFT listings table
CREATE TABLE `nft_listings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nft_item_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `listing_type` enum('fixed_price','auction','offer') NOT NULL,
  `status` enum('active','sold','cancelled','expired') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nft_listings_user_id_index` (`user_id`),
  KEY `nft_listings_nft_item_id_index` (`nft_item_id`),
  KEY `nft_listings_status_index` (`status`),
  CONSTRAINT `nft_listings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nft_listings_nft_item_id_foreign` FOREIGN KEY (`nft_item_id`) REFERENCES `nft_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System health monitoring table
CREATE TABLE `system_health` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `status` enum('healthy','unhealthy','degraded','maintenance') NOT NULL,
  `response_time` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `error_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `uptime` decimal(8,4) NOT NULL DEFAULT 100.0000,
  `last_check` timestamp NOT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_health_service_name_index` (`service_name`),
  KEY `system_health_status_index` (`status`),
  KEY `system_health_last_check_index` (`last_check`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial data
INSERT INTO `trading_pairs` (`base_currency`, `quote_currency`, `symbol`, `is_active`, `is_spot_enabled`, `is_futures_enabled`, `is_margin_enabled`, `is_staking_enabled`, `min_trade_amount`, `max_trade_amount`, `tick_size`, `step_size`, `maker_fee`, `taker_fee`, `price_precision`, `quantity_precision`, `margin_ratio`, `liquidation_ratio`, `funding_rate`, `funding_interval`, `stake_apy`, `min_stake_amount`, `max_stake_amount`, `stake_lock_period`) VALUES
('BTC', 'USD', 'BTCUSD', 1, 1, 1, 1, 1, 0.0001, 1000.0000, 0.01, 0.0001, 0.0010, 0.0010, 2, 8, 0.1000, 0.0800, 0.0001, 8, 5.0000, 0.001, 100.0000, 0),
('ETH', 'USD', 'ETHUSD', 1, 1, 1, 1, 1, 0.001, 10000.0000, 0.01, 0.001, 0.0010, 0.0010, 2, 8, 0.1000, 0.0800, 0.0001, 8, 6.0000, 0.01, 1000.0000, 0),
('BTC', 'USDT', 'BTCUSDT', 1, 1, 1, 1, 1, 0.0001, 1000.0000, 0.01, 0.0001, 0.0010, 0.0010, 2, 8, 0.1000, 0.0800, 0.0001, 8, 5.0000, 0.001, 100.0000, 0),
('ETH', 'USDT', 'ETHUSDT', 1, 1, 1, 1, 1, 0.001, 10000.0000, 0.01, 0.001, 0.0010, 0.0010, 2, 8, 0.1000, 0.0800, 0.0001, 8, 6.0000, 0.01, 1000.0000, 0),
('ADA', 'USDT', 'ADAUSDT', 1, 1, 0, 0, 1, 1.0000, 100000.0000, 0.0001, 1.0000, 0.0010, 0.0010, 4, 2, 0.0000, 0.0000, 0.0000, 0, 4.5000, 100.0000, 1000000.0000, 0),
('SOL', 'USDT', 'SOLUSDT', 1, 1, 0, 0, 1, 0.01, 10000.0000, 0.01, 0.01, 0.0010, 0.0010, 2, 2, 0.0000, 0.0000, 0.0000, 0, 7.0000, 1.0000, 10000.0000, 0);

INSERT INTO `service_providers` (`name`, `type`, `subtype`, `is_enabled`, `is_active`, `priority`, `weight`, `config`, `api_endpoint`, `rate_limit`, `timeout`, `retry_attempts`, `health_status`, `health_score`) VALUES
('Binance', 'market_data', 'spot', 1, 1, 1, 1.00, '{"api_url": "https://api.binance.com", "rate_limit": 1200, "features": ["spot", "futures", "margin"]}', 'https://api.binance.com', 1200, 30, 3, 'healthy', 95.00),
('CoinGecko', 'market_data', 'spot', 1, 1, 2, 0.80, '{"api_url": "https://api.coingecko.com", "rate_limit": 50, "features": ["prices", "market_data", "trending"]}', 'https://api.coingecko.com', 50, 30, 3, 'healthy', 90.00),
('CoinMarketCap', 'market_data', 'spot', 1, 1, 3, 0.70, '{"api_url": "https://pro-api.coinmarketcap.com", "rate_limit": 100, "features": ["quotes", "listings", "trending"]}', 'https://pro-api.coinmarketcap.com', 100, 30, 3, 'healthy', 88.00),
('Stripe', 'payment', 'fiat', 1, 1, 1, 1.00, '{"api_url": "https://api.stripe.com", "webhook_tolerance": 300, "features": ["cards", "bank_transfers", "ach"]}', 'https://api.stripe.com', 100, 30, 3, 'healthy', 98.00),
('Jumio', 'kyc', 'identity_verification', 1, 1, 1, 1.00, '{"api_url": "https://netverify.com", "timeout": 30, "features": ["document_verification", "liveness_detection", "face_match"]}', 'https://netverify.com', 100, 30, 3, 'healthy', 92.00),
('Onfido', 'kyc', 'identity_verification', 1, 1, 2, 0.90, '{"api_url": "https://api.onfido.com", "timeout": 30, "features": ["document_verification", "liveness_detection", "face_match"]}', 'https://api.onfido.com', 100, 30, 3, 'healthy', 90.00),
('SendGrid', 'notification', 'email', 1, 1, 1, 1.00, '{"api_url": "https://api.sendgrid.com", "rate_limit": 100, "features": ["transactional", "marketing", "templates"]}', 'https://api.sendgrid.com', 100, 30, 3, 'healthy', 95.00),
('Twilio', 'notification', 'sms', 1, 1, 1, 1.00, '{"api_url": "https://api.twilio.com", "rate_limit": 100, "features": ["sms", "voice", "whatsapp"]}', 'https://api.twilio.com', 100, 30, 3, 'healthy', 93.00),
('Firebase', 'notification', 'push', 1, 1, 1, 1.00, '{"api_url": "https://fcm.googleapis.com", "rate_limit": 1000, "features": ["push_notifications", "in_app_messaging"]}', 'https://fcm.googleapis.com', 1000, 30, 3, 'healthy', 96.00),
('Infura', 'blockchain', 'ethereum', 1, 1, 1, 1.00, '{"api_url": "https://mainnet.infura.io", "rate_limit": 100000, "features": ["ethereum", "polygon", "arbitrum"]}', 'https://mainnet.infura.io', 100000, 30, 3, 'healthy', 97.00),
('Alchemy', 'blockchain', 'ethereum', 1, 1, 2, 0.95, '{"api_url": "https://eth-mainnet.alchemyapi.io", "rate_limit": 100000, "features": ["ethereum", "polygon", "arbitrum", "optimism"]}', 'https://eth-mainnet.alchemyapi.io', 100000, 30, 3, 'healthy', 96.00),
('QuickNode', 'blockchain', 'multi_chain', 1, 1, 3, 0.90, '{"api_url": "https://api.quicknode.com", "rate_limit": 100000, "features": ["ethereum", "bitcoin", "polygon", "solana"]}', 'https://api.quicknode.com', 100000, 30, 3, 'healthy', 94.00);

INSERT INTO `blockchain_networks` (`name`, `symbol`, `chain_id`, `rpc_url`, `ws_url`, `explorer_url`, `is_mainnet`, `is_testnet`, `is_enabled`, `is_active`, `confirmation_blocks`, `min_confirmations`, `max_confirmations`, `gas_limit`, `gas_price`, `gas_price_gwei`, `provider_id`) VALUES
('Ethereum', 'ETH', '1', 'https://mainnet.infura.io/v3/YOUR_PROJECT_ID', 'wss://mainnet.infura.io/ws/v3/YOUR_PROJECT_ID', 'https://etherscan.io', 1, 0, 1, 1, 12, 1, 100, 21000, 0.00000002, 20.00, 1),
('Bitcoin', 'BTC', '0', 'https://api.blockcypher.com/v1/btc/main', 'wss://api.blockcypher.com/v1/btc/main', 'https://blockstream.info', 1, 0, 1, 1, 6, 1, 100, 1000, 0.00000001, 0.00, 1),
('Polygon', 'MATIC', '137', 'https://polygon-rpc.com', 'wss://polygon-rpc.com', 'https://polygonscan.com', 1, 0, 1, 1, 30, 1, 100, 21000, 0.00000001, 1.00, 1),
('Binance Smart Chain', 'BNB', '56', 'https://bsc-dataseed.binance.org', 'wss://bsc-ws-node.nariox.org:443/ws', 'https://bscscan.com', 1, 0, 1, 1, 15, 1, 100, 21000, 0.00000001, 5.00, 1),
('Solana', 'SOL', '0', 'https://api.mainnet-beta.solana.com', 'wss://api.mainnet-beta.solana.com', 'https://explorer.solana.com', 1, 0, 1, 1, 1, 1, 100, 5000, 0.00000001, 0.00, 1),
('Avalanche', 'AVAX', '43114', 'https://api.avax.network/ext/bc/C/rpc', 'wss://api.avax.network/ext/bc/C/ws', 'https://snowtrace.io', 1, 0, 1, 1, 1, 1, 100, 21000, 0.00000001, 25.00, 1);

INSERT INTO `cryptocurrencies` (`symbol`, `name`, `full_name`, `description`, `logo_url`, `website`, `is_active`, `is_tradable`, `is_depositable`, `is_withdrawable`, `is_stakable`, `is_lendable`, `is_marginable`, `is_futures`, `is_defi`, `is_nft`, `decimals`, `min_deposit`, `max_deposit`, `min_withdrawal`, `max_withdrawal`, `withdrawal_fee`, `deposit_fee`, `trading_fee`, `staking_apy`, `lending_apy`, `price`, `price_change_24h`, `volume_24h`, `rank`, `category`, `blockchain_network_id`, `contract_address`, `provider_id`) VALUES
('BTC', 'Bitcoin', 'Bitcoin', 'The first and largest cryptocurrency by market cap', 'https://cryptologos.cc/logos/bitcoin-btc-logo.png', 'https://bitcoin.org', 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 8, 0.0001, 1000.0000, 0.0001, 1000.0000, 0.0005, 0.0000, 0.1000, 5.0000, 3.0000, 50000.00000000, 2.5000, 1000000.00000000, 1, 'currency', 2, NULL, 1),
('ETH', 'Ethereum', 'Ethereum', 'The second-largest cryptocurrency and leading smart contract platform', 'https://cryptologos.cc/logos/ethereum-eth-logo.png', 'https://ethereum.org', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 18, 0.001, 10000.0000, 0.001, 10000.0000, 0.005, 0.0000, 0.1000, 6.0000, 4.0000, 3000.00000000, 3.2000, 2000000.00000000, 2, 'platform', 1, NULL, 1),
('USDT', 'Tether', 'Tether USD', 'The largest stablecoin by market cap', 'https://cryptologos.cc/logos/tether-usdt-logo.png', 'https://tether.to', 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 6, 1.0000, 1000000.0000, 1.0000, 1000000.0000, 1.0000, 0.0000, 0.1000, 0.0000, 2.0000, 1.00000000, 0.1000, 5000000.00000000, 3, 'stablecoin', 1, '0xdAC17F958D2ee523a2206206994597C13D831ec7', 1),
('ADA', 'Cardano', 'Cardano', 'A blockchain platform for smart contracts and decentralized applications', 'https://cryptologos.cc/logos/cardano-ada-logo.png', 'https://cardano.org', 1, 1, 1, 1, 1, 0, 0, 0, 1, 0, 6, 1.0000, 1000000.0000, 1.0000, 1000000.0000, 0.1700, 0.0000, 0.1000, 4.5000, 0.0000, 0.50000000, 1.5000, 500000.00000000, 8, 'platform', 1, NULL, 1),
('SOL', 'Solana', 'Solana', 'A high-performance blockchain for decentralized applications', 'https://cryptologos.cc/logos/solana-sol-logo.png', 'https://solana.com', 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 9, 0.01, 100000.0000, 0.01, 100000.0000, 0.0005, 0.0000, 0.1000, 7.0000, 0.0000, 100.00000000, 5.2000, 800000.00000000, 9, 'platform', 5, NULL, 1),
('MATIC', 'Polygon', 'Polygon', 'A Layer 2 scaling solution for Ethereum', 'https://cryptologos.cc/logos/polygon-matic-logo.png', 'https://polygon.technology', 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 18, 1.0000, 1000000.0000, 1.0000, 1000000.0000, 0.1000, 0.0000, 0.1000, 8.0000, 3.0000, 1.00000000, 2.1000, 300000.00000000, 10, 'platform', 3, NULL, 1);

INSERT INTO `config` (`key`, `value`, `type`, `description`, `is_public`, `is_editable`, `category`, `subcategory`) VALUES
('exchange_name', 'CryptoExchange Pro', 'string', 'Exchange display name', 1, 1, 'general', 'branding'),
('exchange_logo', 'https://example.com/logo.png', 'string', 'Exchange logo URL', 1, 1, 'general', 'branding'),
('default_currency', 'USD', 'string', 'Default fiat currency', 1, 1, 'general', 'currency'),
('supported_currencies', '["USD", "EUR", "GBP", "JPY", "CAD", "AUD"]', 'array', 'Supported fiat currencies', 1, 1, 'general', 'currency'),
('trading_enabled', 'true', 'boolean', 'Enable/disable trading', 0, 1, 'trading', 'general'),
('futures_enabled', 'true', 'boolean', 'Enable/disable futures trading', 0, 1, 'trading', 'futures'),
('margin_enabled', 'true', 'boolean', 'Enable/disable margin trading', 0, 1, 'trading', 'margin'),
('staking_enabled', 'true', 'boolean', 'Enable/disable staking', 0, 1, 'trading', 'staking'),
('lending_enabled', 'true', 'boolean', 'Enable/disable lending', 0, 1, 'trading', 'lending'),
('defi_enabled', 'true', 'boolean', 'Enable/disable DeFi features', 0, 1, 'trading', 'defi'),
('nft_enabled', 'true', 'boolean', 'Enable/disable NFT features', 0, 1, 'trading', 'nft'),
('withdrawals_enabled', 'true', 'boolean', 'Enable/disable withdrawals', 0, 1, 'wallet', 'withdrawals'),
('deposits_enabled', 'true', 'boolean', 'Enable/disable deposits', 0, 1, 'wallet', 'deposits'),
('kyc_required', 'true', 'boolean', 'Require KYC for trading', 0, 1, 'compliance', 'kyc'),
('kyc_levels', '["basic", "intermediate", "advanced", "institutional"]', 'array', 'Available KYC levels', 0, 1, 'compliance', 'kyc'),
('min_trade_amount', '10', 'decimal', 'Minimum trade amount in USD', 1, 1, 'trading', 'limits'),
('max_trade_amount', '1000000', 'decimal', 'Maximum trade amount in USD', 1, 1, 'trading', 'limits'),
('trading_fee_percentage', '0.1', 'decimal', 'Default trading fee percentage', 1, 1, 'trading', 'fees'),
('withdrawal_fee_percentage', '0.5', 'decimal', 'Default withdrawal fee percentage', 1, 1, 'wallet', 'fees'),
('deposit_fee_percentage', '0.0', 'decimal', 'Default deposit fee percentage', 1, 1, 'wallet', 'fees'),
('maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', 0, 1, 'system', 'maintenance'),
('registration_enabled', 'true', 'boolean', 'Enable user registration', 0, 1, 'system', 'registration'),
('api_enabled', 'true', 'boolean', 'Enable API access', 0, 1, 'system', 'api'),
('websocket_enabled', 'true', 'boolean', 'Enable WebSocket connections', 0, 1, 'system', 'websocket'),
('rate_limit_per_minute', '60', 'integer', 'API rate limit per minute', 0, 1, 'system', 'rate_limiting'),
('max_login_attempts', '5', 'integer', 'Maximum login attempts before lockout', 0, 1, 'security', 'authentication'),
('session_timeout', '3600', 'integer', 'Session timeout in seconds', 0, 1, 'security', 'authentication'),
('two_factor_required', 'false', 'boolean', 'Require 2FA for all users', 0, 1, 'security', 'authentication'),
('ip_whitelist_enabled', 'false', 'boolean', 'Enable IP whitelist for admin', 0, 1, 'security', 'access_control'),
('audit_logging_enabled', 'true', 'boolean', 'Enable audit logging', 0, 1, 'security', 'logging'),
('backup_enabled', 'true', 'boolean', 'Enable automatic backups', 0, 1, 'system', 'backup'),
('backup_frequency', 'daily', 'string', 'Backup frequency', 0, 1, 'system', 'backup'),
('monitoring_enabled', 'true', 'boolean', 'Enable system monitoring', 0, 1, 'system', 'monitoring'),
('alert_email', 'admin@cryptoexchange.com', 'string', 'Alert email address', 0, 1, 'system', 'alerts');

-- Create super admin user (password: admin123)
INSERT INTO `admin_users` (`email`, `password`, `role`, `permissions`, `is_active`, `two_factor_enabled`) VALUES
('admin@cryptoexchange.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '["*"]', 1, 0);

-- Create staking pools
INSERT INTO `staking_pools` (`currency`, `name`, `description`, `apy`, `min_stake_amount`, `max_stake_amount`, `lock_period`, `is_active`, `total_staked`, `total_rewards`) VALUES
('BTC', 'Bitcoin Staking Pool', 'Stake Bitcoin and earn rewards', 5.0000, 0.001, 100.0000, 0, 1, 0.00000000, 0.00000000),
('ETH', 'Ethereum Staking Pool', 'Stake Ethereum and earn rewards', 6.0000, 0.01, 1000.0000, 0, 1, 0.00000000, 0.00000000),
('ADA', 'Cardano Staking Pool', 'Stake Cardano and earn rewards', 4.5000, 100.0000, 1000000.0000, 0, 1, 0.00000000, 0.00000000),
('SOL', 'Solana Staking Pool', 'Stake Solana and earn rewards', 7.0000, 1.0000, 10000.0000, 0, 1, 0.00000000, 0.00000000),
('MATIC', 'Polygon Staking Pool', 'Stake Polygon and earn rewards', 8.0000, 100.0000, 1000000.0000, 0, 1, 0.00000000, 0.00000000);

-- Create lending pools
INSERT INTO `lending_pools` (`currency`, `name`, `description`, `apy`, `min_lend_amount`, `max_lend_amount`, `min_borrow_amount`, `max_borrow_amount`, `collateral_ratio`, `liquidation_ratio`, `is_active`, `total_lent`, `total_borrowed`) VALUES
('USDT', 'USDT Lending Pool', 'Lend USDT and earn interest', 3.0000, 100.0000, 1000000.0000, 10.0000, 100000.0000, 1.5000, 1.2000, 1, 0.00000000, 0.00000000),
('USDC', 'USDC Lending Pool', 'Lend USDC and earn interest', 3.5000, 100.0000, 1000000.0000, 10.0000, 100000.0000, 1.5000, 1.2000, 1, 0.00000000, 0.00000000),
('BTC', 'Bitcoin Lending Pool', 'Lend Bitcoin and earn interest', 2.0000, 0.001, 100.0000, 0.0001, 10.0000, 2.0000, 1.5000, 1, 0.00000000, 0.00000000),
('ETH', 'Ethereum Lending Pool', 'Lend Ethereum and earn interest', 4.0000, 0.01, 1000.0000, 0.001, 100.0000, 1.7500, 1.3000, 1, 0.00000000, 0.00000000);

-- Create DeFi protocols
INSERT INTO `defi_protocols` (`name`, `symbol`, `description`, `website`, `protocol_type`, `blockchain_network_id`, `contract_address`, `is_active`, `apy`, `tvl`) VALUES
('Uniswap V3', 'UNI', 'Decentralized exchange protocol', 'https://uniswap.org', 'dex', 1, '0x1F98431c8aD98523631AE4a59f267346ea31F984', 1, 15.0000, 5000000000.00),
('Aave V3', 'AAVE', 'Decentralized lending protocol', 'https://aave.com', 'lending', 1, '0x87870Bca3F3fD6335C3F4ce8392D69350B4fA4E2', 1, 8.0000, 3000000000.00),
('Compound', 'COMP', 'Decentralized lending protocol', 'https://compound.finance', 'lending', 1, '0xc00e94Cb662C3520282E6f5717214004A7f26888', 1, 6.0000, 2000000000.00),
('PancakeSwap', 'CAKE', 'Decentralized exchange on BSC', 'https://pancakeswap.finance', 'dex', 4, '0x0E09FaBB73Bd3Ade0a17ECC321fD13a19e81cE82', 1, 12.0000, 1000000000.00),
('Raydium', 'RAY', 'Decentralized exchange on Solana', 'https://raydium.io', 'dex', 5, '4k3Dyjzvzp8eMZWUXbBCjEvwSkkk59S5iCNLY3QrkX6R', 1, 18.0000, 800000000.00);

-- Create futures contracts
INSERT INTO `futures_contracts` (`symbol`, `base_currency`, `quote_currency`, `contract_size`, `tick_size`, `tick_value`, `margin_requirement`, `liquidation_ratio`, `funding_rate`, `funding_interval`, `settlement_date`, `is_active`) VALUES
('BTCUSD-PERP', 'BTC', 'USD', 1.0000, 0.01, 0.01, 0.1000, 0.0800, 0.0001, 8, '2024-12-31', 1),
('ETHUSD-PERP', 'ETH', 'USD', 1.0000, 0.01, 0.01, 0.1000, 0.0800, 0.0001, 8, '2024-12-31', 1),
('BTCUSDT-PERP', 'BTC', 'USDT', 1.0000, 0.01, 0.01, 0.1000, 0.0800, 0.0001, 8, '2024-12-31', 1),
('ETHUSDT-PERP', 'ETH', 'USDT', 1.0000, 0.01, 0.01, 0.1000, 0.0800, 0.0001, 8, '2024-12-31', 1);

COMMIT;