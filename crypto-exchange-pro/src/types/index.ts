// User types
export interface User {
  id: number;
  email: string;
  phone?: string;
  status: 'active' | 'suspended' | 'banned' | 'pending';
  kyc_status: 'none' | 'pending' | 'approved' | 'rejected' | 'expired';
  two_factor_enabled: boolean;
  last_login_at?: string;
  created_at: string;
  profile?: UserProfile;
}

export interface UserProfile {
  first_name: string;
  last_name: string;
  country?: string;
  date_of_birth?: string;
}

// Wallet types
export interface Wallet {
  id: number;
  currency: string;
  balance_available: number;
  balance_reserved: number;
  balance_total: number;
  wallet_type: 'spot' | 'futures' | 'margin' | 'staking';
  formatted_balance: string;
  address?: string;
  network?: string;
}

export interface WalletAddress {
  id: number;
  address: string;
  network: string;
  tag?: string;
  is_active: boolean;
}

// Transaction types
export interface Transaction {
  id: number;
  type: 'deposit' | 'withdrawal' | 'trade' | 'fee' | 'reward' | 'refund' | 'transfer';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled';
  amount: number;
  fee: number;
  total_amount: number;
  currency: string;
  txid?: string;
  from_address?: string;
  to_address?: string;
  memo?: string;
  created_at: string;
  processed_at?: string;
}

// Trading types
export interface TradingPair {
  id: number;
  symbol: string;
  base_currency: string;
  quote_currency: string;
  price: number;
  change_24h: number;
  volume_24h: number;
  high_24h: number;
  low_24h: number;
  min_trade_amount: number;
  max_trade_amount: number;
  tick_size: number;
  step_size: number;
  maker_fee: number;
  taker_fee: number;
}

export interface Order {
  id: number;
  pair_id: number;
  side: 'buy' | 'sell';
  type: 'market' | 'limit' | 'stop' | 'stop_limit';
  price?: number;
  stop_price?: number;
  amount: number;
  filled: number;
  remaining: number;
  status: 'pending' | 'open' | 'partially_filled' | 'filled' | 'cancelled' | 'rejected';
  time_in_force: 'GTC' | 'IOC' | 'FOK' | 'GTD';
  created_at: string;
  filled_at?: string;
  cancelled_at?: string;
}

export interface Trade {
  id: number;
  price: number;
  amount: number;
  total: number;
  side: 'buy' | 'sell';
  timestamp: string;
}

export interface OrderBookEntry {
  price: number;
  amount: number;
  total: number;
}

export interface MarketData {
  price: number;
  change_24h: number;
  volume_24h: number;
  high_24h: number;
  low_24h: number;
  timestamp: string;
}

// KYC types
export interface KycRequest {
  id: number;
  status: 'pending' | 'processing' | 'approved' | 'rejected' | 'expired';
  document_type?: 'passport' | 'drivers_license' | 'national_id';
  submitted_at: string;
  reviewed_at?: string;
  review_notes?: string;
}

// Notification types
export interface Notification {
  id: number;
  type: 'trade' | 'deposit' | 'withdrawal' | 'kyc' | 'security' | 'general';
  title: string;
  message: string;
  is_read: boolean;
  created_at: string;
  read_at?: string;
}

// API response types
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  error?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

// Form types
export interface LoginForm {
  email: string;
  password: string;
}

export interface RegisterForm {
  email: string;
  password: string;
  password_confirmation: string;
  first_name: string;
  last_name: string;
  phone?: string;
  date_of_birth: string;
  country: string;
  terms_accepted: boolean;
}

export interface PlaceOrderForm {
  pair_id: number;
  side: 'buy' | 'sell';
  type: 'market' | 'limit' | 'stop' | 'stop_limit';
  amount: number;
  price?: number;
  stop_price?: number;
  time_in_force?: 'GTC' | 'IOC' | 'FOK' | 'GTD';
  expires_at?: string;
}

export interface WithdrawForm {
  currency: string;
  amount: number;
  fee: number;
  to_address: string;
  memo?: string;
}

// Chart types
export interface ChartData {
  time: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
}

// Admin types
export interface AdminStats {
  total_users: number;
  active_users: number;
  total_volume_24h: number;
  total_trades_24h: number;
  pending_kyc: number;
  pending_withdrawals: number;
}

export interface AdminUser extends User {
  created_at: string;
  last_login_at?: string;
  total_volume: number;
  total_trades: number;
}

// WebSocket types
export interface WebSocketMessage {
  type: string;
  data: any;
}

export interface PriceUpdate {
  symbol: string;
  price: number;
  change_24h: number;
  volume_24h: number;
}

export interface TradeUpdate {
  symbol: string;
  price: number;
  amount: number;
  side: 'buy' | 'sell';
  timestamp: string;
}

export interface OrderBookUpdate {
  symbol: string;
  bids: OrderBookEntry[];
  asks: OrderBookEntry[];
}